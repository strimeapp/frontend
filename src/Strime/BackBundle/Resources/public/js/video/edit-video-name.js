// Function which allows the user to change the name of the video
function editVideoName() {

    var currentVideoName;

    // When the user clicks on the button
    $(document).on("click", "#asset-meta h1.editable", function() {

        // Get the actual name of the video
        currentVideoName = $("#asset-meta h1").text().trim();

        // Change the class name to avoid conflicts
        $("#asset-meta h1").removeClass("editable").addClass("editing");

        // Replace the text by an input
        var inputToEditVideoName = '<input type="text" name="edit_video_name" value="'+currentVideoName+'" id="edit-video-name">';
        $("#asset-meta h1").empty().html(inputToEditVideoName);
        setTimeout(function() {
            $("#asset-meta h1 input").addClass("ready");
        }, 200);

        // Set the focus on the input field
        setTimeout(function() {

            // Get the the length of the text
            videoNameLength = currentVideoName.length;

            // Focus and position the cursor
            $("#asset-meta h1 input").focus();
            $("#asset-meta h1 input")[0].setSelectionRange(videoNameLength, videoNameLength);
        }, 200);

    });

    // When the user presses the enter key, save the result
    $(document).on("keyup", window, function (e) {

        if (e.which == 13) {

            // Check that the focus is on the input field to edit the name of the video
            if( $("#asset-meta h1 input").is(":focus") ) {
                saveVideoName(currentVideoName);
            }
        }
    });

    $(document).on("click", "body", function(e) {

        if( $("#asset-meta h1 input").is(":visible") ) {
            if (e.target.id == "edit-video-name" || $(e.target).parents("#edit-video-name").size()) {
                // The click happened inside the input.
                // Do nothing
            }
            else {
                if( $("#asset-meta h1 input").hasClass("ready") ) {
                    $("#asset-meta h1").empty().text(currentVideoName).removeClass("editing").addClass("editable");
                }
            }
        }

    });
}


// Function which allows the user to change the name of the video
function saveVideoName(originalVideoName) {

    var newVideoName = $("#asset-meta h1 input").val();

    // Send the request to save the new name of the video
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: {
            'asset_type': 'video',
            'asset_id': videoID,
            'name': newVideoName
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the comment has not been updated
            if(obj.response_code == 200) {

                // Remove the input field and display the new name
                $("#asset-meta h1").empty().text(newVideoName);

                // Display a marker showing that the changes have been saved.
                var savedSuccessMarker = '<div class="changes-saved"></div>';
                $("#asset-meta h1").append(savedSuccessMarker).addClass("saved");
                $("#asset-meta h1 .changes-saved").fadeIn();

                // Change the title of the page
                var backVideoJsTextNewTitle = backVideoJsTextTitle.replace('*|VIDEO_NAME|*', newVideoName);
                $("html head title").empty().text(backVideoJsTextNewTitle + " | Strime");

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
                $("#asset-meta h1").empty().text(originalVideoName);
            }

            // Reset the classes
            $("#asset-meta h1").removeClass("editing").addClass("editable");
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
}
