
// Function which resizes the video and comments container
function resizeVideoAndCommentsContainer() {

    // If we're not on a mobile
    if(isMobile == false) {

        // Get the height of the window
        var windowHeight = window.innerHeight;

        // Get the height of the header, the footer, the meta bar of the comments, and the bar of buttons of the comments.
        var headerHeight = $("#header").height();
        var footerHeight = $("footer").outerHeight();
        var videoMetaHeight = $("#video #asset-meta").outerHeight();
        var commentsMetaHeight = $("#comments .header").outerHeight();
        var buttonsHeight = $("#comments #comments-filters").outerHeight();

        // Get the margin bottom of the body
        var bodyMarginBottom = parseInt( $("body").css("marginBottom") );

        // Get the padding of the content div
        var contentPaddingBottom = parseInt( $("#content").css("paddingBottom") );

        // Define the height of video container and of the comments container
        var videoContainerHeight = windowHeight - headerHeight - videoMetaHeight - contentPaddingBottom - footerHeight - bodyMarginBottom + 10;
        var commentsContainerHeight = windowHeight - headerHeight - commentsMetaHeight - buttonsHeight - contentPaddingBottom - footerHeight - bodyMarginBottom + 10;

        // Set these values
        $("#video #video-container").css("height", videoContainerHeight+"px");
        $("#comments #comments-container").css("height", commentsContainerHeight+"px");
    }

}
