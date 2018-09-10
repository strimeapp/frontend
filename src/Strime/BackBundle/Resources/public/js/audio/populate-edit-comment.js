// Function which puts content into the modal to edit a comment
function populateEditCommentModal(sound) {

    $(document).on("click", ".comment .comment-edition-tools .comment-edit", function() {

        // If the player is playing, pause it
        if(sound.playing()) {
            sound.pause();
        }

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


// Function which edits a comment
function editComment() {

    $(document).on("click", ".modal#editCommentModal button.validate", function() {

        // Get the ID of the comment
        var commentID = $(this).attr("data-comment-id");

        // Get the comment to edit
        var commentToEdit = null;

        $(".comment").each(function() {

            if( $(this).attr("data-api-comment-id") == commentID ) {
                commentToEdit = $(this);
            }

        });

        // Get the new content of the comment
        var newComment = $(this).closest(".modal").find(".emojionearea-editor").html();
        // console.log(newComment);

        if(newComment === undefined) {
            newComment = $(this).closest(".modal").find("textarea").val();
        }

        // Update the comment
        if(commentID != undefined) {
            $.ajax({
                type: 'POST',
                url: ajaxEditCommentURL,
                data: {
                    'asset_type': assetType,
                    'comment_id': commentID,
                    'comment': newComment
                },
                success: function(data, textStatus, jqXHR){

                    // Get the response
                    var obj = $.parseJSON(data);
                    // console.log(obj);

                    // Check the status
                    // If the comment has been deleted
                    if(obj.response_code == 200) {

                        // Change the content of comment in the discussion feed
                        commentToEdit.find(".text-inner").html( newComment );

                        // Reset the links on the edited comment
                        commentToEdit.linkify({
                            target: "_blank"
                        });

                        // Close the modal
                        $("#editCommentModal").modal('hide');
                    }

                    else {

                        // Display the error message
                        $("#comments #comments-container #edit-comment-error-message").fadeIn();
                        $("#editCommentModal").modal('hide');

                        setTimeout(function() {
                            $("#comments #comments-container #edit-comment-error-message").fadeOut();
                        }, 5000);
                    }
                },
                error: function(data, textStatus, jqXHR){
                    // console.log(data);
                }
            });
        }
    });

}
