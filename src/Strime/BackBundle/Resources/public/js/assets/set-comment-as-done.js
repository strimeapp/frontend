// Function which sets a comment as done
function setCommentAsDone() {

    $(document).on("click", ".comment .mark-as-done", function() {

        var markAsDone = $(this);
        var checkbox = markAsDone.find(".mark-as-done-checkbox");
        var commentID = markAsDone.parent(".comment").attr("data-api-comment-id");

        // If the checkbox is already checked
        if( checkbox.hasClass("checked") ) {

            // Update the comment
            $.ajax({
                type: 'POST',
                url: ajaxEditCommentURL,
                data: {
                    'asset_type': assetType,
                    'comment_id': commentID,
                    'done': 0
                },
                success: function(data, textStatus, jqXHR){

                    // Get the response
                    var obj = $.parseJSON(data);
                    // console.log(obj);

                    // Check the status
                    // If the comment has not been updated
                    if(obj.response_code == 200) {

                        // If the checkbox is already checked,
                        // Uncheck the checkbox
                        checkbox.removeClass("checked");

                        // Remove the done class to the comment
                        markAsDone.parent(".comment").removeClass("done");
                    }
                },
                error: function(data, textStatus, jqXHR){
                    // console.log(data);
                }
            });
        }
        else {

            // Update the comment
            $.ajax({
                type: 'POST',
                url: ajaxEditCommentURL,
                data: {
                    'asset_type': assetType,
                    'comment_id': commentID,
                    'done': 1
                },
                success: function(data, textStatus, jqXHR){

                    // Get the response
                    var obj = $.parseJSON(data);
                    // console.log(obj);

                    // Check the status
                    // If the comment has not been updated
                    if(obj.response_code == 200) {

                        // If the checkbox is not checked
                        // Check the checkbox
                        checkbox.addClass("checked");

                        // Add the done class to the comment
                        markAsDone.parent(".comment").addClass("done");

                        // Hide the comment if the toggle to hide the resolved comments is activated
                        if( ( $('#comments #comments-filters #resolved-comments-toggle label input:checked').length == 1 )
                            && ( $("#comments #comments-container .comment.done").is(":visible") ) ) {

                            markAsDone.parent(".comment").fadeOut();
                        }
                    }
                },
                error: function(data, textStatus, jqXHR){
                    // console.log(data);
                }
            });
        }
    });

}
