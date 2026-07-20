"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_planning_Planning_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm-bundler.js");
/* harmony import */ var _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/project/attendance */ "./resources/js/apis/project/attendance.js");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }


var DAY_LABELS = ["Lun", "Mar", "Mer", "Jeu", "Ven"];
var MONTHS = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];

// Horaires habituels par défaut (fallback si rien en localStorage)
var DEFAULT_SCHEDULE = {
  expected_arrival: "08:30",
  expected_departure: "17:00",
  break_minutes: 0
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  data: function data() {
    return {
      weekStart: this.getMonday(new Date()),
      employees: [],
      attendances: [],
      loading: false,
      saving: false,
      editing: null,
      form: this.emptyForm()
    };
  },
  created: function created() {
    this.fetchEmployees();
    this.fetchAttendances();
  },
  methods: {
    // --- Date helpers ---
    getMonday: function getMonday(d) {
      var date = new Date(d);
      var offset = (date.getDay() + 6) % 7;
      date.setDate(date.getDate() - offset);
      date.setHours(0, 0, 0, 0);
      return date;
    },
    toYMD: function toYMD(date) {
      var y = date.getFullYear();
      var m = String(date.getMonth() + 1).padStart(2, "0");
      var d = String(date.getDate()).padStart(2, "0");
      return "".concat(y, "-").concat(m, "-").concat(d);
    },
    addDays: function addDays(date, n) {
      var d = new Date(date);
      d.setDate(d.getDate() + n);
      return d;
    },
    // Extrait "HH:mm" d'une valeur date/heure (insensible au fuseau)
    toTime: function toTime(value) {
      if (!value) return "";
      var m = String(value).match(/[T ](\d{2}:\d{2})/);
      if (m) return m[1];
      var m2 = String(value).match(/^(\d{2}:\d{2})/);
      return m2 ? m2[1] : "";
    },
    combine: function combine(date, time) {
      return time ? "".concat(date, " ").concat(time, ":00") : null;
    },
    emptyForm: function emptyForm() {
      return {
        status: "present",
        expected_arrival: DEFAULT_SCHEDULE.expected_arrival,
        expected_departure: DEFAULT_SCHEDULE.expected_departure,
        arrival: "",
        departure: "",
        break_minutes: DEFAULT_SCHEDULE.break_minutes,
        note: "",
        leave_from: "",
        leave_to: "",
        save_as_default: false
      };
    },
    // --- Horaires habituels par employé (localStorage) ---
    defaultsStorageKey: function defaultsStorageKey() {
      return "hc-planning-defaults-" + (this.project ? this.project.slug : "");
    },
    getEmployeeDefault: function getEmployeeDefault(employeeId) {
      try {
        var all = JSON.parse(localStorage.getItem(this.defaultsStorageKey()) || "{}");
        return _objectSpread(_objectSpread({}, DEFAULT_SCHEDULE), all[employeeId] || {});
      } catch (e) {
        return _objectSpread({}, DEFAULT_SCHEDULE);
      }
    },
    saveEmployeeDefault: function saveEmployeeDefault(employeeId, values) {
      var all = {};
      try {
        all = JSON.parse(localStorage.getItem(this.defaultsStorageKey()) || "{}");
      } catch (e) {
        all = {};
      }
      all[employeeId] = {
        expected_arrival: values.expected_arrival,
        expected_departure: values.expected_departure,
        break_minutes: values.break_minutes || 0
      };
      localStorage.setItem(this.defaultsStorageKey(), JSON.stringify(all));
    },
    // --- Data ---
    fetchEmployees: function fetchEmployees() {
      var _this = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$attendanceServ, data;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].users(_this.project.slug);
            case 2:
              _yield$attendanceServ = _context.sent;
              data = _yield$attendanceServ.data;
              _this.employees = Array.isArray(data) ? data : data.data || [];
            case 5:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    fetchAttendances: function fetchAttendances() {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var _yield$attendanceServ2, data;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _this2.loading = true;
              _context2.prev = 1;
              _context2.next = 4;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].get(_this2.project.slug, _this2.toYMD(_this2.weekStart), _this2.toYMD(_this2.addDays(_this2.weekStart, 6)));
            case 4:
              _yield$attendanceServ2 = _context2.sent;
              data = _yield$attendanceServ2.data;
              _this2.attendances = data;
            case 7:
              _context2.prev = 7;
              _this2.loading = false;
              return _context2.finish(7);
            case 10:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[1,, 7, 10]]);
      }))();
    },
    previousWeek: function previousWeek() {
      this.weekStart = this.addDays(this.weekStart, -7);
      this.fetchAttendances();
    },
    nextWeek: function nextWeek() {
      this.weekStart = this.addDays(this.weekStart, 7);
      this.fetchAttendances();
    },
    // --- Cell rendering ---
    getAttendance: function getAttendance(userId, date) {
      var _this3 = this;
      return this.attendances.find(function (a) {
        return a.user_id == userId && _this3.toYMD(new Date(a.date)) == date;
      });
    },
    cellStatus: function cellStatus(userId, date) {
      var a = this.getAttendance(userId, date);
      if (!a) return null;
      if (a.status == "leave") return {
        label: "Congé",
        cls: "conge"
      };
      if (a.status == "pause") return {
        label: "Pause",
        cls: "pause"
      };
      if (a.status == "absent") return {
        label: "Absent",
        cls: "absent"
      };
      if (a.arrival_at) {
        if (a.retard_minutes > 0) {
          return {
            label: "Retard +" + a.retard_minutes + "′",
            cls: "retard"
          };
        }
        return {
          label: "Présent",
          cls: "present"
        };
      }
      return {
        label: "—",
        cls: ""
      };
    },
    cellTimes: function cellTimes(userId, date) {
      var a = this.getAttendance(userId, date);
      if (!a || !a.arrival_at) return "";
      return this.toTime(a.arrival_at) + "–" + this.toTime(a.departure_at);
    },
    employeeTotal: function employeeTotal(userId) {
      var _this4 = this;
      var minutes = 0;
      var retards = 0;
      this.days.forEach(function (day) {
        var a = _this4.getAttendance(userId, day.date);
        if (a) {
          minutes += a.worked_minutes || 0;
          if (a.retard_minutes > 0) retards++;
        }
      });
      var h = Math.floor(minutes / 60);
      var m = minutes % 60;
      var out = h + "h" + (m > 0 ? String(m).padStart(2, "0") : "");
      if (retards > 0) out += " · " + retards + " ret.";
      return out;
    },
    // --- Editor ---
    openEditor: function openEditor(employee, date) {
      var a = this.getAttendance(employee.id, date);
      var d = new Date(date);
      this.editing = {
        id: a ? a.id : null,
        employee: employee,
        date: date,
        dateLabel: DAY_LABELS[(d.getDay() + 6) % 7] + " " + d.getDate() + " " + MONTHS[d.getMonth()]
      };
      var def = this.getEmployeeDefault(employee.id);
      if (a) {
        this.form = {
          status: a.status || "present",
          expected_arrival: this.toTime(a.expected_arrival) || def.expected_arrival,
          expected_departure: this.toTime(a.expected_departure) || def.expected_departure,
          arrival: this.toTime(a.arrival_at),
          departure: this.toTime(a.departure_at),
          break_minutes: a.break_minutes != null ? a.break_minutes : def.break_minutes,
          note: a.note || "",
          leave_from: "",
          leave_to: "",
          save_as_default: false
        };
      } else {
        // Nouveau pointage → pré-rempli avec les horaires habituels
        this.form = this.emptyForm();
        this.form.expected_arrival = def.expected_arrival;
        this.form.expected_departure = def.expected_departure;
        this.form.break_minutes = def.break_minutes;
      }

      // Congé : on pré-remplit Du/Au avec la période contiguë détectée
      // (pour pouvoir annuler/modifier tout le congé d'un coup).
      if (a && a.status == "leave") {
        var block = this.leaveBlock(employee.id, date);
        this.form.leave_from = block.from;
        this.form.leave_to = block.to;
        // Mémorise la période d'origine (pour gérer la réduction)
        this.editing.leaveOriginal = {
          from: block.from,
          to: block.to
        };
      } else {
        this.form.leave_from = date;
        this.form.leave_to = date;
        this.editing.leaveOriginal = null;
      }
    },
    /**
     * Détecte la période de congé contiguë autour d'un jour donné.
     */
    leaveBlock: function leaveBlock(userId, date) {
      var _this5 = this;
      var isLeave = function isLeave(ymd) {
        var a = _this5.getAttendance(userId, ymd);
        return a && a.status == "leave";
      };
      var from = new Date(date);
      var to = new Date(date);
      while (isLeave(this.toYMD(this.addDays(from, -1)))) {
        from = this.addDays(from, -1);
      }
      while (isLeave(this.toYMD(this.addDays(to, 1)))) {
        to = this.addDays(to, 1);
      }
      return {
        from: this.toYMD(from),
        to: this.toYMD(to)
      };
    },
    closeEditor: function closeEditor() {
      this.editing = null;
    },
    save: function save() {
      var _this6 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3() {
        var o, _yield$attendanceServ3, _data, payload, _yield$attendanceServ4, data;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              _this6.saving = true;
              _context3.prev = 1;
              if (!(_this6.form.status == "leave")) {
                _context3.next = 15;
                break;
              }
              if (!_this6.editing.leaveOriginal) {
                _context3.next = 8;
                break;
              }
              o = _this6.editing.leaveOriginal;
              _context3.next = 7;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].removeLeave(_this6.project.slug, {
                user_id: _this6.editing.employee.id,
                from: o.from,
                to: o.to
              });
            case 7:
              _this6.attendances = _this6.attendances.filter(function (att) {
                if (att.user_id != _this6.editing.employee.id || att.status != "leave") {
                  return true;
                }
                var d = _this6.toYMD(new Date(att.date));
                return d < o.from || d > o.to;
              });
            case 8:
              _context3.next = 10;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].leave(_this6.project.slug, {
                user_id: _this6.editing.employee.id,
                from: _this6.form.leave_from,
                to: _this6.form.leave_to,
                note: _this6.form.note
              });
            case 10:
              _yield$attendanceServ3 = _context3.sent;
              _data = _yield$attendanceServ3.data;
              (Array.isArray(_data) ? _data : []).forEach(function (a) {
                return _this6.upsertLocal(a);
              });
              _this6.closeEditor();
              return _context3.abrupt("return");
            case 15:
              payload = {
                user_id: _this6.editing.employee.id,
                date: _this6.editing.date,
                status: _this6.form.status,
                note: _this6.form.note
              };
              if (_this6.form.status == "present") {
                payload.expected_arrival = _this6.form.expected_arrival || null;
                payload.expected_departure = _this6.form.expected_departure || null;
                payload.arrival_at = _this6.combine(_this6.editing.date, _this6.form.arrival);
                payload.departure_at = _this6.combine(_this6.editing.date, _this6.form.departure);
                payload.break_minutes = _this6.form.break_minutes || 0;

                // Enregistre les horaires habituels de l'employé si demandé.
                // (modifier un jour précis n'écrase PAS ces valeurs par défaut)
                if (_this6.form.save_as_default) {
                  _this6.saveEmployeeDefault(_this6.editing.employee.id, {
                    expected_arrival: _this6.form.expected_arrival,
                    expected_departure: _this6.form.expected_departure,
                    break_minutes: _this6.form.break_minutes
                  });
                }
              } else {
                payload.expected_arrival = null;
                payload.expected_departure = null;
                payload.arrival_at = null;
                payload.departure_at = null;
                payload.break_minutes = 0;
              }
              _context3.next = 19;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].store(_this6.project.slug, payload);
            case 19:
              _yield$attendanceServ4 = _context3.sent;
              data = _yield$attendanceServ4.data;
              _this6.upsertLocal(data);
              _this6.closeEditor();
            case 23:
              _context3.prev = 23;
              _this6.saving = false;
              return _context3.finish(23);
            case 26:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[1,, 23, 26]]);
      }))();
    },
    remove: function remove() {
      var _this7 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4() {
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              _this7.saving = true;
              _context4.prev = 1;
              if (!(_this7.form.status == "leave")) {
                _context4.next = 8;
                break;
              }
              _context4.next = 5;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].removeLeave(_this7.project.slug, {
                user_id: _this7.editing.employee.id,
                from: _this7.form.leave_from,
                to: _this7.form.leave_to
              });
            case 5:
              _this7.attendances = _this7.attendances.filter(function (att) {
                if (att.user_id != _this7.editing.employee.id || att.status != "leave") {
                  return true;
                }
                var d = _this7.toYMD(new Date(att.date));
                return d < _this7.form.leave_from || d > _this7.form.leave_to;
              });
              _this7.closeEditor();
              return _context4.abrupt("return");
            case 8:
              _context4.next = 10;
              return _apis_project_attendance__WEBPACK_IMPORTED_MODULE_0__["default"].destroy(_this7.project.slug, _this7.editing.id);
            case 10:
              _this7.attendances = _this7.attendances.filter(function (a) {
                return a.id != _this7.editing.id;
              });
              _this7.closeEditor();
            case 12:
              _context4.prev = 12;
              _this7.saving = false;
              return _context4.finish(12);
            case 15:
            case "end":
              return _context4.stop();
          }
        }, _callee4, null, [[1,, 12, 15]]);
      }))();
    },
    upsertLocal: function upsertLocal(attendance) {
      var i = this.attendances.findIndex(function (a) {
        return a.id == attendance.id;
      });
      if (i >= 0) {
        this.attendances.splice(i, 1, attendance);
      } else {
        this.attendances.push(attendance);
      }
    }
  },
  computed: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_1__.mapGetters)(["project"])), {}, {
    // Pause saisie en h:min mais stockée/calculée en minutes
    breakTime: {
      get: function get() {
        var m = this.form.break_minutes || 0;
        var h = Math.floor(m / 60);
        var mm = m % 60;
        return String(h).padStart(2, "0") + ":" + String(mm).padStart(2, "0");
      },
      set: function set(value) {
        if (!value) {
          this.form.break_minutes = 0;
          return;
        }
        var _value$split$map = value.split(":").map(Number),
          _value$split$map2 = _slicedToArray(_value$split$map, 2),
          h = _value$split$map2[0],
          m = _value$split$map2[1];
        this.form.break_minutes = (h || 0) * 60 + (m || 0);
      }
    },
    days: function days() {
      var _this8 = this;
      return DAY_LABELS.map(function (label, i) {
        var d = _this8.addDays(_this8.weekStart, i);
        return {
          date: _this8.toYMD(d),
          label: label + " " + d.getDate()
        };
      });
    },
    weekLabel: function weekLabel() {
      var start = this.weekStart;
      var end = this.addDays(this.weekStart, 4);
      return "Semaine du " + start.getDate() + " – " + end.getDate() + " " + MONTHS[end.getMonth()];
    },
    livePreview: function livePreview() {
      if (this.form.status != "present" || !this.form.arrival) return "";
      var out = "";
      if (this.form.expected_arrival && this.form.arrival > this.form.expected_arrival) {
        var _this$form$expected_a = this.form.expected_arrival.split(":").map(Number),
          _this$form$expected_a2 = _slicedToArray(_this$form$expected_a, 2),
          eh = _this$form$expected_a2[0],
          em = _this$form$expected_a2[1];
        var _this$form$arrival$sp = this.form.arrival.split(":").map(Number),
          _this$form$arrival$sp2 = _slicedToArray(_this$form$arrival$sp, 2),
          ah = _this$form$arrival$sp2[0],
          am = _this$form$arrival$sp2[1];
        var retard = ah * 60 + am - (eh * 60 + em);
        if (retard > 0) out += "Retard : <b>" + retard + " min</b>";
      }
      return out;
    }
  })
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render)
/* harmony export */ });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm-bundler.js");

