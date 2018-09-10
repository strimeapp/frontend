$(document).ready(function() {

    // Allow the edition of the names of projects or assets
    editEltName();

    function editEltName() {

        var currentEltName;
        var originalEltName;
        var currentEltID;
        var currentEltFullID;
        var eltIsProject;
        var currentlyEditing = false;

        // If the user wants to change the name of an asset or a project
        $(document).on("click", "#assets .asset .asset-name", function(e) {

            e.preventDefault();

            // Check if we are not already editing the element
            if( !$(this).hasClass("editing") && (currentlyEditing == false) ) {

                currentlyEditing == true;

                // Get the actual name of the element
                currentEltName = $(this).text().trim();
                originalEltName = currentEltName;

                // Get the ID of the elt
                currentEltID = $(this).attr("data-elt-id");

                // Check if it's a project of an asset
                if( $(this).closest(".asset").hasClass("project") ) {
                    eltIsProject = true;
                    currentEltFullID = "project-" + currentEltID;
                }
                else {
                    eltIsProject = false;
                    currentEltFullID = "asset-" + currentEltID;
                }

                // Change the class name to avoid conflicts
                $(this).addClass("editing");

                // Replace the text by an input
                var inputToEditEltName = '<input type="text" name="edit_elt_name" value="'+currentEltName+'" id="edit-elt-name">';
                $("#assets .asset#"+currentEltFullID+" .asset-name").empty().html(inputToEditEltName);
                setTimeout(function() {
                    $("#assets .asset#"+currentEltFullID+" .asset-name input").addClass("ready");
                }, 200);

                // Remove the drag and drop abilities of the element
                $("#assets .asset#"+currentEltFullID+" a").removeAttr("ondragstart").removeAttr("ondragend", "").attr("draggable", "false");

                // Set the focus on the input field
                setTimeout(function() {

                    // Get the the length of the text
                    currentEltNameLength = currentEltName.length;

                    // Focus and position the cursor
                    $("#assets .asset#"+currentEltFullID+" .asset-name input").focus();
                    $("#assets .asset#"+currentEltFullID+" .asset-name input")[0].setSelectionRange(currentEltNameLength, currentEltNameLength);
                }, 200);

            }

        });


        // Detect the keys used by the user
        $(document).on("keyup", window, function (e) {

            // When the user presses the enter key, save the result
            if (e.which === 13) {

                // Check that the focus is on the input field to edit the name of the asset
                if( $("#assets .asset#"+currentEltFullID+" .asset-name input").is(":focus") ) {
                    saveEltName(originalEltName, currentEltID, currentEltFullID, eltIsProject);
                }
            }

            // If the user clicks on the ESC key, remove any input field
            else if (e.which === 27) {

                if( $("#assets .asset#"+currentEltFullID+" .asset-name input").is(":visible") ) {
                    if( $("#assets .asset#"+currentEltFullID+" .asset-name input").hasClass("ready") ) {
                        $("#assets .asset#"+currentEltFullID+" .asset-name").empty().text(originalEltName).removeClass("editing");
                        currentlyEditing = false;
                    }

                    // Reset the drag and drop capabilities
                    $("#assets .asset#"+currentEltFullID+" a").attr("ondragstart", "dragAsset(event)").attr("ondragend", "dragEndAsset(event)").attr("draggable", "true");
                }
            }
        });

        // If the user clicks somewhere in the page, deactivate the input
        $(document).on("click", "body", function(e) {

            if( $("#assets .asset#"+currentEltFullID+" .asset-name input").is(":visible") ) {
                if (e.target.id == "edit-elt-name" || $(e.target).parents("#edit-elt-name").size()) {
                    // The click happened inside the input.
                    // Do nothing
                }
                else {
                    if( $("#assets .asset#"+currentEltFullID+" .asset-name input").hasClass("ready") ) {
                        $("#assets .asset#"+currentEltFullID+" .asset-name").empty().text(originalEltName).removeClass("editing");
                        currentlyEditing = false;

                        // Reset the drag and drop capabilities
                        $("#assets .asset#"+currentEltFullID+" a").attr("ondragstart", "dragAsset(event)").attr("ondragend", "dragEndAsset(event)").attr("draggable", "true");
                    }
                }
            }

        });
    }


    // Function which allows the user to change the name of the asset
    function saveEltName(originalEltName, currentEltID, currentEltFullID, eltIsProject) {

    	var newEltName = $("#assets .asset#"+currentEltFullID+" .asset-name input").val();

        // Set the URL to which we will send the request
        if( eltIsProject == true) {
            var finalURL = ajaxEditProjectURL;
            var data = { 'project_id': currentEltID, 'name': newEltName }
        }
        else {
            var finalURL = ajaxEditAssetURL;
            if($("#assets .asset#"+currentEltFullID).hasClass("video")) {
                var assetType = "video";
            }
            else if($("#assets .asset#"+currentEltFullID).hasClass("image")) {
                var assetType = "image";
            }
            else if($("#assets .asset#"+currentEltFullID).hasClass("audio")) {
                var assetType = "audio";
            }
            else {
                var assetType = "video";
            }
            var data = { 'asset_id': currentEltID, 'name': newEltName, 'asset_type': assetType }
        }

    	// Send the request to save the new name of the asset
		$.ajax({
			type: 'POST',
			url: finalURL,
			data: data,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the asset has been updated
				if(obj.response_code == 200) {

					// Remove the input field and display the new name
	    			$("#assets .asset#"+currentEltFullID+" .asset-name").empty().text(newEltName);

	    			// Display a marker showing that the changes have been saved.
	    			var savedSuccessMarker = '<div class="changes-saved"></div>';
	    			$("#assets .asset#"+currentEltFullID+" .asset-name").addClass("saved");
	    			$("#assets .asset#"+currentEltFullID+" .asset-name").fadeIn();

	    			setTimeout(function(){
	    				$("#assets .asset#"+currentEltFullID+" .asset-name").removeClass("saved");
	    			}, 2000);
				}

                // If the asset has NOT been updated
				else {

					// Remove the input field and display the original name
					$("#assets .asset#"+currentEltFullID+" .asset-name").empty().text(originalEltName);
				}

	    		// Reset the classes
				$("#assets .asset#"+currentEltFullID+" .asset-name").removeClass("editing");
                currentlyEditing = false;

                // Reset the drag and drop capabilities
                $("#assets .asset#"+currentEltFullID+" a").attr("ondragstart", "dragAsset(event)").attr("ondragend", "dragEndAsset(event)").attr("draggable", "true");
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
    }

});
