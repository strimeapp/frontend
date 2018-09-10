// Display the markers for the existing comments
$(document).ready(function() {
    setTimeout(function() {
        displayImageMarkers();
    }, 2000);
});

// Function which deals with the fact to display or hide markers
var displayImageMarkers = function(){

    // This should be performed only if we have to see the markers
    if(globallyHideMarkersOnImage == false) {

        // Browse all the markers
        $("#image-inner #comments-layer img.marker").each(function() {
            $(this).fadeIn();
        });
    }
};
