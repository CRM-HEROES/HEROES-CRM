"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_agent_chat_AgentPage_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ui/AgentButton.vue */ "./resources/js/components/agent/ui/AgentButton.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var ICONS = {
  write: "fa fa-pen",
  communication: "fa fa-paper-plane",
  destructive: "fa fa-trash"
};

// What is shown on its own line, not in the list of details
var APART = ["action", "prospects", "sample", "skipped_processed", "error"];

/**
 * A request for authorisation: an agent wants to do something that changes the
 * CRM or leaves it (messages, deletions, many records) and waits for a human.
 * It says what will happen, to how many records, and how risky it is; the
 * decision itself is taken on the server.
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    AgButton: _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  props: {
    action: {
      type: Object,
      required: true
    },
    working: {
      type: Boolean,
      "default": false
    }
  },
  emits: ["approve", "reject"],
  computed: {
    preview: function preview() {
      return this.action.preview || {};
    },
    // What the agent wants to do, in words; the tool name when the server gave none
    title: function title() {
      return typeof this.preview.action === "string" && this.preview.action ? this.preview.action : this.$t("agent.tools.".concat(this.action.tool), this.action.tool);
    },
    icon: function icon() {
      return ICONS[this.action.risk] || "fa fa-shield-halved";
    },
    destructive: function destructive() {
      return this.action.risk === "destructive";
    },
    eyebrow: function eyebrow() {
      var states = {
        pending: this.$t("agent.perm.asks", {
          agent: this.action.agent || "L'agent"
        }),
        approved: this.$t("agent.perm.status.running"),
        executed: this.$t("agent.perm.status.executed"),
        rejected: this.$t("agent.perm.status.rejected"),
        failed: this.$t("agent.perm.status.failed"),
        expired: this.$t("agent.perm.status.expired")
      };
      return states[this.action.status] || "";
    },
    count: function count() {
      return Number.isInteger(this.preview.prospects) ? this.preview.prospects : null;
    },
    sample: function sample() {
      return Array.isArray(this.preview.sample) ? this.preview.sample : [];
    },
    skipped: function skipped() {
      return Number(this.preview.skipped_processed) || 0;
    },
    details: function details() {
      var _this = this;
      return Object.entries(this.preview).filter(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          value = _ref2[1];
        return !APART.includes(key) && value !== null && value !== "" && value !== undefined;
      }).map(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
          key = _ref4[0],
          value = _ref4[1];
        return {
          key: key,
          value: _this.format(value)
        };
      });
    },
    percent: function percent() {
      var progress = this.action.progress;
      return progress && progress.total ? Math.min(100, Math.round(progress.done / progress.total * 100)) : 0;
    },
    expiry: function expiry() {
      return new Date(this.action.expires_at).toLocaleString();
    }
  },
  methods: {
    format: function format(value) {
      var _this2 = this;
      if (Array.isArray(value)) return value.map(function (item) {
        return _this2.format(item);
      }).join(", ");
      if (value && _typeof(value) === "object") {
        return Object.entries(value).map(function (_ref5) {
          var _ref6 = _slicedToArray(_ref5, 2),
            key = _ref6[0],
            item = _ref6[1];
          return "".concat(key, " : ").concat(_this2.format(item));
        }).join("\n");
      }
      if (typeof value === "boolean") return this.$t(value ? "agent.perm.yes" : "agent.perm.no");
      return String(value);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    agents: {
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    selectedId: {
      type: Number,
      "default": null
    }
  },
  emits: ["select", "info", "create"]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../ui/AgentButton.vue */ "./resources/js/components/agent/ui/AgentButton.vue");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    AgButton: _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  props: {
    open: {
      type: Boolean,
      "default": false
    },
    agent: {
      type: Object,
      "default": null
    },
    loading: {
      type: Boolean,
      "default": false
    },
    error: {
      type: String,
      "default": ""
    }
  },
  emits: ["close", "edit"]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/store */ "./resources/js/store/index.js");
/* harmony import */ var _apis_agent__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/apis/agent */ "./resources/js/apis/agent.js");
/* harmony import */ var _actions_modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/actions/modal */ "./resources/js/actions/modal.js");
/* harmony import */ var _AgentBar_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./AgentBar.vue */ "./resources/js/components/agent/chat/AgentBar.vue");
/* harmony import */ var _AgentInfo_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./AgentInfo.vue */ "./resources/js/components/agent/chat/AgentInfo.vue");
/* harmony import */ var _ChatPanel_vue__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./ChatPanel.vue */ "./resources/js/components/agent/chat/ChatPanel.vue");
/* harmony import */ var _ConversationHistory_vue__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ConversationHistory.vue */ "./resources/js/components/agent/chat/ConversationHistory.vue");
/* harmony import */ var _ReportPanel_vue__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./ReportPanel.vue */ "./resources/js/components/agent/chat/ReportPanel.vue");
/* harmony import */ var _items__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./items */ "./resources/js/components/agent/chat/items.js");
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




// Actions


// Components






var POLL_MS = 1500;
var STALLED_AFTER_S = 20;
var GIVE_UP_AFTER_S = 120;
var COLLAPSED_KEY = "hc-agent-history-collapsed";
var WIDE_SCREEN = 1100;

/**
 * Talking to the AI agents of a project: the agents on top, the history of the
 * conversations on the left, the conversation in the middle, and what the
 * agents produce (documents) on the right.
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    AgentBar: _AgentBar_vue__WEBPACK_IMPORTED_MODULE_3__["default"],
    AgentInfo: _AgentInfo_vue__WEBPACK_IMPORTED_MODULE_4__["default"],
    ChatPanel: _ChatPanel_vue__WEBPACK_IMPORTED_MODULE_5__["default"],
    ConversationHistory: _ConversationHistory_vue__WEBPACK_IMPORTED_MODULE_6__["default"],
    ReportPanel: _ReportPanel_vue__WEBPACK_IMPORTED_MODULE_7__["default"]
  },
  data: function data() {
    return {
      selectedAgentId: null,
      conversations: [],
      historyLoading: false,
      historyCollapsed: false,
      conversationId: null,
      conversationTitle: "",
      messages: [],
      reports: [],
      pendingActions: [],
      deciding: {},
      busy: false,
      stalled: false,
      error: "",
      // Changes with every conversation opened: late answers of the previous one are ignored
      token: 0,
      timer: null,
      infoOpen: false,
      infoAgent: null,
      infoLoading: false,
      infoError: "",
      canvasOpen: false,
      openedReport: null,
      reportLoading: false,
      reportError: ""
    };
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_9__.mapGetters)(["project", "agents"])), {}, {
    // The agents that work in this project: the ones for every project and the ones bound to it
    projectAgents: function projectAgents() {
      var _this = this;
      return this.agents.filter(function (agent) {
        return !agent.project || agent.project.id === _this.project.id;
      });
    },
    selectedAgent: function selectedAgent() {
      var _this2 = this;
      return this.projectAgents.find(function (agent) {
        return agent.id === _this2.selectedAgentId;
      }) || null;
    },
    items: function items() {
      return (0,_items__WEBPACK_IMPORTED_MODULE_8__.buildItems)(this.messages, this.reports);
    },
    openedReportId: function openedReportId() {
      return this.openedReport ? this.openedReport.id : null;
    }
  }),
  watch: {
    // Another project: everything on the page belongs to the previous one
    "project.id": function projectId() {
      this.start();
    },
    // The first agent created here is the one to talk to; one deleted no longer is
    projectAgents: function projectAgents(list) {
      var _this3 = this;
      if (!list.some(function (agent) {
        return agent.id === _this3.selectedAgentId;
      })) {
        this.selectedAgentId = list.length ? list[0].id : null;
      }
    }
  },
  created: function created() {
    try {
      this.historyCollapsed = localStorage.getItem(COLLAPSED_KEY) === "1";
    } catch (e) {
      // No storage: the history stays open
    }
    this.canvasOpen = window.innerWidth > WIDE_SCREEN;
    this.start();
  },
  beforeUnmount: function beforeUnmount() {
    this.stop();
  },
  methods: {
    message: function message(e) {
      var _e$response;
      return (e === null || e === void 0 || (_e$response = e.response) === null || _e$response === void 0 || (_e$response = _e$response.data) === null || _e$response === void 0 ? void 0 : _e$response.message) || this.$t("agent.error");
    },
    stop: function stop() {
      clearTimeout(this.timer);
      this.timer = null;
      this.token++;
    },
    schedule: function schedule(fn) {
      clearTimeout(this.timer);
      this.timer = setTimeout(fn, POLL_MS);
    },
    // Load
    start: function start() {
      var _this4 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this4.stop();
              _this4.resetConversation();
              _this4.selectedAgentId = null;
              _context.prev = 3;
              _context.next = 6;
              return _store__WEBPACK_IMPORTED_MODULE_0__["default"].dispatch("FETCH_AGENTS");
            case 6:
              _context.next = 11;
              break;
            case 8:
              _context.prev = 8;
              _context.t0 = _context["catch"](3);
              _this4.error = _this4.message(_context.t0);
            case 11:
              if (!_this4.selectedAgent && _this4.projectAgents.length) {
                _this4.selectedAgentId = _this4.projectAgents[0].id;
              }
              _context.next = 14;
              return _this4.loadHistory();
            case 14:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[3, 8]]);
      }))();
    },
    loadHistory: function loadHistory() {
      var _this5 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$AgentService$c, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this5.historyLoading = _this5.conversations.length === 0;
              _context2.prev = 1;
              _context2.next = 4;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].conversations(_this5.project.slug);
            case 4:
              _yield$AgentService$c = _context2.sent;
              data = _yield$AgentService$c.data;
              _this5.conversations = data;
              _context2.next = 12;
              break;
            case 9:
              _context2.prev = 9;
              _context2.t0 = _context2["catch"](1);
              _this5.error = _this5.message(_context2.t0);
            case 12:
              _context2.prev = 12;
              _this5.historyLoading = false;
              return _context2.finish(12);
            case 15:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1, 9, 12, 15]]);
      }))();
    },
    toggleHistory: function toggleHistory() {
      this.historyCollapsed = !this.historyCollapsed;
      try {
        localStorage.setItem(COLLAPSED_KEY, this.historyCollapsed ? "1" : "0");
      } catch (e) {
        // No storage: it only is not remembered
      }
    },
    // Conversations
    resetConversation: function resetConversation() {
      this.conversationId = null;
      this.conversationTitle = "";
      this.messages = [];
      this.reports = [];
      this.pendingActions = [];
      this.busy = false;
      this.stalled = false;
      this.error = "";
    },
    newConversation: function newConversation() {
      this.stop();
      this.resetConversation();
    },
    selectAgent: function selectAgent(id) {
      if (id === this.selectedAgentId) {
        return;
      }
      this.selectedAgentId = id;

      // A conversation belongs to one agent: choosing another one starts a new one
      this.newConversation();
    },
    openConversation: function openConversation(id) {
      var _this6 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var token, _yield$AgentService$c2, data;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              if (!(id === _this6.conversationId)) {
                _context3.next = 2;
                break;
              }
              return _context3.abrupt("return");
            case 2:
              _this6.stop();
              _this6.resetConversation();
              token = _this6.token;
              _context3.prev = 5;
              _context3.next = 8;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].conversation(id);
            case 8:
              _yield$AgentService$c2 = _context3.sent;
              data = _yield$AgentService$c2.data;
              if (!(token !== _this6.token)) {
                _context3.next = 12;
                break;
              }
              return _context3.abrupt("return");
            case 12:
              _this6.conversationId = data.id;
              _this6.apply(data);
              if (data.agent && _this6.projectAgents.some(function (agent) {
                return agent.id === data.agent.id;
              })) {
                _this6.selectedAgentId = data.agent.id;
              }

              // An answer still being written: carry on following it
              if (data.run && !data.run.finished) {
                _this6.busy = true;
                _this6.follow(data.run.id, token);
              }
              _context3.next = 21;
              break;
            case 18:
              _context3.prev = 18;
              _context3.t0 = _context3["catch"](5);
              if (token === _this6.token) {
                _this6.error = _this6.message(_context3.t0);
              }
            case 21:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[5, 18]]);
      }))();
    },
    removeConversation: function removeConversation(conversation) {
      var _this7 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              if (confirm(_this7.$t("agent.chat.confirm_delete_conversation", {
                title: conversation.title || ""
              }))) {
                _context4.next = 2;
                break;
              }
              return _context4.abrupt("return");
            case 2:
              _context4.prev = 2;
              _context4.next = 5;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].archiveConversation(conversation.id);
            case 5:
              if (conversation.id === _this7.conversationId) {
                _this7.newConversation();
              }
              _context4.next = 8;
              return _this7.loadHistory();
            case 8:
              _context4.next = 13;
              break;
            case 10:
              _context4.prev = 10;
              _context4.t0 = _context4["catch"](2);
              _this7.error = _this7.message(_context4.t0);
            case 13:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[2, 10]]);
      }))();
    },
    // What the server returns for a conversation
    apply: function apply(data) {
      this.conversationTitle = data.title || "";
      this.messages = data.messages;
      this.reports = data.reports || [];
      this.pendingActions = data.pending_actions || [];
    },
    // Sending
    send: function send(text) {
      var _this8 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
        var agent, token, _yield$AgentService$a, data;
        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              agent = _this8.selectedAgent;
              if (!(!agent || _this8.busy || !text.trim())) {
                _context5.next = 3;
                break;
              }
              return _context5.abrupt("return");
            case 3:
              _this8.error = "";
              _this8.busy = true;
              _this8.messages = [].concat(_toConsumableArray(_this8.messages), [{
                role: "user",
                kind: "text",
                text: text
              }]);
              token = _this8.token;
              _context5.prev = 7;
              _context5.next = 10;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].ask(agent.id, _this8.project.slug, text, _this8.conversationId);
            case 10:
              _yield$AgentService$a = _context5.sent;
              data = _yield$AgentService$a.data;
              if (!(token !== _this8.token)) {
                _context5.next = 14;
                break;
              }
              return _context5.abrupt("return");
            case 14:
              _this8.conversationId = data.conversation_id;
              _this8.follow(data.run_id, token);
              _context5.next = 21;
              break;
            case 18:
              _context5.prev = 18;
              _context5.t0 = _context5["catch"](7);
              if (token === _this8.token) {
                _this8.busy = false;
                _this8.error = _this8.message(_context5.t0);
              }
            case 21:
            case "end":
              return _context5.stop();
          }
        }, _callee5, null, [[7, 18]]);
      }))();
    },
    // Ask for the run until it is over, then show the whole conversation
    follow: function follow(runId, token) {
      var _this9 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6() {
        var _yield$AgentService$r, data;
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.prev = 0;
              _context6.next = 3;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].run(runId);
            case 3:
              _yield$AgentService$r = _context6.sent;
              data = _yield$AgentService$r.data;
              if (!(token !== _this9.token)) {
                _context6.next = 7;
                break;
              }
              return _context6.abrupt("return");
            case 7:
              if (data.finished) {
                _context6.next = 14;
                break;
              }
              // Nobody picked the job up: the worker of the "agents" queue is not running
              _this9.stalled = data.waiting_seconds > STALLED_AFTER_S;
              if (!(data.waiting_seconds > GIVE_UP_AFTER_S)) {
                _context6.next = 13;
                break;
              }
              _this9.busy = false;
              _this9.error = _this9.$t("agent.not_started");
              return _context6.abrupt("return");
            case 13:
              return _context6.abrupt("return", _this9.schedule(function () {
                return _this9.follow(runId, token);
              }));
            case 14:
              _this9.stalled = false;
              _this9.busy = false;
              if (data.error) {
                _this9.error = data.error;
              }
              _context6.next = 19;
              return _this9.refresh(token);
            case 19:
              _this9.loadHistory();
              _context6.next = 25;
              break;
            case 22:
              _context6.prev = 22;
              _context6.t0 = _context6["catch"](0);
              if (token === _this9.token) {
                _this9.busy = false;
                _this9.error = _this9.message(_context6.t0);
              }
            case 25:
            case "end":
              return _context6.stop();
          }
        }, _callee6, null, [[0, 22]]);
      }))();
    },
    refresh: function refresh() {
      var _arguments = arguments,
        _this10 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7() {
        var token, before, _yield$AgentService$c3, data, created;
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              token = _arguments.length > 0 && _arguments[0] !== undefined ? _arguments[0] : _this10.token;
              if (_this10.conversationId) {
                _context7.next = 3;
                break;
              }
              return _context7.abrupt("return");
            case 3:
              before = _this10.reports;
              _context7.next = 6;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].conversation(_this10.conversationId);
            case 6:
              _yield$AgentService$c3 = _context7.sent;
              data = _yield$AgentService$c3.data;
              if (!(token !== _this10.token)) {
                _context7.next = 10;
                break;
              }
              return _context7.abrupt("return");
            case 10:
              _this10.apply(data);

              // A document the agent has just written opens by itself
              created = (0,_items__WEBPACK_IMPORTED_MODULE_8__.newReports)(before, _this10.reports);
              if (created.length) {
                _this10.openReportById(created[created.length - 1].id);
              }
            case 13:
            case "end":
              return _context7.stop();
          }
        }, _callee7);
      }))();
    },
    // Documents
    openReportById: function openReportById(id) {
      var _this11 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee8() {
        var _yield$AgentService$r2, data;
        return _regeneratorRuntime().wrap(function _callee8$(_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              _this11.canvasOpen = true;
              if (!(_this11.openedReport && _this11.openedReport.id === id)) {
                _context8.next = 3;
                break;
              }
              return _context8.abrupt("return");
            case 3:
              _this11.openedReport = null;
              _this11.reportError = "";
              _this11.reportLoading = true;
              _context8.prev = 6;
              _context8.next = 9;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].report(id);
            case 9:
              _yield$AgentService$r2 = _context8.sent;
              data = _yield$AgentService$r2.data;
              _this11.openedReport = data;
              _context8.next = 17;
              break;
            case 14:
              _context8.prev = 14;
              _context8.t0 = _context8["catch"](6);
              _this11.reportError = _this11.message(_context8.t0);
            case 17:
              _context8.prev = 17;
              _this11.reportLoading = false;
              return _context8.finish(17);
            case 20:
            case "end":
              return _context8.stop();
          }
        }, _callee8, null, [[6, 14, 17, 20]]);
      }))();
    },
    closeCanvas: function closeCanvas() {
      this.canvasOpen = false;
      this.openedReport = null;
    },
    // Decisions on what an agent proposed
    approve: function approve(action) {
      var _this12 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee9() {
        return _regeneratorRuntime().wrap(function _callee9$(_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              _context9.next = 2;
              return _this12.decide(action, function () {
                return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].approve(action.id);
              });
            case 2:
            case "end":
              return _context9.stop();
          }
        }, _callee9);
      }))();
    },
    reject: function reject(action) {
      var _this13 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee10() {
        return _regeneratorRuntime().wrap(function _callee10$(_context10) {
          while (1) switch (_context10.prev = _context10.next) {
            case 0:
              _context10.next = 2;
              return _this13.decide(action, function () {
                return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].reject(action.id);
              });
            case 2:
            case "end":
              return _context10.stop();
          }
        }, _callee10);
      }))();
    },
    decide: function decide(action, call) {
      var _this14 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee11() {
        return _regeneratorRuntime().wrap(function _callee11$(_context11) {
          while (1) switch (_context11.prev = _context11.next) {
            case 0:
              _this14.error = "";
              _this14.deciding = _objectSpread(_objectSpread({}, _this14.deciding), {}, _defineProperty({}, action.id, true));
              _context11.prev = 2;
              _context11.next = 5;
              return call();
            case 5:
              _context11.next = 10;
              break;
            case 7:
              _context11.prev = 7;
              _context11.t0 = _context11["catch"](2);
              _this14.error = _this14.message(_context11.t0);
            case 10:
              _this14.deciding = _objectSpread(_objectSpread({}, _this14.deciding), {}, _defineProperty({}, action.id, false));
              _context11.next = 13;
              return _this14.refresh()["catch"](function () {});
            case 13:
            case "end":
              return _context11.stop();
          }
        }, _callee11, null, [[2, 7]]);
      }))();
    },
    // Agents
    showInfo: function showInfo(id) {
      var _this15 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee12() {
        var _yield$AgentService$g, data;
        return _regeneratorRuntime().wrap(function _callee12$(_context12) {
          while (1) switch (_context12.prev = _context12.next) {
            case 0:
              _this15.infoOpen = true;
              _this15.infoAgent = null;
              _this15.infoError = "";
              _this15.infoLoading = true;
              _context12.prev = 4;
              _context12.next = 7;
              return _apis_agent__WEBPACK_IMPORTED_MODULE_1__["default"].get(id);
            case 7:
              _yield$AgentService$g = _context12.sent;
              data = _yield$AgentService$g.data;
              // Another agent may have been asked for in the meantime
              if (_this15.infoOpen) {
                _this15.infoAgent = data;
              }
              _context12.next = 15;
              break;
            case 12:
              _context12.prev = 12;
              _context12.t0 = _context12["catch"](4);
              _this15.infoError = _this15.message(_context12.t0);
            case 15:
              _context12.prev = 15;
              _this15.infoLoading = false;
              return _context12.finish(15);
            case 18:
            case "end":
              return _context12.stop();
          }
        }, _callee12, null, [[4, 12, 15, 18]]);
      }))();
    },
    // See: @/components/agent/FormModal.vue
    editAgent: function editAgent(agent) {
      this.infoOpen = false;
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit("SET_AGENT_EDITING", agent);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_2__.OPEN_MODAL, "agent-form");
    },
    createAgent: function createAgent() {
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit("SET_AGENT_EDITING", null);
      _store__WEBPACK_IMPORTED_MODULE_0__["default"].commit(_actions_modal__WEBPACK_IMPORTED_MODULE_2__.OPEN_MODAL, "agent-form");
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _components_agent_ActionCard_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/agent/ActionCard.vue */ "./resources/js/components/agent/ActionCard.vue");
/* harmony import */ var _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../ui/AgentButton.vue */ "./resources/js/components/agent/ui/AgentButton.vue");
/* harmony import */ var _Markdown_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Markdown.vue */ "./resources/js/components/agent/chat/Markdown.vue");



