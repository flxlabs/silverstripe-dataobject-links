/* global tinymce, ss */
import i18n from 'i18n';
import TinyMCEActionRegistrar from 'lib/TinyMCEActionRegistrar';
import React from 'react';
import ReactDOM from 'react-dom';
import { ApolloProvider } from 'react-apollo';
import { Provider } from 'react-redux';
import jQuery from 'jquery';
import ShortcodeSerialiser from 'lib/ShortcodeSerialiser';
import { createInsertLinkModal } from 'containers/InsertLinkModal/InsertLinkModal';
import { provideInjector } from 'lib/Injector';
import Injector from 'lib/Injector';

const commandName = 'sslinkdataobject';

// Link to a dataobject
TinyMCEActionRegistrar.addAction('sslink', {
	text: i18n._t('CMS.LINKLABEL_PAGE', 'Link to an Object'),
	onclick: editor => editor.execCommand(commandName),
	priority: 53
}).addCommandWithUrlTest(commandName, /^\[dataobject_link.+]$/);

const plugin = {
	init(editor) {
		editor.addCommand(commandName, () => {
			const field = jQuery(`#${editor.id}`).entwine('ss');

			field.openLinkDataObjectDialog();
		});
	}
};

const modalId = 'insert-link__dialog-wrapper--dataobject';
const sectionConfigKey =
	'SilverStripe\\CMS\\Controllers\\CMSPageEditController';
const formName = 'editorDataObjectLink';
const InsertLinkDataObjectModal = provideInjector(
	createInsertLinkModal(sectionConfigKey, formName)
);

const MyReducerTransformer = originalReducer => getGlobalState => (
	state,
	{ type, payload, __modified, ...more }
) => {
	if (type === 'SET_SCHEMA' && __modified) {
		setTimeout(() => {
			const obj = {};
			payload.state.fields.forEach(f => (obj[f.name] = f.value));
			delete obj['action_insert'];

			ss.store.dispatch({
				type: '@@redux-form/INITIALIZE',
				meta: {
					form: 'Admin.InsertLinkDataObjectModal.editorDataObjectLink',
					keepDirty: false,
					keepSubmitSucceeded: true
				},
				payload: obj
			});
		}, 0);
		return originalReducer(state, { type, payload, ...more });
	}

	if (
		type === 'SET_SCHEMA' &&
		!__modified &&
		state.formSchemas[payload.id].stateOverride &&
		state.formSchemas[payload.id].stateOverride.fields.length > 0
	) {
		$.ajax({
			type: 'GET',
			url: payload.id,
			headers: {
				'x-formschema-request': 'auto,schema,state,errors'
			},
			data: state.formSchemas[payload.id].stateOverride.fields,
			success: function(data) {
				ss.store.dispatch({
					type: 'SET_SCHEMA',
					__modified: true,
					payload: {
						...data,
						id: payload.id,
						state: {
							...data.state,
							// We merge the new fields with the existing ones
							fields: data.state.fields.map((f, i) => ({
								...payload.state.fields.find(f2 => f2.id === f.id),
								...f
							}))
						}
					},
					...more
				});
			}
		});
		return state;
	}

	return originalReducer(state, { type, payload, ...more });
};

Injector.transform(
	'tinymce-link-dataobject',
	updater => {
		updater.reducer('form', MyReducerTransformer);

		updater.form.alterSchema('Admin.InsertLinkDataObjectModal.*', form =>
			form
				.updateField('ClassName', {
					onChange: (e, value, oldValue) => {
						$.ajax({
							type: 'GET',
							url: form.schema.id,
							headers: {
								'x-formschema-request': 'auto,schema,state,errors'
							},
							data: [
								{
									name: 'ClassName',
									value
								}
							],
							success: function(data) {
								ss.store.dispatch({
									type: 'SET_SCHEMA',
									payload: {
										...data,
										id: form.schema.id,
										state: {
											...data.state,
											// We merge the new fields with the existing ones
											fields: data.state.fields.map((f, i) => ({
												...form.schema.state.fields.find(f2 => f2.id === f.id),
												...f
											}))
										}
									}
								});
							}
						});
					}
				})
				.getState()
		);
	},
	{ before: '*' }
);

jQuery.entwine('ss', $ => {
	$('textarea.htmleditor').entwine({
		openLinkDataObjectDialog() {
			let dialog = $(`#${modalId}`);

			if (!dialog.length) {
				dialog = $(`<div id="${modalId}" />`);
				$('body').append(dialog);
			}
			dialog.addClass('insert-link__dialog-wrapper');

			dialog.setElement(this);
			dialog.open();
		}
	});

	/**
	 * Assumes that $('.insert-link__dialog-wrapper').entwine({}); is defined for shared functions
	 */
	$(`#${modalId}`).entwine({
		renderModal(isOpen) {
			const store = ss.store;
			const client = ss.apolloClient;
			const handleHide = () => this.close();
			const handleInsert = (...args) => this.handleInsert(...args);
			const attrs = this.getOriginalAttributes();
			const requireLinkText = this.getRequireLinkText();

			// create/update the react component
			ReactDOM.render(
				<ApolloProvider client={client}>
					<Provider store={store}>
						<InsertLinkDataObjectModal
							show={isOpen}
							isOpen={isOpen}
							onInsert={handleInsert}
							onHide={handleHide}
							onClosed={handleHide}
							title={i18n._t('CMS.LINK_PAGE', 'Link to a DataObject')}
							bodyClassName="modal__dialog"
							className={modalId}
							fileAttributes={attrs}
							identifier="Admin.InsertLinkDataObjectModal"
							requireLinkText={requireLinkText}
						/>
					</Provider>
				</ApolloProvider>,
				this[0]
			);
		},

		/**
		 * Determine whether to show the link text field
		 *
		 * @return {Boolean}
		 */
		getRequireLinkText() {
			const selection = this.getElement()
				.getEditor()
				.getInstance().selection;
			const selectionContent = selection.getContent() || '';
			const tagName = selection.getNode().tagName;
			const requireLinkText = tagName !== 'A' && selectionContent.trim() === '';

			return requireLinkText;
		},

		/**
		 * @param {Object} data - Posted data
		 * @return {Object}
		 */
		buildAttributes(data) {
			const attributes = this._super(data);

			const shortcode = ShortcodeSerialiser.serialise(
				{
					name: 'dataobject_link',
					properties: { clazz: data.ClassName, id: data.ObjectID }
				},
				true
			);

			attributes.href = shortcode;

			return attributes;
		},

		getOriginalAttributes() {
			const editor = this.getElement().getEditor();
			const node = $(editor.getSelectedNode());

			// Get href
			const href = node.attr('href') || '';
			if (!href || !clazz) {
				return {};
			}

			// check if page is safe
			const shortcode = ShortcodeSerialiser.match(
				'dataobject_link',
				false,
				href
			);
			if (!shortcode) {
				return {};
			}

			// Parse class ourselves because shortcode parser sucks
			const clazz = shortcode.original.match(/clazz=(.*?)\W/)[1];

			return {
				ClassName: clazz,
				ObjectID: shortcode.properties.id
					? parseInt(shortcode.properties.id, 10)
					: 0,
				Description: node.attr('title'),
				TargetBlank: !!node.attr('target')
			};
		}
	});
});

// Adds the plugin class to the list of available TinyMCE plugins
tinymce.PluginManager.add(commandName, editor => plugin.init(editor));

export default plugin;
