// Function which resizes the image
function resizeImage() {

    // Get the width of the container
    var containerWidth = $("#image #image-inner").width();

    // Calculate the corresponding height
    var imageHeight = containerWidth / goldNumber;

    // Save the data in variables
    imageActualWidth = containerWidth;
    imageActualHeight = imageHeight;

    $("#image #image-inner #image-content").css("width", containerWidth+"px").css("height", imageHeight+"px");

}
