// Function which puts content into the modal to edit a comment
function populateEditCommentModal() {

    $(document).on("click", ".comment .comment-edition-tools .comment-edit", function() {

        var commentID = $(this).closest(".comment").attr("data-api-comment-id");
        var commentToEdit = $(this).closest(".comment");

        // Set the comment ID in the DOM
        $(".modal#editCommentModal .modal-footer button.validate").attr("data-comment-id", commentID);

        // Display the author avatar
        if(userAvatar != null) {
            $(".modal#editCommentModal .modal-body .avatar img").attr("src", userAvatar);
            $(".modal#editCommentModal .modal-body .avatar img").attr("alt", author).attr("title", author);
        }

        // Put the comment into the textarea
        var commentContent = commentToEdit.find(".text-inner").html().trim();
        commentContent = stripnl( commentContent );
        $(".modal#editCommentModal .modal-body textarea").val( br2nl(commentContent) );
        $(".modal#editCommentModal .modal-body .emojionearea .emojionearea-editor").html( commentContent );

    });

}
