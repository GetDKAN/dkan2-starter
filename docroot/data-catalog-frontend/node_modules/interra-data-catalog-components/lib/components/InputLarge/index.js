"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _react = _interopRequireDefault(require("react"));

var _reactstrap = require("reactstrap");

var _propTypes = _interopRequireDefault(require("prop-types"));

var _FormGroup = _interopRequireDefault(require("./FormGroup"));

var _Button = _interopRequireDefault(require("../Button"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var InputLarge =
/*#__PURE__*/
function (_React$Component) {
  _inherits(InputLarge, _React$Component);

  function InputLarge() {
    _classCallCheck(this, InputLarge);

    return _possibleConstructorReturn(this, _getPrototypeOf(InputLarge).apply(this, arguments));
  }

  _createClass(InputLarge, [{
    key: "onFieldChange",
    value: function onFieldChange(event) {
      // for a regular input field, read field name and value from the event
      var fieldName = event.target.name;
      var fieldValue = event.target.value;
      this.props.onChange(fieldName, fieldValue);
    }
  }, {
    key: "onReset",
    value: function onReset(event) {
      window.location = '/search';
    }
  }, {
    key: "onGo",
    value: function onGo(event) {
      if (this.props.facets || this.props.value) {
        var location = "/search?";

        for (var i in this.props.facets) {
          location = location + this.props.facets[i][0] + "=" + this.props.facets[i][1] + '&';

          if (i === this.props.facets.length - 1) {
            console.log(i);
          }
        }

        if (this.props.value) {
          location = location + "q=" + this.props.value;
        } else {
          // Remove last &.
          location = location.slice(0, -1);
        }

        window.location = location;
      }
    }
  }, {
    key: "render",
    value: function render() {
      return _react["default"].createElement(_FormGroup["default"], null, _react["default"].createElement(_reactstrap.Label, {
        "for": "search",
        className: "sr-only"
      }, "Search"), _react["default"].createElement("input", {
        type: "text",
        name: "search",
        id: "search",
        className: "form-control form-text",
        placeholder: "Search for...",
        onChange: this.onFieldChange.bind(this),
        value: this.props.value
      }), _react["default"].createElement("span", {
        className: "input-group-btn"
      }, _react["default"].createElement(_Button["default"], _defineProperty({
        type: "submit",
        id: "submit",
        name: "op",
        className: "btn btn-primary",
        onClick: this.onGo.bind(this)
      }, "type", "button"), "Go!"), _react["default"].createElement(_Button["default"], {
        className: "btn btn-primary",
        onClick: this.onReset.bind(this),
        type: "button"
      }, "Reset")));
    }
  }]);

  return InputLarge;
}(_react["default"].Component);

InputLarge.propTypes = {
  onChange: _propTypes["default"].func.isRequired,
  items: _propTypes["default"].array,
  className: _propTypes["default"].string
};
var _default = InputLarge;
exports["default"] = _default;