// Function which sticks the video player inside the video div container
function stickVideoPlayer() {

    // When the window scrolls down, get the the position of the window
    $(window).scroll(function (event) {
        var $video = $("#content #video");
        var windowTop = $(window).scrollTop();
        var videoBottom = $video.offset().top + $video.outerHeight();
        var videoContainerBottom = $("#content").offset().top + $("#content").outerHeight();

        // Set the height of the header
        if(userLoggedIn == true) {
            var headerHeight = 100;
        }
        else {
            var headerHeight = 134;
        }

        if((windowTop > headerHeight) && (videoBottom < videoContainerBottom)) {
            var videoInnerMarginTop = windowTop - headerHeight;
            $video.css("marginTop", videoInnerMarginTop+"px");
        }
        else if(windowTop <= headerHeight) {
            $video.css("marginTop", "0px");
        }
    });
}
