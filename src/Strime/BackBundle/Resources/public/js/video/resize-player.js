// Function which resizes the video player
function resizeVideoPlayer() {

    // Get the width of the container
    var containerWidth = $("#video #video-inner").width();

    // Calculate the corresponding height
    var videoHeight = setNewVideoPlayerSize(containerWidth, goldNumber);

    // Get the height of the video container and the video player
    var videoContainerHeight = $("#video-container").outerHeight();

    // Prepare a variable.
    var videoInnerWidth;

    // If the video is heigher than its container, resize the panes.
    while(videoContainerHeight < videoHeight + 40) {

        // Set the margins and the width to the video inner
        videoInnerWidth = $("#video #video-inner").width();

        // Define the new widths.
        videoInnerWidth = videoInnerWidth - 2;

        // Set the new width and paddings.
        $("#video #video-inner").width(videoInnerWidth);

        // Calculate the corresponding height
        videoHeight = setNewVideoPlayerSize(videoInnerWidth, goldNumber);
    }

    return;
}



function setNewVideoPlayerSize(containerWidth, ratio) {

    // Calculate the corresponding height
    var videoHeight = containerWidth / ratio;

    // Save the data in variables
    videoActualWidth = containerWidth;
    videoActualHeight = videoHeight;

    $("#video #video-inner video, #video #video-inner .video-js").css("width", containerWidth+"px").css("height", videoHeight+"px");

    return videoHeight;
}
