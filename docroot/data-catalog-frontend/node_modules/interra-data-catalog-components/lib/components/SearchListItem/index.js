"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _react = _interopRequireDefault(require("react"));

var _propTypes = _interopRequireDefault(require("prop-types"));

var _ListItem = _interopRequireDefault(require("../ListItem"));

var _Wrapper = _interopRequireDefault(require("./Wrapper"));

var _excerpts = _interopRequireDefault(require("excerpts"));

var _TopicImage = _interopRequireDefault(require("../IconListItem/TopicImage"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var SearchListItem =
/*#__PURE__*/
function (_React$PureComponent) {
  _inherits(SearchListItem, _React$PureComponent);

  function SearchListItem() {
    _classCallCheck(this, SearchListItem);

    return _possibleConstructorReturn(this, _getPrototypeOf(SearchListItem).apply(this, arguments));
  }

  _createClass(SearchListItem, [{
    key: "formats",
    // eslint-disable-line react/prefer-stateless-function
    value: function formats(distribution) {
      if (!distribution) {
        return null;
      } else {
        var i = 0;
        return distribution.map(function (dist) {
          i++;
          var format = dist.format === undefined ? '' : dist.format.toLowerCase();
          return _react["default"].createElement("div", {
            title: "format: ".concat(dist.format),
            key: "dist-id-".concat(dist.identifier, "-").concat(i),
            className: "label",
            "data-format": format
          }, format);
        });
      }
    }
  }, {
    key: "themes",
    value: function themes(theme) {
      if (!theme) {
        return null;
      } else {
        var i = 0;
        return theme.map(function (topic) {
          i++; // if (topic.icon) {
          //   return <div key={`dist-${topic.identifier}-${i}`}>
          //     <img src={topic.icon} height="16px" width="16px" alt={topic.alt} /> 
          //     {topic.title}
          //   </div>
          // }
          // else {

          return _react["default"].createElement("div", {
            key: "dist-".concat(topic.identifier, "-").concat(i)
          }, _react["default"].createElement(_TopicImage["default"], {
            title: topic.title,
            height: "16",
            width: "16",
            fill: "#A7AAAC"
          }), topic.title); //}
        });
      }
    }
  }, {
    key: "render",
    value: function render() {
      var item = this.props.item;
      var description = (0, _excerpts["default"])(item.description, {
        words: 35
      });
      var formats = this.formats(item.format);
      var themes = this.themes(item.theme); // Put together the content of the repository

      var content = _react["default"].createElement(_Wrapper["default"], {
        key: "wrapper-".concat(item.identifier),
        className: "search-list-item"
      }, _react["default"].createElement("a", {
        href: item.ref
      }, _react["default"].createElement("h2", null, item.title)), _react["default"].createElement("div", {
        className: "item-theme"
      }, themes), _react["default"].createElement("div", {
        className: "item-description"
      }, description), _react["default"].createElement("div", {
        className: "item-org"
      }, _react["default"].createElement("strong", null, "organization:"), " ", item.publisher), _react["default"].createElement("div", {
        className: "format-types"
      }, formats));

      return _react["default"].createElement(_ListItem["default"], {
        key: "repo-list-item-".concat(item.identifier),
        item: content
      });
    }
  }]);

  return SearchListItem;
}(_react["default"].PureComponent);

SearchListItem.defaultProps = {
  item: {
    "identifier": 1234,
    "title": "This is a title",
    "description": "I am an item",
    "modified": "1/12/2018",
    "publisher": "Publish Inc."
  }
};
SearchListItem.propTypes = {
  item: _propTypes["default"].object
};
var _default = SearchListItem;
exports["default"] = _default;