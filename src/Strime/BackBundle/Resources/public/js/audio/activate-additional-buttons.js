// Function which activates the back to begining button
function activateBackToBeginningButton(sound) {

    // When the user clicks on this button
    $(document).on("click", "#audio-inner .audio-back-to-begining", function() {

        // Go to the begining in the audio and pause it
        sound.seek( 0 );
        sound.pause();
    });
}



// Function which activates the back to begining button
function activateBackFrom10sButton(sound) {

    // When the user clicks on this button
    $(document).on("click", "#audio-inner .audio-back-from-10s", function() {

        // Get the current time
        var currentTime = sound.seek();

        // Check if the current time is over 10s
        // If yes, set the audio to 10s before
        if(currentTime > 10) {
            currentTime = currentTime - 10;
            sound.seek( currentTime );
        }

        // If no, set the audio to the beginning
        else {
            sound.seek( 0 );
        }
    });
}



// Function which activates the button to go to the next comment
function activateGoToNextCommentButton(sound) {

    // When the user clicks on this button
    $(document).on("click", "#audio-inner .audio-go-to-next-comment", function() {

        // Create a variable for the next comment ID
        var nextCommentID = null;
        var nextCommentTime = null;

        // Browse all the comments
        $("#audio-inner #comments-layer .marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == nextCommentForButton) {
                nextCommentTime = parseFloat( $("#comments-container .comment#comment-"+nextCommentForButton).attr('data-time') );
                nextCommentID = $(this).attr('data-comment-id');
            }
        });

        if(nextCommentTime != null) {

            // Pause the player
            if(sound.playing())
                sound.pause();

            // Move the player to the proper moment
            sound.seek( nextCommentTime );

            // Display the corresponding marker in the audio and hide all the others
            $("#audio-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#audio-inner .marker-progress").removeClass("active").addClass("inactive");
            $("#audio-inner #comments-layer .marker#marker-"+nextCommentID).removeClass("inactive").addClass("active");
            $("#audio-inner .marker-progress#marker-progress-"+nextCommentID).removeClass("inactive").addClass("active");

            // Activate the corresponding marker in the timeline
            $("#comments-container .comment").removeClass("active");
            $("#comments-container .comment#comment-"+nextCommentForButton).addClass("active");

            $("#comments #comments-container").animate({
                scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment#comment-"+nextCommentForButton).position().top
            }, 1000);

            // Set the new values for the next and previous comments
            updatePreviousNextButtonsValues(nextCommentForButton);
        }
    });
}



// Function which activates the button to go to the previous comment
function activateGoToPreviousCommentButton(sound) {

    // When the user clicks on this button
    $(document).on("click", "#audio-inner .audio-go-to-previous-comment", function() {

        // Set variables with the closest time and the comment ID
        var previousCommentID = null;
        var previousCommentTime = null;

        // Browse all the comments
        $("#audio-inner #comments-layer .marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == previousCommentForButton) {
                previousCommentTime = parseFloat( $("#comments-container .comment#comment-"+previousCommentForButton).attr('data-time') );
                previousCommentID = $(this).attr('data-comment-id');
            }
        });

        if(previousCommentTime != null) {

            // Pause the player
            if(sound.playing())
                sound.pause();

            // Move the player to the proper moment
            sound.seek( previousCommentTime );

            // Display the corresponding marker in the audio and hide all the others
            $("#audio-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#audio-inner .marker-progress").removeClass("active").addClass("inactive");
            $("#audio-inner #comments-layer .marker#marker-"+previousCommentID).removeClass("inactive").addClass("active");
            $("#audio-inner .marker-progress#marker-progress-"+previousCommentID).removeClass("inactive").addClass("active");

            // Activate the corresponding marker in the timeline
            $("#comments-container .comment").removeClass("active");
            $("#comments-container .comment#comment-"+previousCommentForButton).addClass("active");

            $("#comments #comments-container").animate({
                scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment#comment-"+previousCommentForButton).position().top
            }, 1000);

            // Set the new values for the next and previous comments
            updatePreviousNextButtonsValues(previousCommentForButton);
        }
    });
}



// Function which activates the button to globally hide markers on the audio
function activateButtonToGloballyHideMarkersOnAudio(sound) {

    // When the user clicks on this button
    $(document).on("click", "#audio .audio-hide-markers-on-audio", function() {

        // If the markers had to be shown until now
        if(globallyHideMarkersOnAudio == false) {

            // Change the value of the variable
            globallyHideMarkersOnAudio = true;

            // Hide all the markers
            $("#comments-layer .marker").fadeOut();

            // Change the button appearance
            $("#audio .audio-hide-markers-on-audio").addClass("markers-hidden");
        }

        // If the markers had to be hidden until now
        else {

            // Change the value of the variable
            globallyHideMarkersOnAudio = false;

            // Browse all the markers and display the markers of the current time
            $("#audio-inner #comments-layer .marker").fadeIn();

            // Change the button appearance
            $("#audio .audio-hide-markers-on-audio").removeClass("markers-hidden");
        }
    });
}
