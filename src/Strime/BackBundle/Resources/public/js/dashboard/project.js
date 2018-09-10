$(document).ready(function() {

	// Edit the name of the project
	editProjectName();


    // Function which allows the user to change the name of the project
    function editProjectName() {

    	var currentProjectName;

    	// When the user clicks on the button
    	$(document).on("click", "body.project h1.editable", function() {

    		// Get the actual name of the project
    		currentProjectName = $("body.project h1").text().trim();

    		// Change the class name to avoid conflicts
			$("body.project h1").removeClass("editable").addClass("editing");

    		// Replace the text by an input
    		var inputToEditProjectName = '<input type="text" name="edit_project_name" value="'+currentProjectName+'" id="edit-project-name">';
    		$("body.project h1").empty().html(inputToEditProjectName);
    		setTimeout(function() {
    			$("body.project h1 input").addClass("ready");
    		}, 200);

    		// Set the focus on the input field
    		setTimeout(function() {

    			// Get the the length of the text
    			var assetProjectLength = currentProjectName.length;

    			// Focus and position the cursor
    			$("body.project h1 input").focus();
    			$("body.project h1 input")[0].setSelectionRange(assetProjectLength, assetProjectLength);
    		}, 200);

    	});

    	// When the user presses the enter key, save the result
		$(document).on("keyup", window, function (e) {

			if (e.which == 13) {

				// Check that the focus is on the input field to edit the name of the project
				if( $("body.project h1 input").is(":focus") ) {
					saveProjectName(currentProjectName);
				}
			}
    	});

    	$(document).on("click", "body", function(e) {

    		if( $("body.project h1 input").is(":visible") ) {
    			if (e.target.id == "edit-project-name" || $(e.target).parents("#edit-project-name").size()) {
    				// The click happened inside the input.
    				// Do nothing
		        }
		        else {
		        	if( $("body.project h1 input").hasClass("ready") ) {
		        		$("body.project h1").empty().text(currentProjectName).removeClass("editing").addClass("editable");
		        	}
		        }
    		}

    	});
    }


    // Function which allows the user to change the name of the project
    function saveProjectName(originalProjectName) {

    	var newProjectName = $("body.project h1 input").val();

    	// Send the request to save the new name of the project
		$.ajax({
			type: 'POST',
			url: ajaxEditProjectURL,
			data: {
				'project_id': projectID,
				'name': newProjectName
			},
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the comment has not been updated
				if(obj.response_code == 200) {

					// Remove the input field and display the new name
	    			$("body.project h1").empty().text(newProjectName);

	    			// Display a marker showing that the changes have been saved.
	    			var savedSuccessMarker = '<div class="changes-saved"></div>';
	    			$("body.project h1").append(savedSuccessMarker).addClass("saved");
	    			$("body.project h1 .changes-saved").fadeIn();

					// Change the title of the page
					$("html head title").empty().text("Strime | " + newProjectName);

	    			setTimeout(function(){
	    				$("body.project h1 .changes-saved").fadeOut();

	    				// Remove the marker
	    				setTimeout(function(){
							$("body.project h1").removeClass("saved");
		    				$("body.project h1 .changes-saved").remove();
		    			}, 500);
	    			}, 2000);
				}
				else {

					// Remove the input field and display the original name
					$("body.project h1").empty().text(originalProjectName);
				}

	    		// Reset the classes
				$("body.project h1").removeClass("editing").addClass("editable");
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
    }

});
