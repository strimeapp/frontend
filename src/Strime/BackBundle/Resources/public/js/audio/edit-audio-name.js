// Function which allows the user to change the name of the audio file
function editAudioName() {

    var currentAudioName;

    // When the user clicks on the button
    $(document).on("click", "#asset-meta h1.editable", function() {

        // Get the actual name of the audio
        currentAudioName = $("#asset-meta h1").text().trim();

        // Change the class name to avoid conflicts
        $("#asset-meta h1").removeClass("editable").addClass("editing");

        // Replace the text by an input
        var inputToEditAudioName = '<input type="text" name="edit_audio_name" value="'+currentAudioName+'" id="edit-audio-name">';
        $("#asset-meta h1").empty().html(inputToEditAudioName);
        setTimeout(function() {
            $("#asset-meta h1 input").addClass("ready");
        }, 200);

        // Set the focus on the input field
        setTimeout(function() {

            // Get the the length of the text
            audioNameLength = currentAudioName.length;

            // Focus and position the cursor
            $("#asset-meta h1 input").focus();
            $("#asset-meta h1 input")[0].setSelectionRange(audioNameLength, audioNameLength);
        }, 200);

    });

    // When the user presses the enter key, save the result
    $(document).on("keyup", window, function (e) {

        if (e.which == 13) {

            // Check that the focus is on the input field to edit the name of the audio
            if( $("#asset-meta h1 input").is(":focus") ) {
                saveAudioName(currentAudioName);
            }
        }
    });

    $(document).on("click", "body", function(e) {

        if( $("#asset-meta h1 input").is(":visible") ) {
            if (e.target.id == "edit-audio-name" || $(e.target).parents("#edit-audio-name").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#asset-meta h1 input").hasClass("ready") ) {
                    $("#asset-meta h1").empty().text(currentAudioName).removeClass("editing").addClass("editable");
                }
            }
        }

    });
}


// Function which allows the user to change the name of the audio
function saveAudioName(originalAudioName) {

    var newAudioName = $("#asset-meta h1 input").val();

    // Send the request to save the new name of the audio
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': assetType,
            'asset_id': audioID,
            'name': newAudioName
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new name
                $("#asset-meta h1").empty().text(newAudioName);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#asset-meta h1").append(savedSuccessMarker).addClass("saved");
                $("#asset-meta h1 .changes-saved").fadeIn();

                // Change the title of the page
                var backAudioJsTextNewTitle = backAudioJsTextTitle.replace('*|AUDIO_NAME|*', newAudioName);
                $("html head title").empty().text(backAudioJsTextNewTitle + " | Strime");

                setTimeout(function(){
                    $("#asset-meta h1 .changes-saved").fadeOut();

                    // Remove the marker
                    setTimeout(function(){
                        $("#asset-meta h1").removeClass("saved");
                        $("#asset-meta h1 .changes-saved").remove();
                    }, 500);
                }, 2000);
            }
            else {

                // Remove the input field and display the original name
                $("#asset-meta h1").empty().text(originalAudioName);
            }

            // Reset the classes
            $("#asset-meta h1").removeClass("editing").addClass("editable");
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
}
