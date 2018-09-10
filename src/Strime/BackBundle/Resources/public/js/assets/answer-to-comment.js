// Function to answer to a comment
function answerToComment() {

    // When the user clicks on a link to answer to a comment
    $(document).on("click", ".answer-to-comment", function(e) {

        // Prevent the browser to scroll back up
        e.preventDefault();

        // Get the targeted comment
        if($(this).hasClass("is-child")) {
            var target = $(this).attr("data-target-parent");
        }
        else {
            var target = $(this).attr("data-target");
        }

        // Hide all the fields to answer to comment
        $("#comments-container .comment-answer-field").fadeOut();

        // Display the field
        $("#comments-container #"+target+" .comment-answer-field").fadeIn();
        var commentTarget = $(this).attr("data-target");
        var currentCommentID = commentTarget.substring(8, commentTarget.length);

        // Focus on the field
        $("#comments-container #"+target+" .comment-answer-field").focus();
        $("#comments-container #"+target+" .emojionearea .emojionearea-editor").attr("contenteditable", true).focus();
        $("#comments-container #"+target+" .emojionearea").addClass("focused");

        answerToAuthorID = $("#"+commentTarget).attr("data-comment-author-id");
        answerToAuthorType = $("#"+commentTarget).attr("data-comment-author-type");
    });
}
