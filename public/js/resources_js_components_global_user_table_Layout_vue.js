"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_global_user_table_Layout_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js":
/*!********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js ***!
  \********************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }



// Actions

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    project: {
      type: Object
    }
  },
  methods: {
    change: function change(event) {
      // Remove without param
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.REMOVE_GLOBAL_USER_PARAMS, {
        key: this.withoutKey,
        value: this.value,
        multiple: true
      });

      // Add or remove with param
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(event.target.checked ? _actions_user__WEBPACK_IMPORTED_MODULE_1__.ADD_GLOBAL_USER_PARAMS : _actions_user__WEBPACK_IMPORTED_MODULE_1__.REMOVE_GLOBAL_USER_PARAMS, {
        key: this.withKey,
        value: this.value,
        multiple: true
      });
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.FETCH_GLOBAL_USERS);
      this.$emit("change", event);
    },
    toggleExclude: function toggleExclude() {
      // Add or remove with param
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(this.isExcluded ? _actions_user__WEBPACK_IMPORTED_MODULE_1__.ADD_GLOBAL_USER_PARAMS : _actions_user__WEBPACK_IMPORTED_MODULE_1__.REMOVE_GLOBAL_USER_PARAMS, {
        key: this.withKey,
        value: this.value,
        multiple: true
      });

      // Add or remove without param
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(this.isExcluded ? _actions_user__WEBPACK_IMPORTED_MODULE_1__.REMOVE_GLOBAL_USER_PARAMS : _actions_user__WEBPACK_IMPORTED_MODULE_1__.ADD_GLOBAL_USER_PARAMS, {
        key: this.withoutKey,
        value: this.value,
        multiple: true
      });
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.FETCH_GLOBAL_USERS);
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_2__.mapGetters)(["globalUsersParamExists"])), {}, {
    /**
     *
     */
    withKey: function withKey() {
      return "withProjects";
    },
    /**
     *
     */
    withoutKey: function withoutKey() {
      return "withoutProjects";
    },
    /**
     *
     */
    value: function value() {
      return this.project.id;
    },
    /**
     *
     */
    isExcluded: function isExcluded() {
      return this.globalUsersParamExists(this.withoutKey, this.value);
    },
    /**
     *
     */
    isChecked: function isChecked() {
      return this.globalUsersParamExists(this.withKey, this.value) || this.globalUsersParamExists(this.withoutKey, this.value);
    },
    /**
     *
     */
    style: function style() {
      return {
        color: this.project.bgcolor
      };
    },
    /**
     *
     */
    excludeStyle: function excludeStyle() {
      return {
        color: this.isExcluded ? "#CC0000" : "#CCCCCC"
      };
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user_project__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user/project */ "./resources/js/actions/user/project.js");
/* harmony import */ var _actions_project__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/project */ "./resources/js/actions/project.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _ProjectRow_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ProjectRow.vue */ "./resources/js/components/global/user/filters/project/ProjectRow.vue");
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }



// Actions




// Components

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    ProjectRow: _ProjectRow_vue__WEBPACK_IMPORTED_MODULE_4__["default"]
  },
  data: function data() {
    return {
      name: "global-users-table-filter-project",
      tab: 0,
      projectKeyword: ""
    };
  },
  methods: {
    /**
     *
     * @param {*} project
     */
    isProjectChecked: function isProjectChecked(project) {
      return this.userProjects.findIndex(function (l) {
        return l.id == project.id;
      }) >= 0;
    },
    /**
     *
     * @param {*} project
     */
    fetchProjects: function fetchProjects() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_project__WEBPACK_IMPORTED_MODULE_2__.FETCH_PROJECTS, {
        filters: JSON.stringify({
          query: this.projectKeyword,
          type: ["professional"]
        })
      });
    },
    /**
     *
     */
    addProject: function addProject() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_3__.OPEN_MODAL, "project-add");
    }
  },
  watch: {
    globalUser: function globalUser(newValue) {
      if (this.slideOpen(this.name)) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_project__WEBPACK_IMPORTED_MODULE_1__.FETCH_USER_PROJECTS, newValue);
      }
    },
    projectKeyword: function projectKeyword() {
      this.fetchProjects();
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_5__.mapGetters)(["projects", "globalUser", "userProjects", "slideOpen", "can"])), {}, {
    /**
     *
     */
    filteredProjects: function filteredProjects() {
      var keyword = removeStringAccent(this.projectKeyword);
      return this.projects.filter(function (project) {
        return removeStringAccent(project.name).indexOf(keyword) >= 0;
      });
    },
    /**
     *
     */
    all: {
      get: function get() {
        var _this = this;
        var _loop = function _loop(i) {
          if (!_this.userProjects.find(function (project) {
            return project.id == _this.filteredProjects[i].id;
          })) {
            return {
              v: false
            };
          }
        };
        for (var i in this.filteredProjects) {
          var _ret = _loop(i);
          if (_typeof(_ret) === "object") return _ret.v;
        }
        return true;
      },
      set: function () {
        var _set = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(value) {
          return _regeneratorRuntime().wrap(function _callee$(_context) {
            while (1) switch (_context.prev = _context.next) {
              case 0:
                if (value) {
                  this.filteredProjects.forEach(function (f) {
                    _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user_project__WEBPACK_IMPORTED_MODULE_1__.ADD_USER_PROJECT, f);
                  });
                  _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(BULK_ADD_USER_PROJECT, {
                    users: [this.user.id],
                    projects: this.filteredProjects.map(function (f) {
                      return f.id;
                    })
                  });
                } else {
                  this.filteredProjects.forEach(function (f) {
                    _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user_project__WEBPACK_IMPORTED_MODULE_1__.REMOVE_USER_PROJECT, f);
                  });
                  _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(BULK_REMOVE_USER_PROJECT, {
                    users: [this.user.id],
                    projects: this.filteredProjects.map(function (f) {
                      return f.id;
                    })
                  });
                }
              case 1:
              case "end":
                return _context.stop();
            }
          }, _callee, this);
        }));
        function set(_x) {
          return _set.apply(this, arguments);
        }
        return set;
      }()
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user_project__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user/project */ "./resources/js/actions/user/project.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");



// Actions


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    value: {
      type: Boolean,
      "default": false
    },
    project: {
      type: Object
    }
  },
  data: function data() {
    return {
      currentValue: this.value
    };
  },
  methods: {
    change: function change(event) {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(event.target.checked ? _actions_user_project__WEBPACK_IMPORTED_MODULE_1__.ADD_USER_PROJECT : _actions_user_project__WEBPACK_IMPORTED_MODULE_1__.REMOVE_USER_PROJECT, this.project);
      this.$emit("change", event);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _apis_project__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/apis/project */ "./resources/js/apis/project.js");
/* harmony import */ var _actions_user_project__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/user/project */ "./resources/js/actions/user/project.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _ProjectRow_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ProjectRow.vue */ "./resources/js/components/global/user/project/ProjectRow.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }




// Actions



// Components

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    ProjectRow: _ProjectRow_vue__WEBPACK_IMPORTED_MODULE_4__["default"]
  },
  data: function data() {
    return {
      name: "user-manage-projects",
      tab: 0,
      projects: [],
      projectKeyword: "",
      projectCancelTokenSource: null
    };
  },
  methods: {
    /**
     *
     * @param {*} project
     */
    isProjectChecked: function isProjectChecked(project) {
      return this.userProjects.findIndex(function (l) {
        return l.id == project.id;
      }) >= 0;
    },
    /**
     *
     * @param {*} project
     */
    fetchProjects: function fetchProjects() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$ProjectService, data;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (_this.projectCancelTokenSource) {
                _this.projectCancelTokenSource.cancel();
              }
              _this.projectCancelTokenSource = axios.CancelToken.source();
              _context.next = 4;
              return _apis_project__WEBPACK_IMPORTED_MODULE_1__["default"].get({
                params: {
                  filters: JSON.stringify({
                    query: _this.projectKeyword
                    // type: ["professional"],
                  })
                },
                cancelToken: _this.projectCancelTokenSource.token
              });
            case 4:
              _yield$ProjectService = _context.sent;
              data = _yield$ProjectService.data;
              _this.projects = data.data;
            case 7:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    /**
     *
     */
    addProject: function addProject() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_3__.OPEN_MODAL, "project-add");
    }
  },
  watch: {
    globalUser: function globalUser(newValue) {
      if (this.slideOpen(this.name)) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_project__WEBPACK_IMPORTED_MODULE_2__.FETCH_USER_PROJECTS, newValue);
      }
    },
    projectKeyword: function projectKeyword() {
      this.fetchProjects();
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_5__.mapGetters)(["globalUser", "userProjects", "slideOpen", "can"])), {}, {
    /**
     *
     */
    filteredProjects: function filteredProjects() {
      var keyword = removeStringAccent(this.projectKeyword);
      return this.projects.filter(function (project) {
        return removeStringAccent(project.name).indexOf(keyword) >= 0;
      });
    },
    /**
     *
     */
    all: {
      get: function get() {
        var _this2 = this;
        var _loop = function _loop(i) {
          if (!_this2.userProjects.find(function (project) {
            return project.id == _this2.filteredProjects[i].id;
          })) {
            return {
              v: false
            };
          }
        };
        for (var i in this.filteredProjects) {
          var _ret = _loop(i);
          if (_typeof(_ret) === "object") return _ret.v;
        }
        return true;
      },
      set: function () {
        var _set = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(value) {
          return _regeneratorRuntime().wrap(function _callee2$(_context2) {
            while (1) switch (_context2.prev = _context2.next) {
              case 0:
                if (value) {
                  this.filteredProjects.forEach(function (f) {
                    _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user_project__WEBPACK_IMPORTED_MODULE_2__.ADD_USER_PROJECT, f);
                  });
                  _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(BULK_ADD_USER_PROJECT, {
                    users: [this.user.id],
                    projects: this.filteredProjects.map(function (f) {
                      return f.id;
                    })
                  });
                } else {
                  this.filteredProjects.forEach(function (f) {
                    _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user_project__WEBPACK_IMPORTED_MODULE_2__.REMOVE_USER_PROJECT, f);
                  });
                  _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(BULK_REMOVE_USER_PROJECT, {
                    users: [this.user.id],
                    projects: this.filteredProjects.map(function (f) {
                      return f.id;
                    })
                  });
                }
              case 1:
              case "end":
                return _context2.stop();
            }
          }, _callee2, this);
        }));
        function set(_x) {
          return _set.apply(this, arguments);
        }
        return set;
      }()
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _actions_slide__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/slide */ "./resources/js/actions/slide.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _apis_common__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/apis/common */ "./resources/js/apis/common.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }






