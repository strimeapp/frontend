// Function which activates the back to begining button
function activateBackToBeginningButton() {

    // When the user clicks on this button
    $(document).on("click", "#video-inner .vjs-back-to-begining", function() {

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Hide all the markers
        $("img.marker").fadeOut();

        // Go to the begining in the video and pause it
        strimePlayer.currentTime( 0 );
        strimePlayer.pause();
    });
}



// Function which activates the back to begining button
function activateBackFrom10sButton() {

    // When the user clicks on this button
    $(document).on("click", "#video-inner .vjs-back-from-10s", function() {

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Hide all the markers
        $("img.marker").fadeOut();

        // Get the current time
        var currentTime = strimePlayer.currentTime();

        // Check if the current time is over 10s
        // If yes, set the video to 10s before
        if(currentTime > 10) {
            currentTime = currentTime - 10;
            strimePlayer.currentTime( currentTime );
        }

        // If no, set the video to the beginning
        else {
            strimePlayer.currentTime( 0 );
        }
    });
}



// Function which activates the button to go to the next comment
function activateGoToNextCommentButton() {

    // When the user clicks on this button
    $(document).on("click", "#video-inner .vjs-go-to-next-comment", function() {

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Create a variable for the next comment ID
        var nextCommentID = null;
        var nextCommentTime = null;

        // Browse all the comments
        $("#video-inner #comments-layer img.marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == nextCommentForButton) {
                nextCommentTime = parseFloat( $(this).attr('data-show') ) + 1;
                nextCommentID = $(this).attr('data-comment-id');
            }
        });

        if(nextCommentTime != null) {

            // Hide all the markers
            $("img.marker").fadeOut();

            // Move the player to the proper moment
            strimePlayer.currentTime( nextCommentTime );

            // Pause the video
            strimePlayer.pause();

            // Display the corresponding marker in the video and hide all the others
            $("#video-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#video-inner #comments-layer .marker#marker-"+nextCommentID).removeClass("inactive").addClass("active").fadeIn();

            // Change the color of the marker in the timeline
            $("#video-inner .marker-progress").removeClass("active").removeClass("inactive");
            $("#video-inner .marker-progress").addClass("inactive");
            $("#video-inner .marker-progress#marker-progress-"+nextCommentID).removeClass("inactive").addClass("active");

            // Activate the corresponding marker in the timeline
            $(".comment").removeClass("active");
            $(".comment#comment-"+nextCommentForButton).addClass("active");

            $("#comments #comments-container").animate({
                scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment#comment-"+nextCommentForButton).position().top
            }, 1000);

            // Set the new values for the next and previous comments
            updatePreviousNextButtonsValues(nextCommentForButton);
        }
    });
}



// Function which activates the button to go to the previous comment
function activateGoToPreviousCommentButton() {

    // When the user clicks on this button
    $(document).on("click", "#video-inner .vjs-go-to-previous-comment", function() {

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Get the current time
        var currentTime = strimePlayer.currentTime();

        // Set variables with the closest time and the comment ID
        var previousCommentID = null;
        var previousCommentTime = null;

        // Browse all the comments
        $("#video-inner #comments-layer img.marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == previousCommentForButton) {
                previousCommentTime = parseFloat( $(this).attr('data-show') ) + 1;
                previousCommentID = $(this).attr('data-comment-id');
            }
        });

        if(previousCommentTime != null) {

            // Hide all the markers
            $("img.marker").fadeOut();

            // Move the player to the proper moment
            strimePlayer.currentTime( previousCommentTime );

            // Pause the video
            strimePlayer.pause();

            // Display the corresponding marker in the video and hide all the others
            $("#video-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#video-inner #comments-layer .marker#marker-"+previousCommentID).removeClass("inactive").addClass("active").fadeIn();

            // Change the color of the marker in the timeline
            $("#video-inner .marker-progress").removeClass("active").removeClass("inactive");
            $("#video-inner .marker-progress").addClass("inactive");
            $("#video-inner .marker-progress#marker-progress-"+previousCommentID).removeClass("inactive").addClass("active");

            // Activate the corresponding marker in the timeline
            $(".comment").removeClass("active");
            $(".comment#comment-"+previousCommentForButton).addClass("active");

            $("#comments #comments-container").animate({
                scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment#comment-"+previousCommentForButton).position().top
            }, 1000);

            // Set the new values for the next and previous comments
            updatePreviousNextButtonsValues(previousCommentForButton);
        }
    });
}



// Function which activates the button to globally hide markers on the video
function activateButtonToGloballyHideMarkersOnVideo() {

    // When the user clicks on this button
    $(document).on("click", "#video .video-js .vjs-hide-markers-on-video", function() {

        // If the markers had to be shown until now
        if(globallyHideMarkersOnVideo == false) {

            // Change the value of the variable
            globallyHideMarkersOnVideo = true;

            // Hide all the markers
            $("img.marker").fadeOut();

            // Change the button appearance
            $("#video .video-js .vjs-hide-markers-on-video").addClass("markers-hidden");
        }

        // If the markers had to be hidden until now
        else {

            // Change the value of the variable
            globallyHideMarkersOnVideo = false;

            // Change the button appearance
            $("#video .video-js .vjs-hide-markers-on-video").removeClass("markers-hidden");

            // Set the current player
            var strimePlayer = videojs("strime-video");

            // Get the current time
            var currentTime = strimePlayer.currentTime();

            // Browse all the markers and display the markers of the current time
            $("#video-inner #comments-layer img.marker").each(function() {

                // Check the show and hide parameters of each marker
                var currentMarkerShow = $(this).attr("data-show");
                var currentMarkerHide = $(this).attr("data-hide");

                // Check if the marker is visible and if the current time is between show and hide
                if( !$(this).is(":visible") && (currentTime > currentMarkerShow) && (currentTime < currentMarkerHide) ) {
                    $(this).fadeIn();
                }
            });
        }
    });
}
