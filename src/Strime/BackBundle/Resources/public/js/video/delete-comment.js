// Function which deletes a comment after the click on the trash can
function populateDeleteCommentModal() {

    $(document).on("click", ".comment .comment-edition-tools .comment-delete", function(e) {

        // We prevent the click to happen twice
        e.stopPropagation();
        e.preventDefault();

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Check if the player is paused
        var isPaused = strimePlayer.paused();

        // If the player is playing, pause it
        if(!isPaused) {
            strimePlayer.pause();
        }

        // Get the comment ID
        var commentID = $(this).closest(".comment").attr("data-api-comment-id");

        // Set the comment ID in the DOM
        $(".modal#deleteCommentModal .modal-footer button.validate").attr("data-comment-id", commentID);
    });

}



// Function which deletes a comment after the click on the trash can
function deleteComment() {

    $(document).on("click", ".modal#deleteCommentModal button.validate", function() {

        // Get the comment ID
        var commentID = $(this).attr("data-comment-id");

        // Get the comment to edit
        var commentToDelete = null;

        $(".comment").each(function() {

            if( $(this).attr("data-api-comment-id") == commentID ) {
                commentToDelete = $(this);
            }

        });

        // Update the comment
        if(commentID != undefined) {
            $.ajax({
                type: 'POST',
                url: ajaxDeleteCommentURL,
                data: {
                    'asset_type': 'video',
                    'comment_id': commentID,
                    'asset_id': videoID
                },
                success: function(data, textStatus, jqXHR){

                    // Get the response
                    var obj = $.parseJSON(data);
                    // console.log(obj);

                    // Check the status
                    // If the comment has been deleted
                    if(obj.response_code == 204) {

                        // Hide it from the UI
                        commentToDelete.fadeOut( function() {
                            commentToDelete.remove();
                        } );

                        // Hide the marker from the video
                        $("#video #video-inner #comments-layer img.marker").each(function() {

                            if( $(this).attr("data-api-comment-id") == commentID ) {
                                $(this).fadeOut( function(){
                                    $(this).remove();
                                } );
                            }

                        });

                        // Hide the marker on the timeline
                        $("#video #video-inner .vjs-control-bar .vjs-progress-control .marker-progress").each(function() {

                            if( $(this).attr("data-api-comment-id") == commentID ) {
                                $(this).fadeOut( function(){
                                    $(this).remove();
                                } );
                            }

                        });

                        // Update the number of comments
                        nbComments = obj.nb_comments;
                        if(obj.nb_comments > 1)
                            var nbCommentsText = nbComments + " "+backVideoJsTextComments;
                        else
                            var nbCommentsText = nbComments + " "+backVideoJsTextComment;

                        $("#comments .header #nb-comments").empty().text(nbCommentsText);

                        // Hide the modal
                        $("#deleteCommentModal").modal('hide');
                    }

                    else {

                        // Display the error message
                        $("#comments #comments-container #delete-comment-error-message").fadeIn();
                        $("#deleteCommentModal").modal('hide');

                        setTimeout(function() {
                            $("#comments #comments-container #delete-comment-error-message").fadeOut();
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