var SUGGESTIONS = ["activity", "uncontacted", "appointments"];
var SUGGESTION_ICONS = {
  activity: "fa fa-chart-line",
  uncontacted: "fa fa-user-clock",
  appointments: "fa fa-calendar-check"
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    ActionCard: _components_agent_ActionCard_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    AgButton: _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_1__["default"],
    Markdown: _Markdown_vue__WEBPACK_IMPORTED_MODULE_2__["default"]
  },
  props: {
    agent: {
      type: Object,
      "default": null
    },
    title: {
      type: String,
      "default": ""
    },
    items: {
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    busy: {
      type: Boolean,
      "default": false
    },
    stalled: {
      type: Boolean,
      "default": false
    },
    error: {
      type: String,
      "default": ""
    },
    pendingActions: {
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    deciding: {
      type: Object,
      "default": function _default() {
        return {};
      }
    },
    openReportId: {
      type: Number,
      "default": null
    }
  },
  emits: ["send", "create", "approve", "reject", "open-report"],
  data: function data() {
    return {
      draft: "",
      suggestions: SUGGESTIONS,
      suggestionIcons: SUGGESTION_ICONS
    };
  },
  computed: {
    canSend: function canSend() {
      return !!this.agent && !this.busy && !!this.draft.trim();
    }
  },
  watch: {
    // Stay at the bottom while the conversation grows
    items: function items() {
      this.$nextTick(this.scrollDown);
    },
    busy: function busy() {
      this.$nextTick(this.scrollDown);
    }
  },
  methods: {
    send: function send(text) {
      var message = (text || "").trim();
      if (!this.agent || this.busy || !message) {
        return;
      }
      this.$emit("send", message);
      this.draft = "";
      this.$nextTick(this.grow);
    },
    // The box grows with what is typed
    grow: function grow() {
      var box = this.$refs.input;
      if (box) {
        box.style.height = "auto";
        box.style.height = Math.min(box.scrollHeight, 200) + "px";
      }
    },
    scrollDown: function scrollDown() {
      var box = this.$refs.scroller;
      if (box) {
        box.scrollTop = box.scrollHeight;
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../ui/AgentButton.vue */ "./resources/js/components/agent/ui/AgentButton.vue");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    AgButton: _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  props: {
    conversations: {
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    activeId: {
      type: Number,
      "default": null
    },
    collapsed: {
      type: Boolean,
      "default": false
    },
    loading: {
      type: Boolean,
      "default": false
    }
  },
  emits: ["open", "new", "remove", "toggle"]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _utils_markdown__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/utils/markdown */ "./resources/js/utils/markdown.js");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    text: {
      type: String,
      "default": ""
    }
  },
  computed: {
    html: function html() {
      return (0,_utils_markdown__WEBPACK_IMPORTED_MODULE_0__.renderMarkdown)(this.text);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../ui/AgentButton.vue */ "./resources/js/components/agent/ui/AgentButton.vue");
/* harmony import */ var _Markdown_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Markdown.vue */ "./resources/js/components/agent/chat/Markdown.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    AgButton: _ui_AgentButton_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    Markdown: _Markdown_vue__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  props: {
    report: {
      type: Object,
      "default": null
    },
    loading: {
      type: Boolean,
      "default": false
    },
    error: {
      type: String,
      "default": ""
    }
  },
  emits: ["close"],
  data: function data() {
    return {
      copied: false
    };
  },
  methods: {
    date: function date(value) {
      return new Date(value).toLocaleString();
    },
    copy: function copy() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              _context.next = 3;
              return navigator.clipboard.writeText(_this.report.content);
            case 3:
              _this.copied = true;
              setTimeout(function () {
                return _this.copied = false;
              }, 1500);
              _context.next = 9;
              break;
            case 7:
              _context.prev = 7;
              _context.t0 = _context["catch"](0);
            case 9:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[0, 7]]);
      }))();
    },
    // The document as a Markdown file
    download: function download() {
      var blob = new Blob([this.report.content], {
        type: "text/markdown;charset=utf-8"
      });
      var link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = (this.report.title || "document").replace(/[^\w\-. ]+/g, "").trim().slice(0, 80) + ".md";
      document.body.appendChild(link);
      link.click();
      link.remove();
      setTimeout(function () {
        return URL.revokeObjectURL(link.href);
      }, 1000);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * The button of the AI agents screens. One look for every action:
 *   primary    the main action          success   authorise
 *   secondary  a quieter action         danger    delete / cannot be undone
 *   ghost      no frame                 ghost-danger  a quiet destructive link
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    variant: {
      type: String,
      "default": "secondary",
      validator: function validator(value) {
        return ["primary", "secondary", "ghost", "danger", "success", "ghost-danger"].includes(value);
      }
    },
    size: {
      type: String,
      "default": "md",
      validator: function validator(value) {
        return ["sm", "md"].includes(value);
      }
    },
    icon: {
      type: String,
      "default": ""
    },
    // A round button with an icon only: the label then goes in aria-label
    iconOnly: {
      type: Boolean,
      "default": false
    },
    loading: {
      type: Boolean,
      "default": false
    },
    disabled: {
      type: Boolean,
      "default": false
    },
    block: {
      type: Boolean,
      "default": false
    },
    type: {
      type: String,
      "default": "button"
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b":
/*!**************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["aria-label"];
var _hoisted_2 = {
  "class": "hc-ag-perm-head"
};
var _hoisted_3 = {
  "class": "hc-ag-perm-icon",
  "aria-hidden": "true"
};
var _hoisted_4 = {
  "class": "hc-ag-perm-heading"
};
var _hoisted_5 = {
  "class": "hc-ag-perm-eyebrow"
};
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["textContent"];
var _hoisted_8 = {
  key: 0,
  "class": "hc-ag-perm-body"
};
var _hoisted_9 = {
  key: 0,
  "class": "hc-ag-perm-stat"
};
var _hoisted_10 = {
  key: 1,
  "class": "hc-ag-perm-details"
};
var _hoisted_11 = ["textContent"];
var _hoisted_12 = ["textContent"];
var _hoisted_13 = ["aria-label"];
var _hoisted_14 = ["textContent"];
var _hoisted_15 = {
  key: 3,
  "class": "hc-ag-perm-note"
};
var _hoisted_16 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-circle-info",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_17 = {
  key: 1,
  "class": "hc-ag-perm-state is-running",
  role: "status"
};
var _hoisted_18 = {
  "class": "hc-ag-perm-progress"
};
var _hoisted_19 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-ag-perm-spinner",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_20 = {
  key: 2,
  "class": "hc-ag-perm-state is-ok",
  role: "status"
};
var _hoisted_21 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-circle-check",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_22 = {
  key: 3,
  "class": "hc-ag-perm-state is-ko",
  role: "alert"
};
var _hoisted_23 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-circle-exclamation",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_24 = {
  key: 4,
  "class": "hc-ag-perm-state is-muted"
};
var _hoisted_25 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-ban",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_26 = {
  key: 5,
  "class": "hc-ag-perm-state is-muted"
};
var _hoisted_27 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-clock",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_28 = {
  key: 6,
  "class": "hc-ag-perm-foot"
};
var _hoisted_29 = {
  key: 0,
  "class": "hc-ag-perm-expiry"
};
var _hoisted_30 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-clock",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_31 = {
  "class": "hc-ag-perm-actions"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _$props$action$result, _$props$action$result2;
  var _component_ag_button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("ag-button");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("article", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-ag-perm", ["is-".concat($props.action.status), "risk-".concat($props.action.risk)]]),
    role: "group",
    "aria-label": $options.title
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)($options.icon)
  }, null, 2 /* CLASS */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.eyebrow), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.title)
  }, null, 8 /* PROPS */, _hoisted_6)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "hc-ag-perm-risk",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.risk.".concat($props.action.risk)))
  }, null, 8 /* PROPS */, _hoisted_7)]), $options.count !== null || $options.details.length || $options.sample.length || $options.skipped ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_8, [$options.count !== null ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_9, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.count), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t($options.count === 1 ? "agent.perm.concerned_one" : "agent.perm.concerned_many")), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.details.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("dl", _hoisted_10, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.details, function (detail) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: detail.key
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.preview.".concat(detail.key), detail.key))
    }, null, 8 /* PROPS */, _hoisted_11), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", {
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(detail.value)
    }, null, 8 /* PROPS */, _hoisted_12)], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.sample.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("ul", {
    key: 2,
    "class": "hc-ag-perm-sample",
    "aria-label": _ctx.$t('agent.preview.sample')
  }, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.sample, function (name, i) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: i,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(name)
    }, null, 8 /* PROPS */, _hoisted_14);
  }), 128 /* KEYED_FRAGMENT */))], 8 /* PROPS */, _hoisted_13)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.skipped ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_15, [_hoisted_16, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.skipped_processed", {
    n: $options.skipped
  })), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" While it runs "), $props.action.running ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_17, [$props.action.progress ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_18, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
      width: $options.percent + '%'
    })
  }, null, 4 /* STYLE */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.action.progress.done) + " / " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.action.progress.total), 1 /* TEXT */)], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [_hoisted_19, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.running")), 1 /* TEXT */)], 64 /* STABLE_FRAGMENT */))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" The outcome "), $props.action.status === 'executed' && $props.action.result ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_20, [_hoisted_21, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.done", {
    updated: (_$props$action$result = $props.action.result.updated) !== null && _$props$action$result !== void 0 ? _$props$action$result : 1,
    failed: (_$props$action$result2 = $props.action.result.failed) !== null && _$props$action$result2 !== void 0 ? _$props$action$result2 : 0
  })), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.action.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_22, [_hoisted_23, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.action.error), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.action.status === 'rejected' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_24, [_hoisted_25, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.perm.rejected_note")), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.action.status === 'expired' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_26, [_hoisted_27, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.perm.expired_note")), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" The decision "), $props.action.status === 'pending' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("footer", _hoisted_28, [$props.action.expires_at ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_29, [_hoisted_30, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.expires", {
    date: $options.expiry
  })), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_31, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "ghost",
    disabled: $props.working,
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('reject');
    })
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.perm.reject")), 1 /* TEXT */)];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["disabled"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: $options.destructive ? 'danger' : 'success',
    icon: $options.destructive ? 'fa fa-trash' : 'fa fa-check',
    loading: $props.working,
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('approve');
    })
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t($options.destructive ? "agent.perm.approve_destructive" : "agent.perm.approve")), 1 /* TEXT */)];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["variant", "icon", "loading"])])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 10 /* CLASS, PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["aria-label"];
var _hoisted_2 = {
  "class": "hc-agent-bar-scroll"
};
var _hoisted_3 = ["aria-pressed", "title", "onClick"];
var _hoisted_4 = ["src"];
var _hoisted_5 = {
  key: 1,
  "class": "fa fa-robot",
  "aria-hidden": "true"
};
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["title", "aria-label", "onClick"];
var _hoisted_8 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-info-circle",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_9 = [_hoisted_8];
var _hoisted_10 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-plus",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("nav", {
    "class": "hc-agent-bar",
    "aria-label": _ctx.$t('agent.chat.agents_of_project')
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.agents, function (agent) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: agent.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-agent-chip", {
        'is-selected': agent.id === $props.selectedId
      }])
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
      type: "button",
      "class": "hc-agent-chip-main",
      "aria-pressed": agent.id === $props.selectedId,
      title: agent.specialty || agent.name,
      onClick: function onClick($event) {
        return _ctx.$emit('select', agent.id);
      }
    }, [agent.avatar_url ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
      key: 0,
      src: agent.avatar_url,
      alt: '',
      width: "28",
      height: "28"
    }, null, 8 /* PROPS */, _hoisted_4)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", _hoisted_5)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-agent-chip-name",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(agent.name)
    }, null, 8 /* PROPS */, _hoisted_6)], 8 /* PROPS */, _hoisted_3), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
      type: "button",
      "class": "hc-agent-chip-info",
      title: _ctx.$t('agent.chat.info'),
      "aria-label": _ctx.$t('agent.chat.info_of', {
        name: agent.name
      }),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return _ctx.$emit('info', agent.id);
      }, ["stop"])
    }, _hoisted_9, 8 /* PROPS */, _hoisted_7)], 2 /* CLASS */);
  }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": "hc-agent-chip-add",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('create');
    })
  }, [_hoisted_10, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.new")), 1 /* TEXT */)])])], 8 /* PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e":
/*!******************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  key: 0,
  "class": "hc-agent-info-layer"
};
var _hoisted_2 = ["aria-label"];
var _hoisted_3 = {
  key: 0,
  "class": "hc-agent-info-hint"
};
var _hoisted_4 = {
  key: 1,
  "class": "hc-agent-info-error",
  role: "alert"
};
var _hoisted_5 = {
  key: 2,
  "class": "hc-agent-info-body"
};
var _hoisted_6 = {
  "class": "hc-agent-info-identity"
};
var _hoisted_7 = ["src", "alt"];
var _hoisted_8 = {
  key: 1,
  "class": "fa fa-robot",
  "aria-hidden": "true"
};
var _hoisted_9 = ["textContent"];
var _hoisted_10 = ["textContent"];
var _hoisted_11 = {
  key: 0
};
var _hoisted_12 = ["textContent"];
var _hoisted_13 = {
  key: 1
};
var _hoisted_14 = ["textContent"];
var _hoisted_15 = {
  key: 2
};
var _hoisted_16 = ["textContent"];
var _hoisted_17 = {
  key: 3
};
var _hoisted_18 = ["textContent"];
var _hoisted_19 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ag_button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("ag-button");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Transition, {
    name: "hc-agent-info"
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [$props.open ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
        "class": "hc-agent-info-backdrop",
        onClick: _cache[0] || (_cache[0] = function ($event) {
          return _ctx.$emit('close');
        })
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("aside", {
        "class": "hc-agent-info",
        role: "dialog",
        "aria-modal": "true",
        "aria-label": _ctx.$t('agent.chat.info_of', {
          name: $props.agent ? $props.agent.name : ''
        }),
        onKeydown: _cache[3] || (_cache[3] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)(function ($event) {
          return _ctx.$emit('close');
        }, ["esc"]))
      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.info")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
        variant: "ghost",
        size: "sm",
        icon: "fa fa-times",
        "icon-only": "",
        "aria-label": _ctx.$t('agent.chat.close'),
        onClick: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.$emit('close');
        })
      }, null, 8 /* PROPS */, ["aria-label"])]), $props.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_3, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.loading")), 1 /* TEXT */)) : $props.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_4, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.error), 1 /* TEXT */)) : $props.agent ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_5, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_6, [$props.agent.avatar_url ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
        key: 0,
        src: $props.agent.avatar_url,
        alt: $props.agent.name
      }, null, 8 /* PROPS */, _hoisted_7)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", _hoisted_8)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.name)
      }, null, 8 /* PROPS */, _hoisted_9), $props.agent.specialty ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
        key: 2,
        "class": "hc-agent-info-specialty",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.specialty)
      }, null, 8 /* PROPS */, _hoisted_10)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-agent-info-badge", "is-".concat($props.agent.autonomy)])
      }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.autonomy_short.".concat($props.agent.autonomy))), 3 /* TEXT, CLASS */)]), $props.agent.description ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", _hoisted_11, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.field_description")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.description)
      }, null, 8 /* PROPS */, _hoisted_12)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.agent.persona ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", _hoisted_13, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.persona")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.persona)
      }, null, 8 /* PROPS */, _hoisted_14)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.agent.objectives ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", _hoisted_15, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.objectives")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.objectives)
      }, null, 8 /* PROPS */, _hoisted_16)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.agent.instructions ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", _hoisted_17, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.instructions")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
        "class": "hc-agent-info-instructions",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.instructions)
      }, null, 8 /* PROPS */, _hoisted_18)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.can_do")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.knows_all")), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h4", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.scope")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.project ? $props.agent.project.name : _ctx.$t('agent.all_projects'))
      }, null, 8 /* PROPS */, _hoisted_19)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
        variant: "secondary",
        icon: "fa fa-pen",
        block: "",
        onClick: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.$emit('edit', $props.agent);
        })
      }, {
        "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
          return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.edit")), 1 /* TEXT */)];
        }),
        _: 1 /* STABLE */
      })])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_2)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)];
    }),
    _: 1 /* STABLE */
  });
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c":
/*!******************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  id: "hc-agent-page"
};
var _hoisted_2 = {
  "class": "hc-agent-page-body"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_agent_bar = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("agent-bar");
  var _component_conversation_history = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("conversation-history");
  var _component_chat_panel = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("chat-panel");
  var _component_report_panel = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("report-panel");
  var _component_agent_info = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("agent-info");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_agent_bar, {
    agents: $options.projectAgents,
    "selected-id": $data.selectedAgentId,
    onSelect: $options.selectAgent,
    onInfo: $options.showInfo,
    onCreate: $options.createAgent
  }, null, 8 /* PROPS */, ["agents", "selected-id", "onSelect", "onInfo", "onCreate"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_conversation_history, {
    conversations: $data.conversations,
    "active-id": $data.conversationId,
    collapsed: $data.historyCollapsed,
    loading: $data.historyLoading,
    onToggle: $options.toggleHistory,
    onNew: $options.newConversation,
    onOpen: $options.openConversation,
    onRemove: $options.removeConversation
  }, null, 8 /* PROPS */, ["conversations", "active-id", "collapsed", "loading", "onToggle", "onNew", "onOpen", "onRemove"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_chat_panel, {
    agent: $options.selectedAgent,
    title: $data.conversationTitle,
    items: $options.items,
    busy: $data.busy,
    stalled: $data.stalled,
    error: $data.error,
    "pending-actions": $data.pendingActions,
    deciding: $data.deciding,
    "open-report-id": $options.openedReportId,
    onSend: $options.send,
    onCreate: $options.createAgent,
    onApprove: $options.approve,
    onReject: $options.reject,
    onOpenReport: $options.openReportById
  }, null, 8 /* PROPS */, ["agent", "title", "items", "busy", "stalled", "error", "pending-actions", "deciding", "open-report-id", "onSend", "onCreate", "onApprove", "onReject", "onOpenReport"]), $data.canvasOpen ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_report_panel, {
    key: 0,
    report: $data.openedReport,
    loading: $data.reportLoading,
    error: $data.reportError,
    onClose: $options.closeCanvas
  }, null, 8 /* PROPS */, ["report", "loading", "error", "onClose"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_agent_info, {
    open: $data.infoOpen,
    agent: $data.infoAgent,
    loading: $data.infoLoading,
    error: $data.infoError,
    onClose: _cache[0] || (_cache[0] = function ($event) {
      return $data.infoOpen = false;
    }),
    onEdit: $options.editAgent
  }, null, 8 /* PROPS */, ["open", "agent", "loading", "error", "onEdit"])])]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec":
