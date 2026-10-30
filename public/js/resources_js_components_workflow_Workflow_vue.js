"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_workflow_Workflow_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _FieldInput_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./FieldInput.vue */ "./resources/js/components/workflow/FieldInput.vue");
/* harmony import */ var _HelpTip_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./HelpTip.vue */ "./resources/js/components/workflow/HelpTip.vue");
/* harmony import */ var _utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/utils/workflow-conditions */ "./resources/js/utils/workflow-conditions.js");
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



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "ConditionGroup",
  components: {
    FieldInput: _FieldInput_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    HelpTip: _HelpTip_vue__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  props: {
    // { op: "and" | "or", items: [ { filters } | { check, ... } | { op, items } ] }
    modelValue: {
      type: Object,
      required: true
    },
    // prospect | order | event: the criteria depend on it
    subject: {
      type: String,
      "default": "prospect"
    },
    catalog: {
      type: Object,
      required: true
    },
    readOnly: {
      type: Boolean,
      "default": false
    },
    depth: {
      type: Number,
      "default": 1
    }
  },
  emits: ["update:modelValue", "remove"],
  computed: {
    items: function items() {
      return this.modelValue.items || [];
    },
    filters: function filters() {
      return (0,_utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_2__.filtersFor)(this.catalog, this.subject);
    }
  },
  methods: {
    update: function update(patch) {
      this.$emit("update:modelValue", _objectSpread(_objectSpread({}, this.modelValue), {}, {
        items: this.items
      }, patch));
    },
    setItem: function setItem(index, item) {
      this.update({
        items: this.items.map(function (current, i) {
          return i === index ? item : current;
        })
      });
    },
    removeItem: function removeItem(index) {
      this.update({
        items: this.items.filter(function (_, i) {
          return i !== index;
        })
      });
    },
    add: function add(item) {
      this.update({
        items: [].concat(_toConsumableArray(this.items), [item])
      });
    },
    filterName: function filterName(item) {
      return Object.keys(item.filters || {})[0];
    },
    known: function known(name) {
      return this.filters.some(function (filter) {
        return filter.name === name;
      });
    },
    filterType: function filterType(item) {
      var _this = this;
      return (this.filters.find(function (filter) {
        return filter.name === _this.filterName(item);
      }) || {}).type;
    },
    newFilter: function newFilter(name) {
      return (0,_utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_2__.newFilterItem)(this.catalog, this.subject, name);
    },
    newCheck: function newCheck(name) {
      return (0,_utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_2__.newCheckItem)(name);
    },
    checkFields: function checkFields(name) {
      return (this.catalog.conditions.checks.find(function (check) {
        return check.name === name;
      }) || {}).fields || [];
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/utils/workflow-i18n */ "./resources/js/utils/workflow-i18n.js");
/* harmony import */ var _utils_workflow_format__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/utils/workflow-format */ "./resources/js/utils/workflow-format.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    // An execution of the follow-up API, with its steps
    execution: {
      type: Object,
      required: true
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_2__.mapGetters)(["project"])), {}, {
    waiting: function waiting() {
      return (0,_utils_workflow_format__WEBPACK_IMPORTED_MODULE_1__.waitingKind)(this.execution);
    },
    subjectLabel: function subjectLabel() {
      var subject = this.execution.subject;
      return subject.name || "".concat(this.$t("workflow.monitor.subject." + subject.type), " #").concat(subject.id);
    },
    prospectLink: function prospectLink() {
      var subject = this.execution.subject;
      return subject.type === "prospect" ? {
        name: "prospect.show",
        params: {
          project: this.project.slug,
          prospect: subject.id
        }
      } : null;
    }
  }),
  methods: {
    dateTime: _utils_workflow_format__WEBPACK_IMPORTED_MODULE_1__.dateTime,
    stepIcon: _utils_workflow_format__WEBPACK_IMPORTED_MODULE_1__.stepIcon,
    /**
     * What the step is: the name given in the builder, else what it does
     */
    title: function title(step) {
      if (step.name) {
        return step.name;
      }
      if (step.action) {
        var key = "workflow.action_type." + (0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_0__.slug)(step.action);
        return this.$te(key) ? this.$t(key) : step.action;
      }
      if (step.trigger) {
        return this.$t("workflow.trigger." + (0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_0__.slug)(step.trigger));
      }
      return this.$t("workflow.node." + step.type);
    },
    /**
     * What the step left behind (references only: never a credential)
     */
    details: function details(step) {
      var result = step.result || {};
      var lines = [];
      if (result.provider) {
        lines.push("".concat(this.$t("workflow.monitor.operator"), " : ").concat(result.provider));
      }
      if (result.sms_id) {
        lines.push("".concat(this.$t("workflow.monitor.sms"), " #").concat(result.sms_id));
      }
      if (result.message_id) {
        lines.push("".concat(this.$t("workflow.monitor.message"), " #").concat(result.message_id));
      }
      if (result.call_uuid || result.call_id) {
        lines.push("".concat(this.$t("workflow.monitor.call"), " ").concat(result.call_uuid || result.call_id));
      }
      if (result.outcome) {
        lines.push(this.$t("workflow.monitor.outcome." + result.outcome));
      }
      if (step.status === "skipped" && step.error) {
        lines.push(this.$t("workflow.monitor.skipped"));
      }
      return lines;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _apis_project_workflow__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/apis/project/workflow */ "./resources/js/apis/project/workflow.js");
/* harmony import */ var _apis_project_label__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/apis/project/label */ "./resources/js/apis/project/label.js");
/* harmony import */ var _actions_project_user__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/actions/project/user */ "./resources/js/actions/project/user.js");
/* harmony import */ var _actions_project_group__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/actions/project/group */ "./resources/js/actions/project/group.js");
/* harmony import */ var _actions_project_thread__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/actions/project/thread */ "./resources/js/actions/project/thread.js");
/* harmony import */ var _actions_project_category__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @/actions/project/category */ "./resources/js/actions/project/category.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }








var ALL_DAYS = [1, 2, 3, 4, 5, 6, 7];
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    modelValue: {
      "default": null
    },
    // boolean, number, minutes, text, html, user(s), group(s), label(s), thread, provider, on_error, time, date, weekdays
    type: {
      type: String,
      required: true
    },
    // The field of the catalog (its capability, for an operator)
    field: {
      type: Object,
      "default": function _default() {
        return {};
      }
    },
    // { prospect: [...], user: [...], project: [...] }
    variables: {
      type: Object,
      "default": function _default() {
        return {};
      }
    },
    readOnly: {
      type: Boolean,
      "default": false
    }
  },
  emits: ["update:modelValue", "provider-label"],
  data: function data() {
    return {
      providers: [],
      providersLoading: false,
      labels: [],
      labelsLoaded: false,
      loadingLists: false,
      newThreadName: this.$t("workflow.thread_create.default_name"),
      creatingThread: false,
      allDays: ALL_DAYS
    };
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_7__.mapGetters)(["project", "users", "groups", "threads", "categories", "can"])), {}, {
    isSingle: function isSingle() {
      return ["user", "group", "label", "thread"].includes(this.type);
    },
    canAddThread: function canAddThread() {
      return this.can("all.project.thread.add");
    },
    /**
     * First option of the list: for a channel, what happens when none is chosen
     */
    emptyLabel: function emptyLabel() {
      if (this.type !== "thread") {
        return this.$t("workflow.choose");
      }
      return this.options.length ? this.$t("workflow.thread_create.automatic", {
        name: this.options[0].label
      }) : this.$t("workflow.choose");
    },
    isLabels: function isLabels() {
      return this.type === "label" || this.type === "labels";
    },
    isMultiple: function isMultiple() {
      return ["users", "groups", "labels"].includes(this.type);
    },
    /**
     * Items of the project to choose from
     */
    options: function options() {
      switch (this.type) {
        case "user":
        case "users":
          return this.users.map(function (u) {
            return {
              id: u.id,
              label: [u.name, u.last_name].filter(Boolean).join(" ")
            };
          });
        case "group":
        case "groups":
          return this.groups.map(function (g) {
            return {
              id: g.id,
              label: g.name
            };
          });
        case "thread":
          // An email is not sent through a private channel
          return this.threads.filter(function (t) {
            return !t["private"];
          }).map(function (t) {
            return {
              id: t.id,
              label: t.name
            };
          });
        case "label":
        case "labels":
          return this.labels;
      }
      return [];
    },
    /**
     * Nothing to choose from (once loaded): said, instead of an empty list
     */
    emptyList: function emptyList() {
      if (!this.isSingle && !this.isMultiple) {
        return false;
      }
      var loaded = this.type === "label" || this.type === "labels" ? this.labelsLoaded : !this.loadingLists;
      return loaded && !this.options.length;
    },
    variableList: function variableList() {
      return Object.entries(this.variables).flatMap(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          group = _ref2[0],
          names = _ref2[1];
        return names.map(function (name) {
          return "{{".concat(group, ".").concat(name, "}}");
        });
      });
    }
  }),
  watch: {
    // The same field can change of type (another criterion) or get its categories late
    type: function type() {
      this.refresh();
    },
    categories: function categories() {
      if (this.isLabels) {
        this.loadLabels();
      }
    }
  },
  created: function created() {
    var _this = this;
    return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            if (_this.type === "provider") {
              _this.loadProviders();
            }

            // The lists of the project are not loaded for this page: load what is needed
            _context.next = 3;
            return _this.refresh();
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }))();
  },
  methods: {
    refresh: function refresh() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.labelsLoaded = false;
              _context2.next = 3;
              return _this2.ensureLoaded();
            case 3:
              if (_this2.isLabels) {
                _this2.loadLabels();
              }
            case 4:
            case "end":
              return _context2.stop();
          }
        }, _callee2);
      }))();
    },
    ensureLoaded: function ensureLoaded() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var needs;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              needs = [];
              if (["user", "users"].includes(_this3.type) && !_this3.users.length) needs.push(_actions_project_user__WEBPACK_IMPORTED_MODULE_3__.FETCH_USERS);
              if (["group", "groups"].includes(_this3.type) && !_this3.groups.length) needs.push(_actions_project_group__WEBPACK_IMPORTED_MODULE_4__.FETCH_GROUPS);
              if (_this3.type === "thread" && !_this3.threads.length) needs.push(_actions_project_thread__WEBPACK_IMPORTED_MODULE_5__.FETCH_THREADS);
              if (["label", "labels"].includes(_this3.type) && !_this3.categories.length) needs.push(_actions_project_category__WEBPACK_IMPORTED_MODULE_6__.FETCH_CATEGORIES);
              if (needs.length) {
                _context3.next = 7;
                break;
              }
              return _context3.abrupt("return");
            case 7:
              _this3.loadingLists = true;
              _context3.prev = 8;
              _context3.next = 11;
              return Promise.all(needs.map(function (action) {
                return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(action);
              }));
            case 11:
              _context3.next = 16;
              break;
            case 13:
              _context3.prev = 13;
              _context3.t0 = _context3["catch"](8);
              flashError({
                title: _this3.$t("workflow.title"),
                body: _this3.$t("workflow.error.load")
              });
            case 16:
              _context3.prev = 16;
              _this3.loadingLists = false;
              return _context3.finish(16);
            case 19:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[8, 13, 16, 19]]);
      }))();
    },
    /**
     * A public channel made on the spot, and chosen
     */
    createThread: function createThread() {
      var _this4 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var name, thread;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              name = _this4.newThreadName.trim();
              if (!(!name || _this4.creatingThread)) {
                _context4.next = 3;
                break;
              }
              return _context4.abrupt("return");
            case 3:
              _this4.creatingThread = true;
              _context4.prev = 4;
              _context4.next = 7;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch(_actions_project_thread__WEBPACK_IMPORTED_MODULE_5__.ADD_THREAD, {
                name: name,
                "private": false
              });
            case 7:
              thread = _context4.sent;
              _this4.$emit("update:modelValue", thread.id);
              flash({
                title: _this4.$t("workflow.title"),
                body: _this4.$t("workflow.thread_create.done", {
                  name: name
                })
              });
              _context4.next = 15;
              break;
            case 12:
              _context4.prev = 12;
              _context4.t0 = _context4["catch"](4);
              flashError({
                title: _this4.$t("workflow.title"),
                body: _this4.$t("workflow.thread_create.error")
              });
            case 15:
              _context4.prev = 15;
              _this4.creatingThread = false;
              return _context4.finish(15);
            case 18:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[4, 12, 15, 18]]);
      }))();
    },
    isChosen: function isChosen(id) {
      return (this.modelValue || []).includes(id);
    },
    toggleId: function toggleId(id) {
      var ids = new Set(this.modelValue || []);
      ids.has(id) ? ids["delete"](id) : ids.add(id);
      this.$emit("update:modelValue", _toConsumableArray(ids));
    },
    toggleDay: function toggleDay(day) {
      var days = new Set(this.modelValue || ALL_DAYS);
      days.has(day) ? days["delete"](day) : days.add(day);
      this.$emit("update:modelValue", _toConsumableArray(days).sort());
    },
    /**
     * Chosen operator: the node keeps its reference, and its name for the messages
     */
    chooseProvider: function chooseProvider(ref) {
      var _this$providers$find;
      this.$emit("update:modelValue", ref || null);
      this.$emit("provider-label", ((_this$providers$find = this.providers.find(function (p) {
        return p.ref === ref;
      })) === null || _this$providers$find === void 0 ? void 0 : _this$providers$find.label) || null);
    },
    insertVariable: function insertVariable(variable) {
      var area = this.$refs.text;
      var text = this.modelValue || "";
      var start = area ? area.selectionStart : text.length;
      var end = area ? area.selectionEnd : text.length;
      this.$emit("update:modelValue", text.slice(0, start) + variable + text.slice(end));
    },
    loadProviders: function loadProviders() {
      var _this5 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
        var _yield$WorkflowServic, data;
        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              _this5.providersLoading = true;
              _context5.prev = 1;
              _context5.next = 4;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_1__["default"].providers(_this5.project.slug, _this5.field.capability);
            case 4:
              _yield$WorkflowServic = _context5.sent;
              data = _yield$WorkflowServic.data;
              _this5.providers = data;
              _context5.next = 12;
              break;
            case 9:
              _context5.prev = 9;
              _context5.t0 = _context5["catch"](1);
              flashError({
                title: _this5.$t("workflow.title"),
                body: _this5.$t("workflow.error.load")
              });
            case 12:
              _context5.prev = 12;
              _this5.providersLoading = false;
              return _context5.finish(12);
            case 15:
            case "end":
              return _context5.stop();
          }
        }, _callee5, null, [[1, 9, 12, 15]]);
      }))();
    },
    /**
     * Labels belong to categories: the ones of the prospects, all of them
     */
    loadLabels: function loadLabels() {
      var _this6 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        var categories, lists;
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              categories = _this6.categories.filter(function (c) {
                return c["for"] === "prospect";
              });
              _context7.next = 3;
              return Promise.all(categories.map(/*#__PURE__*/function () {
                var _ref3 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6(category) {
                  var _yield$LabelService$g, data;
                  return _regeneratorRuntime().wrap(function _callee6$(_context6) {
                    while (1) switch (_context6.prev = _context6.next) {
                      case 0:
                        _context6.prev = 0;
                        _context6.next = 3;
                        return _apis_project_label__WEBPACK_IMPORTED_MODULE_2__["default"].get(_this6.project.slug, category.id);
                      case 3:
                        _yield$LabelService$g = _context6.sent;
                        data = _yield$LabelService$g.data;
                        return _context6.abrupt("return", data.map(function (label) {
                          return {
                            id: label.id,
                            label: "".concat(category.name, " / ").concat(label.name)
                          };
                        }));
                      case 8:
                        _context6.prev = 8;
                        _context6.t0 = _context6["catch"](0);
                        return _context6.abrupt("return", []);
                      case 11:
                      case "end":
                        return _context6.stop();
                    }
                  }, _callee6, null, [[0, 8]]);
                }));
                return function (_x2) {
                  return _ref3.apply(this, arguments);
                };
              }()));
            case 3:
              lists = _context7.sent;
              _this6.labels = lists.flat();
              _this6.labelsLoaded = true;
            case 6:
            case "end":
              return _context7.stop();
          }
        }, _callee7);
      }))();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
// One bubble at a time: opening another one closes the previous
var opened = null;

/**
 * A small "i" button that explains, in a bubble, what the field or the button
 * next to it does. Give it the text (text) or the key of a translation (k);
 * without text there is no button at all.
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    text: {
      type: String,
      "default": ""
    },
    k: {
      type: String,
      "default": ""
    }
  },
  data: function data() {
    return {
      open: false,
      position: {}
    };
  },
  computed: {
    content: function content() {
      if (this.text) {
        return this.text;
      }
      return this.k && this.$te(this.k) ? this.$t(this.k) : "";
    }
  },
  beforeUnmount: function beforeUnmount() {
    this.close();
  },
  methods: {
    toggle: function toggle() {
      this.open ? this.close() : this.show();
    },
    show: function show() {
      if (opened && opened !== this) {
        opened.close();
      }
      var rect = this.$refs.button.getBoundingClientRect();
      var width = 250;
      this.position = {
        left: Math.max(8, Math.min(rect.left - 12, window.innerWidth - width - 8)) + "px",
        top: rect.bottom + 8 + "px"
      };
      this.open = true;
      opened = this;
      document.addEventListener("click", this.close);
      document.addEventListener("keydown", this.onKey);
      window.addEventListener("scroll", this.close, true);
    },
    close: function close() {
      this.open = false;
      if (opened === this) {
        opened = null;
      }
      document.removeEventListener("click", this.close);
      document.removeEventListener("keydown", this.onKey);
      window.removeEventListener("scroll", this.close, true);
    },
    onKey: function onKey(event) {
      if (event.key === "Escape") {
        this.close();
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ConditionGroup_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ConditionGroup.vue */ "./resources/js/components/workflow/ConditionGroup.vue");
/* harmony import */ var _HelpTip_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./HelpTip.vue */ "./resources/js/components/workflow/HelpTip.vue");
/* harmony import */ var _FieldInput_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FieldInput.vue */ "./resources/js/components/workflow/FieldInput.vue");
/* harmony import */ var _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/utils/workflow-graph */ "./resources/js/utils/workflow-graph.js");
/* harmony import */ var _utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/utils/workflow-i18n */ "./resources/js/utils/workflow-i18n.js");
/* harmony import */ var _utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/utils/workflow-conditions */ "./resources/js/utils/workflow-conditions.js");
var _excluded = ["mode", "filters"];
function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }
function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }
function _toArray(arr) { return _arrayWithHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableRest(); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }






/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    HelpTip: _HelpTip_vue__WEBPACK_IMPORTED_MODULE_1__["default"],
    ConditionGroup: _ConditionGroup_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    FieldInput: _FieldInput_vue__WEBPACK_IMPORTED_MODULE_2__["default"]
  },
  props: {
    node: {
      type: Object,
      required: true
    },
    graph: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    readOnly: {
      type: Boolean,
      "default": false
    }
  },
  // update: { name?, config? } - link: { branch, to } (to null: no next step)
  emits: ["update", "link", "remove", "close"],
  data: function data() {
    return {
      pending: null
    };
  },
  watch: {
    // The parent has applied the change: the prop is up to date again
    "node.config": function nodeConfig() {
      this.pending = null;
    }
  },
  computed: {
    /**
     * The configuration being edited. A change is only seen in the "node" prop
     * after the parent has rendered again; "pending" holds the latest one in the meantime, so
     * that two changes in a row (an operator and its name, for instance) are
     * both kept instead of the second starting from the old configuration.
     */
    config: function config() {
      return this.pending || this.node.config || {};
    },
    /**
     * What the workflow works on (prospect, order, appointment): the criteria depend on it
     */
    subject: function subject() {
      var trigger = (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.triggerNode)(this.graph);
      return trigger && trigger.config.trigger ? trigger.config.trigger.split(".")[0] : "prospect";
    },
    triggerFields: function triggerFields() {
      var _this = this;
      return (this.catalog.triggers.find(function (t) {
        return t.type === _this.config.trigger;
      }) || {}).fields || [];
    },
    actionDef: function actionDef() {
      var _this2 = this;
      return this.catalog.actions.find(function (a) {
        return a.type === _this2.config.action;
      }) || null;
    },
    actionGroups: function actionGroups() {
      var groups = {};
      this.catalog.actions.forEach(function (action) {
        (groups[action.category] = groups[action.category] || {
          category: action.category,
          actions: []
        }).actions.push(action);
      });
      return Object.values(groups);
    },
    /**
     * The condition of a "If" step or of a filter trigger, as a tree.
     * (It sits at the top of the configuration: { op, items }.)
     */
    tree: function tree() {
      if (Array.isArray(this.config.items)) {
        return {
          op: this.config.op || "and",
          items: this.config.items
        };
      }

      // The first format: { mode: "all" | "any", filters: { name: value } }
      var filters = this.config.filters || {};
      return {
        op: this.config.mode === "any" ? "or" : "and",
        items: Object.entries(filters).map(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
            name = _ref2[0],
            value = _ref2[1];
          return {
            filters: _defineProperty({}, name, value)
          };
        })
      };
    },
    hasValue: function hasValue() {
      var value = this.config.value;
      return value && _typeof(value) === "object" && Object.keys(value).length > 0;
    },
    waitMode: function waitMode() {
      return this.config.until_date !== undefined ? "date" : this.config.until_time !== undefined ? "time" : "duration";
    },
    outputList: function outputList() {
      var _this3 = this;
      return (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.outputs)(this.node).map(function (branch) {
        var edge = _this3.graph.edges.find(function (e) {
          return e.from === _this3.node.id && (e.branch || null) === branch;
        });
        return {
          branch: branch,
          to: edge ? edge.to : null
        };
      });
    },
    /**
     * Steps that can follow this one
     */
    candidates: function candidates() {
      var _this4 = this;
      return this.graph.nodes.filter(function (n) {
        return n.id !== _this4.node.id && n.type !== "trigger";
      });
    }
  },
  methods: {
    slug: _utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_4__.slug,
    /**
     * Name of an action; the identifier itself when it has no translation yet
     */
    actionLabel: function actionLabel(type) {
      var key = "workflow.action_type." + (0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_4__.slug)(type);
      return this.$te(key) ? this.$t(key) : type;
    },
    setConfig: function setConfig(patch) {
      this.replaceConfig(_objectSpread(_objectSpread({}, this.config), patch));
    },
    replaceConfig: function replaceConfig(config) {
      this.pending = config;
      this.$emit("update", {
        config: config
      });
    },
    changeTrigger: function changeTrigger(type) {
      this.replaceConfig((0,_utils_workflow_conditions__WEBPACK_IMPORTED_MODULE_5__.triggerConfig)(this.catalog, type));
    },
    /**
     * The explanation of a field, if there is one
     */
    help: function help(field) {
      var key = "workflow.help." + this.fieldName(field);
      return this.$te(key) ? this.$t(key) : null;
    },
    /**
     * "value.user" => config.value.user
     */
    getPath: function getPath(path) {
      return path.split(".").reduce(function (value, key) {
        return value == null ? undefined : value[key];
      }, this.config);
    },
    setPath: function setPath(path, value) {
      var _path$split = path.split("."),
        _path$split2 = _toArray(_path$split),
        first = _path$split2[0],
        rest = _path$split2.slice(1);
      if (!rest.length) {
        this.setConfig(_defineProperty({}, first, value));
        return;
      }
      this.setConfig(_defineProperty({}, first, _objectSpread(_objectSpread({}, this.config[first] || {}), {}, _defineProperty({}, rest.join("."), value))));
    },
    /**
     * "value.user" => "user": the name of the field in the translations
     */
    fieldName: function fieldName(field) {
      return field.key.split(".").pop();
    },
    chooseAction: function chooseAction(type) {
      var def = this.catalog.actions.find(function (a) {
        return a.type === type;
      });

      // The legacy campaign actions keep their data in "value"
      this.replaceConfig(def && def.fields.some(function (f) {
        return f.key.startsWith("value.");
      }) ? {
        action: type,
        value: {}
      } : {
        action: type
      });
    },
    setTree: function setTree(tree) {
      // Only the tree: the first format ("mode", "filters") is dropped
      var _this$config = this.config,
        mode = _this$config.mode,
        filters = _this$config.filters,
        rest = _objectWithoutProperties(_this$config, _excluded);
      this.replaceConfig(_objectSpread(_objectSpread({}, rest), {}, {
        op: tree.op,
        items: tree.items
      }));
    },
    setWaitMode: function setWaitMode(mode) {
      var config = {
        duration: {
          minutes: 30
        },
        time: {
          until_time: "09:00",
          days: [1, 2, 3, 4, 5, 6, 7]
        },
        date: {
          until_date: ""
        }
      }[mode];
      this.replaceConfig(config);
    },
    cycle: function cycle(id) {
      return (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.wouldCreateCycle)(this.graph, this.node.id, id);
    },
    label: function label(node) {
      return node.name || "".concat(this.$t("workflow.node." + node.type), " (").concat(node.id, ")");
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/project/workflow */ "./resources/js/apis/project/workflow.js");
/* harmony import */ var _WorkflowBuilder_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WorkflowBuilder.vue */ "./resources/js/components/workflow/WorkflowBuilder.vue");
/* harmony import */ var _utils_workflow_templates__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/utils/workflow-templates */ "./resources/js/utils/workflow-templates.js");
/* harmony import */ var _WorkflowMonitor_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./WorkflowMonitor.vue */ "./resources/js/components/workflow/WorkflowMonitor.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }





