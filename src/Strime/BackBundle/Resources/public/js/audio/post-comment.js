// Function to post a comment
function postComment(sound) {

    // When the user clicks on the send button of the comment box
    $(document).on("click", "#audio-inner #comments-layer .comment-box button", function() {

        // Get the content of the comment
        var comment = $("#audio-inner #comments-layer .comment-box textarea").val();

        // Make sure that the comment is not empty
        if(comment != "") {

            // Sanitize the comment
            comment = sanitizeText(comment);

            // Display the loader and hide the submit button
            $(".comment-field button").hide();
            $(".comment-field .loader-container").show();

            // Increment the number of comments
            nbComments++;

            // Get the current time
            var currentTime = sound.seek();

            // Get the comment box position
            var commentBoxTop = parseInt( $("#audio-inner #comments-layer .comment-box").css("top") );

            if($("#audio-inner #comments-layer .comment-box").hasClass("right"))
                var commentBoxLeft = parseInt( $("#audio-inner #comments-layer .comment-box").css("left") ) + parseInt( $("#audio-inner #comments-layer .comment-box").css("width") ) - 20;
            else
                var commentBoxLeft = parseInt( $("#audio-inner #comments-layer .comment-box").css("left") );

            // Set the position of the marker
            var markerTop = 0;
            var markerLeft = parseFloat( commentBoxLeft / parseInt( $("#audio-inner #comments-layer").css("width") )) * 100;
            var markerProgressLeft = parseFloat( (commentBoxLeft + 8) / parseInt( $("#audio-inner #comments-layer").css("width") )) * 100;

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
                    'asset_type': 'audio',
                    'comment': comment,
                    'time': currentTime,
                    'asset_id': audioID,
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
                        $(".comment-field .loader-container").hide();
                        $(".comment-field button").show();
                    }

                    // If the comment has been added
                    else {

                        // Get the comment ID
                        var APICommentID = obj.comment_id;
                        var comment_author = obj.user;

                        if(comment_author == null)
                            comment_author = author;

                        // Add a marker on the audio player
                        var marker = '<img src="'+iconMarker+'" class="marker" id="marker-'+nbComments+'" alt="'+backAudioJsTextSeeTheComment+'" title="'+backAudioJsTextSeeTheComment+'" data-comment-id="'+nbComments+'" data-api-comment-id="'+APICommentID+'" style="left: '+markerLeft+'%;">';
                        $("#comments-layer").append(marker);

                        // 4. Create the marker
                        var markerProgress = '<div class="marker-progress" id="marker-progress-'+nbComments+'" data-time="'+currentTime+'" data-comment-id="'+nbComments+'" data-api-comment-id="'+APICommentID+'" style="left: '+markerProgressLeft+'%;"></div>';
                        $("#content #audio #audio-progress-bar").append(markerProgress);

                        // Define the timestamp
                        var timestampHours = Math.floor(currentTime / (60 * 60));
                        var timestampMinutes = Math.floor( (currentTime - (timestampHours * 60 * 60)) / 60 );
                        var timestampSeconds = Math.round( currentTime - (timestampMinutes * 60) );

                        if(timestampHours < 10)
                            timestampHours = "0" + timestampHours;
                        if(timestampMinutes < 10)
                            timestampMinutes = "0" + timestampMinutes;
                        if(timestampSeconds < 10)
                            timestampSeconds = "0" + timestampSeconds;
                        var timestamp = timestampHours + ":" + timestampMinutes + ":" + timestampSeconds;

                        // Define the author and ID
                        if(userID != null) {
                            var authorID = userID;
                        }
                        else if(contactID != null) {
                            var authorID = contactID;
                        }

                        // Display the comment in the sidebar
                        var commentHTML = '<div class="comment" id="comment-'+APICommentID+'" data-comment-id="'+nbComments+'" data-time="'+currentTime+'" data-api-comment-id="'+APICommentID+'" data-comment-author-type="'+authorType+'" data-comment-author-id="'+authorID+'">';

                        if(isOwner == true) {
                            commentHTML += '<div class="mark-as-done">';
                            commentHTML += backAudioJsTextResolved;
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
                        commentHTML += '<div class="name"><span class="timestamp">'+timestamp+'</span><span class="pipe"> | </span><span class="comment-author">'+comment_author+'</span></div>';
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
                        commentHTML += backAudioJsTextThereWasOneMinute;
                        commentHTML += '<div class="separator">•</div>';
                        commentHTML += '<a href="#" class="answer-to-comment" data-target="comment-'+APICommentID+'">';
                        commentHTML += backAudioJsTextAnswer;
                        commentHTML += '</a>';
                        commentHTML += '</div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="clear"></div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="answers-container">';
                        commentHTML += '</div>';
                        commentHTML += '<div class="comment-answer-field">';
                        commentHTML += '<textarea placeholder="'+backAudioJsTextTypeInAnswer+'..."></textarea>';
                        commentHTML += '<button class="post-comment-answer" data-target="comment-'+APICommentID+'">'+backAudioJsTextSend+'</button>';
                        commentHTML += '<div class="loader-container">';
                        commentHTML += '<div class="loader-pulse">';
                        commentHTML += backAudioJsTextLoading+'...';
                        commentHTML += '</div>';
                        commentHTML += '</div>';
                        commentHTML += '<div class="clear"></div>';
                        commentHTML += '</div>';
                        commentHTML += '</div><!-- ./Comment -->';

                        $("#comments #comments-container").append(commentHTML);

                        // Display the number of comments in the header of the section
                        if(nbComments > 1)
                            $("#comments .header #nb-comments").empty().text(nbComments+" "+backAudioJsTextComments);
                        else
                            $("#comments .header #nb-comments").empty().text(nbComments+" "+backAudioJsTextComment);

                        // Empty the textarea
                        $("#audio-inner #comments-layer .comment-box textarea").val("");
                        $("#audio-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").empty();

                        // Hide the comment box
                        $("#audio-inner #comments-layer .comment-box").fadeOut();

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
                        updateListOfCommentsForButtons(audioID, 'audio');
                        setTimeout(function() {
                            updatePreviousNextButtonsValues(APICommentID);
                        }, 500);

                        // Reset the position of the comment box
                        setTimeout(function() {
                            $("#audio-inner #comments-layer .comment-box").removeClass("right").css("left", "").css("right", "");
                        }, 500);
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
