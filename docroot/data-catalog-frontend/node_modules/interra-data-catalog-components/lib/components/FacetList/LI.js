"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = _interopRequireDefault(require("styled-components"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  background: #FFFFFF;\n  padding: 0;\n  list-style-type: none;\n  width: 100%;\n  position: relative;\n  display: block;\n  &:last-of-type {\n   border-bottom: 1px solid ", ";\n }\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var LI = _styledComponents["default"].li(_templateObject(), function (props) {
  return props.theme.borderColor;
});

var _default = LI;
exports["default"] = _default;