/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  data: function data() {
    return {
      createdUsers: 0,
      bulkingRemove: false,
      bulkingRestore: false,
      bulkingProcessed: false,
      bulkingNotProcessed: false,
      fetchingPreviousPage: false,
      fetchingNextPage: false,
      settingUsersCount: false
    };
  },
  mounted: function mounted() {
    if (!this.globalUsersOptions) {
      this.toggleUsersOptions();
    }
  },
  methods: {
    /**
     * Deselect selected user
     */
    bulkDeselect: function bulkDeselect() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.UPDATE_GLOBAL_SELECTED_USERS, []);
    },
    /**
     * Store new user
     */
    addUser: function addUser() {
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_1__.OPEN_MODAL, "global-user-add");
            case 1:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    /**
     * Add other columns
     * in the table
     */
    addColumn: function addColumn() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_1__.OPEN_MODAL, "global-users-table-add-column");
    },
    /**
     * Previous page
     */
    previousPage: function previousPage() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.SET_GLOBAL_USERS_PAGE, parseInt(_this.globalUsersPage) - 1);

              // Refresh users list
              _this.fetchingPreviousPage = true;
              _context2.prev = 2;
              _context2.next = 5;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_GLOBAL_USERS);
            case 5:
              _context2.prev = 5;
              _this.fetchingPreviousPage = false;
              return _context2.finish(5);
            case 8:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[2,, 5, 8]]);
      }))();
    },
    /**
     * Next page
     */
    nextPage: function nextPage() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.SET_GLOBAL_USERS_PAGE, parseInt(_this2.globalUsersPage) + 1);

              // Refresh users list
              _this2.fetchingNextPage = true;
              _context3.prev = 2;
              _context3.next = 5;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_GLOBAL_USERS);
            case 5:
              _context3.prev = 5;
              _this2.fetchingNextPage = false;
              return _context3.finish(5);
            case 8:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[2,, 5, 8]]);
      }))();
    },
    /**
     * Init filters
     */
    initParams: function initParams() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.INIT_GLOBAL_USER_PARAMS);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_GLOBAL_USERS);
    },
    /**
     * Toggle users options
     * Show or hide each user menus (sms, phone, order, ..)
     * from the users table
     */
    toggleUsersOptions: function toggleUsersOptions() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.TOGGLE_GLOBAL_USERS_OPTIONS);
    },
    /**
     * Bulk calendars for selected users
     * See: @/components/user/bulk/calendar/Slide.vue
     */
    bulkCalendar: function bulkCalendar() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-calendars");
    },
    /**
     * Bulk categories for selected users
     * See: @/components/user/bulk/category/Slide.vue
     */
    bulkCategory: function bulkCategory() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-categories");
    },
    /**
     * Bulk documents for selected users
     * See: @/components/user/bulk/document/Slide.vue
     */
    bulkDocument: function bulkDocument() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-documents");
    },
    /**
     * Bulk folders for selected users
     * See: @/components/user/bulk/folder/Slide.vue
     */
    bulkFolder: function bulkFolder() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-folders");
    },
    /**
     * Bulk groups for selected users
     * See: @/components/user/bulk/group/Slide.vue
     */
    bulkGroup: function bulkGroup() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-groups");
    },
    /**
     * Bulk menus for selected users
     * See: @/components/user/bulk/menu/Slide.vue
     */
    bulkMenu: function bulkMenu() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-menus");
    },
    /**
     * Bulk order actions for selected users
     * See: @/components/user/bulk/order-action/Slide.vue
     */
    bulkOrderAction: function bulkOrderAction() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-order-actions");
    },
    /**
     * Bulk order_steps for selected users
     * See: @/components/user/bulk/order-step/Slide.vue
     */
    bulkOrderStep: function bulkOrderStep() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-order-steps");
    },
    /**
     * Bulk users for selected roles
     * See: @/components/user/bulk/role/Slide.vue
     */
    bulkRole: function bulkRole() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-roles");
    },
    /**
     * Bulk threads for selected users
     * See: @/components/user/bulk/thread/Slide.vue
     */
    bulkThread: function bulkThread() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-threads");
    },
    /**
     * Bulk users for selected users
     * See: @/components/user/bulk/user/Slide.vue
     */
    bulkUser: function bulkUser() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "user-bulk-manage-users");
    },
    /**
     * Remove multiple users
     */
    bulkRemove: function bulkRemove() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var globalUsersSelected;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _this3.bulkingRemove = true;

              // Selected users
              globalUsersSelected = _this3.globalUsersSelected;
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.UPDATE_GLOBAL_SELECTED_USERS, []);
              _context4.prev = 3;
              _context4.next = 6;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.BULK_GLOBAL_REMOVE_USER, globalUsersSelected);
            case 6:
              // Refresh users list
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_GLOBAL_USERS);
            case 7:
              _context4.prev = 7;
              _this3.bulkingRemove = false;
              return _context4.finish(7);
            case 10:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[3,, 7, 10]]);
      }))();
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_5__.mapGetters)(["project", "globalUsersSelected", "users", "globalUsersParamExists", "fields", "globalUsersPage", "globalUsersOptions", "globalUsersCount", "usersParams", "globalUsersParamsUrl", "usersSortOrder", "usersSortBy"])), {}, {
    /**
     * Check if there are
     * selected users
     */
    bulkDisabled: function bulkDisabled() {
      return this.globalUsersSelected.length == 0;
    },
    /**
     * Check if
     * all selected users
     * is processed
     */
    selectedUsersProcessed: function selectedUsersProcessed() {
      var _this4 = this;
      return this.users.filter(function (p) {
        return _this4.globalUsersSelected.indexOf(p.id) >= 0 && p.processed_at;
      }).length == this.globalUsersSelected.length;
    },
    /**
     * Check if
     * all selected users
     * is not processed
     */
    selectedUsersNotProcessed: function selectedUsersNotProcessed() {
      var _this5 = this;
      return this.users.filter(function (p) {
        return _this5.globalUsersSelected.indexOf(p.id) >= 0 && !p.processed_at;
      }).length == this.globalUsersSelected.length;
    },
    /**
     * Check if
     * there are activated filters
     */
    filtersActivated: function filtersActivated() {
      return this.globalUsersParamExists() || this.usersSortOrder || this.usersSortBy;
    },
    count: {
      get: function get() {
        return this.globalUsersCount;
      },
      set: function () {
        var _set = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5(value) {
          return _regeneratorRuntime().wrap(function _callee5$(_context5) {
            while (1) switch (_context5.prev = _context5.next) {
              case 0:
                _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_3__.SET_GLOBAL_USERS_COUNT, value);
                this.settingUsersCount = true;
                _context5.prev = 2;
                _context5.next = 5;
                return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_GLOBAL_USERS);
              case 5:
                _context5.prev = 5;
                this.settingUsersCount = false;
                return _context5.finish(5);
              case 8:
              case "end":
                return _context5.stop();
            }
          }, _callee5, this, [[2,, 5, 8]]);
        }));
        function set(_x) {
          return _set.apply(this, arguments);
        }
        return set;
      }()
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_slide__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/slide */ "./resources/js/actions/slide.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _cell_DefaultCell_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./cell/DefaultCell.vue */ "./resources/js/components/global/user/table/cell/DefaultCell.vue");
/* harmony import */ var _cell_MetaCell_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./cell/MetaCell.vue */ "./resources/js/components/global/user/table/cell/MetaCell.vue");
/* harmony import */ var _cell_RelationCell_vue__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./cell/RelationCell.vue */ "./resources/js/components/global/user/table/cell/RelationCell.vue");
/* harmony import */ var _cell_StreetCell_vue__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./cell/StreetCell.vue */ "./resources/js/components/global/user/table/cell/StreetCell.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }



// Actions



// Components




/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    DefaultCell: _cell_DefaultCell_vue__WEBPACK_IMPORTED_MODULE_3__["default"],
    MetaCell: _cell_MetaCell_vue__WEBPACK_IMPORTED_MODULE_4__["default"],
    RelationCell: _cell_RelationCell_vue__WEBPACK_IMPORTED_MODULE_5__["default"],
    StreetCell: _cell_StreetCell_vue__WEBPACK_IMPORTED_MODULE_6__["default"]
  },
  props: {
    /**
     * HTML tag
     * Default <td>
     * When the column is fixed
     * we use <div> as tag
     */
    tag: {
      type: String,
      "default": "td"
    },
    /**
     * Column
     */
    column: {
      type: Array
    },
    /**
     * User
     */
    user: {
      type: Object
    }
  },
  methods: {
    /**
     * Manage user project
     * See: @/components/global/user/project/Slide.vue
     */
    manageProjects: function manageProjects() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_2__.SET_GLOBAL_USER, this.user);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_1__.OPEN_SLIDE, "user-manage-projects");
    },
    showProject: function showProject(project) {
      this.$router.push({
        name: "prospect",
        params: {
          project: project.slug
        }
      });
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_7__.mapGetters)(["globalUsersParamExists"])), {}, {
    /**
     * Define the width of the column
     */
    style: function style() {
      var style = {};
      if (this.column.width) {
        style.maxWidth = "".concat(this.column.width, "px");
        style.minWidth = "".concat(this.column.width, "px");
      }
      return style;
    },
    /**
     * Check if column is filtered
     */
    isFiltered: function isFiltered() {
      // By calendars
      if (this.column.category == "calendars") {
        return this.globalUsersParamExists("withCalendars") || this.globalUsersParamExists("withCalendars");
        // By categories
      } else if (this.column.category == "categories") {
        return this.globalUsersParamExists("withCategories") || this.globalUsersParamExists("withCategories");
        // By documents
      } else if (this.column.category == "documents") {
        return this.globalUsersParamExists("withDocuments") || this.globalUsersParamExists("withDocuments");
        // By folders
      } else if (this.column.category == "folders") {
        return this.globalUsersParamExists("withFolders") || this.globalUsersParamExists("withFolders");
        // By groups
      } else if (this.column.category == "groups") {
        return this.globalUsersParamExists("withGroups") || this.globalUsersParamExists("withGroups");
        // By menus
      } else if (this.column.category == "menus") {
        return this.globalUsersParamExists("withMenus") || this.globalUsersParamExists("withMenus");
        // By order actions
      } else if (this.column.category == "order_actions") {
        return this.globalUsersParamExists("withOrderActions") || this.globalUsersParamExists("withoutUsers");
        // By order steps
      } else if (this.column.category == "order_steps") {
        return this.globalUsersParamExists("withOrderSteps") || this.globalUsersParamExists("withOrderSteps");
        // By roles
      } else if (this.column.category == "roles") {
        return this.globalUsersParamExists("withRoles") || this.globalUsersParamExists("withRoles");
        // By threads
      } else if (this.column.category == "threads") {
        return this.globalUsersParamExists("withThreads") || this.globalUsersParamExists("withThreads");
        // By users
      } else if (this.column.category == "users") {
        return this.globalUsersParamExists("withUsers") || this.globalUsersParamExists("withUsers");
        // By meta fields
      } else if (this.column.category == "meta") {
        return this.globalUsersParamExists("meta_" + this.column.id);
      }

      // By default field
      return this.globalUsersParamExists("field_" + this.column.key);
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _composables_mouse__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/composables/mouse */ "./resources/js/composables/mouse.js");
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_slide__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/slide */ "./resources/js/actions/slide.js");
/* harmony import */ var _actions_user_setting__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/user/setting */ "./resources/js/actions/user/setting.js");
/* harmony import */ var _header_cell_DefaultHeaderCell_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./header-cell/DefaultHeaderCell.vue */ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue");
/* harmony import */ var _header_cell_RelationHeaderCell_vue__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./header-cell/RelationHeaderCell.vue */ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }




// Actions



// Components


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    DefaultHeaderCell: _header_cell_DefaultHeaderCell_vue__WEBPACK_IMPORTED_MODULE_4__["default"],
    RelationHeaderCell: _header_cell_RelationHeaderCell_vue__WEBPACK_IMPORTED_MODULE_5__["default"]
  },
  props: {
    /**
     * HTML tag
     * Default <th>
     * When the column is fixed
     * we use <div> as tag
     */
    tag: {
      type: String,
      "default": "th"
    },
    /**
     * Column
     */
    column: {
      type: Array
    }
  },
  data: function data() {
    return {
      /**
       * Resize handler
       */
      resize: null,
      initialWidth: null,
      currentWidth: null
    };
  },
  mounted: function mounted() {
    var resize = this.$refs.resize;
    this.resize = (0,_composables_mouse__WEBPACK_IMPORTED_MODULE_0__.useDrag)(resize);
  },
  methods: {
    /**
     * Current column offet width
     */
    getCurrentWidth: function getCurrentWidth() {
      return this.$refs.resizable.offsetWidth;
    },
    /**
     * Remove the column
     * from the users table
     */
    removeColumn: function removeColumn() {
      var newSettings = this.userSettingsUsersTable;
      newSettings.splice(this.column.index, 1);
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
        key: "global-users-table",
        value: newSettings
      });
    },
    /**
     * Pin or do not pin the column
     */
    togglePinColumn: function togglePinColumn() {
      var settings = this.userSettingsUsersTable;
      var count = settings.filter(function (c) {
        return c.fixed;
      }).length;
      var index = this.column.index;
      var newSettings;

      // Do not fix the column
      if (this.column.fixed) {
        delete settings[index].fixed;
        // Put the column
        // At the start
        // of the list of none fixed columns
        newSettings = [].concat(_toConsumableArray(settings.slice(0, index)), _toConsumableArray(settings.slice(index + 1, count)), [settings[index]], _toConsumableArray(settings.slice(count)));
        // Fix the column
      } else {
        settings[index].fixed = true;
        // Put the column
        // At the end
        // of the list of fixed columns
        newSettings = [].concat(_toConsumableArray(settings.slice(0, count)), [settings[index]], _toConsumableArray(settings.slice(count, index)), _toConsumableArray(settings.slice(index + 1)));
      }

      // Update user users table setting
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
        key: "global-users-table",
        value: newSettings
      });
    },
    /**
     * Auto width column
     */
    autoWidth: function autoWidth() {
      this.$refs.resize.style.left = "100%";
      var newSettings = this.userSettingsUsersTable;
      var index = this.column.index;
      if (newSettings[index].width !== undefined) {
        delete newSettings[index].width;
        _store__WEBPACK_IMPORTED_MODULE_1__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
          key: "global-users-table",
          value: newSettings
        });
      }
    },
    /**
     * When we finally resize the column width
     * update user propsects table setting
     */
    change: function change() {
      var newSettings = this.userSettingsUsersTable;
      var index = this.column.index;
      newSettings[index].width = this.currentWidth;
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
        key: "global-users-table",
        value: newSettings
      });
    },
    /**
     * Filter by project
     * See: @/components/global/user/filters/project/Slide.vue
     */
    filterProject: function filterProject() {
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].commit(_actions_slide__WEBPACK_IMPORTED_MODULE_2__.OPEN_SLIDE, "global-users-table-filter-project");
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_6__.mapGetters)(["userSettingsUsersTable", "globalUsersParamExists"])), {}, {
    /**
     */
    resizeDragging: function resizeDragging() {
      return this.resize && this.resize.dragging;
    },
    /**
     */
    resizePosition: function resizePosition() {
      if (!this.resize || !this.resize.position) {
        return null;
      }
      return this.resize.position;
    },
    /**
     * Define the width of the column
     */
    style: function style() {
      var style = {};
      if (this.column.width) {
        style.maxWidth = "".concat(this.column.width, "px");
        style.minWidth = "".concat(this.column.width, "px");
      }
      return style;
    },
    /**
     * Check if column is filtered
     */
    isFiltered: function isFiltered() {
      // By calendars
      if (this.column.category == "calendars") {
        return this.globalUsersParamExists("withCalendars") || this.globalUsersParamExists("withCalendars");
        // By categories
      } else if (this.column.category == "categories") {
        return this.globalUsersParamExists("withCategories") || this.globalUsersParamExists("withCategories");
        // By documents
      } else if (this.column.category == "documents") {
        return this.globalUsersParamExists("withDocuments") || this.globalUsersParamExists("withDocuments");
        // By folders
      } else if (this.column.category == "folders") {
        return this.globalUsersParamExists("withFolders") || this.globalUsersParamExists("withFolders");
        // By groups
      } else if (this.column.category == "groups") {
        return this.globalUsersParamExists("withGroups") || this.globalUsersParamExists("withGroups");
        // By menus
      } else if (this.column.category == "menus") {
        return this.globalUsersParamExists("withMenus") || this.globalUsersParamExists("withMenus");
        // By order actions
      } else if (this.column.category == "order_actions") {
        return this.globalUsersParamExists("withOrderActions") || this.globalUsersParamExists("withoutUsers");
        // By order steps
      } else if (this.column.category == "order_steps") {
        return this.globalUsersParamExists("withOrderSteps") || this.globalUsersParamExists("withOrderSteps");
        // By roles
      } else if (this.column.category == "roles") {
        return this.globalUsersParamExists("withRoles") || this.globalUsersParamExists("withRoles");
        // By threads
      } else if (this.column.category == "threads") {
        return this.globalUsersParamExists("withThreads") || this.globalUsersParamExists("withThreads");
        // By users
      } else if (this.column.category == "users") {
        return this.globalUsersParamExists("withUsers") || this.globalUsersParamExists("withUsers");
        // By meta fields
      } else if (this.column.category == "meta") {
        return this.globalUsersParamExists("meta_" + this.column.id);
      }

      // By default field
      return this.globalUsersParamExists("field_" + this.column.key);
    }
  }),
  watch: {
    /**
     *
     */
    resizeDragging: function resizeDragging(newValue) {
      if (newValue) {
        this.initialWidth = this.getCurrentWidth();
      } else {
        if (this.initialWidth !== null && this.initialWidth != this.currentWidth) {
          this.change();
        }
        this.initialWidth = null;
      }
    },
    /**
     */
    resizePosition: function resizePosition(newValue) {
      if (!newValue || this.initialWidth === null) {
        return;
      }
      var deltaX = this.resize.position.x - this.resize.origin.x;
      this.currentWidth = Math.max(60, deltaX + this.initialWidth);
      this.$refs.resize.style.left = "".concat(this.currentWidth, "px");
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _CardMenus_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CardMenus.vue */ "./resources/js/components/global/user/table/CardMenus.vue");
/* harmony import */ var _Table_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Table.vue */ "./resources/js/components/global/user/table/Table.vue");
/* harmony import */ var _project_Slide__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../project/Slide */ "./resources/js/components/global/user/project/Slide.vue");
/* harmony import */ var _filters_project_Slide__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../filters/project/Slide */ "./resources/js/components/global/user/filters/project/Slide.vue");
/* harmony import */ var _modals_add_column_Modal_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modals/add-column/Modal.vue */ "./resources/js/components/global/user/table/modals/add-column/Modal.vue");





