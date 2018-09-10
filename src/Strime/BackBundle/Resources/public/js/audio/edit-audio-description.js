// Function which allows the user to change the name of the audio
function editAudioDescription() {

    var currentAudioDescription;

    // When the user clicks on the button
    $(document).on("click", "#audio-description.editable .description-content", function() {

        // Get the actual name of the audio
        currentAudioDescription = $("#audio-description .description-content").text().trim();

        // Change the class name to avoid conflicts
        $("#audio-description").removeClass("editable").addClass("editing");

        // Replace the text by a textarea
        var textareaToEditAudioDescription = '<textarea name="description" id="edit-audio-description" placeholder="'+backAudioJsTextAddDescription+'">'+br2nl(currentAudioDescription)+'</textarea>';
        textareaToEditAudioDescription += '<div>';
        textareaToEditAudioDescription += '<button type="submit" name="save-description">'+backAudioJsTextSave+'</button>';
        textareaToEditAudioDescription += '<div class="clear"></div>';
        textareaToEditAudioDescription += '</div>';

        $("#audio-description").empty().html(textareaToEditAudioDescription);
        setTimeout(function() {
            $("#audio-description textarea").addClass("ready");
        }, 200);

        // Set the focus on the textarea
        setTimeout(function() {

            // Get the the length of the text
            audioDescriptionLength = br2nl(currentAudioDescription).length;

            // Focus and position the cursor
            $("#audio-description textarea").focus();
            $("#audio-description textarea")[0].setSelectionRange(audioDescriptionLength, audioDescriptionLength);
        }, 200);

    });

    // When the user presses the button, saves the description
    $(document).on("click", "#audio-description button", function () {

        saveAudioDescription(currentAudioDescription);
    });

    // When the user clicks out of the textarea, discard the changes if the textarea has been dynamically added
    $(document).on("click", "body", function(e) {

        if( $("#audio-description textarea").is(":visible") ) {
            if (e.target.id == "edit-audio-description" || $(e.target).parents("#edit-audio-description").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#audio-description textarea").hasClass("ready") ) {
                    var audioDescriptionHtml = '<div class="description-content">'+nl2br( sanitizeText(currentAudioDescription) )+'</div>';
                    $("#audio-description").empty().html(audioDescriptionHtml).removeClass("editing").addClass("editable");
                }
            }
        }

    });

    // If the user presses the ESC key, remove the texteare if it has been dynamically added
}


// Function which allows the user to change the description of the audio
function saveAudioDescription(originalAudioDescription) {

    // Display the loader and hide the submit button
    $("#audio-description button").hide();
    $("#audio-description .loader-container").show();

    // Get the new description
    var newAudioDescription = $("#audio-description textarea").val();
    // console.log(newAudioDescription);

    // Change the \n with <br>
    newAudioDescription = nl2br( sanitizeText(newAudioDescription) );

    // Send the request to save the new name of the audio
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': assetType,
            'asset_id': audioID,
            'description': newAudioDescription
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new description
                var newAudioDescriptionHtml = '<div class="description-content">'+newAudioDescription+'</div>';
                $("#audio-description").empty().html(newAudioDescriptionHtml);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#audio-description").append(savedSuccessMarker).addClass("saved");
                $("#audio-description .changes-saved").fadeIn();

                setTimeout(function(){
                    $("#audio-description .changes-saved").fadeOut();

                    // Remove the marker
                    setTimeout(function(){
                        $("#audio-description").removeClass("saved");
                        $("#audio-description .changes-saved").remove();
                    }, 500);
                }, 2000);
            }
            else {

                // Remove the input field and display the original description
                $("#audio-description").empty().text(originalAudioDescription);
            }

            // Reset the classes
            $("#audio-description").removeClass("editing").addClass("editable");

            // Hide the loader and display the submit button
            $("#audio-description .loader-container").hide();
            $("#audio-description button").show();
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);

            // Hide the loader and display the submit button
            $("#audio-description .loader-container").hide();
            $("#audio-description button").show();
        }
    });
}