/*!******************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["aria-label"];
var _hoisted_2 = {
  "class": "hc-agent-chat-header"
};
var _hoisted_3 = ["src"];
var _hoisted_4 = {
  key: 1,
  "class": "fa fa-robot",
  "aria-hidden": "true"
};
var _hoisted_5 = {
  "class": "hc-agent-chat-heading"
};
var _hoisted_6 = ["textContent"];
var _hoisted_7 = ["textContent"];
var _hoisted_8 = {
  key: 1
};
var _hoisted_9 = {
  ref: "scroller",
  "class": "hc-agent-chat-messages",
  role: "log",
  "aria-live": "polite"
};
var _hoisted_10 = {
  key: 0,
  "class": "hc-agent-chat-welcome"
};
var _hoisted_11 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-robot",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_12 = {
  key: 1,
  "class": "hc-agent-chat-welcome"
};
var _hoisted_13 = ["src", "alt"];
var _hoisted_14 = {
  key: 1,
  "class": "fa fa-robot",
  "aria-hidden": "true"
};
var _hoisted_15 = ["textContent"];
var _hoisted_16 = ["textContent"];
var _hoisted_17 = {
  "class": "hc-agent-chat-suggestions"
};
var _hoisted_18 = ["onClick"];
var _hoisted_19 = ["textContent"];
var _hoisted_20 = {
  key: 1,
  "class": "hc-agent-msg is-agent"
};
var _hoisted_21 = {
  key: 2,
  "class": "hc-agent-msg-tool"
};
var _hoisted_22 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-wrench",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_23 = ["textContent"];
var _hoisted_24 = ["onClick"];
var _hoisted_25 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-agent-report-card-icon",
  "aria-hidden": "true"
}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-file-lines"
})], -1 /* HOISTED */);
var _hoisted_26 = {
  "class": "hc-agent-report-card-text"
};
var _hoisted_27 = ["textContent"];
var _hoisted_28 = {
  "class": "hc-agent-report-card-kind"
};
var _hoisted_29 = {
  "class": "hc-agent-report-card-open"
};
var _hoisted_30 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-arrow-right",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_31 = {
  key: 2,
  "class": "hc-agent-msg is-agent is-working"
};
var _hoisted_32 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
  "class": "hc-agent-dots",
  "aria-hidden": "true"
}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i")], -1 /* HOISTED */);
var _hoisted_33 = {
  key: 0,
  "class": "hc-agent-chat-error",
  role: "alert"
};
var _hoisted_34 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-circle-exclamation",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_35 = ["disabled", "placeholder"];
var _hoisted_36 = {
  "class": "hc-agent-chat-hint"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ag_button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("ag-button");
  var _component_markdown = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("markdown");
  var _component_action_card = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("action-card");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", {
    "class": "hc-agent-chat",
    "aria-label": _ctx.$t('agent.chat.conversation')
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", _hoisted_2, [$props.agent ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [$props.agent.avatar_url ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
    key: 0,
    src: $props.agent.avatar_url,
    alt: "",
    width: "32",
    height: "32"
  }, null, 8 /* PROPS */, _hoisted_3)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", _hoisted_4)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_5, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.name)
  }, null, 8 /* PROPS */, _hoisted_6), $props.title ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 0,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.title)
  }, null, 8 /* PROPS */, _hoisted_7)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("strong", _hoisted_8, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.title")), 1 /* TEXT */))]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_9, [!$props.agent ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_10, [_hoisted_11, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.no_agent_page")), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "primary",
    icon: "fa fa-plus",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('create');
    })
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.new")), 1 /* TEXT */)];
    }),
    _: 1 /* STABLE */
  })])) : !$props.items.length && !$props.busy ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_12, [$props.agent.avatar_url ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
    key: 0,
    src: $props.agent.avatar_url,
    alt: $props.agent.name
  }, null, 8 /* PROPS */, _hoisted_13)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", _hoisted_14)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.name)
  }, null, 8 /* PROPS */, _hoisted_15), $props.agent.specialty ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", {
    key: 2,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.agent.specialty)
  }, null, 8 /* PROPS */, _hoisted_16)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_17, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.suggestions, function (key) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
      key: key,
      type: "button",
      onClick: function onClick($event) {
        return $options.send(_ctx.$t("agent.chat.suggestion.".concat(key)));
      }
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)($data.suggestionIcons[key]),
      "aria-hidden": "true"
    }, null, 2 /* CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.suggestion.".concat(key))), 1 /* TEXT */)], 8 /* PROPS */, _hoisted_18);
  }), 128 /* KEYED_FRAGMENT */))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.items, function (item, i) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: i
    }, [item.kind === 'user' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: 0,
      "class": "hc-agent-msg is-user",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(item.text)
    }, null, 8 /* PROPS */, _hoisted_19)) : item.kind === 'agent' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_20, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_markdown, {
      text: item.text
    }, null, 8 /* PROPS */, ["text"])])) : item.kind === 'tool' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_21, [_hoisted_22, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.tool_used", {
      tool: _ctx.$t("agent.tools.".concat(item.tool), item.tool)
    })), 1 /* TEXT */)])) : item.kind === 'system' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: 3,
      "class": "hc-agent-msg-system",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(item.text)
    }, null, 8 /* PROPS */, _hoisted_23)) : item.kind === 'report' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
      key: 4,
      type: "button",
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-agent-report-card", {
        'is-open': item.id === $props.openReportId
      }]),
      onClick: function onClick($event) {
        return _ctx.$emit('open-report', item.id);
      }
    }, [_hoisted_25, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_26, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-agent-report-card-title",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(item.title)
    }, null, 8 /* PROPS */, _hoisted_27), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_28, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.document")), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_29, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.open_document")) + " ", 1 /* TEXT */), _hoisted_30])], 10 /* CLASS, PROPS */, _hoisted_24)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */);
  }), 128 /* KEYED_FRAGMENT */)), $props.busy ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_31, [_hoisted_32, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t($props.stalled ? "agent.waiting_worker" : "agent.working")), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.pendingActions, function (action) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: action.id,
      "class": "hc-agent-chat-action"
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_action_card, {
      action: action,
      working: $props.deciding[action.id],
      onApprove: function onApprove($event) {
        return _ctx.$emit('approve', action);
      },
      onReject: function onReject($event) {
        return _ctx.$emit('reject', action);
      }
    }, null, 8 /* PROPS */, ["action", "working", "onApprove", "onReject"])]);
  }), 128 /* KEYED_FRAGMENT */))], 512 /* NEED_PATCH */), $props.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_33, [_hoisted_34, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.error), 1 /* TEXT */)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("form", {
    "class": "hc-agent-chat-form",
    onSubmit: _cache[4] || (_cache[4] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
      return $options.send($data.draft);
    }, ["prevent"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("textarea", {
    ref: "input",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return $data.draft = $event;
    }),
    rows: "1",
    maxlength: "8000",
    disabled: !$props.agent || $props.busy,
    placeholder: $props.agent ? _ctx.$t('agent.chat.placeholder', {
      name: $props.agent.name
    }) : '',
    onInput: _cache[2] || (_cache[2] = function () {
      return $options.grow && $options.grow.apply($options, arguments);
    }),
    onKeydown: _cache[3] || (_cache[3] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withKeys)((0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
      return $options.send($data.draft);
    }, ["exact", "prevent"]), ["enter"]))
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_35), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.draft]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    type: "submit",
    variant: "primary",
    icon: "fa fa-arrow-up",
    "icon-only": "",
    disabled: !$options.canSend,
    "aria-label": _ctx.$t('agent.send'),
    title: _ctx.$t('agent.send')
  }, null, 8 /* PROPS */, ["disabled", "aria-label", "title"])], 32 /* HYDRATE_EVENTS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", _hoisted_36, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.hint")), 1 /* TEXT */)], 8 /* PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["aria-label"];
var _hoisted_2 = {
  "class": "hc-agent-history-top"
};
var _hoisted_3 = {
  key: 0,
  "class": "hc-agent-history-hint"
};
var _hoisted_4 = {
  key: 1,
  "class": "hc-agent-history-hint"
};
var _hoisted_5 = {
  "class": "hc-agent-history-list"
};
var _hoisted_6 = ["title", "onClick"];
var _hoisted_7 = ["src"];
var _hoisted_8 = {
  key: 1,
  "class": "fa fa-robot",
  "aria-hidden": "true"
};
var _hoisted_9 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ag_button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("ag-button");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("aside", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-agent-history", {
      'is-collapsed': $props.collapsed
    }]),
    "aria-label": _ctx.$t('agent.chat.history')
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "ghost",
    size: "sm",
    icon: $props.collapsed ? 'fa fa-angle-double-right' : 'fa fa-angle-double-left',
    "icon-only": "",
    "class": "hc-agent-history-toggle",
    title: _ctx.$t($props.collapsed ? 'agent.chat.expand' : 'agent.chat.collapse'),
    "aria-label": _ctx.$t($props.collapsed ? 'agent.chat.expand' : 'agent.chat.collapse'),
    "aria-expanded": !$props.collapsed,
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('toggle');
    })
  }, null, 8 /* PROPS */, ["icon", "title", "aria-label", "aria-expanded"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "secondary",
    icon: "fa fa-plus",
    "icon-only": $props.collapsed,
    block: !$props.collapsed,
    title: _ctx.$t('agent.chat.new_conversation'),
    "aria-label": _ctx.$t('agent.chat.new_conversation'),
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('new');
    })
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.new_conversation")), 1 /* TEXT */)];
    }),
    _: 1 /* STABLE */
  }, 8 /* PROPS */, ["icon-only", "block", "title", "aria-label"])]), !$props.collapsed ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [$props.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_3, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.loading")), 1 /* TEXT */)) : !$props.conversations.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_4, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.no_history")), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("ul", _hoisted_5, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($props.conversations, function (conversation) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: conversation.id,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)({
        'is-active': conversation.id === $props.activeId
      })
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
      type: "button",
      "class": "hc-agent-history-item",
      title: conversation.title,
      onClick: function onClick($event) {
        return _ctx.$emit('open', conversation.id);
      }
    }, [conversation.agent && conversation.agent.avatar_url ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
      key: 0,
      src: conversation.agent.avatar_url,
      alt: "",
      width: "20",
      height: "20"
    }, null, 8 /* PROPS */, _hoisted_7)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", _hoisted_8)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": "hc-agent-history-title",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(conversation.title || _ctx.$t('agent.chat.untitled'))
    }, null, 8 /* PROPS */, _hoisted_9)], 8 /* PROPS */, _hoisted_6), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
      variant: "ghost",
      size: "sm",
      icon: "fa fa-trash",
      "icon-only": "",
      "class": "hc-agent-history-remove",
      title: _ctx.$t('agent.chat.delete_conversation'),
      "aria-label": _ctx.$t('agent.chat.delete_conversation'),
      onClick: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function ($event) {
        return _ctx.$emit('remove', conversation);
      }, ["stop"])
    }, null, 8 /* PROPS */, ["title", "aria-label", "onClick"])], 2 /* CLASS */);
  }), 128 /* KEYED_FRAGMENT */))])], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 10 /* CLASS, PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Safe by construction: the text is escaped before anything else (utils/markdown.js) "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-md",
    innerHTML: $options.html
  }, null, 8 /* PROPS */, _hoisted_1)], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e":