/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    WorkflowBuilder: _WorkflowBuilder_vue__WEBPACK_IMPORTED_MODULE_1__["default"],
    WorkflowMonitor: _WorkflowMonitor_vue__WEBPACK_IMPORTED_MODULE_3__["default"]
  },
  data: function data() {
    return {
      tab: "schema",
      workflows: [],
      current: null,
      catalog: null,
      loading: false,
      loadingCurrent: false,
      creating: false,
      newName: "",
      showTemplates: null
    };
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_4__.mapGetters)(["project", "can"])), {}, {
    /**
     * Open by default while there is no automation yet
     */
    templatesOpen: function templatesOpen() {
      return this.showTemplates === null ? !this.loading && !this.workflows.length : this.showTemplates;
    },
    templateIds: function templateIds() {
      return _utils_workflow_templates__WEBPACK_IMPORTED_MODULE_2__.TEMPLATE_IDS;
    },
    mobile: function mobile() {
      return deviceType() != "desktop";
    },
    canMonitor: function canMonitor() {
      return this.can("all.project.workflow.execution.view");
    }
  }),
  created: function created() {
    var _this = this;
    return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            if (!_this.mobile) {
              _context.next = 2;
              break;
            }
            return _context.abrupt("return");
          case 2:
            _context.next = 4;
            return Promise.all([_this.refreshList(), _this.loadCatalog()]);
          case 4:
            _context.next = 6;
            return _this.load();
          case 6:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }))();
  },
  methods: {
    /**
     * Executions worth showing in the list
     */
    visibleCounts: function visibleCounts(workflow) {
      var counts = workflow.executions || {};
      return Object.fromEntries(["waiting", "running", "failed"].filter(function (status) {
        return counts[status] > 0;
      }).map(function (status) {
        return [status, counts[status]];
      }));
    },
    refreshList: function refreshList() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$WorkflowServic, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.loading = true;
              _context2.prev = 1;
              _context2.next = 4;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].get(_this2.project.slug);
            case 4:
              _yield$WorkflowServic = _context2.sent;
              data = _yield$WorkflowServic.data;
              _this2.workflows = data;
              _context2.next = 12;
              break;
            case 9:
              _context2.prev = 9;
              _context2.t0 = _context2["catch"](1);
              flashError({
                title: _this2.$t("workflow.title"),
                body: _this2.$t("workflow.error.load")
              });
            case 12:
              _context2.prev = 12;
              _this2.loading = false;
              return _context2.finish(12);
            case 15:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 9, 12, 15]]);
      }))();
    },
    loadCatalog: function loadCatalog() {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var _yield$WorkflowServic2, data;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _context3.prev = 0;
              _context3.next = 3;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].catalog(_this3.project.slug);
            case 3:
              _yield$WorkflowServic2 = _context3.sent;
              data = _yield$WorkflowServic2.data;
              _this3.catalog = data;
              _context3.next = 11;
              break;
            case 8:
              _context3.prev = 8;
              _context3.t0 = _context3["catch"](0);
              flashError({
                title: _this3.$t("workflow.title"),
                body: _this3.$t("workflow.error.load")
              });
            case 11:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[0, 8]]);
      }))();
    },
    /**
     * The workflow of the route
     */
    load: function load() {
      var _this4 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var id, _yield$WorkflowServic3, data;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              id = _this4.$route.params.workflow;
              if (id) {
                _context4.next = 4;
                break;
              }
              _this4.current = null;
              return _context4.abrupt("return");
            case 4:
              _this4.loadingCurrent = true;
              _context4.prev = 5;
              _context4.next = 8;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].show(_this4.project.slug, id);
            case 8:
              _yield$WorkflowServic3 = _context4.sent;
              data = _yield$WorkflowServic3.data;
              _this4.current = data;
              _context4.next = 17;
              break;
            case 13:
              _context4.prev = 13;
              _context4.t0 = _context4["catch"](5);
              _this4.current = null;
              flashError({
                title: _this4.$t("workflow.title"),
                body: _this4.$t("workflow.error.load")
              });
            case 17:
              _context4.prev = 17;
              _this4.loadingCurrent = false;
              return _context4.finish(17);
            case 20:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[5, 13, 17, 20]]);
      }))();
    },
    templateIcon: function templateIcon(id) {
      return {
        welcome_email: "✉",
        sms_follow_up: "↻",
        appointment_reminder: "⏰",
        work_hours_contact: "☀"
      }[id] || "⚡";
    },
    createFromTemplate: function createFromTemplate(id) {
      var _this5 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              _context5.next = 2;
              return _this5.create(_this5.$t("workflow.template.".concat(id, ".name")), (0,_utils_workflow_templates__WEBPACK_IMPORTED_MODULE_2__.templateGraph)(id));
            case 2:
            case "end":
              return _context5.stop();
          }
        }, _callee5);
      }))();
    },
    create: function create() {
      var _arguments = arguments,
        _this6 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6() {
        var name, graph, _yield$WorkflowServic4, data;
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              name = _arguments.length > 0 && _arguments[0] !== undefined ? _arguments[0] : _this6.newName.trim();
              graph = _arguments.length > 1 && _arguments[1] !== undefined ? _arguments[1] : null;
              if (!(typeof name !== "string" || !name || _this6.creating)) {
                _context6.next = 4;
                break;
              }
              return _context6.abrupt("return");
            case 4:
              _this6.creating = true;
              _context6.prev = 5;
              _context6.next = 8;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].create(_this6.project.slug, {
                name: name
              });
            case 8:
              _yield$WorkflowServic4 = _context6.sent;
              data = _yield$WorkflowServic4.data;
              if (!graph) {
                _context6.next = 13;
                break;
              }
              _context6.next = 13;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].update(_this6.project.slug, data.id, {
                draft_graph: graph
              });
            case 13:
              _this6.newName = "";
              _context6.next = 16;
              return _this6.refreshList();
            case 16:
              _this6.$router.push({
                name: "workflow.show",
                params: {
                  project: _this6.project.slug,
                  workflow: data.id
                }
              });
              _context6.next = 22;
              break;
            case 19:
              _context6.prev = 19;
              _context6.t0 = _context6["catch"](5);
              flashError({
                title: _this6.$t("workflow.title"),
                body: _this6.$t("workflow.error.create")
              });
            case 22:
              _context6.prev = 22;
              _this6.creating = false;
              return _context6.finish(22);
            case 25:
            case "end":
              return _context6.stop();
          }
        }, _callee6, null, [[5, 19, 22, 25]]);
      }))();
    },
    deleted: function deleted() {
      var _this7 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              _this7.current = null;
              _context7.next = 3;
              return _this7.refreshList();
            case 3:
              _this7.$router.push({
                name: "workflow",
                params: {
                  project: _this7.project.slug
                }
              });
            case 4:
            case "end":
              return _context7.stop();
          }
        }, _callee7);
      }))();
    }
  },
  watch: {
    "$route.params.workflow": function $routeParamsWorkflow() {
      this.load();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/project/workflow */ "./resources/js/apis/project/workflow.js");
/* harmony import */ var _NodeEditor_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NodeEditor.vue */ "./resources/js/components/workflow/NodeEditor.vue");
/* harmony import */ var _utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/utils/workflow-i18n */ "./resources/js/utils/workflow-i18n.js");
/* harmony import */ var _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/utils/workflow-graph */ "./resources/js/utils/workflow-graph.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
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





var SAVE_DELAY = 1000;

/**
 * The problems of an answer: { message, node } (the step they are about, if known)
 */
function problemsOf(data) {
  return data.problems || (data.errors || []).map(function (message) {
    return {
      message: message,
      node: null
    };
  });
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    NodeEditor: _NodeEditor_vue__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  props: {
    workflow: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    }
  },
  emits: ["changed", "deleted"],
  data: function data() {
    return {
      graph: (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.normalize)(this.workflow.graph),
      name: this.workflow.name,
      status: this.workflow.status,
      version: this.workflow.version,
      unpublished: this.workflow.unpublished_changes,
      errors: [],
      checked: false,
      saveState: "saved",
      busy: false,
      selectedId: null,
      connecting: null,
      menu: null,
      moreOpen: false,
      live: null,
      saveTimer: null,
      saving: Promise.resolve()
    };
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_4__.mapGetters)(["project", "can"])), {}, {
    canUpdate: function canUpdate() {
      return this.can("all.project.workflow.update");
    },
    canExecute: function canExecute() {
      return this.can("all.project.workflow.execute");
    },
    canDelete: function canDelete() {
      return this.can("all.project.workflow.delete");
    },
    readOnly: function readOnly() {
      return !this.canUpdate || this.status === "archived";
    },
    /**
     * The one button to go further: turn the workflow on, or publish the changes
     */
    primary: function primary() {
      var _this = this;
      if (this.status === 'archived') return null;
      if (this.status === 'active') {
        return this.canExecute && this.unpublished ? {
          label: 'workflow.action.publish_now',
          write: false,
          run: function run() {
            return _this.publish(false);
          }
        } : null;
      }
      if (this.status === 'paused' && !this.unpublished && this.canExecute) {
        return {
          label: 'workflow.action.activate',
          write: false,
          run: function run() {
            return _this.activate();
          }
        };
      }
      if (this.canExecute) {
        return {
          label: 'workflow.action.turn_on',
          write: true,
          run: function run() {
            return _this.publish(true);
          }
        };
      }
      return this.canUpdate ? {
        label: 'workflow.action.publish',
        write: true,
        run: function run() {
          return _this.publish(false);
        }
      } : null;
    },
    triggerId: function triggerId() {
      var trigger = this.graph.nodes.find(function (node) {
        return node.type === 'trigger';
      });
      return trigger ? trigger.id : null;
    },
    paletteTypes: function paletteTypes() {
      return ["condition", "action", "wait", "wait_event", "end"];
    },
    /**
     * The graph with the node being dragged where the mouse is
     */
    view: function view() {
      return this.live ? (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.moveNode)(this.graph, this.live.id, this.live.x, this.live.y) : this.graph;
    },
    links: function links() {
      return (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.links)(this.view);
    },
    size: function size() {
      return (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.bounds)(this.view);
    },
    errorNodes: function errorNodes() {
      return this.errors.map(function (error) {
        return error.node;
      }).filter(Boolean);
    },
    orphanIds: function orphanIds() {
      return (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.orphans)(this.graph);
    },
    selectedNode: function selectedNode() {
      return this.selectedId ? (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.findNode)(this.graph, this.selectedId) : null;
    }
  }),
  mounted: function mounted() {
    document.addEventListener('click', this.closeMenus);
  },
  beforeUnmount: function beforeUnmount() {
    document.removeEventListener('click', this.closeMenus);
    this.stopDrag();
    clearTimeout(this.saveTimer);

    // Do not lose the last changes
    if (this.saveState === "pending") {
      this.saveNow();
    }
  },
  methods: {
    outputsOf: _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.outputs,
    menuY: function menuY(node) {
      return node.position.y + _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_HEIGHT + 30;
    },
    /**
     * What can be added, by what it is for. An end cannot be put before another step.
     */
    sections: function sections(inserting) {
      return [{
        key: 'actions',
        types: ['action']
      }, {
        key: 'rules',
        types: ['condition', 'wait', 'wait_event'].concat(_toConsumableArray(inserting ? [] : ['end']))
      }];
    },
    nodeExists: function nodeExists(id) {
      return !!(0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.findNode)(this.graph, id);
    },
    /**
     * From a problem: show the step and open its settings
     */
    goTo: function goTo(id) {
      this.selectedId = id;
      var node = (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.findNode)(this.graph, id);
      var canvas = this.$refs.canvas;
      if (node && canvas) {
        canvas.scrollTo({
          left: Math.max(0, node.position.x - 60),
          top: Math.max(0, node.position.y - 80),
          behavior: 'smooth'
        });
      }
    },
    /**
     * A refused publication only lists messages: ask again for the steps they are about
     */
    refreshProblems: function refreshProblems() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$WorkflowServic, data;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 3;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].validate(_this2.project.slug, _this2.workflow.id, {
                graph: _this2.graph
              });
            case 3:
              _yield$WorkflowServic = _context.sent;
              data = _yield$WorkflowServic.data;
              _this2.errors = problemsOf(data);
              _context.next = 10;
              break;
            case 8:
              _context.prev = 8;
              _context.t0 = _context["catch"](0);
            case 10:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[0, 8]]);
      }))();
    },
    closeMenus: function closeMenus() {
      this.menu = null;
      this.moreOpen = false;
    },
    icon: function icon(type) {
      return {
        trigger: '⚡',
        condition: '⑂',
        action: '✉',
        wait: '⏱',
        wait_event: '⏳',
        end: '■'
      }[type] || '•';
    },
    hasLink: function hasLink(node, branch) {
      return this.graph.edges.some(function (edge) {
        return edge.from === node.id && (edge.branch || null) === (branch || null);
      });
    },
    portCenter: function portCenter(branch) {
      return branch === 'yes' ? _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH * 0.3 : branch === 'no' ? _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH * 0.7 : _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH / 2;
    },
    openMenu: function openMenu(from, branch, x, y, insert) {
      if (this.readOnly) return;
      this.menu = {
        from: from,
        branch: branch || null,
        x: Math.max(0, x - 140),
        y: y,
        insert: insert
      };
    },
    /**
     * A step added from the "+": created, linked, and open for configuration
     */
    addAfter: function addAfter(type) {
      var _this$menu = this.menu,
        from = _this$menu.from,
        branch = _this$menu.branch;
      var _appendNode = (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.appendNode)(this.graph, from, branch, type),
        graph = _appendNode.graph,
        node = _appendNode.node,
        error = _appendNode.error;
      this.menu = null;
      if (error) {
        flashWarning({
          title: this.$t("workflow.title"),
          body: this.$t('workflow.error.' + error)
        });
        return;
      }
      this.edit(graph);
      this.selectedId = node.id;
    },
    linkExisting: function linkExisting() {
      var _this$menu2 = this.menu,
        from = _this$menu2.from,
        branch = _this$menu2.branch;
      this.menu = null;
      this.connecting = {
        from: from,
        branch: branch
      };
    },
    portLeft: function portLeft(branch) {
      return branch === "yes" ? "".concat(_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH * 0.3 - 8, "px") : branch === "no" ? "".concat(_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH * 0.7 - 8, "px") : undefined;
    },
    /**
     * One line that says what a step does
     */
    describe: function describe(node) {
      var _this3 = this;
      var c = node.config || {};
      var t = function t(key, params) {
        return _this3.$t("workflow.".concat(key), params);
      };
      switch (node.type) {
        case "trigger":
          return t("trigger.".concat((0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_2__.slug)(c.trigger)));
        case "action":
          return c.action ? this.$te("workflow.action_type.".concat((0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_2__.slug)(c.action))) ? t("action_type.".concat((0,_utils_workflow_i18n__WEBPACK_IMPORTED_MODULE_2__.slug)(c.action))) : c.action : t("not_configured");
        case "condition":
          {
            var count = (c.items || []).length;
            return count ? t("condition.summary", {
              count: count
            }) : t("not_configured");
          }
        case "wait":
          return this.describeWait(c);
        case "wait_event":
          return "".concat(t("event.".concat(c.event)), " (").concat(this.describeDuration(c), ")");
        default:
          return node.name || "";
      }
    },
    describeDuration: function describeDuration(c) {
      return [c.days ? "".concat(c.days, " ").concat(this.$t("workflow.wait.days")) : null, c.hours ? "".concat(c.hours, " ").concat(this.$t("workflow.wait.hours")) : null, c.minutes ? "".concat(c.minutes, " ").concat(this.$t("workflow.wait.minutes")) : null].filter(Boolean).join(" ");
    },
    describeWait: function describeWait(c) {
      if (c.until_date) {
        return this.$t("workflow.wait.until_date_summary", {
          date: c.until_date
        });
      }
      if (c.until_time) {
        return this.$t("workflow.wait.until_time_summary", {
          time: c.until_time
        });
      }
      return this.describeDuration(c) || this.$t("workflow.not_configured");
    },
    // Editing the graph
    edit: function edit(graph) {
      this.graph = graph;
    },
    addFromPalette: function addFromPalette(type) {
      if (this.readOnly) {
        return;
      }

      // Under the lowest step, aligned with the selected one
      var lowest = Math.max.apply(Math, [0].concat(_toConsumableArray(this.graph.nodes.map(function (n) {
        return n.position.y;
      }))));
      var anchor = this.selectedNode || {
        position: {
          x: 80
        }
      };
      this.place(type, {
        x: anchor.position.x,
        y: lowest + _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_HEIGHT + 80
      });
    },
    place: function place(type, position) {
      var node = (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.createNode)(this.graph, type, position);
      this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.addNode)(this.graph, node));
      this.selectedId = node.id;
    },
    paletteDragStart: function paletteDragStart(event, type) {
      event.dataTransfer.setData("text/plain", type);
      event.dataTransfer.effectAllowed = "copy";
    },
    paletteDrop: function paletteDrop(event) {
      var type = event.dataTransfer.getData("text/plain");
      if (this.readOnly || !this.paletteTypes.includes(type)) {
        return;
      }
      var rect = this.$refs.canvas.getBoundingClientRect();
      this.place(type, {
        x: event.clientX - rect.left + this.$refs.canvas.scrollLeft - _utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.NODE_WIDTH / 2,
        y: event.clientY - rect.top + this.$refs.canvas.scrollTop - 20
      });
    },
    removeNode: function removeNode(node) {
      if (this.readOnly) {
        return;
      }
      this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.removeNode)(this.graph, node.id));
      if (this.selectedId === node.id) {
        this.selectedId = null;
      }
    },
    updateNode: function updateNode(patch) {
      this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.updateNode)(this.graph, this.selectedId, patch));
    },
    arrange: function arrange() {
      this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.layout)(this.graph));
    },
    // Links
    startConnect: function startConnect(node, branch) {
      if (this.readOnly) {
        return;
      }
      this.connecting = {
        from: node.id,
        branch: branch
      };
    },
    cancelConnect: function cancelConnect() {
      this.connecting = null;
    },
    nodeClick: function nodeClick(node) {
      this.menu = null;
      if (this.connecting) {
        this.completeConnect(node);
        return;
      }
      this.selectedId = node.id;
    },
    canvasClick: function canvasClick() {
      this.menu = null;
      if (this.connecting) {
        this.cancelConnect();
      } else {
        this.selectedId = null;
      }
    },
    completeConnect: function completeConnect(node) {
      var _this$connecting = this.connecting,
        from = _this$connecting.from,
        branch = _this$connecting.branch;
      this.connecting = null;
      this.link(from, node.id, branch);
    },
    link: function link(from, to, branch) {
      var _connect = (0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.connect)(this.graph, from, to, branch),
        graph = _connect.graph,
        error = _connect.error;
      if (error) {
        flashWarning({
          title: this.$t("workflow.title"),
          body: this.$t("workflow.error.".concat(error))
        });
        return;
      }
      this.edit(graph);
    },
    /**
     * From the panel of a step: link an output to a step, or remove it (to = null)
     */
    linkFromPanel: function linkFromPanel(_ref) {
      var branch = _ref.branch,
        to = _ref.to;
      if (to) {
        this.link(this.selectedId, to, branch);
      } else {
        this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.disconnect)(this.graph, this.selectedId, branch));
      }
    },
    removeLink: function removeLink(link) {
      this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.disconnect)(this.graph, link.from, link.branch));
    },
    // Moving a step
    dragStart: function dragStart(event, node) {
      if (this.readOnly || event.button !== 0 || this.connecting) {
        return;
      }
      this.drag = {
        id: node.id,
        mx: event.clientX,
        my: event.clientY,
        x: node.position.x,
        y: node.position.y,
        moved: false
      };
      window.addEventListener("mousemove", this.dragMove);
      window.addEventListener("mouseup", this.dragEnd);
    },
    dragMove: function dragMove(event) {
      var d = this.drag;
      if (!d) {
        return;
      }
      var x = Math.max(0, d.x + event.clientX - d.mx);
      var y = Math.max(0, d.y + event.clientY - d.my);
      d.moved = d.moved || Math.abs(x - d.x) + Math.abs(y - d.y) > 3;
      this.live = {
        id: d.id,
        x: x,
        y: y
      };
    },
    dragEnd: function dragEnd() {
      var live = this.live;
      this.stopDrag();
      if (live) {
        this.edit((0,_utils_workflow_graph__WEBPACK_IMPORTED_MODULE_3__.moveNode)(this.graph, live.id, live.x, live.y));
      }
    },
    stopDrag: function stopDrag() {
      window.removeEventListener("mousemove", this.dragMove);
      window.removeEventListener("mouseup", this.dragEnd);
      this.drag = null;
      this.live = null;
    },
    // Saving the draft
    scheduleSave: function scheduleSave() {
      var _this4 = this;
      if (this.readOnly) {
        return;
      }
      this.saveState = "pending";
      clearTimeout(this.saveTimer);
      this.saveTimer = setTimeout(function () {
        return _this4.saveNow();
      }, SAVE_DELAY);
    },
    /**
     * Saves the draft, one request at a time and in order.
     * The answer says what is still wrong with it.
     */
    saveNow: function saveNow() {
      var _this5 = this;
      clearTimeout(this.saveTimer);
      this.saving = this.saving.then(/*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$WorkflowServic2, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this5.saveState = "saving";
              _context2.prev = 1;
              _context2.next = 4;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].update(_this5.project.slug, _this5.workflow.id, {
                draft_graph: _this5.graph
              });
            case 4:
              _yield$WorkflowServic2 = _context2.sent;
              data = _yield$WorkflowServic2.data;
              _this5.unpublished = data.unpublished_changes;
              _this5.errors = _this5.checked ? problemsOf(data) : [];

              // Edited again while saving: the next save is already planned
              if (_this5.saveState === "saving") {
                _this5.saveState = "saved";
              }
              _context2.next = 14;
              break;
            case 11:
              _context2.prev = 11;
              _context2.t0 = _context2["catch"](1);
              _this5.saveState = "error";
            case 14:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 11]]);
      })));
      return this.saving;
    },
    rename: function rename() {
      var _this6 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var name;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              name = _this6.name.trim();
              if (name) {
                _context3.next = 4;
                break;
              }
              _this6.name = _this6.workflow.name;
              return _context3.abrupt("return");
            case 4:
              _context3.prev = 4;
              _context3.next = 7;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].update(_this6.project.slug, _this6.workflow.id, {
                name: name
              });
            case 7:
              _this6.$emit("changed");
              _context3.next = 13;
              break;
            case 10:
              _context3.prev = 10;
              _context3.t0 = _context3["catch"](4);
              flashError({
                title: _this6.$t("workflow.title"),
                body: _this6.$t("workflow.error.save")
              });
            case 13:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[4, 10]]);
      }))();
    },
    // Checking, publishing, life cycle
    check: function check() {
      var _this7 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var _yield$WorkflowServic3, data;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _this7.busy = true;
              _context4.prev = 1;
              _context4.next = 4;
              return _this7.saveNow();
            case 4:
              _context4.next = 6;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].validate(_this7.project.slug, _this7.workflow.id, {
                graph: _this7.graph
              });
            case 6:
              _yield$WorkflowServic3 = _context4.sent;
              data = _yield$WorkflowServic3.data;
              _this7.errors = problemsOf(data);
              _this7.checked = true;
              _context4.next = 15;
              break;
            case 12:
              _context4.prev = 12;
              _context4.t0 = _context4["catch"](1);
              flashError({
                title: _this7.$t("workflow.title"),
                body: _this7.$t("workflow.error.load")
              });
            case 15:
              _context4.prev = 15;
              _this7.busy = false;
              return _context4.finish(15);
            case 18:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[1, 12, 15, 18]]);
      }))();
    },
    publish: function publish(activate) {
      var _this8 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6() {
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.next = 2;
              return _this8.run(/*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
                return _regeneratorRuntime().wrap(function _callee5$(_context5) {
                  while (1) switch (_context5.prev = _context5.next) {
                    case 0:
                      _context5.next = 2;
                      return _this8.saveNow();
                    case 2:
                      return _context5.abrupt("return", _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].publish(_this8.project.slug, _this8.workflow.id, {
                        activate: activate
                      }));
                    case 3:
                    case "end":
                      return _context5.stop();
                  }
                }, _callee5);
              })), "published");
            case 2:
            case "end":
              return _context6.stop();
          }
        }, _callee6);
      }))();
    },
    activate: function activate() {
      var _this9 = this;
      return this.run(function () {
        return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].activate(_this9.project.slug, _this9.workflow.id);
      }, "activated");
    },
    pause: function pause() {
      var _this10 = this;
      return this.run(function () {
        return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].pause(_this10.project.slug, _this10.workflow.id);
      }, "paused");
    },
    archive: function archive() {
      var _this11 = this;
      if (!window.confirm(this.$t("workflow.confirm.archive"))) {
        return;
      }
      return this.run(function () {
        return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].archive(_this11.project.slug, _this11.workflow.id);
      }, "archived");
    },
    destroy: function destroy() {
      var _this12 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              if (window.confirm(_this12.$t("workflow.confirm.delete"))) {
                _context7.next = 2;
                break;
              }
              return _context7.abrupt("return");
            case 2:
              _this12.busy = true;
              _context7.prev = 3;
              _context7.next = 6;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].destroy(_this12.project.slug, _this12.workflow.id);
            case 6:
              _this12.$emit("deleted");
              _context7.next = 13;
              break;
            case 9:
              _context7.prev = 9;
              _context7.t0 = _context7["catch"](3);
              flashError({
                title: _this12.$t("workflow.title"),
                body: _this12.$t("workflow.error.delete")
              });
              _this12.busy = false;
            case 13:
            case "end":
              return _context7.stop();
          }
        }, _callee7, null, [[3, 9]]);
      }))();
    },
    /**
     * Runs a request that changes the state of the workflow and applies the
     * answer. A refusal (422) lists what must be fixed first.
     */
    run: function run(request, done) {
      var _this13 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee8() {
        var _yield$request, data, response;
        return _regeneratorRuntime().wrap(function _callee8$(_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              _this13.busy = true;
              _context8.prev = 1;
              _context8.next = 4;
              return request();
            case 4:
              _yield$request = _context8.sent;
              data = _yield$request.data;
              _this13.status = data.status;
              _this13.version = data.version;
              _this13.unpublished = data.unpublished_changes;
              _this13.errors = [];
              _this13.checked = false;
              flash({
                title: _this13.$t("workflow.title"),
                body: _this13.$t("workflow.done.".concat(done))
              });
              _this13.$emit("changed");
              _context8.next = 19;
              break;
            case 15:
              _context8.prev = 15;
              _context8.t0 = _context8["catch"](1);
              response = _context8.t0.response;
              if (response && response.status === 422 && Array.isArray(response.data.errors)) {
                _this13.errors = problemsOf(response.data);
                _this13.checked = true;
                _this13.refreshProblems();
              } else {
                flashError({
                  title: _this13.$t("workflow.title"),
                  body: _this13.$t("workflow.error.save")
                });
              }
            case 19:
              _context8.prev = 19;
              _this13.busy = false;
              return _context8.finish(19);
            case 22:
            case "end":
              return _context8.stop();
          }
        }, _callee8, null, [[1, 15, 19, 22]]);
      }))();
    }
  },
  watch: {
    graph: {
      deep: true,
      handler: function handler() {
        this.scheduleSave();
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/project/workflow */ "./resources/js/apis/project/workflow.js");
/* harmony import */ var _ExecutionSteps_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ExecutionSteps.vue */ "./resources/js/components/workflow/ExecutionSteps.vue");
/* harmony import */ var _utils_workflow_format__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/utils/workflow-format */ "./resources/js/utils/workflow-format.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }




/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    ExecutionSteps: _ExecutionSteps_vue__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  props: {
    // Start on the executions of this workflow
    workflowId: {
      type: [Number, String],
      "default": ""
    }
  },
  data: function data() {
    return {
      days: 7,
      stats: null,
      loadingStats: false,
      filters: {
        workflow: this.workflowId || "",
        status: ""
      },
      rows: [],
      page: 1,
      hasMore: false,
      loadingList: false,
      detail: null,
      cancelling: false
    };
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_3__.mapGetters)(["project", "can"])), {}, {
    canCancel: function canCancel() {
      return this.can("all.project.workflow.execute");
    },
    workflowOptions: function workflowOptions() {
      return this.stats ? this.stats.workflows : [];
    },
    /**
     * Sums over the workflows of the figures
     */
    totals: function totals() {
      var _this = this;
      var sum = function sum(path) {
        return _this.stats ? _this.stats.workflows.reduce(function (total, w) {
          return total + path(w);
        }, 0) : 0;
      };
      return {
        waiting: sum(function (w) {
          return w.current.waiting + w.current.pending + w.current.running;
        }),
        completed: sum(function (w) {
          return w.period.completed;
        }),
        failed: sum(function (w) {
          return w.period.failed;
        })
      };
    },
    stepTotal: function stepTotal() {
      return this.stats ? Object.values(this.stats.steps).reduce(function (total, byStatus) {
        return total + Object.values(byStatus).reduce(function (a, b) {
          return a + b;
        }, 0);
      }, 0) : 0;
    }
  }),
  created: function created() {
    this.loadStats();
    this.loadList(true);
  },
  methods: {
    cancellable: _utils_workflow_format__WEBPACK_IMPORTED_MODULE_2__.cancellable,
    dateTime: _utils_workflow_format__WEBPACK_IMPORTED_MODULE_2__.dateTime,
    duration: _utils_workflow_format__WEBPACK_IMPORTED_MODULE_2__.duration,
    loadStats: function loadStats() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$WorkflowServic, data;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this2.loadingStats = true;
              _context.prev = 1;
              _context.next = 4;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].stats(_this2.project.slug, _this2.days);
            case 4:
              _yield$WorkflowServic = _context.sent;
              data = _yield$WorkflowServic.data;
              _this2.stats = data;
              _context.next = 12;
              break;
            case 9:
              _context.prev = 9;
              _context.t0 = _context["catch"](1);
              flashError({
                title: _this2.$t("workflow.title"),
                body: _this2.$t("workflow.error.load")
              });
            case 12:
              _context.prev = 12;
              _this2.loadingStats = false;
              return _context.finish(12);
            case 15:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1, 9, 12, 15]]);
      }))();
    },
    /**
     * @param {boolean} reset start again from the first page (filters changed)
     */
    loadList: function loadList(reset) {
      var _this3 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$WorkflowServic2, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              if (reset) {
                _this3.page = 1;
                _this3.rows = [];
                _this3.detail = null;
              }
              _this3.loadingList = true;
              _context2.prev = 2;
              _context2.next = 5;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].executions(_this3.project.slug, {
                workflow: _this3.filters.workflow || undefined,
                status: _this3.filters.status || undefined,
                page: _this3.page
              });
            case 5:
              _yield$WorkflowServic2 = _context2.sent;
              data = _yield$WorkflowServic2.data;
              _this3.rows = [].concat(_toConsumableArray(_this3.rows), _toConsumableArray(data.data));
              _this3.hasMore = data.has_more;
              _this3.page = data.current_page + 1;
              _context2.next = 15;
              break;
            case 12:
              _context2.prev = 12;
              _context2.t0 = _context2["catch"](2);
              flashError({
                title: _this3.$t("workflow.title"),
                body: _this3.$t("workflow.error.load")
              });
            case 15:
              _context2.prev = 15;
              _this3.loadingList = false;
              return _context2.finish(15);
            case 18:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[2, 12, 15, 18]]);
      }))();
    },
    refresh: function refresh() {
      this.loadStats();
      this.loadList(true);
    },
    open: function open(row) {
      var _this4 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var _yield$WorkflowServic3, data;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _context3.prev = 0;
              _context3.next = 3;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].execution(_this4.project.slug, row.id);
            case 3:
              _yield$WorkflowServic3 = _context3.sent;
              data = _yield$WorkflowServic3.data;
              _this4.detail = data;
              _context3.next = 11;
              break;
            case 8:
              _context3.prev = 8;
              _context3.t0 = _context3["catch"](0);
              flashError({
                title: _this4.$t("workflow.title"),
                body: _this4.$t("workflow.error.load")
              });
            case 11:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[0, 8]]);
      }))();
    },
    cancel: function cancel() {
      var _this5 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        var _yield$WorkflowServic4, data;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              if (window.confirm(_this5.$t("workflow.monitor.confirm_cancel"))) {
                _context4.next = 2;
                break;
              }
              return _context4.abrupt("return");
            case 2:
              _this5.cancelling = true;
              _context4.prev = 3;
              _context4.next = 6;
              return _apis_project_workflow__WEBPACK_IMPORTED_MODULE_0__["default"].cancelExecution(_this5.project.slug, _this5.detail.id);
            case 6:
              _yield$WorkflowServic4 = _context4.sent;
              data = _yield$WorkflowServic4.data;
              _this5.detail = data;
              _this5.rows = _this5.rows.map(function (row) {
                return row.id === data.id ? _objectSpread(_objectSpread({}, row), {}, {
                  status: data.status,
                  error: data.error
                }) : row;
              });
              _this5.loadStats();
              _context4.next = 16;
              break;
            case 13:
              _context4.prev = 13;
              _context4.t0 = _context4["catch"](3);
              flashError({
                title: _this5.$t("workflow.title"),
                body: _this5.$t("workflow.monitor.cannot_cancel")
              });
            case 16:
              _context4.prev = 16;
              _this5.cancelling = false;
              return _context4.finish(16);
            case 19:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[3, 13, 16, 19]]);
      }))();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }

