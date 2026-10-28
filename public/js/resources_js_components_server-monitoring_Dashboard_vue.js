"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_server-monitoring_Dashboard_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _MetricChart_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MetricChart.vue */ "./resources/js/components/server-monitoring/MetricChart.vue");
/* harmony import */ var _MetricGauge_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MetricGauge.vue */ "./resources/js/components/server-monitoring/MetricGauge.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }



var validNumber = function validNumber(value) {
  return typeof value === "number" && Number.isFinite(value);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  components: {
    MetricChart: _MetricChart_vue__WEBPACK_IMPORTED_MODULE_0__["default"],
    MetricGauge: _MetricGauge_vue__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  data: function data() {
    return {
      snapshot: null,
      samples: [],
      accessDenied: false,
      loading: false,
      error: "",
      timer: null,
      controller: null,
      destroyed: false,
      paused: document.hidden,
      lastSuccess: null,
      roundTripMs: null,
      resourceSeries: [{
        key: "cpu",
        label: "CPU",
        color: "#7939b8"
      }, {
        key: "memory",
        label: "RAM",
        color: "#149bc0"
      }, {
        key: "disk",
        label: "Disque",
        color: "#df9c23"
      }],
      networkSeries: [{
        key: "rx",
        label: "Entrant",
        color: "#149bc0"
      }, {
        key: "tx",
        label: "Sortant",
        color: "#7939b8"
      }],
      requestSeries: [{
        key: "rps",
        label: "Requêtes",
        color: "#7939b8"
      }],
      responseSeries: [{
        key: "response",
        label: "Moyenne",
        color: "#149bc0"
      }, {
        key: "p95",
        label: "P95 approximatif",
        color: "#df9c23"
      }]
    };
  },
  computed: {
    isSuperAdmin: function isSuperAdmin() {
      var _this$$store$state$au;
      return !!((_this$$store$state$au = this.$store.state.auth.user) !== null && _this$$store$state$au !== void 0 && _this$$store$state$au.is_super_admin);
    },
    system: function system() {
      var _this$snapshot;
      return ((_this$snapshot = this.snapshot) === null || _this$snapshot === void 0 ? void 0 : _this$snapshot.system) || {};
    },
    host: function host() {
      return this.system.host || {};
    },
    hostSamples: function hostSamples() {
      var unique = new Map();
      var _iterator = _createForOfIteratorHelper(this.samples),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var sample = _step.value;
          if (sample.systemTime && new Date(sample.systemTime).getTime() >= Date.now() - 300000) {
            unique.set(sample.systemTime, _objectSpread(_objectSpread({}, sample), {}, {
              time: sample.systemTime
            }));
          }
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      return Array.from(unique.values());
    },
    hostLabel: function hostLabel() {
      return this.host.available ? 'Ressources physiques / VM' : 'Source indisponible';
    },
    cpu: function cpu() {
      return this.host.cpu || {};
    },
    memory: function memory() {
      return this.host.memory || {};
    },
    disk: function disk() {
      return this.host.disk || {};
    },
    network: function network() {
      return this.host.network || {};
    },
    docker: function docker() {
      return this.system.docker || {};
    },
    http: function http() {
      var _this$snapshot2;
      return ((_this$snapshot2 = this.snapshot) === null || _this$snapshot2 === void 0 ? void 0 : _this$snapshot2.http) || {};
    },
    redis: function redis() {
      var _this$snapshot3;
      return ((_this$snapshot3 = this.snapshot) === null || _this$snapshot3 === void 0 ? void 0 : _this$snapshot3.redis) || {};
    },
    logs: function logs() {
      var _this$snapshot4;
      return ((_this$snapshot4 = this.snapshot) === null || _this$snapshot4 === void 0 || (_this$snapshot4 = _this$snapshot4.laravel) === null || _this$snapshot4 === void 0 ? void 0 : _this$snapshot4.logs) || {};
    },
    failedJobs: function failedJobs() {
      var _this$snapshot5;
      return ((_this$snapshot5 = this.snapshot) === null || _this$snapshot5 === void 0 || (_this$snapshot5 = _this$snapshot5.laravel) === null || _this$snapshot5 === void 0 ? void 0 : _this$snapshot5.failed_jobs) || {};
    },
    alerts: function alerts() {
      var _this$snapshot6;
      return ((_this$snapshot6 = this.snapshot) === null || _this$snapshot6 === void 0 ? void 0 : _this$snapshot6.alerts) || [];
    },
    loads: function loads() {
      var _this = this;
      return Array.isArray(this.cpu.load) ? this.cpu.load.map(function (value) {
        return _this.number(value);
      }).join(' / ') : '—';
    },
    httpKpis: function httpKpis() {
      var http = this.http;
      return [{
        label: 'Requêtes / seconde',
        value: this.withUnit(http.requests_per_second, 'req/s'),
        note: 'Moyenne sur la fenêtre'
      }, {
        label: 'Temps de réponse',
        value: this.withUnit(http.average_response_ms, 'ms'),
        note: 'Traitement Laravel moyen'
      }, {
        label: http.p95_approximate ? 'Latence P95 approximative' : 'Latence P95',
        value: http.p95_overflow ? '> 30 000 ms' : this.withUnit(http.p95_response_ms, 'ms'),
        note: http.p95_approximate ? 'Borne haute estimée par histogramme' : '95 % des réponses sont plus rapides'
      }, {
        label: 'Erreurs 4xx',
        value: this.number(http.errors_4xx, 0),
        note: 'Sur la fenêtre',
        danger: http.errors_4xx > 0
      }, {
        label: 'Erreurs 5xx',
        value: this.number(http.errors_5xx, 0),
        note: 'Sur la fenêtre',
        danger: http.errors_5xx > 0
      }];
    }
  },
  watch: {
    isSuperAdmin: function isSuperAdmin(allowed) {
      if (!allowed) {
        this.stop();
        this.snapshot = null;
        this.samples = [];
      }
    }
  },
  mounted: function mounted() {
    document.addEventListener('visibilitychange', this.visibilityChanged);
    if (!document.hidden && this.isSuperAdmin) this.refresh();
  },
  beforeUnmount: function beforeUnmount() {
    this.destroyed = true;
    this.stop();
    document.removeEventListener('visibilitychange', this.visibilityChanged);
  },
  methods: {
    stop: function stop() {
      clearTimeout(this.timer);
      this.timer = null;
      if (this.controller) this.controller.abort();
    },
    visibilityChanged: function visibilityChanged() {
      this.paused = document.hidden;
      if (document.hidden) this.stop();else if (this.isSuperAdmin && !this.loading) this.refresh();
    },
    refresh: function refresh() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var started, _yield$axios$get, data, sample, cutoff, _error$response;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (!(_this2.loading || _this2.destroyed || _this2.accessDenied || !_this2.isSuperAdmin)) {
                _context.next = 2;
                break;
              }
              return _context.abrupt("return");
            case 2:
              clearTimeout(_this2.timer);
              _this2.loading = true;
              _this2.controller = new AbortController();
              started = performance.now();
              _context.prev = 6;
              _context.next = 9;
              return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('/api/server-monitoring', {
                signal: _this2.controller.signal,
                timeout: 10000
              });
            case 9:
              _yield$axios$get = _context.sent;
              data = _yield$axios$get.data;
              if (!(_this2.destroyed || _this2.controller.signal.aborted || !_this2.isSuperAdmin)) {
                _context.next = 13;
                break;
              }
              return _context.abrupt("return");
            case 13:
              _this2.roundTripMs = performance.now() - started;
              _this2.snapshot = data;
              _this2.lastSuccess = new Date().toISOString();
              _this2.error = '';
              sample = {
                time: data.collected_at || _this2.lastSuccess,
                systemTime: _this2.host.available && _this2.system.collected_at ? _this2.system.collected_at : data.collected_at || _this2.lastSuccess,
                cpu: _this2.host.available ? _this2.cpu.usage_percent : null,
                memory: _this2.host.available ? _this2.memory.usage_percent : null,
                disk: _this2.host.available ? _this2.disk.usage_percent : null,
                rx: _this2.host.available && validNumber(_this2.network.rx_bytes_per_second) ? _this2.network.rx_bytes_per_second * 8 / 1000000 : null,
                tx: _this2.host.available && validNumber(_this2.network.tx_bytes_per_second) ? _this2.network.tx_bytes_per_second * 8 / 1000000 : null,
                rps: _this2.http.available ? _this2.http.requests_per_second : null,
                response: _this2.http.available ? _this2.http.average_response_ms : null,
                p95: _this2.http.available ? _this2.http.p95_response_ms : null
              };
              cutoff = Date.now() - 300000;
              _this2.samples = [].concat(_toConsumableArray(_this2.samples.filter(function (point) {
                return new Date(point.time).getTime() >= cutoff;
              })), [sample]).slice(-61);
              _context.next = 33;
              break;
            case 22:
              _context.prev = 22;
              _context.t0 = _context["catch"](6);
              if (!(axios__WEBPACK_IMPORTED_MODULE_2__["default"].isCancel(_context.t0) || _this2.destroyed)) {
                _context.next = 26;
                break;
              }
              return _context.abrupt("return");
            case 26:
              if (![401, 403].includes((_error$response = _context.t0.response) === null || _error$response === void 0 ? void 0 : _error$response.status)) {
                _context.next = 32;
                break;
              }
              _this2.accessDenied = true;
              _this2.snapshot = null;
              _this2.samples = [];
              _this2.$router.replace({
                name: 'dashboard'
              });
              return _context.abrupt("return");
            case 32:
              _this2.error = 'Impossible d’actualiser le monitoring. Une nouvelle tentative sera effectuée automatiquement.';
            case 33:
              _context.prev = 33;
              _this2.loading = false;
              _this2.controller = null;
              if (!_this2.destroyed && !_this2.accessDenied && !document.hidden && _this2.isSuperAdmin) {
                _this2.timer = setTimeout(_this2.refresh, Math.max(0, 10000 - (performance.now() - started)));
              }
              return _context.finish(33);
            case 38:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[6, 22, 33, 38]]);
      }))();
    },
    number: function number(value) {
      var digits = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
      return validNumber(value) ? value.toLocaleString('fr-FR', {
        maximumFractionDigits: digits
      }) : '—';
    },
    withUnit: function withUnit(value, unit) {
      var digits = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
      return validNumber(value) ? "".concat(this.number(value, digits), " ").concat(unit) : '—';
    },
    bytes: function bytes(value) {
      if (!validNumber(value) || value < 0) return '—';
      if (value === 0) return '0 o';
      var units = ['o', 'Kio', 'Mio', 'Gio', 'Tio'];
      var index = Math.max(0, Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1));
      return "".concat(this.number(value / Math.pow(1024, index)), " ").concat(units[index]);
    },
    rate: function rate(value) {
      return validNumber(value) ? "".concat(this.bytes(value), "/s") : '—';
    },
    duration: function duration(value) {
      if (!validNumber(value)) return '—';
      var days = Math.floor(value / 86400);
      var hours = Math.floor(value % 86400 / 3600);
      var minutes = Math.floor(value % 3600 / 60);
      return "".concat(days ? days + ' j ' : '').concat(hours, " h ").concat(minutes, " min");
    },
    date: function date(value) {
      var full = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      if (!value) return '—';
      var parsed = new Date(value);
      return Number.isNaN(parsed.getTime()) ? String(value) : full ? parsed.toLocaleString('fr-FR') : parsed.toLocaleTimeString('fr-FR');
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");
/* harmony import */ var _utils_chart__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/utils/chart */ "./resources/js/utils/chart.js");


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    label: {
      type: String,
      required: true
    },
    samples: {
      type: Array,
      "default": function _default() {
        return [];
      }
    },
    series: {
      type: Array,
      required: true
    },
    maximum: {
      type: Number,
      "default": undefined
    },
    unit: {
      type: String,
      "default": ""
    }
  },
  data: function data() {
    return {
      chart: null,
      destroyed: false,
      error: false
    };
  },
  mounted: function mounted() {
    var _this = this;
    (0,_utils_chart__WEBPACK_IMPORTED_MODULE_1__["default"])().then(function () {
      if (!_this.destroyed) _this.render();
    })["catch"](function () {
      _this.error = true;
    });
  },
  beforeUnmount: function beforeUnmount() {
    this.destroyed = true;
    if (this.chart) this.chart.destroy();
  },
  watch: {
    samples: function samples() {
      this.render();
    }
  },
  methods: {
    render: function render() {
      var _this2 = this;
      if (!window.Chart || !this.$refs.canvas || this.destroyed) return;
      var data = {
        labels: this.samples.map(function (sample) {
          return new Date(sample.time).toLocaleTimeString("fr-FR");
        }),
        datasets: this.series.map(function (series) {
          return {
            label: series.label,
            data: _this2.samples.map(function (sample) {
              var _sample$series$key;
              return (_sample$series$key = sample[series.key]) !== null && _sample$series$key !== void 0 ? _sample$series$key : null;
            }),
            borderColor: series.color,
            backgroundColor: series.color + "14",
            pointRadius: 0,
            pointHoverRadius: 4,
            borderWidth: 2,
            tension: 0.2,
            fill: false,
            spanGaps: false
          };
        })
      };
      if (this.chart) {
        this.chart.data = data;
        this.chart.update("none");
        return;
      }
      this.chart = (0,vue__WEBPACK_IMPORTED_MODULE_0__.markRaw)(new window.Chart(this.$refs.canvas, {
        type: "line",
        data: data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          interaction: {
            mode: "index",
            intersect: false
          },
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                usePointStyle: true,
                boxWidth: 8,
                font: {
                  size: 11
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function label(ctx) {
                  return "".concat(ctx.dataset.label, " : ").concat(Number(ctx.parsed.y).toLocaleString("fr-FR", {
                    maximumFractionDigits: 2
                  }), " ").concat(_this2.unit);
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              ticks: {
                maxTicksLimit: 6,
                maxRotation: 0,
                font: {
                  size: 10
                }
              }
            },
            y: {
              beginAtZero: true,
              max: this.maximum,
              grid: {
                color: "#e9edf5"
              },
              ticks: {
                maxTicksLimit: 5,
                callback: function callback(value) {
                  return "".concat(value, " ").concat(_this2.unit);
                },
                font: {
                  size: 10
                }
              }
            }
          }
        }
      }));
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  props: {
    value: {
      "default": null
    },
    label: {
      type: String,
      required: true
    }
  },
  computed: {
    available: function available() {
      return typeof this.value === "number" && Number.isFinite(this.value);
    },
    percent: function percent() {
      return this.available ? Math.max(0, Math.min(100, this.value)) : 0;
    },
    color: function color() {
      return !this.available ? "#d7dce8" : this.percent >= 90 ? "#dc4960" : this.percent >= 80 ? "#ecad30" : "#7939b8";
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _withScopeId = function _withScopeId(n) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.pushScopeId)("data-v-36f0ff3c"), n = n(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.popScopeId)(), n;
};
var _hoisted_1 = {
  key: 0,
  "class": "server-monitoring"
};
var _hoisted_2 = {
  "class": "monitoring-heading"
};
var _hoisted_3 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "eyebrow"
  }, "SUPER ADMIN · INFRASTRUCTURE"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h1", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-server",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Monitoring Serveur")]), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", null, "État du serveur hôte, des conteneurs et de l’application.")], -1 /* HOISTED */);
});
var _hoisted_4 = {
  "class": "refresh-status"
};
var _hoisted_5 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "status-dot"
  }, null, -1 /* HOISTED */);
});
var _hoisted_6 = {
  key: 0,
  "class": "muted"
};
var _hoisted_7 = ["disabled"];
var _hoisted_8 = {
  key: 0,
  "class": "notice notice-danger",
  role: "alert"
};
var _hoisted_9 = {
  key: 0
};
var _hoisted_10 = {
  key: 1,
  "class": "empty-state"
};
var _hoisted_11 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-circle-o-notch fa-spin",
    "aria-hidden": "true"
  }, null, -1 /* HOISTED */);
});
var _hoisted_12 = {
  key: 0,
  "class": "alerts",
  "aria-label": "Alertes de monitoring",
  "aria-live": "polite"
};
var _hoisted_13 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-exclamation-triangle",
    "aria-hidden": "true"
  }, null, -1 /* HOISTED */);
});
var _hoisted_14 = {
  key: 1,
  "class": "healthy-message"
};
var _hoisted_15 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-check-circle",
    "aria-hidden": "true"
  }, null, -1 /* HOISTED */);
});
var _hoisted_16 = {
  "class": "section-heading"
};
var _hoisted_17 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h2", null, "Serveur hôte", -1 /* HOISTED */);
});
var _hoisted_18 = {
  "class": "muted"
};
var _hoisted_19 = {
  key: 2,
  "class": "notice notice-warning"
};
var _hoisted_20 = {
  "class": "resource-grid",
  "aria-label": "Ressources du serveur hôte"
};
var _hoisted_21 = {
  "class": "monitoring-card resource-card"
};
var _hoisted_22 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-microchip",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" CPU")]), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "scope-label"
  }, "HÔTE")], -1 /* HOISTED */);
});
var _hoisted_23 = {
  "class": "resource-content"
};
var _hoisted_24 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Cœurs logiques", -1 /* HOISTED */);
});
var _hoisted_25 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Charge 1 / 5 / 15 min", -1 /* HOISTED */);
});
var _hoisted_26 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Uptime", -1 /* HOISTED */);
});
var _hoisted_27 = {
  "class": "monitoring-card resource-card"
};
var _hoisted_28 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-memory",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" RAM")]), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "scope-label"
  }, "HÔTE")], -1 /* HOISTED */);
});
var _hoisted_29 = {
  "class": "resource-content"
};
var _hoisted_30 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Totale", -1 /* HOISTED */);
});
var _hoisted_31 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Utilisée", -1 /* HOISTED */);
});
var _hoisted_32 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Disponible", -1 /* HOISTED */);
});
var _hoisted_33 = {
  "class": "monitoring-card resource-card"
};
var _hoisted_34 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-hdd",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Disque")]), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "scope-label"
  }, "HÔTE")], -1 /* HOISTED */);
});
var _hoisted_35 = {
  "class": "resource-content"
};
var _hoisted_36 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Total", -1 /* HOISTED */);
});
var _hoisted_37 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Utilisé", -1 /* HOISTED */);
});
var _hoisted_38 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Disponible", -1 /* HOISTED */);
});
var _hoisted_39 = {
  "class": "card-note"
};
var _hoisted_40 = {
  "class": "chart-grid",
  "aria-label": "Évolution des métriques"
};
var _hoisted_41 = {
  "class": "monitoring-card"
};
var _hoisted_42 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, "Ressources de l’hôte"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "muted"
  }, "Dernières 5 minutes ouvertes")], -1 /* HOISTED */);
});
var _hoisted_43 = {
  "class": "monitoring-card"
};
var _hoisted_44 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, "Trafic réseau de l’hôte"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "muted"
  }, "Débit mesuré")], -1 /* HOISTED */);
});
var _hoisted_45 = {
  "class": "monitoring-card network-section"
};
var _hoisted_46 = {
  "class": "card-title"
};
var _hoisted_47 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-exchange-alt",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Réseau hôte")], -1 /* HOISTED */);
});
var _hoisted_48 = {
  "class": "network-summary"
};
var _hoisted_49 = {
  key: 0,
  "class": "empty-state small"
};
var _hoisted_50 = {
  key: 1,
  "class": "table-scroll"
};
var _hoisted_51 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("thead", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tr", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Interface"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Entrant"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Sortant"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Capacité du lien")])], -1 /* HOISTED */);
});
var _hoisted_52 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "card-note"
  }, "La capacité du lien est celle déclarée par l’interface lorsqu’elle est disponible. Elle ne mesure pas le débit maximal de la connexion Internet.", -1 /* HOISTED */);
});
var _hoisted_53 = {
  "class": "section-heading"
};
var _hoisted_54 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h2", null, "Requêtes HTTP", -1 /* HOISTED */);
});
var _hoisted_55 = {
  "class": "muted"
};
var _hoisted_56 = {
  key: 3,
  "class": "notice notice-warning"
};
var _hoisted_57 = {
  "class": "kpi-grid",
  "aria-label": "Indicateurs HTTP"
};
var _hoisted_58 = {
  "class": "chart-grid"
};
var _hoisted_59 = {
  "class": "monitoring-card"
};
var _hoisted_60 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, "Requêtes par seconde")], -1 /* HOISTED */);
});
var _hoisted_61 = {
  "class": "monitoring-card"
};
var _hoisted_62 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, "Temps de réponse Laravel")], -1 /* HOISTED */);
});
var _hoisted_63 = {
  "class": "monitoring-card"
};
var _hoisted_64 = {
  "class": "card-title"
};
var _hoisted_65 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, "Endpoints les plus sollicités", -1 /* HOISTED */);
});
var _hoisted_66 = {
  "class": "muted"
};
var _hoisted_67 = {
  key: 0,
  "class": "empty-state small"
};
var _hoisted_68 = {
  key: 1,
  "class": "table-scroll"
};
var _hoisted_69 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("thead", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tr", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Méthode"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Endpoint"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Requêtes"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Réponse moyenne")])], -1 /* HOISTED */);
});
var _hoisted_70 = {
  "class": "method-pill"
};
var _hoisted_71 = {
  "class": "card-note"
};
var _hoisted_72 = {
  "class": "section-heading"
};
var _hoisted_73 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h2", null, "Conteneurs Docker", -1 /* HOISTED */);
});
var _hoisted_74 = {
  "class": "muted"
};
var _hoisted_75 = {
  "class": "monitoring-card"
};
var _hoisted_76 = {
  key: 0,
  "class": "notice notice-warning"
};
var _hoisted_77 = {
  key: 1,
  "class": "notice notice-warning"
};
var _hoisted_78 = {
  key: 2,
  "class": "notice notice-warning"
};
var _hoisted_79 = {
  key: 3,
  "class": "empty-state small"
};
var _hoisted_80 = {
  key: 4,
  "class": "table-scroll"
};
var _hoisted_81 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("thead", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tr", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Conteneur"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "État"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Statut"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "CPU"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "RAM utilisée / limite"), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "RAM %")])], -1 /* HOISTED */);
});
var _hoisted_82 = {
  "class": "container-id"
};
var _hoisted_83 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "card-note"
  }, "Les ressources des conteneurs sont distinctes de celles de l’hôte. Pour Docker, 100 % de CPU correspond à un cœur logique.", -1 /* HOISTED */);
});
var _hoisted_84 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "section-heading"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h2", null, "Services et application Laravel")], -1 /* HOISTED */);
});
var _hoisted_85 = {
  "class": "service-grid"
};
var _hoisted_86 = {
  "class": "monitoring-card"
};
var _hoisted_87 = {
  "class": "card-title"
};
var _hoisted_88 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-layer-group",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Redis")], -1 /* HOISTED */);
});
var _hoisted_89 = {
  key: 0,
  "class": "unavailable-message"
};
var _hoisted_90 = {
  "class": "service-details"
};
var _hoisted_91 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Mémoire consommée", -1 /* HOISTED */);
});
var _hoisted_92 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Limite de mémoire", -1 /* HOISTED */);
});
var _hoisted_93 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dt", null, "Latence du service", -1 /* HOISTED */);
});
var _hoisted_94 = {
  "class": "monitoring-card"
};
var _hoisted_95 = {
  "class": "card-title"
};
var _hoisted_96 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-tasks",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Jobs échoués")], -1 /* HOISTED */);
});
var _hoisted_97 = {
  "class": "failure-count"
};
var _hoisted_98 = {
  "class": "card-note"
};
var _hoisted_99 = {
  key: 0,
  "class": "unavailable-message"
};
var _hoisted_100 = {
  key: 1,
  "class": "empty-state small"
};
var _hoisted_101 = {
  key: 2,
  "class": "failure-list"
};
var _hoisted_102 = {
  "class": "muted"
};
var _hoisted_103 = {
  "class": "monitoring-card logs-section"
};
var _hoisted_104 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "card-title"
  }, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("h3", null, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": "fa fa-file-alt",
    "aria-hidden": "true"
  }), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Logs, erreurs et exceptions")]), /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": "muted"
  }, "Dernières entrées disponibles")], -1 /* HOISTED */);
});
var _hoisted_105 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "card-note",
    style: {
      "margin": "0 0 12px"
    }
  }, "Jusqu’à 50 entrées récentes des fichiers de logs actifs. Les messages et les contextes sont masqués ; les classes d’exception sont conservées.", -1 /* HOISTED */);
});
var _hoisted_106 = {
  key: 0,
  "class": "unavailable-message"
};
var _hoisted_107 = {
  key: 1,
  "class": "empty-state small"
};
var _hoisted_108 = {
  key: 2,
  "class": "log-list"
};
var _hoisted_109 = {
  "class": "log-metadata"
};
var _hoisted_110 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", {
    "class": "monitoring-footer"
  }, "Les valeurs « — » signalent une mesure indisponible ou en attente du prochain échantillon. Les graphiques conservent jusqu’à 5 minutes de mesures pendant l’ouverture de cette page.", -1 /* HOISTED */);
});
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_metric_gauge = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("metric-gauge");
  var _component_metric_chart = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("metric-chart");
  return $options.isSuperAdmin ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("main", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [_hoisted_3, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["status-pill", _ctx.error ? 'status-danger' : _ctx.paused ? 'status-neutral' : 'status-success'])
  }, [_hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.error ? 'Connexion interrompue' : _ctx.paused ? 'Actualisation en pause' : _ctx.loading && !_ctx.snapshot ? 'Connexion…' : 'Actualisation · 10 s'), 1 /* TEXT */)], 2 /* CLASS */), _ctx.lastSuccess ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_6, "Reçu à " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.date(_ctx.lastSuccess)), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    type: "button",
    "class": "monitoring-button",
    disabled: _ctx.loading,
    onClick: _cache[0] || (_cache[0] = function () {
      return $options.refresh && $options.refresh.apply($options, arguments);
    })
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("i", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["fa fa-refresh", {
      'fa-spin': _ctx.loading
    }]),
    "aria-hidden": "true"
  }, null, 2 /* CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Actualiser")], 8 /* PROPS */, _hoisted_7)])]), _ctx.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_8, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.error) + " ", 1 /* TEXT */), _ctx.snapshot ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_9, "Les dernières valeurs reçues sont conservées ; elles peuvent être obsolètes.")) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), !_ctx.snapshot && _ctx.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_10, [_hoisted_11, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Chargement des métriques réelles…")])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), _ctx.snapshot ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 2
  }, [$options.alerts.length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("section", _hoisted_12, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.alerts, function (alert) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: alert.key,
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["notice", ['critical', 'danger', 'error'].includes(alert.level) ? 'notice-danger' : 'notice-warning'])
    }, [_hoisted_13, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(alert.message), 1 /* TEXT */)], 2 /* CLASS */);
  }), 128 /* KEYED_FRAGMENT */))])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_14, [_hoisted_15, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Aucune alerte active sur les sources disponibles.")])), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_16, [_hoisted_17, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_18, "Collecté à " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.date($options.system.collected_at)) + " · " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.hostLabel), 1 /* TEXT */)]), !$options.host.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_19, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.host.reason || 'Les métriques du serveur hôte sont indisponibles.'), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_20, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_21, [_hoisted_22, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_23, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_gauge, {
    value: $options.cpu.usage_percent,
    label: "Utilisation"
  }, null, 8 /* PROPS */, ["value"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dl", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_24, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.number($options.cpu.cores, 0)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_25, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.loads), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_26, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.duration($options.cpu.uptime_seconds)), 1 /* TEXT */)])])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_27, [_hoisted_28, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_29, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_gauge, {
    value: $options.memory.usage_percent,
    label: "Utilisée"
  }, null, 8 /* PROPS */, ["value"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dl", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_30, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.memory.total_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_31, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.memory.used_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_32, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.memory.available_bytes)), 1 /* TEXT */)])])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_33, [_hoisted_34, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_35, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_gauge, {
    value: $options.disk.usage_percent,
    label: "Utilisé"
  }, null, 8 /* PROPS */, ["value"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dl", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_36, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.disk.total_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_37, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.disk.used_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_38, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.disk.available_bytes)), 1 /* TEXT */)])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", _hoisted_39, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.disk.path ? "Volume mesur\xE9 : ".concat($options.disk.path) : 'Système de fichiers racine de l’hôte.'), 1 /* TEXT */)])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_40, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_41, [_hoisted_42, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_chart, {
    label: "Évolution CPU, RAM et disque du serveur hôte",
    samples: $options.hostSamples,
    series: _ctx.resourceSeries,
    maximum: 100,
    unit: "%"
  }, null, 8 /* PROPS */, ["samples", "series"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_43, [_hoisted_44, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_chart, {
    label: "Débits réseau entrant et sortant du serveur hôte",
    samples: $options.hostSamples,
    series: _ctx.networkSeries,
    unit: "Mbit/s"
  }, null, 8 /* PROPS */, ["samples", "series"])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_45, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_46, [_hoisted_47, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_48, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)("Entrant "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.rate($options.network.rx_bytes_per_second)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)("Sortant "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.rate($options.network.tx_bytes_per_second)), 1 /* TEXT */)])])]), !($options.network.interfaces || []).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_49, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.network.reason || 'Interfaces réseau indisponibles.'), 1 /* TEXT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_50, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("table", null, [_hoisted_51, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.network.interfaces, function (iface) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
      key: iface.name
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("code", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(iface.name), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.rate(iface.rx_bytes_per_second)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.rate(iface.tx_bytes_per_second)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit(iface.speed_mbps, 'Mbit/s')), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))])])])), _hoisted_52]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_53, [_hoisted_54, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_55, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.http.window_seconds ? "Fen\xEAtre de ".concat($options.http.window_seconds, " secondes") : 'Collecte applicative'), 1 /* TEXT */)]), !$options.http.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_56, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.http.reason || 'Les métriques HTTP sont indisponibles.'), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_57, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.httpKpis, function (kpi) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("article", {
      key: kpi.label,
      "class": "monitoring-card kpi-card"
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(kpi.label), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(kpi.danger ? 'text-danger' : '')
    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(kpi.value), 3 /* TEXT, CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(kpi.note), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_58, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_59, [_hoisted_60, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_chart, {
    label: "Évolution du nombre de requêtes HTTP par seconde",
    samples: _ctx.samples,
    series: _ctx.requestSeries,
    unit: "req/s"
  }, null, 8 /* PROPS */, ["samples", "series"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_61, [_hoisted_62, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_metric_chart, {
    label: "Évolution du temps de réponse Laravel moyen et percentile 95",
    samples: _ctx.samples,
    series: _ctx.responseSeries,
    unit: "ms"
  }, null, 8 /* PROPS */, ["samples", "series"])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_63, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_64, [_hoisted_65, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_66, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit($options.http.count, 'requêtes', 0)) + " sur la fenêtre", 1 /* TEXT */)]), !($options.http.endpoints || []).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_67, "Aucune requête observée sur cette fenêtre.")) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_68, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("table", null, [_hoisted_69, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.http.endpoints, function (endpoint) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
      key: endpoint.method + endpoint.path
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_70, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(endpoint.method), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("code", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(endpoint.path), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.number(endpoint.count, 0)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit(endpoint.average_response_ms, 'ms')), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))])])])), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", _hoisted_71, "Requêtes traitées par Laravel, hors monitoring ; les fichiers statiques et les rejets en amont ne sont pas comptés. Collecte locale par tranches de 5 secondes, certaines mesures peuvent être omises en cas de forte contention. Latence aller-retour de cette API depuis votre navigateur : " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit(_ctx.roundTripMs, 'ms')) + ".", 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_72, [_hoisted_73, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_74, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.docker.available ? "".concat(($options.docker.containers || []).length, " conteneurs") : 'Collecteur Docker'), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_75, [!$options.docker.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_76, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.docker.reason || 'Le service Docker est inaccessible au collecteur.'), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.docker.available && $options.docker.reason ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_77, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.docker.reason), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.docker.truncated ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_78, "Liste limitée à " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(($options.docker.containers || []).length) + " conteneurs sur " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.docker.total_containers) + ".", 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $options.docker.available && !($options.docker.containers || []).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_79, "Aucun conteneur observé.")) : $options.docker.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_80, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("table", null, [_hoisted_81, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.docker.containers, function (container) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
      key: container.id
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(container.name), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", _hoisted_82, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)((container.id || '').slice(0, 12)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["status-pill", container.state === 'running' ? 'status-success' : container.state === 'exited' ? 'status-neutral' : 'status-danger'])
    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(container.state), 3 /* TEXT, CLASS */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(container.status), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit(container.cpu_percent, '%')), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes(container.memory_bytes)) + " / " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes(container.memory_limit_bytes)), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit(container.memory_percent, '%')), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))])])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), _hoisted_83]), _hoisted_84, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_85, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_86, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_87, [_hoisted_88, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["status-pill", $options.redis.available ? 'status-success' : 'status-danger'])
  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.redis.available ? 'Disponible' : 'Indisponible'), 3 /* TEXT, CLASS */)]), !$options.redis.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_89, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.redis.reason || 'Connexion au service impossible.'), 1 /* TEXT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dl", _hoisted_90, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_91, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.bytes($options.redis.used_memory_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_92, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.redis.maxmemory_bytes === 0 ? 'Sans limite configurée' : $options.bytes($options.redis.maxmemory_bytes)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [_hoisted_93, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("dd", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.withUnit($options.redis.latency_ms, 'ms')), 1 /* TEXT */)])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("article", _hoisted_94, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_95, [_hoisted_96, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", _hoisted_97, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.failedJobs.available ? $options.number($options.failedJobs.count, 0) : '—'), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("p", _hoisted_98, "Échecs observés sur les dernières 24 heures. " + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.failedJobs.since ? "Suivi actif depuis le ".concat($options.date($options.failedJobs.since, true), " ; l\u2019historique ant\xE9rieur n\u2019est pas inclus.") : ''), 1 /* TEXT */), !$options.failedJobs.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_99, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.failedJobs.reason || 'Suivi des jobs indisponible.'), 1 /* TEXT */)) : !($options.failedJobs.entries || []).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_100, "Aucun job échoué observé.")) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("ul", _hoisted_101, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.failedJobs.entries, function (job, index) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("li", {
      key: index
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(job.queue || 'File inconnue'), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", _hoisted_102, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.date(job.timestamp, true)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("small", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(job.connection), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("pre", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(job.exception), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))]))])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("section", _hoisted_103, [_hoisted_104, _hoisted_105, !$options.logs.available ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_106, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.logs.reason || 'Les logs Laravel sont indisponibles.'), 1 /* TEXT */)) : !($options.logs.entries || []).length ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_107, "Aucune entrée récente.")) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_108, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.logs.entries, function (entry, index) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
      key: index,
      "class": "log-entry"
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_109, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
      "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["log-level", ['error', 'critical', 'alert', 'emergency'].includes((entry.level || '').toLowerCase()) ? 'text-danger' : ''])
    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(entry.level), 3 /* TEXT, CLASS */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("time", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.date(entry.timestamp, true)), 1 /* TEXT */)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("pre", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(entry.message), 1 /* TEXT */)]);
  }), 128 /* KEYED_FRAGMENT */))]))]), _hoisted_110], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _withScopeId = function _withScopeId(n) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.pushScopeId)("data-v-2f6903b6"), n = n(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.popScopeId)(), n;
};
var _hoisted_1 = {
  "class": "monitoring-chart"
};
var _hoisted_2 = ["aria-label"];
var _hoisted_3 = {
  key: 0,
  "class": "chart-error"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("canvas", {
    ref: "canvas",
    role: "img",
    "aria-label": $props.label
  }, null, 8 /* PROPS */, _hoisted_2), _ctx.error ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("p", _hoisted_3, "Graphique indisponible. Les valeurs restent consultables dans les cartes.")) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
}

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _withScopeId = function _withScopeId(n) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.pushScopeId)("data-v-9093115e"), n = n(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.popScopeId)(), n;
};
var _hoisted_1 = ["aria-label", "aria-valuenow", "aria-valuetext"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    "class": "metric-gauge",
    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)({
      '--gauge-value': $options.percent + '%',
      '--gauge-color': $options.color
    }),
    role: "meter",
    "aria-label": $props.label,
    "aria-valuemin": "0",
    "aria-valuemax": "100",
    "aria-valuenow": $options.available ? $options.percent : undefined,
    "aria-valuetext": $options.available ? $options.percent + ' %' : 'Indisponible'
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("strong", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.available ? $options.percent.toLocaleString('fr-FR', {
    maximumFractionDigits: 1
  }) + ' %' : '—'), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($props.label), 1 /* TEXT */)])], 12 /* STYLE, PROPS */, _hoisted_1);
}