/*!********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["aria-label"];
var _hoisted_2 = {
  "class": "hc-agent-canvas-title"
};
var _hoisted_3 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-file-lines",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
var _hoisted_4 = ["textContent"];
var _hoisted_5 = {
  "class": "hc-agent-canvas-actions"
};
var _hoisted_6 = {
  key: 0,
  "class": "hc-agent-canvas-empty"
};
var _hoisted_7 = {
  key: 1,
  "class": "hc-agent-canvas-empty is-error",
  role: "alert"
};
var _hoisted_8 = {
  key: 2,
  "class": "hc-agent-canvas-body"
};
var _hoisted_9 = {
  "class": "hc-agent-canvas-meta"
};
var _hoisted_10 = ["textContent"];
var _hoisted_11 = ["textContent"];
var _hoisted_12 = {
  key: 3,
  "class": "hc-agent-canvas-empty"
};
var _hoisted_13 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-file-lines",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ag_button = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("ag-button");
  var _component_markdown = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("markdown");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("aside", {
    "class": "hc-agent-canvas",
    "aria-label": _ctx.$t('agent.chat.renders')
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("header", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [_hoisted_3, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.report ? $props.report.title : _ctx.$t('agent.chat.renders'))
  }, null, 8 /* PROPS */, _hoisted_4)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_5, [$props.report ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "ghost",
    size: "sm",
    icon: $data.copied ? 'fa fa-check' : 'fa fa-copy',
    "icon-only": "",
    title: _ctx.$t('agent.chat.copy'),
    "aria-label": _ctx.$t('agent.chat.copy'),
    onClick: $options.copy
  }, null, 8 /* PROPS */, ["icon", "title", "aria-label", "onClick"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "ghost",
    size: "sm",
    icon: "fa fa-download",
    "icon-only": "",
    title: _ctx.$t('agent.chat.download'),
    "aria-label": _ctx.$t('agent.chat.download'),
    onClick: $options.download
  }, null, 8 /* PROPS */, ["title", "aria-label", "onClick"])], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ag_button, {
    variant: "ghost",
    size: "sm",
    icon: "fa fa-times",
    "icon-only": "",
    title: _ctx.$t('agent.chat.close'),
    "aria-label": _ctx.$t('agent.chat.close'),
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('close');
    })
  }, null, 8 /* PROPS */, ["title", "aria-label"])])]), $props.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_6, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.loading")), 1 /* TEXT */)) : $props.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_7, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.error), 1 /* TEXT */)) : $props.report ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_8, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", _hoisted_9, [$props.report.agent ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 0,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.report.agent)
  }, null, 8 /* PROPS */, _hoisted_10)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $props.report.created_at ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", {
    key: 1,
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.date($props.report.created_at))
  }, null, 8 /* PROPS */, _hoisted_11)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_markdown, {
    text: $props.report.content
  }, null, 8 /* PROPS */, ["text"])])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_12, [_hoisted_13, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t("agent.chat.renders_empty")), 1 /* TEXT */)]))], 8 /* PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde":
/*!******************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = ["type", "disabled", "aria-busy"];
var _hoisted_2 = {
  key: 0,
  "class": "hc-ag-btn-spinner",
  "aria-hidden": "true"
};
var _hoisted_3 = {
  key: 2,
  "class": "hc-ag-btn-label"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-ag-btn", ["is-".concat($props.variant), "is-".concat($props.size), {
      'is-block': $props.block,
      'is-loading': $props.loading,
      'is-icon': $props.iconOnly
    }]]),
    type: $props.type,
    disabled: $props.disabled || $props.loading,
    "aria-busy": $props.loading ? 'true' : null
  }, [$props.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_2)) : $props.icon ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("i", {
    key: 1,
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)($props.icon),
    "aria-hidden": "true"
  }, null, 2 /* CLASS */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), !$props.iconOnly ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.renderSlot)(_ctx.$slots, "default")])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 10 /* CLASS, PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./resources/js/components/agent/chat/items.js":
/*!*****************************************************!*\
  !*** ./resources/js/components/agent/chat/items.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buildItems: () => (/* binding */ buildItems),
