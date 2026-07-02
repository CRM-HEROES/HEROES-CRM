"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_auth_two-factors_Enable_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "login",
  data: function data() {
    return {
      qrcode: null,
      secret: "",
      totp: "",
      checkType: null,
      fetchingQrcode: false,
      activatingTwoFactors: false
    };
  },
  created: function created() {
    this.getQrCode();
  },
  methods: {
    getQrCode: function getQrCode() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$axios$get, data;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _this.fetchingQrcode = true;
              _context.prev = 1;
              _context.next = 4;
              return axios.get("/api/google/authenticator/enable");
            case 4:
              _yield$axios$get = _context.sent;
              data = _yield$axios$get.data;
              _this.fetchingQrcode = false;
              _this.qrcode = data.qrcode;
              _this.secret = data.secret;
              _context.next = 13;
              break;
            case 11:
              _context.prev = 11;
              _context.t0 = _context["catch"](1);
            case 13:
              _context.prev = 13;
              _this.fetchingQrcode = false;
              return _context.finish(13);
            case 16:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[1, 11, 13, 16]]);
      }))();
    },
    activateTwoFactors: function activateTwoFactors() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$axios$post, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.activatingTwoFactors = true;
              if (!(_this2.totp.length != 6)) {
                _context2.next = 3;
                break;
              }
              return _context2.abrupt("return");
            case 3:
              _context2.prev = 3;
              _context2.next = 6;
              return axios.post("/api/google/authenticator/activate", {
                totp: _this2.totp,
                check_type: _this2.checkType
              });
            case 6:
              _yield$axios$post = _context2.sent;
              data = _yield$axios$post.data;
              _this2.$router.push({
                name: "dashboard"
              });
              _context2.next = 13;
              break;
            case 11:
              _context2.prev = 11;
              _context2.t0 = _context2["catch"](3);
            case 13:
              _context2.prev = 13;
              _this2.activatingTwoFactors = false;
              return _context2.finish(13);
            case 16:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[3, 11, 13, 16]]);
      }))();
    },
    copyConfigurationKey: function copyConfigurationKey() {
      navigator.clipboard.writeText(this.secret);
      flashInfo({
        title: "Google Authenticator",
        body: this.$t("auth.google_authenticator.key_security_copied"),
        duration: 5000
      });
    }
  },
  computed: {
    checkTypeIpChanged: {
      get: function get() {
        this.checkType == "ip_changed";
      },
      set: function set(value) {
        this.checkType = value ? "ip_changed" : null;
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _hoisted_1 = {
  id: "hc-two-factors-wrapper"
};
var _hoisted_2 = {
  id: "hc-two-factors"
};
var _hoisted_3 = {
  id: "hc-two-factors-form",
  action: "javascript:void(0)",
  method: "post"
};
var _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("img", {
  id: "hc-two-factors-form-logo",
  src: "/images/logo.png"
}, null, -1 /* HOISTED */);
var _hoisted_5 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h1", {
  id: "hc-two-factors-form-title"
}, "Google Authenticator", -1 /* HOISTED */);
var _hoisted_6 = {
  id: "hc-two-factors-form-content"
};
var _hoisted_7 = {
  "class": "hc-item-main-content",
  style: {
    "white-space": "normal"
  }
};
var _hoisted_8 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("br", null, null, -1 /* HOISTED */);
var _hoisted_9 = {
  id: "hc-two-factors-form-inputs"
};
var _hoisted_10 = {
  id: "hc-two-factors-qrcode"
};
var _hoisted_11 = ["innerHTML"];
var _hoisted_12 = ["textContent"];
var _hoisted_13 = {
  style: {
    "position": "relative",
    "width": "100%",
    "height": "300px"
  }
};
var _hoisted_14 = ["src"];
var _hoisted_15 = {
  id: "hc-two-factors-secret"
};
var _hoisted_16 = ["textContent"];
var _hoisted_17 = ["href"];
var _hoisted_18 = ["textContent"];
var _hoisted_19 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
  "class": "fa fa-copy",
  style: {
    "margin-left": "12px",
    "color": "#999"
  }
}, null, -1 /* HOISTED */);
var _hoisted_20 = ["textContent"];
var _hoisted_21 = ["placeholder"];
var _hoisted_22 = ["textContent"];
var _hoisted_23 = ["disabled"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_icon = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("icon");
  var _component_item = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("item");
  var _component_loading = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("loading");
  var _component_checkbox = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("checkbox");
  var _component_flash = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("flash");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("form", _hoisted_3, [_hoisted_4, _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item, {
    tag: "label",
    style: {
      "padding": "10px",
      "gap": "0px",
      "flex-direction": "row",
      "background-color": "#fdf4e6",
      "border-radius": "10px",
      "margin-bottom": "10px",
      "color": "#8b4a15",
      "align-items": "start"
    }
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": "fa fa-lightbulb",
        style: {
          "position": "absolute",
          "right": "-17px",
          "top": "-17px",
          "width": "34px",
          "height": "34px",
          "background-color": "rgb(255, 223, 173)",
          "color": "rgb(225, 126, 0)",
          "border-radius": "50%",
          "transform": "rotate(24deg)"
        }
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)("Activer Google Authenticator sur votre compte ajoute une sécurité supplémentaire en exigeant un code de vérification unique après votre mot de passe.") + " ", 1 /* TEXT */), _hoisted_8, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)("Cela rend l'accès à votre compte beaucoup plus difficile pour les personnes non autorisées, renforçant ainsi la protection de vos données et de votre confidentialité."), 1 /* TEXT */)])];
    }),
    _: 1 /* STABLE */
  }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_9, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_10, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    style: {
      "text-align": "center",
      "margin-bottom": "10px",
      "font-weight": "bold"
    },
    innerHTML: _ctx.$t('auth.google_authenticator.open')
  }, null, 8 /* PROPS */, _hoisted_11), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    style: {
      "text-align": "center"
    },
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('auth.google_authenticator.scan_qrcode'))
  }, null, 8 /* PROPS */, _hoisted_12), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_13, [$data.qrcode ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("img", {
    key: 0,
    style: {
      "width": "100%",
      "height": "100%"
    },
    alt: "Image of QR barcode",
    src: $data.qrcode
  }, null, 8 /* PROPS */, _hoisted_14)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.fetchingQrcode
  }, null, 8 /* PROPS */, ["loading"])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_15, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    style: {
      "text-align": "center"
    },
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('auth.google_authenticator.copy_following_key_configuration'))
  }, null, 8 /* PROPS */, _hoisted_16), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a", {
    style: {
      "position": "relative",
      "width": "100%",
      "text-align": "center",
      "margin-top": "10px",
      "margin-bottom": "20px",
      "font-size": "18px",
      "letter-spacing": "2px",
      "cursor": "pointer"
    },
    href: 'otpauth://totp/dev@it.com?secret=' + $data.secret + '&issuer=HeroesCRM',
    onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.copyConfigurationKey && $options.copyConfigurationKey.apply($options, arguments);
    }, ["prevent"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.secret)
  }, null, 8 /* PROPS */, _hoisted_18), _hoisted_19], 8 /* PROPS */, _hoisted_17)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('auth.google_authenticator.key_security'))
  }, null, 8 /* PROPS */, _hoisted_20), (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return $data.totp = $event;
    }),
    name: "totp",
    id: "hc-two-factors-form-input-password",
    placeholder: _ctx.$t('auth.google_authenticator.key_security')
  }, null, 8 /* PROPS */, _hoisted_21), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.totp]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_item, {
    tag: "label",
    style: {
      "padding": "10px",
      "gap": "0px",
      "flex-direction": "row",
      "background-color": "#ede6fd",
      "border-radius": "10px",
      "margin-top": "10px",
      "color": "#793dbc"
    }
  }, {
    "default": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(function () {
      return [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_icon, {
        "class": "fa fa-question icon-blue"
      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
        "class": "hc-item-main-content",
        style: {
          "white-space": "normal"
        },
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$t('auth.google_authenticator.check_type'))
      }, null, 8 /* PROPS */, _hoisted_22), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_checkbox, {
        modelValue: $options.checkTypeIpChanged,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          return $options.checkTypeIpChanged = $event;
        })
      }, null, 8 /* PROPS */, ["modelValue"])];
    }),
    _: 1 /* STABLE */
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    id: "hc-two-factors-form-submit",
    type: "submit",
    disabled: $data.activatingTwoFactors,
    onClick: _cache[3] || (_cache[3] = function () {
      return $options.activateTwoFactors && $options.activateTwoFactors.apply($options, arguments);
    })
  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.activatingTwoFactors ? _ctx.$t("auth.google_authenticator.activating") : _ctx.$t("auth.google_authenticator.activate")), 9 /* TEXT, PROPS */, _hoisted_23)])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_flash)]);
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
___CSS_LOADER_EXPORT___.push([module.id, "\n#hc-two-factors-wrapper {\n    width: 100%;\n    height: 100%;\n    display: flex;\n    flex-direction: column;\n    align-items: center;\n    padding-top: 30px;\n    padding-bottom: 30px;\n    background-color: #f6f4f9;\n    overflow: auto;\n    background-image: url(/images/bg.jpg);\n    background-size: cover;\n    background-position: center;\n}\n#hc-two-factors {\n    max-width: 100%;\n    width: auto;\n    height: auto;\n    display: flex;\n    flex-direction: row;\n    border-radius: 10px;\n}\n#hc-two-factors-form {\n    width: 340px;\n    padding: 12px;\n    justify-content: center;\n    align-items: center;\n    display: flex;\n    flex-direction: column;\n    gap: 30px;\n    background-color: #fff8;\n}\n#hc-two-factors-form-logo {\n    width: 160px;\n}\n#hc-two-factors-form-title {\n    font-size: 22px;\n    font-weight: bold;\n}\n#hc-two-factors-form-content {\n    width: 100%;\n    display: flex;\n    flex-direction: column;\n    gap: 10px;\n}\n#hc-two-factors-form-inputs {\n    width: 100%;\n    display: flex;\n    flex-direction: column;\n}\n#hc-two-factors-form-inputs > label {\n    width: 100%;\n    margin-bottom: -1px;\n    display: flex;\n    flex-direction: column;\n}\n#hc-two-factors-form-inputs > label > input {\n    height: 40px;\n    border: 1px solid #ccc;\n    width: 100%;\n    padding: 0 10px;\n    font-size: 13px;\n    border-radius: 5px;\n}\n#hc-two-factors-form-submit {\n    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);\n    border: none;\n    border-radius: 5px;\n    color: white;\n    height: 40px;\n}\n#hc-two-factors-forgotten {\n    text-align: right;\n    color: #7939b8;\n    font-size: 14px;\n    text-decoration: underline;\n}\n#hc-two-factors-form-other {\n    display: flex;\n    flex-direction: column;\n    width: 100%;\n    gap: 3px;\n}\n#hc-two-factors-sign-in {\n}\n#hc-two-factors-sign-in > a {\n    color: #7939b8;\n    font-size: 14px;\n    text-decoration: underline;\n}\n#hc-two-factors-google {\n    border-radius: 5px;\n    border: 2px solid #7939b8;\n    background: linear-gradient(\n        135deg,\n        #f2eff5 0%,\n        rgba(255, 255, 255, 0) 100%\n    );\n    height: 40px;\n    color: #7939b8;\n    text-align: center;\n    line-height: 36px;\n    font-weight: bold;\n    font-size: 13px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_style_index_0_id_da7f420a_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_style_index_0_id_da7f420a_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_style_index_0_id_da7f420a_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/auth/two-factors/Enable.vue":
/*!*************************************************************!*\
  !*** ./resources/js/components/auth/two-factors/Enable.vue ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Enable_vue_vue_type_template_id_da7f420a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Enable.vue?vue&type=template&id=da7f420a */ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a");
/* harmony import */ var _Enable_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Enable.vue?vue&type=script&lang=js */ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js");
/* harmony import */ var _Enable_vue_vue_type_style_index_0_id_da7f420a_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css */ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Enable_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Enable_vue_vue_type_template_id_da7f420a__WEBPACK_IMPORTED_MODULE_0__.render],['__file',"resources/js/components/auth/two-factors/Enable.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Enable.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_template_id_da7f420a__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_template_id_da7f420a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Enable.vue?vue&type=template&id=da7f420a */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=template&id=da7f420a");


/***/ }),

/***/ "./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css":
/*!*********************************************************************************************************!*\
  !*** ./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Enable_vue_vue_type_style_index_0_id_da7f420a_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/style-loader/dist/cjs.js!../../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/auth/two-factors/Enable.vue?vue&type=style&index=0&id=da7f420a&lang=css");


/***/ })

}]);