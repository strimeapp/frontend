/*
 *
 * If the user drags and drops a marker
 *
 */

// Function which allow elements to be dropped into himself
function allowDropMarker(ev) {
    ev.preventDefault();
}

// When an image is dragged
function dragMarker(ev) {

    // Set the draggable element ID
    var markerID = ev.target.id;

    // Transfer the parent ID
    ev.dataTransfer.setData("markerId", markerID);

	// Get the original position
	var markerPosition = $("#image #image-inner #comments-layer .marker#"+markerID).position();
	var markerTop = parseInt( markerPosition.top );
	var markerLeft = parseInt( markerPosition.left );

	// Set the position of the marker in percentage
	markerOriginalPositionTop = parseFloat( markerTop / parseInt( $("#image-inner #comments-layer").css("height") )) * 100;
	markerOriginalPositionLeft = parseFloat( markerLeft / parseInt( $("#image-inner #comments-layer").css("width") )) * 100;

	// Save the original position
	ev.dataTransfer.setData("markerOriginalPositionTop", markerOriginalPositionTop);
	ev.dataTransfer.setData("markerOriginalPositionLeft", markerOriginalPositionLeft);

    // Add a class to the parendID
	if((document.getElementById(markerID) !== null) && (document.getElementById(markerID).getAttribute('draggable') == 'true')) {
    	$(".marker#"+markerID).addClass("draggable-marker");
	}

    // Generate a click on this marker to activate it on the image and in the sidebar.
    isDraggingMarker = true;
    $(".marker#"+markerID).trigger("click");
}

function dragEnterZoneMarker(ev) {
    ev.preventDefault();

	// Nothing special happens here, because the marker is supposed to already be in the drop zone
}

function dragLeaveZoneMarker(ev) {
    ev.preventDefault();

    // Nothing special happens here.
	// If the marker leaves the zone, we will reposition it during the drop
}

function dragEndMarker(ev) {
    ev.preventDefault();

    // Set the value of the variable to check if we are dragging the marker to false.
    isDraggingMarker = false;
}

function dropMarker(ev) {
    ev.preventDefault();
	var markerID = ev.dataTransfer.getData("markerId");

    // Set the value of the variable to check if we are dragging the marker to false.
    isDraggingMarker = false;

	if((document.getElementById(markerID) !== null) && (document.getElementById(markerID).getAttribute('draggable') == 'true')) {
	    // Collect the data transfered
		var markerOriginalPositionLeft = ev.dataTransfer.getData("markerOriginalPositionLeft");
		var markerOriginalPositionTop = ev.dataTransfer.getData("markerOriginalPositionTop");
		var commentID = $(".marker#"+markerID).attr("data-api-comment-id");
		var finalMarkerPositionTop = null;
		var finalMarkerPositionLeft = null;

		// Get the position of the marker
		var divOffset = $("#comments-layer").offset();
		var mousePositionTop = ev.pageY - divOffset.top - ( $(".marker#"+markerID).outerHeight() / 2);
		var mousePositionLeft = ev.pageX - divOffset.left - ( $(".marker#"+markerID).outerWidth() / 2);

		// Set the position of the marker in percentage
		finalMarkerPositionTop = parseFloat( mousePositionTop / parseInt( $("#image-inner #comments-layer").css("height") )) * 100;
		finalMarkerPositionLeft = parseFloat( mousePositionLeft / parseInt( $("#image-inner #comments-layer").css("width") )) * 100;

		// If the marker is out of the boundaries of the comment zone, reposition it
		if(finalMarkerPositionTop > 100) {
			finalMarkerPositionTop = parseFloat( parseInt( $("#image-inner #comments-layer").css("height")) -  parseInt( $("#image #image-inner #comments-layer .marker#"+markerID).css("height")) );
			finalMarkerPositionTop = parseFloat( finalMarkerPositionTop / parseInt( $("#image-inner #comments-layer").css("height") )) * 100;
		}
		else if(finalMarkerPositionTop < 0) {
			finalMarkerPositionTop = 0;
		}
		if(finalMarkerPositionLeft > 100) {
			finalMarkerPositionLeft = parseFloat( parseInt( $("#image-inner #comments-layer").css("width")) -  parseInt( $("#image #image-inner #comments-layer .marker#"+markerID).css("width")) );
			finalMarkerPositionLeft = parseFloat( finalMarkerPositionLeft / parseInt( $("#image-inner #comments-layer").css("width") )) * 100;
		}
		else if(finalMarkerPositionLeft < 0) {
			finalMarkerPositionLeft = 0;
		}
		$("#image #image-inner #comments-layer .marker#"+markerID).css("left", finalMarkerPositionLeft+'%').css("top", finalMarkerPositionTop+'%');

		$(".marker#"+markerID).removeClass('draggable-marker');

	    var intervalUpdateMarkerPosition = setInterval(function() {
			if((finalMarkerPositionTop != null) && (finalMarkerPositionLeft != null)) {
				saveNewMarkerPosition(commentID, markerID, finalMarkerPositionLeft, finalMarkerPositionTop, markerOriginalPositionLeft, markerOriginalPositionTop);
				stopIntervalUpdateMarkerPosition(intervalUpdateMarkerPosition);
			}
		}, 200);
	}
}


function saveNewMarkerPosition(commentID, markerID, finalMarkerPositionLeft, finalMarkerPositionTop, markerOriginalPositionLeft, markerOriginalPositionTop) {

	$.ajax({
		type: 'POST',
		url: ajaxEditCommentURL,
		data: {
            'asset_type': 'image',
			'comment_id': commentID,
			'top': finalMarkerPositionTop,
			'left': finalMarkerPositionLeft
		},
		success: function(data, textStatus, jqXHR){

			// Get the response
			var obj = $.parseJSON(data);
			// console.log(obj);

			// Check the status
			// If the comment has not been added
			if(obj.response_code != 200) {

				// Reposition the marker
				$("#image #image-inner #comments-layer .marker#"+markerID).css("left", markerOriginalPositionLeft+'%').css("left", markerOriginalPositionTop+'%');

				// Display an error message saying that the new position has not been saved
				$("#comments #comments-container #edit-comment-error-message").fadeIn();
				setTimeout(function() {
					$("#comments #comments-container #edit-comment-error-message").fadeOut();
				}, 5000);
			}

			// If the comment has been updated
			else {

				// Do nothing special
			}
		},
		error: function(data, textStatus, jqXHR){
			// console.log(data);

			// Reposition the marker
			$("#image #image-inner #comments-layer .marker#"+markerID).css("left", markerOriginalPositionLeft+'%').css("left", markerOriginalPositionTop+'%');

			// Display an error message saying that the new position has not been saved
			$("#comments #comments-container #edit-comment-error-message").fadeIn();
			setTimeout(function() {
				$("#comments #comments-container #edit-comment-error-message").fadeOut();
			}, 5000);
		}
	});
}

function stopIntervalUpdateMarkerPosition(intervalUpdateMarkerPosition) {
	clearInterval(intervalUpdateMarkerPosition);
}
