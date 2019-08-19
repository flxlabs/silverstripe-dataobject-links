import Injector from 'lib/Injector';

Injector.transform(
	'tinymce-link-dataobject',
	updater => {
		updater.reducer('form', originalReducer => () => (state, { type, payload, __modified, ...more }) => {
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
				return originalReducer(state, {
					type,
					payload,
					...more
				});
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

			return originalReducer(state, {
				type,
				payload,
				...more
			});
		});

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
	{
		before: '*'
	}
);
