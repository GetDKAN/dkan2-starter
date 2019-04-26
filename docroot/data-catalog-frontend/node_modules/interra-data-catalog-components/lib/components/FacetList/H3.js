"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = _interopRequireDefault(require("styled-components"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  background-color: ", ";\n  font-size: 1.6rem;\n  font-weight: 500;\n  text-transform: uppercase;\n  color: #323A45;\n  letter-spacing: .5px;\n  border: 1px solid ", ";\n  padding: 13px 16px;\n  margin: 0;\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var H3 = _styledComponents["default"].h3(_templateObject(), function (props) {
  return props.theme.primaryDust;
}, function (props) {
  return props.theme.primary;
});

var _default = H3;
exports["default"] = _default;