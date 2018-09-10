// Function which activates the button to go to the next comment
function activateGoToNextCommentButton() {

    // When the user clicks on this button
    $(document).on("click", "#image-comments-controls .image-go-to-next-comment", function() {

        // Create a variable for the next comment ID
        var nextCommentID = null;

        // Browse all the comments
        $("#image-inner #comments-layer img.marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == nextCommentForButton) {
                nextCommentID = $(this).attr('data-comment-id');
            }
        });

        if(nextCommentID != null) {

            // Hide all the markers
            $("img.marker").removeClass("active").addClass("inactive");

            // Display the corresponding marker on the image and hide all the others
            $("#image-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#image-inner #comments-layer .marker#marker-"+nextCommentID).removeClass("inactive").addClass("active").fadeIn();

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
    $(document).on("click", "#image-comments-controls .image-go-to-previous-comment", function() {

        // Create a variable for the previous comment ID
        var previousCommentID = null;

        // Browse all the comments
        $("#image-inner #comments-layer img.marker").each(function() {

            // Check if the API ID of the comment is the same than the next comment ID
            if($(this).attr('data-api-comment-id') == previousCommentForButton) {
                previousCommentID = $(this).attr('data-comment-id');
            }
        });

        if(previousCommentID != null) {

            // Hide all the markers
            $("img.marker").removeClass("active").addClass("inactive");

            // Display the corresponding marker in the image and hide all the others
            $("#image-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#image-inner #comments-layer .marker#marker-"+previousCommentID).removeClass("inactive").addClass("active").fadeIn();

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



// Function which activates the button to globally hide markers on the image
function activateButtonToGloballyHideMarkersOnImage() {

    // When the user clicks on this button
    $(document).on("click", "#image #image-comments-controls .image-hide-markers-on-image", function() {

        // If the markers had to be shown until now
        if(globallyHideMarkersOnImage == false) {

            // Change the value of the variable
            globallyHideMarkersOnImage = true;

            // Hide all the markers
            $("img.marker").fadeOut();

            // Change the button appearance
            $("#image #image-comments-controls .image-hide-markers-on-image").addClass("markers-hidden").removeClass("active").removeClass("inactive");
        }

        // If the markers had to be hidden until now
        else {

            // Change the value of the variable
            globallyHideMarkersOnImage = false;

            // Show all the markers
            $("img.marker").fadeIn();

            // Change the button appearance
            $("#image #image-comments-controls .image-hide-markers-on-image").removeClass("markers-hidden").removeClass("active").removeClass("inactive");
        }
    });
}
