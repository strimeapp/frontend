// Function which allows the user to play or pause the video when he presses the space bar
function playPauseVideoOnSpacePress() {

    // When the user presses the space bar
    $(document).on("keyup", window, function(e) {

        if (e.which == 32) {

            // Set the variable for the player
            strimePlayer = videojs("strime-video");

            // Check if the player is paused
            var isPaused = strimePlayer.paused();

            // If the player is paused
            if(isPaused == true) {

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

                // If the box is not visible, play the video
                else {
                    strimePlayer.play();
                }
            }

            // If the video is playing
            else {

                // Pause the video
                strimePlayer.pause();
            }

            // Prevent the window to scroll down
            if(e.target == document.body) {
                e.preventDefault();
            }

            return false;
        }
    });

}