/* harmony export */   newReports: () => (/* binding */ newReports)
/* harmony export */ });
/**
 * The lines shown in a conversation, from the messages the server returns and
 * the documents the agent produced.
 *
 * A document appears as a card right after the last message of the run that
 * produced it, so it sits where the agent mentioned it.
 *
 * @param {Array} messages {role: user|agent|system, kind: text|tool|system, text?, tool?, run_id}
 * @param {Array} reports {id, run_id, title}
 * @returns {Array} {kind: user|agent|tool|system|report, ...}
 */
function buildItems(messages) {
  var reports = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
  var items = [];
  messages.forEach(function (message, i) {
    if (message.kind === "tool") {
      items.push({
        kind: "tool",
        tool: message.tool
      });
    } else if (message.kind === "system" || message.role === "system") {
      items.push({
        kind: "system",
        text: message.text
      });
    } else if (message.role === "user") {
      items.push({
        kind: "user",
        text: message.text
      });
    } else {
      items.push({
        kind: "agent",
        text: message.text
      });
    }
    var next = messages[i + 1];
    if (message.run_id && (!next || next.run_id !== message.run_id)) {
      reports.filter(function (report) {
        return report.run_id === message.run_id;
      }).forEach(function (report) {
        return items.push({
          kind: "report",
          id: report.id,
          title: report.title
        });
      });
    }
  });
  return items;
}

/**
 * The documents that were not there before: the newest one is opened by itself.
 */
function newReports(before, after) {
  var known = new Set(before.map(function (report) {
    return report.id;
  }));
  return after.filter(function (report) {
    return !known.has(report.id);
  });
}

/***/ }),

/***/ "./resources/js/utils/markdown.js":
/*!****************************************!*\
  !*** ./resources/js/utils/markdown.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   escapeHtml: () => (/* binding */ escapeHtml),
/* harmony export */   renderMarkdown: () => (/* binding */ renderMarkdown)
/* harmony export */ });
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
/**
 * Markdown to HTML for what an AI agent writes (answers and documents).
 *
 * What an agent writes is untrusted: it reads names, notes and messages of the
 * CRM, which anybody can fill in, so a prompt injection can make it write
 * anything. The renderer is therefore built to be safe by construction:
 *
 *  - the whole text is HTML-escaped FIRST; no tag of the input ever survives,
 *    and only the tags written here are produced;
 *  - links are limited to http(s) and mailto, and open in a new tab with
 *    rel="noopener noreferrer";
 *  - images are NOT supported: an image address would send data to a stranger's
 *    server as soon as the page is displayed (a classic way to steal data).
 *
 * Supported: titles, paragraphs, bold, italic, strikethrough, inline code,
 * code blocks, quotes, bulleted and numbered lists (nested), tables, rules, links.
 */

var ESCAPES = {
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;",
  "'": "&#39;"
};
function escapeHtml(text) {
  return String(text).replace(/[&<>"']/g, function (c) {
    return ESCAPES[c];
  });
}

// An address already escaped by escapeHtml: only http(s) and mailto
function safeUrl(escaped) {
  var url = escaped.replace(/&amp;/g, "&");
  return /^(https?:\/\/|mailto:)[^\s"'<>`]+$/i.test(url) ? escaped : null;
}

/**
 * Inline formatting of text that is already escaped
 */
function inline(text) {
  var codes = [];

  // Code first, so that nothing inside it is formatted
  text = text.replace(/`([^`\n]+)`/g, function (_, code) {
    codes.push(code);
    return "\x01".concat(codes.length - 1, "\x01");
  });
  text = text.replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, function (match, label, url) {
    var safe = safeUrl(url);
    return safe ? "<a href=\"".concat(safe, "\" target=\"_blank\" rel=\"noopener noreferrer\">").concat(label, "</a>") : match;
  });
  text = text.replace(/\*\*([^*\n]+?)\*\*/g, "<strong>$1</strong>").replace(/__([^_\n]+?)__/g, "<strong>$1</strong>").replace(/(^|[^*\w])\*([^*\s][^*\n]*?)\*(?!\w)/g, "$1<em>$2</em>").replace(/(^|[^_\w])_([^_\s][^_\n]*?)_(?!\w)/g, "$1<em>$2</em>").replace(/~~([^~\n]+?)~~/g, "<del>$1</del>");
  return text.replace(/\u0001(\d+)\u0001/g, function (_, i) {
    return "<code>".concat(codes[i], "</code>");
  });
}
var FENCE = /^ {0,3}(```|~~~)\s*([\w+-]*)\s*$/;
var HEADING = /^ {0,3}(#{1,6})\s+(.*?)\s*#*\s*$/;
var RULE = /^ {0,3}([-*_])(\s*\1){2,}\s*$/;
var QUOTE = /^ {0,3}&gt;\s?(.*)$/;
var BULLET = /^(\s*)[-*+]\s+(.*)$/;
var NUMBERED = /^(\s*)\d+[.)]\s+(.*)$/;
var TABLE_SEPARATOR = /^\s*\|?\s*:?-{2,}:?\s*(\|\s*:?-{2,}:?\s*)*\|?\s*$/;
function cells(line) {
  return line.trim().replace(/^\|/, "").replace(/\|$/, "").split("|").map(function (cell) {
    return cell.trim();
  });
}
function startsBlock(line, next) {
  return FENCE.test(line) || HEADING.test(line) || RULE.test(line) || QUOTE.test(line) || BULLET.test(line) || NUMBERED.test(line) || line.includes("|") && next !== undefined && TABLE_SEPARATOR.test(next) && next.includes("-");
}
function list(lines, start) {
  var first = lines[start];
  var ordered = NUMBERED.test(first);
  var marker = ordered ? NUMBERED : BULLET;
  var baseIndent = first.match(/^\s*/)[0].length;
  var items = [];
  var i = start;
  while (i < lines.length) {
    var line = lines[i];
    var match = line.match(marker);
    if (!match || match[1].length < baseIndent) {
      break;
    }
    if (match[1].length > baseIndent + 1) {
      // Deeper than the list: it belongs to the item above (handled below)
      break;
    }
    var body = [match[2]];
    i++;

    // Lines indented deeper than the marker belong to this item (sub-lists, continuations)
    var nested = [];
    while (i < lines.length) {
      var next = lines[i];
      var indent = next.match(/^\s*/)[0].length;
      if (next.trim() === "" || indent <= baseIndent + 1) {
        break;
      }
      nested.push(next.slice(Math.min(indent, baseIndent + 2)));
      i++;
    }
    var html = inline(body.join(" "));
    if (nested.length) {
      html += blocks(nested);
    }
    items.push("<li>".concat(html, "</li>"));
  }
  var tag = ordered ? "ol" : "ul";
  return ["<".concat(tag, ">").concat(items.join(""), "</").concat(tag, ">"), i];
}
function table(lines, start) {
  var header = cells(lines[start]);
  var aligns = cells(lines[start + 1]).map(function (cell) {
    return cell.startsWith(":") && cell.endsWith(":") ? "center" : cell.endsWith(":") ? "right" : cell.startsWith(":") ? "left" : null;
  });
  var align = function align(i) {
    return aligns[i] ? " style=\"text-align:".concat(aligns[i], "\"") : "";
  };
  var html = "<div class=\"hc-md-table\"><table><thead><tr>";
  html += header.map(function (cell, i) {
    return "<th".concat(align(i), ">").concat(inline(cell), "</th>");
  }).join("");
  html += "</tr></thead><tbody>";
  var i = start + 2;
  var _loop = function _loop() {
    var row = cells(lines[i]);
    html += "<tr>" + header.map(function (_, c) {
      var _row$c;
      return "<td".concat(align(c), ">").concat(inline((_row$c = row[c]) !== null && _row$c !== void 0 ? _row$c : ""), "</td>");
    }).join("") + "</tr>";
    i++;
  };
  while (i < lines.length && lines[i].trim() !== "" && lines[i].includes("|")) {
    _loop();
  }
  return [html + "</tbody></table></div>", i];
}
function blocks(lines) {
  var out = [];
  var i = 0;
  while (i < lines.length) {
    var line = lines[i];
    if (line.trim() === "") {
      i++;
      continue;
    }
    var fence = line.match(FENCE);
    if (fence) {
      var code = [];
      i++;
      while (i < lines.length && !new RegExp("^ {0,3}".concat(fence[1], "\\s*$")).test(lines[i])) {
        code.push(lines[i]);
        i++;
      }
      i++; // the closing fence (or the end of the text)
      out.push("<pre><code>".concat(code.join("\n"), "</code></pre>"));
      continue;
    }
    var heading = line.match(HEADING);
    if (heading) {
      out.push("<h".concat(heading[1].length, ">").concat(inline(heading[2]), "</h").concat(heading[1].length, ">"));
      i++;
      continue;
    }
    if (RULE.test(line)) {
      out.push("<hr>");
      i++;
      continue;
    }
    if (QUOTE.test(line)) {
      var quoted = [];
      while (i < lines.length && QUOTE.test(lines[i])) {
        quoted.push(lines[i].match(QUOTE)[1]);
        i++;
      }
      out.push("<blockquote>".concat(blocks(quoted), "</blockquote>"));
      continue;
    }
    if (BULLET.test(line) || NUMBERED.test(line)) {
      var _list = list(lines, i),
        _list2 = _slicedToArray(_list, 2),
        html = _list2[0],
        next = _list2[1];
      out.push(html);
      i = next;
      continue;
    }
    if (line.includes("|") && lines[i + 1] !== undefined && lines[i + 1].includes("-") && TABLE_SEPARATOR.test(lines[i + 1])) {
      var _table = table(lines, i),
        _table2 = _slicedToArray(_table, 2),
        _html = _table2[0],
        _next = _table2[1];
      out.push(_html);
      i = _next;
      continue;
    }

    // A paragraph: lines up to a blank line or the start of another block
    var paragraph = [line];
    i++;
    while (i < lines.length && lines[i].trim() !== "" && !startsBlock(lines[i], lines[i + 1])) {
      paragraph.push(lines[i]);
      i++;
    }
    out.push("<p>".concat(paragraph.map(function (l) {
      return inline(l.trim());
    }).join("<br>"), "</p>"));
  }
  return out.join("");
}

/**
 * @param {string} text Markdown written by an agent
 * @returns {string} HTML that is safe to put in v-html
 */
