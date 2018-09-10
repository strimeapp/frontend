// Function which resizes the image and comments container
function resizeAudioAndCommentsContainer() {

    // If we're not on a mobile
    if(isMobile == false) {

        // Get the height of the window
        var windowHeight = window.innerHeight;

        // Get the height of the header, the footer, the meta bar of the comments, and the bar of buttons of the comments.
        var headerHeight = $("#header").height();
        var footerHeight = $("footer").outerHeight();
        var audioMetaHeight = $("#audio #asset-meta").outerHeight();
        var commentsMetaHeight = $("#comments .header").outerHeight();
        var buttonsHeight = $("#comments #comments-filters").outerHeight();

        // Get the margin bottom of the body
        var bodyMarginBottom = parseInt( $("body").css("marginBottom") );

        // Get the padding of the content div
        var contentPaddingBottom = parseInt( $("#content").css("paddingBottom") );

        // Define the height of audio container and of the comments container
        var audioContainerHeight = windowHeight - headerHeight - audioMetaHeight - contentPaddingBottom - footerHeight - bodyMarginBottom + 10;
        var commentsContainerHeight = windowHeight - headerHeight - commentsMetaHeight - buttonsHeight - contentPaddingBottom - footerHeight - bodyMarginBottom + 10;

        // Set these values
        $("#audio #audio-container").css("height", audioContainerHeight+"px");
        $("#comments #comments-container").css("height", commentsContainerHeight+"px");
    }
}


// Function which resizes the waveform background
function resizeWaveformBackground() {

    // Get the width of the waveform container
    var audioProgressWidth = $("#content #audio #audio-progress").width();

    // Set this width as the width of the waveform progress background
    $("#content #audio #audio-progress #audio-progress-inner").css("backgroundSize", audioProgressWidth+"px 100%");
}
