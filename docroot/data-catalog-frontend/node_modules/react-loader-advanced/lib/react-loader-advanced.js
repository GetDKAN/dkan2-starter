'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.createLoaderStack = undefined;

var _assign = require('babel-runtime/core-js/object/assign');

var _assign2 = _interopRequireDefault(_assign);

var _getPrototypeOf = require('babel-runtime/core-js/object/get-prototype-of');

var _getPrototypeOf2 = _interopRequireDefault(_getPrototypeOf);

var _classCallCheck2 = require('babel-runtime/helpers/classCallCheck');

var _classCallCheck3 = _interopRequireDefault(_classCallCheck2);

var _createClass2 = require('babel-runtime/helpers/createClass');

var _createClass3 = _interopRequireDefault(_createClass2);

var _possibleConstructorReturn2 = require('babel-runtime/helpers/possibleConstructorReturn');

var _possibleConstructorReturn3 = _interopRequireDefault(_possibleConstructorReturn2);

var _inherits2 = require('babel-runtime/helpers/inherits');

var _inherits3 = _interopRequireDefault(_inherits2);

var _keys = require('babel-runtime/core-js/object/keys');

var _keys2 = _interopRequireDefault(_keys);

var _extends2 = require('babel-runtime/helpers/extends');

var _extends3 = _interopRequireDefault(_extends2);

var _react = require('react');

var _react2 = _interopRequireDefault(_react);

var _propTypes = require('prop-types');

var _propTypes2 = _interopRequireDefault(_propTypes);

var _CSSTransitionGroup = require('react-transition-group/CSSTransitionGroup');

var _CSSTransitionGroup2 = _interopRequireDefault(_CSSTransitionGroup);

var _wolfy87Eventemitter = require('wolfy87-eventemitter');

var _wolfy87Eventemitter2 = _interopRequireDefault(_wolfy87Eventemitter);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/* eslint-disable
  prefer-template,
  quote-props,
  no-underscore-dangle,
  react/require-default-props,
  react/forbid-prop-types
  */
function uid() {
  return Math.random().toString(36).substr(2, 9);
}

var backgroundDefaultStyle = {
  display: 'block',
  position: 'absolute',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%',
  backgroundColor: 'rgba(0,0,0,0.5)',
  zIndex: 10
};

var foregroundDefaultStyle = {
  display: 'table',
  width: '100%',
  height: '100%',
  textAlign: 'center',
  zIndex: 20,
  color: 'white'
};

var messageDefaultStyle = {
  display: 'table-cell',
  verticalAlign: 'middle'
};

var createLoaderStack = exports.createLoaderStack = function createLoaderStack() {
  return (0, _extends3.default)({}, _wolfy87Eventemitter2.default.prototype, {

    stack: [],

    addLoader: function addLoader(id) {
      var priority = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

      if (this.getIndex(id) === -1) {
        this.stack.push({ id: id, priority: priority });
        this.emitChange();
      }
    },
    removeLoader: function removeLoader(id) {
      if (this.getIndex(id) !== -1) {
        this.stack.splice(this.getIndex(id), 1);
        this.emitChange();
      }
    },
    getIndex: function getIndex(id) {
      return this.stack.findIndex(function (loader) {
        return loader.id === id;
      });
    },
    getMaxPriority: function getMaxPriority() {
      var _this = this;

      var max = 0;

      (0, _keys2.default)(this.stack).forEach(function (key) {
        if (_this.stack[key].priority > max) {
          max = _this.stack[key].priority;
        }
      });

      return max;
    },
    emitChange: function emitChange() {
      this.emit('change');
    },
    addChangeListener: function addChangeListener(callback) {
      this.on('change', callback);
    },
    removeChangeListener: function removeChangeListener(callback) {
      this.removeListener('change', callback);
    }
  });
};