/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    CardMenus: _CardMenus_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    UserTable: _Table_vue__WEBPACK_IMPORTED_MODULE_1__["default"],
    UserProjectSlide: _project_Slide__WEBPACK_IMPORTED_MODULE_2__["default"],
    UserFilterProjectSlide: _filters_project_Slide__WEBPACK_IMPORTED_MODULE_3__["default"],
    AddColumnModal: _modals_add_column_Modal_vue__WEBPACK_IMPORTED_MODULE_4__["default"]
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _Cell_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Cell.vue */ "./resources/js/components/global/user/table/Cell.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }



// Actions



// Components

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    Cell: _Cell_vue__WEBPACK_IMPORTED_MODULE_2__["default"]
  },
  props: {
    /**
     * User
     */
    user: {
      type: Object
    },
    /**
     * List of fixed columns
     */
    fixedColumns: {
      type: Array
    },
    /**
     * List of none fixed columns
     */
    notFixedColumns: {
      type: Array
    },
    /**
     * Index of the row in the users table
     * we need it when we use SHIFT
     * to select multiple users
     * See: toggleSelectedUser
     */
    index: {
      type: Number
    }
  },
  methods: {
    /**
     * Handle SHIFT
     * when cliicking checkbox
     *
     * @param {*} event
     */
    toggleSelectedUser: function toggleSelectedUser(event) {
      var index = this.index;
      var shift = event.shiftKey;
      var checked = event.currentTarget.checked;
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.TOGGLE_GLOBAL_SELECTED_USERS, {
        index: index,
        shift: shift,
        checked: checked
      });
    },
    /**
     * Go to the user profile
     */
    showUser: function showUser() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.SET_GLOBAL_USER, this.user);
      this.$router.push({
        name: "global.user.show",
        params: {
          user: this.user.id
        }
      });
    }
  },
  computed: _objectSpread({
    /**
     * Select a row
     */
    selected: {
      get: function get() {
        return this.globalUsersSelected;
      },
      set: function set(value) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_SELECTED_USERS, value);
      }
    }
  }, (0,vuex__WEBPACK_IMPORTED_MODULE_3__.mapGetters)(["project", "globalUsersSelected", "globalUsersParamExists"]))
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _actions_slide__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/slide */ "./resources/js/actions/slide.js");
/* harmony import */ var _actions_user_setting__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/user/setting */ "./resources/js/actions/user/setting.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _Row_vue__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Row.vue */ "./resources/js/components/global/user/table/Row.vue");
/* harmony import */ var _HeaderCell_vue__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./HeaderCell.vue */ "./resources/js/components/global/user/table/HeaderCell.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }



// Actions