var _hoisted_1 = ["textContent"];
var _hoisted_2 = {
  "class": "hc-wfc-head"
};
var _hoisted_3 = ["value", "disabled"];
var _hoisted_4 = ["textContent"];
var _hoisted_5 = ["textContent"];
var _hoisted_6 = ["title"];
var _hoisted_7 = ["textContent"];
var _hoisted_8 = {
  "class": "hc-wfc-row"
};
var _hoisted_9 = ["value", "disabled", "onChange"];
var _hoisted_10 = ["value", "textContent"];
var _hoisted_11 = ["textContent"];
var _hoisted_12 = ["textContent"];
var _hoisted_13 = {
  "class": "hc-wfc-row"
};
var _hoisted_14 = ["value", "disabled", "onChange"];
var _hoisted_15 = ["value", "textContent"];
var _hoisted_16 = ["value", "textContent"];
var _hoisted_17 = ["value", "disabled", "onChange"];
var _hoisted_18 = ["textContent"];
var _hoisted_19 = ["textContent"];
var _hoisted_20 = ["title", "onClick"];
var _hoisted_21 = {
  key: 2,
  "class": "hc-wfc-add"
};
var _hoisted_22 = {
  key: 0,
  "class": "hc-wfc-addwrap"
};
var _hoisted_23 = ["textContent"];
var _hoisted_24 = {
  "class": "hc-wfc-addwrap"
};
var _hoisted_25 = ["textContent"];
var _hoisted_26 = {
  key: 1,
  "class": "hc-wfc-addwrap"
};
var _hoisted_27 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_help_tip = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("help-tip");
  var _component_condition_group = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("condition-group", true);
  var _component_field_input = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("field-input");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfc", 'depth-' + $props.depth])
  }, [$props.depth === 1 ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-wfc-intro",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.intro'))
  }, null, 8 /* PROPS */, _hoisted_1)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $props.modelValue.op || 'and',
    disabled: $props.readOnly,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return $options.update({
        op: $event.target.value
      });
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "and",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.all'))
  }, null, 8 /* PROPS */, _hoisted_4), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "or",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.any'))
  }, null, 8 /* PROPS */, _hoisted_5)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.condition_op"
  }), $props.depth > 1 && !$props.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 0,
    type: "button",
    "class": "hc-wfc-remove",
    title: _ctx.$t('workflow.condition.remove_group'),
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('remove');
    })
  }, "×", 8 /* PROPS */, _hoisted_6)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]), !$options.items.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 1,
    "class": "hc-wfc-empty",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.empty'))
  }, null, 8 /* PROPS */, _hoisted_7)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.items, function (item, index) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: index,
      "class": "hc-wfc-item"
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" A group inside the group "), item.items ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_condition_group, {
      key: 0,
      "model-value": item,
      subject: $props.subject,
      catalog: $props.catalog,
      "read-only": $props.readOnly,
      depth: $props.depth + 1,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setItem(index, $event);
      },
      onRemove: function onRemove($event) {
        return $options.removeItem(index);
      }
    }, null, 8 /* PROPS */, ["model-value", "subject", "catalog", "read-only", "depth", "onUpdate:modelValue", "onRemove"])) : item.check ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 1
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Date / time check "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_8, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
      value: item.check,
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return $options.setItem(index, $options.newCheck($event.target.value));
      }
    }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.catalog.conditions.checks, function (check) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
        key: check.name,
        value: check.name,
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.check.' + check.name))
      }, null, 8 /* PROPS */, _hoisted_10);
    }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_9), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.checkFields(item.check), function (field) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
        key: field.key,
        "class": "hc-wfc-field"
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.field.' + field.key))
      }, null, 8 /* PROPS */, _hoisted_11), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
        "model-value": item[field.key],
        type: field.type,
        "read-only": $props.readOnly,
        "onUpdate:modelValue": function onUpdateModelValue($event) {
          return $options.setItem(index, _objectSpread(_objectSpread({}, item), {}, _defineProperty({}, field.key, $event)));
        }
      }, null, 8 /* PROPS */, ["model-value", "type", "read-only", "onUpdate:modelValue"])]);
    }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
      "class": "hc-wfc-help",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.timezone_help'))
    }, null, 8 /* PROPS */, _hoisted_12)])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 2
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Criterion on the prospect "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_13, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
      value: $options.filterName(item),
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return $options.setItem(index, $options.newFilter($event.target.value));
      }
    }, [!$options.known($options.filterName(item)) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: 0,
      value: $options.filterName(item),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.filterName(item))
    }, null, 8 /* PROPS */, _hoisted_15)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.filters, function (filter) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
        key: filter.name,
        value: filter.name,
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.filter.' + filter.name))
      }, null, 8 /* PROPS */, _hoisted_16);
    }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_14), $options.known($options.filterName(item)) && $options.filterType(item) !== 'boolean' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_field_input, {
      key: 0,
      "model-value": item.filters[$options.filterName(item)],
      type: $options.filterType(item),
      "read-only": $props.readOnly,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setItem(index, {
          filters: _defineProperty({}, $options.filterName(item), $event)
        });
      }
    }, null, 8 /* PROPS */, ["model-value", "type", "read-only", "onUpdate:modelValue"])) : $options.known($options.filterName(item)) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("select", {
      key: 1,
      value: item.filters[$options.filterName(item)] ? 'true' : 'false',
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return $options.setItem(index, {
          filters: _defineProperty({}, $options.filterName(item), $event.target.value === 'true')
        });
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
      value: "true",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.yes'))
    }, null, 8 /* PROPS */, _hoisted_18), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
      value: "false",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.no'))
    }, null, 8 /* PROPS */, _hoisted_19)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_17)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)), !$props.readOnly && !item.items ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
      key: 3,
      type: "button",
      "class": "hc-wfc-remove",
      title: _ctx.$t('workflow.condition.remove'),
      onClick: function onClick($event) {
        return $options.removeItem(index);
      }
    }, "×", 8 /* PROPS */, _hoisted_20)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
  }), 128 /* KEYED_FRAGMENT */)), !$props.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_21, [$options.filters.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_22, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return $options.add($options.newFilter($options.filters[0].name));
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)('+ ' + _ctx.$t('workflow.condition.add_filter'))
  }, null, 8 /* PROPS */, _hoisted_23), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.add_filter"
  })])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_24, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return $options.add($options.newCheck('work_hours'));
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)('+ ' + _ctx.$t('workflow.condition.add_check'))
  }, null, 8 /* PROPS */, _hoisted_25), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.add_check"
  })]), $props.depth < $props.catalog.conditions.max_depth ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_26, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return $options.add({
        op: 'and',
        items: []
      });
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)('+ ' + _ctx.$t('workflow.condition.add_group'))
  }, null, 8 /* PROPS */, _hoisted_27), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.add_group"
  })])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 2 /* CLASS */);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764 ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-wfe"
};
var _hoisted_2 = {
  "class": "hc-wfe-head"
};
var _hoisted_3 = ["textContent"];
var _hoisted_4 = ["textContent"];
var _hoisted_5 = ["textContent"];
var _hoisted_6 = {
  key: 0,
  "class": "hc-wfe-waiting"
};
var _hoisted_7 = ["textContent"];
var _hoisted_8 = {
  "class": "hc-wfe-steps"
};
var _hoisted_9 = ["title", "textContent"];
var _hoisted_10 = {
  "class": "hc-wfe-step"
};
var _hoisted_11 = {
  "class": "hc-wfe-title"
};
var _hoisted_12 = ["textContent"];
var _hoisted_13 = ["textContent"];
var _hoisted_14 = ["textContent"];
var _hoisted_15 = ["textContent"];
var _hoisted_16 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_router_link = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("router-link");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.execution.workflow.name || _ctx.$t('workflow.monitor.deleted_workflow'))
  }, null, 8 /* PROPS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-badge", 'exec-' + $props.execution.status]),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.exec_status.' + $props.execution.status))
  }, null, 10 /* CLASS, PROPS */, _hoisted_4), $options.prospectLink ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_router_link, {
    key: 0,
    "class": "hc-wfe-subject",
    to: $options.prospectLink,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.subjectLabel)
  }, null, 8 /* PROPS */, ["to", "textContent"])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 1,
    "class": "hc-wfe-subject",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.subjectLabel)
  }, null, 8 /* PROPS */, _hoisted_5))]), $options.waiting ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.monitor." + $options.waiting, {
    event: $props.execution.waiting_event ? _ctx.$t("workflow.event." + $props.execution.waiting_event) : "",
    date: $options.dateTime($props.execution.resume_at)
  })) + " ", 1 /* TEXT */), $props.execution.waiting_for && $props.execution.waiting_for.name ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" (" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.execution.waiting_for.name) + ")", 1 /* TEXT */)], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.execution.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 1,
    "class": "hc-wfe-error",
    role: "alert",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.execution.error)
  }, null, 8 /* PROPS */, _hoisted_7)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("ol", _hoisted_8, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.execution.steps, function (step) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: step.node_id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(step.status)
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-wfe-icon",
      title: _ctx.$t('workflow.step_status.' + step.status),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.stepIcon(step.status))
    }, null, 8 /* PROPS */, _hoisted_9), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_10, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_11, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.title(step)) + " ", 1 /* TEXT */), step.branch ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 0,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfe-branch", step.branch]),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.' + step.branch))
    }, null, 10 /* CLASS, PROPS */, _hoisted_12)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.details(step), function (line) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
        key: line,
        "class": "hc-wfe-detail",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(line)
      }, null, 8 /* PROPS */, _hoisted_13);
    }), 128 /* KEYED_FRAGMENT */)), step.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: 0,
      "class": "hc-wfe-error",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(step.error)
    }, null, 8 /* PROPS */, _hoisted_14)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-wfe-time",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.dateTime(step.finished_at || step.started_at))
    }, null, 8 /* PROPS */, _hoisted_15)])], 2 /* CLASS */);
  }), 128 /* KEYED_FRAGMENT */)), !$props.execution.steps.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
    key: 0,
    "class": "hc-wfe-none",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.no_step'))
  }, null, 8 /* PROPS */, _hoisted_16)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-wff"
};
var _hoisted_2 = {
  key: 0,
  "class": "hc-wff-check"
};
var _hoisted_3 = ["checked", "disabled"];
var _hoisted_4 = ["textContent"];
var _hoisted_5 = ["value", "disabled"];
var _hoisted_6 = ["value", "disabled"];
var _hoisted_7 = {
  key: 0,
  "class": "hc-wff-variables"
};
var _hoisted_8 = ["textContent"];
var _hoisted_9 = ["onClick", "textContent"];
var _hoisted_10 = ["value", "disabled"];
var _hoisted_11 = ["textContent"];
var _hoisted_12 = ["value", "textContent"];
var _hoisted_13 = {
  "class": "hc-wff-list"
};
var _hoisted_14 = ["checked", "disabled", "onChange"];
var _hoisted_15 = ["textContent"];
var _hoisted_16 = ["textContent"];
var _hoisted_17 = ["value", "disabled"];
var _hoisted_18 = ["textContent"];
var _hoisted_19 = ["value", "textContent"];
var _hoisted_20 = ["value", "textContent"];
var _hoisted_21 = ["textContent"];
var _hoisted_22 = ["value", "disabled"];
var _hoisted_23 = ["textContent"];
var _hoisted_24 = ["textContent"];
var _hoisted_25 = ["value", "disabled"];
var _hoisted_26 = ["value", "disabled"];
var _hoisted_27 = {
  key: 9,
  "class": "hc-wff-days"
};
var _hoisted_28 = ["checked", "disabled", "onChange"];
var _hoisted_29 = ["textContent"];
var _hoisted_30 = ["value", "disabled"];
var _hoisted_31 = ["textContent"];
var _hoisted_32 = {
  key: 12,
  "class": "hc-wff-create"
};
var _hoisted_33 = ["textContent"];
var _hoisted_34 = ["placeholder"];
var _hoisted_35 = ["disabled", "textContent"];
var _hoisted_36 = ["textContent"];
var _hoisted_37 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Yes / no "), $props.type === 'boolean' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "checkbox",
    checked: !!$props.modelValue,
    disabled: $props.readOnly,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.checked);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.yes'))
  }, null, 8 /* PROPS */, _hoisted_4)])) : $props.type === 'number' || $props.type === 'minutes' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Numbers "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "number",
    min: "0",
    value: $props.modelValue,
    disabled: $props.readOnly,
    onInput: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value));
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_5)], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : $props.type === 'text' || $props.type === 'html' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 2
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Texts "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("textarea", {
    ref: "text",
    rows: "5",
    maxlength: "5000",
    value: $props.modelValue,
    disabled: $props.readOnly,
    onInput: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_6), !$props.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.variables'))
  }, null, 8 /* PROPS */, _hoisted_8), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.variableList, function (variable) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
      key: variable,
      type: "button",
      onClick: function onClick($event) {
        return $options.insertVariable(variable);
      },
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(variable)
    }, null, 8 /* PROPS */, _hoisted_9);
  }), 128 /* KEYED_FRAGMENT */))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : $options.isSingle ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 3
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" One or several items of the project "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $props.modelValue,
    disabled: $props.readOnly,
    onChange: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value));
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.emptyLabel)
  }, null, 8 /* PROPS */, _hoisted_11), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.options, function (option) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: option.id,
      value: option.id,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(option.label)
    }, null, 8 /* PROPS */, _hoisted_12);
  }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_10)], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : $options.isMultiple ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 4
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Several items: a click on a line ticks it, no key to hold "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_13, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.options, function (option) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("label", {
      key: option.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wff-item", {
        on: $options.isChosen(option.id)
      }])
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
      type: "checkbox",
      checked: $options.isChosen(option.id),
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return $options.toggleId(option.id);
      }
    }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_14), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(option.label)
    }, null, 8 /* PROPS */, _hoisted_15)], 2 /* CLASS */);
  }), 128 /* KEYED_FRAGMENT */)), !$options.emptyList ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-wff-count",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.chosen_count', {
      count: ($props.modelValue || []).length
    }))
  }, null, 8 /* PROPS */, _hoisted_16)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : $props.type === 'provider' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 5
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" An operator of the project "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $props.modelValue,
    disabled: $props.readOnly,
    onChange: _cache[4] || (_cache[4] = function ($event) {
      return $options.chooseProvider($event.target.value);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.choose'))
  }, null, 8 /* PROPS */, _hoisted_18), $props.modelValue && !$data.providers.some(function (p) {
    return p.ref === $props.modelValue;
  }) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
    key: 0,
    value: $props.modelValue,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.provider_missing'))
  }, null, 8 /* PROPS */, _hoisted_19)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.providers, function (provider) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: provider.ref,
      value: provider.ref,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.label)
    }, null, 8 /* PROPS */, _hoisted_20);
  }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_17), !$data.providersLoading && !$data.providers.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-wff-warn",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.no_provider.' + $props.field.capability))
  }, null, 8 /* PROPS */, _hoisted_21)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : $props.type === 'on_error' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 6
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" What happens when the step fails "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $props.modelValue || 'fail',
    disabled: $props.readOnly,
    onChange: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "fail",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.on_error.fail'))
  }, null, 8 /* PROPS */, _hoisted_23), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "continue",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.on_error.continue'))
  }, null, 8 /* PROPS */, _hoisted_24)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_22)], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */)) : $props.type === 'time' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 7,
    type: "time",
    value: $props.modelValue,
    disabled: $props.readOnly,
    onInput: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_25)) : $props.type === 'date' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 8,
    type: "date",
    value: $props.modelValue,
    disabled: $props.readOnly,
    onInput: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value || null);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_26)) : $props.type === 'weekdays' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_27, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(7, function (day) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
      key: day,
      "class": "hc-wff-check"
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
      type: "checkbox",
      checked: ($props.modelValue || $data.allDays).includes(day),
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return $options.toggleDay(day);
      }
    }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_28), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.weekday.' + day))
    }, null, 8 /* PROPS */, _hoisted_29)]);
  }), 64 /* STABLE_FRAGMENT */))])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("input", {
    key: 10,
    type: "text",
    value: $props.modelValue,
    disabled: $props.readOnly,
    onInput: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.$emit('update:modelValue', $event.target.value);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_30)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Not part of the chain above: it would take the place of its last \"else\" "), $options.isLabels && !$data.labelsLoaded ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 11,
    "class": "hc-wff-count",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.loading'))
  }, null, 8 /* PROPS */, _hoisted_31)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" A channel is needed to send emails: offer to create it right here "), $options.emptyList && $props.type === 'thread' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_32, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "hc-wff-warn",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.thread_create.why'))
  }, null, 8 /* PROPS */, _hoisted_33), $options.canAddThread && !$props.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
      return $data.newThreadName = $event;
    }),
    type: "text",
    maxlength: "50",
    placeholder: _ctx.$t('workflow.thread_create.placeholder'),
    onKeydown: _cache[10] || (_cache[10] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)((0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.createThread && $options.createThread.apply($options, arguments);
    }, ["prevent"]), ["enter"]))
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_34), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.newThreadName]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    disabled: !$data.newThreadName.trim() || $data.creatingThread,
    onClick: _cache[11] || (_cache[11] = function () {
      return $options.createThread && $options.createThread.apply($options, arguments);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.thread_create.button'))
  }, null, 8 /* PROPS */, _hoisted_35)], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 1,
    "class": "hc-wff-count",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.thread_create.ask_admin'))
  }, null, 8 /* PROPS */, _hoisted_36))])) : $options.emptyList ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 13,
    "class": "hc-wff-warn",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.empty_list.' + $props.type))
  }, null, 8 /* PROPS */, _hoisted_37)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716":
