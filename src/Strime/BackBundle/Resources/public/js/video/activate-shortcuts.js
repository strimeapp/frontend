// Function which allows the user to use keys to control video speed
function activateShortcuts() {

    // When the user presses the space bar
    $(document).on("keyup", window, function(e) {

        // Set the variable for the player
        strimePlayer = videojs("strime-video");

        // If the user presses the "j" key
        if (e.which == 74) {

            // Check if the box is opened and if the focus is in the textarea
            // Or if the edit box is opened
            // Or if the share box is opened
            // Or if the user is editing the name of the video
            // Or if the answer field is visible
            if(( $("#video #video-inner #comments-layer .comment-box").is(":visible") && $("#video #video-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#video-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#video #video-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")
                || (isDraggingMarker == true)) {

                // Do nothing
            }

            // If the box is not visible, decrease the rate
            else {

                if(currentPlaybackRate <= 1) {
                    currentPlaybackRate = 0.5;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 2) {
                    currentPlaybackRate = 1;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 4) {
                    currentPlaybackRate = 2;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }

                // Show the info box
                $("#playback-rate-info-box span").empty().text(currentPlaybackRate);
                $("#playback-rate-info-box").fadeIn().css("display", "table");
                setTimeout(function() {
                    $("#playback-rate-info-box").fadeOut();
                }, 4000);
            }

            return false;
        }

        // If the user presses the "k" key
        if (e.which == 75) {

            // Check if the box is opened and if the focus is in the textarea
            // Or if the edit box is opened
            // Or if the share box is opened
            // Or if the user is editing the name of the video
            // Or if the answer field is visible
            if(( $("#video #video-inner #comments-layer .comment-box").is(":visible") && $("#video #video-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#video-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#video #video-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")
                || (isDraggingMarker == true)) {

                // Do nothing
            }

            // If the box is not visible, back to the normal rate
            else {

                currentPlaybackRate = 1;
                strimePlayer.playbackRate(currentPlaybackRate);

                // Show the info box
                $("#playback-rate-info-box span").empty().text(currentPlaybackRate);
                $("#playback-rate-info-box").fadeIn().css("display", "table");
                setTimeout(function() {
                    $("#playback-rate-info-box").fadeOut();
                }, 4000);
            }

            return false;
        }

        // If the user presses the "l" key
        if (e.which == 76) {

            // Check if the box is opened and if the focus is in the textarea
            // Or if the edit box is opened
            // Or if the share box is opened
            // Or if the user is editing the name of the video
            // Or if the answer field is visible
            if(( $("#video #video-inner #comments-layer .comment-box").is(":visible") && $("#video #video-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#video-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#video #video-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")
                || (isDraggingMarker == true)) {

                // Do nothing
            }

            // If the box is not visible, increase the rate
            else {

                if(currentPlaybackRate == 0.5) {
                    currentPlaybackRate = 1;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 1) {
                    currentPlaybackRate = 2;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }
                else if(currentPlaybackRate >= 2) {
                    currentPlaybackRate = 4;
                    strimePlayer.playbackRate(currentPlaybackRate);
                }

                // Show the info box
                $("#playback-rate-info-box span").empty().text(currentPlaybackRate);
                $("#playback-rate-info-box").fadeIn().css("display", "table");
                setTimeout(function() {
                    $("#playback-rate-info-box").fadeOut();
                }, 4000);
            }

            return false;
        }
    });

}