// Components


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    Row: _Row_vue__WEBPACK_IMPORTED_MODULE_5__["default"],
    HeaderCell: _HeaderCell_vue__WEBPACK_IMPORTED_MODULE_6__["default"]
  },
  data: function data() {
    return {
      loading: false,
      fields: [{
        slug: "name",
        name: "Nom",
        meta: false
      }, {
        slug: "last_name",
        name: "Prénom",
        meta: false
      }, {
        slug: "email",
        name: "Email",
        meta: false
      }, {
        slug: "street",
        name: "Rue",
        meta: false
      }, {
        slug: "street_bis",
        name: "Rue 2",
        meta: false
      }, {
        slug: "postal_code",
        name: "CP",
        meta: false
      }, {
        slug: "city",
        name: "Ville",
        meta: false
      }, {
        slug: "country",
        name: "Pays",
        meta: false
      }, {
        slug: "ip_city",
        name: "IP Ville",
        meta: false
      }, {
        slug: "ip_country",
        name: "IP Pays",
        meta: false
      }, {
        slug: "ip_state",
        name: "IP Région",
        meta: false
      }, {
        slug: "ip_postal_code",
        name: "IP Code postal",
        meta: false
      }, {
        slug: "ip_latitude",
        name: "IP Latitude",
        meta: false
      }, {
        slug: "ip_longitude",
        name: "IP Longitude",
        meta: false
      }, {
        slug: "last_activity",
        name: "Dernière activité",
        meta: false
      }, {
        slug: "ip_address",
        name: "Adresse IP",
        meta: false
      }]
    };
  },
  created: function created() {
    this.updateParamsFromUrl();
    this.getColumns();
    this.getUsers();
  },
  methods: {
    /**
     * Update params from URL
     */
    updateParamsFromUrl: function updateParamsFromUrl() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.INIT_GLOBAL_USER_PARAMS);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_FIELDS, null);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_SORT_BY, "id");
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_SORT_ORDER, "desc");
      var url = new URL(window.location.href);
      var searchParams = new URLSearchParams(url.search);
      var filters = {};
      var globalUsersCount = 50;
      searchParams.forEach(function (value, param) {
        if (param == "page") {
          _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_PAGE, parseInt(value));
        } else if (param == "count") {
          globalUsersCount = parseInt(value);
        } else if (param == "fields") {
          _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_FIELDS, value);
        } else if (param == "sortOrder") {
          _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_SORT_ORDER, value);
        } else if (param == "sortBy") {
          _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_SORT_BY, value);
        } else if (param == "filters") {
          try {
            value = JSON.parse(value);
            for (var key in value) {
              filters[key] = value[key];
            }
          } catch (e) {}
        }
      });
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USER_PARAMS, filters);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.SET_GLOBAL_USERS_COUNT, globalUsersCount);
    },
    /**
     * Get user columns
     */
    getColumns: function getColumns() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.GET_USER_SETTING, "global-users-table");
    },
    /**
     * Get project fields
     */
    getProjectFields: function getProjectFields() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(FETCH_FIELDS);
    },
    /**
     * Get project categories
     */
    getProjectCategories: function getProjectCategories() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(FETCH_CATEGORIES);
    },
    /**
     * Get project users
     */
    getUsers: function getUsers() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this.loading = true;
              _context.prev = 1;
              _context.next = 4;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_4__.FETCH_GLOBAL_USERS);
            case 4:
              _context.prev = 4;
              _this.loading = false;
              return _context.finish(4);
            case 7:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1,, 4, 7]]);
      }))();
    },
    /**
     *
     * @param {*} e
     */
    fixedColumnMoved: function fixedColumnMoved(e) {
      this.columnMoved(e, true);
    },
    /**
     *
     * @param {*} e
     */
    notFixedColumnMoved: function notFixedColumnMoved(e) {
      this.columnMoved(e, false);
    },
    /**
     *
     * @param {*} e
     * @param {*} fixed
     */
    columnMoved: function columnMoved(e, fixed) {
      var settings = this.userSettingsUsersTable;
      var newSettings;
      var oldIndex = fixed ? this.columns.filter(function (c) {
        return c.fixed;
      })[e.oldDraggableIndex].index : this.columns.filter(function (c) {
        return !c.fixed;
      })[e.oldDraggableIndex].index;
      var newIndex = fixed ? this.columns.filter(function (c) {
        return c.fixed;
      })[e.newDraggableIndex].index : this.columns.filter(function (c) {
        return !c.fixed;
      })[e.newDraggableIndex].index;
      if (oldIndex < newIndex) {
        newSettings = [].concat(_toConsumableArray(settings.slice(0, oldIndex)), _toConsumableArray(settings.slice(oldIndex + 1, newIndex + 1)), [settings[oldIndex]], _toConsumableArray(settings.slice(newIndex + 1)));
      } else if (oldIndex > newIndex) {
        newSettings = [].concat(_toConsumableArray(settings.slice(0, newIndex)), [settings[oldIndex]], _toConsumableArray(settings.slice(newIndex, oldIndex)), _toConsumableArray(settings.slice(oldIndex + 1)));
      }
      if (newSettings) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
          key: "global-users-table",
          value: newSettings
        });
      }
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_7__.mapGetters)(["userSettingsUsersTable", "globalUsers", "globalUsersSelected", "globalUsersOptions", "globalUsersParamExists", "globalUsersParamsUrl", "globalUsersPage", "globalUsersCount"])), {}, {
    /**
     *
     */
    selectAll: {
      get: function get() {
        return this.globalUsers.length == this.globalUsersSelected.length;
      },
      set: function set(value) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_4__.UPDATE_GLOBAL_SELECTED_USERS, value ? this.globalUsers.map(function (p) {
          return p.id;
        }) : []);
      }
    },
    /**
     * Columns
     */
    columns: function columns() {
      var _this2 = this;
      return this.userSettingsUsersTable.map(function (column, index) {
        var key = column.key;
        var type = "list";
        var category = key;
        var id = key;
        var name;

        // Project
        if (key == "projects") {
          name = _this2.$t("user.table.column.others.projects");
          // Meta field
        } else if (key == "last-project") {
          name = _this2.$t("user.table.column.others.last_project");
          // Meta field
        } else {
          var slug = key;
          var field = _this2.fields.find(function (field) {
            return (
              /*field.for == "user" &&
              !field.meta &&*/
              field.slug == slug
            );
          });
          if (!field) {
            return null;
          }
          name = field.name;
          type = field.type;
          category = "default";
        }
        return _objectSpread(_objectSpread({}, column), {}, {
          name: name,
          type: type,
          index: index,
          id: id,
          category: category
        });
      }).filter(function (column) {
        return column;
      });
    },
    /**
     *
     */
    fixedColumns: function fixedColumns() {
      return this.columns.filter(function (c) {
        return c.fixed && !c.hidden;
      });
    },
    /**
     *
     */
    notFixedColumns: function notFixedColumns() {
      return this.columns.filter(function (c) {
        return !c.fixed && !c.hidden;
      });
    }
  }),
  watch: {
    globalUsersParamsUrl: function globalUsersParamsUrl(newValue) {
      var url = "?page=" + this.globalUsersPage + "&count=" + this.globalUsersCount;
      if (newValue) {
        url += "&filters=" + newValue;
      }
      history.pushState(null, null, url);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Project
     */
    user: {
      type: Object
    },
    /**
     * Field
     */
    field: {
      type: String
    }
  },
  methods: {
    /**
     * Select input content
     *
     * @param {*} e
     */
    select: function select(e) {
      // e.target.select();
    },
    /**
     * When we tap ENTER
     * we focus the next input
     */
    focusNextInput: function focusNextInput() {
      // Get list of inputs in the document
      var currentInput = document.activeElement;
      var inputs = _toConsumableArray(document.getElementsByTagName("input"));

      // Get the next input
      var currentInputIndex = inputs.indexOf(currentInput);
      var nextInputIndex = (currentInputIndex + 1) % inputs.length;
      var input = inputs[nextInputIndex];

      // Focus the next input
      if (input) {
        input.focus();
      }
    },
    /**
     */
    focusPreviousProjectInput: function focusPreviousProjectInput() {
      // Get list of inputs in the document
      var currentInput = document.activeElement;
      var parentNode = currentInput;
      while (parentNode && parentNode.tagName !== "TR") {
        parentNode = parentNode.parentNode;
      }

      // Get the next input
      var previousParentNode = parentNode.previousElementSibling;
      if (previousParentNode) {
        var inputs = _toConsumableArray(parentNode.getElementsByTagName("input"));
        var prevInputs = _toConsumableArray(previousParentNode.getElementsByTagName("input"));
        var currentInputIndex = inputs.indexOf(currentInput);
        var input = prevInputs[currentInputIndex];

        // Focus the next input
        if (input) {
          input.focus();
        }
      }
    },
    /**
     */
    focusNextProjectInput: function focusNextProjectInput() {
      // Get list of inputs in the document
      var currentInput = document.activeElement;
      var parentNode = currentInput;
      while (parentNode && parentNode.tagName !== "TR") {
        parentNode = parentNode.parentNode;
      }

      // Get the next input
      var nextParentNode = parentNode.nextElementSibling;
      if (nextParentNode) {
        var inputs = _toConsumableArray(parentNode.getElementsByTagName("input"));
        var nextInputs = _toConsumableArray(nextParentNode.getElementsByTagName("input"));
        var currentInputIndex = inputs.indexOf(currentInput);
        var input = nextInputs[currentInputIndex];

        // Focus the next input
        if (input) {
          input.focus();
        }
      }
    }
  },
  computed: {
    /**
     * When value is updated
     * Send modification to the API
     */
    value: {
      get: function get() {
        return this.user[this.field];
      },
      set: function set(newValue) {
        // Modification payload
        var update = _defineProperty({
          id: this.user.id
        }, this.field, newValue);

        // If the user is a new user
        // that doesn't have yet an ID
        // we simply locally update field
        // The modification will be stacked
        // and will be sent lately
        // after we retrieve the real ID of the prospect
        if (this.user.id <= 0) {
          _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_USER, update);
          return;
        }

        // else
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_USER, update);
      }
    },
    /**
     * Do not allow user
     * to edit some field
     * or on certain conditions applied to the user
     */
    disabled: function disabled() {
      return false;
    },
    /**
     * Label that will be shown
     * instead of raw value
     * for some type of field
     */
    label: function label() {
      // created_at and updated_at field
      // as human readable fields
      if (this.field == "created_at" || this.field == "updated_at" || this.field == "last_activity") {
        var date = dayjs(new Date(this.value)).format("DD/MM/YYYY HH:mm:ss");
        if (date == "Invalid date") {
          return this.value;
        }
        return date;
      }
      return this.value;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Project
     */
    USER: {
      type: Object
    },
    /**
     * Field name
     */
    field: {
      type: String
    },
    /**
     * Field type
     */
    type: {
      type: String,
      "default": "text"
    }
  },
  methods: {
    /**
     * Select input content
     *
     * @param {*} e
     */
    select: function select(e) {
      // e.target.select();
    },
    /**
     * When we tap ENTER
     * we focus the next input
     */
    focusNextInput: function focusNextInput() {
      // Get list of inputs in the document
      var inputs = _toConsumableArray(document.getElementsByTagName("input"));

      // Get the next input
      var currentInput = document.activeElement;
      var currentInputIndex = inputs.indexOf(currentInput);
      var nextInputIndex = (currentInputIndex + 1) % inputs.length;
      var input = inputs[nextInputIndex];

      // Focus the next input
      if (input) {
        input.focus();
      }
    },
    /**
     */
    focusPreviousProjectInput: function focusPreviousProjectInput() {
      // Get list of inputs in the document
      var currentInput = document.activeElement;
      var parentNode = currentInput;
      while (parentNode && parentNode.tagName !== "TR") {
        parentNode = parentNode.parentNode;
      }

      // Get the next input
      var previousParentNode = parentNode.previousElementSibling;
      if (previousParentNode) {
        var inputs = _toConsumableArray(parentNode.getElementsByTagName("input"));
        var prevInputs = _toConsumableArray(previousParentNode.getElementsByTagName("input"));
        var currentInputIndex = inputs.indexOf(currentInput);
        var input = prevInputs[currentInputIndex];

        // Focus the next input
        if (input) {
          input.focus();
        }
      }
    },
    /**
     */
    focusNextProjectInput: function focusNextProjectInput() {
      // Get list of inputs in the document
      var currentInput = document.activeElement;
      var parentNode = currentInput;
      while (parentNode && parentNode.tagName !== "TR") {
        parentNode = parentNode.parentNode;
      }

      // Get the next input
      var nextParentNode = parentNode.nextElementSibling;
      if (nextParentNode) {
        var inputs = _toConsumableArray(parentNode.getElementsByTagName("input"));
        var nextInputs = _toConsumableArray(nextParentNode.getElementsByTagName("input"));
        var currentInputIndex = inputs.indexOf(currentInput);
        var input = nextInputs[currentInputIndex];

        // Focus the next input
        if (input) {
          input.focus();
        }
      }
    },
    /**
     * @param {*} e
     */
    autoHeight: function autoHeight(e) {
      e.target.style.height = "auto";
      e.target.style.height = "".concat(e.target.scrollHeight, "px");
    },
    /**
     * @param {*} e
     */
    initHeight: function initHeight(e) {
      e.target.style.height = "100%";
    },
    /**
     * Update address
     * @param {*} e
     */
    updateAddress: function updateAddress(address) {
      this.value = address.place.formatted_address;
    }
  },
  computed: {
    /**
     * When value is updated
     * Send modification to the API
     */
    value: function value() {
      return this.USER.meta ? this.USER.meta[this.field] : "";
    },
    /**
     * Do not allow user
     * to edit
     * on certain conditions applied to the USER
     */
    disabled: function disabled() {
      return true;
    },
    /**
     * Label that will be shown
     * instead of raw value
     * for some type of field
     */
    label: function label() {
      // For date field
      if (this.type == "date" && this.value) {
        var date = dayjs(new Date(this.value)).format("DD/MM/YYYY");
        if (date == "Invalid date") {
          return this.value;
        }
        return date;

        // For datetime field
      } else if (this.type == "datetime" && this.value) {
        var _date = dayjs(new Date(this.value)).format("DD/MM/YYYY HH:mm:ss");
        if (_date == "Invalid date") {
          return this.value;
        }
        return _date;
      }
      return this.value;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * User
     */
    user: {
      type: Object
    },
    /**
     * User asscoaited items
     * (labels, groups, users, ...)
     */
    items: {
      type: Array
    },
    /**
     * Name field of each item
     */
    name: {
      type: String,
      "default": "name"
    },
    /**
     * Color field of each item
     */
    color: {
      type: String,
      "default": "color"
    },
    /**
     * Bg color field of each item
     */
    bgcolor: {
      type: String,
      "default": "bgcolor"
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Project
     */
    user: {
      type: Object
    },
    /**
     * Field name (street)
     */
    field: {
      type: String
    }
  },
  data: function data() {
    return {
      updatingAddress: false
    };
  },
  methods: {
    /**
     * Select input content
     *
     * @param {*} e
     */
    select: function select(e) {
      // e.target.select();
    },
    /**
     * Update user address
     * @param {*} e
     */
    updateAddress: function updateAddress(e) {
      this.updatingAddress = true;

      // Mark the user address
      // as valid
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_USER, {
        id: this.user.id,
        valid_address: true
      });

      // Update all user fields
      // related to address
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_USER, {
        id: this.user.id,
        // address fields
        street: e.street,
        postal_code: e.postal_code,
        city: e.city,
        country: e.country,
        state: e.state,
        county: e.county,
        // Lat and Lng
        latitude: e.latitude,
        longitude: e.longitude
      });
    }
  },
  computed: {
    /**
     * When value is updated
     * Send modification to the API
     */
    value: {
      get: function get() {
        return this.user[this.field];
      },
      set: function set(newValue) {
        var _this = this;
        // We wait
        // to check if value was updated
        // from google map autocomplete
        // because this event is triggered
        // before the google map autocomplete event
        // (to do: replace it with better solution)
        setTimeout(function () {
          if (_this.updatingAddress) {
            _this.updatingAddress = false;
          } else {
            _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.UPDATE_GLOBAL_USER, _defineProperty({
              id: _this.user.id
            }, _this.field, newValue));
          }
        }, 500);
      }
    },
    /**
     * Do not allow user
     * to edit
     * on certain conditions applied to the user
     */
    disabled: function disabled() {
      return false;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _actions_project_field__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/project/field */ "./resources/js/actions/project/field.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }





/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Column
     */
    column: {
      type: Object
    }
  },
  data: function data() {
    return {
      showOptions: false
    };
  },
  methods: {
    /**
     * Sort users list by
     * the current field
     */
    sortBy: function sortBy() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.SET_GLOBAL_USERS_SORT_ORDER, _this.sortedAsc ? "desc" : "asc");
            case 2:
              _context.next = 4;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.SET_GLOBAL_USERS_SORT_BY, _this.column.key);
            case 4:
              // Update users list
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.FETCH_GLOBAL_USERS);
            case 5:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    /**
     * Perform a users search
     * from the current field
     * using the entered keyword
     * @param {*} e
     */
    search: function search(e) {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var value;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              value = e.target.value; // Perform search when keyword is given
              if (!value) {
                _context2.next = 6;
                break;
              }
              _context2.next = 4;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.ADD_GLOBAL_USER_PARAMS, {
                key: _this2.searchKey,
                value: value,
                multiple: false
              });
            case 4:
              _context2.next = 8;
              break;
            case 6:
              _context2.next = 8;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_user__WEBPACK_IMPORTED_MODULE_1__.REMOVE_GLOBAL_USER_PARAMS, {
                key: _this2.searchKey
              });
            case 8:
              // Update users list
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.FETCH_GLOBAL_USERS);
            case 9:
            case "end":
              return _context2.stop();
          }
        }, _callee2);
      }))();
    },
    /**
     * Edit current field
     */
    edit: function edit() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_2__.OPEN_MODAL, "field-update");
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_project_field__WEBPACK_IMPORTED_MODULE_3__.SET_FIELD, this.field);
    },
    /**
     */
    hideOptions: function hideOptions() {
      var _this3 = this;
      setTimeout(function () {
        _this3.showOptions = false;
      }, 200);
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_4__.mapGetters)(["usersParams", "fields", "globalUsersSortOrder", "globalUsersSortBy"])), {}, {
    /**
     * Check if current users list
     * is sorted by the current field
     */
    sortedBy: function sortedBy() {
      return this.globalUsersSortBy == this.column.key;
    },
    /**
     * Get sort order
     */
    sortedAsc: function sortedAsc() {
      return this.globalUsersSortOrder == "asc";
    },
    /**
     * Gest the user param
     * for the current field search
     */
    searchKey: function searchKey() {
      return this.column.category == "meta" ? "meta_" + this.column.id : "field_" + this.column.id;
    },
    /**
     * Current field
     */
    field: function field() {
      var _this4 = this;
      return this.fields.find(function (f) {
        return f["for"] == "user" && (_this4.column.category == "meta" ? f.meta && f.slug == _this4.column.id : !f.meta && f.slug == _this4.column.id);
      });
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _actions_project_category__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/project/category */ "./resources/js/actions/project/category.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }




/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Column
     */
    column: {
      type: Object
    }
  },
  computed: _objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_3__.mapGetters)(["globalUsersParamExists"]))
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _actions_project_user__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/project/user */ "./resources/js/actions/project/user.js");
/* harmony import */ var _actions_user_setting__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/user/setting */ "./resources/js/actions/user/setting.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }





/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    /**
     * Column
     */
    column: {
      type: Object
    }
  },
  methods: {
    /**
     * Add thread as column
     * to the users table
     * for the current user
     */
    addOrRemove: function addOrRemove() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (_this.existingColumn && !_this.existingColumn.hidden) {
                _this.removeColumn();
              } else {
                _this.addColumn();
              }
            case 1:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    /**
     * Add column
     * to the users table
     * for the current user
     */
    addColumn: function addColumn() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var newSettings, index;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              // Get user users table settings
              newSettings = _this2.userSettingsUsersTable;
              index = newSettings.findIndex(function (column) {
                return column.key === _this2.column.columnId;
              });
              if (index >= 0) {
                delete newSettings[index].hidden;
              } else {
                // Add new column
                newSettings.push({
                  key: _this2.column.columnId
                });
              }
              _context2.prev = 3;
              _context2.next = 6;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings
              });
            case 6:
              // And refresh users list
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_project_user__WEBPACK_IMPORTED_MODULE_2__.FETCH_USERS);
              _context2.next = 9;
              return _this2.$nextTick();
            case 9:
              _this2.scrollToCorrespondingColumn();
            case 10:
              _context2.prev = 10;
              return _context2.finish(10);
            case 12:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[3,, 10, 12]]);
      }))();
    },
    /**
     * Remove column
     * from the users table
     * for the current user
     */
    removeColumn: function removeColumn() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var newSettings;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              // Get user users table settings
              newSettings = _this3.userSettingsUsersTable.map(function (column) {
                return column.key === _this3.column.columnId ? _objectSpread(_objectSpread({}, column), {}, {
                  hidden: true
                }) : column;
              }); // Update setting
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings
              });
            case 2:
            case "end":
              return _context3.stop();
          }
        }, _callee3);
      }))();
    },
    /**
     * Pin or do not pin the column
     */
    togglePinColumn: function togglePinColumn() {
      var settings = this.userSettingsUsersTable;
      var count = settings.filter(function (c) {
        return c.fixed;
      }).length;
      var index = this.column.index;
      var newSettings;

      // Do not fix the column
      if (this.column.fixed) {
        delete settings[index].fixed;
        // Put the column
        // At the start
        // of the list of none fixed columns
        newSettings = [].concat(_toConsumableArray(settings.slice(0, index)), _toConsumableArray(settings.slice(index + 1, count)), [settings[index]], _toConsumableArray(settings.slice(count)));
        // Fix the column
      } else {
        settings[index].fixed = true;
        // Put the column
        // At the end
        // of the list of fixed columns
        newSettings = [].concat(_toConsumableArray(settings.slice(0, count)), [settings[index]], _toConsumableArray(settings.slice(count, index)), _toConsumableArray(settings.slice(index + 1)));
      }

      // Update user users table setting
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_3__.UPDATE_USER_SETTING, {
        key: "global-users-table",
        value: newSettings
      });
    },
    /**
     * When we're inside the users table
     * Let scroll to the column
     * corresponding to the first filter
     * from the menu
     */
    scrollToCorrespondingColumn: function scrollToCorrespondingColumn() {
      var header = document.getElementById(this.column.headerId);
      if (!header) {
        return;
      }
      header.scrollIntoView();
    },
    /**
     *
     */
    closeModal: function closeModal() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_1__.CLOSE_MODAL);
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_4__.mapGetters)(["userSettingsUsersTable", "userSettingsUsersTableHas"])), {}, {
    existingColumn: function existingColumn() {
      var _this4 = this;
      return this.userSettingsUsersTable.find(function (c) {
        return c.key == _this4.column.columnId;
      });
    },
    existing: function existing() {
      return this.userSettingsUsersTableHas(this.column.columnId);
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _actions_user__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/actions/user */ "./resources/js/actions/user.js");
/* harmony import */ var _actions_user_setting__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/user/setting */ "./resources/js/actions/user/setting.js");
/* harmony import */ var _ColumnRow_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ColumnRow.vue */ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }



// Actions



// Components

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    ColumnRow: _ColumnRow_vue__WEBPACK_IMPORTED_MODULE_3__["default"]
  },
  data: function data() {
    return {
      frameTab: 0,
      // keyword search
      keyword: "",
      movable: deviceType() == "desktop",
      settingUsersTable: false
    };
  },
  methods: {
    setUsersTableSetting: function setUsersTableSetting(userImport) {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var settings;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this.settingUsersTable = true;
              _context.prev = 1;
              _context.next = 4;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(SHOW_IMPORT, userImport.id);
            case 4:
              userImport = _context.sent;
              settings = userImport.mapping.filter(function (s) {
                return s;
              }).map(function (s) {
                return {
                  key: s
                };
              });
              _context.next = 8;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_2__.UPDATE_USER_SETTING, {
                key: "users-table",
                value: settings
              });
            case 8:
              _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user__WEBPACK_IMPORTED_MODULE_1__.FETCH_GLOBAL_USERS);
            case 9:
              _context.prev = 9;
              _this.settingUsersTable = false;
              return _context.finish(9);
            case 12:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1,, 9, 12]]);
      }))();
    },
    /**
     *
     * @param {*} e
     */
    fixedColumnMoved: function fixedColumnMoved(e) {
      this.columnMoved(e, true);
    },
    /**
     *
     * @param {*} e
     */
    notFixedColumnMoved: function notFixedColumnMoved(e) {
      this.columnMoved(e, false);
    },
    /**
     *
     * @param {*} e
     * @param {*} fixed
     */
    columnMoved: function columnMoved(e, fixed) {
      var _this2 = this;
      var settings = this.userSettingsUsersTable.filter(function (c) {
        return _this2.columns.find(function (column) {
          return column.columnId == c.key;
        });
      });
      var newSettings;
      var oldIndex = fixed ? this.availableColumns.filter(function (c) {
        return c.fixed;
      })[e.oldDraggableIndex].index : this.availableColumns.filter(function (c) {
        return !c.fixed;
      })[e.oldDraggableIndex].index;
      var newIndex = fixed ? this.availableColumns.filter(function (c) {
        return c.fixed;
      })[e.newDraggableIndex].index : this.availableColumns.filter(function (c) {
        return !c.fixed;
      })[e.newDraggableIndex].index;
      if (oldIndex < newIndex) {
        newSettings = [].concat(_toConsumableArray(settings.slice(0, oldIndex)), _toConsumableArray(settings.slice(oldIndex + 1, newIndex + 1)), [settings[oldIndex]], _toConsumableArray(settings.slice(newIndex + 1)));
      } else if (oldIndex > newIndex) {
        newSettings = [].concat(_toConsumableArray(settings.slice(0, newIndex)), [settings[oldIndex]], _toConsumableArray(settings.slice(newIndex, oldIndex)), _toConsumableArray(settings.slice(oldIndex + 1)));
      }
      if (newSettings) {
        _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_user_setting__WEBPACK_IMPORTED_MODULE_2__.UPDATE_USER_SETTING, {
          key: "users-table",
          value: newSettings
        });
      }
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_4__.mapGetters)(["userSettingsUsersTable", "userSettingsUsersTableHas"])), {}, {
    columns: function columns() {
      var _this3 = this;
      return [
      // Projects
      {
        type: "other",
        name: "Projects",
        icon: "fa fa-columns icon-purple",
        columnId: "projects",
        headerId: "hc-users-table-header-projects-projects"
      },
      // Last project
      {
        type: "other",
        name: "Connecté sur le project",
        icon: "fa fa-columns icon-purple",
        columnId: "last-project",
        headerId: "hc-users-table-header-last-project-last-project"
      }].concat(_toConsumableArray([{
        slug: "name",
        name: "Prénom"
      }, {
        slug: "last_name",
        name: "Nom"
      }, {
        slug: "email",
        name: "Email"
      }, {
        slug: "street",
        name: "Rue"
      }, {
        slug: "street_bis",
        name: "Rue 2"
      }, {
        slug: "postal_code",
        name: "Code postal"
      }, {
        key: "city",
        name: "Ville"
      }, {
        slug: "country",
        name: "Pays"
      }, {
        slug: "ip_address",
        name: "Adresse IP"
      }, {
        slug: "ip_city",
        name: "IP Ville"
      }, {
        slug: "ip_postal_code",
        name: "IP Code postal"
      }, {
        slug: "ip_state",
        name: "IP Région"
      }, {
        slug: "ip_country",
        name: "IP Pays"
      }, {
        slug: "ip_latitude",
        name: "IP Latitude"
      }, {
        slug: "ip_longitude",
        name: "IP Longitude"
      }, {
        slug: "last_activity",
        name: "Dernière activité"
      }].map(function (field) {
        return {
          type: "default-field",
          name: field.name,
          icon: "fa fa-columns",
          columnId: field.slug,
          headerId: "hc-users-table-header-default-" + field.slug
        };
      }))).map(function (column, i) {
        var index = _this3.userSettingsUsersTable.findIndex(function (c) {
          return c.key == column.columnId;
        });
        var c = _this3.userSettingsUsersTable.find(function (c) {
          return c.key == column.columnId;
        });
        return _objectSpread(_objectSpread({}, column), {}, {
          index: index >= 0 ? index : 1000 + i,
          fixed: c ? c.fixed ? true : false : null
        });
      }).sort(function (column1, column2) {
        return !column1.fixed && column2.fixed ? 1 : column1.fixed && !column2.fixed ? -1 : column1.index > column2.index ? 1 : -1;
      }).map(function (column, index) {
        return _objectSpread(_objectSpread({}, column), {}, {
          index: index
        });
      });
    },
    /**
     *
     */
    availableColumns: function availableColumns() {
      var keyword = removeStringAccent(this.keyword);
      return this.columns.filter(function (column) {
        return removeStringAccent(column.name).indexOf(keyword) >= 0;
      });
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_icon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("icon");
  var _component_checkbox = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("checkbox");
  var _component_item = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_item, {
    tag: "label"
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": "fa fa-tag",
        style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($options.style)
      }, null, 8 /* PROPS */, ["style"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
        "class": "hc-item-main-content",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.project.name)
      }, null, 8 /* PROPS */, _hoisted_1), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        tag: "a",
        "class": "fa fa-thumbs-down",
        style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($options.excludeStyle),
        title: _ctx.$t('user.table.filters.project.without_project', {
          project: $props.project.name
        }),
        onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)($options.toggleExclude, ["prevent"])
      }, null, 8 /* PROPS */, ["style", "title", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_checkbox, {
        "model-value": $options.isChecked,
        onChange: $options.change
      }, null, 8 /* PROPS */, ["model-value", "onChange"])];
    }),
    _: 1 /* STABLE */
  });
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-flex-column",
  style: {
    "height": "100%"
  }
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_search = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("search");
  var _component_project_row = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("project-row");
  var _component_item_list = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item-list");
  var _component_slide = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("slide", true);
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_slide, {
    name: $data.name,
    title: _ctx.$t('user.project.title'),
    icon: "fa fa-file",
    style: {
      "width": "260px"
    }
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_search, {
        modelValue: $data.projectKeyword,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return $data.projectKeyword = $event;
        })
      }, null, 8 /* PROPS */, ["modelValue"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item_list, {
        "class": "hc-flex-1",
        padding: "5px"
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(_ctx.projects, function (project) {
            return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_project_row, {
              key: project.id,
              project: project
            }, null, 8 /* PROPS */, ["project"]);
          }), 128 /* KEYED_FRAGMENT */))];
        }),
        _: 1 /* STABLE */
      })])];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["name", "title"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["src", "alt"];
var _hoisted_2 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_icon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("icon");
  var _component_checkbox = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("checkbox");
  var _component_item = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_item, {
    tag: "label"
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, null, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("img", {
            src: $props.project.thumbnail ? $props.project.thumbnail : '/images/logo.png',
            alt: $props.project.name
          }, null, 8 /* PROPS */, _hoisted_1)];
        }),
        _: 1 /* STABLE */
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
        "class": "hc-item-main-content",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.project.name)
      }, null, 8 /* PROPS */, _hoisted_2), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_checkbox, {
        "model-value": $props.value,
        onChange: $options.change
      }, null, 8 /* PROPS */, ["model-value", "onChange"])];
    }),
    _: 1 /* STABLE */
  });
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0 ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-flex-column",
  style: {
    "height": "100%"
  }
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_search = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("search");
  var _component_project_row = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("project-row");
  var _component_item_list = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item-list");
  var _component_slide = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("slide", true);
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_slide, {
    name: $data.name,
    title: _ctx.$t('user.project.title'),
    icon: "fa fa-file",
    style: {
      "width": "260px"
    },
    onOpen: _cache[1] || (_cache[1] = function ($event) {
      return $options.fetchProjects();
    }),
    onClose: _cache[2] || (_cache[2] = function ($event) {
      return $data.projectCancelTokenSource && $data.projectCancelTokenSource.cancel();
    })
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_search, {
        modelValue: $data.projectKeyword,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return $data.projectKeyword = $event;
        })
      }, null, 8 /* PROPS */, ["modelValue"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item_list, {
        "class": "hc-flex-1",
        padding: "5px"
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.projects, function (project) {
            return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_project_row, {
              key: project.id,
              project: project,
              value: $options.isProjectChecked(project)
            }, null, 8 /* PROPS */, ["project", "value"]);
          }), 128 /* KEYED_FRAGMENT */))];
        }),
        _: 1 /* STABLE */
      })])];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["name", "title"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660 ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-users-table-footer-select"
};
var _hoisted_2 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "50"
}, "50", -1 /* HOISTED */);
var _hoisted_3 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "100"
}, "100", -1 /* HOISTED */);
var _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "200"
}, "200", -1 /* HOISTED */);
var _hoisted_5 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "300"
}, "300", -1 /* HOISTED */);
var _hoisted_6 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "400"
}, "400", -1 /* HOISTED */);
var _hoisted_7 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
  value: "500"
}, "500", -1 /* HOISTED */);
var _hoisted_8 = [_hoisted_2, _hoisted_3, _hoisted_4, _hoisted_5, _hoisted_6, _hoisted_7];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_card_menu = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("card-menu");
  var _component_loading = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("loading");
  var _component_card_menu_list = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("card-menu-list");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_card_menu_list, null, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [$options.bulkDisabled ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 0
      }, [$options.filtersActivated ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_card_menu, {
        key: 0,
        icon: "fa fa-filter",
        label: _ctx.$t('user.table.footer.init_filters'),
        title: "Réinitialiser les filtres",
        style: {
          "background-color": "#e6effd"
        },
        color: "#1a73e8",
        onClick: $options.initParams
      }, null, 8 /* PROPS */, ["label", "onClick"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-ellipsis-h",
        label: _ctx.$t('user.table.footer.toggle_user_menus'),
        onClick: $options.toggleUsersOptions
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return $options.count = $event;
        })
      }, _hoisted_8, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelSelect, $options.count]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
        loading: $data.settingUsersCount
      }, null, 8 /* PROPS */, ["loading"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-plus",
        label: _ctx.$t('user.table.footer.add_user'),
        onClick: $options.addUser,
        color: "#12a0f3"
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-table",
        label: _ctx.$t('user.table.footer.add_column'),
        onClick: $options.addColumn
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        disabled: $data.fetchingPreviousPage || _ctx.globalUsersPage <= 1,
        icon: "fa fa-caret-left",
        label: _ctx.$t('user.table.footer.previous'),
        onClick: $options.previousPage
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
            loading: $data.fetchingPreviousPage
          }, null, 8 /* PROPS */, ["loading"])];
        }),
        _: 1 /* STABLE */
      }, 8 /* PROPS */, ["disabled", "label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        disabled: $data.fetchingNextPage || _ctx.users.length < _ctx.globalUsersCount,
        icon: "fa fa-caret-right",
        label: _ctx.$t('user.table.footer.next'),
        onClick: $options.nextPage
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
            loading: $data.fetchingNextPage
          }, null, 8 /* PROPS */, ["loading"])];
        }),
        _: 1 /* STABLE */
      }, 8 /* PROPS */, ["disabled", "label", "onClick"])], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 1
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-check-square",
        label: _ctx.$t('user.table.footer.deselect'),
        onClick: $options.bulkDeselect
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-trash",
        label: _ctx.$t('user.table.footer.bulk_delete'),
        color: "#d9402c",
        onClick: $options.bulkRemove
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
            loading: $data.bulkingRemove
          }, null, 8 /* PROPS */, ["loading"])];
        }),
        _: 1 /* STABLE */
      }, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-calendar",
        label: _ctx.$t('user.table.footer.bulk_calendars'),
        onClick: $options.bulkCalendar
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-tags",
        label: _ctx.$t('user.table.footer.bulk_categories'),
        onClick: $options.bulkCategory
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-file-pdf",
        label: _ctx.$t('user.table.footer.bulk_documents'),
        onClick: $options.bulkDocument
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-folder",
        label: _ctx.$t('user.table.footer.bulk_folders'),
        onClick: $options.bulkFolder
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-users",
        label: _ctx.$t('user.table.footer.bulk_groups'),
        onClick: $options.bulkGroup
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-list",
        label: _ctx.$t('user.table.footer.bulk_menus'),
        onClick: $options.bulkMenu
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-person-digging",
        label: _ctx.$t('user.table.footer.bulk_order_actions'),
        onClick: $options.bulkOrderAction
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-step-forward",
        label: _ctx.$t('user.table.footer.bulk_order_steps'),
        onClick: $options.bulkOrderStep
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-user-tie",
        label: _ctx.$t('user.table.footer.bulk_roles'),
        onClick: $options.bulkRole
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-envelope",
        label: _ctx.$t('user.table.footer.bulk_threads'),
        onClick: $options.bulkThread
      }, null, 8 /* PROPS */, ["label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menu, {
        icon: "fa fa-user-plus",
        label: _ctx.$t('user.table.footer.bulk_users'),
        onClick: $options.bulkUser
      }, null, 8 /* PROPS */, ["label", "onClick"])], 64 /* STABLE_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("card-menu icon=\"fa fa-search\" :label=\"$t('user.table.footer.search_and_replace')\" /")];
    }),
    _: 1 /* STABLE */
  });
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06":
/*!********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06 ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_relation_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("relation-cell");
  var _component_meta_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("meta-cell");
  var _component_street_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("street-cell");
  var _component_default_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("default-cell");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)((0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveDynamicComponent)($props.tag), {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([$options.isFiltered ? 'filtered' : '']),
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($options.style)
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [$props.column.category == 'projects' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_relation_cell, {
        key: 0,
        user: $props.user,
        items: $props.user.projects ? $props.user.projects : [],
        onClick: $options.manageProjects
      }, null, 8 /* PROPS */, ["user", "items", "onClick"])) : $props.column.category == 'last-project' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_relation_cell, {
        key: 1,
        user: $props.user,
        items: $props.user.last_project ? [$props.user.last_project] : [],
        onItemClicked: $options.showProject
      }, null, 8 /* PROPS */, ["user", "items", "onItemClicked"])) : $props.column.category == 'meta' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_meta_cell, {
        key: 2,
        user: $props.user,
        field: $props.column.id,
        type: $props.column.type
      }, null, 8 /* PROPS */, ["user", "field", "type"])) : $props.column.key == 'street' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_street_cell, {
        key: 3,
        user: $props.user,
        field: $props.column.key
      }, null, 8 /* PROPS */, ["user", "field"])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_default_cell, {
        key: 4,
        user: $props.user,
        field: $props.column.key
      }, null, 8 /* PROPS */, ["user", "field"]))];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["class", "style"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_relation_header_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("relation-header-cell");
  var _component_default_header_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("default-header-cell");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)((0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveDynamicComponent)($props.tag), {
    ref: "resizable",
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([$options.resizeDragging ? 'hc-header-cell-resizing' : '', $options.isFiltered ? 'filtered' : '']),
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($options.style)
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Projects "), $props.column.category == 'projects' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_relation_header_cell, {
        key: 0,
        column: $props.column,
        onClick: $options.filterProject
      }, null, 8 /* PROPS */, ["column", "onClick"])) : $props.column.category == 'last-project' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 1
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Last project "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_relation_header_cell, {
        column: $props.column
      }, null, 8 /* PROPS */, ["column"])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : $props.column.category == 'meta' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 2
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Meta field "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_default_header_cell, {
        column: $props.column
      }, null, 8 /* PROPS */, ["column"])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 3
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Default field "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_default_header_cell, {
        column: $props.column
      }, null, 8 /* PROPS */, ["column"])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("div class=\"hc-header-cell-options\""), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Remove column "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("span\n                class=\"hc-header-cell-remove fa fa-times\"\n                @click.stop=\"removeColumn\"\n                title=\"Enlever cette colonne du tableau\"\n            ></span"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Pin column "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("span\n                class=\"hc-header-cell-pin fa fa-thumbtack\"\n                @click.stop=\"togglePinColumn\"\n                title=\"Figer cette colonne\"\n            ></!--span>\n        </div-"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Resize column "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
        "class": "hc-header-cell-resize",
        ref: "resize",
        onDblclick: _cache[0] || (_cache[0] = function () {
          return $options.autoWidth && $options.autoWidth.apply($options, arguments);
        })
      }, null, 544 /* HYDRATE_EVENTS, NEED_PATCH */)];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["class", "style"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264 ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  id: "users-table"
};
var _hoisted_2 = {
  id: "users-table-body"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_user_table = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("user-table");
  var _component_card_menus = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("card-menus");
  var _component_user_project_slide = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("user-project-slide");
  var _component_add_column_modal = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("add-column-modal");
  var _component_user_filter_project_slide = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("user-filter-project-slide");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_user_table)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_card_menus), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_user_project_slide), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_add_column_modal), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_user_filter_project_slide)]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "fixed"
};
var _hoisted_2 = {
  "class": "hc-table-selector"
};
var _hoisted_3 = ["value"];
var _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, null, -1 /* HOISTED */);
var _hoisted_5 = {
  "class": "hc-table-row-options"
};
var _hoisted_6 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-eye"
}, null, -1 /* HOISTED */);
var _hoisted_7 = [_hoisted_6];
var _hoisted_8 = {
  "class": "hc-table-fixed-cells"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("cell");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([$props.user.deleted_at ? 'deleted' : '', $props.user.processed_at ? 'processed' : ''])
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Select user "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "checkbox",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $options.selected = $event;
    }),
    value: $props.user.id,
    onClick: _cache[1] || (_cache[1] = function () {
      return $options.toggleSelectedUser && $options.toggleSelectedUser.apply($options, arguments);
    })
  }, null, 8 /* PROPS */, _hoisted_3), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelCheckbox, $options.selected]]), _hoisted_4]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_5, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" View user "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a", {
    "class": "hc-table-user-option-view",
    onClick: _cache[2] || (_cache[2] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.showUser && $options.showUser.apply($options, arguments);
    }, ["prevent"]))
  }, _hoisted_7)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" List of fixed columns "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_8, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.fixedColumns, function (column) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_cell, {
      tag: "div",
      key: column.key,
      user: $props.user,
      column: column
    }, null, 8 /* PROPS */, ["user", "column"]);
  }), 128 /* KEYED_FRAGMENT */))])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" List of none fixed columns "), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.notFixedColumns, function (column) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_cell, {
      key: column.key,
      user: $props.user,
      column: column
    }, null, 8 /* PROPS */, ["user", "column"]);
  }), 128 /* KEYED_FRAGMENT */))], 2 /* CLASS */);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  key: 0
};
var _hoisted_2 = {
  "class": "fixed"
};
var _hoisted_3 = {
  "class": "hc-table-selector"
};
var _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, null, -1 /* HOISTED */);
var _hoisted_5 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
  "class": "hc-table-row-options"
}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a")], -1 /* HOISTED */);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_header_cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("header-cell");
  var _component_draggable = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("draggable");
  var _component_row = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("row");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(['hc-table', _ctx.globalUsersOptions ? 'hc-table-show-options' : ''])
  }, [this.columns.length > 0 ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("table", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_draggable, {
    tag: "thead",
    list: $options.notFixedColumns,
    "item-key": "key",
    onEnd: $options.notFixedColumnMoved
  }, {
    header: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
        type: "checkbox",
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return $options.selectAll = $event;
        })
      }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelCheckbox, $options.selectAll]]), _hoisted_4]), _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_draggable, {
        tag: "div",
        list: $options.fixedColumns,
        "class": "hc-table-fixed-headers",
        "item-key": "key",
        onEnd: $options.fixedColumnMoved
      }, {
        item: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function (_ref) {
          var element = _ref.element;
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_header_cell, {
            tag: "div",
            column: element
          }, null, 8 /* PROPS */, ["column"])];
        }),
        _: 1 /* STABLE */
      }, 8 /* PROPS */, ["list", "onEnd"])])];
    }),
    item: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function (_ref2) {
      var element = _ref2.element;
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_header_cell, {
        column: element
      }, null, 8 /* PROPS */, ["column"])];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["list", "onEnd"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(_ctx.globalUsers, function (user, index) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_row, {
      key: user.id,
      index: index,
      user: user,
      "fixed-columns": $options.fixedColumns,
      "not-fixed-columns": $options.notFixedColumns
    }, null, 8 /* PROPS */, ["index", "user", "fixed-columns", "not-fixed-columns"]);
  }), 128 /* KEYED_FRAGMENT */))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("loading :loading=\"loading\" /")], 2 /* CLASS */);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  key: 0,
  "class": "hc-default-cell-label"
};
var _hoisted_2 = ["textContent"];
var _hoisted_3 = {
  key: 1,
  "class": "hc-default-cell-label"
};
var _hoisted_4 = ["disabled"];
var _hoisted_5 = ["textContent"];
var _hoisted_6 = {
  key: 2,
  "class": "hc-default-cell-label"
};
var _hoisted_7 = ["disabled"];
var _hoisted_8 = ["textContent"];
var _hoisted_9 = {
  key: 3,
  "class": "hc-default-cell-label"
};
var _hoisted_10 = ["textContent"];
var _hoisted_11 = {
  key: 4,
  "class": "hc-default-cell-label"
};
var _hoisted_12 = ["disabled"];
var _hoisted_13 = ["textContent"];
var _hoisted_14 = ["href"];
var _hoisted_15 = ["disabled"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return $props.field == 'created_at' || $props.field == 'updated_at' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_2)])) : $props.field == 'phone_number' || $props.field == 'mobile_phone_number' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "tel",
    "class": "hc-default-cell-input",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[1] || (_cache[1] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    disabled: $options.disabled,
    onKeydown: [_cache[2] || (_cache[2] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[3] || (_cache[3] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[4] || (_cache[4] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_4), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_5)])) : $props.field == 'email' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "email",
    "class": "hc-default-cell-input",
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[6] || (_cache[6] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    disabled: $options.disabled,
    onKeydown: [_cache[7] || (_cache[7] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[8] || (_cache[8] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[9] || (_cache[9] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_7), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_8)])) : $props.field == 'created_at' || $props.field == 'updated_at' || $props.field == 'last_activity' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_9, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_10)])) : $props.field == 'website_url' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_11, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "text",
    "class": "hc-default-cell-input",
    "onUpdate:modelValue": _cache[10] || (_cache[10] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[11] || (_cache[11] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    disabled: $options.disabled,
    onKeydown: [_cache[12] || (_cache[12] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[13] || (_cache[13] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[14] || (_cache[14] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_12), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_13), $options.value ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("a", {
    key: 0,
    target: "_blank",
    href: $options.value,
    "class": "fa fa-external-link"
  }, null, 8 /* PROPS */, _hoisted_14)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)(((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 5,
    "class": "hc-default-cell-input",
    "onUpdate:modelValue": _cache[15] || (_cache[15] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[16] || (_cache[16] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    disabled: $options.disabled,
    onKeydown: [_cache[17] || (_cache[17] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[18] || (_cache[18] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[19] || (_cache[19] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_15)), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  key: 0,
  "class": "hc-meta-cell-label"
};
var _hoisted_2 = ["type", "disabled"];
var _hoisted_3 = ["textContent"];
var _hoisted_4 = {
  key: 1,
  "class": "hc-meta-cell-label"
};
var _hoisted_5 = ["disabled"];
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["href"];
var _hoisted_8 = ["type", "disabled"];
var _hoisted_9 = ["disabled"];
var _hoisted_10 = ["disabled"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_google_map_input = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("google-map-input");
  return $props.type == 'date' || $props.type == 'email' || $props.type == 'tel' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    "class": "hc-meta-cell-input",
    type: $props.type,
    disabled: $options.disabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[1] || (_cache[1] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    onChange: _cache[2] || (_cache[2] = function () {
      return _ctx.update && _ctx.update.apply(_ctx, arguments);
    }),
    onKeydown: [_cache[3] || (_cache[3] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[4] || (_cache[4] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[5] || (_cache[5] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_2), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelDynamic, $options.value, void 0, {
    lazy: true
  }]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_3)])) : $props.type == 'url' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "text",
    "class": "hc-meta-cell-input",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return $options.value = $event;
    }),
    disabled: $options.disabled,
    onFocus: _cache[7] || (_cache[7] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    onKeydown: [_cache[8] || (_cache[8] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[9] || (_cache[9] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[10] || (_cache[10] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_5), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label)
  }, null, 8 /* PROPS */, _hoisted_6), $options.value ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("a", {
    key: 0,
    target: "_blank",
    href: $options.value,
    "class": "fa fa-external-link"
  }, null, 8 /* PROPS */, _hoisted_7)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : $props.type == 'text' || $props.type == 'number' ? (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)(((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 2,
    "class": "hc-meta-cell-input",
    type: $props.type,
    disabled: $options.disabled,
    "onUpdate:modelValue": _cache[11] || (_cache[11] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[12] || (_cache[12] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    onChange: _cache[13] || (_cache[13] = function () {
      return _ctx.update && _ctx.update.apply(_ctx, arguments);
    }),
    onKeydown: [_cache[14] || (_cache[14] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[15] || (_cache[15] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[16] || (_cache[16] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_8)), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelDynamic, $options.value, void 0, {
    lazy: true
  }]]) : $props.type == 'datetime' ? (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)(((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 3,
    type: "datetime-local",
    disabled: $options.disabled,
    "onUpdate:modelValue": _cache[17] || (_cache[17] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[18] || (_cache[18] = function () {
      return $options.select && $options.select.apply($options, arguments);
    }),
    onChange: _cache[19] || (_cache[19] = function () {
      return _ctx.update && _ctx.update.apply(_ctx, arguments);
    }),
    onKeydown: [_cache[20] || (_cache[20] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextInput && $options.focusNextInput.apply($options, arguments);
    }, ["enter"])), _cache[21] || (_cache[21] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusPreviousProjectInput && $options.focusPreviousProjectInput.apply($options, arguments);
    }, ["up"])), _cache[22] || (_cache[22] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function () {
      return $options.focusNextProjectInput && $options.focusNextProjectInput.apply($options, arguments);
    }, ["down"]))]
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_9)), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]) : $props.type == 'address' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_google_map_input, {
    key: 4,
    "class": "hc-meta-cell-input",
    disabled: $options.disabled,
    modelValue: $options.value,
    "onUpdate:modelValue": _cache[23] || (_cache[23] = function ($event) {
      return $options.value = $event;
    }),
    modelModifiers: {
      lazy: true
    },
    onChanged: $options.updateAddress,
    onKeydown: [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)($options.focusNextInput, ["enter"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)($options.focusPreviousProjectInput, ["up"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)($options.focusNextProjectInput, ["down"])]
  }, null, 8 /* PROPS */, ["disabled", "modelValue", "onChanged", "onKeydown"])) : $props.type == 'long_text' ? (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)(((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("textarea", {
    key: 5,
    "class": "hc-meta-cell-input",
    disabled: $options.disabled,
    "onUpdate:modelValue": _cache[24] || (_cache[24] = function ($event) {
      return $options.value = $event;
    }),
    onFocus: _cache[25] || (_cache[25] = function ($event) {
      return $options.select($event), $options.autoHeight($event);
    }),
    onBlur: _cache[26] || (_cache[26] = function ($event) {
      return $options.initHeight($event);
    }),
    onChange: _cache[27] || (_cache[27] = function () {
      return _ctx.update && _ctx.update.apply(_ctx, arguments);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_10)), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.value, void 0, {
    lazy: true
  }]]) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7 ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-relation-cell"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_tag = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("tag");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.items, function (item) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_tag, {
      key: item.id,
      text: item[$props.name],
      color: item[$props.color] ? item[$props.color] : '#333333',
      bgcolor: item[$props.bgcolor] ? item[$props.bgcolor] : '#EEEEEE',
      onClick: function onClick($event) {
        return _ctx.$emit('item-clicked', item);
      }
    }, null, 8 /* PROPS */, ["text", "color", "bgcolor", "onClick"]);
  }), 128 /* KEYED_FRAGMENT */))]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_google_map_input = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("google-map-input");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_google_map_input, {
    "class": "hc-default-cell-input",
    modelValue: $options.value,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $options.value = $event;
    }),
    onChanged: $options.updateAddress,
    disabled: $options.disabled,
    placeholder: ""
  }, null, 8 /* PROPS */, ["modelValue", "onChanged", "disabled"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, "Trier", -1 /* HOISTED */);
