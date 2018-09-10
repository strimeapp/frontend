// Function which detects the click on the timeline to move to this specific moment
function goToTime(sound) {

    // When the user clicks on the comment zone
    $(document).on("click", "#audio #audio-inner #comments-layer", function(e) {

        e.preventDefault();

        // Check if a marker is clicked
        var clickTarget = $(e.target);
        if(clickTarget.hasClass("marker")) {
            var clickOnMarker = true;
        }
        else {
            var clickOnMarker = false;
        }

        // Get the left position of the cursor
        var parentOffset = $(this).offset();
        var relX = e.pageX - parentOffset.left;
        var relY = e.pageY - parentOffset.top;

        // Get the comment box position
        var commentBoxTop = parseInt( $("#audio-inner #comments-layer .comment-box").css("top") );
        var commentBoxLeft = parseInt( $("#audio-inner #comments-layer .comment-box").css("left") );

        // Get the dimensions of the comment box
        var commentBoxWidth = parseInt( $("#audio-inner #comments-layer .comment-box").css("width") );
        var commentBoxHeight = parseInt( $("#audio-inner #comments-layer .comment-box").css("height") );

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
        var trashTop = commentBoxTop + parseInt( $("#audio-inner #comments-layer .comment-box").css("paddingTop") ) + parseInt( $("#audio-inner #comments-layer .comment-box .comment-box-inner").css("paddingTop") );
        var trashRight = commentBoxRight - parseInt( $("#audio-inner #comments-layer .comment-box .comment-box-inner").css("paddingRight") ) - parseInt( $("#audio-inner #comments-layer .comment-box").css("paddingRight") );
        var trashBottom = trashTop + parseInt( $("#audio-inner #comments-layer .comment-box .comment-box-inner .trash").css("height") );
        var trashLeft = trashRight - parseInt( $("#audio-inner #comments-layer .comment-box .comment-box-inner .trash").css("width") );

        // Check if the click happened on the trash
        var clickOnTrash = false;

        if((relX >= trashLeft) && (relX <= trashRight)
            && (relY >= trashTop) && (relY <= trashBottom)) {
            clickOnTrash = true;
        }

        // If the click happened outside the comment box
        if((clickInCommentBox == false) && !clickOnMarker) {

            // Get the full width of the comments zone
            var commentsLayerWidth = $("#audio #audio-inner #comments-layer").width();

            // Define which percentage it is
            var leftPositionPercentage = relX / commentsLayerWidth;

            // Get the duration of the sound
            var soundDuration = sound.duration();

            // Define the corresponding time
            var correspondingTime = Math.round(soundDuration * leftPositionPercentage);

            // Pause the player
            if(sound.playing())
                sound.pause();

            // Set the player to this time
            sound.seek(correspondingTime);

            // Move the progress bar
            leftPositionPercentage = leftPositionPercentage * 100;
            $("#audio #audio-inner #audio-progress #audio-progress-inner").css("width", leftPositionPercentage+"%");
            $("#audio #audio-inner #audio-progress-bar #audio-progress-bar-inner").css("width", leftPositionPercentage+"%");

            // Display the comment box
            displayCommentBox(relX);
        }

        // If the click happened on the trash can
        else if(clickOnTrash == true) {
            hideCommentBox();
            sound.play();
        }

        // If the click happened on a marker
        else if(clickOnMarker) {

            // Hide the comment box if visible
            hideCommentBox();

            // Get the ID of the comment
            var apiCommentId = $(this).attr("data-api-comment-id");

            // Get the time of the marker
            var commentTime = $("#comments #comments-container #comment-"+apiCommentId).attr("data-time");

            // Position the play on this time
            sound.seek(commentTime);

            // Pause the player
            if(sound.playing())
                sound.pause();
        }
    });

    // When the user clicks on the progress bar
    $(document).on("click", "#audio #audio-inner #audio-progress-bar", function(e) {

        e.preventDefault();

        // Check if a marker is clicked
        var clickTarget = $(e.target);
        if(clickTarget.hasClass("marker-progress")) {
            var clickOnMarker = true;
        }
        else {
            var clickOnMarker = false;
        }

        // If the click happened on a marker
        if(clickOnMarker) {

            // Hide the comment box if visible
            hideCommentBox();

            // Get the ID of the comment
            var apiCommentId = $(this).attr("data-api-comment-id");

            // Get the time of the marker
            var commentTime = $("#comments #comments-container #comment-"+apiCommentId).attr("data-time");

            // Position the play on this time
            sound.seek(commentTime);

            // Pause the player
            if(sound.playing())
                sound.pause();
        }

        // If the click happened on the progress bar
        else {

            // Get the left position of the cursor
            var parentOffset = $(this).offset();
            var relX = e.pageX - parentOffset.left;

            // Get the full width of the progress bar
            var progressBarWidth = $("#audio #audio-inner #audio-progress-bar").width();

            // Define which percentage it is
            var leftPositionPercentage = relX / progressBarWidth;

            // Get the duration of the sound
            var soundDuration = sound.duration();

            // Define the corresponding time
            var correspondingTime = Math.round(soundDuration * leftPositionPercentage);

            // Set the player to this time
            sound.seek(correspondingTime);

            // Move the progress bar
            leftPositionPercentage = leftPositionPercentage * 100;
            $("#audio #audio-inner #audio-progress #audio-progress-inner").css("width", leftPositionPercentage+"%");
            $("#audio #audio-inner #audio-progress-bar #audio-progress-bar-inner").css("width", leftPositionPercentage+"%");

        }
    });
}