/*!**************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716 ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  key: 0,
  "class": "hc-tip"
};
var _hoisted_2 = ["title"];
var _hoisted_3 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return $options.content ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    ref: "button",
    type: "button",
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-tip-button", {
      open: $data.open
    }]),
    title: _ctx.$t('workflow.tip.what'),
    onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.toggle && $options.toggle.apply($options, arguments);
    }, ["stop", "prevent"]))
  }, "i", 10 /* CLASS, PROPS */, _hoisted_2), $data.open ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 0,
    "class": "hc-tip-pop",
    role: "tooltip",
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($data.position),
    onClick: _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {}, ["stop"])),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.content)
  }, null, 12 /* STYLE, PROPS */, _hoisted_3)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }

var _hoisted_1 = {
  "class": "hc-wfn"
};
var _hoisted_2 = {
  "class": "hc-wfn-head"
};
var _hoisted_3 = ["textContent"];
var _hoisted_4 = ["title"];
var _hoisted_5 = {
  "class": "hc-wfn-label"
};
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["value", "placeholder", "disabled"];
var _hoisted_8 = {
  "class": "hc-wfn-label"
};
var _hoisted_9 = ["textContent"];
var _hoisted_10 = ["value", "disabled"];
var _hoisted_11 = ["value", "textContent"];
var _hoisted_12 = ["textContent"];
var _hoisted_13 = {
  "class": "hc-wfn-label"
};
var _hoisted_14 = ["textContent"];
var _hoisted_15 = {
  "class": "hc-wfn-label"
};
var _hoisted_16 = ["textContent"];
var _hoisted_17 = {
  "class": "hc-wfn-label"
};
var _hoisted_18 = ["textContent"];
var _hoisted_19 = {
  "class": "hc-wfn-label"
};
var _hoisted_20 = ["textContent"];
var _hoisted_21 = ["value", "disabled"];
var _hoisted_22 = ["textContent"];
var _hoisted_23 = ["value", "textContent"];
var _hoisted_24 = ["label"];
var _hoisted_25 = ["value", "textContent"];
var _hoisted_26 = ["textContent"];
var _hoisted_27 = ["textContent"];
var _hoisted_28 = ["textContent"];
var _hoisted_29 = {
  "class": "hc-wfn-label"
};
var _hoisted_30 = {
  key: 0,
  "class": "hc-wfn-required"
};
var _hoisted_31 = {
  "class": "hc-wfn-label"
};
var _hoisted_32 = ["textContent"];
var _hoisted_33 = ["value", "disabled"];
var _hoisted_34 = ["textContent"];
var _hoisted_35 = ["textContent"];
var _hoisted_36 = ["textContent"];
var _hoisted_37 = {
  key: 0,
  "class": "hc-wfn-duration"
};
var _hoisted_38 = ["textContent"];
var _hoisted_39 = ["textContent"];
var _hoisted_40 = ["textContent"];
var _hoisted_41 = ["textContent"];
var _hoisted_42 = ["value", "disabled"];
var _hoisted_43 = ["textContent"];
var _hoisted_44 = {
  "class": "hc-wfn-label"
};
var _hoisted_45 = ["textContent"];
var _hoisted_46 = ["value", "disabled"];
var _hoisted_47 = ["value", "textContent"];
var _hoisted_48 = {
  "class": "hc-wfn-label"
};
var _hoisted_49 = ["textContent"];
var _hoisted_50 = {
  "class": "hc-wfn-duration"
};
var _hoisted_51 = ["textContent"];
var _hoisted_52 = ["textContent"];
var _hoisted_53 = {
  "class": "hc-wfn-label"
};
var _hoisted_54 = ["textContent"];
var _hoisted_55 = ["textContent"];
var _hoisted_56 = ["value", "disabled", "onChange"];
var _hoisted_57 = ["textContent"];
var _hoisted_58 = ["value", "disabled", "textContent"];
var _hoisted_59 = ["title", "textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_help_tip = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("help-tip");
  var _component_condition_group = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("condition-group");
  var _component_field_input = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("field-input");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("aside", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node.' + $props.node.type))
  }, null, 8 /* PROPS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": "hc-wfn-close",
    title: _ctx.$t('workflow.close'),
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('close');
    })
  }, "×", 8 /* PROPS */, _hoisted_4)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_5, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.step_name'))
  }, null, 8 /* PROPS */, _hoisted_6), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.step_name"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "text",
    maxlength: "120",
    value: $props.node.name,
    placeholder: _ctx.$t('workflow.step_name_placeholder'),
    disabled: $props.readOnly,
    onInput: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('update', {
        name: $event.target.value
      });
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_7), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" When "), $props.node.type === 'trigger' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_8, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node.trigger'))
  }, null, 8 /* PROPS */, _hoisted_9), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.trigger"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $options.config.trigger,
    disabled: $props.readOnly,
    onChange: _cache[2] || (_cache[2] = function ($event) {
      return $options.changeTrigger($event.target.value);
    })
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.catalog.triggers, function (trigger) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: trigger.type,
      value: trigger.type,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.trigger.' + $options.slug(trigger.type)))
    }, null, 8 /* PROPS */, _hoisted_11);
  }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_10), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "hc-wfn-help",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.trigger_help.' + $options.slug($options.config.trigger)))
  }, null, 8 /* PROPS */, _hoisted_12), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.triggerFields, function (field) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: field.key
    }, [field.type === 'condition' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 0
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_13, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.trigger_filter'))
    }, null, 8 /* PROPS */, _hoisted_14), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
      k: "workflow.trigger_filter_help"
    })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_condition_group, {
      "model-value": $options.tree,
      subject: $options.subject,
      catalog: $props.catalog,
      "read-only": $props.readOnly,
      "onUpdate:modelValue": $options.setTree
    }, null, 8 /* PROPS */, ["model-value", "subject", "catalog", "read-only", "onUpdate:modelValue"])], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 1
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_15, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.field.' + field.key))
    }, null, 8 /* PROPS */, _hoisted_16), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
      text: $options.help(field) || ''
    }, null, 8 /* PROPS */, ["text"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
      "model-value": $options.config[field.key],
      type: field.type,
      field: field,
      "read-only": $props.readOnly,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setConfig(_defineProperty({}, field.key, $event));
      }
    }, null, 8 /* PROPS */, ["model-value", "type", "field", "read-only", "onUpdate:modelValue"])], 64 /* STABLE_FRAGMENT */))], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */))], 64 /* STABLE_FRAGMENT */)) : $props.node.type === 'condition' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" If "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_17, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node_help.condition'))
  }, null, 8 /* PROPS */, _hoisted_18)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_condition_group, {
    "model-value": $options.tree,
    subject: $options.subject,
    catalog: $props.catalog,
    "read-only": $props.readOnly,
    "onUpdate:modelValue": $options.setTree
  }, null, 8 /* PROPS */, ["model-value", "subject", "catalog", "read-only", "onUpdate:modelValue"])], 64 /* STABLE_FRAGMENT */)) : $props.node.type === 'action' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 2
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Do "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_19, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.field.action'))
  }, null, 8 /* PROPS */, _hoisted_20), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.action"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $options.config.action || '',
    disabled: $props.readOnly,
    onChange: _cache[3] || (_cache[3] = function ($event) {
      return $options.chooseAction($event.target.value);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.choose'))
  }, null, 8 /* PROPS */, _hoisted_22), $options.config.action && !$options.actionDef ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
    key: 0,
    value: $options.config.action,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.actionLabel($options.config.action))
  }, null, 8 /* PROPS */, _hoisted_23)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.actionGroups, function (group) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("optgroup", {
      key: group.category,
      label: _ctx.$t('workflow.category.' + group.category)
    }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(group.actions, function (action) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
        key: action.type,
        value: action.type,
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action_type.' + $options.slug(action.type)))
      }, null, 8 /* PROPS */, _hoisted_25);
    }), 128 /* KEYED_FRAGMENT */))], 8 /* PROPS */, _hoisted_24);
  }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_21), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" An action taken over from a campaign: kept as it is, its settings are shown "), $options.config.action && !$options.actionDef ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "hc-wfn-help",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.legacy_action'))
  }, null, 8 /* PROPS */, _hoisted_26), $options.hasValue ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("pre", {
    key: 0,
    "class": "hc-wfn-legacy",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(JSON.stringify($options.config.value, null, 2))
  }, null, 8 /* PROPS */, _hoisted_27)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.actionDef ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [$options.actionDef.danger ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-wfn-danger",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.danger'))
  }, null, 8 /* PROPS */, _hoisted_28)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.actionDef.fields, function (field) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: field.key
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_29, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.field." + $options.fieldName(field))) + " ", 1 /* TEXT */), field.required ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_30, "*")) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
      text: $options.help(field) || ''
    }, null, 8 /* PROPS */, ["text"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
      "model-value": $options.getPath(field.key),
      type: field.type,
      field: field,
      variables: $props.catalog.variables,
      "read-only": $props.readOnly,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setPath(field.key, $event);
      },
      onProviderLabel: _cache[4] || (_cache[4] = function ($event) {
        return $options.setConfig({
          provider_label: $event
        });
      })
    }, null, 8 /* PROPS */, ["model-value", "type", "field", "variables", "read-only", "onUpdate:modelValue"])], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */))], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : $props.node.type === 'wait' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 3
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Wait "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_31, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.mode'))
  }, null, 8 /* PROPS */, _hoisted_32), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.wait_mode"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $options.waitMode,
    disabled: $props.readOnly,
    onChange: _cache[5] || (_cache[5] = function ($event) {
      return $options.setWaitMode($event.target.value);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "duration",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.duration'))
  }, null, 8 /* PROPS */, _hoisted_34), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "time",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.until_time'))
  }, null, 8 /* PROPS */, _hoisted_35), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "date",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.until_date'))
  }, null, 8 /* PROPS */, _hoisted_36)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_33), $options.waitMode === 'duration' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_37, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(['days', 'hours', 'minutes'], function (unit) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
      key: unit
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.' + unit))
    }, null, 8 /* PROPS */, _hoisted_38), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
      "model-value": $options.config[unit],
      type: "number",
      "read-only": $props.readOnly,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setConfig(_defineProperty({}, unit, $event));
      }
    }, null, 8 /* PROPS */, ["model-value", "read-only", "onUpdate:modelValue"])]);
  }), 64 /* STABLE_FRAGMENT */))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.waitMode === 'time' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
    "class": "hc-wfn-label",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.time'))
  }, null, 8 /* PROPS */, _hoisted_39), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
    "model-value": $options.config.until_time,
    type: "time",
    "read-only": $props.readOnly,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return $options.setConfig({
        until_time: $event
      });
    })
  }, null, 8 /* PROPS */, ["model-value", "read-only"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
    "class": "hc-wfn-label",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.only_days'))
  }, null, 8 /* PROPS */, _hoisted_40), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
    "model-value": $options.config.days,
    type: "weekdays",
    "read-only": $props.readOnly,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return $options.setConfig({
        days: $event
      });
    })
  }, null, 8 /* PROPS */, ["model-value", "read-only"])], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.waitMode === 'date' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 2
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
    "class": "hc-wfn-label",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.date'))
  }, null, 8 /* PROPS */, _hoisted_41), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "datetime-local",
    value: ($options.config.until_date || '').replace(' ', 'T'),
    disabled: $props.readOnly,
    onInput: _cache[8] || (_cache[8] = function ($event) {
      return $options.setConfig({
        until_date: $event.target.value.replace('T', ' ')
      });
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_42)], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.waitMode !== 'duration' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 3,
    "class": "hc-wfn-help",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.condition.timezone_help'))
  }, null, 8 /* PROPS */, _hoisted_43)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : $props.node.type === 'wait_event' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 4
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Wait for an event "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_44, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.field.event'))
  }, null, 8 /* PROPS */, _hoisted_45), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.wait_event"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    value: $options.config.event,
    disabled: $props.readOnly,
    onChange: _cache[9] || (_cache[9] = function ($event) {
      return $options.setConfig({
        event: $event.target.value
      });
    })
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.catalog.events, function (event) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: event,
      value: event,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.event.' + event))
    }, null, 8 /* PROPS */, _hoisted_47);
  }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_46), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_48, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.max'))
  }, null, 8 /* PROPS */, _hoisted_49), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.wait.event_help"
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_50, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(['days', 'hours', 'minutes'], function (unit) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", {
      key: unit
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.wait.' + unit))
    }, null, 8 /* PROPS */, _hoisted_51), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_field_input, {
      "model-value": $options.config[unit],
      type: "number",
      "read-only": $props.readOnly,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return $options.setConfig(_defineProperty({}, unit, $event));
      }
    }, null, 8 /* PROPS */, ["model-value", "read-only", "onUpdate:modelValue"])]);
  }), 64 /* STABLE_FRAGMENT */))])], 64 /* STABLE_FRAGMENT */)) : $props.node.type === 'end' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 5,
    "class": "hc-wfn-help",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node_help.end'))
  }, null, 8 /* PROPS */, _hoisted_52)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" What follows "), $options.outputList.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 6
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_53, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.next'))
  }, null, 8 /* PROPS */, _hoisted_54), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_help_tip, {
    k: "workflow.tip.next"
  })]), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.outputList, function (output) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: String(output.branch),
      "class": "hc-wfn-output"
    }, [output.branch ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 0,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfn-branch", output.branch]),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.' + output.branch))
    }, null, 10 /* CLASS, PROPS */, _hoisted_55)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
      value: output.to || '',
      disabled: $props.readOnly,
      onChange: function onChange($event) {
        return _ctx.$emit('link', {
          branch: output.branch,
          to: $event.target.value || null
        });
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
      value: "",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.nothing'))
    }, null, 8 /* PROPS */, _hoisted_57), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.candidates, function (candidate) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
        key: candidate.id,
        value: candidate.id,
        disabled: $options.cycle(candidate.id),
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.label(candidate))
      }, null, 8 /* PROPS */, _hoisted_58);
    }), 128 /* KEYED_FRAGMENT */))], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_56)]);
  }), 128 /* KEYED_FRAGMENT */))], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), !$props.readOnly && $props.node.type !== 'trigger' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 7,
    type: "button",
    "class": "hc-wfn-delete",
    title: _ctx.$t('workflow.tip.remove_node'),
    onClick: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.$emit('remove');
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.remove_node'))
  }, null, 8 /* PROPS */, _hoisted_59)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18":
