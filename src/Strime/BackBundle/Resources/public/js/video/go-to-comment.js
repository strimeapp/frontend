// Function which goes to a comment
function goToComment() {

    // When the user clicks on a comment in the sidebar
    $(document).on("click", "#comments-container > .comment, #video-inner .marker-progress, #video-inner .marker", function(e) {

        // Check that the click didn't occur on an emoji, inside the textarea or on the button to post the comment
        if(($(e.target).parents(".emojionearea-picker").size() == 0)
        && (!$(e.target).hasClass("emojioneemoji"))
        && (!$(e.target).hasClass("answer-to-comment"))
        && (!$(e.target).hasClass("emojionearea"))
        && (!$(e.target).hasClass("emojionearea-editor"))
        && (!$(e.target).hasClass("emojionearea-button"))
        && (!$(e.target).hasClass("post-comment-answer"))) {

            // Set the current player
            var strimePlayer = videojs("strime-video");

            // Get the ID of the comment and its time
            var commentID = parseInt( $(this).attr("data-comment-id") );
            var commentTime = parseFloat( $(this).attr("data-time") );

            // Go to the corresponding time in the video and pause it
            if((strimePlayer.paused() == true) && (isDraggingMarker == false) && !$(e.target).hasClass("marker")) {
                strimePlayer.play();
            }
            if(isNaN(commentTime) == false) {
                strimePlayer.currentTime( commentTime );
                strimePlayer.pause();
            }

            // Display the corresponding marker in the video and deactivate all the others
            $("#video-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#video-inner #comments-layer .marker#marker-"+commentID).removeClass("inactive").addClass("active").fadeIn();

            // Change the color of the marker in the timeline
            $("#video-inner .marker-progress").removeClass("active").removeClass("inactive");
            $("#video-inner .marker-progress").each(function() {

                if( $(this).attr("data-comment-id") == commentID ) {
                    $(this).addClass("active")
                }
                else {
                    $(this).addClass("inactive")
                }
            });

            // Hide the markers that shouldn't be displayed
            $("#video-inner #comments-layer .marker.inactive").each(function() {
                if((parseFloat( $(this).attr("data-show") ) > strimePlayer.currentTime()) || (parseFloat( $(this).attr("data-hide") ) < strimePlayer.currentTime())) {
                    $(this).fadeOut();
                }
            });
        }


        // If the click occured on a progress marker, move to the right comment on the sidebar pane
        if( $(e.target).hasClass("marker-progress") ) {

            activateThisCommentInSidebar(e.target, $(this));
        }

    });

}