function renderMarkdown(text) {
  if (text === null || text === undefined) {
    return "";
  }

  // The escape comes first, and the markers used internally cannot be forged
  var escaped = escapeHtml(String(text).replace(/\u0000|\u0001/g, "")).replace(/\r\n?/g, "\n");
  return blocks(escaped.split("\n"));
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css ***!
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
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-ag-perm {\n    --perm: var(--ag-warn);\n    --perm-soft: var(--ag-warn-soft);\n    border: 1px solid var(--ag-line);\n    border-left: 4px solid var(--perm);\n    border-radius: var(--ag-radius);\n    background: #fff;\n    box-shadow: var(--ag-shadow);\n    overflow: hidden;\n}\n.hc-ag-perm.risk-communication {\n    --perm: var(--ag-accent);\n    --perm-soft: var(--ag-accent-soft);\n}\n.hc-ag-perm.risk-destructive {\n    --perm: var(--ag-danger);\n    --perm-soft: var(--ag-danger-soft);\n}\n.hc-ag-perm.is-executed {\n    --perm: var(--ag-success);\n    --perm-soft: var(--ag-success-soft);\n}\n.hc-ag-perm.is-rejected,\n.hc-ag-perm.is-expired {\n    --perm: #94a3b8;\n    --perm-soft: var(--ag-surface);\n    opacity: 0.85;\n}\n.hc-ag-perm.is-failed {\n    --perm: var(--ag-danger);\n    --perm-soft: var(--ag-danger-soft);\n}\n.hc-ag-perm-head {\n    display: flex;\n    align-items: center;\n    gap: 12px;\n    padding: 14px 16px 10px;\n}\n.hc-ag-perm-icon {\n    flex: 0 0 38px;\n    width: 38px;\n    height: 38px;\n    border-radius: 50%;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    background: var(--perm-soft);\n    color: var(--perm);\n    font-size: 16px;\n}\n.hc-ag-perm-heading {\n    flex: 1;\n    min-width: 0;\n}\n.hc-ag-perm-eyebrow {\n    display: block;\n    font-size: 12px;\n    color: var(--ag-muted);\n}\n.hc-ag-perm-heading h4 {\n    margin: 2px 0 0;\n    font-size: 15px;\n    line-height: 1.3;\n    color: var(--ag-ink);\n    overflow-wrap: anywhere;\n}\n.hc-ag-perm-risk {\n    flex: 0 0 auto;\n    padding: 3px 10px;\n    border-radius: 999px;\n    background: var(--perm-soft);\n    color: var(--perm);\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.04em;\n    text-transform: uppercase;\n}\n.hc-ag-perm-body {\n    padding: 0 16px 12px 66px;\n    display: flex;\n    flex-direction: column;\n    gap: 10px;\n}\n.hc-ag-perm-stat {\n    display: flex;\n    align-items: baseline;\n    gap: 8px;\n}\n.hc-ag-perm-stat strong {\n    font-size: 28px;\n    line-height: 1;\n    color: var(--ag-ink);\n    font-variant-numeric: tabular-nums;\n}\n.hc-ag-perm-stat span {\n    color: var(--ag-muted);\n}\n.hc-ag-perm-details {\n    display: grid;\n    grid-template-columns: max-content 1fr;\n    gap: 4px 14px;\n    margin: 0;\n    font-size: 13px;\n}\n.hc-ag-perm-details dt {\n    color: var(--ag-muted);\n}\n.hc-ag-perm-details dd {\n    margin: 0;\n    color: var(--ag-ink-soft);\n    overflow-wrap: anywhere;\n    white-space: pre-wrap;\n    display: -webkit-box;\n    -webkit-line-clamp: 6;\n    -webkit-box-orient: vertical;\n    overflow: hidden;\n}\n.hc-ag-perm-sample {\n    list-style: none;\n    margin: 0;\n    padding: 0;\n    display: flex;\n    flex-wrap: wrap;\n    gap: 6px;\n}\n.hc-ag-perm-sample li {\n    padding: 3px 10px;\n    border-radius: 999px;\n    background: var(--ag-surface);\n    border: 1px solid var(--ag-line);\n    color: var(--ag-ink-soft);\n    font-size: 12px;\n}\n.hc-ag-perm-note {\n    margin: 0;\n    padding: 8px 10px;\n    border-radius: 8px;\n    background: var(--ag-surface);\n    color: var(--ag-muted);\n    font-size: 12px;\n    display: flex;\n    gap: 8px;\n}\n.hc-ag-perm-state {\n    margin: 0 16px 12px 66px;\n    padding: 9px 12px;\n    border-radius: 8px;\n    font-size: 13px;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n}\n.hc-ag-perm-state.is-running {\n    background: var(--ag-accent-soft);\n    color: var(--ag-accent-strong);\n}\n.hc-ag-perm-state.is-ok {\n    background: var(--ag-success-soft);\n    color: #166534;\n}\n.hc-ag-perm-state.is-ko {\n    background: var(--ag-danger-soft);\n    color: var(--ag-danger-strong);\n}\n.hc-ag-perm-state.is-muted {\n    background: var(--ag-surface);\n    color: var(--ag-muted);\n}\n.hc-ag-perm-progress {\n    flex: 1;\n    height: 6px;\n    border-radius: 3px;\n    background: rgba(18, 160, 243, 0.2);\n    overflow: hidden;\n}\n.hc-ag-perm-progress span {\n    display: block;\n    height: 100%;\n    background: var(--ag-accent);\n    transition: width 0.3s ease;\n}\n.hc-ag-perm-spinner {\n    width: 14px;\n    height: 14px;\n    border: 2px solid currentColor;\n    border-right-color: transparent;\n    border-radius: 50%;\n    animation: hc-ag-spin 0.7s linear infinite;\n}\n.hc-ag-perm-foot {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    flex-wrap: wrap;\n    gap: 10px;\n    padding: 12px 16px;\n    border-top: 1px solid var(--ag-line);\n    background: var(--ag-surface);\n}\n.hc-ag-perm-expiry {\n    color: var(--ag-muted);\n    font-size: 12px;\n    display: inline-flex;\n    gap: 6px;\n    align-items: center;\n}\n.hc-ag-perm-actions {\n    display: flex;\n    gap: 8px;\n    margin-left: auto;\n}\n@media (max-width: 560px) {\n.hc-ag-perm-body,\n    .hc-ag-perm-state {\n        padding-left: 16px;\n        margin-left: 16px;\n}\n.hc-ag-perm-actions {\n        width: 100%;\n}\n.hc-ag-perm-actions .hc-ag-btn {\n        flex: 1;\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-agent-bar {\n    flex: 0 0 auto;\n    background: #fff;\n    border-bottom: 1px solid var(--ag-line);\n}\n.hc-agent-bar-scroll {\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    padding: 10px 16px;\n    overflow-x: auto;\n    scrollbar-width: thin;\n}\n.hc-agent-chip {\n    display: flex;\n    align-items: center;\n    flex: 0 0 auto;\n    border: 1px solid var(--ag-line);\n    border-radius: 999px;\n    background: #fff;\n    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;\n}\n.hc-agent-chip:hover {\n    border-color: #cbd5e1;\n}\n.hc-agent-chip.is-selected {\n    border-color: var(--ag-accent);\n    background: var(--ag-accent-soft);\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.12);\n}\n.hc-agent-chip button {\n    border: 0;\n    background: transparent;\n    cursor: pointer;\n    font: inherit;\n}\n.hc-agent-chip button:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n.hc-agent-chip-main {\n    display: flex;\n    align-items: center;\n    gap: 9px;\n    padding: 5px 6px 5px 6px;\n    border-radius: 999px;\n    color: var(--ag-ink);\n}\n.hc-agent-chip-main img,\n.hc-agent-chip-main .fa-robot {\n    width: 30px;\n    height: 30px;\n    border-radius: 50%;\n    -o-object-fit: cover;\n       object-fit: cover;\n    background: var(--ag-surface);\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 14px;\n    color: var(--ag-muted);\n}\n.hc-agent-chip-name {\n    font-weight: 600;\n    white-space: nowrap;\n}\n.hc-agent-chip-info {\n    width: 30px;\n    height: 30px;\n    margin-right: 4px;\n    padding: 0;\n    border-radius: 50%;\n    color: #94a3b8;\n    font-size: 15px;\n}\n.hc-agent-chip-info:hover {\n    background: rgba(18, 160, 243, 0.14);\n    color: var(--ag-accent-strong);\n}\n.hc-agent-chip-add {\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    flex: 0 0 auto;\n    padding: 8px 14px;\n    border: 1px dashed #cbd5e1;\n    border-radius: 999px;\n    background: transparent;\n    color: var(--ag-muted);\n    cursor: pointer;\n    font: inherit;\n    white-space: nowrap;\n    transition: border-color 0.15s ease, color 0.15s ease;\n}\n.hc-agent-chip-add:hover {\n    border-color: var(--ag-accent);\n    color: var(--ag-accent-strong);\n}\n.hc-agent-chip-add:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-agent-info-layer {\n    position: absolute;\n    inset: 0;\n    z-index: 20;\n}\n.hc-agent-info-backdrop {\n    position: absolute;\n    inset: 0;\n    background: rgba(15, 23, 42, 0.3);\n    -webkit-backdrop-filter: blur(1px);\n            backdrop-filter: blur(1px);\n}\n.hc-agent-info {\n    position: absolute;\n    top: 0;\n    bottom: 0;\n    left: 0;\n    width: 380px;\n    max-width: 94%;\n    background: #fff;\n    box-shadow: 8px 0 32px rgba(15, 23, 42, 0.18);\n    overflow-y: auto;\n    display: flex;\n    flex-direction: column;\n    color: var(--ag-ink);\n}\n.hc-agent-info header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding: 12px 14px 12px 20px;\n    border-bottom: 1px solid var(--ag-line);\n    position: sticky;\n    top: 0;\n    background: rgba(255, 255, 255, 0.95);\n    -webkit-backdrop-filter: blur(4px);\n            backdrop-filter: blur(4px);\n    z-index: 1;\n}\n.hc-agent-info-body {\n    padding: 22px 20px 28px;\n    display: flex;\n    flex-direction: column;\n    gap: 18px;\n}\n.hc-agent-info-identity {\n    display: flex;\n    flex-direction: column;\n    align-items: center;\n    text-align: center;\n    gap: 4px;\n    padding-bottom: 6px;\n}\n.hc-agent-info-identity img,\n.hc-agent-info-identity .fa-robot {\n    width: 104px;\n    height: 104px;\n    border-radius: 50%;\n    -o-object-fit: cover;\n       object-fit: cover;\n    background: var(--ag-surface);\n    box-shadow: var(--ag-shadow);\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 46px;\n    color: #94a3b8;\n}\n.hc-agent-info-identity h3 {\n    margin: 10px 0 0;\n    font-size: 20px;\n}\n.hc-agent-info-specialty {\n    margin: 0;\n    color: var(--ag-muted);\n}\n.hc-agent-info-badge {\n    margin-top: 4px;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.04em;\n    text-transform: uppercase;\n    padding: 3px 12px;\n    border-radius: 999px;\n    background: var(--ag-surface);\n    color: var(--ag-muted);\n    border: 1px solid var(--ag-line);\n}\n.hc-agent-info-badge.is-propose {\n    background: var(--ag-accent-soft);\n    color: var(--ag-accent-strong);\n    border-color: transparent;\n}\n.hc-agent-info-badge.is-auto {\n    background: var(--ag-warn-soft);\n    color: #92400e;\n    border-color: transparent;\n}\n.hc-agent-info-body section {\n    padding-top: 14px;\n    border-top: 1px solid var(--ag-line);\n}\n.hc-agent-info-body h4 {\n    margin: 0 0 6px;\n    font-size: 11px;\n    font-weight: 700;\n    color: var(--ag-muted);\n    text-transform: uppercase;\n    letter-spacing: 0.06em;\n}\n.hc-agent-info-body p {\n    margin: 0;\n    white-space: pre-wrap;\n    overflow-wrap: anywhere;\n    color: var(--ag-ink-soft);\n    line-height: 1.5;\n}\n.hc-agent-info-instructions {\n    max-height: 220px;\n    overflow-y: auto;\n    padding: 10px 12px;\n    background: var(--ag-surface);\n    border: 1px solid var(--ag-line);\n    border-radius: 10px;\n    font-size: 13px;\n}\n.hc-agent-info-hint,\n.hc-agent-info-error {\n    padding: 20px;\n    margin: 0;\n    color: var(--ag-muted);\n}\n.hc-agent-info-error {\n    color: var(--ag-danger-strong);\n}\n\n/* The drawer slides in from the left */\n.hc-agent-info-enter-active .hc-agent-info,\n.hc-agent-info-leave-active .hc-agent-info {\n    transition: transform 0.25s cubic-bezier(0.2, 0.7, 0.2, 1);\n}\n.hc-agent-info-enter-active .hc-agent-info-backdrop,\n.hc-agent-info-leave-active .hc-agent-info-backdrop {\n    transition: opacity 0.25s ease;\n}\n.hc-agent-info-enter-from .hc-agent-info,\n.hc-agent-info-leave-to .hc-agent-info {\n    transform: translateX(-100%);\n}\n.hc-agent-info-enter-from .hc-agent-info-backdrop,\n.hc-agent-info-leave-to .hc-agent-info-backdrop {\n    opacity: 0;\n}\n@media (prefers-reduced-motion: reduce) {\n.hc-agent-info-enter-active .hc-agent-info,\n    .hc-agent-info-leave-active .hc-agent-info,\n    .hc-agent-info-enter-active .hc-agent-info-backdrop,\n    .hc-agent-info-leave-active .hc-agent-info-backdrop {\n        transition: none;\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n#hc-agent-page {\n    height: 100%;\n    display: flex;\n    flex-direction: column;\n    background: #fff;\n}\n.hc-agent-page-body {\n    flex: 1;\n    min-height: 0;\n    position: relative;\n    display: flex;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-agent-chat {\n    flex: 1;\n    min-width: 0;\n    display: flex;\n    flex-direction: column;\n    background: #fff;\n    color: var(--ag-ink);\n}\n.hc-agent-chat-header {\n    flex: 0 0 auto;\n    display: flex;\n    align-items: center;\n    gap: 12px;\n    padding: 12px 22px;\n    border-bottom: 1px solid var(--ag-line);\n}\n.hc-agent-chat-header img,\n.hc-agent-chat-header .fa-robot {\n    width: 34px;\n    height: 34px;\n    border-radius: 50%;\n    -o-object-fit: cover;\n       object-fit: cover;\n    background: var(--ag-surface);\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    color: var(--ag-muted);\n}\n.hc-agent-chat-heading {\n    display: flex;\n    flex-direction: column;\n    min-width: 0;\n    line-height: 1.25;\n}\n.hc-agent-chat-heading span {\n    color: var(--ag-muted);\n    font-size: 12px;\n    overflow: hidden;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-agent-chat-messages {\n    flex: 1;\n    overflow-y: auto;\n    padding: 24px 22px;\n    display: flex;\n    flex-direction: column;\n    gap: 14px;\n    scroll-behavior: smooth;\n}\n.hc-agent-chat-welcome {\n    margin: auto;\n    max-width: 560px;\n    text-align: center;\n    color: var(--ag-muted);\n    display: flex;\n    flex-direction: column;\n    align-items: center;\n    gap: 8px;\n}\n.hc-agent-chat-welcome img,\n.hc-agent-chat-welcome > .fa-robot {\n    width: 88px;\n    height: 88px;\n    border-radius: 50%;\n    -o-object-fit: cover;\n       object-fit: cover;\n    background: var(--ag-surface);\n    box-shadow: var(--ag-shadow);\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 38px;\n    color: #94a3b8;\n}\n.hc-agent-chat-welcome h3 {\n    margin: 6px 0 0;\n    color: var(--ag-ink);\n    font-size: 22px;\n}\n.hc-agent-chat-welcome p {\n    margin: 0;\n}\n.hc-agent-chat-suggestions {\n    display: flex;\n    flex-direction: column;\n    gap: 10px;\n    margin-top: 18px;\n    width: 100%;\n}\n.hc-agent-chat-suggestions button {\n    display: flex;\n    align-items: center;\n    gap: 12px;\n    padding: 12px 16px;\n    border: 1px solid var(--ag-line);\n    border-radius: var(--ag-radius);\n    background: #fff;\n    color: var(--ag-ink-soft);\n    text-align: left;\n    cursor: pointer;\n    font: inherit;\n    transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;\n}\n.hc-agent-chat-suggestions button i {\n    flex: 0 0 18px;\n    color: var(--ag-accent);\n    text-align: center;\n}\n.hc-agent-chat-suggestions button:hover {\n    border-color: var(--ag-accent);\n    box-shadow: var(--ag-shadow);\n    transform: translateY(-1px);\n}\n.hc-agent-chat-suggestions button:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n.hc-agent-msg {\n    max-width: min(760px, 94%);\n    line-height: 1.55;\n}\n.hc-agent-msg.is-user {\n    align-self: flex-end;\n    padding: 10px 16px;\n    border-radius: 18px 18px 4px 18px;\n    background: var(--ag-accent-soft);\n    color: var(--ag-ink);\n    white-space: pre-wrap;\n    overflow-wrap: anywhere;\n}\n.hc-agent-msg.is-agent {\n    align-self: flex-start;\n    padding: 2px 4px;\n    color: var(--ag-ink);\n    font-size: 15px;\n}\n.hc-agent-msg.is-working {\n    display: inline-flex;\n    align-items: center;\n    gap: 10px;\n    padding: 8px 14px;\n    border-radius: 999px;\n    background: var(--ag-surface);\n    color: var(--ag-muted);\n    font-size: 13px;\n}\n.hc-agent-dots {\n    display: inline-flex;\n    gap: 4px;\n}\n.hc-agent-dots i {\n    width: 6px;\n    height: 6px;\n    border-radius: 50%;\n    background: var(--ag-accent);\n    animation: hc-agent-blink 1.2s infinite ease-in-out;\n}\n.hc-agent-dots i:nth-child(2) { animation-delay: 0.2s;\n}\n.hc-agent-dots i:nth-child(3) { animation-delay: 0.4s;\n}\n@keyframes hc-agent-blink {\n0%, 80%, 100% { opacity: 0.25; transform: scale(0.85);\n}\n40% { opacity: 1; transform: scale(1);\n}\n}\n.hc-agent-msg-tool {\n    align-self: flex-start;\n    display: inline-flex;\n    align-items: center;\n    gap: 8px;\n    padding: 3px 12px;\n    border: 1px solid var(--ag-line);\n    border-radius: 999px;\n    background: var(--ag-surface);\n    color: var(--ag-muted);\n    font-size: 12px;\n}\n.hc-agent-msg-system {\n    align-self: center;\n    max-width: 90%;\n    padding: 5px 14px;\n    border-radius: 999px;\n    background: var(--ag-warn-soft);\n    color: #92400e;\n    font-size: 12px;\n    overflow-wrap: anywhere;\n}\n.hc-agent-report-card {\n    align-self: flex-start;\n    display: flex;\n    align-items: center;\n    gap: 14px;\n    width: min(460px, 94%);\n    padding: 12px 16px;\n    border: 1px solid var(--ag-line);\n    border-radius: var(--ag-radius);\n    background: #fff;\n    box-shadow: var(--ag-shadow);\n    cursor: pointer;\n    font: inherit;\n    text-align: left;\n    transition: border-color 0.15s ease, transform 0.15s ease;\n}\n.hc-agent-report-card:hover,\n.hc-agent-report-card.is-open {\n    border-color: var(--ag-accent);\n}\n.hc-agent-report-card:hover {\n    transform: translateY(-1px);\n}\n.hc-agent-report-card:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n.hc-agent-report-card-icon {\n    flex: 0 0 40px;\n    width: 40px;\n    height: 40px;\n    border-radius: 10px;\n    background: var(--ag-accent-soft);\n    color: var(--ag-accent);\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 18px;\n}\n.hc-agent-report-card-text {\n    flex: 1;\n    min-width: 0;\n    display: flex;\n    flex-direction: column;\n}\n.hc-agent-report-card-title {\n    font-weight: 600;\n    color: var(--ag-ink);\n    overflow: hidden;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-agent-report-card-kind {\n    color: var(--ag-muted);\n    font-size: 12px;\n}\n.hc-agent-report-card-open {\n    color: var(--ag-accent-strong);\n    font-size: 13px;\n    font-weight: 600;\n    white-space: nowrap;\n}\n.hc-agent-chat-action {\n    width: min(660px, 100%);\n}\n.hc-agent-chat-error {\n    flex: 0 0 auto;\n    margin: 0 22px 10px;\n    padding: 10px 14px;\n    border-radius: 10px;\n    background: var(--ag-danger-soft);\n    color: var(--ag-danger-strong);\n    display: flex;\n    gap: 10px;\n    align-items: flex-start;\n    font-size: 13px;\n}\n.hc-agent-chat-form {\n    flex: 0 0 auto;\n    display: flex;\n    align-items: flex-end;\n    gap: 10px;\n    margin: 0 22px;\n    padding: 8px 8px 8px 18px;\n    border: 1px solid var(--ag-line);\n    border-radius: 22px;\n    background: #fff;\n    box-shadow: var(--ag-shadow);\n    transition: border-color 0.15s ease, box-shadow 0.15s ease;\n}\n.hc-agent-chat-form:focus-within {\n    border-color: var(--ag-accent);\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.18), var(--ag-shadow);\n}\n.hc-agent-chat-form textarea {\n    flex: 1;\n    border: 0;\n    outline: 0;\n    resize: none;\n    max-height: 200px;\n    padding: 8px 0;\n    font: inherit;\n    font-size: 15px;\n    line-height: 1.4;\n    color: var(--ag-ink);\n    background: transparent;\n}\n.hc-agent-chat-form textarea::-moz-placeholder {\n    color: #94a3b8;\n}\n.hc-agent-chat-form textarea::placeholder {\n    color: #94a3b8;\n}\n.hc-agent-chat-hint {\n    flex: 0 0 auto;\n    margin: 6px 22px 12px;\n    text-align: center;\n    color: #94a3b8;\n    font-size: 11px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-agent-history {\n    flex: 0 0 290px;\n    width: 290px;\n    display: flex;\n    flex-direction: column;\n    background: var(--ag-surface);\n    border-right: 1px solid var(--ag-line);\n    transition: width 0.2s ease, flex-basis 0.2s ease;\n    overflow: hidden;\n}\n.hc-agent-history.is-collapsed {\n    flex-basis: 56px;\n    width: 56px;\n}\n.hc-agent-history-top {\n    display: flex;\n    flex-direction: column;\n    gap: 8px;\n    padding: 12px;\n}\n.hc-agent-history.is-collapsed .hc-agent-history-top {\n    align-items: center;\n    padding: 12px 0;\n}\n.hc-agent-history-toggle {\n    align-self: flex-end;\n}\n.hc-agent-history.is-collapsed .hc-agent-history-toggle {\n    align-self: center;\n}\n.hc-agent-history-hint {\n    margin: 0;\n    padding: 6px 16px;\n    color: var(--ag-muted);\n    font-size: 13px;\n}\n.hc-agent-history-list {\n    list-style: none;\n    margin: 0;\n    padding: 4px 8px 12px;\n    overflow-y: auto;\n    flex: 1;\n}\n.hc-agent-history-list li {\n    display: flex;\n    align-items: center;\n    border-radius: 10px;\n    transition: background-color 0.12s ease;\n}\n.hc-agent-history-list li:hover {\n    background: rgba(15, 23, 42, 0.05);\n}\n.hc-agent-history-list li.is-active {\n    background: #fff;\n    box-shadow: 0 0 0 1px var(--ag-line), 0 1px 3px rgba(15, 23, 42, 0.06);\n}\n.hc-agent-history-item {\n    flex: 1;\n    min-width: 0;\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    border: 0;\n    background: transparent;\n    cursor: pointer;\n    font: inherit;\n    text-align: left;\n    padding: 9px 10px;\n    color: var(--ag-ink-soft);\n    border-radius: 10px;\n}\n.hc-agent-history-item:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n.hc-agent-history-list li.is-active .hc-agent-history-item {\n    color: var(--ag-ink);\n    font-weight: 600;\n}\n.hc-agent-history-item img,\n.hc-agent-history-item .fa-robot {\n    flex: 0 0 22px;\n    width: 22px;\n    height: 22px;\n    border-radius: 50%;\n    -o-object-fit: cover;\n       object-fit: cover;\n    font-size: 11px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    background: #e2e8f0;\n    color: var(--ag-muted);\n}\n.hc-agent-history-title {\n    overflow: hidden;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-agent-history-remove {\n    margin-right: 4px;\n    visibility: hidden;\n}\n.hc-agent-history-list li:hover .hc-agent-history-remove,\n.hc-agent-history-remove:focus-visible {\n    visibility: visible;\n}\n@media (max-width: 767px) {\n.hc-agent-history {\n        flex-basis: 56px;\n        width: 56px;\n}\n.hc-agent-history:not(.is-collapsed) {\n        position: absolute;\n        z-index: 15;\n        top: 0;\n        bottom: 0;\n        left: 0;\n        width: 290px;\n        box-shadow: 8px 0 32px rgba(15, 23, 42, 0.18);\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-md {\n    line-height: 1.55;\n    overflow-wrap: anywhere;\n}\n.hc-md > :first-child {\n    margin-top: 0;\n}\n.hc-md > :last-child {\n    margin-bottom: 0;\n}\n.hc-md h1,\n.hc-md h2,\n.hc-md h3,\n.hc-md h4,\n.hc-md h5,\n.hc-md h6 {\n    margin: 1.1em 0 0.4em;\n    line-height: 1.25;\n}\n.hc-md h1 { font-size: 1.45em;\n}\n.hc-md h2 { font-size: 1.25em;\n}\n.hc-md h3 { font-size: 1.1em;\n}\n.hc-md h4,\n.hc-md h5,\n.hc-md h6 { font-size: 1em;\n}\n.hc-md p,\n.hc-md ul,\n.hc-md ol,\n.hc-md blockquote,\n.hc-md pre {\n    margin: 0.5em 0;\n}\n.hc-md ul,\n.hc-md ol {\n    padding-left: 1.4em;\n}\n.hc-md li {\n    margin: 0.15em 0;\n}\n.hc-md blockquote {\n    padding: 0.2em 0.9em;\n    border-left: 3px solid #ccc;\n    color: #555;\n}\n.hc-md code {\n    padding: 0.1em 0.35em;\n    border-radius: 4px;\n    background: #f0f0f2;\n    font-size: 0.9em;\n}\n.hc-md pre {\n    padding: 10px 12px;\n    border-radius: 6px;\n    background: #f0f0f2;\n    overflow-x: auto;\n}\n.hc-md pre code {\n    padding: 0;\n    background: transparent;\n}\n.hc-md hr {\n    border: 0;\n    border-top: 1px solid #ddd;\n    margin: 1em 0;\n}\n.hc-md a {\n    color: #0b78b8;\n    text-decoration: underline;\n}\n.hc-md .hc-md-table {\n    overflow-x: auto;\n    margin: 0.6em 0;\n}\n.hc-md table {\n    border-collapse: collapse;\n    min-width: 50%;\n}\n.hc-md th,\n.hc-md td {\n    padding: 5px 10px;\n    border: 1px solid #ddd;\n    text-align: left;\n}\n.hc-md th {\n    background: #f4f4f6;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-agent-canvas {\n    flex: 0 0 42%;\n    max-width: 660px;\n    min-width: 320px;\n    display: flex;\n    flex-direction: column;\n    background: #fff;\n    border-left: 1px solid var(--ag-line);\n    overflow: hidden;\n    color: var(--ag-ink);\n}\n.hc-agent-canvas > header {\n    flex: 0 0 auto;\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    gap: 8px;\n    padding: 10px 12px 10px 18px;\n    border-bottom: 1px solid var(--ag-line);\n}\n.hc-agent-canvas-title {\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    min-width: 0;\n}\n.hc-agent-canvas-title .fa-file-lines {\n    color: var(--ag-accent);\n}\n.hc-agent-canvas-title strong {\n    overflow: hidden;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}\n.hc-agent-canvas-actions {\n    display: flex;\n    gap: 2px;\n}\n.hc-agent-canvas-body {\n    flex: 1;\n    overflow-y: auto;\n    padding: 18px 24px 32px;\n}\n.hc-agent-canvas-meta {\n    margin: 0 0 12px;\n    color: var(--ag-muted);\n    font-size: 12px;\n    display: flex;\n    gap: 12px;\n}\n.hc-agent-canvas-empty {\n    flex: 1;\n    display: flex;\n    flex-direction: column;\n    align-items: center;\n    justify-content: center;\n    gap: 10px;\n    padding: 28px;\n    margin: 0;\n    color: var(--ag-muted);\n    text-align: center;\n}\n.hc-agent-canvas-empty .fa {\n    font-size: 40px;\n    color: #cbd5e1;\n}\n.hc-agent-canvas-empty.is-error {\n    color: var(--ag-danger-strong);\n}\n@media (max-width: 1100px) {\n.hc-agent-canvas {\n        position: absolute;\n        z-index: 12;\n        top: 0;\n        right: 0;\n        bottom: 0;\n        width: min(94%, 580px);\n        max-width: none;\n        box-shadow: -8px 0 32px rgba(15, 23, 42, 0.18);\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "\n/* Shared look of the AI agents screens */\n:root {\n    --ag-accent: #12a0f3;\n    --ag-accent-strong: #0b86d1;\n    --ag-accent-soft: rgba(18, 160, 243, 0.1);\n    --ag-ink: #0f172a;\n    --ag-ink-soft: #334155;\n    --ag-muted: #64748b;\n    --ag-line: #e2e8f0;\n    --ag-surface: #f8fafc;\n    --ag-danger: #dc2626;\n    --ag-danger-strong: #b91c1c;\n    --ag-danger-soft: #fef2f2;\n    --ag-success: #16a34a;\n    --ag-success-soft: #f0fdf4;\n    --ag-warn: #d97706;\n    --ag-warn-soft: #fffbeb;\n    --ag-radius: 12px;\n    --ag-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 6px 18px rgba(15, 23, 42, 0.06);\n}\n.hc-ag-btn {\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    gap: 8px;\n    border: 1px solid transparent;\n    border-radius: 10px;\n    font: inherit;\n    font-weight: 600;\n    line-height: 1;\n    white-space: nowrap;\n    cursor: pointer;\n    -webkit-user-select: none;\n       -moz-user-select: none;\n            user-select: none;\n    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease,\n        color 0.15s ease, transform 0.05s ease;\n}\n.hc-ag-btn.is-md {\n    padding: 10px 18px;\n    font-size: 14px;\n}\n.hc-ag-btn.is-sm {\n    padding: 7px 12px;\n    font-size: 13px;\n    border-radius: 8px;\n}\n.hc-ag-btn.is-icon {\n    padding: 0;\n    width: 36px;\n    height: 36px;\n    border-radius: 50%;\n}\n.hc-ag-btn.is-icon.is-sm {\n    width: 30px;\n    height: 30px;\n}\n.hc-ag-btn.is-block {\n    display: flex;\n    width: 100%;\n}\n.hc-ag-btn:active:not(:disabled) {\n    transform: translateY(1px);\n}\n.hc-ag-btn:focus-visible {\n    outline: 0;\n    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);\n}\n.hc-ag-btn:disabled {\n    cursor: not-allowed;\n    opacity: 0.55;\n}\n.hc-ag-btn.is-loading {\n    cursor: progress;\n    opacity: 0.85;\n}\n\n/* Primary: the main action of a screen */\n.hc-ag-btn.is-primary {\n    background: var(--ag-accent);\n    color: #fff;\n    box-shadow: 0 1px 2px rgba(11, 134, 209, 0.35);\n}\n.hc-ag-btn.is-primary:hover:not(:disabled) {\n    background: var(--ag-accent-strong);\n}\n\n/* Success: authorising something */\n.hc-ag-btn.is-success {\n    background: var(--ag-success);\n    color: #fff;\n    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.35);\n}\n.hc-ag-btn.is-success:hover:not(:disabled) {\n    background: #15803d;\n}\n\n/* Danger: something that cannot be undone */\n.hc-ag-btn.is-danger {\n    background: var(--ag-danger);\n    color: #fff;\n    box-shadow: 0 1px 2px rgba(185, 28, 28, 0.35);\n}\n.hc-ag-btn.is-danger:hover:not(:disabled) {\n    background: var(--ag-danger-strong);\n}\n\n/* Secondary: a visible but quieter action */\n.hc-ag-btn.is-secondary {\n    background: #fff;\n    border-color: var(--ag-line);\n    color: var(--ag-ink-soft);\n}\n.hc-ag-btn.is-secondary:hover:not(:disabled) {\n    border-color: #cbd5e1;\n    background: var(--ag-surface);\n}\n\n/* Ghost: no frame until hovered */\n.hc-ag-btn.is-ghost {\n    background: transparent;\n    color: var(--ag-muted);\n}\n.hc-ag-btn.is-ghost:hover:not(:disabled) {\n    background: rgba(15, 23, 42, 0.06);\n    color: var(--ag-ink);\n}\n.hc-ag-btn.is-ghost-danger {\n    background: transparent;\n    color: var(--ag-danger);\n}\n.hc-ag-btn.is-ghost-danger:hover:not(:disabled) {\n    background: var(--ag-danger-soft);\n}\n.hc-ag-btn-spinner {\n    width: 14px;\n    height: 14px;\n    border: 2px solid currentColor;\n    border-right-color: transparent;\n    border-radius: 50%;\n    animation: hc-ag-spin 0.7s linear infinite;\n}\n@keyframes hc-ag-spin {\nto {\n        transform: rotate(360deg);\n}\n}\n@media (prefers-reduced-motion: reduce) {\n.hc-ag-btn,\n    .hc-ag-btn-spinner {\n        transition: none;\n        animation-duration: 2s;\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_style_index_0_id_6e001e1b_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_style_index_0_id_6e001e1b_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_style_index_0_id_6e001e1b_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_style_index_0_id_0edeb280_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_style_index_0_id_0edeb280_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_style_index_0_id_0edeb280_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_style_index_0_id_e152ed5e_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_style_index_0_id_e152ed5e_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_style_index_0_id_e152ed5e_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_style_index_0_id_9014a29c_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_style_index_0_id_9014a29c_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_style_index_0_id_9014a29c_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_style_index_0_id_885cddec_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_style_index_0_id_885cddec_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_style_index_0_id_885cddec_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_style_index_0_id_3ac4d80f_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_style_index_0_id_3ac4d80f_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_style_index_0_id_3ac4d80f_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_style_index_0_id_762d8461_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_style_index_0_id_762d8461_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_style_index_0_id_762d8461_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_style_index_0_id_2e6c052e_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_style_index_0_id_2e6c052e_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_style_index_0_id_2e6c052e_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_style_index_0_id_13277cde_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_style_index_0_id_13277cde_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_style_index_0_id_13277cde_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/agent/ActionCard.vue":
/*!******************************************************!*\
  !*** ./resources/js/components/agent/ActionCard.vue ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ActionCard_vue_vue_type_template_id_6e001e1b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActionCard.vue?vue&type=template&id=6e001e1b */ "./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b");
/* harmony import */ var _ActionCard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActionCard.vue?vue&type=script&lang=js */ "./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js");
/* harmony import */ var _ActionCard_vue_vue_type_style_index_0_id_6e001e1b_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css */ "./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ActionCard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ActionCard_vue_vue_type_template_id_6e001e1b__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/ActionCard.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentBar.vue":
/*!*********************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentBar.vue ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AgentBar_vue_vue_type_template_id_0edeb280__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AgentBar.vue?vue&type=template&id=0edeb280 */ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280");
/* harmony import */ var _AgentBar_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AgentBar.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js");
/* harmony import */ var _AgentBar_vue_vue_type_style_index_0_id_0edeb280_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css */ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_AgentBar_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_AgentBar_vue_vue_type_template_id_0edeb280__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/AgentBar.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentInfo.vue":
/*!**********************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentInfo.vue ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AgentInfo_vue_vue_type_template_id_e152ed5e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AgentInfo.vue?vue&type=template&id=e152ed5e */ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e");
/* harmony import */ var _AgentInfo_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AgentInfo.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js");
/* harmony import */ var _AgentInfo_vue_vue_type_style_index_0_id_e152ed5e_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css */ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_AgentInfo_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_AgentInfo_vue_vue_type_template_id_e152ed5e__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/AgentInfo.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentPage.vue":
/*!**********************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentPage.vue ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AgentPage_vue_vue_type_template_id_9014a29c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AgentPage.vue?vue&type=template&id=9014a29c */ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c");
/* harmony import */ var _AgentPage_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AgentPage.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js");
/* harmony import */ var _AgentPage_vue_vue_type_style_index_0_id_9014a29c_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css */ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_AgentPage_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_AgentPage_vue_vue_type_template_id_9014a29c__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/AgentPage.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/ChatPanel.vue":
/*!**********************************************************!*\
  !*** ./resources/js/components/agent/chat/ChatPanel.vue ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ChatPanel_vue_vue_type_template_id_885cddec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ChatPanel.vue?vue&type=template&id=885cddec */ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec");