/*!***************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18 ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  id: "hc-workflow"
};
var _hoisted_2 = ["textContent"];
var _hoisted_3 = {
  id: "hc-workflow-list"
};
var _hoisted_4 = {
  "class": "hc-workflow-list-title"
};
var _hoisted_5 = ["textContent"];
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["placeholder"];
var _hoisted_8 = ["disabled"];
var _hoisted_9 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, "+", -1 /* HOISTED */);
var _hoisted_10 = {
  "class": "hc-workflow-items"
};
var _hoisted_11 = ["textContent"];
var _hoisted_12 = {
  "class": "hc-workflow-list-top"
};
var _hoisted_13 = ["textContent"];
var _hoisted_14 = ["textContent"];
var _hoisted_15 = ["textContent"];
var _hoisted_16 = {
  key: 1,
  "class": "hc-workflow-list-counts"
};
var _hoisted_17 = ["title"];
var _hoisted_18 = {
  key: 1,
  "class": "hc-workflow-templates"
};
var _hoisted_19 = ["textContent"];
var _hoisted_20 = ["disabled", "onClick"];
var _hoisted_21 = ["textContent"];
var _hoisted_22 = {
  "class": "hc-workflow-template-text"
};
var _hoisted_23 = ["textContent"];
var _hoisted_24 = ["textContent"];
var _hoisted_25 = {
  id: "hc-workflow-main"
};
var _hoisted_26 = {
  key: 0,
  "class": "hc-workflow-tabs"
};
var _hoisted_27 = ["textContent"];
var _hoisted_28 = ["textContent"];
var _hoisted_29 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_router_link = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("router-link");
  var _component_loading = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("loading");
  var _component_workflow_monitor = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("workflow-monitor");
  var _component_workflow_builder = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("workflow-builder");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [$options.mobile ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 0,
    id: "hc-workflow-mobile",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.mobile'))
  }, null, 8 /* PROPS */, _hoisted_2)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("aside", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.title'))
  }, null, 8 /* PROPS */, _hoisted_5), $data.workflows.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 0,
    "class": "hc-workflow-count",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.workflows.length)
  }, null, 8 /* PROPS */, _hoisted_6)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]), _ctx.can('all.project.workflow.add') ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("form", {
    key: 0,
    "class": "hc-workflow-new",
    onSubmit: _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
      return $options.create();
    }, ["prevent"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $data.newName = $event;
    }),
    type: "text",
    maxlength: "150",
    placeholder: _ctx.$t('workflow.new_placeholder')
  }, null, 8 /* PROPS */, _hoisted_7), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.newName]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "submit",
    disabled: !$data.newName.trim() || $data.creating
  }, [_hoisted_9, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.new")), 1 /* TEXT */)], 8 /* PROPS */, _hoisted_8)], 32 /* HYDRATE_EVENTS */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_10, [!$data.loading && !$data.workflows.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-workflow-empty",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.empty'))
  }, null, 8 /* PROPS */, _hoisted_11)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.workflows, function (workflow) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_router_link, {
      key: workflow.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-list-item", [workflow.status, {
        current: $data.current && $data.current.id === workflow.id
      }]]),
      to: {
        name: 'workflow.show',
        params: {
          project: _ctx.project.slug,
          workflow: workflow.id
        }
      }
    }, {
      "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
        return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_12, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
          "class": "hc-workflow-list-name",
          textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.name)
        }, null, 8 /* PROPS */, _hoisted_13), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
          "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-badge", workflow.status]),
          textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.status.' + workflow.status))
        }, null, 10 /* CLASS, PROPS */, _hoisted_14)]), workflow.legacy_campaign_id ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
          key: 0,
          "class": "hc-workflow-origin",
          textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.from_campaign'))
        }, null, 8 /* PROPS */, _hoisted_15)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), Object.keys($options.visibleCounts(workflow)).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_16, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.visibleCounts(workflow), function (total, status) {
          return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
            key: status,
            "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-pill", 'exec-' + status]),
            title: _ctx.$t('workflow.executions.' + status)
          }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(total) + " " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.executions." + status).toLowerCase()), 11 /* TEXT, CLASS, PROPS */, _hoisted_17);
        }), 128 /* KEYED_FRAGMENT */))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)];
      }),
      _: 2 /* DYNAMIC */
    }, 1032 /* PROPS, DYNAMIC_SLOTS */, ["class", "to"]);
  }), 128 /* KEYED_FRAGMENT */))]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Starting points: open while there is nothing yet, tucked away afterwards "), _ctx.can('all.project.workflow.add') ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_18, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": "hc-workflow-templates-toggle",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return $data.showTemplates = !$data.showTemplates;
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.templates.title'))
  }, null, 8 /* PROPS */, _hoisted_19), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-chevron", {
      open: $options.templatesOpen
    }])
  }, "›", 2 /* CLASS */)]), $options.templatesOpen ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.templateIds, function (id) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
      key: id,
      type: "button",
      "class": "hc-workflow-template",
      disabled: $data.creating,
      onClick: function onClick($event) {
        return $options.createFromTemplate(id);
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-workflow-template-icon",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.templateIcon(id))
    }, null, 8 /* PROPS */, _hoisted_21), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_22, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.template.' + id + '.name'))
    }, null, 8 /* PROPS */, _hoisted_23), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.template.' + id + '.description'))
    }, null, 8 /* PROPS */, _hoisted_24)])], 8 /* PROPS */, _hoisted_20);
  }), 128 /* KEYED_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.loading
  }, null, 8 /* PROPS */, ["loading"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_25, [$options.canMonitor ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("nav", _hoisted_26, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)({
      active: $data.tab === 'schema'
    }),
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return $data.tab = 'schema';
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.tab.schema'))
  }, null, 10 /* CLASS, PROPS */, _hoisted_27), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)({
      active: $data.tab === 'monitor'
    }),
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return $data.tab = 'monitor';
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.tab.monitor'))
  }, null, 10 /* CLASS, PROPS */, _hoisted_28)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Follow-up of the executions (of the workflow selected, else of all of them) "), $data.tab === 'monitor' && $options.canMonitor ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_workflow_monitor, {
    key: 'monitor-' + ($data.current ? $data.current.id : 0),
    "workflow-id": $data.current ? $data.current.id : ''
  }, null, 8 /* PROPS */, ["workflow-id"])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 2
  }, [!$data.current && !$data.loadingCurrent ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-workflow-hint",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.select'))
  }, null, 8 /* PROPS */, _hoisted_29)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.current && $data.catalog ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_workflow_builder, {
    key: $data.current.id,
    workflow: $data.current,
    catalog: $data.catalog,
    onChanged: $options.refreshList,
    onDeleted: $options.deleted
  }, null, 8 /* PROPS */, ["workflow", "catalog", "onChanged", "onDeleted"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.loadingCurrent
  }, null, 8 /* PROPS */, ["loading"])], 64 /* STABLE_FRAGMENT */))])], 64 /* STABLE_FRAGMENT */))]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492 ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-wfb"
};
var _hoisted_2 = {
  "class": "hc-wfb-toolbar"
};
var _hoisted_3 = ["disabled"];
var _hoisted_4 = ["textContent"];
var _hoisted_5 = {
  key: 0,
  "class": "hc-wfb-muted"
};
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["textContent"];
var _hoisted_8 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-wfb-spacer"
}, null, -1 /* HOISTED */);
var _hoisted_9 = ["disabled", "textContent"];
var _hoisted_10 = ["disabled", "textContent"];
var _hoisted_11 = {
  "class": "hc-wfb-more"
};
var _hoisted_12 = ["title"];
var _hoisted_13 = ["disabled", "title", "textContent"];
var _hoisted_14 = ["disabled", "title", "textContent"];
var _hoisted_15 = ["disabled", "textContent"];
var _hoisted_16 = ["disabled", "textContent"];
var _hoisted_17 = ["disabled", "textContent"];
var _hoisted_18 = ["disabled", "textContent"];
var _hoisted_19 = {
  key: 0,
  "class": "hc-wfb-errors",
  role: "alert"
};
var _hoisted_20 = ["textContent"];
var _hoisted_21 = ["onClick", "textContent"];
var _hoisted_22 = ["textContent"];
var _hoisted_23 = ["textContent"];
var _hoisted_24 = {
  "class": "hc-wfb-body"
};
var _hoisted_25 = {
  "class": "hc-wfb-palette"
};
var _hoisted_26 = {
  "class": "hc-wfb-section"
};
var _hoisted_27 = ["textContent"];
var _hoisted_28 = ["textContent"];
var _hoisted_29 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-wfb-palette-dot"
}, null, -1 /* HOISTED */);
var _hoisted_30 = ["textContent"];
var _hoisted_31 = {
  "class": "hc-wfb-section"
};
var _hoisted_32 = ["textContent"];
var _hoisted_33 = ["textContent"];
var _hoisted_34 = ["draggable", "disabled", "title", "onClick", "onDragstart"];
var _hoisted_35 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-wfb-palette-dot"
}, null, -1 /* HOISTED */);
var _hoisted_36 = ["textContent"];
var _hoisted_37 = ["textContent"];
var _hoisted_38 = {
  key: 0,
  "class": "hc-wfb-connecting"
};
var _hoisted_39 = ["textContent"];
var _hoisted_40 = ["width", "height"];
var _hoisted_41 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("defs", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("marker", {
  id: "hc-wfb-arrow",
  viewBox: "0 0 10 10",
  refX: "8",
  refY: "5",
  markerWidth: "7",
  markerHeight: "7",
  orient: "auto-start-reverse"
}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("path", {
  d: "M 0 0 L 10 5 L 0 10 z",
  fill: "#8a94a6"
})])], -1 /* HOISTED */);
var _hoisted_42 = ["d"];
var _hoisted_43 = ["transform", "onClick"];
var _hoisted_44 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("circle", {
  r: "8"
}, null, -1 /* HOISTED */);
var _hoisted_45 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("text", {
  y: "4",
  "text-anchor": "middle"
}, "×", -1 /* HOISTED */);
var _hoisted_46 = ["transform", "onClick"];
var _hoisted_47 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("circle", {
  r: "8"
}, null, -1 /* HOISTED */);
var _hoisted_48 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("text", {
  y: "4",
  "text-anchor": "middle"
}, "+", -1 /* HOISTED */);
var _hoisted_49 = ["onClick"];
var _hoisted_50 = ["title", "onClick"];
var _hoisted_51 = ["textContent"];
var _hoisted_52 = ["onMousedown"];
var _hoisted_53 = ["textContent"];
var _hoisted_54 = ["title", "textContent"];
var _hoisted_55 = ["title", "textContent"];
var _hoisted_56 = ["title", "onClick"];
var _hoisted_57 = ["disabled", "title", "onClick"];
var _hoisted_58 = ["textContent"];
var _hoisted_59 = ["disabled", "title", "onClick"];
var _hoisted_60 = ["textContent"];
var _hoisted_61 = ["textContent"];
var _hoisted_62 = ["onClick"];
var _hoisted_63 = ["textContent"];
var _hoisted_64 = ["textContent"];
var _hoisted_65 = ["textContent"];
var _hoisted_66 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-wfb-menu-icon"
}, "↪", -1 /* HOISTED */);
var _hoisted_67 = ["textContent"];
var _hoisted_68 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_node_editor = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("node-editor");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Toolbar "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $data.name = $event;
    }),
    "class": "hc-wfb-name",
    type: "text",
    maxlength: "150",
    disabled: !$options.canUpdate,
    onChange: _cache[1] || (_cache[1] = function () {
      return $options.rename && $options.rename.apply($options, arguments);
    })
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_3), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.name]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-badge", $data.status]),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.status.' + $data.status))
  }, null, 10 /* CLASS, PROPS */, _hoisted_4), $data.version ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.version", {
    n: $data.version.number
  })), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.unpublished ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 1,
    "class": "hc-wfb-warn",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.unpublished'))
  }, null, 8 /* PROPS */, _hoisted_6)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-muted hc-wfb-save", $data.saveState]),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.save.' + $data.saveState))
  }, null, 10 /* CLASS, PROPS */, _hoisted_7), _hoisted_8, $options.primary ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 2,
    type: "button",
    "class": "hc-wfb-primary",
    disabled: $data.busy || $options.primary.write && !$options.canUpdate,
    onClick: _cache[2] || (_cache[2] = function () {
      var _$options$primary;
      return $options.primary.run && (_$options$primary = $options.primary).run.apply(_$options$primary, arguments);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t($options.primary.label))
  }, null, 8 /* PROPS */, _hoisted_9)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.canExecute && $data.status === 'active' && !$data.unpublished ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 3,
    type: "button",
    "class": "hc-wfb-soft",
    disabled: $data.busy,
    onClick: _cache[3] || (_cache[3] = function () {
      return $options.pause && $options.pause.apply($options, arguments);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.pause'))
  }, null, 8 /* PROPS */, _hoisted_10)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_11, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": "hc-wfb-soft hc-wfb-more-toggle",
    title: _ctx.$t('workflow.more'),
    onClick: _cache[4] || (_cache[4] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
      return $data.moreOpen = !$data.moreOpen;
    }, ["stop"]))
  }, "⋯", 8 /* PROPS */, _hoisted_12), $data.moreOpen ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 0,
    "class": "hc-wfb-more-menu",
    onClick: _cache[11] || (_cache[11] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {}, ["stop"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    disabled: $data.busy,
    title: _ctx.$t('workflow.tip.validate'),
    onClick: _cache[5] || (_cache[5] = function ($event) {
      $data.moreOpen = false;
      $options.check();
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.validate'))
  }, null, 8 /* PROPS */, _hoisted_13), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    disabled: $options.readOnly,
    title: _ctx.$t('workflow.tip.arrange'),
    onClick: _cache[6] || (_cache[6] = function ($event) {
      $data.moreOpen = false;
      $options.arrange();
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.arrange'))
  }, null, 8 /* PROPS */, _hoisted_14), $options.canUpdate && $data.status !== 'archived' && $data.status !== 'active' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 0,
    type: "button",
    disabled: $data.busy,
    onClick: _cache[7] || (_cache[7] = function ($event) {
      $data.moreOpen = false;
      $options.publish(false);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.publish'))
  }, null, 8 /* PROPS */, _hoisted_15)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.canExecute && $data.status === 'active' && $data.unpublished ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 1,
    type: "button",
    disabled: $data.busy,
    onClick: _cache[8] || (_cache[8] = function ($event) {
      $data.moreOpen = false;
      $options.pause();
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.pause'))
  }, null, 8 /* PROPS */, _hoisted_16)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.canExecute && $data.status !== 'archived' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 2,
    type: "button",
    disabled: $data.busy,
    onClick: _cache[9] || (_cache[9] = function ($event) {
      $data.moreOpen = false;
      $options.archive();
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.archive'))
  }, null, 8 /* PROPS */, _hoisted_17)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.canDelete ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 3,
    type: "button",
    "class": "hc-wfb-danger",
    disabled: $data.busy,
    onClick: _cache[10] || (_cache[10] = function ($event) {
      $data.moreOpen = false;
      $options.destroy();
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.action.delete'))
  }, null, 8 /* PROPS */, _hoisted_18)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Problems "), $data.errors.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_19, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.problems'))
  }, null, 8 /* PROPS */, _hoisted_20), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("ul", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.errors, function (error) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: error.message
    }, [error.node && $options.nodeExists(error.node) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("a", {
      key: 0,
      href: "#",
      "class": "hc-wfb-error-link",
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.goTo(error.node);
      }, ["prevent"]),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(error.message)
    }, null, 8 /* PROPS */, _hoisted_21)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 1,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(error.message)
    }, null, 8 /* PROPS */, _hoisted_22))]);
  }), 128 /* KEYED_FRAGMENT */))])])) : $data.checked ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 1,
    "class": "hc-wfb-ok",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.valid'))
  }, null, 8 /* PROPS */, _hoisted_23)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_24, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Palette "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("nav", _hoisted_25, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" The trigger is the first step: one click to change it "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_26, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.trigger'))
  }, null, 8 /* PROPS */, _hoisted_27)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "hc-wfb-section-help",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.trigger_help'))
  }, null, 8 /* PROPS */, _hoisted_28), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-palette-item type-trigger", {
      on: $data.selectedId === $options.triggerId
    }]),
    onClick: _cache[12] || (_cache[12] = function ($event) {
      return $data.selectedId = $options.triggerId;
    })
  }, [_hoisted_29, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.trigger_change'))
  }, null, 8 /* PROPS */, _hoisted_30)], 2 /* CLASS */), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.sections(false), function (section) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: section.key
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_31, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.' + section.key))
    }, null, 8 /* PROPS */, _hoisted_32)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
      "class": "hc-wfb-section-help",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.' + section.key + '_help'))
    }, null, 8 /* PROPS */, _hoisted_33), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(section.types, function (type) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
        key: type,
        type: "button",
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-palette-item", 'type-' + type]),
        draggable: !$options.readOnly,
        disabled: $options.readOnly,
        title: _ctx.$t('workflow.node_help.' + type),
        onClick: function onClick($event) {
          return $options.addFromPalette(type);
        },
        onDragstart: function onDragstart($event) {
          return $options.paletteDragStart($event, type);
        }
      }, [_hoisted_35, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node.' + type))
      }, null, 8 /* PROPS */, _hoisted_36)], 42 /* CLASS, PROPS, HYDRATE_EVENTS */, _hoisted_34);
    }), 128 /* KEYED_FRAGMENT */))], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "hc-wfb-palette-hint",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.palette_hint'))
  }, null, 8 /* PROPS */, _hoisted_37)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Canvas "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    ref: "canvas",
    "class": "hc-wfb-canvas",
    onClick: _cache[17] || (_cache[17] = function () {
      return $options.canvasClick && $options.canvasClick.apply($options, arguments);
    }),
    onDragover: _cache[18] || (_cache[18] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {}, ["prevent"])),
    onDrop: _cache[19] || (_cache[19] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.paletteDrop && $options.paletteDrop.apply($options, arguments);
    }, ["prevent"]))
  }, [$data.connecting ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_38, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.connecting")) + " ", 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    onClick: _cache[13] || (_cache[13] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.cancelConnect && $options.cancelConnect.apply($options, arguments);
    }, ["stop"])),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.cancel'))
  }, null, 8 /* PROPS */, _hoisted_39)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-wfb-surface",
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
      width: $options.size.width + 'px',
      height: $options.size.height + 'px'
    })
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("svg", {
    "class": "hc-wfb-links",
    width: $options.size.width,
    height: $options.size.height
  }, [_hoisted_41, ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.links, function (link) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("g", {
      key: link.key
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("path", {
      d: link.path,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-link", link.branch]),
      "marker-end": "url(#hc-wfb-arrow)"
    }, null, 10 /* CLASS, PROPS */, _hoisted_42), !$options.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("g", {
      key: 0,
      "class": "hc-wfb-link-remove",
      transform: "translate(".concat(link.middle.x, ", ").concat(link.middle.y, ")"),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.removeLink(link);
      }, ["stop"])
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("title", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.remove_link")), 1 /* TEXT */), _hoisted_44, _hoisted_45], 8 /* PROPS */, _hoisted_43)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), !$options.readOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("g", {
      key: 1,
      "class": "hc-wfb-link-insert",
      transform: "translate(".concat(link.middle.x + 22, ", ").concat(link.middle.y, ")"),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.openMenu(link.from, link.branch, link.middle.x + 22, link.middle.y + 12, true);
      }, ["stop"])
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("title", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.add_menu.insert")), 1 /* TEXT */), _hoisted_47, _hoisted_48], 8 /* PROPS */, _hoisted_46)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
  }), 128 /* KEYED_FRAGMENT */))], 8 /* PROPS */, _hoisted_40)), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.view.nodes, function (node) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: node.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-node", ['type-' + node.type, {
        selected: $data.selectedId === node.id,
        orphan: $options.orphanIds.includes(node.id),
        invalid: $options.errorNodes.includes(node.id),
        target: $data.connecting && $data.connecting.from !== node.id && node.type !== 'trigger'
      }]]),
      style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
        left: node.position.x + 'px',
        top: node.position.y + 'px'
      }),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.nodeClick(node);
      }, ["stop"])
    }, [node.type !== 'trigger' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 0,
      "class": "hc-wfb-port in",
      title: _ctx.$t('workflow.input'),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.nodeClick(node);
      }, ["stop"])
    }, null, 8 /* PROPS */, _hoisted_50)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-wfb-node-icon",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.icon(node.type))
    }, null, 8 /* PROPS */, _hoisted_51), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-wfb-node-main",
      onMousedown: function onMousedown($event) {
        return $options.dragStart($event, node);
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-wfb-node-type",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node.' + node.type))
    }, null, 8 /* PROPS */, _hoisted_53), node.name ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 0,
      "class": "hc-wfb-node-name",
      title: node.name,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(node.name)
    }, null, 8 /* PROPS */, _hoisted_54)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-node-body", {
        small: node.name
      }]),
      title: $options.describe(node),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.describe(node))
    }, null, 10 /* CLASS, PROPS */, _hoisted_55)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_52), !$options.readOnly && node.type !== 'trigger' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 1,
      "class": "hc-wfb-node-remove",
      title: _ctx.$t('workflow.remove_node'),
      onMousedown: _cache[14] || (_cache[14] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {}, ["stop"])),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return $options.removeNode(node);
      }, ["stop"])
    }, "×", 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_56)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.outputsOf(node), function (branch) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: String(branch)
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Open output: the \"+\" adds the next step, already linked "), !$options.hasLink(node, branch) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
        key: 0,
        type: "button",
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-add", branch]),
        style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
          left: $options.portLeft(branch)
        }),
        disabled: $options.readOnly,
        title: _ctx.$t('workflow.add_menu.title'),
        onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
          return $options.openMenu(node.id, branch, node.position.x + $options.portCenter(branch), $options.menuY(node), false);
        }, ["stop"])
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" + "), branch ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
        key: 0,
        "class": "hc-wfb-add-label",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.' + branch))
      }, null, 8 /* PROPS */, _hoisted_58)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 14 /* CLASS, STYLE, PROPS */, _hoisted_57)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 1
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Linked output: a small dot, to link it elsewhere "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
        type: "button",
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-port out", [branch, {
          active: $data.connecting && $data.connecting.from === node.id && $data.connecting.branch === branch
        }]]),
        style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
          left: $options.portLeft(branch)
        }),
        disabled: $options.readOnly,
        title: _ctx.$t('workflow.output'),
        onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
          return $options.startConnect(node, branch);
        }, ["stop"])
      }, [branch ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
        key: 0,
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.' + branch))
      }, null, 8 /* PROPS */, _hoisted_60)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 14 /* CLASS, STYLE, PROPS */, _hoisted_59)], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */))], 64 /* STABLE_FRAGMENT */);
    }), 128 /* KEYED_FRAGMENT */))], 14 /* CLASS, STYLE, PROPS */, _hoisted_49);
  }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Menu of the \"+\" "), $data.menu ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 0,
    "class": "hc-wfb-menu",
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
      left: $data.menu.x + 'px',
      top: $data.menu.y + 'px'
    }),
    onClick: _cache[16] || (_cache[16] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {}, ["stop"]))
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.sections($data.menu.insert), function (section) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: section.key
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-wfb-menu-title",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.section.' + section.key))
    }, null, 8 /* PROPS */, _hoisted_61), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(section.types, function (type) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
        key: type,
        type: "button",
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfb-menu-item", 'type-' + type]),
        onClick: function onClick($event) {
          return $options.addAfter(type);
        }
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
        "class": "hc-wfb-menu-icon",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.icon(type))
      }, null, 8 /* PROPS */, _hoisted_63), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node.' + type))
      }, null, 8 /* PROPS */, _hoisted_64), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.node_help.' + type))
      }, null, 8 /* PROPS */, _hoisted_65)])], 10 /* CLASS, PROPS */, _hoisted_62);
    }), 128 /* KEYED_FRAGMENT */))], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */)), !$data.menu.insert ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 0,
    type: "button",
    "class": "hc-wfb-menu-item link",
    onClick: _cache[15] || (_cache[15] = function () {
      return $options.linkExisting && $options.linkExisting.apply($options, arguments);
    })
  }, [_hoisted_66, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.add_menu.link'))
  }, null, 8 /* PROPS */, _hoisted_67), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.add_menu.link_help'))
  }, null, 8 /* PROPS */, _hoisted_68)])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 4 /* STYLE */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 4 /* STYLE */)], 544 /* HYDRATE_EVENTS, NEED_PATCH */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Configuration of the selected step "), $options.selectedNode ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_node_editor, {
    key: $options.selectedNode.id,
    node: $options.selectedNode,
    graph: $data.graph,
    catalog: $props.catalog,
    "read-only": $options.readOnly,
    onUpdate: $options.updateNode,
    onLink: $options.linkFromPanel,
    onRemove: _cache[20] || (_cache[20] = function ($event) {
      return $options.removeNode($options.selectedNode);
    }),
    onClose: _cache[21] || (_cache[21] = function ($event) {
      return $data.selectedId = null;
    })
  }, null, 8 /* PROPS */, ["node", "graph", "catalog", "read-only", "onUpdate", "onLink"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814 ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  "class": "hc-wfm"
};
var _hoisted_2 = {
  "class": "hc-wfm-block"
};
var _hoisted_3 = {
  "class": "hc-wfm-title"
};
var _hoisted_4 = ["textContent"];
var _hoisted_5 = ["value"];
var _hoisted_6 = {
  key: 0,
  "class": "hc-wfm-cards"
};
var _hoisted_7 = {
  "class": "hc-wfm-card"
};
var _hoisted_8 = ["textContent"];
var _hoisted_9 = {
  "class": "hc-wfm-card"
};
var _hoisted_10 = ["textContent"];
var _hoisted_11 = ["textContent"];
var _hoisted_12 = {
  "class": "hc-wfm-card"
};
var _hoisted_13 = ["textContent"];
var _hoisted_14 = {
  key: 1,
  "class": "hc-wfm-table"
};
var _hoisted_15 = ["textContent"];
var _hoisted_16 = ["textContent"];
var _hoisted_17 = ["textContent"];
var _hoisted_18 = ["textContent"];
var _hoisted_19 = ["textContent"];
var _hoisted_20 = ["textContent"];
var _hoisted_21 = ["textContent"];
var _hoisted_22 = ["onClick"];
var _hoisted_23 = ["textContent"];
var _hoisted_24 = {
  key: 2,
  "class": "hc-wfm-columns"
};
var _hoisted_25 = {
  key: 0
};
var _hoisted_26 = ["textContent"];
var _hoisted_27 = {
  "class": "hc-wfm-list"
};
var _hoisted_28 = {
  key: 1
};
var _hoisted_29 = ["textContent"];
var _hoisted_30 = {
  "class": "hc-wfm-list"
};
var _hoisted_31 = {
  "class": "bad"
};
var _hoisted_32 = {
  "class": "hc-wfm-block"
};
var _hoisted_33 = {
  "class": "hc-wfm-title"
};
var _hoisted_34 = ["textContent"];
var _hoisted_35 = ["textContent"];
var _hoisted_36 = ["value", "textContent"];
var _hoisted_37 = ["textContent"];
var _hoisted_38 = ["value", "textContent"];
var _hoisted_39 = ["textContent"];
var _hoisted_40 = {
  "class": "hc-wfm-split"
};
var _hoisted_41 = {
  "class": "hc-wfm-rows"
};
var _hoisted_42 = ["textContent"];
var _hoisted_43 = ["onClick"];
var _hoisted_44 = ["textContent"];
var _hoisted_45 = {
  "class": "hc-wfm-row-main"
};
var _hoisted_46 = {
  "class": "hc-wfm-row-date"
};
var _hoisted_47 = ["title", "textContent"];
var _hoisted_48 = ["disabled", "textContent"];
var _hoisted_49 = {
  key: 0,
  "class": "hc-wfm-detail"
};
var _hoisted_50 = ["disabled", "textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_loading = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("loading");
  var _component_execution_steps = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("execution-steps");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Figures "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.overview'))
  }, null, 8 /* PROPS */, _hoisted_4), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return $data.days = $event;
    }),
    onChange: _cache[1] || (_cache[1] = function () {
      return $options.loadStats && $options.loadStats.apply($options, arguments);
    })
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)([1, 7, 30], function (d) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
      key: d,
      value: d
    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.monitor.last_days", {
      n: d
    })), 9 /* TEXT, PROPS */, _hoisted_5);
  }), 64 /* STABLE_FRAGMENT */))], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelSelect, $data.days, void 0, {
    number: true
  }]])]), $data.stats ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.totals.waiting), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.card.waiting'))
  }, null, 8 /* PROPS */, _hoisted_8)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_9, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.totals.completed), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.card.completed'))
  }, null, 8 /* PROPS */, _hoisted_10)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfm-card", {
      bad: $options.totals.failed
    }])
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.totals.failed), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.card.failed'))
  }, null, 8 /* PROPS */, _hoisted_11)], 2 /* CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_12, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.stepTotal), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.card.steps'))
  }, null, 8 /* PROPS */, _hoisted_13)])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.stats && $data.stats.workflows.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("table", _hoisted_14, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("thead", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tr", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.workflow'))
  }, null, 8 /* PROPS */, _hoisted_15), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.waiting'))
  }, null, 8 /* PROPS */, _hoisted_16), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.completed'))
  }, null, 8 /* PROPS */, _hoisted_17), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.failed'))
  }, null, 8 /* PROPS */, _hoisted_18), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.average'))
  }, null, 8 /* PROPS */, _hoisted_19), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.last'))
  }, null, 8 /* PROPS */, _hoisted_20), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.col.next'))
  }, null, 8 /* PROPS */, _hoisted_21)])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.stats.workflows, function (workflow) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
      key: workflow.id,
      onClick: function onClick($event) {
        $data.filters.workflow = workflow.id;
        $options.loadList(true);
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.name) + " ", 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-badge", workflow.status]),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.status.' + workflow.status))
    }, null, 10 /* CLASS, PROPS */, _hoisted_23)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.current.waiting + workflow.current.pending + workflow.current.running), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.period.completed), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)({
        bad: workflow.period.failed
      })
    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.period.failed), 3 /* TEXT, CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.duration(workflow.average_seconds)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.dateTime(workflow.last_started_at)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.dateTime(workflow.next_resume_at)), 1 /* TEXT */)], 8 /* PROPS */, _hoisted_22);
  }), 128 /* KEYED_FRAGMENT */))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.stats && ($data.stats.errors.length || $data.stats.providers.length) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_24, [$data.stats.errors.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_25, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.errors'))
  }, null, 8 /* PROPS */, _hoisted_26), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("ul", _hoisted_27, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.stats.errors, function (error) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: error.message
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(error.count) + "×", 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(error.message), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.stats.providers.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_28, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.operators'))
  }, null, 8 /* PROPS */, _hoisted_29), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("ul", _hoisted_30, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.stats.providers, function (provider) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: provider.provider
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.provider) + ": " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.sent) + " " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.monitor.sent")), 1 /* TEXT */), provider.failed ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 0
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(", "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_31, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(provider.failed) + " " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("workflow.monitor.failed")), 1 /* TEXT */)], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
  }), 128 /* KEYED_FRAGMENT */))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.loadingStats
  }, null, 8 /* PROPS */, ["loading"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Executions "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_32, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_33, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.executions'))
  }, null, 8 /* PROPS */, _hoisted_34), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return $data.filters.workflow = $event;
    }),
    onChange: _cache[3] || (_cache[3] = function ($event) {
      return $options.loadList(true);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.all_workflows'))
  }, null, 8 /* PROPS */, _hoisted_35), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.workflowOptions, function (workflow) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("option", {
      key: workflow.id,
      value: workflow.id,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(workflow.name)
    }, null, 8 /* PROPS */, _hoisted_36);
  }), 128 /* KEYED_FRAGMENT */))], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelSelect, $data.filters.workflow]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return $data.filters.status = $event;
    }),
    onChange: _cache[5] || (_cache[5] = function ($event) {
      return $options.loadList(true);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.all_statuses'))
  }, null, 8 /* PROPS */, _hoisted_37), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)(['waiting', 'failed', 'completed', 'cancelled'], function (status) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
      key: status,
      value: status,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.exec_status.' + status))
    }, null, 8 /* PROPS */, _hoisted_38);
  }), 64 /* STABLE_FRAGMENT */))], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelSelect, $data.filters.status]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    onClick: _cache[6] || (_cache[6] = function () {
      return $options.refresh && $options.refresh.apply($options, arguments);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.refresh'))
  }, null, 8 /* PROPS */, _hoisted_39)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_40, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_41, [!$data.loadingList && !$data.rows.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 0,
    "class": "hc-workflow-hint",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.none'))
  }, null, 8 /* PROPS */, _hoisted_42)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.rows, function (row) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: row.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-wfm-row", {
        current: $data.detail && $data.detail.id === row.id
      }]),
      onClick: function onClick($event) {
        return $options.open(row);
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-workflow-badge", 'exec-' + row.status]),
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.exec_status.' + row.status))
    }, null, 10 /* CLASS, PROPS */, _hoisted_44), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_45, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("b", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(row.workflow.name || _ctx.$t("workflow.monitor.deleted_workflow")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(row.subject.name || _ctx.$t("workflow.monitor.subject." + row.subject.type) + " #" + row.subject.id), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_46, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.dateTime(row.started_at)), 1 /* TEXT */), row.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
      key: 0,
      "class": "hc-wfm-row-error",
      title: row.error,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(row.error)
    }, null, 8 /* PROPS */, _hoisted_47)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 10 /* CLASS, PROPS */, _hoisted_43);
  }), 128 /* KEYED_FRAGMENT */)), $data.hasMore ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 1,
    type: "button",
    "class": "hc-wfm-more",
    disabled: $data.loadingList,
    onClick: _cache[7] || (_cache[7] = function ($event) {
      return $options.loadList(false);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.more'))
  }, null, 8 /* PROPS */, _hoisted_48)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.loadingList
  }, null, 8 /* PROPS */, ["loading"])]), $data.detail ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_49, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_execution_steps, {
    execution: $data.detail
  }, null, 8 /* PROPS */, ["execution"]), $options.canCancel && $options.cancellable($data.detail) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 0,
    type: "button",
    "class": "hc-wfb-danger",
    disabled: $data.cancelling,
    onClick: _cache[8] || (_cache[8] = function () {
      return $options.cancel && $options.cancel.apply($options, arguments);
    }),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('workflow.monitor.cancel'))
  }, null, 8 /* PROPS */, _hoisted_50)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])])]);
}

