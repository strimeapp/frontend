// Function which goes to a comment
function goToComment(sound) {

    // When the user clicks on a comment in the sidebar
    $(document).on("click", "#comments-container > .comment, #audio-inner .marker-progress, #audio-inner .marker", function(e) {

        // Check that the click didn't occur on an emoji, inside the textarea or on the button to post the comment
        if(($(e.target).parents(".emojionearea-picker").size() == 0)
        && (!$(e.target).hasClass("emojioneemoji"))
        && (!$(e.target).hasClass("answer-to-comment"))
        && (!$(e.target).hasClass("emojionearea"))
        && (!$(e.target).hasClass("emojionearea-editor"))
        && (!$(e.target).hasClass("emojionearea-button"))
        && (!$(e.target).hasClass("post-comment-answer"))) {

            // Get the ID of the comment and its time
            var commentID = parseInt( $(this).attr("data-comment-id") );
            var commentTime = parseFloat( $(this).attr("data-time") );

            // Go to the corresponding time in the audio. Pause it if necessary.
            if(isNaN(commentTime) == false) {
                if(sound.playing())
                    sound.pause();

                // Set the player to this time
                sound.seek(commentTime);
            }

            // Display the corresponding marker in the audio and deactivate all the others
            $("#audio-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#audio-inner #comments-layer .marker#marker-"+commentID).removeClass("inactive").addClass("active").fadeIn();

            // Change the color of the marker in the timeline
            $("#audio-inner .marker-progress").removeClass("active").removeClass("inactive");
            $("#audio-inner .marker-progress").each(function() {

                if( $(this).attr("data-comment-id") == commentID ) {
                    $(this).addClass("active")
                }
                else {
                    $(this).addClass("inactive")
                }
            });
        }


        activateThisCommentInSidebar(e.target, $(this));

    });

}