/* harmony import */ var _ChatPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ChatPanel.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js");
/* harmony import */ var _ChatPanel_vue_vue_type_style_index_0_id_885cddec_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css */ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ChatPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ChatPanel_vue_vue_type_template_id_885cddec__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/ChatPanel.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/ConversationHistory.vue":
/*!********************************************************************!*\
  !*** ./resources/js/components/agent/chat/ConversationHistory.vue ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ConversationHistory_vue_vue_type_template_id_3ac4d80f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ConversationHistory.vue?vue&type=template&id=3ac4d80f */ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f");
/* harmony import */ var _ConversationHistory_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ConversationHistory.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js");
/* harmony import */ var _ConversationHistory_vue_vue_type_style_index_0_id_3ac4d80f_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css */ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ConversationHistory_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ConversationHistory_vue_vue_type_template_id_3ac4d80f__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/ConversationHistory.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/Markdown.vue":
/*!*********************************************************!*\
  !*** ./resources/js/components/agent/chat/Markdown.vue ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Markdown_vue_vue_type_template_id_762d8461__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Markdown.vue?vue&type=template&id=762d8461 */ "./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461");
/* harmony import */ var _Markdown_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Markdown.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js");
/* harmony import */ var _Markdown_vue_vue_type_style_index_0_id_762d8461_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css */ "./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Markdown_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Markdown_vue_vue_type_template_id_762d8461__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/Markdown.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/chat/ReportPanel.vue":
/*!************************************************************!*\
  !*** ./resources/js/components/agent/chat/ReportPanel.vue ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ReportPanel_vue_vue_type_template_id_2e6c052e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ReportPanel.vue?vue&type=template&id=2e6c052e */ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e");
