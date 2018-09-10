// Function which allows the user to change the name of the image
function editImageDescription() {

    var currentImageDescription;

    // When the user clicks on the button
    $(document).on("click", "#image-description.editable .description-content", function() {

        // Get the actual name of the image
        currentImageDescription = $("#image-description .description-content").text().trim();

        // Change the class name to avoid conflicts
        $("#image-description").removeClass("editable").addClass("editing");

        // Replace the text by a textarea
        var textareaToEditImageDescription = '<textarea name="description" id="edit-image-description" placeholder="'+backImageJsTextAddDescription+'">'+br2nl(currentImageDescription)+'</textarea>';
        textareaToEditImageDescription += '<div>';
        textareaToEditImageDescription += '<button type="submit" name="save-description">'+backImageJsTextSave+'</button>';
        textareaToEditImageDescription += '<div class="clear"></div>';
        textareaToEditImageDescription += '</div>';

        $("#image-description").empty().html(textareaToEditImageDescription);
        setTimeout(function() {
            $("#image-description textarea").addClass("ready");
        }, 200);

        // Set the focus on the textarea
        setTimeout(function() {

            // Get the the length of the text
            imageDescriptionLength = br2nl(currentImageDescription).length;

            // Focus and position the cursor
            $("#image-description textarea").focus();
            $("#image-description textarea")[0].setSelectionRange(imageDescriptionLength, imageDescriptionLength);
        }, 200);

    });

    // When the user presses the button, saves the description
    $(document).on("click", "#image-description button", function () {

        saveImageDescription(currentImageDescription);
    });

    // When the user clicks out of the textarea, discard the changes if the textarea has been dynamically added
    $(document).on("click", "body", function(e) {

        if( $("#image-description textarea").is(":visible") ) {
            if (e.target.id == "edit-image-description" || $(e.target).parents("#edit-image-description").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#image-description textarea").hasClass("ready") ) {
                    var imageDescriptionHtml = '<div class="description-content">'+nl2br( sanitizeText(currentImageDescription) )+'</div>';
                    $("#image-description").empty().html(imageDescriptionHtml).removeClass("editing").addClass("editable");
                }
            }
        }

    });

    // If the user presses the ESC key, remove the texteare if it has been dynamically added
}


// Function which allows the user to change the description of the image
function saveImageDescription(originalImageDescription) {

    // Display the loader and hide the submit button
    $("#image-description button").hide();
    $("#image-description .loader-container").show();

    // Get the new description
    var newImageDescription = $("#image-description textarea").val();

    // Change the \n with <br>
    newImageDescription = nl2br( sanitizeText(newImageDescription) );

    // Send the request to save the new name of the image
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': 'image',
            'asset_id': imageID,
            'description': newImageDescription
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new description
                var newimageDescriptionHtml = '<div class="description-content">'+newImageDescription+'</div>';
                $("#image-description").empty().html(newimageDescriptionHtml);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#image-description").append(savedSuccessMarker).addClass("saved");
                $("#image-description .changes-saved").fadeIn();

                setTimeout(function(){
                    $("#image-description .changes-saved").fadeOut();

                    // Remove the marker
                    setTimeout(function(){
                        $("#image-description").removeClass("saved");
                        $("#image-description .changes-saved").remove();
                    }, 500);
                }, 2000);
            }
            else {

                // Remove the input field and display the original description
                $("#image-description").empty().text(originalImageDescription);
            }

            // Reset the classes
            $("#image-description").removeClass("editing").addClass("editable");

            // Hide the loader and display the submit button
            $("#image-description .loader-container").hide();
            $("#image-description button").show();
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);

            // Hide the loader and display the submit button
            $("#image-description .loader-container").hide();
            $("#image-description button").show();
        }
    });
}