/***/ }),

/***/ "./resources/js/apis/project/workflow.js":
/*!***********************************************!*\
  !*** ./resources/js/apis/project/workflow.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _apis_api_service__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/api.service */ "./resources/js/apis/api.service.js");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  get: function get(project) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow"));
  },
  create: function create(project, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow"), params);
  },
  show: function show(project, workflow) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow/").concat(workflow));
  },
  /**
   * Name, description and the draft graph (the response lists what is still wrong)
   */
  update: function update(project, workflow, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].put("project/".concat(project, "/workflow/").concat(workflow), params);
  },
  destroy: function destroy(project, workflow) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"]["delete"]("project/".concat(project, "/workflow/").concat(workflow));
  },
  /**
   * Problems of the draft, without saving
   */
  validate: function validate(project, workflow, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow/").concat(workflow, "/validate"), params);
  },
  publish: function publish(project, workflow, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow/").concat(workflow, "/publish"), params);
  },
  activate: function activate(project, workflow) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow/").concat(workflow, "/activate"));
  },
  pause: function pause(project, workflow) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow/").concat(workflow, "/pause"));
  },
  archive: function archive(project, workflow) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow/").concat(workflow, "/archive"));
  },
  // Follow-up of the executions
  /**
   * One page of executions: { workflow, status ("failed,waiting"), page, per_page }
   */
  executions: function executions(project, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow-execution"), {
      params: params
    });
  },
  /**
   * An execution with its steps
   */
  execution: function execution(project, _execution) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow-execution/").concat(_execution));
  },
  cancelExecution: function cancelExecution(project, execution) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/workflow-execution/").concat(execution, "/cancel"));
  },
  /**
   * Figures of the last days
   */
  stats: function stats(project, days) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow-stats"), {
      params: {
        days: days
      }
    });
  },
  /**
   * The workflows a prospect went through, with their steps
   */
  prospectExecutions: function prospectExecutions(project, prospect) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/prospect/").concat(prospect, "/workflow-execution"));
  },
  /**
   * What the builder offers (the backend is the source of truth)
   */
  catalog: function catalog(project) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow-catalog"));
  },
  /**
   * Operators of the project (email, sms, call): reference and name only
   */
  providers: function providers(project, capability) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/workflow-provider"), {
      params: capability ? {
        capability: capability
      } : {}
    });
  }
});

/***/ }),

/***/ "./resources/js/utils/workflow-conditions.js":
/*!***************************************************!*\
  !*** ./resources/js/utils/workflow-conditions.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   filtersFor: () => (/* binding */ filtersFor),
/* harmony export */   newCheckItem: () => (/* binding */ newCheckItem),
/* harmony export */   newFilterItem: () => (/* binding */ newFilterItem),
/* harmony export */   triggerConfig: () => (/* binding */ triggerConfig)
/* harmony export */ });
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
/**
 * Starting values for the condition editor and for the triggers.
 * Pure functions: shared by the components, tested without Vue.
 */

// What a new criterion starts with, by type of value
var DEFAULT_VALUE = {
  "boolean": true,
  number: 1,
  users: [],
  groups: [],
  labels: []
};
function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

/**
 * The criteria offered for a subject (prospect, order, appointment)
 */
function filtersFor(catalog, subject) {
  return catalog.conditions.filters && catalog.conditions.filters[subject] || [];
}

/**
 * A new criterion { filters: { name: value } }, null if the catalog has no such criterion
 */
function newFilterItem(catalog, subject, name) {
  var _DEFAULT_VALUE$filter;
  var filter = filtersFor(catalog, subject).find(function (f) {
    return f.name === name;
  });
  if (!filter) {
    return null;
  }
  return {
    filters: _defineProperty({}, name, clone((_DEFAULT_VALUE$filter = DEFAULT_VALUE[filter.type]) !== null && _DEFAULT_VALUE$filter !== void 0 ? _DEFAULT_VALUE$filter : null))
  };
}

/**
 * A new date / time check
 */
function newCheckItem(name) {
  return name === "work_hours" ? {
    check: name,
    days: [1, 2, 3, 4, 5],
    from: "08:00",
    to: "17:00"
  } : {
    check: name,
    from: null,
    to: null
  };
}

/**
 * The configuration of a trigger just chosen.
 *
 * A trigger that watches a filter starts with its first criterion already in
 * place, so that there is something to choose from and to read instead of an
 * empty box.
 */
function triggerConfig(catalog, type) {
  var subject = type.split(".")[0];
  var config = {
    trigger: type
  };
  if (type.includes(".filter_")) {
    var first = filtersFor(catalog, subject)[0];
    if (first) {
      config.op = "and";
      config.items = [newFilterItem(catalog, subject, first.name)];
    }
  }
  return config;
}

/***/ }),

/***/ "./resources/js/utils/workflow-format.js":
/*!***********************************************!*\
  !*** ./resources/js/utils/workflow-format.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cancellable: () => (/* binding */ cancellable),
/* harmony export */   dateTime: () => (/* binding */ dateTime),
/* harmony export */   duration: () => (/* binding */ duration),
/* harmony export */   stepIcon: () => (/* binding */ stepIcon),
/* harmony export */   waitingKind: () => (/* binding */ waitingKind)
/* harmony export */ });
/**
 * Display helpers of the workflow follow-up screens (no Vue, no store).
 */

/**
 * 90 => "1 min 30 s", 7200 => "2 h", null => "-"
 */
function duration(seconds) {
  if (seconds === null || seconds === undefined) {
    return "-";
  }
  var total = Math.max(0, Math.round(seconds));
  var hours = Math.floor(total / 3600);
  var minutes = Math.floor(total % 3600 / 60);
  var rest = total % 60;
  if (hours) {
    return minutes ? "".concat(hours, " h ").concat(minutes, " min") : "".concat(hours, " h");
  }
  if (minutes) {
    return rest ? "".concat(minutes, " min ").concat(rest, " s") : "".concat(minutes, " min");
  }
  return "".concat(rest, " s");
}

/**
 * A date from the API in the user's locale, "-" when there is none
 */
function dateTime(value) {
  if (!value) {
    return "-";
  }
  var date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return "-";
  }
  return date.toLocaleString(undefined, {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  });
}

/**
 * Sign shown in front of a step, by its outcome
 */
function stepIcon(status) {
  return {
    completed: "✓",
    failed: "✗",
    skipped: "–",
    running: "…",
    waiting: "⏳"
  }[status] || "·";
}

/**
 * Executions that can still be cancelled (a running one is in a worker's hands)
 */
function cancellable(execution) {
  return execution.status === "pending" || execution.status === "waiting";
}

/**
 * What an execution waits for, as a translation key suffix
 * (workflow.monitor.<key>), null when it does not wait
 */
function waitingKind(execution) {
  if (execution.status !== "waiting") {
    return null;
  }
  return execution.waiting_event ? "waiting_event" : "waiting_until";
}

/***/ }),

/***/ "./resources/js/utils/workflow-graph.js":
/*!**********************************************!*\
  !*** ./resources/js/utils/workflow-graph.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   NO: () => (/* binding */ NO),
/* harmony export */   NODE_HEIGHT: () => (/* binding */ NODE_HEIGHT),
/* harmony export */   NODE_WIDTH: () => (/* binding */ NODE_WIDTH),
/* harmony export */   YES: () => (/* binding */ YES),
/* harmony export */   addNode: () => (/* binding */ addNode),
/* harmony export */   appendNode: () => (/* binding */ appendNode),
/* harmony export */   bounds: () => (/* binding */ bounds),
/* harmony export */   connect: () => (/* binding */ connect),
/* harmony export */   createNode: () => (/* binding */ createNode),
/* harmony export */   disconnect: () => (/* binding */ disconnect),
/* harmony export */   findNode: () => (/* binding */ findNode),
/* harmony export */   inputPoint: () => (/* binding */ inputPoint),
/* harmony export */   layout: () => (/* binding */ layout),
/* harmony export */   linkPath: () => (/* binding */ linkPath),
/* harmony export */   links: () => (/* binding */ links),
/* harmony export */   moveNode: () => (/* binding */ moveNode),
/* harmony export */   nextId: () => (/* binding */ nextId),
/* harmony export */   normalize: () => (/* binding */ normalize),
/* harmony export */   orphans: () => (/* binding */ orphans),
/* harmony export */   outputPoint: () => (/* binding */ outputPoint),
/* harmony export */   outputs: () => (/* binding */ outputs),
/* harmony export */   reachable: () => (/* binding */ reachable),
/* harmony export */   removeNode: () => (/* binding */ removeNode),
/* harmony export */   triggerNode: () => (/* binding */ triggerNode),
/* harmony export */   updateNode: () => (/* binding */ updateNode),
/* harmony export */   wouldCreateCycle: () => (/* binding */ wouldCreateCycle)
/* harmony export */ });
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
/**
 * Graph model of the workflow builder.
 *
 * Pure functions on a graph { nodes: [{ id, type, name, config, position }],
 * edges: [{ from, to, branch? }] }: they never change the graph they receive
 * and know nothing about Vue. The same format is what the API stores and the
 * engine executes; the rules enforced here are the ones that can be decided
 * without the server (the server validates everything again when publishing).
 */

var NODE_WIDTH = 230;
var NODE_HEIGHT = 76;
var YES = "yes";
var NO = "no";
var GAP_X = 70;
var GAP_Y = 80;
var ORIGIN_X = 80;
var ORIGIN_Y = 60;

/**
 * Starting configuration of each kind of node
 */
var DEFAULT_CONFIG = {
  trigger: function trigger() {
    return {
      trigger: "prospect.created"
    };
  },
  condition: function condition() {
    return {
      op: "and",
      items: []
    };
  },
  action: function action() {
    return {};
  },
  wait: function wait() {
    return {
      minutes: 30
    };
  },
  wait_event: function wait_event() {
    return {
      event: "sms_received",
      hours: 24
    };
  },
  end: function end() {
    return {};
  }
};
function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

/**
 * Always the same shape, whatever was stored
 */
function normalize(graph) {
  return {
    nodes: Array.isArray(graph && graph.nodes) ? clone(graph.nodes) : [],
    edges: Array.isArray(graph && graph.edges) ? clone(graph.edges) : []
  };
}
function findNode(graph, id) {
  return graph.nodes.find(function (node) {
    return node.id === id;
  }) || null;
}
function triggerNode(graph) {
  return graph.nodes.find(function (node) {
    return node.type === "trigger";
  }) || null;
}

/**
 * Output ports of a node: [null] one way out, ["yes", "no"] two ways, [] none.
 */
function outputs(node) {
  if (node.type === "end") {
    return [];
  }
  if (node.type === "condition" || node.type === "wait_event") {
    return [YES, NO];
  }
  return [null];
}

/**
 * A free identifier ("action3")
 */
function nextId(graph, prefix) {
  var used = new Set(graph.nodes.map(function (node) {
    return node.id;
  }));
  var i = 1;
  while (used.has("".concat(prefix).concat(i))) {
    i++;
  }
  return "".concat(prefix).concat(i);
}
function createNode(graph, type) {
  var position = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {
    x: ORIGIN_X,
    y: ORIGIN_Y
  };
  var config = arguments.length > 3 ? arguments[3] : undefined;
  var make = DEFAULT_CONFIG[type];
  if (!make) {
    throw new Error("Unknown node type: ".concat(type));
  }
  return {
    id: nextId(graph, type === "wait_event" ? "event" : type),
    type: type,
    name: null,
    config: config || make(),
    position: {
      x: Math.round(position.x),
      y: Math.round(position.y)
    }
  };
}
function addNode(graph, node) {
  var next = normalize(graph);

  // One trigger per workflow
  if (node.type === "trigger" && triggerNode(next)) {
    return next;
  }
  next.nodes.push(clone(node));
  return next;
}

/**
 * The trigger cannot be removed: a workflow always has one.
 */
function removeNode(graph, id) {
  var next = normalize(graph);
  var node = findNode(next, id);
  if (!node || node.type === "trigger") {
    return next;
  }
  next.nodes = next.nodes.filter(function (n) {
    return n.id !== id;
  });
  next.edges = next.edges.filter(function (edge) {
    return edge.from !== id && edge.to !== id;
  });
  return next;
}
function moveNode(graph, id, x, y) {
  var next = normalize(graph);
  var node = findNode(next, id);
  if (node) {
    node.position = {
      x: Math.round(x),
      y: Math.round(y)
    };
  }
  return next;
}

/**
 * Change the name and / or the configuration of a node
 */
function updateNode(graph, id, patch) {
  var next = normalize(graph);
  var node = findNode(next, id);
  if (node) {
    if ("name" in patch) {
      node.name = patch.name || null;
    }
    if ("config" in patch) {
      node.config = clone(patch.config);
    }
  }
  return next;
}

/**
 * Nodes that can be reached from $startId
 */
function reachable(graph, startId) {
  var seen = new Set();
  var stack = [startId];
  var _loop = function _loop() {
    var id = stack.pop();
    if (seen.has(id)) {
      return "continue";
    }
    seen.add(id);
    graph.edges.filter(function (edge) {
      return edge.from === id;
    }).forEach(function (edge) {
      return stack.push(edge.to);
    });
  };
  while (stack.length) {
    var _ret = _loop();
    if (_ret === "continue") continue;
  }
  return seen;
}

/**
 * Would the link from -> to bring the workflow back to a step it already went through?
 */
function wouldCreateCycle(graph, from, to) {
  return from === to || reachable(graph, to).has(from);
}

/**
 * Nodes that no path from the trigger reaches
 */
function orphans(graph) {
  var trigger = triggerNode(graph);
  if (!trigger) {
    return graph.nodes.map(function (node) {
      return node.id;
    });
  }
  var reached = reachable(graph, trigger.id);
  return graph.nodes.filter(function (node) {
    return !reached.has(node.id);
  }).map(function (node) {
    return node.id;
  });
}

/**
 * Link two nodes. Returns { graph, error }: error is a translation key suffix
 * (workflow.error.<error>) when the link is refused, and graph is then unchanged.
 * An output has a single link: linking it again replaces the previous one.
 */
function connect(graph, from, to) {
  var branch = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  var next = normalize(graph);
  var source = findNode(next, from);
  var target = findNode(next, to);
  if (!source || !target) {
    return {
      graph: next,
      error: "unknown_node"
    };
  }
  if (from === to) {
    return {
      graph: next,
      error: "self_link"
    };
  }
  if (target.type === "trigger") {
    return {
      graph: next,
      error: "into_trigger"
    };
  }
  var ports = outputs(source);
  if (!ports.length) {
    return {
      graph: next,
      error: "from_end"
    };
  }
  if (!ports.includes(branch)) {
    return {
      graph: next,
      error: "bad_output"
    };
  }
  if (wouldCreateCycle(next, from, to)) {
    return {
      graph: next,
      error: "cycle"
    };
  }
  next.edges = next.edges.filter(function (edge) {
    return !(edge.from === from && (edge.branch || null) === branch);
  });
  next.edges.push(branch ? {
    from: from,
    to: to,
    branch: branch
  } : {
    from: from,
    to: to
  });
  return {
    graph: next,
    error: null
  };
}

/**
 * The "+" of an output: adds a step right after it, already linked.
 * When the output leads somewhere, the new step is inserted in between (what
 * followed is moved down to make room). Returns { graph, node, error }.
 */
function appendNode(graph, from, branch, type) {
  var base = normalize(graph);
  var source = findNode(base, from);
  if (!source) {
    return {
      graph: base,
      node: null,
      error: "unknown_node"
    };
  }
  var existing = base.edges.find(function (edge) {
    return edge.from === from && (edge.branch || null) === (branch || null);
  });
  var node = createNode(base, type, {
    x: Math.max(0, source.position.x + (branch === YES ? -(NODE_WIDTH / 2 + GAP_X / 2) : branch === NO ? NODE_WIDTH / 2 + GAP_X / 2 : 0)),
    y: source.position.y + NODE_HEIGHT + GAP_Y
  });
  if (existing && !outputs(node).length) {
    return {
      graph: base,
      node: null,
      error: "from_end"
    };
  }
  var next = addNode(base, node);
  if (existing) {
    // Make room for the new step, then keep the rest of the path behind it
    var shift = NODE_HEIGHT + GAP_Y;
    var moved = reachable(base, existing.to);
    next.nodes.forEach(function (n) {
      if (moved.has(n.id)) {
        n.position = {
          x: n.position.x,
          y: n.position.y + shift
        };
      }
    });
  }
  var first = connect(next, from, node.id, branch || null);
  if (first.error) {
    return {
      graph: base,
      node: null,
      error: first.error
    };
  }
  next = first.graph;
  if (existing) {
    var second = connect(next, node.id, existing.to, outputs(node)[0]);
    if (second.error) {
      return {
        graph: base,
        node: null,
        error: second.error
      };
    }
    next = second.graph;
  }
  return {
    graph: next,
    node: node,
    error: null
  };
}
function disconnect(graph, from) {
  var branch = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var next = normalize(graph);
  next.edges = next.edges.filter(function (edge) {
    return !(edge.from === from && (edge.branch || null) === branch);
  });
  return next;
}

/**
 * Where a port is drawn, in the canvas coordinates
 */
function outputPoint(node, branch) {
  var x = node.position.x;
  var y = node.position.y + NODE_HEIGHT;
  if (branch === YES) {
    return {
      x: x + NODE_WIDTH * 0.3,
      y: y
    };
  }
  if (branch === NO) {
    return {
      x: x + NODE_WIDTH * 0.7,
      y: y
    };
  }
  return {
    x: x + NODE_WIDTH / 2,
    y: y
  };
}
function inputPoint(node) {
  return {
    x: node.position.x + NODE_WIDTH / 2,
    y: node.position.y
  };
}

/**
 * SVG path of a link, from the output of a node to the input of another
 */
function linkPath(from, to) {
  var dy = Math.max(40, Math.abs(to.y - from.y) / 2);
  return "M ".concat(from.x, " ").concat(from.y, " C ").concat(from.x, " ").concat(from.y + dy, ", ").concat(to.x, " ").concat(to.y - dy, ", ").concat(to.x, " ").concat(to.y);
}

/**
 * Everything needed to draw the links: path and position of the label
 */
function links(graph) {
  return graph.edges.map(function (edge) {
    var source = findNode(graph, edge.from);
    var target = findNode(graph, edge.to);
    if (!source || !target) {
      return null;
    }
    var start = outputPoint(source, edge.branch || null);
    var end = inputPoint(target);
    return {
      key: "".concat(edge.from, ":").concat(edge.branch || "", ":").concat(edge.to),
      from: edge.from,
      to: edge.to,
      branch: edge.branch || null,
      path: linkPath(start, end),
      // Where the button to remove the link is drawn
      middle: {
        x: (start.x + end.x) / 2,
        y: (start.y + end.y) / 2
      }
    };
  }).filter(Boolean);
}

/**
 * Arrange the nodes top to bottom, by the longest path from the trigger.
 * Nodes that are not connected are put in a row below.
 */
function layout(graph) {
  var next = normalize(graph);
  var trigger = triggerNode(next);
  var level = {};
  if (trigger) {
    level[trigger.id] = 0;

    // The graph has no cycle: n passes are enough to settle the levels
    for (var pass = 0; pass < next.nodes.length; pass++) {
      next.edges.forEach(function (edge) {
        if (level[edge.from] !== undefined && (level[edge.to] === undefined || level[edge.to] < level[edge.from] + 1)) {
          level[edge.to] = level[edge.from] + 1;
        }
      });
    }
  }
  var rows = {};
  var maxLevel = Math.max.apply(Math, [-1].concat(_toConsumableArray(Object.values(level))));
  next.nodes.forEach(function (node) {
    var row = level[node.id] !== undefined ? level[node.id] : maxLevel + 1;
    (rows[row] = rows[row] || []).push(node);
  });
  Object.entries(rows).forEach(function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
      row = _ref2[0],
      nodes = _ref2[1];
    nodes.forEach(function (node, column) {
      node.position = {
        x: ORIGIN_X + column * (NODE_WIDTH + GAP_X),
        y: ORIGIN_Y + Number(row) * (NODE_HEIGHT + GAP_Y)
      };
    });
  });
  return next;
}

/**
 * Size of the canvas that contains every node
 */
function bounds(graph) {
  var width = 0;
  var height = 0;
  graph.nodes.forEach(function (node) {
    width = Math.max(width, node.position.x + NODE_WIDTH);
    height = Math.max(height, node.position.y + NODE_HEIGHT);
  });
  return {
    width: width + 200,
    height: height + 200
  };
}

/***/ }),

/***/ "./resources/js/utils/workflow-i18n.js":
/*!*********************************************!*\
  !*** ./resources/js/utils/workflow-i18n.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   slug: () => (/* binding */ slug)
/* harmony export */ });
/**
 * Translation key of a workflow identifier.
 *
 * The identifiers of the catalog contain dots and hyphens ("prospect.created",
 * "prospect-attach-user"): vue-i18n reads a dot as a path, so the translations
 * of these identifiers are keyed with underscores.
 */
function slug(id) {
  return String(id).replace(/[.-]/g, "_");
}

/***/ }),

/***/ "./resources/js/utils/workflow-templates.js":
/*!**************************************************!*\
  !*** ./resources/js/utils/workflow-templates.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TEMPLATE_IDS: () => (/* binding */ TEMPLATE_IDS),
/* harmony export */   templateGraph: () => (/* binding */ templateGraph)
/* harmony export */ });
/* harmony import */ var _workflow_graph__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./workflow-graph */ "./resources/js/utils/workflow-graph.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
/**
 * Ready-made workflows: a starting point to adapt, not a finished product.
 * Operators and texts are left empty on purpose (they belong to the project);
 * the builder says what is still to fill in before turning it on.
 *
 * Names and descriptions: workflow.template.<id>.name / .description
 */