/* harmony import */ var _ReportPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ReportPanel.vue?vue&type=script&lang=js */ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js");
/* harmony import */ var _ReportPanel_vue_vue_type_style_index_0_id_2e6c052e_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css */ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_ReportPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_ReportPanel_vue_vue_type_template_id_2e6c052e__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/chat/ReportPanel.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/ui/AgentButton.vue":
/*!**********************************************************!*\
  !*** ./resources/js/components/agent/ui/AgentButton.vue ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _AgentButton_vue_vue_type_template_id_13277cde__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AgentButton.vue?vue&type=template&id=13277cde */ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde");
/* harmony import */ var _AgentButton_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AgentButton.vue?vue&type=script&lang=js */ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js");
/* harmony import */ var _AgentButton_vue_vue_type_style_index_0_id_13277cde_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css */ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_AgentButton_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_AgentButton_vue_vue_type_template_id_13277cde__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/agent/ui/AgentButton.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js":
/*!******************************************************************************!*\
  !*** ./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActionCard.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js":
/*!*********************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentBar.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js":
/*!**********************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentInfo.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js":
/*!**********************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentPage.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js":
/*!**********************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ChatPanel.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConversationHistory.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js":
/*!*********************************************************************************!*\
  !*** ./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Markdown.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js":
/*!************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ReportPanel.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js":
/*!**********************************************************************************!*\
  !*** ./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js ***!
  \**********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentButton.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b":
/*!************************************************************************************!*\
  !*** ./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_template_id_6e001e1b__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_template_id_6e001e1b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActionCard.vue?vue&type=template&id=6e001e1b */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=template&id=6e001e1b");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280 ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_template_id_0edeb280__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_template_id_0edeb280__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentBar.vue?vue&type=template&id=0edeb280 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=template&id=0edeb280");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_template_id_e152ed5e__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_template_id_e152ed5e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentInfo.vue?vue&type=template&id=e152ed5e */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=template&id=e152ed5e");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_template_id_9014a29c__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_template_id_9014a29c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentPage.vue?vue&type=template&id=9014a29c */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=template&id=9014a29c");


/***/ }),

/***/ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_template_id_885cddec__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_template_id_885cddec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ChatPanel.vue?vue&type=template&id=885cddec */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=template&id=885cddec");


/***/ }),

/***/ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_template_id_3ac4d80f__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_template_id_3ac4d80f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConversationHistory.vue?vue&type=template&id=3ac4d80f */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=template&id=3ac4d80f");


/***/ }),

/***/ "./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461 ***!
  \***************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_template_id_762d8461__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_template_id_762d8461__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Markdown.vue?vue&type=template&id=762d8461 */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=template&id=762d8461");


/***/ }),

/***/ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e":
/*!******************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_template_id_2e6c052e__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_template_id_2e6c052e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ReportPanel.vue?vue&type=template&id=2e6c052e */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=template&id=2e6c052e");


/***/ }),

/***/ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_template_id_13277cde__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_template_id_13277cde__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentButton.vue?vue&type=template&id=13277cde */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=template&id=13277cde");


/***/ }),

/***/ "./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActionCard_vue_vue_type_style_index_0_id_6e001e1b_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ActionCard.vue?vue&type=style&index=0&id=6e001e1b&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentBar_vue_vue_type_style_index_0_id_0edeb280_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentBar.vue?vue&type=style&index=0&id=0edeb280&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css":
/*!******************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentInfo_vue_vue_type_style_index_0_id_e152ed5e_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentInfo.vue?vue&type=style&index=0&id=e152ed5e&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css":
/*!******************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentPage_vue_vue_type_style_index_0_id_9014a29c_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/AgentPage.vue?vue&type=style&index=0&id=9014a29c&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css":
/*!******************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ChatPanel_vue_vue_type_style_index_0_id_885cddec_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ChatPanel.vue?vue&type=style&index=0&id=885cddec&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css":
/*!****************************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css ***!
  \****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ConversationHistory_vue_vue_type_style_index_0_id_3ac4d80f_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ConversationHistory.vue?vue&type=style&index=0&id=3ac4d80f&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Markdown_vue_vue_type_style_index_0_id_762d8461_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/Markdown.vue?vue&type=style&index=0&id=762d8461&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css":
/*!********************************************************************************************************!*\
  !*** ./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css ***!
  \********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ReportPanel_vue_vue_type_style_index_0_id_2e6c052e_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/chat/ReportPanel.vue?vue&type=style&index=0&id=2e6c052e&lang=css");


/***/ }),

/***/ "./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css":
/*!******************************************************************************************************!*\
  !*** ./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AgentButton_vue_vue_type_style_index_0_id_13277cde_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/agent/ui/AgentButton.vue?vue&type=style&index=0&id=13277cde&lang=css");


/***/ })

}]);