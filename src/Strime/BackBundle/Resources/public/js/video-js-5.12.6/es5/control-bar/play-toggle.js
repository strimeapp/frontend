'use strict';

exports.__esModule = true;

var _button = require('../button.js');

var _button2 = _interopRequireDefault(_button);

var _component = require('../component.js');

var _component2 = _interopRequireDefault(_component);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                * @file play-toggle.js
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                */


/**
 * Button to toggle between play and pause
 *
 * @param {Player|Object} player
 * @param {Object=} options
 * @extends Button
 * @class PlayToggle
 */
var PlayToggle = function (_Button) {
  _inherits(PlayToggle, _Button);

  function PlayToggle(player, options) {
    _classCallCheck(this, PlayToggle);

    var _this = _possibleConstructorReturn(this, _Button.call(this, player, options));

    _this.on(player, 'play', _this.handlePlay);
    _this.on(player, 'pause', _this.handlePause);
    return _this;
  }

  /**
   * Allow sub components to stack CSS class names
   *
   * @return {String} The constructed class name
   * @method buildCSSClass
   */


  PlayToggle.prototype.buildCSSClass = function buildCSSClass() {
    return 'vjs-play-control ' + _Button.prototype.buildCSSClass.call(this);
  };

  /**
   * Handle click to toggle between play and pause
   *
   * @method handleClick
   */


  PlayToggle.prototype.handleClick = function handleClick() {
    if (this.player_.paused()) {
      this.player_.play();
    } else {
      this.player_.pause();
    }
  };

  /**
   * Add the vjs-playing class to the element so it can change appearance
   *
   * @method handlePlay
   */


  PlayToggle.prototype.handlePlay = function handlePlay() {
    this.removeClass('vjs-paused');
    this.addClass('vjs-playing');
    // change the button text to "Pause"
    this.controlText('Pause');
  };

  /**
   * Add the vjs-paused class to the element so it can change appearance
   *
   * @method handlePause
   */


  PlayToggle.prototype.handlePause = function handlePause() {
    this.removeClass('vjs-playing');
    this.addClass('vjs-paused');
    // change the button text to "Play"
    this.controlText('Play');
  };

  return PlayToggle;
}(_button2['default']);

PlayToggle.prototype.controlText_ = 'Play';

_component2['default'].registerComponent('PlayToggle', PlayToggle);
exports['default'] = PlayToggle;
