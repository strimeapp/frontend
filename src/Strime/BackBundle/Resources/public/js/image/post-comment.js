// Function to post a comment
function postComment() {

    // When the user clicks on the send button of the comment box
    $(document).on("click", "#image-inner #comments-layer .comment-box button", function() {

        // Get the content of the comment
        var comment = $("#image-inner #comments-layer .comment-box textarea").val();

        // Make sure that the comment is not empty
        if(comment != "") {

            // Sanitize the comment
            comment = sanitizeText(comment);

            // Display the loader and hide the submit button
            $(".comment-field button").hide();
            $(".comment-field .loader-container").show();

            // Increment the number of comments
            nbComments++;

            // Get the comment box position
            if($("#image-inner #comments-layer .comment-box").hasClass("bottom"))
                var commentBoxTop = parseInt( $("#image-inner #comments-layer .comment-box").css("top") ) + parseInt( $("#image-inner #comments-layer .comment-box").css("height") ) - 20;
            else
                var commentBoxTop = parseInt( $("#image-inner #comments-layer .comment-box").css("top") );

            if($("#image-inner #comments-layer .comment-box").hasClass("right"))
                var commentBoxLeft = parseInt( $("#image-inner #comments-layer .comment-box").css("left") ) + parseInt( $("#image-inner #comments-layer .comment-box").css("width") ) - 20;
            else
                var commentBoxLeft = parseInt( $("#image-inner #comments-layer .comment-box").css("left") );

            // Set the position of the marker
            var markerTop = parseFloat( commentBoxTop / parseInt( $("#image-inner #comments-layer").css("height") )) * 100;
            var markerLeft = parseFloat( commentBoxLeft / parseInt( $("#image-inner #comments-layer").css("width") )) * 100;

            // Prepare the value
            if(isOwner == true)
                var isOwnerValue = 1;
            else
                var isOwnerValue = 0;

            // Send the request to save the comment
            $.ajax({
                type: 'POST',
                url: ajaxAddCommentURL,
                data: {
                    'asset_type': 'image',
                    'comment': comment,
                    'top': markerTop,
                    'left': markerLeft,
                    'asset_id': imageID,
                    'contact_id': contactID,
                    'user_id': userID,
                    'author': author,
                    'is_owner': isOwnerValue,
                    'answer_to_author_id': answerToAuthorID,
                    'answer_to_author_type': answerToAuthorType
                },
                success: function(data, textStatus, jqXHR){

                    // Get the response
                    var obj = $.parseJSON(data);
                    // console.log(obj);

                    // Check the status
                    // If the comment has not been added
                    if(obj.response_code != 201) {

                        // Display the error message
                        $("#comments #comments-container #new-comment-error-message").fadeIn();
                        setTimeout(function() {
                            $("#comments #comments-container #new-comment-error-message").fadeOut();
                        }, 5000);
                    }

                    // If the comment has been added
                    else {

                        // Get the comment ID
                        var APICommentID = obj.comment_id;
                        var comment_author = obj.user;

                        if(comment_author == null)
                            comment_author = author;

                        // Add a marker on the image
                        var marker = '<img src="'+iconMarker+'" class="marker" id="marker-'+nbComments+'" alt="'+backImageJsTextSeeTheComment+'" title="'+backImageJsTextSeeTheComment+'" data-comment-id="'+nbComments+'" data-api-comment-id="'+APICommentID+'" style="top: '+markerTop+'%; left: '+markerLeft+'%; display: block;" draggable="true" ondragstart="dragMarker(event)" ondragend="dragEndMarker(event)">';
                        $("#comments-layer").append(marker);

                        // Define the author and ID
                        if(userID != null) {
                            var authorID = userID;
                        }
                        else if(contactID != null) {
                            var authorID = contactID;
                        }

                        // Display the comment in the sidebar
                        var commentHTML = '<div class="comment" id="comment-'+APICommentID+'" data-comment-id="'+nbComments+'" data-api-comment-id="'+APICommentID+'" data-comment-author-type="'+authorType+'" data-comment-author-id="'+authorID+'">';

                        if(isOwner == true) {
                            commentHTML += '<div class="mark-as-done">';
                            commentHTML += backImageJsTextResolved;
                            commentHTML += '<div class="mark-as-done-checkbox"></div>';
                            commentHTML += '</div>';
                        }

                        commentHTML += '<div class="row">';
                        commentHTML += '<div class="col-avatar">';
                        if(userAvatar != null) {
                            commentHTML += '<div class="avatar no-background">';
                            commentHTML += '<img src="'+userAvatar+'" alt="'+comment_author+'" title="'+comment_author+'">';
                            commentHTML += '</div>';
                        }
                        else {
                            commentHTML += '<div class="avatar">';
                            commentHTML += '</div>';
                        }
                        commentHTML += '</div>';
                        commentHTML += '<div class="col-content">';
                        commentHTML += '<div class="name"></span><span class="comment-author">'+comment_author+'</span></div>';
                        commentHTML += '<div class="text">';
                        commentHTML += '<div class="text-inner">';
                        commentHTML += nl2br(comment);
                        commentHTML += '</div>';
                        commentHTML += '<div class="comment-edition-tools">';
                        commentHTML += '<div class="comment-delete" data-toggle="modal" data-target="#deleteCommentModal"></div>';
                        commentHTML += '<div class="comment-edit" data-toggle="modal" data-target="#editCommentModal"></div>';
                        commentHTML += '<div class="clear"></div>';
                        commentHTML += '</div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="details">';
                        commentHTML += backImageJsTextThereWasOneMinute;
                        commentHTML += '<div class="separator">•</div>';
                        commentHTML += '<a href="#" class="answer-to-comment" data-target="comment-'+APICommentID+'">';
                        commentHTML += backImageJsTextAnswer;
                        commentHTML += '</a>';
                        commentHTML += '</div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="clear"></div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="answers-container">';
                        commentHTML += '</div>';
                        commentHTML += '<div class="comment-answer-field">';
                        commentHTML += '<textarea placeholder="'+backImageJsTextTypeInAnswer+'..."></textarea>';
                        commentHTML += '<button class="post-comment-answer" data-target="comment-'+APICommentID+'">'+backImageJsTextSend+'</button>';
                        commentHTML += '<div class="loader-container">';
                        commentHTML += '<div class="loader-pulse">';
                        commentHTML += backImageJsTextLoading+'...';
                        commentHTML += '</div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="clear"></div>';
                        commentHTML += '</div>';
                        commentHTML += '</div><!-- ./Comment -->';

                        $("#comments #comments-container").append(commentHTML);

                        // Display the number of comments in the header of the section
                        if(nbComments > 1)
                            $("#comments .header #nb-comments").empty().text(nbComments+" "+backImageJsTextComments);
                        else
                            $("#comments .header #nb-comments").empty().text(nbComments+" "+backImageJsTextComment);

                        // Empty the textarea
                        $("#image-inner #comments-layer .comment-box textarea").val("");
                        $("#image-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").empty();

                        // Hide the comment box
                        $("#image-inner #comments-layer .comment-box").fadeOut();

                        // Transform the links in the comment
                        $('.comment#comment-'+APICommentID).linkify({
                            target: "_blank"
                        });

                        // Hide the loader and display the submit button
                        $(".comment-field .loader-container").hide();
                        $(".comment-field button").show();

                        // Reload the Emoji editor
                        $("#comments #comments-container .comment .comment-answer-field textarea").emojioneArea({
                            pickerPosition: "top",
                            filtersPosition: "top",
                            tonesStyle: "checkbox"
                        });

                        // Reset the list of comments for the previous and next button
                        updateListOfCommentsForButtons(imageID, 'image');
                        updatePreviousNextButtonsValues(APICommentID);
                    }
                },
                error: function(data, textStatus, jqXHR){
                    // console.log(data);

                    // Hide the loader and display the submit button
                    $(".comment-field .loader-container").hide();
                    $(".comment-field button").show();

                    // Display the error message
                    $("#comments #comments-container #new-comment-error-message").fadeIn();
                    setTimeout(function() {
                        $("#comments #comments-container #new-comment-error-message").fadeOut();
                    }, 5000);
                }
            });
        }
    });
}
