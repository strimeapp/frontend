'use strict';

exports.__esModule = true;
exports.bufferedPercent = bufferedPercent;

var _timeRanges = require('./time-ranges.js');

/**
 * Compute how much your video has been buffered
 *
 * @param  {Object} Buffered object
 * @param  {Number} Total duration
 * @return {Number} Percent buffered of the total duration
 * @private
 * @function bufferedPercent
 */
function bufferedPercent(buffered, duration) {
  var bufferedDuration = 0;
  var start = void 0;
  var end = void 0;

  if (!duration) {
    return 0;
  }

  if (!buffered || !buffered.length) {
    buffered = (0, _timeRanges.createTimeRange)(0, 0);
  }

  for (var i = 0; i < buffered.length; i++) {
    start = buffered.start(i);
    end = buffered.end(i);

    // buffered end can be bigger than duration by a very small fraction
    if (end > duration) {
      end = duration;
    }

    bufferedDuration += end - start;
  }

  return bufferedDuration / duration;
} /**
   * @file buffer.js
   */
