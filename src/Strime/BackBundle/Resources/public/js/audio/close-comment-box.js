// Function which closes the comment box when the user clicks on the play button
function closeCommentBoxOnClickOnPlayButton(sound) {

    $(document).on("click", "#audio .audio-play-control", function() {

        // Check if the box is opened
        if( $("#audio #audio-inner #comments-layer .comment-box").is(":visible") ) {

            // Hide the comment box
            $("#audio #audio-inner #comments-layer .comment-box").fadeOut();

            // Empty the textarea in the comment box
            setTimeout(function(){
                $("#audio #audio-inner #comments-layer .comment-box textarea").val("");
                $("#audio #audio-inner #comments-layer .comment-box .emojionearea-editor").empty();
            }, 500);

            // Play the audio
            if(!sound.playing()) {
                sound.play();
            }
        }
    });
}


// Function which closes the comment box when the escape key is pressed
function closeCommentBoxOnEscKeypress(sound) {

    // When the user presses the escape key
    $(document).on("keyup", window, function (e) {

        if (e.which == 27) {

            // Check if the box is opened
            if( $("#audio #audio-inner #comments-layer .comment-box").is(":visible") ) {

                // Hide the comment box
                $("#audio #audio-inner #comments-layer .comment-box").fadeOut();

                // Empty the textarea in the comment box
                setTimeout(function(){
                    $("#audio #audio-inner #comments-layer .comment-box textarea").val("");
                    $("#audio #audio-inner #comments-layer .comment-box .emojionearea-editor").empty();
                }, 500);

                // Play the audio
                if(!sound.playing()) {
                    sound.play();
                }
            }

            // Prevent the window to scroll down
            return false;
        }
    });
}