var _withScopeId = function _withScopeId(n) {
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.pushScopeId)("data-v-1e69abb4"), n = n(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.popScopeId)(), n;
};
var _hoisted_1 = {
  "class": "hc-planning"
};
var _hoisted_2 = {
  "class": "hc-planning-toolbar"
};
var _hoisted_3 = {
  "class": "hc-planning-week"
};
var _hoisted_4 = ["textContent"];
var _hoisted_5 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createStaticVNode)("<div class=\"hc-planning-legend\" data-v-1e69abb4><span data-v-1e69abb4><i class=\"dot present\" data-v-1e69abb4></i> Présent</span><span data-v-1e69abb4><i class=\"dot retard\" data-v-1e69abb4></i> Retard</span><span data-v-1e69abb4><i class=\"dot conge\" data-v-1e69abb4></i> Congé</span><span data-v-1e69abb4><i class=\"dot pause\" data-v-1e69abb4></i> Pause</span><span data-v-1e69abb4><i class=\"dot absent\" data-v-1e69abb4></i> Absent</span></div>", 1);
var _hoisted_6 = {
  "class": "hc-planning-table-wrapper"
};
var _hoisted_7 = {
  "class": "hc-planning-table"
};
var _hoisted_8 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", {
    "class": "hc-planning-emp"
  }, "Employé", -1 /* HOISTED */);
});
var _hoisted_9 = ["textContent"];
var _hoisted_10 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("th", null, "Total", -1 /* HOISTED */);
});
var _hoisted_11 = {
  "class": "hc-planning-emp"
};
var _hoisted_12 = ["textContent"];
var _hoisted_13 = ["textContent"];
var _hoisted_14 = ["onClick"];
var _hoisted_15 = ["textContent"];
var _hoisted_16 = ["textContent"];
var _hoisted_17 = {
  key: 1,
  "class": "hc-planning-empty"
};
var _hoisted_18 = ["textContent"];
var _hoisted_19 = {
  key: 0
};
var _hoisted_20 = ["colspan"];
var _hoisted_21 = {
  "class": "hc-planning-editor"
};
var _hoisted_22 = {
  "class": "hc-planning-editor-header"
};
var _hoisted_23 = ["textContent"];
var _hoisted_24 = ["textContent"];
var _hoisted_25 = {
  "class": "hc-planning-field"
};
var _hoisted_26 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Statut", -1 /* HOISTED */);
});
var _hoisted_27 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "present"
  }, "Présent", -1 /* HOISTED */);
});
var _hoisted_28 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "leave"
  }, "Congé", -1 /* HOISTED */);
});
var _hoisted_29 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "pause"
  }, "Pause", -1 /* HOISTED */);
});
var _hoisted_30 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("option", {
    value: "absent"
  }, "Absent", -1 /* HOISTED */);
});
var _hoisted_31 = [_hoisted_27, _hoisted_28, _hoisted_29, _hoisted_30];
var _hoisted_32 = {
  "class": "hc-planning-field-row"
};
var _hoisted_33 = {
  "class": "hc-planning-field"
};
var _hoisted_34 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Du", -1 /* HOISTED */);
});
var _hoisted_35 = {
  "class": "hc-planning-field"
};
var _hoisted_36 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Au", -1 /* HOISTED */);
});
var _hoisted_37 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-planning-preview"
  }, " Le congé sera appliqué à tous les jours de la période. ", -1 /* HOISTED */);
});
var _hoisted_38 = {
  "class": "hc-planning-field-row"
};
var _hoisted_39 = {
  "class": "hc-planning-field"
};
var _hoisted_40 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Arrivée prévue", -1 /* HOISTED */);
});
var _hoisted_41 = {
  "class": "hc-planning-field"
};
var _hoisted_42 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Départ prévu", -1 /* HOISTED */);
});
var _hoisted_43 = {
  "class": "hc-planning-field-row"
};
var _hoisted_44 = {
  "class": "hc-planning-field"
};
var _hoisted_45 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Arrivée réelle", -1 /* HOISTED */);
});
var _hoisted_46 = {
  "class": "hc-planning-field"
};
var _hoisted_47 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Départ réel", -1 /* HOISTED */);
});
var _hoisted_48 = {
  "class": "hc-planning-field"
};
var _hoisted_49 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Pause (h:min)", -1 /* HOISTED */);
});
var _hoisted_50 = {
  style: {
    "display": "flex",
    "align-items": "center",
    "gap": "8px",
    "font-size": "12px",
    "color": "#555",
    "margin-bottom": "10px",
    "cursor": "pointer"
  }
};
var _hoisted_51 = ["innerHTML"];
var _hoisted_52 = {
  "class": "hc-planning-field"
};
var _hoisted_53 = /*#__PURE__*/_withScopeId(function () {
  return /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", null, "Note", -1 /* HOISTED */);
});
var _hoisted_54 = {
  "class": "hc-planning-editor-buttons"
};
var _hoisted_55 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_loading = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)("loading");
  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Toolbar "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a", {
    "class": "fa fa-caret-left hc-planning-nav",
    onClick: _cache[0] || (_cache[0] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.previousWeek && $options.previousWeek.apply($options, arguments);
    }, ["prevent"])),
    title: "Semaine précédente"
  }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.weekLabel)
  }, null, 8 /* PROPS */, _hoisted_4), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a", {
    "class": "fa fa-caret-right hc-planning-nav",
    onClick: _cache[1] || (_cache[1] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.nextWeek && $options.nextWeek.apply($options, arguments);
    }, ["prevent"])),
    title: "Semaine suivante"
  })]), _hoisted_5]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Grid "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("table", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("thead", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tr", null, [_hoisted_8, ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.days, function (day) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("th", {
      key: day.date,
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(day.label)
    }, null, 8 /* PROPS */, _hoisted_9);
  }), 128 /* KEYED_FRAGMENT */)), _hoisted_10])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("tbody", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.employees, function (employee) {
    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", {
      key: employee.id
    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", _hoisted_11, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-planning-emp-name",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(employee.name)
    }, null, 8 /* PROPS */, _hoisted_12), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
      "class": "hc-planning-emp-sub",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(employee.email)
    }, null, 8 /* PROPS */, _hoisted_13)]), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($options.days, function (day) {
      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("td", {
        key: day.date,
        "class": "hc-planning-cell",
        onClick: function onClick($event) {
          return $options.openEditor(employee, day.date);
        }
      }, [$options.cellStatus(employee.id, day.date) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
        key: 0
      }, [$options.cellTimes(employee.id, day.date) ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
        key: 0,
        "class": "hc-planning-times",
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.cellTimes(employee.id, day.date))
      }, null, 8 /* PROPS */, _hoisted_15)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("span", {
        "class": (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)(["hc-planning-pill", $options.cellStatus(employee.id, day.date).cls]),
        textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.cellStatus(employee.id, day.date).label)
      }, null, 10 /* CLASS, PROPS */, _hoisted_16)], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("span", _hoisted_17, "+"))], 8 /* PROPS */, _hoisted_14);
    }), 128 /* KEYED_FRAGMENT */)), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", {
      "class": "hc-planning-total",
      textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.employeeTotal(employee.id))
    }, null, 8 /* PROPS */, _hoisted_18)]);
  }), 128 /* KEYED_FRAGMENT */)), !$data.loading && $data.employees.length == 0 ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("tr", _hoisted_19, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("td", {
    colspan: $options.days.length + 2,
    "class": "hc-planning-none"
  }, " Aucun employé dans ce projet. ", 8 /* PROPS */, _hoisted_20)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.loading
  }, null, 8 /* PROPS */, ["loading"])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(" Editor modal "), $data.editing ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 0,
    "class": "hc-planning-overlay",
    onClick: _cache[15] || (_cache[15] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.closeEditor && $options.closeEditor.apply($options, arguments);
    }, ["self"]))
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_21, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_22, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-planning-editor-title",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.editing.employee.name)
  }, null, 8 /* PROPS */, _hoisted_23), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", {
    "class": "hc-planning-emp-sub",
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.editing.dateLabel)
  }, null, 8 /* PROPS */, _hoisted_24)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("a", {
    "class": "fa fa-close hc-planning-nav",
    onClick: _cache[2] || (_cache[2] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.closeEditor && $options.closeEditor.apply($options, arguments);
    }, ["prevent"]))
  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_25, [_hoisted_26, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("select", {
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return $data.form.status = $event;
    })
  }, _hoisted_31, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelSelect, $data.form.status]])]), $data.form.status == 'leave' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 0
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_32, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_33, [_hoisted_34, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "date",
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return $data.form.leave_from = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.leave_from]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_35, [_hoisted_36, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "date",
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return $data.form.leave_to = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.leave_to]])])]), _hoisted_37], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), $data.form.status == 'present' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    key: 1
  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_38, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_39, [_hoisted_40, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "time",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return $data.form.expected_arrival = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.expected_arrival]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_41, [_hoisted_42, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "time",
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return $data.form.expected_departure = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.expected_departure]])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_43, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_44, [_hoisted_45, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "time",
    "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
      return $data.form.arrival = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.arrival]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_46, [_hoisted_47, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "time",
    "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
      return $data.form.departure = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.departure]])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_48, [_hoisted_49, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "time",
    "onUpdate:modelValue": _cache[10] || (_cache[10] = function ($event) {
      return $options.breakTime = $event;
    })
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $options.breakTime]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("label", _hoisted_50, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("input", {
    type: "checkbox",
    "onUpdate:modelValue": _cache[11] || (_cache[11] = function ($event) {
      return $data.form.save_as_default = $event;
    }),
    style: {
      "width": "auto"
    }
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelCheckbox, $data.form.save_as_default]]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(" Enregistrer ces horaires comme habituels pour cet employé ")]), $options.livePreview ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("div", {
    key: 0,
    "class": "hc-planning-preview",
    innerHTML: $options.livePreview
  }, null, 8 /* PROPS */, _hoisted_51)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)], 64 /* STABLE_FRAGMENT */)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_52, [_hoisted_53, (0,vue__WEBPACK_IMPORTED_MODULE_0__.withDirectives)((0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("textarea", {
    "onUpdate:modelValue": _cache[12] || (_cache[12] = function ($event) {
      return $data.form.note = $event;
    }),
    rows: "2",
    placeholder: "Note ..."
  }, null, 512 /* NEED_PATCH */), [[vue__WEBPACK_IMPORTED_MODULE_0__.vModelText, $data.form.note]])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("div", _hoisted_54, [$data.editing.id ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)("button", {
    key: 0,
    "class": "hc-planning-btn danger",
    onClick: _cache[13] || (_cache[13] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.remove && $options.remove.apply($options, arguments);
    }, ["prevent"])),
    textContent: (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($data.form.status == 'leave' ? 'Annuler le congé' : 'Supprimer')
  }, null, 8 /* PROPS */, _hoisted_55)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)("button", {
    "class": "hc-planning-btn primary",
    onClick: _cache[14] || (_cache[14] = (0,vue__WEBPACK_IMPORTED_MODULE_0__.withModifiers)(function () {
      return $options.save && $options.save.apply($options, arguments);
    }, ["prevent"]))
  }, " Enregistrer "), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_loading, {
    loading: $data.saving
  }, null, 8 /* PROPS */, ["loading"])])])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)("v-if", true)]);
}

