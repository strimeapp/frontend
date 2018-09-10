$(document).ready(function() {

	// Set the height of the video and comments containers
	resizeVideoAndCommentsContainer();
	$(window).resize(function() {
		resizeVideoAndCommentsContainer();
	});

	// Resize the video player if the window is resized
	resizeVideoPlayer();
	$(window).resize(function() {

		// Remove the style of the inner.
		$("#video #video-inner").removeAttr("style");

		resizeVideoPlayer();
	});

	// Allow comments
	postComment();

	// Allow answers to this comment
	answerToComment();

	// Allow the answer to be posted
	postAnswer();

	// Allow the user to click on a comment to see it on the video
	goToComment();

	// Activate the back to begining button
	activateBackToBeginningButton();

	// Activate the button to back from 10s in the video
	activateBackFrom10sButton();

	// Activate the button to go to the next comment
	activateGoToNextCommentButton();

	// Activate the button to go to the previous comment
	activateGoToPreviousCommentButton();

	// Activate the button which globally hides all the markers on the video
	activateButtonToGloballyHideMarkersOnVideo();

	// Activate the keyboard shortcuts.
	activateShortcuts();

	// Reload the markers when the user clicks on the play bar
	reloadMarkersOnClickOnPlayBar();

	// Close the comment box on click on the play button
	closeCommentBoxOnClickOnPlayButton();

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

	// Play / pause the video when the user presses the space bar
	playPauseVideoOnSpacePress();

	// Close comment box when the ESC key is pressed
	closeCommentBoxOnEscKeypress();

	// Edit the name of the video
	editVideoName();

	// Edit the description of the video
	editVideoDescription();

	// Reorder the comments
	reorderComments();


	// If the player is ready, allow actions on the comments layer
	videojs("strime-video").ready(function(){

		// Add a button to go back to the begining of the video
		var backToBeginingButton = '<button class="vjs-back-to-begining vjs-control" type="button" aria-live="polite" title="'+backVideoJsTextRewind+'"></button>';
		$("#video-inner #strime-video .vjs-control-bar").prepend(backToBeginingButton);

		// Add a button to go back from 10s in the video
		var backFrom10sButton = '<button class="vjs-back-from-10s vjs-control" type="button" aria-live="polite" title="'+backVideoJsTextBackFrom10s+'"></button>';
		$("#video-inner #strime-video .vjs-control-bar .vjs-play-control").after(backFrom10sButton);

		// Add a button to go to the next comment
		var goToNextCommentButton = '<button class="vjs-go-to-next-comment vjs-control" type="button" aria-live="polite" title="'+backVideoJsTextGoToNextComment+'"></button>';
		$("#video-inner #strime-video .vjs-control-bar .vjs-play-control").after(goToNextCommentButton);

		// Add a button to go to the previous comment
		var goToPreviousCommentButton = '<button class="vjs-go-to-previous-comment vjs-control" type="button" aria-live="polite" title="'+backVideoJsTextGoToPreviousComment+'"></button>';
		$("#video-inner #strime-video .vjs-control-bar .vjs-play-control").after(goToPreviousCommentButton);

		// Add a button to hide the markers on the video
		var globallyHideMarkersOnVideoButton = '<button class="vjs-hide-markers-on-video vjs-control" type="button" aria-live="polite" title="'+backVideoJsTextHideMarkers+'"></button>';
		$("#video-inner #strime-video .vjs-control-bar").append(globallyHideMarkersOnVideoButton);

		// Change the default button
		$("#video #video-inner .video-js .vjs-play-control").addClass("vjs-paused");

		// Set the variable for the player
		var strimePlayer = this;

		// Set the volume if it has been saved in the session
		if(videoVolume != null) {
			strimePlayer.volume( videoVolume );
		}

		// Set a regulare check to display or hide markers on the video
		// During this check also check if we are fullscreen or not to adapt the size of the player
		// This check is supposed to happen only if the option to hide the markers is not activated.
		strimePlayer.on('timeupdate', displayMarkers);

		// Detect when the volume changed to save the value in the session
		strimePlayer.on('volumechange', saveVideoVolume);

		// If the user clicks on the layer
		$("#video-inner #comments-layer").on("click", function(e) {

			e.preventDefault();

			// Check if a marker is clicked
			var clickTarget = $(e.target);
			if(clickTarget.hasClass("marker")) {
				var clickOnMarker = true;
			}
			else {
				var clickOnMarker = false;
			}

			// Check if the player is paused
			var isPaused = strimePlayer.paused();

			// Get the position of the cursor in the comments layer div
			var parentOffset = $(this).offset();
			var relX = e.pageX - parentOffset.left;
			var relY = e.pageY - parentOffset.top;

			// If the player is paused
			if(isPaused) {

				// Get the comment box position
				var commentBoxTop = parseInt( $("#video-inner #comments-layer .comment-box").css("top") );
				var commentBoxLeft = parseInt( $("#video-inner #comments-layer .comment-box").css("left") );

				// Get the dimensions of the comment box
				var commentBoxWidth = parseInt( $("#video-inner #comments-layer .comment-box").css("width") );
				var commentBoxHeight = parseInt( $("#video-inner #comments-layer .comment-box").css("height") );

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
				var trashTop = commentBoxTop + parseInt( $("#video-inner #comments-layer .comment-box").css("paddingTop") ) + parseInt( $("#video-inner #comments-layer .comment-box .comment-box-inner").css("paddingTop") );
				var trashRight = commentBoxRight - parseInt( $("#video-inner #comments-layer .comment-box .comment-box-inner").css("paddingRight") ) - parseInt( $("#video-inner #comments-layer .comment-box").css("paddingRight") );
				var trashBottom = trashTop + parseInt( $("#video-inner #comments-layer .comment-box .comment-box-inner .trash").css("height") );
				var trashLeft = trashRight - parseInt( $("#video-inner #comments-layer .comment-box .comment-box-inner .trash").css("width") );

				// Check if the click happened on the trash
				var clickOnTrash = false;

				if((relX >= trashLeft) && (relX <= trashRight)
					&& (relY >= trashTop) && (relY <= trashBottom)) {
					clickOnTrash = true;
				}

				// Set the current time
				var currentTime = strimePlayer.currentTime();

				// If the comment box is not visible and the video is not at the beginning, and the user didn't click on a marker, show it
				if( $("#video-inner #comments-layer .comment-box").is(":hidden") && (currentTime != 0) && !clickOnMarker ) {

					// Check if we are at the right of the video
					// If yes, we display the comment box on the left of the marker
					if(relX + 300 > $("#video-inner #comments-layer").width()) {
						var rightPosition = $("#video-inner #comments-layer").width() - relX - 10;
						$("#video-inner #comments-layer .comment-box").addClass("right").css("left", "").css("right", rightPosition+"px");
					}
					else {
						var leftPosition = relX - 10;
						$("#video-inner #comments-layer .comment-box").removeClass("right").css("right", "").css("left", leftPosition+"px");
					}

					// Check if we are at the bottom of the video
					// If yes, we display the comment box over the marker
					if(relY + 225 > $("#video-inner #comments-layer").height()) {
						var bottomPosition = $("#video-inner #comments-layer").height() - relY - 10;
						$("#video-inner #comments-layer .comment-box").addClass("bottom").css("top", "").css("bottom", bottomPosition+"px");
					}
					else {
						var topPosition = relY - 10;
						$("#video-inner #comments-layer .comment-box").css("bottom", "").css("top", topPosition+"px");
					}
					$("#video-inner #comments-layer .comment-box").fadeIn();

					// Force the focus on the textarea of the comment box
					$("#video-inner #comments-layer .comment-box textarea").focus();
					$("#video-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").attr("contenteditable", true).focus();
					$("#video-inner #comments-layer .comment-box .emojionearea").addClass("focused");

					// Remove the active class from the markers
					$("#video-inner .marker-progress").each(function(){
						$(this).removeClass("active").removeClass("inactive");
					});
					$("#video-inner .marker").each(function(){
						$(this).removeClass("active").removeClass("inactive");
					});
					$("#comment-inner .comment").each(function(){
						$(this).removeClass("active");
					});
				}

				// If the comment box is visible
				else {

					// If the user clicked on a marker, just hide the comment box
					if(clickOnMarker == true) {
						$("#video-inner #comments-layer .comment-box").fadeOut(function() {
							$("#video-inner #comments-layer .comment-box").removeClass("bottom").removeClass("right").css("top", "").css("left", "").css("bottom", "").css("right", "");
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

							// Launch the video and hide the comment box
							strimePlayer.play();
							$("#video-inner #comments-layer .comment-box").fadeOut(function() {
								$("#video-inner #comments-layer .comment-box").removeClass("bottom").removeClass("right").css("top", "").css("left", "").css("bottom", "").css("right", "");
							});
						}
					}
				}
			}

			// Else if the player is playing
			else {

				// Pause the video
				strimePlayer.pause();

				// If the user didn't click on a marker, show the comment box
				if(!clickOnMarker) {

					// Check if we are at the right of the video
					// If yes, we display the comment box on the left of the marker
					if(relX + 300 > $("#video-inner #comments-layer").width()) {
						var rightPosition = $("#video-inner #comments-layer").width() - relX - 10;
						$("#video-inner #comments-layer .comment-box").addClass("right").css("left", "").css("right", rightPosition+"px");
					}
					else {
						var leftPosition = relX - 10;
						$("#video-inner #comments-layer .comment-box").removeClass("right").css("right", "").css("left", leftPosition+"px");
					}

					// Check if we are at the bottom of the video
					// If yes, we display the comment box over the marker
					if(relY + 225 > $("#video-inner #comments-layer").height()) {
						var bottomPosition = $("#video-inner #comments-layer").height() - relY - 10;
						$("#video-inner #comments-layer .comment-box").addClass("bottom").css("top", "").css("bottom", bottomPosition+"px");
					}
					else {
						var topPosition = relY - 10;
						$("#video-inner #comments-layer .comment-box").css("bottom", "").css("top", topPosition+"px");
					}
					$("#video-inner #comments-layer .comment-box").fadeIn();

					// Force the focus on the textarea of the comment box
					$("#video-inner #comments-layer .comment-box textarea").focus();
					$("#video-inner #comments-layer .comment-box .emojionearea .emojionearea-editor").attr("contenteditable", true).focus();
					$("#video-inner #comments-layer .comment-box .emojionearea").addClass("focused");
				}

			}
		});

	});

});