var step = function step(id, type) {
  var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  return {
    id: id,
    type: type,
    name: null,
    config: config,
    position: {
      x: 0,
      y: 0
    }
  };
};
var WORK_HOURS = {
  check: "work_hours",
  days: [1, 2, 3, 4, 5],
  from: "08:00",
  to: "17:00"
};
var TEMPLATES = {
  welcome_email: function welcome_email() {
    return {
      nodes: [step("trigger", "trigger", {
        trigger: "prospect.created"
      }), step("action1", "action", {
        action: "send_email"
      })],
      edges: [{
        from: "trigger",
        to: "action1"
      }]
    };
  },
  sms_follow_up: function sms_follow_up() {
    return {
      nodes: [step("trigger", "trigger", {
        trigger: "prospect.created"
      }), step("action1", "action", {
        action: "send_sms"
      }), step("event1", "wait_event", {
        event: "sms_received",
        days: 2
      }), step("end1", "end"), step("action2", "action", {
        action: "send_sms"
      }), step("end2", "end")],
      edges: [{
        from: "trigger",
        to: "action1"
      }, {
        from: "action1",
        to: "event1"
      }, {
        from: "event1",
        to: "end1",
        branch: "yes"
      }, {
        from: "event1",
        to: "action2",
        branch: "no"
      }, {
        from: "action2",
        to: "end2"
      }]
    };
  },
  appointment_reminder: function appointment_reminder() {
    return {
      nodes: [step("trigger", "trigger", {
        trigger: "event.starting_soon",
        minutes_before: 1440
      }), step("action1", "action", {
        action: "send_sms"
      })],
      edges: [{
        from: "trigger",
        to: "action1"
      }]
    };
  },
  work_hours_contact: function work_hours_contact() {
    return {
      nodes: [step("trigger", "trigger", {
        trigger: "prospect.created"
      }), step("condition1", "condition", {
        op: "and",
        items: [_objectSpread({}, WORK_HOURS)]
      }), step("action1", "action", {
        action: "send_sms"
      }), step("wait1", "wait", {
        until_time: "08:00"
      }), step("action2", "action", {
        action: "send_sms"
      })],
      edges: [{
        from: "trigger",
        to: "condition1"
      }, {
        from: "condition1",
        to: "action1",
        branch: "yes"
      }, {
        from: "condition1",
        to: "wait1",
        branch: "no"
      }, {
        from: "wait1",
        to: "action2"
      }]
    };
  }
};
var TEMPLATE_IDS = Object.keys(TEMPLATES);

/**
 * A new graph of the template, arranged; null for an unknown template
 */
function templateGraph(id) {
  return TEMPLATES[id] ? (0,_workflow_graph__WEBPACK_IMPORTED_MODULE_0__.layout)(TEMPLATES[id]()) : null;
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-wfc { position: relative; padding: 10px; background: #f6f7fb; border: 1px solid #e3e6ef; border-radius: 10px;\n}\n.hc-wfc .hc-wfc { margin-top: 4px; background: #fff; border-left: 3px solid #5b6cf0;\n}\n.hc-wfc select { padding: 7px 10px; color: #2b2f3a; background: #fff; border: 1px solid #d5d9e4; border-radius: 8px; font-size: 13px;\n}\n.hc-wfc select:focus { border-color: #5b6cf0; box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15); outline: 0;\n}\n.hc-wfc-intro { margin: 0 0 8px; color: #5d6272; font-size: 12px;\n}\n.hc-wfc-empty { margin: 4px 0 8px; padding: 8px 10px; background: #fff7dd; border-radius: 8px; color: #6b4b00; font-size: 12px;\n}\n.hc-wfc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;\n}\n.hc-wfc-item { position: relative; margin-bottom: 8px; padding: 8px 30px 8px 8px; background: #fff; border: 1px solid #e3e6ef; border-radius: 8px;\n}\n.hc-wfc .hc-wfc .hc-wfc-item { background: #f6f7fb;\n}\n.hc-wfc-row { display: flex; flex-direction: column; gap: 6px;\n}\n.hc-wfc-row select { width: 100%;\n}\n.hc-wfc-field label { font-size: 11px; color: #7a7f90;\n}\n.hc-wfc-help { margin: 0; color: #8a8fa0; font-size: 11px;\n}\n.hc-wfc-remove { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; padding: 0; color: #8a8fa0; background: transparent; border: 0; border-radius: 50%; font-size: 16px; line-height: 1; cursor: pointer;\n}\n.hc-wfc-remove:hover { color: #c0341d; background: #fdecea;\n}\n.hc-wfc-head .hc-wfc-remove { position: static;\n}\n.hc-wfc-head {\n    gap: 6px;\n    justify-content: flex-start;\n}\n.hc-wfc-head .hc-wfc-remove {\n    margin-left: auto;\n}\n.hc-wfc-addwrap {\n    display: inline-flex;\n    align-items: center;\n}\n.hc-wfc-add { display: flex; flex-wrap: wrap; gap: 6px;\n}\n.hc-wfc-add button { padding: 5px 12px; color: #3a49c9; background: #eceefd; border: 1px dashed #aab2f5; border-radius: 16px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.15s;\n}\n.hc-wfc-add button:hover { background: #dde1fb;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n/* This component is also used outside of the workflow page (prospect page) */\n.hc-wfe .hc-workflow-badge {\n    padding: 1px 8px;\n    border-radius: 10px;\n    background: #e5e7eb;\n    color: #444;\n    font-size: 11px;\n}\n.hc-wfe-branch {\n    margin-left: 6px;\n    font-size: 11px;\n    font-weight: 600;\n}\n.hc-wfe-branch.yes {\n    color: #2e9d5c;\n}\n.hc-wfe-branch.no {\n    color: #d05a45;\n}\n.hc-wfe-head {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 6px 10px;\n}\n.hc-wfe-subject {\n    color: #555;\n    font-size: 13px;\n}\n.hc-workflow-badge.exec-completed {\n    background: #d6f5df;\n    color: #14633a;\n}\n.hc-workflow-badge.exec-failed {\n    background: #fdecea;\n    color: #8a1f11;\n}\n.hc-workflow-badge.exec-waiting,\n.hc-workflow-badge.exec-pending,\n.hc-workflow-badge.exec-running {\n    background: #e3ecff;\n    color: #1b4aa8;\n}\n.hc-workflow-badge.exec-cancelled,\n.hc-workflow-badge.exec-skipped {\n    background: #ececec;\n    color: #888;\n}\n.hc-wfe-waiting {\n    margin: 8px 0 0;\n    color: #1b4aa8;\n    font-size: 13px;\n}\n.hc-wfe-error {\n    margin: 4px 0 0;\n    color: #c0341d;\n    font-size: 13px;\n}\n.hc-wfe-steps {\n    margin: 10px 0 0;\n    padding: 0;\n    list-style: none;\n}\n.hc-wfe-steps li {\n    display: flex;\n    gap: 10px;\n    padding: 6px 0;\n    border-top: 1px solid #eee;\n}\n.hc-wfe-icon {\n    flex: none;\n    width: 20px;\n    color: #14633a;\n    text-align: center;\n}\n.hc-wfe-steps li.failed .hc-wfe-icon {\n    color: #c0341d;\n}\n.hc-wfe-steps li.skipped .hc-wfe-icon,\n.hc-wfe-steps li.waiting .hc-wfe-icon {\n    color: #888;\n}\n.hc-wfe-step {\n    flex: 1;\n    min-width: 0;\n}\n.hc-wfe-title {\n    font-size: 13px;\n    font-weight: 600;\n}\n.hc-wfe-detail,\n.hc-wfe-time {\n    color: #777;\n    font-size: 12px;\n}\n.hc-wfe-none {\n    color: #888;\n    font-size: 13px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-wff select,\n.hc-wff input[type=\"text\"],\n.hc-wff input[type=\"number\"],\n.hc-wff input[type=\"time\"],\n.hc-wff input[type=\"date\"],\n.hc-wff textarea {\n    width: 100%;\n    box-sizing: border-box;\n}\n.hc-wff select,\n.hc-wff input[type=\"text\"],\n.hc-wff input[type=\"number\"],\n.hc-wff input[type=\"time\"],\n.hc-wff input[type=\"date\"],\n.hc-wff textarea {\n    padding: 7px 10px;\n    color: #2b2f3a;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n    font-size: 13px;\n    transition: border-color 0.15s, box-shadow 0.15s;\n}\n.hc-wff select:focus,\n.hc-wff input:focus,\n.hc-wff textarea:focus {\n    border-color: #5b6cf0;\n    box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15);\n    outline: 0;\n}\n.hc-wff-check {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    margin-right: 8px;\n    font-size: 13px;\n    cursor: pointer;\n}\n.hc-wff-list {\n    max-height: 190px;\n    overflow: auto;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n}\n.hc-wff-item {\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    margin: 0;\n    padding: 7px 10px;\n    border-bottom: 1px solid #f0f1f6;\n    font-size: 13px;\n    cursor: pointer;\n}\n.hc-wff-item:hover {\n    background: #f5f6fe;\n}\n.hc-wff-item.on {\n    color: #3a49c9;\n    background: #eceefd;\n    font-weight: 600;\n}\n.hc-wff-count {\n    position: sticky;\n    bottom: 0;\n    margin: 0;\n    padding: 4px 10px;\n    color: #7a7f90;\n    background: #fafbfd;\n    font-size: 11px;\n}\n.hc-wff-variables {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 4px;\n    margin-top: 6px;\n    color: #7a7f90;\n    font-size: 11px;\n}\n.hc-wff-variables button {\n    padding: 2px 8px;\n    color: #3a49c9;\n    background: #eceefd;\n    border: 0;\n    border-radius: 10px;\n    font-size: 11px;\n    cursor: pointer;\n}\n.hc-wff-variables button:hover {\n    background: #dde1fb;\n}\n.hc-wff-create {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    margin-top: 6px;\n}\n.hc-wff-create button {\n    align-self: flex-start;\n    padding: 6px 14px;\n    color: #fff;\n    background: #4b5ce8;\n    border: 0;\n    border-radius: 8px;\n    font-size: 13px;\n    font-weight: 600;\n    cursor: pointer;\n}\n.hc-wff-create button:disabled {\n    opacity: 0.5;\n    cursor: not-allowed;\n}\n.hc-wff-create .hc-wff-warn {\n    margin: 0;\n}\n.hc-wff-warn {\n    margin: 4px 0 0;\n    padding: 6px 10px;\n    color: #8a5a00;\n    background: #fff7dd;\n    border-radius: 6px;\n    font-size: 12px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-tip {\n    display: inline-block;\n    margin-left: 4px;\n    vertical-align: middle;\n}\n\n/* Two classes: stronger than the rules of the buttons around it (menus, toolbars) */\n.hc-tip > .hc-tip-button {\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    box-sizing: border-box;\n    width: 16px;\n    min-width: 16px;\n    height: 16px;\n    min-height: 16px;\n    margin: 0;\n    padding: 0;\n    color: #3a49c9;\n    background: #eceefd;\n    border: 0;\n    border-radius: 50%;\n    box-shadow: none;\n    font-family: Georgia, \"Times New Roman\", serif;\n    font-size: 11px;\n    font-style: italic;\n    font-weight: 700;\n    line-height: 1;\n    letter-spacing: 0;\n    text-transform: none;\n    cursor: pointer;\n}\n.hc-tip > .hc-tip-button:hover,\n.hc-tip > .hc-tip-button.open {\n    color: #fff;\n    background: #4b5ce8;\n}\n.hc-tip-pop {\n    position: fixed;\n    z-index: 2000;\n    width: 250px;\n    padding: 10px 12px;\n    color: #2b2f3a;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 10px;\n    box-shadow: 0 10px 30px rgba(30, 35, 60, 0.22);\n    font-size: 12px;\n    font-style: normal;\n    font-weight: 400;\n    letter-spacing: 0;\n    line-height: 1.45;\n    text-align: left;\n    text-transform: none;\n    white-space: normal;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-wfn {\n    display: flex;\n    flex: none;\n    flex-direction: column;\n    gap: 4px;\n    width: 320px;\n    padding: 12px;\n    overflow: auto;\n    background: #fff;\n    border-left: 1px solid #ddd;\n}\n.hc-wfn select,\n.hc-wfn input[type=\"text\"],\n.hc-wfn input[type=\"datetime-local\"] {\n    width: 100%;\n    box-sizing: border-box;\n    padding: 7px 10px;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n    font-size: 13px;\n}\n.hc-wfn-head {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    font-size: 15px;\n}\n.hc-wfn-close { width: 28px; height: 28px; padding: 0; color: #6b7080; background: #f0f1f6; border: 0; border-radius: 50%; font-size: 18px; line-height: 1; cursor: pointer;\n}\n.hc-wfn-close:hover { background: #e1e3ec;\n}\n.hc-wfn-label {\n    margin-top: 8px;\n    color: #555;\n    font-size: 12px;\n    font-weight: 600;\n}\n.hc-wfn-required {\n    color: #c0341d;\n}\n.hc-wfn-help {\n    margin: 4px 0 0;\n    color: #888;\n    font-size: 12px;\n}\n.hc-wfn-danger {\n    margin: 4px 0 0;\n    color: #c0341d;\n    font-size: 12px;\n}\n.hc-wfn-legacy {\n    max-height: 160px;\n    margin: 4px 0 0;\n    padding: 6px;\n    overflow: auto;\n    background: #f4f5f9;\n    border-radius: 4px;\n    font-size: 11px;\n}\n.hc-wfn-duration {\n    display: flex;\n    gap: 6px;\n}\n.hc-wfn-duration label {\n    flex: 1;\n    min-width: 0;\n    font-size: 11px;\n    color: #777;\n}\n.hc-wfn-output {\n    display: flex;\n    align-items: center;\n    gap: 6px;\n}\n.hc-wfn-branch {\n    flex: none;\n    width: 34px;\n    font-size: 12px;\n    font-weight: 600;\n}\n.hc-wfn-branch.yes {\n    color: #2e9d5c;\n}\n.hc-wfn-branch.no {\n    color: #d05a45;\n}\n.hc-wfn-delete { margin-top: 16px; padding: 8px 12px; color: #c0341d; background: #fff; border: 1px solid #f1c3bb; border-radius: 8px; font-weight: 600; cursor: pointer;\n}\n.hc-wfn-delete:hover { background: #fdecea;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n#hc-workflow {\n    display: flex;\n    width: 100%;\n    height: 100%;\n    background-color: #f7f7fb;\n}\n#hc-workflow-mobile {\n    display: flex;\n    flex: 1;\n    align-items: center;\n    justify-content: center;\n    padding: 30px;\n    font-size: 20px;\n    text-align: center;\n}\n#hc-workflow-list {\n    position: relative;\n    display: flex;\n    flex-direction: column;\n    flex: none;\n    width: 290px;\n    overflow: auto;\n    background: #fff;\n    border-right: 1px solid #e3e6ef;\n}\n.hc-workflow-list-title {\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    padding: 18px 16px 10px;\n    font-size: 17px;\n    font-weight: 700;\n}\n.hc-workflow-count {\n    padding: 1px 8px;\n    color: #3a49c9;\n    background: #eceefd;\n    border-radius: 10px;\n    font-size: 12px;\n}\n.hc-workflow-new {\n    align-items: center;\n    display: flex;\n    gap: 6px;\n    padding: 4px 14px 14px;\n}\n.hc-workflow-new input {\n    flex: 1;\n    min-width: 0;\n    padding: 8px 10px;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n    font-size: 13px;\n}\n.hc-workflow-new input:focus {\n    border-color: #5b6cf0;\n    box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15);\n    outline: 0;\n}\n.hc-workflow-new button {\n    flex: none;\n    padding: 8px 12px;\n    color: #fff;\n    background: #4b5ce8;\n    border: 0;\n    border-radius: 8px;\n    font-size: 13px;\n    font-weight: 600;\n    cursor: pointer;\n}\n.hc-workflow-new button:hover:not(:disabled) {\n    background: #3a49c9;\n}\n.hc-workflow-new button:disabled {\n    opacity: 0.45;\n    cursor: not-allowed;\n}\n.hc-workflow-items {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    padding: 0 14px;\n}\n.hc-workflow-empty {\n    margin: 0;\n    padding: 14px;\n    color: #7a7f90;\n    background: #f6f7fb;\n    border-radius: 10px;\n    font-size: 13px;\n    line-height: 1.4;\n    text-align: center;\n}\n.hc-workflow-hint {\n    padding: 16px;\n    color: #7a7f90;\n    font-size: 13px;\n}\n.hc-workflow-list-item {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    padding: 10px 12px;\n    color: inherit;\n    text-decoration: none;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-left: 4px solid #c3c7d4;\n    border-radius: 10px;\n    transition: box-shadow 0.15s, border-color 0.15s;\n}\n.hc-workflow-list-item.active {\n    border-left-color: #2e9d5c;\n}\n.hc-workflow-list-item.paused {\n    border-left-color: #e8a317;\n}\n.hc-workflow-list-item:hover {\n    box-shadow: 0 3px 10px rgba(30, 35, 60, 0.1);\n}\n.hc-workflow-list-item.current {\n    background: #f1f2fd;\n    border-color: #aab2f5;\n    box-shadow: 0 0 0 2px rgba(91, 108, 240, 0.18);\n}\n.hc-workflow-list-top {\n    display: flex;\n    align-items: center;\n    gap: 8px;\n}\n.hc-workflow-list-name {\n    flex: 1;\n    min-width: 0;\n    overflow: hidden;\n    color: #2b2f3a;\n    font-size: 14px;\n    font-weight: 600;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-workflow-list-counts {\n    display: flex;\n    flex-wrap: wrap;\n    gap: 4px;\n}\n.hc-workflow-pill {\n    padding: 1px 8px;\n    color: #5d6272;\n    background: #f0f1f6;\n    border-radius: 10px;\n    font-size: 11px;\n}\n.hc-workflow-pill.exec-completed {\n    color: #14633a;\n    background: #d6f5df;\n}\n.hc-workflow-pill.exec-failed {\n    color: #8a1f11;\n    background: #fdecea;\n}\n.hc-workflow-pill.exec-waiting {\n    color: #1b4aa8;\n    background: #e3ecff;\n}\n.hc-workflow-badge {\n    flex: none;\n    padding: 2px 9px;\n    border-radius: 10px;\n    background: #e5e7eb;\n    color: #444;\n    font-size: 11px;\n    font-weight: 600;\n}\n.hc-workflow-origin {\n    color: #8e5bd6;\n    font-size: 11px;\n}\n.hc-workflow-badge.active {\n    background: #d6f5df;\n    color: #14633a;\n}\n.hc-workflow-badge.paused {\n    background: #fff0cc;\n    color: #8a5a00;\n}\n.hc-workflow-badge.archived {\n    background: #ececec;\n    color: #888;\n}\n.hc-workflow-templates {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    margin-top: auto;\n    padding: 14px;\n}\n.hc-workflow-templates-toggle {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding: 6px 2px;\n    color: #7a7f90;\n    background: transparent;\n    border: 0;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    text-transform: uppercase;\n    cursor: pointer;\n}\n.hc-workflow-chevron {\n    font-size: 16px;\n    transition: transform 0.15s;\n}\n.hc-workflow-chevron.open {\n    transform: rotate(90deg);\n}\n.hc-workflow-template {\n    display: flex;\n    align-items: flex-start;\n    gap: 10px;\n    padding: 9px 10px;\n    text-align: left;\n    background: #fafbfd;\n    border: 1px solid #e3e6ef;\n    border-radius: 10px;\n    cursor: pointer;\n    transition: border-color 0.15s, background 0.15s;\n}\n.hc-workflow-template:hover:not(:disabled) {\n    background: #f1f2fd;\n    border-color: #aab2f5;\n}\n.hc-workflow-template:disabled {\n    opacity: 0.5;\n}\n.hc-workflow-template-icon {\n    display: flex;\n    flex: none;\n    align-items: center;\n    justify-content: center;\n    width: 30px;\n    height: 30px;\n    background: #eceefd;\n    border-radius: 8px;\n    font-size: 15px;\n}\n.hc-workflow-template-text {\n    display: flex;\n    flex-direction: column;\n    gap: 2px;\n    min-width: 0;\n}\n.hc-workflow-template strong {\n    color: #2b2f3a;\n    font-size: 13px;\n}\n.hc-workflow-template small {\n    color: #7a7f90;\n    font-size: 11px;\n    line-height: 1.35;\n}\n#hc-workflow-main {\n    position: relative;\n    display: flex;\n    flex: 1;\n    min-width: 0;\n    flex-direction: column;\n}\n.hc-workflow-tabs {\n    display: flex;\n    gap: 4px;\n    padding: 10px 16px 0;\n    background: #fff;\n    border-bottom: 1px solid #e3e6ef;\n}\n.hc-workflow-tabs button {\n    padding: 8px 16px;\n    color: #7a7f90;\n    background: transparent;\n    border: 0;\n    border-bottom: 3px solid transparent;\n    font-size: 13px;\n    font-weight: 600;\n    cursor: pointer;\n}\n.hc-workflow-tabs button:hover {\n    color: #2b2f3a;\n}\n.hc-workflow-tabs button.active {\n    color: #3a49c9;\n    border-bottom-color: #4b5ce8;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-wfb {\n    display: flex;\n    flex: 1;\n    flex-direction: column;\n    min-height: 0;\n}\n.hc-wfb-toolbar {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 8px;\n    padding: 10px 16px;\n    background: #fff;\n    border-bottom: 1px solid #e3e6ef;\n}\n.hc-wfb-toolbar button {\n    padding: 7px 14px;\n    color: #2b2f3a;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n    font-size: 13px;\n    font-weight: 600;\n    cursor: pointer;\n    transition: background 0.15s, box-shadow 0.15s;\n}\n.hc-wfb-toolbar button:hover:not(:disabled) {\n    background: #f3f4f9;\n}\n.hc-wfb-toolbar button:disabled {\n    opacity: 0.5;\n    cursor: not-allowed;\n}\n.hc-wfb-name {\n    width: 260px;\n    padding: 6px 10px;\n    border: 1px solid transparent;\n    border-radius: 8px;\n    font-size: 16px;\n    font-weight: 700;\n}\n.hc-wfb-name:hover,\n.hc-wfb-name:focus {\n    border-color: #d5d9e4;\n    outline: 0;\n}\n.hc-wfb-muted {\n    color: #7a7f90;\n    font-size: 12px;\n}\n.hc-wfb-warn {\n    padding: 2px 8px;\n    color: #8a5a00;\n    background: #fff7dd;\n    border-radius: 10px;\n    font-size: 12px;\n}\n.hc-wfb-save.error {\n    color: #c0341d;\n}\n.hc-wfb-spacer {\n    flex: 1;\n}\n.hc-wfb-toolbar .hc-wfb-primary {\n    color: #fff;\n    background: #4b5ce8;\n    border-color: #4b5ce8;\n    box-shadow: 0 2px 6px rgba(75, 92, 232, 0.35);\n}\n.hc-wfb-toolbar .hc-wfb-primary:hover:not(:disabled) {\n    background: #3a49c9;\n}\n.hc-wfb-more {\n    position: relative;\n}\n.hc-wfb-more-toggle {\n    padding: 7px 12px;\n    font-size: 16px;\n    line-height: 1;\n}\n.hc-wfb-more-menu {\n    position: absolute;\n    top: calc(100% + 6px);\n    right: 0;\n    z-index: 20;\n    display: flex;\n    flex-direction: column;\n    min-width: 190px;\n    padding: 6px;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 10px;\n    box-shadow: 0 8px 24px rgba(30, 35, 60, 0.15);\n}\n.hc-wfb-toolbar .hc-wfb-more-menu button {\n    text-align: left;\n    border: 0;\n    font-weight: 500;\n}\n.hc-wfb-toolbar .hc-wfb-danger {\n    color: #c0341d;\n}\n.hc-wfb-errors {\n    padding: 8px 14px;\n    background: #fdecea;\n    color: #8a1f11;\n    font-size: 13px;\n}\n.hc-wfb-errors ul {\n    margin: 4px 0 0 18px;\n    padding: 0;\n}\n.hc-wfb-ok {\n    padding: 6px 14px;\n    background: #e6f6ec;\n    color: #14633a;\n    font-size: 13px;\n}\n.hc-wfb-body {\n    display: flex;\n    flex: 1;\n    min-height: 0;\n}\n.hc-wfb-palette {\n    display: flex;\n    flex: none;\n    flex-direction: column;\n    gap: 6px;\n    width: 170px;\n    padding: 14px 12px;\n    overflow: auto;\n    background: #fff;\n    border-right: 1px solid #e3e6ef;\n}\n.hc-wfb-palette-title {\n    color: #7a7f90;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    text-transform: uppercase;\n}\n.hc-wfb-palette-item {\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    padding: 8px 10px;\n    text-align: left;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 8px;\n    font-size: 13px;\n    cursor: grab;\n    transition: border-color 0.15s, box-shadow 0.15s;\n}\n.hc-wfb-palette-item:hover {\n    border-color: #aab2f5;\n    box-shadow: 0 2px 8px rgba(75, 92, 232, 0.12);\n}\n.hc-wfb-section {\n    margin-top: 10px;\n    color: #3a49c9;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    text-transform: uppercase;\n}\n.hc-wfb-section-help {\n    margin: -2px 0 2px;\n    color: #8a8fa0;\n    font-size: 11px;\n    line-height: 1.3;\n}\n.hc-wfb-palette-item.on {\n    background: #eceefd;\n    border-color: #aab2f5;\n}\n.hc-wfb-palette-hint {\n    color: #8a8fa0;\n    font-size: 11px;\n}\n.type-trigger .hc-wfb-palette-dot {\n    background: #2e9d5c;\n}\n.hc-wfb-palette-dot {\n    width: 10px;\n    height: 10px;\n    border-radius: 50%;\n    background: #8a94a6;\n}\n.type-condition .hc-wfb-palette-dot {\n    background: #e8a317;\n}\n.type-action .hc-wfb-palette-dot {\n    background: #4b5ce8;\n}\n.type-wait .hc-wfb-palette-dot,\n.type-wait_event .hc-wfb-palette-dot {\n    background: #8e5bd6;\n}\n.type-end .hc-wfb-palette-dot {\n    background: #666;\n}\n.hc-wfb-canvas {\n    position: relative;\n    flex: 1;\n    min-width: 0;\n    overflow: auto;\n    background-color: #fbfbfe;\n    background-image: radial-gradient(#d9dbe6 1px, transparent 1px);\n    background-size: 20px 20px;\n}\n.hc-wfb-connecting {\n    position: sticky;\n    top: 0;\n    left: 0;\n    z-index: 5;\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    padding: 6px 12px;\n    background: #fff7dd;\n    color: #6b4b00;\n    font-size: 13px;\n}\n.hc-wfb-surface {\n    position: relative;\n}\n.hc-wfb-links {\n    position: absolute;\n    top: 0;\n    left: 0;\n    pointer-events: none;\n}\n.hc-wfb-link {\n    fill: none;\n    stroke: #aab0c0;\n    stroke-width: 2;\n}\n.hc-wfb-link.yes {\n    stroke: #2e9d5c;\n}\n.hc-wfb-link.no {\n    stroke: #d05a45;\n}\n.hc-wfb-link-remove {\n    cursor: pointer;\n    pointer-events: all;\n    opacity: 0.35;\n}\n.hc-wfb-link-remove:hover {\n    opacity: 1;\n}\n.hc-wfb-link-remove circle {\n    fill: #fff;\n    stroke: #8a94a6;\n}\n.hc-wfb-link-remove text {\n    font-size: 13px;\n    fill: #555;\n}\n.hc-wfb-node {\n    position: absolute;\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    width: 230px;\n    height: 76px;\n    box-sizing: border-box;\n    padding: 0 12px;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 14px;\n    box-shadow: 0 2px 8px rgba(30, 35, 60, 0.08);\n    cursor: pointer;\n    -webkit-user-select: none;\n       -moz-user-select: none;\n            user-select: none;\n    transition: box-shadow 0.15s, border-color 0.15s;\n}\n.hc-wfb-node:hover {\n    box-shadow: 0 4px 14px rgba(30, 35, 60, 0.14);\n}\n.hc-wfb-node-icon {\n    display: flex;\n    flex: none;\n    align-items: center;\n    justify-content: center;\n    width: 38px;\n    height: 38px;\n    color: #fff;\n    background: #8a94a6;\n    border-radius: 10px;\n    font-size: 18px;\n}\n.hc-wfb-node.type-trigger .hc-wfb-node-icon {\n    background: #2e9d5c;\n}\n.hc-wfb-node.type-condition .hc-wfb-node-icon {\n    background: #e8a317;\n}\n.hc-wfb-node.type-action .hc-wfb-node-icon {\n    background: #4b5ce8;\n}\n.hc-wfb-node.type-wait .hc-wfb-node-icon,\n.hc-wfb-node.type-wait_event .hc-wfb-node-icon {\n    background: #8e5bd6;\n}\n.hc-wfb-node.type-end .hc-wfb-node-icon {\n    background: #555b6b;\n}\n.hc-wfb-node.selected {\n    border-color: #4b5ce8;\n    box-shadow: 0 0 0 3px rgba(75, 92, 232, 0.22);\n}\n.hc-wfb-node.orphan {\n    border-style: dashed;\n    border-color: #d05a45;\n}\n.hc-wfb-node.invalid {\n    border-color: #d05a45;\n    box-shadow: 0 0 0 3px rgba(208, 90, 69, 0.2);\n}\n.hc-wfb-error-link {\n    color: #8a1f11;\n    text-decoration: underline;\n    cursor: pointer;\n}\n.hc-wfb-node.target {\n    outline: 2px dashed #4b5ce8;\n}\n.hc-wfb-node-main {\n    display: flex;\n    flex: 1;\n    flex-direction: column;\n    min-width: 0;\n    cursor: move;\n}\n.hc-wfb-node-type {\n    color: #7a7f90;\n    font-size: 10px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    text-transform: uppercase;\n}\n.hc-wfb-node-body {\n    display: -webkit-box;\n    overflow: hidden;\n    color: #2b2f3a;\n    font-size: 13px;\n    font-weight: 600;\n    line-height: 1.25;\n    -webkit-box-orient: vertical;\n    -webkit-line-clamp: 2;\n}\n.hc-wfb-node-name {\n    overflow: hidden;\n    color: #1f2330;\n    font-size: 13px;\n    font-weight: 700;\n    line-height: 1.25;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-wfb-node-body.small {\n    color: #6b7080;\n    font-size: 11px;\n    font-weight: 500;\n    -webkit-line-clamp: 1;\n}\n.hc-wfb-node-remove {\n    position: absolute;\n    top: -8px;\n    right: -8px;\n    display: none;\n    width: 20px;\n    height: 20px;\n    color: #8a8fa0;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 50%;\n    font-size: 14px;\n    line-height: 18px;\n    text-align: center;\n}\n.hc-wfb-node:hover .hc-wfb-node-remove {\n    display: block;\n}\n.hc-wfb-node-remove:hover {\n    color: #c0341d;\n    border-color: #f1c3bb;\n}\n.hc-wfb-port {\n    position: absolute;\n    width: 14px;\n    height: 14px;\n    padding: 0;\n    background: #fff;\n    border: 2px solid #8a94a6;\n    border-radius: 50%;\n}\n.hc-wfb-port.in {\n    top: -8px;\n    left: calc(50% - 7px);\n}\n.hc-wfb-port.out {\n    bottom: -8px;\n    cursor: crosshair;\n}\n.hc-wfb-port.out:not(.yes):not(.no) {\n    left: calc(50% - 7px);\n}\n.hc-wfb-port.out.active {\n    background: #4b5ce8;\n    border-color: #4b5ce8;\n}\n.hc-wfb-port.out.yes {\n    border-color: #2e9d5c;\n}\n.hc-wfb-port.out.no {\n    border-color: #d05a45;\n}\n.hc-wfb-port.out span {\n    position: absolute;\n    top: 14px;\n    left: -6px;\n    color: #555;\n    font-size: 10px;\n}\n.hc-wfb-add {\n    position: absolute;\n    bottom: -14px;\n    width: 28px;\n    height: 28px;\n    padding: 0;\n    color: #fff;\n    background: #4b5ce8;\n    border: 3px solid #fbfbfe;\n    border-radius: 50%;\n    box-shadow: 0 2px 6px rgba(75, 92, 232, 0.4);\n    font-size: 18px;\n    font-weight: 700;\n    line-height: 20px;\n    cursor: pointer;\n    transition: transform 0.12s;\n}\n.hc-wfb-add:hover:not(:disabled) {\n    transform: scale(1.15);\n}\n.hc-wfb-add:disabled {\n    display: none;\n}\n.hc-wfb-add.yes {\n    background: #2e9d5c;\n    box-shadow: 0 2px 6px rgba(46, 157, 92, 0.4);\n}\n.hc-wfb-add.no {\n    background: #d05a45;\n    box-shadow: 0 2px 6px rgba(208, 90, 69, 0.4);\n}\n.hc-wfb-add-label {\n    position: absolute;\n    top: 26px;\n    left: 0;\n    width: 22px;\n    color: #555;\n    font-size: 10px;\n    font-weight: 700;\n    line-height: 1;\n    text-align: center;\n}\n.hc-wfb-link-insert {\n    cursor: pointer;\n    pointer-events: all;\n    opacity: 0.35;\n}\n.hc-wfb-link-insert:hover {\n    opacity: 1;\n}\n.hc-wfb-link-insert circle {\n    fill: #4b5ce8;\n    stroke: #4b5ce8;\n}\n.hc-wfb-link-insert text {\n    font-size: 13px;\n    font-weight: 700;\n    fill: #fff;\n}\n.hc-wfb-menu {\n    position: absolute;\n    z-index: 30;\n    display: flex;\n    flex-direction: column;\n    width: 280px;\n    padding: 6px;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 12px;\n    box-shadow: 0 10px 30px rgba(30, 35, 60, 0.2);\n}\n.hc-wfb-menu-title {\n    padding: 6px 8px;\n    color: #7a7f90;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.05em;\n    text-transform: uppercase;\n}\n.hc-wfb-menu-item {\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    padding: 8px;\n    text-align: left;\n    background: transparent;\n    border: 0;\n    border-radius: 8px;\n    cursor: pointer;\n}\n.hc-wfb-menu-item:hover {\n    background: #f1f2fd;\n}\n.hc-wfb-menu-item strong {\n    display: block;\n    color: #2b2f3a;\n    font-size: 13px;\n}\n.hc-wfb-menu-item small {\n    display: block;\n    color: #7a7f90;\n    font-size: 11px;\n    line-height: 1.3;\n}\n.hc-wfb-menu-icon {\n    display: flex;\n    flex: none;\n    align-items: center;\n    justify-content: center;\n    width: 32px;\n    height: 32px;\n    color: #fff;\n    background: #8a94a6;\n    border-radius: 8px;\n    font-size: 16px;\n}\n.hc-wfb-menu-item.type-condition .hc-wfb-menu-icon {\n    background: #e8a317;\n}\n.hc-wfb-menu-item.type-action .hc-wfb-menu-icon {\n    background: #4b5ce8;\n}\n.hc-wfb-menu-item.type-wait .hc-wfb-menu-icon,\n.hc-wfb-menu-item.type-wait_event .hc-wfb-menu-icon {\n    background: #8e5bd6;\n}\n.hc-wfb-menu-item.type-end .hc-wfb-menu-icon {\n    background: #555b6b;\n}\n.hc-wfb-menu-item.link .hc-wfb-menu-icon {\n    color: #555b6b;\n    background: #eceef4;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-wfm {\n    flex: 1;\n    min-height: 0;\n    padding: 18px;\n    overflow: auto;\n}\n.hc-wfm-block {\n    position: relative;\n    margin-bottom: 18px;\n    padding: 16px;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 12px;\n    box-shadow: 0 1px 4px rgba(30, 35, 60, 0.05);\n}\n.hc-wfm-title {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 8px;\n    margin-bottom: 14px;\n}\n.hc-wfm-title strong {\n    margin-right: auto;\n    font-size: 15px;\n}\n.hc-wfm select,\n.hc-wfm-title button {\n    padding: 6px 10px;\n    color: #2b2f3a;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n    font-size: 13px;\n}\n.hc-wfm-title button,\n.hc-wfm-more {\n    font-weight: 600;\n    cursor: pointer;\n}\n.hc-wfm-title button:hover,\n.hc-wfm-more:hover:not(:disabled) {\n    background: #f3f4f9;\n}\n.hc-wfm-cards {\n    display: grid;\n    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));\n    gap: 12px;\n    margin-bottom: 16px;\n}\n.hc-wfm-card {\n    display: flex;\n    flex-direction: column;\n    gap: 2px;\n    padding: 14px 16px;\n    background: #f6f7fb;\n    border-top: 4px solid #4b5ce8;\n    border-radius: 10px;\n}\n.hc-wfm-card:nth-child(2) {\n    border-top-color: #2e9d5c;\n}\n.hc-wfm-card:nth-child(3) {\n    border-top-color: #c3c7d4;\n}\n.hc-wfm-card:nth-child(4) {\n    border-top-color: #8e5bd6;\n}\n.hc-wfm-card.bad {\n    background: #fdecea;\n    border-top-color: #d05a45;\n}\n.hc-wfm-card b {\n    font-size: 26px;\n    line-height: 1.1;\n}\n.hc-wfm-card span {\n    color: #7a7f90;\n    font-size: 12px;\n}\n.hc-wfm .bad {\n    color: #c0341d;\n}\n.hc-wfm-table {\n    width: 100%;\n    border-collapse: collapse;\n    font-size: 13px;\n}\n.hc-wfm-table th {\n    padding: 8px 10px;\n    color: #7a7f90;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.04em;\n    text-align: left;\n    text-transform: uppercase;\n}\n.hc-wfm-table td {\n    padding: 10px;\n    text-align: left;\n    border-top: 1px solid #eef0f5;\n}\n.hc-wfm-table tbody tr {\n    cursor: pointer;\n}\n.hc-wfm-table tbody tr:hover {\n    background: #f4f5fe;\n}\n.hc-wfm-columns {\n    display: flex;\n    flex-wrap: wrap;\n    gap: 16px;\n    margin-top: 16px;\n    font-size: 13px;\n}\n.hc-wfm-columns > div {\n    flex: 1;\n    min-width: 220px;\n    padding: 12px 14px;\n    background: #f6f7fb;\n    border-radius: 10px;\n}\n.hc-wfm-columns h4 {\n    margin: 0 0 6px;\n    font-size: 13px;\n}\n.hc-wfm-list {\n    margin: 0;\n    padding-left: 16px;\n    line-height: 1.6;\n}\n.hc-wfm-split {\n    display: flex;\n    gap: 16px;\n    align-items: flex-start;\n}\n.hc-wfm-rows {\n    position: relative;\n    flex: 1;\n    min-width: 0;\n}\n.hc-wfm-detail {\n    flex: none;\n    width: 380px;\n    padding: 14px;\n    background: #fafbfe;\n    border: 1px solid #e3e6ef;\n    border-radius: 10px;\n}\n.hc-wfm-row {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 4px 12px;\n    margin-bottom: 6px;\n    padding: 10px 12px;\n    background: #fff;\n    border: 1px solid #e3e6ef;\n    border-radius: 10px;\n    cursor: pointer;\n    transition: border-color 0.15s, box-shadow 0.15s;\n}\n.hc-wfm-row:hover {\n    box-shadow: 0 2px 8px rgba(30, 35, 60, 0.08);\n}\n.hc-wfm-row.current {\n    background: #f1f2fd;\n    border-color: #aab2f5;\n}\n.hc-wfm-row-main {\n    display: flex;\n    flex: 1;\n    flex-direction: column;\n    min-width: 0;\n    font-size: 13px;\n}\n.hc-wfm-row-main span {\n    color: #7a7f90;\n    font-size: 12px;\n}\n.hc-wfm-row-date {\n    color: #7a7f90;\n    font-size: 12px;\n}\n.hc-wfm-row-error {\n    flex-basis: 100%;\n    overflow: hidden;\n    padding: 4px 8px;\n    color: #8a1f11;\n    background: #fdecea;\n    border-radius: 6px;\n    font-size: 12px;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-wfm-more {\n    margin-top: 8px;\n    padding: 7px 16px;\n    background: #fff;\n    border: 1px solid #d5d9e4;\n    border-radius: 8px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_style_index_0_id_4b7d5fce_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_style_index_0_id_4b7d5fce_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_style_index_0_id_4b7d5fce_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_style_index_0_id_31626764_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_style_index_0_id_31626764_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_style_index_0_id_31626764_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_style_index_0_id_712dfd36_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_style_index_0_id_712dfd36_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_style_index_0_id_712dfd36_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_style_index_0_id_b70e4716_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_style_index_0_id_b70e4716_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_style_index_0_id_b70e4716_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_style_index_0_id_23a3b904_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_style_index_0_id_23a3b904_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_style_index_0_id_23a3b904_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_style_index_0_id_1bd84c18_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_style_index_0_id_1bd84c18_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_style_index_0_id_1bd84c18_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_style_index_0_id_1b3bc492_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_style_index_0_id_1b3bc492_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_style_index_0_id_1b3bc492_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_style_index_0_id_a8b81814_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_style_index_0_id_a8b81814_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_style_index_0_id_a8b81814_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/workflow/ConditionGroup.vue":
/*!*************************************************************!*\
  !*** ./resources/js/components/workflow/ConditionGroup.vue ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ConditionGroup_vue_vue_type_template_id_4b7d5fce__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ConditionGroup.vue?vue&type=template&id=4b7d5fce */ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce");
/* harmony import */ var _ConditionGroup_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ConditionGroup.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js");
/* harmony import */ var _ConditionGroup_vue_vue_type_style_index_0_id_4b7d5fce_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css */ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ConditionGroup_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ConditionGroup_vue_vue_type_template_id_4b7d5fce__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/ConditionGroup.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/ExecutionSteps.vue":
/*!*************************************************************!*\
  !*** ./resources/js/components/workflow/ExecutionSteps.vue ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ExecutionSteps_vue_vue_type_template_id_31626764__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ExecutionSteps.vue?vue&type=template&id=31626764 */ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764");
/* harmony import */ var _ExecutionSteps_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ExecutionSteps.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js");
/* harmony import */ var _ExecutionSteps_vue_vue_type_style_index_0_id_31626764_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css */ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ExecutionSteps_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ExecutionSteps_vue_vue_type_template_id_31626764__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/ExecutionSteps.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/FieldInput.vue":
/*!*********************************************************!*\
  !*** ./resources/js/components/workflow/FieldInput.vue ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _FieldInput_vue_vue_type_template_id_712dfd36__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./FieldInput.vue?vue&type=template&id=712dfd36 */ "./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36");
/* harmony import */ var _FieldInput_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./FieldInput.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js");
/* harmony import */ var _FieldInput_vue_vue_type_style_index_0_id_712dfd36_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css */ "./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_FieldInput_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_FieldInput_vue_vue_type_template_id_712dfd36__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/FieldInput.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/HelpTip.vue":
/*!******************************************************!*\
  !*** ./resources/js/components/workflow/HelpTip.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _HelpTip_vue_vue_type_template_id_b70e4716__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./HelpTip.vue?vue&type=template&id=b70e4716 */ "./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716");
