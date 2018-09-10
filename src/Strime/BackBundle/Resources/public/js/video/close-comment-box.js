// Function which closes the comment box when the user clicks on the play button
function closeCommentBoxOnClickOnPlayButton() {

    $(document).on("click", "#video .vjs-play-control", function() {
        strimePlayer = videojs("strime-video");

        if( $(this).hasClass("vjs-paused") ) {
            strimePlayer.play();
            $("#video #comments-layer .comment-box").fadeOut();
        }
    });
}


// Function which closes the comment box when the escape key is pressed
function closeCommentBoxOnEscKeypress() {

    // When the user presses the escape key
    $(document).on("keyup", window, function (e) {

        if (e.which == 27) {

            // Set the variable for the player
            strimePlayer = videojs("strime-video");

            // Check if the box is opened
            if( $("#video #video-inner #comments-layer .comment-box").is(":visible") ) {

                // Hide the comment box
                $("#video #video-inner #comments-layer .comment-box").fadeOut();

                // Empty the textarea in the comment box
                setTimeout(function(){
                    $("#video #video-inner #comments-layer .comment-box textarea").val("");
                }, 500);

                // Play the video
                strimePlayer.play();
            }

            // Prevent the window to scroll down
            return false;
        }
    });
}