/***/ }),

/***/ "./resources/js/apis/project/attendance.js":
/*!*************************************************!*\
  !*** ./resources/js/apis/project/attendance.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _apis_api_service__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/apis/api.service */ "./resources/js/apis/api.service.js");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  users: function users(project) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/attendance/users"));
  },
  get: function get(project, from, to) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].get("project/".concat(project, "/attendance"), {
      params: {
        from: from,
        to: to
      }
    });
  },
  store: function store(project, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/attendance"), params);
  },
  leave: function leave(project, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/attendance/leave"), params);
  },
  removeLeave: function removeLeave(project, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].post("project/".concat(project, "/attendance/leave/remove"), params);
  },
  update: function update(project, id, params) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"].put("project/".concat(project, "/attendance/").concat(id), params);
  },
  destroy: function destroy(project, id) {
    return _apis_api_service__WEBPACK_IMPORTED_MODULE_0__["default"]["delete"]("project/".concat(project, "/attendance/").concat(id));
  }
});

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
___CSS_LOADER_EXPORT___.push([module.id, "\n.hc-planning[data-v-1e69abb4] {\n    padding: 12px;\n    width: 100%;\n    height: 100%;\n    overflow: auto;\n}\n.hc-planning-toolbar[data-v-1e69abb4] {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    flex-wrap: wrap;\n    gap: 10px;\n    margin-bottom: 12px;\n}\n.hc-planning-week[data-v-1e69abb4] {\n    display: flex;\n    align-items: center;\n    gap: 10px;\n    font-size: 14px;\n    font-weight: 500;\n}\n.hc-planning-nav[data-v-1e69abb4] {\n    cursor: pointer;\n    color: #555;\n    padding: 4px 8px;\n    border-radius: 6px;\n    text-decoration: none;\n}\n.hc-planning-nav[data-v-1e69abb4]:hover {\n    background-color: #f0f0f0;\n}\n.hc-planning-legend[data-v-1e69abb4] {\n    display: flex;\n    gap: 14px;\n    font-size: 12px;\n    color: #666;\n}\n.hc-planning-legend .dot[data-v-1e69abb4] {\n    display: inline-block;\n    width: 9px;\n    height: 9px;\n    border-radius: 50%;\n    margin-right: 3px;\n}\n.dot.present[data-v-1e69abb4] { background-color: #2abb47;\n}\n.dot.retard[data-v-1e69abb4] { background-color: #ef9f27;\n}\n.dot.conge[data-v-1e69abb4] { background-color: #1a73e8;\n}\n.dot.pause[data-v-1e69abb4] { background-color: #9a70ff;\n}\n.dot.absent[data-v-1e69abb4] { background-color: #e24b4a;\n}\n.hc-planning-table-wrapper[data-v-1e69abb4] {\n    position: relative;\n    border: 1px solid #e6e6e6;\n    border-radius: 10px;\n    overflow: hidden;\n}\n.hc-planning-table[data-v-1e69abb4] {\n    width: 100%;\n    border-collapse: collapse;\n    table-layout: fixed;\n}\n.hc-planning-table th[data-v-1e69abb4] {\n    background-color: #f7f7f7;\n    font-size: 12px;\n    font-weight: 500;\n    color: #666;\n    padding: 8px 4px;\n    text-align: center;\n    border-bottom: 1px solid #e6e6e6;\n}\n.hc-planning-table th.hc-planning-emp[data-v-1e69abb4],\n.hc-planning-table td.hc-planning-emp[data-v-1e69abb4] {\n    width: 20%;\n    text-align: left;\n    padding-left: 10px;\n}\n.hc-planning-emp-name[data-v-1e69abb4] {\n    font-size: 13px;\n    font-weight: 500;\n    color: #222;\n}\n.hc-planning-emp-sub[data-v-1e69abb4] {\n    font-size: 11px;\n    color: #888;\n    white-space: nowrap;\n    overflow: hidden;\n    text-overflow: ellipsis;\n}\n.hc-planning-cell[data-v-1e69abb4] {\n    text-align: center;\n    padding: 7px 3px;\n    border-bottom: 1px solid #eee;\n    border-left: 1px solid #f2f2f2;\n    cursor: pointer;\n    vertical-align: top;\n}\n.hc-planning-cell[data-v-1e69abb4]:hover {\n    background-color: #f6faff;\n}\n.hc-planning-times[data-v-1e69abb4] {\n    font-family: monospace;\n    font-size: 11px;\n    color: #333;\n    margin-bottom: 3px;\n}\n.hc-planning-pill[data-v-1e69abb4] {\n    display: inline-block;\n    padding: 1px 6px;\n    border-radius: 6px;\n    font-size: 11px;\n}\n.hc-planning-pill.present[data-v-1e69abb4] { background-color: #e1f5e6; color: #1a7a33;\n}\n.hc-planning-pill.retard[data-v-1e69abb4] { background-color: #faeeda; color: #8a5a0b;\n}\n.hc-planning-pill.conge[data-v-1e69abb4] { background-color: #e6f1fb; color: #12508a;\n}\n.hc-planning-pill.pause[data-v-1e69abb4] { background-color: #f0e8ff; color: #5d3abe;\n}\n.hc-planning-pill.absent[data-v-1e69abb4] { background-color: #fcebeb; color: #a32d2d;\n}\n.hc-planning-empty[data-v-1e69abb4] {\n    color: #ccc;\n    font-size: 14px;\n}\n.hc-planning-total[data-v-1e69abb4] {\n    text-align: center;\n    font-size: 11px;\n    color: #666;\n    border-bottom: 1px solid #eee;\n    border-left: 1px solid #f2f2f2;\n    vertical-align: top;\n    padding: 8px 4px;\n}\n.hc-planning-none[data-v-1e69abb4] {\n    text-align: center;\n    color: #999;\n    padding: 20px;\n    font-size: 13px;\n}\n.hc-planning-overlay[data-v-1e69abb4] {\n    position: fixed;\n    inset: 0;\n    background-color: rgba(0, 0, 0, 0.35);\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    z-index: 2000;\n}\n.hc-planning-editor[data-v-1e69abb4] {\n    background-color: white;\n    width: 360px;\n    max-width: 92vw;\n    border-radius: 12px;\n    padding: 16px;\n    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);\n}\n.hc-planning-editor-header[data-v-1e69abb4] {\n    display: flex;\n    align-items: start;\n    justify-content: space-between;\n    margin-bottom: 12px;\n}\n.hc-planning-editor-title[data-v-1e69abb4] {\n    font-size: 15px;\n    font-weight: 500;\n}\n.hc-planning-field[data-v-1e69abb4] {\n    margin-bottom: 10px;\n    display: flex;\n    flex-direction: column;\n    gap: 4px;\n    flex: 1;\n}\n.hc-planning-field label[data-v-1e69abb4] {\n    font-size: 12px;\n    color: #666;\n}\n.hc-planning-field input[data-v-1e69abb4],\n.hc-planning-field select[data-v-1e69abb4],\n.hc-planning-field textarea[data-v-1e69abb4] {\n    border: 1px solid #ddd;\n    border-radius: 6px;\n    padding: 6px 8px;\n    font-size: 13px;\n    width: 100%;\n}\n.hc-planning-field-row[data-v-1e69abb4] {\n    display: flex;\n    gap: 10px;\n}\n.hc-planning-preview[data-v-1e69abb4] {\n    font-size: 12px;\n    color: #8a5a0b;\n    background-color: #faeeda;\n    padding: 6px 8px;\n    border-radius: 6px;\n    margin-bottom: 10px;\n}\n.hc-planning-editor-buttons[data-v-1e69abb4] {\n    display: flex;\n    justify-content: flex-end;\n    gap: 8px;\n    margin-top: 8px;\n    position: relative;\n}\n.hc-planning-btn[data-v-1e69abb4] {\n    border: none;\n    border-radius: 7px;\n    padding: 8px 14px;\n    font-size: 13px;\n    cursor: pointer;\n}\n.hc-planning-btn.primary[data-v-1e69abb4] {\n    background-color: #12a0f3;\n    color: white;\n}\n.hc-planning-btn.danger[data-v-1e69abb4] {\n    background-color: #fcebeb;\n    color: #a32d2d;\n}\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_style_index_0_id_1e69abb4_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css */ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_style_index_0_id_1e69abb4_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_style_index_0_id_1e69abb4_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./resources/js/components/planning/Planning.vue":
/*!*******************************************************!*\
  !*** ./resources/js/components/planning/Planning.vue ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Planning_vue_vue_type_template_id_1e69abb4_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Planning.vue?vue&type=template&id=1e69abb4&scoped=true */ "./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true");
/* harmony import */ var _Planning_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Planning.vue?vue&type=script&lang=js */ "./resources/js/components/planning/Planning.vue?vue&type=script&lang=js");
/* harmony import */ var _Planning_vue_vue_type_style_index_0_id_1e69abb4_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css */ "./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css");
/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/dist/exportHelper.js */ "./node_modules/vue-loader/dist/exportHelper.js");