/* harmony import */ var _HelpTip_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./HelpTip.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js");
/* harmony import */ var _HelpTip_vue_vue_type_style_index_0_id_b70e4716_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css */ "./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_HelpTip_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_HelpTip_vue_vue_type_template_id_b70e4716__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/HelpTip.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/NodeEditor.vue":
/*!*********************************************************!*\
  !*** ./resources/js/components/workflow/NodeEditor.vue ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _NodeEditor_vue_vue_type_template_id_23a3b904__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./NodeEditor.vue?vue&type=template&id=23a3b904 */ "./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904");
/* harmony import */ var _NodeEditor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NodeEditor.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js");
/* harmony import */ var _NodeEditor_vue_vue_type_style_index_0_id_23a3b904_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css */ "./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_NodeEditor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_NodeEditor_vue_vue_type_template_id_23a3b904__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/NodeEditor.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/Workflow.vue":
/*!*******************************************************!*\
  !*** ./resources/js/components/workflow/Workflow.vue ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Workflow_vue_vue_type_template_id_1bd84c18__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Workflow.vue?vue&type=template&id=1bd84c18 */ "./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18");
/* harmony import */ var _Workflow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Workflow.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js");
/* harmony import */ var _Workflow_vue_vue_type_style_index_0_id_1bd84c18_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css */ "./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Workflow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Workflow_vue_vue_type_template_id_1bd84c18__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/Workflow.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/WorkflowBuilder.vue":
/*!**************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowBuilder.vue ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _WorkflowBuilder_vue_vue_type_template_id_1b3bc492__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./WorkflowBuilder.vue?vue&type=template&id=1b3bc492 */ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492");
/* harmony import */ var _WorkflowBuilder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WorkflowBuilder.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js");
/* harmony import */ var _WorkflowBuilder_vue_vue_type_style_index_0_id_1b3bc492_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css */ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_WorkflowBuilder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_WorkflowBuilder_vue_vue_type_template_id_1b3bc492__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/WorkflowBuilder.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/WorkflowMonitor.vue":
/*!**************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowMonitor.vue ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _WorkflowMonitor_vue_vue_type_template_id_a8b81814__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./WorkflowMonitor.vue?vue&type=template&id=a8b81814 */ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814");
/* harmony import */ var _WorkflowMonitor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WorkflowMonitor.vue?vue&type=script&lang=js */ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js");
/* harmony import */ var _WorkflowMonitor_vue_vue_type_style_index_0_id_a8b81814_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css */ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_WorkflowMonitor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_WorkflowMonitor_vue_vue_type_template_id_a8b81814__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/workflow/WorkflowMonitor.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConditionGroup.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ExecutionSteps.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js":
/*!*********************************************************************************!*\
  !*** ./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./FieldInput.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js":
/*!******************************************************************************!*\
  !*** ./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HelpTip.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js":
/*!*********************************************************************************!*\
  !*** ./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NodeEditor.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Workflow.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowBuilder.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowMonitor.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_template_id_4b7d5fce__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_template_id_4b7d5fce__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConditionGroup.vue?vue&type=template&id=4b7d5fce */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=template&id=4b7d5fce");


/***/ }),

/***/ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764 ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_template_id_31626764__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_template_id_31626764__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ExecutionSteps.vue?vue&type=template&id=31626764 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=template&id=31626764");


/***/ }),

/***/ "./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36 ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_template_id_712dfd36__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_template_id_712dfd36__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./FieldInput.vue?vue&type=template&id=712dfd36 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=template&id=712dfd36");


/***/ }),

/***/ "./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716":
/*!************************************************************************************!*\
  !*** ./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716 ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_template_id_b70e4716__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_template_id_b70e4716__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HelpTip.vue?vue&type=template&id=b70e4716 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=template&id=b70e4716");


/***/ }),

/***/ "./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904 ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_template_id_23a3b904__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_template_id_23a3b904__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NodeEditor.vue?vue&type=template&id=23a3b904 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=template&id=23a3b904");


/***/ }),

/***/ "./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18 ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_template_id_1bd84c18__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_template_id_1bd84c18__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Workflow.vue?vue&type=template&id=1bd84c18 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=template&id=1bd84c18");


/***/ }),

/***/ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492 ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_template_id_1b3bc492__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_template_id_1b3bc492__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowBuilder.vue?vue&type=template&id=1b3bc492 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=template&id=1b3bc492");


/***/ }),

/***/ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814 ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_template_id_a8b81814__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_template_id_a8b81814__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowMonitor.vue?vue&type=template&id=a8b81814 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=template&id=a8b81814");


/***/ }),

/***/ "./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css":
/*!*********************************************************************************************************!*\
  !*** ./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConditionGroup_vue_vue_type_style_index_0_id_4b7d5fce_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ConditionGroup.vue?vue&type=style&index=0&id=4b7d5fce&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css":
/*!*********************************************************************************************************!*\
  !*** ./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ExecutionSteps_vue_vue_type_style_index_0_id_31626764_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/ExecutionSteps.vue?vue&type=style&index=0&id=31626764&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_FieldInput_vue_vue_type_style_index_0_id_712dfd36_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/FieldInput.vue?vue&type=style&index=0&id=712dfd36&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_HelpTip_vue_vue_type_style_index_0_id_b70e4716_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/HelpTip.vue?vue&type=style&index=0&id=b70e4716&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NodeEditor_vue_vue_type_style_index_0_id_23a3b904_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/NodeEditor.vue?vue&type=style&index=0&id=23a3b904&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css":
/*!***************************************************************************************************!*\
  !*** ./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Workflow_vue_vue_type_style_index_0_id_1bd84c18_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/Workflow.vue?vue&type=style&index=0&id=1bd84c18&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css ***!
  \**********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowBuilder_vue_vue_type_style_index_0_id_1b3bc492_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowBuilder.vue?vue&type=style&index=0&id=1b3bc492&lang=css");


/***/ }),

/***/ "./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css ***!
  \**********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_WorkflowMonitor_vue_vue_type_style_index_0_id_a8b81814_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/workflow/WorkflowMonitor.vue?vue&type=style&index=0&id=a8b81814&lang=css");


/***/ })

}]);