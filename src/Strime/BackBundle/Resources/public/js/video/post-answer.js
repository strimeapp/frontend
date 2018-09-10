// Function to post the answer
function postAnswer() {

    // When the user clicks on a button to submit an answer
    $(document).on("click", ".comment-answer-field button", function() {

        // Display the loader and hide the submit button
        $(".comment-answer-field button").hide();
        $(".comment-answer-field .loader-container").show();

        // Get the targeted comment
        var target = $(this).attr("data-target");

        // Get the content of the answer
        var answer = $("#comments-container #"+target+" textarea").val();

        // Sanitize the answer
        answer = sanitizeText(answer);

        // Get extra data
        var answerTo = $("#comments-container #"+target).attr("data-api-comment-id");

        // Prepare the value
        if(isOwner == true)
            isOwnerValue = 1;
        else
            isOwnerValue = 0;

        // Send the request to save the comment
        $.ajax({
            type: 'POST',
            url: ajaxAddCommentURL,
            data: {
                'asset_type': 'video',
                'comment': answer,
                'asset_id': videoID,
                'answer_to': answerTo,
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

                    // Hide the loader and display the submit button
                    $(".comment-answer-field .loader-container").hide();
                    $(".comment-answer-field button").show();
                }

                // If the comment has been added
                else {

                    // Get the comment ID
                    var APICommentID = obj.comment_id;

                    if((obj.user != undefined) && (obj.user != null))
                        var comment_author = obj.user;
                    else
                        var comment_author = backVideoJsTextAnonymous;

                    // Display the answer in the timeline
                    var answerHTML = '<div class="comment-answer">';
                    answerHTML += '<div class="col-left">';
                    answerHTML += '<div class="icon-answer"></div>';
                    answerHTML += '<div class="clear"></div>';
                    answerHTML += '</div>';
                    answerHTML += '<div class="col-avatar">';
                    answerHTML += '<div class="avatar">';
                    if(userAvatar != null)
                        answerHTML += '<img src="'+userAvatar+'" alt="'+comment_author+'" title="'+comment_author+'">';
                    answerHTML += '</div>';
                    answerHTML += '</div>';
                    answerHTML += '<div class="col-content">';
                    answerHTML += '<div class="name">'+comment_author+'</div>';
                    answerHTML += '<div class="text">';
                    answerHTML += nl2br(answer);
                    answerHTML += '</div>';
                    answerHTML += '<div class="details">';
                    answerHTML += backVideoJsTextThereWasOneMinute;
                    answerHTML += '<div class="separator">•</div>';
                    answerHTML += '<a href="#" class="answer-to-comment is-child" data-target="'+target+'" data-target-parent="comment-'+ answerTo +'">';
                    answerHTML += backVideoJsTextAnswer;
                    answerHTML += '</a>';
                    answerHTML += '</div>';
                    answerHTML += '</div>';
                    answerHTML += '<div class="clear"></div>';
                    answerHTML += '</div>';

                    $("#comments-container #"+target+" .answers-container").append(answerHTML);

                    // Empty the comment field
                    $("#comments-container #"+target+" textarea").val("");
                    $("#comments-container #"+target+" .emojionearea-editor").empty();

                    // Hide the comment field
                    $("#comments-container #"+target+" .comment-answer-field").fadeOut();

                    // Transform the links in the answer
                    $("#comments-container #"+target+" .answers-container .comment-answer:last-child()").linkify({
                        target: "_blank"
                    });

                    // Increment the number of comments
                    nbComments++;

                    // Display the number of comments in the header of the section
                    if(nbComments > 1)
                        $("#comments .header #nb-comments").empty().text(nbComments+" "+backVideoJsTextComments);
                    else
                        $("#comments .header #nb-comments").empty().text(nbComments+" "+backVideoJsTextComment);

                    // Hide the loader and display the submit button
                    $(".comment-answer-field .loader-container").hide();
                    $(".comment-answer-field button").show();

                    // Empty the answer field
                    $(".comment-answer-field textarea").val("");
                    $(".comment-answer-field .emojionearea-editor").empty();
                }
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);

                // Hide the loader and display the submit button
                $(".comment-answer-field .loader-container").hide();
                $(".comment-answer-field button").show();

                // Display the error message
                $("#comments #comments-container #new-comment-error-message").fadeIn();
                setTimeout(function() {
                    $("#comments #comments-container #new-comment-error-message").fadeOut();
                }, 5000);
            }
        });
    });
}
