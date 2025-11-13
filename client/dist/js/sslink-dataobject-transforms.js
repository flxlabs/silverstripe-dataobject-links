/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

module.exports = jQuery;

/***/ }),

/***/ "lib/Injector":
/*!***************************!*\
  !*** external "Injector" ***!
  \***************************/
/***/ (function(module) {

module.exports = Injector;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
!function() {
/*!****************************************************!*\
  !*** ./client/src/sslink-dataobject-transforms.js ***!
  \****************************************************/
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");


var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
_Injector.default.transform('tinymce-link-dataobject', updater => {
  updater.reducer('form', originalReducer => () => (state, _ref) => {
    let {
      type,
      payload,
      __modified,
      ...more
    } = _ref;
    if (type === 'SET_SCHEMA' && __modified) {
      setTimeout(() => {
        const obj = {};
        payload.state.fields.forEach(f => obj[f.name] = f.value);
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
    if (type === 'SET_SCHEMA' && !__modified && state.formSchemas[payload.id].stateOverride && state.formSchemas[payload.id].stateOverride.fields.length > 0) {
      $.ajax({
        type: 'GET',
        url: payload.id,
        headers: {
          'x-formschema-request': 'auto,schema,state,errors'
        },
        data: state.formSchemas[payload.id].stateOverride.fields,
        success: function (data) {
          ss.store.dispatch({
            type: 'SET_SCHEMA',
            __modified: true,
            payload: {
              ...data,
              id: payload.id,
              state: {
                ...data.state,
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
  updater.form.alterSchema('Admin.InsertLinkDataObjectModal.*', form => form.updateField('ClassName', {
    onChange: (e, value, oldValue) => {
      $.ajax({
        type: 'GET',
        url: form.schema.id,
        headers: {
          'x-formschema-request': 'auto,schema,state,errors'
        },
        data: [{
          name: 'ClassName',
          value
        }],
        success: function (data) {
          ss.store.dispatch({
            type: 'SET_SCHEMA',
            payload: {
              ...data,
              id: form.schema.id,
              state: {
                ...data.state,
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
  }).getState());
}, {
  before: '*'
});
}();
/******/ })()
;
//# sourceMappingURL=sslink-dataobject-transforms.js.map