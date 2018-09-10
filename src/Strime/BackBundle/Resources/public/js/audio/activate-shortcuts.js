// Function which allows the user to use keys to control audio speed
function activateShortcuts(sound) {

    // When the user presses the space bar
    $(document).on("keyup", window, function(e) {

        // If the user presses the "j" key
        if (e.which == 74) {

            // Check if the box is opened and if the focus is in the textarea
            // Or if the edit box is opened
            // Or if the share box is opened
            // Or if the user is editing the name of the audio
            // Or if the answer field is visible
            if(( $("#audio #audio-inner #comments-layer .comment-box").is(":visible") && $("#audio #audio-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#audio-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#audio #audio-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")) {

                // Do nothing
            }

            // If the box is not visible, decrease the rate
            else {

                if(currentPlaybackRate <= 1) {
                    currentPlaybackRate = 0.5;
                    sound.rate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 2) {
                    currentPlaybackRate = 1;
                    sound.rate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 4) {
                    currentPlaybackRate = 2;
                    sound.rate(currentPlaybackRate);
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
            // Or if the user is editing the name of the audio
            // Or if the answer field is visible
            if(( $("#audio #audio-inner #comments-layer .comment-box").is(":visible") && $("#audio #audio-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#audio-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#audio #audio-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")) {

                // Do nothing
            }

            // If the box is not visible, back to the normal rate
            else {

                currentPlaybackRate = 1;
                sound.rate(currentPlaybackRate);

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
            // Or if the user is editing the name of the audio
            // Or if the answer field is visible
            if(( $("#audio #audio-inner #comments-layer .comment-box").is(":visible") && $("#audio #audio-inner #comments-layer .comment-box textarea").is(":focus") )
                || $("#editCommentModal").is(":visible")
                || $("#shareAssetModal").is(":visible")
                || $("#asset-meta h1 input").is(":focus")
                || $("#audio-description textarea").is(":focus")
                || ( $("#feedback-box").is(":visible") && $("#feedback-box textarea").is(":focus") )
                || $("#comments .comment .comment-answer-field textarea").is(":visible")
                || $("#comments .comment .comment-answer-field .emojionearea-editor").is(":visible")
                || $("#audio #audio-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").is(":visible")
                || $(".modal#editCommentModal .modal-body textarea").is(":visible")
                || $(".modal#exportYoutubeModal .modal-body textarea").is(":visible")) {

                // Do nothing
            }

            // If the box is not visible, increase the rate
            else {

                if(currentPlaybackRate == 0.5) {
                    currentPlaybackRate = 1;
                    sound.rate(currentPlaybackRate);
                }
                else if(currentPlaybackRate == 1) {
                    currentPlaybackRate = 2;
                    sound.rate(currentPlaybackRate);
                }
                else if(currentPlaybackRate >= 2) {
                    currentPlaybackRate = 4;
                    sound.rate(currentPlaybackRate);
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
