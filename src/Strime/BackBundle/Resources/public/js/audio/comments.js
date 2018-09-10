// Function which displays the comment box when the user clicks on the comment zone
function displayCommentBox(leftPosition) {

    // Get the full width of the comments zone
    var commentsLayerWidth = $("#audio #audio-inner #comments-layer").width();

    // Get the width of the comments box
    var commentsBoxWidth = $("#comments-layer .comment-box").width();

    // Check if the box will overflow the comments layer
    if(leftPosition + commentsBoxWidth > commentsLayerWidth) {
        $("#comments-layer .comment-box").addClass("right");

        // Define which percentage it is
        var rightPositionPercentage = (commentsLayerWidth - leftPosition - 10) / commentsLayerWidth;

        // Move the progress bar
        rightPositionPercentage = rightPositionPercentage * 100;

        // Set the position of the comment box and make it appear
        $("#comments-layer .comment-box").css("left", "").css("right", rightPositionPercentage+"%").fadeIn();
    }

    // If the comments box has to be displayed normally
    else {
        $("#comments-layer .comment-box").removeClass("right");

        // Define which percentage it is
        var leftPositionPercentage = (leftPosition - 10) / commentsLayerWidth;

        // Move the progress bar
        leftPositionPercentage = leftPositionPercentage * 100;

        // Set the position of the comment box and make it appear
        $("#comments-layer .comment-box").css("right", "").css("left", leftPositionPercentage+"%").fadeIn();
    }

    // Focus on the field
    if( $("#audio #audio-inner #comments-layer .emojionearea .emojionearea-editor").is(":visible") ) {
        $("#audio #audio-inner #comments-layer .emojionearea .emojionearea-editor").focus();
    }
    else if( $("#comments-layer .comment-box textarea").is(":visible") ) {
        $("#comments-layer .comment-box textarea").focus();
    }
}



// Function which hides the comment box
function hideCommentBox() {
    $("#comments-layer .comment-box").fadeOut();
    setTimeout(function() {
        $("#comments-layer .comment-box").removeClass("right").css("left", "").css("right", "");
    }, 400);
}



// Function which repositions the markers on load
function repositionMarkersOnLoad() {

    // Get the full width of the comments zone
    var commentsLayerWidth = $("#audio #audio-inner #comments-layer").width();

    // Get the propertion of 1px with regards to the width
    var pixelRatio = (1 * 100) / commentsLayerWidth;

    // For each marker
    $("#comments-layer .marker").each(function() {

        // Get its left position
        var markerLeft = parseFloat( $(this).css("left") );

        // Get the value as a percentage
        var markerLeftPercentage = (markerLeft * 100) / commentsLayerWidth;

        // Get the new left position
        var newMarkerLeft = markerLeftPercentage - pixelRatio;

        // Set this new position
        $(this).css("left", newMarkerLeft+"%");
    });
}
