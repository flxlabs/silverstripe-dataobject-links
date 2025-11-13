/* global tinymce, ss */
import i18n from 'i18n';
import TinyMCEActionRegistrar from 'lib/TinyMCEActionRegistrar';
import React from 'react';
import ReactDOM from 'react-dom';
import { createRoot } from 'react-dom/client';
//import { ApolloProvider } from '@apollo/client';
//import { Provider } from 'react-redux';
import jQuery from 'jquery';
import ShortcodeSerialiser from 'lib/ShortcodeSerialiser';
import { createInsertLinkModal } from 'containers/InsertLinkModal/InsertLinkModal';
import { loadComponent } from 'lib/Injector';

const commandName = 'sslinkdataobject';



const plugin = {
	init(editor) {
		// Link to a dataobject
		TinyMCEActionRegistrar.addAction('sslink', {
			text: i18n._t('CMS.LINKLABEL_PAGE', 'Link to an Object'),
			onclick: (editorInst) => editorInst.execCommand(commandName),
			priority: 53,
		}).addCommandWithUrlTest(commandName, /^\[dataobject_link.+]$/);

		editor.addCommand(commandName, () => {
			const field = jQuery(`#${editor.id}`).entwine('ss');

			field.openLinkDataObjectDialog();
		});
	},
};

const modalId = 'insert-link__dialog-wrapper--dataobject';
//const sectionConfigKey = 'SilverStripe\\CMS\\Controllers\\CMSPageEditController';
const sectionConfigKey = 'SilverStripe\\Admin\\LeftAndMain';
const formName = 'EditorDataObjectLink';
const InsertLinkDataObjectModal = loadComponent(createInsertLinkModal(sectionConfigKey, formName));

jQuery.entwine('ss', ($) => {
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
		},
	});

	/**
	 * Assumes that $('.insert-link__dialog-wrapper').entwine({}); is defined for shared functions
	 */
	$(`#${modalId}`).entwine({
		ReactRoot: null,
		renderModal(isOpen) {
			const store = ss.store;
			const client = ss.apolloClient;
			const handleHide = () => this.close();
			const handleInsert = (...args) => this.handleInsert(...args);
			const attrs = this.getOriginalAttributes();
			const requireLinkText = this.getRequireLinkText();

			// create/update the react component
			let root = this.getReactRoot();
			if (!root) {
				root = createRoot(this[0]);
				this.setReactRoot(root);
			}
			// create/update the react component
			root.render(
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
			);
			/*root.render(
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
			);*/
		},

		/**
		 * Determine whether to show the link text field
		 *
		 * @return {Boolean}
		 */
		getRequireLinkText() {
			const selection = this.getElement().getEditor().getInstance().selection;
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
					properties: { clazz: data.ClassName, id: data.ObjectID },
				},
				true,
			);

			attributes.href = shortcode;

			return attributes;
		},

		getOriginalAttributes() {
			const editor = this.getElement().getEditor();
			const node = $(editor.getSelectedNode());
			// Get href
			const href = node.attr('href') || '';
			if (!href) {
				return {};
			}
			
			// check if page is safe
			const shortcode = ShortcodeSerialiser.match('dataobject_link', false, href);
			if (!shortcode) {
				return {};
			}

			// Parse class ourselves because shortcode parser sucks
			const clazz = shortcode.original.match(/clazz=(.*?)\W/)[1];
			return {
				ClassName: clazz,
				ObjectID: shortcode.properties.id ? parseInt(shortcode.properties.id, 10) : 0,
				Description: node.attr('title'),
				TargetBlank: !!node.attr('target'),
			};
		},
	});
});

// Adds the plugin class to the list of available TinyMCE plugins
tinymce.PluginManager.add(commandName, (editor) => plugin.init(editor));

export default plugin;
