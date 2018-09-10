// Function which allows the user to change the name of the video
function editVideoDescription() {

    var currentVideoDescription;

    // When the user clicks on the button
    $(document).on("click", "#video-description.editable .description-content", function() {

        // Get the actual name of the video
        currentVideoDescription = $("#video-description .description-content").text().trim();

        // Change the class name to avoid conflicts
        $("#video-description").removeClass("editable").addClass("editing");

        // Replace the text by a textarea
        var textareaToEditVideoDescription = '<textarea name="description" id="edit-video-description" placeholder="'+backVideoJsTextAddDescription+'">'+br2nl(currentVideoDescription)+'</textarea>';
        textareaToEditVideoDescription += '<div>';
        textareaToEditVideoDescription += '<button type="submit" name="save-description">'+backVideoJsTextSave+'</button>';
        textareaToEditVideoDescription += '<div class="clear"></div>';
        textareaToEditVideoDescription += '</div>';

        $("#video-description").empty().html(textareaToEditVideoDescription);
        setTimeout(function() {
            $("#video-description textarea").addClass("ready");
        }, 200);

        // Set the focus on the textarea
        setTimeout(function() {

            // Get the the length of the text
            videoDescriptionLength = br2nl(currentVideoDescription).length;

            // Focus and position the cursor
            $("#video-description textarea").focus();
            $("#video-description textarea")[0].setSelectionRange(videoDescriptionLength, videoDescriptionLength);
        }, 200);

    });

    // When the user presses the button, saves the description
    $(document).on("click", "#video-description button", function () {

        saveVideoDescription(currentVideoDescription);
    });

    // When the user clicks out of the textarea, discard the changes if the textarea has been dynamically added
    $(document).on("click", "body", function(e) {

        if( $("#video-description textarea").is(":visible") ) {
            if (e.target.id == "edit-video-description" || $(e.target).parents("#edit-video-description").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#video-description textarea").hasClass("ready") ) {
                    var videoDescriptionHtml = '<div class="description-content">'+nl2br( sanitizeText(currentVideoDescription) )+'</div>';
                    $("#video-description").empty().html(videoDescriptionHtml).removeClass("editing").addClass("editable");
                }
            }
        }

    });

    // If the user presses the ESC key, remove the texteare if it has been dynamically added
}


// Function which allows the user to change the description of the video
function saveVideoDescription(originalVideoDescription) {

    // Display the loader and hide the submit button
    $("#video-description button").hide();
    $("#video-description .loader-container").show();

    // Get the new description
    var newVideoDescription = $("#video-description textarea").val();
    // console.log(newVideoDescription);

    // Change the \n with <br>
    newVideoDescription = nl2br( sanitizeText(newVideoDescription) );

    // Send the request to save the new name of the video
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': 'video',
            'asset_id': videoID,
            'description': newVideoDescription
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new description
                var newVideoDescriptionHtml = '<div class="description-content">'+newVideoDescription+'</div>';
                $("#video-description").empty().html(newVideoDescriptionHtml);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#video-description").append(savedSuccessMarker).addClass("saved");
                $("#video-description .changes-saved").fadeIn();

                setTimeout(function(){
                    $("#video-description .changes-saved").fadeOut();

                    // Remove the marker
                    setTimeout(function(){
                        $("#video-description").removeClass("saved");
                        $("#video-description .changes-saved").remove();
                    }, 500);
                }, 2000);
            }
            else {

                // Remove the input field and display the original description
                $("#video-description").empty().text(originalVideoDescription);
            }

            // Reset the classes
            $("#video-description").removeClass("editing").addClass("editable");

            // Hide the loader and display the submit button
            $("#video-description .loader-container").hide();
            $("#video-description button").show();
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);

            // Hide the loader and display the submit button
            $("#video-description .loader-container").hide();
            $("#video-description button").show();
        }
    });
}
