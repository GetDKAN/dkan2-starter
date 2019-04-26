"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = _interopRequireDefault(require("styled-components"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  .logo {\n    display: inline-block;\n    vertical-align: bottom;\n  }\n  .site-name {\n    display: inline-block;\n    vertical-align: bottom;\n    line-height: 1em;\n    margin-bottom: 10px;\n    a {\n      color: ", ";\n      font-size: 1.8rem;\n    }\n  }\n  .slogan {\n    margin-top: 10px;\n  }\n\n  @media screen and (max-width: 768px) {\n    flex-wrap: wrap;\n    .logo,\n    .site-name {\n      display: block;\n    }\n  }\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var Wrapper = _styledComponents["default"].div(_templateObject(), function (props) {
  return props.theme.headingColor;
});

var _default = Wrapper;
exports["default"] = _default;