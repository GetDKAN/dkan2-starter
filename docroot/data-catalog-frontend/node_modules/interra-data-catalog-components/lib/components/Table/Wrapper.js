"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = _interopRequireDefault(require("styled-components"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  margin: 2rem 0;\n  .table-striped,\n  .table-hover {\n    > tbody tr:nth-of-type(odd):hover,\n    > tbody tr:nth-of-type(even):hover {\n      background-color: #FFFEEE;\n    }\n  }\n  table {\n    margin: 1rem 0;\n    table-layout: fixed;\n    width: 100%;\n    &.word-break {\n      td {\n        word-break: break-all;\n      }\n    }\n    tr td, tr th {\n      border: 1px solid ", ";\n      font-size: 1.4rem;\n      padding: .5rem;\n    }\n    \n    tr th {\n      background: ", ";\n      border-top-color: ", ";\n      border-bottom: 2px solid ", ";\n      color: #fff;\n      font-weight: 300;\n      &:first-child {\n        border-left-color: ", ";\n      }\n      &:last-child {\n        border-right-color: ", ";\n      }\n    }\n  }\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var Wrapper = _styledComponents["default"].div(_templateObject(), function (props) {
  return props.theme.borderColor;
}, function (props) {
  return props.theme.primaryDark;
}, function (props) {
  return props.theme.primaryDark;
}, function (props) {
  return props.theme.grayMedium;
}, function (props) {
  return props.theme.primaryDark;
}, function (props) {
  return props.theme.primaryDark;
});

var _default = Wrapper;
exports["default"] = _default;