// Function which allows the user to show or hide the comments sidebar
function showCommentsSidebar() {

    // When the user clicks on the button to show the sidebar
    $(document).on("click", "#asset-meta .video-show-comments-sidebar", function() {

        // Change the button layout
        $("#asset-meta .video-show-comments-sidebar").addClass("visible");

        // Show the comments
        $("#comments").addClass("visible");
        $("#video").addClass("comments-visible");

        // For a first resize of the player
        // Get the width of the container
        var containerWidth = $("#video #video-inner").width() * 0.6;

        // Calculate the corresponding height
        var videoHeight = containerWidth / goldNumber;

        // Save the data in variables
        videoActualWidth = containerWidth;
        videoActualHeight = videoHeight;

        $("#video #video-inner video, #video #video-inner .video-js").css("width", containerWidth+"px").css("height", videoHeight+"px");

        setTimeout(function() {

            // Show the content of the comments sidebar
            $("#comments #comments-filters").fadeIn();
            $("#comments #comments-container").fadeIn();
            $("#comments .header #close-comments-pane").fadeIn();

            // Resize the video
            resizeVideoPlayer();
        }, 600);
    });

    // When the user clicks on the button to hide it
    $(document).on("click", "#comments .header #close-comments-pane", function() {

        // Hide the content of the comments sidebar
        $("#comments .header #close-comments-pane").hide();
        $("#comments #comments-container").hide();
        $("#comments #comments-filters").hide();

        // Change the button layout
        $("#asset-meta .video-show-comments-sidebar").removeClass("visible");

        // Change the size and opacity of the comments
        $("#comments").removeClass("visible");
        $("#video").removeClass("comments-visible");

        setTimeout(function() {

            // Resize the video and the comments layer
            resizeVideoPlayer();
        }, 600);

    });
}
