// Function which closes the comment box when the escape key is pressed
function closeCommentBoxOnEscKeypress() {

    // When the user presses the escape key
    $(document).on("keyup", window, function (e) {

        if (e.which == 27) {

            // Check if the box is opened
            if( $("#image #image-inner #comments-layer .comment-box").is(":visible") ) {

                // Hide the comment box
                $("#image #image-inner #comments-layer .comment-box").fadeOut();

                // Empty the textarea in the comment box
                setTimeout(function(){
                    $("#image #image-inner #comments-layer .comment-box textarea").val("");
                }, 500);
            }

            // Prevent the window to scroll down
            return false;
        }
    });
}