/***/ }),

/***/ "./resources/js/utils/chart.js":
/*!*************************************!*\
  !*** ./resources/js/utils/chart.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* export default binding */ __WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _script_loader__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./script-loader */ "./resources/js/utils/script-loader.js");

/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__() {
  return (0,_script_loader__WEBPACK_IMPORTED_MODULE_0__["default"])("chart", "https://unpkg.com/chart.js@4.4.0/dist/chart.umd.js");
}

/***/ }),

/***/ "./resources/js/utils/script-loader.js":
/*!*********************************************!*\
  !*** ./resources/js/utils/script-loader.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* export default binding */ __WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
window.JsScriptLoader = {};
/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__(name, url) {
  if (!JsScriptLoader[name]) {
    JsScriptLoader[name] = {
      initialized: false,
      promiseResolve: null,
      promiseReject: null
    };
    JsScriptLoader[name].promise = new Promise(function (resolve, reject) {
      JsScriptLoader[name].promiseResolve = resolve;
      JsScriptLoader[name].promiseReject = reject;
    });
  }
  var loader = JsScriptLoader[name];

  // If js already is initialized
  // the `promise` should get resolved
  // eventually.
  if (loader.initialized) {
    return loader.promise;
  }
  loader.initialized = true;

  // We inject a new script tag into
  // the `<head>` of our HTML to load
  // the Google Maps script.
  var script = document.createElement("script");
  script.async = true;
  script.defer = true;
  script.src = url;
  script.onload = loader.promiseResolve;
  script.onerror = loader.promiseReject;
  document.querySelector("head").appendChild(script);
  return loader.promise;
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
___CSS_LOADER_EXPORT___.push([module.id, "\n.server-monitoring[data-v-36f0ff3c] { height: 100%; overflow: auto; background: #f4f6fb; padding: 26px clamp(14px, 3vw, 42px) 30px; color: #26334e;\n}\n.monitoring-heading[data-v-36f0ff3c] { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 23px;\n}\n.eyebrow[data-v-36f0ff3c] { font-size: 10px; font-weight: 700; letter-spacing: 1.5px; color: #7939b8; margin-bottom: 7px;\n}\nh1[data-v-36f0ff3c] { font-size: 25px; font-weight: 750; margin: 0 0 8px; color: #26334e;\n}\nh1 i[data-v-36f0ff3c] { font-size: 22px; color: #7939b8; margin-right: 6px;\n}\n.monitoring-heading p[data-v-36f0ff3c] { margin: 0; color: #778298; font-size: 13px;\n}\n.refresh-status[data-v-36f0ff3c] { display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; gap: 10px;\n}\n.monitoring-button[data-v-36f0ff3c] { color: #7939b8; border: 1px solid #ded3ef; background: white; border-radius: 8px; padding: 8px 12px; font-size: 12px; cursor: pointer;\n}\n.monitoring-button[data-v-36f0ff3c]:disabled { opacity: .6; cursor: wait;\n}\n.monitoring-button i[data-v-36f0ff3c] { margin-right: 5px;\n}\n.status-pill[data-v-36f0ff3c] { display: inline-flex; align-items: center; white-space: nowrap; font-size: 11px; font-weight: 650; gap: 6px; padding: 5px 9px; border-radius: 20px;\n}\n.status-success[data-v-36f0ff3c] { color: #16795a; background: #e6f6ee;\n}\n.status-danger[data-v-36f0ff3c] { color: #b12e4a; background: #fde9ed;\n}\n.status-neutral[data-v-36f0ff3c] { color: #6c778c; background: #edf0f6;\n}\n.status-dot[data-v-36f0ff3c] { background: currentColor; width: 6px; height: 6px; border-radius: 50%;\n}\n.muted[data-v-36f0ff3c] { color: #7c879b; font-size: 11px; font-weight: 400;\n}\n.section-heading[data-v-36f0ff3c] { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin: 25px 0 12px;\n}\n.section-heading h2[data-v-36f0ff3c] { margin: 0; font-size: 17px; font-weight: 700;\n}\n.monitoring-card[data-v-36f0ff3c] { background: white; border-radius: 13px; padding: 19px; border: 1px solid #e7ebf3; box-shadow: 0 3px 10px #26334e04; min-width: 0;\n}\n.card-title[data-v-36f0ff3c] { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 18px;\n}\n.card-title h3[data-v-36f0ff3c] { font-size: 13px; font-weight: 700; margin: 0;\n}\n.card-title h3 i[data-v-36f0ff3c] { color: #8b70a8; margin-right: 6px;\n}\n.scope-label[data-v-36f0ff3c] { color: #9b8bac; font-size: 9px; font-weight: 700; letter-spacing: 1px;\n}\n.resource-grid[data-v-36f0ff3c] { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px;\n}\n.resource-content[data-v-36f0ff3c] { display: flex; align-items: center; gap: 20px;\n}\ndl[data-v-36f0ff3c] { margin: 0; flex: 1; min-width: 0;\n}\ndl > div[data-v-36f0ff3c] { display: flex; flex-wrap: wrap; align-items: baseline; gap: 3px 8px; justify-content: space-between; padding: 5px 0;\n}\ndt[data-v-36f0ff3c] { font-weight: 400; color: #7c879b; font-size: 11px;\n}\ndd[data-v-36f0ff3c] { margin: 0; color: #34435d; font-size: 12px; font-weight: 650;\n}\n.card-note[data-v-36f0ff3c] { color: #8a93a5; font-size: 11px; line-height: 1.6; margin: 14px 0 0;\n}\n.chart-grid[data-v-36f0ff3c] { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; margin: 15px 0;\n}\n.chart-grid .card-title[data-v-36f0ff3c] { margin-bottom: 8px;\n}\n.notice[data-v-36f0ff3c] { border-radius: 9px; padding: 11px 14px; font-size: 12px; line-height: 1.6; margin-bottom: 9px;\n}\n.notice i[data-v-36f0ff3c] { margin-right: 5px;\n}\n.notice-danger[data-v-36f0ff3c] { background: #fde9ed; color: #a3334c; border: 1px solid #f2d2da;\n}\n.notice-warning[data-v-36f0ff3c] { background: #fff6df; color: #917022; border: 1px solid #f2e5c3;\n}\n.healthy-message[data-v-36f0ff3c] { font-size: 12px; color: #40846e; margin-bottom: 10px;\n}\n.healthy-message i[data-v-36f0ff3c] { margin-right: 5px;\n}\n.empty-state[data-v-36f0ff3c] { text-align: center; padding: 50px 15px; font-size: 13px; color: #8a93a5;\n}\n.empty-state i[data-v-36f0ff3c] { margin-right: 8px;\n}\n.empty-state.small[data-v-36f0ff3c] { padding: 22px 12px; font-size: 12px;\n}\n.network-summary[data-v-36f0ff3c] { display: flex; flex-wrap: wrap; gap: 18px; font-size: 11px; color: #7c879b;\n}\n.network-summary strong[data-v-36f0ff3c] { margin-left: 5px; color: #34435d;\n}\n.table-scroll[data-v-36f0ff3c] { overflow-x: auto;\n}\ntable[data-v-36f0ff3c] { width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;\n}\nth[data-v-36f0ff3c] { font-size: 10px; color: #8a93a5; font-weight: 650; text-transform: uppercase; letter-spacing: .4px; padding: 9px 12px; background: #f8f9fc; white-space: nowrap;\n}\ntd[data-v-36f0ff3c] { padding: 12px; border-bottom: 1px solid #eff1f6; white-space: nowrap;\n}\ntr:last-child td[data-v-36f0ff3c] { border-bottom: 0;\n}\ncode[data-v-36f0ff3c] { font-size: 11px; color: #6e5090;\n}\n.container-id[data-v-36f0ff3c] { display: block; color: #98a0b1; margin-top: 4px; font-size: 10px;\n}\n.method-pill[data-v-36f0ff3c] { font-size: 10px; background: #f1ebf8; color: #7939b8; padding: 4px 6px; border-radius: 4px;\n}\n.kpi-grid[data-v-36f0ff3c] { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 13px;\n}\n.kpi-card[data-v-36f0ff3c] { display: flex; flex-direction: column; gap: 8px;\n}\n.kpi-card > span[data-v-36f0ff3c] { font-size: 11px; color: #7c879b;\n}\n.kpi-card > strong[data-v-36f0ff3c] { font-size: 25px; color: #34435d; line-height: 1.2;\n}\n.kpi-card > small[data-v-36f0ff3c] { font-size: 10px; color: #9ba2b1;\n}\n.text-danger[data-v-36f0ff3c] { color: #c33b54 !important;\n}\n.service-grid[data-v-36f0ff3c] { display: grid; grid-template-columns: minmax(240px, .8fr) minmax(0, 1.2fr); gap: 15px; margin-bottom: 15px;\n}\n.service-details > div[data-v-36f0ff3c] { border-bottom: 1px solid #eff1f6; padding: 12px 0;\n}\n.service-details > div[data-v-36f0ff3c]:last-child { border: 0;\n}\n.failure-count[data-v-36f0ff3c] { color: #7939b8; font-size: 25px;\n}\n.unavailable-message[data-v-36f0ff3c] { color: #a17a32; font-size: 12px; line-height: 1.6;\n}\n.failure-list[data-v-36f0ff3c] { list-style: none; padding: 0; margin: 12px 0 0; max-height: 230px; overflow: auto;\n}\n.failure-list li[data-v-36f0ff3c] { border-top: 1px solid #eff1f6; padding: 12px 0; font-size: 12px;\n}\n.failure-list li > div[data-v-36f0ff3c] { display: flex; justify-content: space-between; gap: 10px;\n}\n.failure-list small[data-v-36f0ff3c] { color: #8a93a5;\n}\npre[data-v-36f0ff3c] { white-space: pre-wrap; overflow-wrap: anywhere; font-size: 11px; line-height: 1.6; margin: 7px 0 0; font-family: monospace;\n}\n.log-list[data-v-36f0ff3c] { max-height: 380px; overflow: auto; background: #f9fafd; border-radius: 8px; padding: 0 14px;\n}\n.log-entry[data-v-36f0ff3c] { padding: 12px 0; border-bottom: 1px solid #e9edf5;\n}\n.log-entry[data-v-36f0ff3c]:last-child { border: 0;\n}\n.log-metadata[data-v-36f0ff3c] { display: flex; align-items: center; gap: 12px; font-size: 10px; color: #8a93a5;\n}\n.log-level[data-v-36f0ff3c] { font-weight: 700; text-transform: uppercase; color: #6c778c;\n}\n.log-entry pre[data-v-36f0ff3c] { color: #536079;\n}\n.monitoring-footer[data-v-36f0ff3c] { color: #969fb1; font-size: 11px; line-height: 1.7; margin: 18px 0 0;\n}\n@media (min-width: 1800px) {\n.server-monitoring[data-v-36f0ff3c] { padding-left: max(30px, calc((100vw - 1700px) / 2)); padding-right: max(30px, calc((100vw - 1700px) / 2));\n}\n}\n@media (max-width: 1200px) {\n.resource-content[data-v-36f0ff3c] { gap: 12px; flex-wrap: wrap;\n}\n.resource-content dl[data-v-36f0ff3c] { min-width: 130px;\n}\n.kpi-card > strong[data-v-36f0ff3c] { font-size: 20px;\n}\n}\n@media (max-width: 900px) {\n.monitoring-heading[data-v-36f0ff3c] { align-items: flex-start; flex-direction: column;\n}\n.refresh-status[data-v-36f0ff3c] { justify-content: flex-start;\n}\n.resource-grid[data-v-36f0ff3c] { grid-template-columns: 1fr;\n}\n.resource-content[data-v-36f0ff3c] { flex-wrap: nowrap; gap: 24px;\n}\n.resource-content dl[data-v-36f0ff3c] { flex: 1;\n}\n.chart-grid[data-v-36f0ff3c], .service-grid[data-v-36f0ff3c] { grid-template-columns: 1fr;\n}\n.kpi-grid[data-v-36f0ff3c] { grid-template-columns: repeat(3, minmax(0, 1fr));\n}\n}\n@media (max-width: 500px) {\n.server-monitoring[data-v-36f0ff3c] { padding: 18px 12px;\n}\nh1[data-v-36f0ff3c] { font-size: 22px;\n}\n.monitoring-card[data-v-36f0ff3c] { padding: 15px;\n}\n.kpi-grid[data-v-36f0ff3c] { grid-template-columns: repeat(2, minmax(0, 1fr));\n}\n.section-heading[data-v-36f0ff3c] { align-items: flex-start; flex-direction: column; gap: 5px;\n}\n.card-title[data-v-36f0ff3c] { flex-wrap: wrap;\n}\n.network-summary[data-v-36f0ff3c] { gap: 12px;\n}\n.resource-content[data-v-36f0ff3c] { gap: 16px;\n}\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
___CSS_LOADER_EXPORT___.push([module.id, "\n.monitoring-chart[data-v-2f6903b6] { height: 225px; position: relative; width: 100%;\n}\n.chart-error[data-v-2f6903b6] { color: #8b5264; font-size: 12px; padding: 20px; position: absolute; inset: 0;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
___CSS_LOADER_EXPORT___.push([module.id, "\n.metric-gauge[data-v-9093115e] { width: 108px; height: 108px; flex-shrink: 0; border-radius: 50%; padding: 8px; background: conic-gradient(var(--gauge-color) var(--gauge-value), #edf0f6 0);\n}\n.metric-gauge > div[data-v-9093115e] { background: white; height: 100%; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center;\n}\n.metric-gauge strong[data-v-9093115e] { font-size: 19px; color: #26334e;\n}\n.metric-gauge span[data-v-9093115e] { color: #788297; font-size: 11px; margin-top: 2px;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_style_index_0_id_36f0ff3c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_style_index_0_id_36f0ff3c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_style_index_0_id_36f0ff3c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_style_index_0_id_2f6903b6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_style_index_0_id_2f6903b6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_style_index_0_id_2f6903b6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_style_index_0_id_9093115e_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_style_index_0_id_9093115e_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_style_index_0_id_9093115e_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/server-monitoring/Dashboard.vue":
/*!*****************************************************************!*\
  !*** ./resources/js/components/server-monitoring/Dashboard.vue ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Dashboard_vue_vue_type_template_id_36f0ff3c_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true */ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true");
/* harmony import */ var _Dashboard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dashboard.vue?vue&type=script&lang=js */ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js");
/* harmony import */ var _Dashboard_vue_vue_type_style_index_0_id_36f0ff3c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css */ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Dashboard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Dashboard_vue_vue_type_template_id_36f0ff3c_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render],['__scopeId',"data-v-36f0ff3c"],['__file',"resources/js/components/server-monitoring/Dashboard.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricChart.vue":
/*!*******************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricChart.vue ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _MetricChart_vue_vue_type_template_id_2f6903b6_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true */ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true");
/* harmony import */ var _MetricChart_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MetricChart.vue?vue&type=script&lang=js */ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js");
/* harmony import */ var _MetricChart_vue_vue_type_style_index_0_id_2f6903b6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css */ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_MetricChart_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_MetricChart_vue_vue_type_template_id_2f6903b6_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render],['__scopeId',"data-v-2f6903b6"],['__file',"resources/js/components/server-monitoring/MetricChart.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricGauge.vue":
/*!*******************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricGauge.vue ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _MetricGauge_vue_vue_type_template_id_9093115e_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MetricGauge.vue?vue&type=template&id=9093115e&scoped=true */ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true");
/* harmony import */ var _MetricGauge_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MetricGauge.vue?vue&type=script&lang=js */ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js");
/* harmony import */ var _MetricGauge_vue_vue_type_style_index_0_id_9093115e_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css */ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_MetricGauge_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_MetricGauge_vue_vue_type_template_id_9093115e_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render],['__scopeId',"data-v-9093115e"],['__file',"resources/js/components/server-monitoring/MetricGauge.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js":
/*!*****************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Dashboard.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricChart.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js":
/*!*******************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricGauge.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true":
/*!***********************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true ***!
  \***********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_template_id_36f0ff3c_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_template_id_36f0ff3c_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=template&id=36f0ff3c&scoped=true");


/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_template_id_2f6903b6_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_template_id_2f6903b6_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=template&id=2f6903b6&scoped=true");


/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_template_id_9093115e_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_template_id_9093115e_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricGauge.vue?vue&type=template&id=9093115e&scoped=true */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=template&id=9093115e&scoped=true");


/***/ }),

/***/ "./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css":
/*!*************************************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css ***!
  \*************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Dashboard_vue_vue_type_style_index_0_id_36f0ff3c_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/Dashboard.vue?vue&type=style&index=0&id=36f0ff3c&scoped=true&lang=css");


/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css":
/*!***************************************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css ***!
  \***************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricChart_vue_vue_type_style_index_0_id_2f6903b6_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricChart.vue?vue&type=style&index=0&id=2f6903b6&scoped=true&lang=css");


/***/ }),

/***/ "./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css":
/*!***************************************************************************************************************************!*\
  !*** ./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css ***!
  \***************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MetricGauge_vue_vue_type_style_index_0_id_9093115e_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/server-monitoring/MetricGauge.vue?vue&type=style&index=0&id=9093115e&scoped=true&lang=css");


/***/ })

}]);