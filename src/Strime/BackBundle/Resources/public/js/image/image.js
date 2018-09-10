$(document).ready(function() {

	// Set the height of the image and comments containers
	resizeImageAndCommentsContainer();
	$(window).resize(function() {
		resizeImageAndCommentsContainer();
	});

	// Resize the image if the window is resized
	resizeImage();
	$(window).resize(function() {
		resizeImage();
	});

	// Allow comments
	postComment();

	// Allow answers to this comment
	answerToComment();

	// Allow the answer to be posted
	postAnswer();

	// Allow the user to click on a comment to see it on the image
	goToComment();

	// Go to the next comment
	activateGoToNextCommentButton();

	// Go to the previous comment
	activateGoToPreviousCommentButton();

	// Activate the button which globally hides all the markers on the image
	activateButtonToGloballyHideMarkersOnImage();

	// Set a comment as active when clicked
	setCommentAsActive(null);

	// Set a comment as active when its marker is clicked
	setCommentAsActiveWhenMarkerIsClicked(null);

	// Show / hide the comments sidebar
	showCommentsSidebar();

	// Set comments as done/undone.
	setCommentAsDone();

	// Populate the modal to edit a comment if the pen button is clicked.
	populateEditCommentModal();

	// Edit a comment.
	editComment();

	// Populate the modal to delete a comment.
	populateDeleteCommentModal()

	// Delete a comment.
	deleteComment();

	// Close comment box when the ESC key is pressed
	closeCommentBoxOnEscKeypress();

	// Edit the name of the image
	editImageName();

	// Edit the description of the image
	editImageDescription();

	// Reorder the comments
	reorderComments();


	// If the user clicks on the layer
	$("#image-inner #comments-layer").on("click", function(e) {

		// Check if a marker is clicked
		var clickTarget = $(e.target);
		if(clickTarget.hasClass("marker")) {
			var clickOnMarker = true;
		}
		else {
			var clickOnMarker = false;
		}

		// Get the position of the cursor in the comments layer div
		var parentOffset = $(this).offset();
		var relX = e.pageX - parentOffset.left;
		var relY = e.pageY - parentOffset.top;

		// Get the comment box position
		var commentBoxTop = parseInt( $("#image-inner #comments-layer .comment-box").css("top") );
		var commentBoxLeft = parseInt( $("#image-inner #comments-layer .comment-box").css("left") );

		// Get the dimensions of the comment box
		var commentBoxWidth = parseInt( $("#image-inner #comments-layer .comment-box").css("width") );
		var commentBoxHeight = parseInt( $("#image-inner #comments-layer .comment-box").css("height") );

		// Set the coordinates of the comment box
		var commentBoxRight = commentBoxLeft + commentBoxWidth;
		var commentBoxBottom = commentBoxTop + commentBoxHeight;

		// Check if the click happened in the comment box
		var clickInCommentBox = false;

		if((relX >= commentBoxLeft) && (relX <= commentBoxRight)
			&& (relY >= commentBoxTop) && (relY <= commentBoxBottom)) {
			clickInCommentBox = true;
		}

		// Get the trash coordinates
		var trashTop = commentBoxTop + parseInt( $("#image-inner #comments-layer .comment-box").css("paddingTop") ) + parseInt( $("#image-inner #comments-layer .comment-box .comment-box-inner").css("paddingTop") );
		var trashRight = commentBoxRight - parseInt( $("#image-inner #comments-layer .comment-box .comment-box-inner").css("paddingRight") ) - parseInt( $("#image-inner #comments-layer .comment-box").css("paddingRight") );
		var trashBottom = trashTop + parseInt( $("#image-inner #comments-layer .comment-box .comment-box-inner .trash").css("height") );
		var trashLeft = trashRight - parseInt( $("#image-inner #comments-layer .comment-box .comment-box-inner .trash").css("width") );

		// Check if the click happened on the trash
		var clickOnTrash = false;

		if((relX >= trashLeft) && (relX <= trashRight)
			&& (relY >= trashTop) && (relY <= trashBottom)) {
			clickOnTrash = true;
		}

		// If the comment box is not visible and the user didn't click on a marker, show it
		if( $("#image-inner #comments-layer .comment-box").is(":hidden") && !clickOnMarker ) {

			// Check if we are at the right of the video
			// If yes, we display the comment box on the left of the marker
			if(relX + 300 > $("#image-inner #comments-layer").width()) {
				var rightPosition = $("#image-inner #comments-layer").width() - relX - 10;
				$("#image-inner #comments-layer .comment-box").addClass("right").css("left", "").css("right", rightPosition+"px");
			}
			else {
				var leftPosition = relX - 10;
				$("#image-inner #comments-layer .comment-box").css("right", "").css("left", leftPosition+"px");
			}

			// Check if we are at the bottom of the video
			// If yes, we display the comment box over the marker
			if(relY + 225 > $("#image-inner #comments-layer").height()) {
				var bottomPosition = $("#image-inner #comments-layer").height() - relY - 10;
				$("#image-inner #comments-layer .comment-box").addClass("bottom").css("top", "").css("bottom", bottomPosition+"px");
			}
			else {
				var topPosition = relY - 10;
				$("#image-inner #comments-layer .comment-box").css("bottom", "").css("top", topPosition+"px");
			}
			$("#image-inner #comments-layer .comment-box").fadeIn();

			// Force the focus on the textarea of the comment box
			$("#image-inner #comments-layer .comment-box textarea").focus();
			$("#image-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").attr("contenteditable", true).focus();
			$("#image-inner #comments-layer .comment-box .emojionearea").addClass("focused");

			// Remove the active class from the markers
			$("#image-inner .marker").each(function(){
				$(this).removeClass("active").removeClass("inactive");
			});
			$("#comment-inner .comment").each(function(){
				$(this).removeClass("active");
			});
		}

		// If the comment box is visible
		else if( $("#image-inner #comments-layer .comment-box").is(":visible") ) {

			// If the user clicked on a marker, just hide the comment box
			if(clickOnMarker == true) {
				$("#image-inner #comments-layer .comment-box").fadeOut(function() {
					$("#image-inner #comments-layer .comment-box").removeClass("bottom").removeClass("right");
				});
			}

			// If the user clicked on an emoji, do nothing.
			else if((clickTarget.parents(".emojionearea-picker").size() == 1) || (clickTarget.hasClass("emojioneemoji"))) {

				// leave this space blank.
			}

			// Else, if the user clicked somewhere else
			else {

				// If the click was not in the comment box or was on the trash
				if((!clickInCommentBox || clickOnTrash) && !isDraggingMarker) {

					// Hide the comment box
					$("#image-inner #comments-layer .comment-box").fadeOut(function() {
						$("#image-inner #comments-layer .comment-box").removeClass("bottom").removeClass("right");
					});
				}
			}
		}

		// If the user didn't click on a marker, show the comment box
		else if(!clickOnMarker) {

			// Check if we are at the right of the video
			// If yes, we display the comment box on the left of the marker
			if(relX + 300 > $("#image-inner #comments-layer").width()) {
				var rightPosition = $("#image-inner #comments-layer").width() - relX - 10;
				$("#image-inner #comments-layer .comment-box").addClass("right").css("left", "").css("right", rightPosition+"px");
			}
			else {
				var leftPosition = relX - 10;
				$("#image-inner #comments-layer .comment-box").css("right", "").css("left", leftPosition+"px");
			}

			// Check if we are at the bottom of the video
			// If yes, we display the comment box over the marker
			if(relY + 225 > $("#image-inner #comments-layer").height()) {
				var bottomPosition = $("#image-inner #comments-layer").height() - relY - 10;
				$("#image-inner #comments-layer .comment-box").addClass("bottom").css("top", "").css("bottom", bottomPosition+"px");
			}
			else {
				var topPosition = relY - 10;
				$("#image-inner #comments-layer .comment-box").css("bottom", "").css("top", topPosition+"px");
			}
			$("#image-inner #comments-layer .comment-box").fadeIn();

			// Force the focus on the textarea of the comment box
			$("#image-inner #comments-layer .comment-box textarea").focus();
			$("#image-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").attr("contenteditable", true).focus();
			$("#image-inner #comments-layer .comment-box .emojionearea").addClass("focused");

		}
	});

});