var _hoisted_2 = [_hoisted_1];
var _hoisted_3 = ["placeholder", "value"];
var _hoisted_4 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_tooltip = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveDirective)("tooltip");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(['hc-default-header-cell-label', $data.showOptions ? 'show-options' : ''])
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-default-header-cell-options",
    onClick: _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
      return $data.showOptions = !$data.showOptions;
    }, ["stop"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(['fa', $options.sortedBy && $options.sortedAsc ? 'fa-caret-down' : 'fa-caret-up']),
    onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.sortBy && $options.sortBy.apply($options, arguments);
    }, ["prevent"]))
  }, _hoisted_2, 2 /* CLASS */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    placeholder: $props.column.name + ' ...',
    onChange: _cache[2] || (_cache[2] = function () {
      return $options.search && $options.search.apply($options, arguments);
    }),
    value: _ctx.usersParams[$options.searchKey],
    onBlur: _cache[3] || (_cache[3] = function ($event) {
      return $options.hideOptions();
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.column.name)
  }, null, 8 /* PROPS */, _hoisted_4), [[_directive_tooltip, $props.column.name, void 0, {
    top: true
  }]])], 2 /* CLASS */);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048 ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-relation-header-cell-label"
};
var _hoisted_2 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
  "class": "hc-relation-header-cell-options"
}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "fa fa-filter"
})], -1 /* HOISTED */);
var _hoisted_3 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_tooltip = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveDirective)("tooltip");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_1, [_hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.column.name)
  }, null, 8 /* PROPS */, _hoisted_3), [[_directive_tooltip, $props.column.name, void 0, {
    top: true
  }]])]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_icon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("icon");
  var _component_item = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_item, {
    onClick: $options.addOrRemove
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)($props.column.icon),
        style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($props.column.iconColor ? {
          color: $props.column.iconColor
        } : {})
      }, null, 8 /* PROPS */, ["class", "style"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
        "class": "hc-item-main-content",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.column.name)
      }, null, 8 /* PROPS */, _hoisted_1), (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderSlot)(_ctx.$slots, "default"), $options.existingColumn && !$options.existingColumn.hidden ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 0
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)($options.existingColumn.fixed ? 'fa fa-thumbtack icon-blue' : 'fa fa-thumbtack icon-grey'),
        onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)($options.togglePinColumn, ["prevent", "stop"])
      }, null, 8 /* PROPS */, ["class", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        tag: "a",
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)('fa fa-eye icon-blue'),
        onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
          return $options.scrollToCorrespondingColumn(), $options.closeModal();
        }, ["prevent", "stop"]))
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": "fa fa-times icon-red"
      })], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_icon, {
        key: 1,
        "class": "fa fa-plus"
      }))];
    }),
    _: 3 /* FORWARDED */
  }, 8 /* PROPS */, ["onClick"]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75 ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-flex-column",
  style: {
    "height": "100%",
    "overflow": "hidden"
  }
};
var _hoisted_2 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
  "class": "hc-item-main-content hc-flex-column"
}, null, -1 /* HOISTED */);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_search = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("search");
  var _component_column_row = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("column-row");
  var _component_draggable = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("draggable");
  var _component_item_list = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item-list");
  var _component_icon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("icon");
  var _component_item = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item");
  var _component_modal = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("modal", true);
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_modal, {
    title: 'Paramètre des colonnes',
    name: "global-users-table-add-column",
    width: 380
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_search, {
        modelValue: $data.keyword,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return $data.keyword = $event;
        })
      }, null, 8 /* PROPS */, ["modelValue"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item_list, {
        "class": "hc-flex-1",
        style: {
          "overflow": "auto"
        }
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_draggable, {
            tag: "item-list",
            disabled: !$data.movable,
            list: $options.availableColumns.filter(function (c) {
              return c.fixed;
            }),
            "item-key": "columnId",
            style: {
              "border-bottom": "1px solid #1e6ee5",
              "height": "auto",
              "overflow": "visible"
            },
            onEnd: $options.fixedColumnMoved
          }, {
            item: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function (_ref) {
              var element = _ref.element;
              return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_column_row, {
                column: element
              }, null, 8 /* PROPS */, ["column"])];
            }),
            _: 1 /* STABLE */
          }, 8 /* PROPS */, ["disabled", "list", "onEnd"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_draggable, {
            tag: "item-list",
            disabled: !$data.movable,
            list: $options.availableColumns.filter(function (c) {
              return c.fixed === false;
            }),
            "item-key": "columnId",
            style: {
              "border-bottom": "1px solid #1e6ee5",
              "height": "auto",
              "overflow": "visible"
            },
            onEnd: $options.notFixedColumnMoved
          }, {
            item: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function (_ref2) {
              var element = _ref2.element;
              return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_column_row, {
                column: element
              }, null, 8 /* PROPS */, ["column"])];
            }),
            _: 1 /* STABLE */
          }, 8 /* PROPS */, ["disabled", "list", "onEnd"]), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.availableColumns.filter(function (c) {
            return c.fixed === null;
          }), function (element) {
            return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_column_row, {
              column: element
            }, null, 8 /* PROPS */, ["column"]);
          }), 256 /* UNKEYED_FRAGMENT */))];
        }),
        _: 1 /* STABLE */
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item, {
        style: {
          "border-bottom": "1px solid #eee"
        }
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [_hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Movable "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
            "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(['fa', 'fa-arrows', $data.movable ? 'icon-blue' : 'icon-grey']),
            onClick: _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
              return $data.movable = !$data.movable;
            }, ["prevent", "stop"]))
          }, null, 8 /* PROPS */, ["class"])];
        }),
        _: 1 /* STABLE */
      })])];
    }),
    _: 1 /* STABLE */
  });
}

