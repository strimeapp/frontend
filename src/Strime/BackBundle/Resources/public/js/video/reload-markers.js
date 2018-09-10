// Function which reloads the markers when the user clicks on the play bar
function reloadMarkersOnClickOnPlayBar() {

    // When the user clicks on this button
    $(document).on("click", "#video-inner .vjs-progress-control", function() {

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Get the current time
        var currentTime = strimePlayer.currentTime();

        // Browse all the markers
        $("#video-inner #comments-layer img.marker").each(function() {

            // Check the show and hide parameters of each marker
            var currentMarkerShow = $(this).attr("data-show");
            var currentMarkerHide = $(this).attr("data-hide");

            // Check if the marker is visible and if the current time is between show and hide
            if( (currentTime > currentMarkerShow) && (currentTime < currentMarkerHide) ) {
                $(this).fadeIn();
            }
            else {
                $(this).fadeOut();
            }
        });
    });
}
