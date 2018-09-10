// Display the markers for the existing comments
$(document).ready(function() {
    setTimeout(function() {
        displayVideoMarkers();
    }, 5000);
});

// Function which deals with the fact to display or hide markers
var displayMarkers = function(){

    // This should be performed only if we have to see the markers
    if(globallyHideMarkersOnVideo == false) {

        // Set the player
        var strimePlayer = videojs("strime-video");

        // Get the current time
        var currentTime = strimePlayer.currentTime();

        // Browse all the markers
        $("#video-inner #comments-layer img.marker").each(function() {

            // Check the show and hide parameters of each marker
            var currentMarkerShow = $(this).attr("data-show");
            var currentMarkerHide = $(this).attr("data-hide");

            // Check if the marker is visible and if the current time is between show and hide
            if( !$(this).is(":visible") && (currentTime > currentMarkerShow) && (currentTime < currentMarkerHide) ) {
                $(this).fadeIn();

                // Check if this is the next comment set up for the button
                // If yes, change the values of the next and previous buttons
                updatePreviousNextButtonsValues($(this).attr("data-api-comment-id"));
            }

            // If the current time is after the time to hide the marker
            else if( $(this).is(":visible") && (currentTime > currentMarkerHide) ) {
                $(this).fadeOut();
            }
        });
    }
};