/***/ }),

/***/ "./resources/js/composables/mouse.js":
/*!*******************************************!*\
  !*** ./resources/js/composables/mouse.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useDrag: () => (/* binding */ useDrag)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

function useDrag(element) {
  var event = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(null);
  var dragging = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(false);
  var origin = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(null);
  var position = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(null);
  function updateOrigin(ev) {
    origin.value = {
      x: ev.clientX,
      y: ev.clientY
    };
  }
  function updatePosition(ev) {
    event.value = ev;
    position.value = {
      x: ev.clientX,
      y: ev.clientY
    };
  }
  function mousedown(ev) {
    event.value = ev;
    dragging.value = true;
    updateOrigin(ev);
    updatePosition(ev);
    window.addEventListener("mousemove", mousemove);
    window.addEventListener("mouseup", mouseup);
    ev.stopPropagation();
    ev.preventDefault();
  }
  function touchstart(e) {
    e.stopPropagation();
    e.preventDefault();
    var ev = e.touches[0];
    event.value = ev;
    dragging.value = true;
    updateOrigin(ev);
    updatePosition(ev);
    window.addEventListener("touchmove", touchmove, {
      passive: false
    });
    window.addEventListener("touchend", touchend, {
      passive: false
    });
  }
  function mousemove(event) {
    event.stopPropagation();
    event.preventDefault();
    updatePosition(event);
  }
  function touchmove(event) {
    event.stopPropagation();
    event.preventDefault();
    updatePosition(event.touches[0]);
  }
  function mouseup(event) {
    dragging.value = false;
    window.removeEventListener("mousemove", mousemove);
    window.removeEventListener("mouseup", mouseup);
  }
  function touchend(event) {
    event.stopPropagation();
    event.preventDefault();
    dragging.value = false;
    window.removeEventListener("touchmove", touchmove);
    window.removeEventListener("touchend", touchend);
  }
  element.addEventListener("mousedown", mousedown);
  element.addEventListener("touchstart", touchstart, {
    passive: false
  });
  (0,vue__WEBPACK_IMPORTED_MODULE_0__.onUnmounted)(function () {
    element.removeEventListener("mousedown", mousedown);
    element.removeEventListener("touchstart", mousedown);
  });

  // expose managed state as return value
  return {
    event: event,
    dragging: dragging,
    origin: origin,
    position: position
  };
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-users-table-footer-select {\n    position: relative;\n    width: unset !important;\n    min-width: unset !important;\n}\n.hc-users-table-footer-select > select {\n    border: 1px solid #dddddd;\n    background: none;\n    padding: 3px 20px 3px 8px;\n    border-radius: 7px;\n    font-size: 12px;\n    -webkit-appearance: none;\n    -moz-appearance: none;\n    height: 40px;\n    width: auto;\n}\n.hc-users-table-footer-select:after {\n    content: \"\";\n    position: absolute;\n    right: 7px;\n    top: 17px;\n    width: 0;\n    height: 0;\n    border-radius: 2px;\n    border-top: 5px solid #555555;\n    border-left: 4px solid transparent;\n    border-right: 4px solid transparent;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n#users-table {\n    display: flex;\n    flex-direction: column;\n    width: 100%;\n    height: 100%;\n    border-top: 1px solid #c0c0c0;\n}\n#users-table-body {\n    display: flex;\n    flex-direction: row;\n    flex: 1;\n    width: 100%;\n    overflow: hidden;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_style_index_0_id_5d9b2660_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_style_index_0_id_5d9b2660_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_style_index_0_id_5d9b2660_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_style_index_0_id_87e9d264_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_style_index_0_id_87e9d264_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_style_index_0_id_87e9d264_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/global/user/filters/project/ProjectRow.vue":
/*!****************************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/ProjectRow.vue ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ProjectRow_vue_vue_type_template_id_0e99fa4e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ProjectRow.vue?vue&type=template&id=0e99fa4e */ "./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e");
/* harmony import */ var _ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ProjectRow.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ProjectRow_vue_vue_type_template_id_0e99fa4e__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/filters/project/ProjectRow.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/filters/project/Slide.vue":
/*!***********************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/Slide.vue ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Slide_vue_vue_type_template_id_ea2d3318__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Slide.vue?vue&type=template&id=ea2d3318 */ "./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318");
/* harmony import */ var _Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Slide.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Slide_vue_vue_type_template_id_ea2d3318__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/filters/project/Slide.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/project/ProjectRow.vue":
/*!********************************************************************!*\
  !*** ./resources/js/components/global/user/project/ProjectRow.vue ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ProjectRow_vue_vue_type_template_id_243fc0ba__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ProjectRow.vue?vue&type=template&id=243fc0ba */ "./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba");