var createLoader = function createLoader(loaderStack) {
  var _class, _temp2;

  return _temp2 = _class = function (_Component) {
    (0, _inherits3.default)(Loader, _Component);

    function Loader() {
      var _ref;

      var _temp, _this2, _ret;

      (0, _classCallCheck3.default)(this, Loader);

      for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      return _ret = (_temp = (_this2 = (0, _possibleConstructorReturn3.default)(this, (_ref = Loader.__proto__ || (0, _getPrototypeOf2.default)(Loader)).call.apply(_ref, [this].concat(args))), _this2), _this2.state = {
        active: false
      }, _this2.onStackChange = function () {
        // if (this.isMounted()) {
        _this2.setState({
          active: loaderStack.getMaxPriority() === _this2.props.priority
        });
        // }
      }, _this2.initialize = function (props) {
        if (props.show) {
          loaderStack.addLoader(_this2._stackId, props.priority);
        } else {
          loaderStack.removeLoader(_this2._stackId);
        }
      }, _temp), (0, _possibleConstructorReturn3.default)(_this2, _ret);
    }

    (0, _createClass3.default)(Loader, [{
      key: 'componentWillMount',
      value: function componentWillMount() {
        this._stackId = uid();
      }
    }, {
      key: 'componentDidMount',
      value: function componentDidMount() {
        loaderStack.addChangeListener(this.onStackChange);
        this.initialize(this.props);
      }
    }, {
      key: 'componentWillReceiveProps',
      value: function componentWillReceiveProps(nextProps) {
        this.initialize(nextProps);
      }
    }, {
      key: 'componentWillUnmount',
      value: function componentWillUnmount() {
        var _this3 = this;

        loaderStack.removeChangeListener(this.onStackChange);

        // Bugfix: 3.3.2016
        // setTimeout fixes rare bug with React 0.13 that is caused by unpredictable
        // component lifecycle (Uncaught Error: Invariant Violation:
        // must be mounted to trap events).
        setTimeout(function () {
          loaderStack.removeLoader(_this3._stackId);
        });
      }
    }, {
      key: 'render',
      value: function render() {
        var _props = this.props,
            backgroundStyle = _props.backgroundStyle,
            children = _props.children,
            className = _props.className,
            contentBlur = _props.contentBlur,
            contentStyle = _props.contentStyle,
            disableDefaultStyles = _props.disableDefaultStyles,
            foregroundStyle = _props.foregroundStyle,
            hideContentOnLoad = _props.hideContentOnLoad,
            message = _props.message,
            messageStyle = _props.messageStyle,
            style = _props.style,
            show = _props.show,
            transitionConfig = _props.transitionConfig;
        var active = this.state.active;


        var shouldShowLoader = !!active && !!show;

        var bgStyle = (0, _extends3.default)({}, disableDefaultStyles ? {} : backgroundDefaultStyle, backgroundStyle);

        var fgStyle = (0, _extends3.default)({}, disableDefaultStyles ? {} : foregroundDefaultStyle, foregroundStyle);

        var msgStyle = (0, _extends3.default)({}, disableDefaultStyles ? {} : messageDefaultStyle, messageStyle);

        var loaderStyle = (0, _extends3.default)({ position: 'relative' }, style);

        var finalContentStyle = (0, _assign2.default)(shouldShowLoader && contentBlur ? {
          'WebkitFilter': 'blur(' + contentBlur + 'px)',
          'MozFilter': 'blur(' + contentBlur + 'px)',
          'OFilter': 'blur(' + contentBlur + 'px)',
          'msFilter': 'blur(' + contentBlur + 'px)',
          'filter': 'blur(' + contentBlur + 'px)'
        } : {}, contentStyle, {
          opacity: hideContentOnLoad && show ? 0 : 1
        });

        var classes = 'Loader' + (className ? ' ' + className : '');

        var loaderElement = !!shouldShowLoader && _react2.default.createElement(
          'div',
          { className: 'Loader__background', style: bgStyle },
          _react2.default.createElement(
            'div',
            { className: 'Loader__foreground', style: fgStyle },
            _react2.default.createElement(
              'div',
              { className: 'Loader__message', style: msgStyle },
              message
            )
          )
        );

        return _react2.default.createElement(
          'div',
          { className: classes, style: loaderStyle },
          _react2.default.createElement(
            'div',
            { className: 'Loader__content', style: finalContentStyle },
            children
          ),
          transitionConfig ? _react2.default.createElement(
            _CSSTransitionGroup2.default,
            transitionConfig,
            loaderElement
          ) : loaderElement
        );
      }
    }]);
    return Loader;
  }(_react.Component), _class.propTypes = {
    backgroundStyle: _propTypes2.default.object,

    children: _propTypes2.default.node,

    className: _propTypes2.default.string,

    // blur loader content while loading
    contentBlur: _propTypes2.default.number,

    contentStyle: _propTypes2.default.object,

    // disables all default styles if true
    disableDefaultStyles: _propTypes2.default.bool,

    foregroundStyle: _propTypes2.default.object,

    hideContentOnLoad: _propTypes2.default.bool,

    // loader message or element
    message: _propTypes2.default.node,

    messageStyle: _propTypes2.default.object,

    // stack priority
    priority: _propTypes2.default.number,

    show: _propTypes2.default.bool.isRequired,

    style: _propTypes2.default.object,

    transitionConfig: _propTypes2.default.shape({
      transitionName: _propTypes2.default.string.isRequired,
      transitionEnterTimeout: _propTypes2.default.number.isRequired,
      transitionLeaveTimeout: _propTypes2.default.number.isRequired
    })
  }, _class.defaultProps = {
    message: 'loading...',
    priority: 0
  }, _temp2;
};

exports.default = createLoader(createLoaderStack());