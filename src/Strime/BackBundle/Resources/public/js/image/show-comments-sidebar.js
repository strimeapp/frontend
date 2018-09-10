// Function which allows the user to show or hide the comments sidebar
function showCommentsSidebar() {

    // When the user clicks on the button to show the sidebar
    $(document).on("click", "#asset-meta .image-show-comments-sidebar", function() {

        // Change the button layout
        $("#asset-meta .image-show-comments-sidebar").addClass("visible");

        // Show the comments
        $("#comments").addClass("visible");
        $("#image").addClass("comments-visible");

        // For a first resize of the player
        // Get the width of the container
        var containerWidth = $("#image #image-inner").width() * 0.6;

        // Calculate the corresponding height
        var imageHeight = containerWidth / goldNumber;

        // Save the data in variables
        imageActualWidth = containerWidth;
        imageActualHeight = imageHeight;

        $("#image #image-inner #image-asset").css("width", containerWidth+"px").css("height", imageHeight+"px");

        setTimeout(function() {

            // Show the content of the comments sidebar
            $("#comments #comments-filters").fadeIn();
            $("#comments #comments-container").fadeIn();
            $("#comments .header #close-comments-pane").fadeIn();

            // Resize the image
            resizeImage();
        }, 600);
    });

    // When the user clicks on the button to hide it
    $(document).on("click", "#comments .header #close-comments-pane", function() {

        // Hide the content of the comments sidebar
        $("#comments .header #close-comments-pane").hide();
        $("#comments #comments-container").hide();
        $("#comments #comments-filters").hide();

        // Change the button layout
        $("#asset-meta .image-show-comments-sidebar").removeClass("visible");

        // Change the size and opacity of the comments
        $("#comments").removeClass("visible");
        $("#image").removeClass("comments-visible");

        setTimeout(function() {

            // Resize the image and the comments layer
            resizeImage();
        }, 600);

    });
}
