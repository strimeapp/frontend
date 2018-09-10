// Function which allows the user to change the name of the image
function editImageName() {

    var currentImageName;

    // When the user clicks on the button
    $(document).on("click", "#asset-meta h1.editable", function() {

        // Get the actual name of the image
        currentImageName = $("#asset-meta h1").text().trim();

        // Change the class name to avoid conflicts
        $("#asset-meta h1").removeClass("editable").addClass("editing");

        // Replace the text by an input
        var inputToEditImageName = '<input type="text" name="edit_image_name" value="'+currentImageName+'" id="edit-image-name">';
        $("#asset-meta h1").empty().html(inputToEditImageName);
        setTimeout(function() {
            $("#asset-meta h1 input").addClass("ready");
        }, 200);

        // Set the focus on the input field
        setTimeout(function() {

            // Get the the length of the text
            imageNameLength = currentImageName.length;

            // Focus and position the cursor
            $("#asset-meta h1 input").focus();
            $("#asset-meta h1 input")[0].setSelectionRange(imageNameLength, imageNameLength);
        }, 200);

    });

    // When the user presses the enter key, save the result
    $(document).on("keyup", window, function (e) {

        if (e.which == 13) {

            // Check that the focus is on the input field to edit the name of the image
            if( $("#asset-meta h1 input").is(":focus") ) {
                saveImageName(currentImageName);
            }
        }
    });

    $(document).on("click", "body", function(e) {

        if( $("#asset-meta h1 input").is(":visible") ) {
            if (e.target.id == "edit-image-name" || $(e.target).parents("#edit-image-name").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#asset-meta h1 input").hasClass("ready") ) {
                    $("#asset-meta h1").empty().text(currentImageName).removeClass("editing").addClass("editable");
                }
            }
        }

    });
}


// Function which allows the user to change the name of the image
function saveImageName(originalImageName) {

    var newImageName = $("#asset-meta h1 input").val();

    // Send the request to save the new name of the image
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': 'image',
            'asset_id': imageID,
            'name': newImageName
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new name
                $("#asset-meta h1").empty().text(newImageName);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#asset-meta h1").append(savedSuccessMarker).addClass("saved");
                $("#asset-meta h1 .changes-saved").fadeIn();

                // Change the title of the page
                var backImageJsTextNewTitle = backImageJsTextTitle.replace('*|IMAGE_NAME|*', newImageName);
                $("html head title").empty().text(backImageJsTextNewTitle + " | Strime");

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
                $("#asset-meta h1").empty().text(originalImageName);
            }

            // Reset the classes
            $("#asset-meta h1").removeClass("editing").addClass("editable");
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
}
