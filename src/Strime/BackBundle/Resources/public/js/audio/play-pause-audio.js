function playPauseAudio(sound) {

    // On click on the play/pause button, begin playing the sound or pause it.
    $(document).on("click", "#content #audio #audio-comments-controls .audio-play-control", function() {
        if(sound.playing()) {
            $(this).addClass("paused");
            sound.pause();
        }
        else {
            sound.play();
            $(this).removeClass("paused");
        }
    });


    // When the user presses the space bar
    $(document).on("keyup", window, function(e) {

        if (e.which == 32) {

            if(sound.playing()) {
                sound.pause();
            }
            else {

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
                    || $(".modal#editCommentModal .modal-body textarea").is(":visible")) {

                    // Do nothing
                }

                // If the box is not visible, play the audio
                else {
                    sound.play();
                }
            }

            // Prevent the window to scroll down
            if(e.target == document.body) {
                e.preventDefault();
            }

            return false;
        }
    });
}
