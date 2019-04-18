"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _styledComponents = _interopRequireDefault(require("styled-components"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n  background: #fff;\n  border: 1px solid ", ";\n  border-radius: 4px;\n  padding: 16px 48px;\n  a {\n    color: ", ";\n    text-decoration: none;\n    &:hover {\n      text-decoration: underline;\n    }\n  }\n  h2 {\n    margin: 8px 0;\n  }\n  .item-theme {\n    border-bottom: 1px solid ", ";\n    color: ", ";\n    font-size: 1.4rem;\n    font-style: normal;\n    font-weight: 400;\n    text-transform: uppercase;\n    letter-spacing: .25px;\n    margin: 1em 0;\n    padding-bottom: .75em;\n    div {\n      display: inline-block;\n      position: relative;\n      padding: 0 20px 0 25px;\n    }\n    img, svg {\n      position: absolute;\n      top:0;\n      left:0;\n    }\n  }\n  .format-types {\n    display: flex;\n    align-items: flex-start;\n    align-content: stretch;\n    flex-wrap: wrap;\n    flex-direction: row;\n    justify-content: flex-start;\n    margin-top: 0.9em;\n  }\n  .label {\n    border-radius: 3px;\n    color: white;\n    font-size: 1.4rem;\n    line-height: 1.6rem;\n    white-space: nowrap;\n    display: inline-block;\n    padding: 5px 8px;\n    margin: .75em 16px .75em 0;\n    &:first-of-type {\n      margin-left: 0;\n    }\n  }\n  .label[data-format=\"csv\"]     { background: ", "; }\n  .label[data-format=\"json\"]    { background: ", "; }\n  .label[data-format=\"pdf\"]     { background: ", "; }\n  .label[data-format=\"rdf\"],     \n  .label[data-format=\"rdf+xml\"] { background: ", "; }\n  .label[data-format=\"xml\"]     { background: ", "; }\n  .label[data-format=\"zip\"]     { background: ", "; }\n  .label[data-format=\"data\"]    { background: ", "; }\n"]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

var Wrapper = _styledComponents["default"].div(_templateObject(), function (props) {
  return props.theme.borderColor;
}, function (props) {
  return props.theme.headingColor;
}, function (props) {
  return props.theme.grayLight;
}, function (props) {
  return props.theme.grayMedium;
}, function (props) {
  return props.theme.csvIcon;
}, function (props) {
  return props.theme.jsonIcon;
}, function (props) {
  return props.theme.pdfIcon;
}, function (props) {
  return props.theme.rdfIcon;
}, function (props) {
  return props.theme.xmlIcon;
}, function (props) {
  return props.theme.zipIcon;
}, function (props) {
  return props.theme.dataIcon;
});

var _default = Wrapper;
exports["default"] = _default;