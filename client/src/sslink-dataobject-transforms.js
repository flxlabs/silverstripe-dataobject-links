import Injector from 'lib/Injector';

const transformPayloadId = 'admin/modals/linkModalFormSchema/editorDataObjectLink/0';
const formName = 'Admin.InsertLinkDataObjectModal.linkModalForm/editorDataObjectLink';

Injector.transform(
	'tinymce-link-dataobject',
	(updater) => {
		updater.reducer('form', (originalReducer) => () => (state, { __modified, ...action }) => {
			// The default schema does not include state override fields (which in our case are the selected class and object ID).
			// Therefore we need to re-fetch the schema including those fields when the schema is first set.
			if (
				action.type === 'SET_SCHEMA' &&
				!__modified &&
				state.formSchemas[action.payload.id].stateOverride &&
				state.formSchemas[action.payload.id].stateOverride.fields.length > 0 &&
				action.payload.id === transformPayloadId
			) {
				$.ajax({
					type: 'GET',
					url: action.payload.id,
					headers: {
						'x-formschema-request': 'auto,schema,state,errors',
					},
					data: state.formSchemas[action.payload.id].stateOverride.fields,
					success: function (data) {
						ss.store.dispatch({
							...action,
							__modified: true,
							payload: {
								...action.payload,
								...data,
							},
						});
					},
				});
				return state;
			}
			// When the ClassName field changes, we need to re-fetch the schema to update the ObjectID field options.
			// This is does not look like the most elegant way to achieve this, but we've found no better way yet.
			if (action.type === '@@redux-form/CHANGE' && action.meta.form === formName && action.meta.field === 'ClassName') {
				const schemaId = Object.keys(state.formSchemas).find((key) => state.formSchemas[key].name === action.meta.form);
				const form = state.formSchemas[schemaId];
				$.ajax({
					type: 'GET',
					url: form.id,
					headers: {
						'x-formschema-request': 'auto,schema,state,errors',
					},
					data: [
						{
							name: 'ClassName',
							value: action.payload,
						},
					],
					success: function (data) {
						ss.store.dispatch({
							type: 'SET_SCHEMA',
							__modified: true,
							payload: {
								...data,
								id: form.id,
								state: {
									...data.state,
									// Only override ObjectID and ClassName fields
									fields: data.state.fields.map((f, i) =>
										['ObjectID', 'ClassName'].includes(f.name) ? f : form.state.fields.find((f2) => f2.id === f.id),
									),
								},
							},
						});
						ss.store.dispatch({
							type: '@@redux-form/CHANGE',
							meta: {
								...action.meta,
								field: 'ObjectID',
							},
							payload: '',
						});
					},
				});
			}
			return originalReducer(state, action);
		});
	},
	{
		before: '*',
	},
);
