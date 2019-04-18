"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = require("styled-components");

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  background-color: ", ";\n  border: none;\n  border-radius: 100px;\n  color: #fff;\n  cursor: pointer;\n  display: inline-block;\n  font-weight: 500;\n  font-size: 1.6rem;\n  letter-spacing: 1px;\n  margin: 8px;\n  padding: 4px 30px;\n  text-align: center;\n  text-decoration: none;\n  text-shadow: none;\n  touch-action: manipulation;\n  vertical-align: middle;\n  white-space: nowrap;\n  &:hover,\n  &:focus {\n    background-color: ", ";\n  }\n\n  &.btn-hero {\n    background-color: ", ";\n    border: none;\n    box-shadow: 0 4px 7px rgba(0, 0, 0, 0.2);\n    color: ", ";\n    text-transform: uppercase;\n    padding: 8px 30px 6px;\n    &:hover,\n    &:focus {\n      background: white;\n    }\n  }\n\n  &.close {\n    background-color: transparent;\n    color: #000;\n    padding: 10px;\n  }\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var ButtonStyles = (0, _styledComponents.css)(_templateObject(), function (props) {
  return props.theme.primary;
}, function (props) {
  return props.theme.primaryDark;
}, function (props) {
  return props.theme.secondary;
}, function (props) {
  return props.theme.primaryDark;
});
var _default = ButtonStyles;
exports["default"] = _default;