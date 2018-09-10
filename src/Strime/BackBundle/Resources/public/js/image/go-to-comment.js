// Function which goes to a comment
function goToComment() {

    // When the user clicks on a comment in the sidebar
    $(document).on("click", "#comments-container > .comment, #image-inner .marker", function(e) {

        // Check that the click didn't occur on an emoji
        if(($(e.target).parents(".emojionearea-picker").size() == 0)
        && (!$(e.target).hasClass("emojioneemoji"))
        && (!$(e.target).hasClass("answer-to-comment"))
        && (!$(e.target).hasClass("emojionearea"))
        && (!$(e.target).hasClass("emojionearea-editor"))
        && (!$(e.target).hasClass("emojionearea-button"))
        && (!$(e.target).hasClass("post-comment-answer"))) {

            // Get the ID of the comment and its time
            var commentID = parseInt( $(this).attr("data-comment-id") );

            // Display the corresponding marker in the image and set as inactive all the others
            $("#image-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#image-inner #comments-layer .marker#marker-"+commentID).removeClass("inactive").addClass("active").fadeIn();

            // Activate the corresponding marker in the timeline
            $(".comment").each(function() {

                if( $(this).attr("data-comment-id") == commentID ) {

                    $( ".comment#" + $(this).attr("id") ).addClass("active");

                    // If the click was not on the emoji picker
                    if( $(e.target).parents(".emojionearea-picker").size() == 0 ) {
                        $("#comments #comments-container").animate({
                            scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment#"+$(this).attr("id")).position().top
                        }, 1000);
                    }
                }
                else {
                    $(this).removeClass("active");
                }
            });
        }

    });

}