;


const __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__["default"])(_Planning_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"], [['render',_Planning_vue_vue_type_template_id_1e69abb4_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render],['__scopeId',"data-v-1e69abb4"],['__file',"resources/js/components/planning/Planning.vue"]])
/* hot reload */
if (false) {}


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);

/***/ }),

/***/ "./resources/js/components/planning/Planning.vue?vue&type=script&lang=js":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/planning/Planning.vue?vue&type=script&lang=js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Planning.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=script&lang=js");
 

/***/ }),

/***/ "./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true":
/*!*************************************************************************************************!*\
  !*** ./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true ***!
  \*************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_template_id_1e69abb4_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_template_id_1e69abb4_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Planning.vue?vue&type=template&id=1e69abb4&scoped=true */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=template&id=1e69abb4&scoped=true");


/***/ }),

/***/ "./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css":
/*!***************************************************************************************************************!*\
  !*** ./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css ***!
  \***************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_dist_cjs_js_node_modules_css_loader_dist_cjs_js_clonedRuleSet_9_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_9_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Planning_vue_vue_type_style_index_0_id_1e69abb4_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/style-loader/dist/cjs.js!../../../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!../../../../node_modules/vue-loader/dist/stylePostLoader.js!../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css */ "./node_modules/style-loader/dist/cjs.js!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-9.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-9.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./resources/js/components/planning/Planning.vue?vue&type=style&index=0&id=1e69abb4&scoped=true&lang=css");


/***/ })

}]);