/* harmony import */ var _ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ProjectRow.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ProjectRow_vue_vue_type_template_id_243fc0ba__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/project/ProjectRow.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/project/Slide.vue":
/*!***************************************************************!*\
  !*** ./resources/js/components/global/user/project/Slide.vue ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Slide_vue_vue_type_template_id_cb7766f0__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Slide.vue?vue&type=template&id=cb7766f0 */ "./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0");
/* harmony import */ var _Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Slide.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Slide_vue_vue_type_template_id_cb7766f0__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/project/Slide.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/CardMenus.vue":
/*!*****************************************************************!*\
  !*** ./resources/js/components/global/user/table/CardMenus.vue ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _CardMenus_vue_vue_type_template_id_5d9b2660__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CardMenus.vue?vue&type=template&id=5d9b2660 */ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660");
/* harmony import */ var _CardMenus_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CardMenus.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js");
/* harmony import */ var _CardMenus_vue_vue_type_style_index_0_id_5d9b2660_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css */ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_CardMenus_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_CardMenus_vue_vue_type_template_id_5d9b2660__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/CardMenus.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/Cell.vue":
/*!************************************************************!*\
  !*** ./resources/js/components/global/user/table/Cell.vue ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Cell_vue_vue_type_template_id_6540dc06__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Cell.vue?vue&type=template&id=6540dc06 */ "./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06");
/* harmony import */ var _Cell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Cell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Cell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Cell_vue_vue_type_template_id_6540dc06__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/Cell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/HeaderCell.vue":
/*!******************************************************************!*\
  !*** ./resources/js/components/global/user/table/HeaderCell.vue ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _HeaderCell_vue_vue_type_template_id_cba5669a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./HeaderCell.vue?vue&type=template&id=cba5669a */ "./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a");
/* harmony import */ var _HeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./HeaderCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_HeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_HeaderCell_vue_vue_type_template_id_cba5669a__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/HeaderCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/Layout.vue":
/*!**************************************************************!*\
  !*** ./resources/js/components/global/user/table/Layout.vue ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Layout_vue_vue_type_template_id_87e9d264__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Layout.vue?vue&type=template&id=87e9d264 */ "./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264");
/* harmony import */ var _Layout_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Layout.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js");
/* harmony import */ var _Layout_vue_vue_type_style_index_0_id_87e9d264_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css */ "./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Layout_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Layout_vue_vue_type_template_id_87e9d264__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/Layout.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/Row.vue":
/*!***********************************************************!*\
  !*** ./resources/js/components/global/user/table/Row.vue ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Row_vue_vue_type_template_id_70627ce6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Row.vue?vue&type=template&id=70627ce6 */ "./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6");
/* harmony import */ var _Row_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Row.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Row_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Row_vue_vue_type_template_id_70627ce6__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/Row.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/Table.vue":
/*!*************************************************************!*\
  !*** ./resources/js/components/global/user/table/Table.vue ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Table_vue_vue_type_template_id_6dc957da__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Table.vue?vue&type=template&id=6dc957da */ "./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da");
/* harmony import */ var _Table_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Table.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Table_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Table_vue_vue_type_template_id_6dc957da__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/Table.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/DefaultCell.vue":
/*!************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/DefaultCell.vue ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _DefaultCell_vue_vue_type_template_id_5d264f0c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DefaultCell.vue?vue&type=template&id=5d264f0c */ "./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c");
/* harmony import */ var _DefaultCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DefaultCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_DefaultCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_DefaultCell_vue_vue_type_template_id_5d264f0c__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/cell/DefaultCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/MetaCell.vue":
/*!*********************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/MetaCell.vue ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _MetaCell_vue_vue_type_template_id_1f033860__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MetaCell.vue?vue&type=template&id=1f033860 */ "./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860");
/* harmony import */ var _MetaCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MetaCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_MetaCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_MetaCell_vue_vue_type_template_id_1f033860__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/cell/MetaCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/RelationCell.vue":
/*!*************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/RelationCell.vue ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _RelationCell_vue_vue_type_template_id_1e202cd7__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RelationCell.vue?vue&type=template&id=1e202cd7 */ "./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7");
/* harmony import */ var _RelationCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RelationCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_RelationCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_RelationCell_vue_vue_type_template_id_1e202cd7__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/cell/RelationCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/StreetCell.vue":
/*!***********************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/StreetCell.vue ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StreetCell_vue_vue_type_template_id_69ad881e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./StreetCell.vue?vue&type=template&id=69ad881e */ "./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e");
/* harmony import */ var _StreetCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./StreetCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_StreetCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_StreetCell_vue_vue_type_template_id_69ad881e__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/cell/StreetCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _DefaultHeaderCell_vue_vue_type_template_id_57258c0f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DefaultHeaderCell.vue?vue&type=template&id=57258c0f */ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f");
/* harmony import */ var _DefaultHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DefaultHeaderCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_DefaultHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_DefaultHeaderCell_vue_vue_type_template_id_57258c0f__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _RelationHeaderCell_vue_vue_type_template_id_c6a46048__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RelationHeaderCell.vue?vue&type=template&id=c6a46048 */ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048");
/* harmony import */ var _RelationHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RelationHeaderCell.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_RelationHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_RelationHeaderCell_vue_vue_type_template_id_c6a46048__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue":
/*!***********************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ColumnRow_vue_vue_type_template_id_b5a2a428__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ColumnRow.vue?vue&type=template&id=b5a2a428 */ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428");
/* harmony import */ var _ColumnRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ColumnRow.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_ColumnRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ColumnRow_vue_vue_type_template_id_b5a2a428__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/modals/add-column/ColumnRow.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/Modal.vue":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/Modal.vue ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Modal_vue_vue_type_template_id_4a91ad75__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Modal.vue?vue&type=template&id=4a91ad75 */ "./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75");
/* harmony import */ var _Modal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Modal.vue?vue&type=script&lang=js */ "./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;
const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__["default"])(_Modal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Modal_vue_vue_type_template_id_4a91ad75__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/global/user/table/modals/add-column/Modal.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js":
/*!****************************************************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ProjectRow.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Slide.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ProjectRow.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Slide.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js":
/*!*****************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CardMenus.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js":
/*!************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Cell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Cell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Cell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js":
/*!******************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HeaderCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Layout.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js":
/*!***********************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Row_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Row_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Row.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Table_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Table_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Table.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js":
/*!************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js ***!
  \************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./DefaultCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js":
/*!*********************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetaCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetaCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetaCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js":
/*!*************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./RelationCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_StreetCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_StreetCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./StreetCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./DefaultHeaderCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationHeaderCell_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./RelationHeaderCell.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ColumnRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ColumnRow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ColumnRow.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js":
/*!*******************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Modal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Modal_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Modal.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e ***!
  \**********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_template_id_0e99fa4e__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_template_id_0e99fa4e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ProjectRow.vue?vue&type=template&id=0e99fa4e */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/ProjectRow.vue?vue&type=template&id=0e99fa4e");


/***/ }),

/***/ "./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318 ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_template_id_ea2d3318__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_template_id_ea2d3318__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Slide.vue?vue&type=template&id=ea2d3318 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/filters/project/Slide.vue?vue&type=template&id=ea2d3318");


/***/ }),

/***/ "./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_template_id_243fc0ba__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ProjectRow_vue_vue_type_template_id_243fc0ba__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ProjectRow.vue?vue&type=template&id=243fc0ba */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/ProjectRow.vue?vue&type=template&id=243fc0ba");


/***/ }),

/***/ "./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0":
/*!*********************************************************************************************!*\
  !*** ./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0 ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_template_id_cb7766f0__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Slide_vue_vue_type_template_id_cb7766f0__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Slide.vue?vue&type=template&id=cb7766f0 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/project/Slide.vue?vue&type=template&id=cb7766f0");


/***/ }),

/***/ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660 ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_template_id_5d9b2660__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_template_id_5d9b2660__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CardMenus.vue?vue&type=template&id=5d9b2660 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=template&id=5d9b2660");


/***/ }),

/***/ "./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06":
/*!******************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06 ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Cell_vue_vue_type_template_id_6540dc06__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Cell_vue_vue_type_template_id_6540dc06__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Cell.vue?vue&type=template&id=6540dc06 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Cell.vue?vue&type=template&id=6540dc06");


/***/ }),

/***/ "./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a":
/*!************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a ***!
  \************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HeaderCell_vue_vue_type_template_id_cba5669a__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HeaderCell_vue_vue_type_template_id_cba5669a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HeaderCell.vue?vue&type=template&id=cba5669a */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/HeaderCell.vue?vue&type=template&id=cba5669a");


/***/ }),

/***/ "./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264 ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_template_id_87e9d264__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_template_id_87e9d264__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Layout.vue?vue&type=template&id=87e9d264 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=template&id=87e9d264");


/***/ }),

/***/ "./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6":
/*!*****************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6 ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Row_vue_vue_type_template_id_70627ce6__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Row_vue_vue_type_template_id_70627ce6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Row.vue?vue&type=template&id=70627ce6 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Row.vue?vue&type=template&id=70627ce6");


/***/ }),

/***/ "./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Table_vue_vue_type_template_id_6dc957da__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Table_vue_vue_type_template_id_6dc957da__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Table.vue?vue&type=template&id=6dc957da */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Table.vue?vue&type=template&id=6dc957da");


/***/ }),

/***/ "./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c":
/*!******************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultCell_vue_vue_type_template_id_5d264f0c__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultCell_vue_vue_type_template_id_5d264f0c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./DefaultCell.vue?vue&type=template&id=5d264f0c */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/DefaultCell.vue?vue&type=template&id=5d264f0c");


/***/ }),

/***/ "./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860":
/*!***************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860 ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetaCell_vue_vue_type_template_id_1f033860__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetaCell_vue_vue_type_template_id_1f033860__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetaCell.vue?vue&type=template&id=1f033860 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/MetaCell.vue?vue&type=template&id=1f033860");


/***/ }),

/***/ "./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7":
/*!*******************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7 ***!
  \*******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationCell_vue_vue_type_template_id_1e202cd7__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationCell_vue_vue_type_template_id_1e202cd7__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./RelationCell.vue?vue&type=template&id=1e202cd7 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/RelationCell.vue?vue&type=template&id=1e202cd7");


/***/ }),

/***/ "./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_StreetCell_vue_vue_type_template_id_69ad881e__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_StreetCell_vue_vue_type_template_id_69ad881e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./StreetCell.vue?vue&type=template&id=69ad881e */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/cell/StreetCell.vue?vue&type=template&id=69ad881e");


/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f":
/*!*******************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f ***!
  \*******************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultHeaderCell_vue_vue_type_template_id_57258c0f__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_DefaultHeaderCell_vue_vue_type_template_id_57258c0f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./DefaultHeaderCell.vue?vue&type=template&id=57258c0f */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/DefaultHeaderCell.vue?vue&type=template&id=57258c0f");


/***/ }),

/***/ "./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048":
/*!********************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048 ***!
  \********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationHeaderCell_vue_vue_type_template_id_c6a46048__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_RelationHeaderCell_vue_vue_type_template_id_c6a46048__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./RelationHeaderCell.vue?vue&type=template&id=c6a46048 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/header-cell/RelationHeaderCell.vue?vue&type=template&id=c6a46048");


/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428":
/*!*****************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428 ***!
  \*****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ColumnRow_vue_vue_type_template_id_b5a2a428__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ColumnRow_vue_vue_type_template_id_b5a2a428__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ColumnRow.vue?vue&type=template&id=b5a2a428 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/ColumnRow.vue?vue&type=template&id=b5a2a428");


/***/ }),

/***/ "./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75 ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Modal_vue_vue_type_template_id_4a91ad75__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Modal_vue_vue_type_template_id_4a91ad75__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Modal.vue?vue&type=template&id=4a91ad75 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/modals/add-column/Modal.vue?vue&type=template&id=4a91ad75");


/***/ }),

/***/ "./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CardMenus_vue_vue_type_style_index_0_id_5d9b2660_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/style-loader/dist/cjs.js!../../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/CardMenus.vue?vue&type=style&index=0&id=5d9b2660&lang=css");


/***/ }),

/***/ "./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css ***!
  \**********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Layout_vue_vue_type_style_index_0_id_87e9d264_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/style-loader/dist/cjs.js!../../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/global/user/table/Layout.vue?vue&type=style&index=0&id=87e9d264&lang=css");


/***/ })

}]);