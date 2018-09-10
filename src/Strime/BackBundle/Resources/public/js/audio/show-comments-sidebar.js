// Function which allows the user to show or hide the comments sidebar
function showCommentsSidebar() {

    // When the user clicks on the button to show the sidebar
    $(document).on("click", "#asset-meta .audio-show-comments-sidebar", function() {

        // Change the button layout
        $("#asset-meta .audio-show-comments-sidebar").addClass("visible");

        // Show the comments
        $("#comments").addClass("visible");
        $("#audio").addClass("comments-visible");

        // For a first resize of the player
        // Get the width of the container
        var containerWidth = $("#audio #audio-inner").width() * 0.6;

        // Save the data in variables
        audioActualWidth = containerWidth;

        $("#audio #audio-inner").css("width", containerWidth+"px");

        setTimeout(function() {

            // Show the content of the comments sidebar
            $("#comments #comments-filters").fadeIn();
            $("#comments #comments-container").fadeIn();
            $("#comments .header #close-comments-pane").fadeIn();

            // Resize the audio
            resizeAudioPlayer();
        }, 600);
    });

    // When the user clicks on the button to hide it
    $(document).on("click", "#comments .header #close-comments-pane", function() {

        // Hide the content of the comments sidebar
        $("#comments .header #close-comments-pane").hide();
        $("#comments #comments-container").hide();
        $("#comments #comments-filters").hide();

        // Change the button layout
        $("#asset-meta .audio-show-comments-sidebar").removeClass("visible");

        // Change the size and opacity of the comments
        $("#comments").removeClass("visible");
        $("#audio").removeClass("comments-visible");

        setTimeout(function() {

            // Resize the audio and the comments layer
            resizeAudioPlayer();
        }, 600);

    });
}
