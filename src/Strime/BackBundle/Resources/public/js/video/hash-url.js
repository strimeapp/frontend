$(document).ready(function() {

    // If there is a hash in the URL
    if(window.location.hash) {

        // Set the variable with the comment ID
        var commentID = window.location.hash;

        // Scroll down to this comment
        $("#comments #comments-container").animate({
            scrollTop: $("#comments #comments-container").scrollTop() + $("#comments #comments-container .comment"+commentID).position().top
        }, 1000);

        // Check if it's an answer
        if( $("#comments-container " + commentID ).hasClass("comment-answer") ) {

            // Get the parent ID
            var mainCommentID = $("#comments-container " + commentID ).closest(".comment").attr("id");
        }
        else {
            var mainCommentID = commentID.substring(1, commentID.length);;
        }

        // Mark the comment as active
        $( "#comments-container .comment#" + mainCommentID ).addClass("active");

        // Get the time of the video
        var commentTime = $("#comments-container #" + mainCommentID ).attr("data-time");

        // Set the current player
        var strimePlayer = videojs("strime-video");

        // Get the loop ID of the comment
        var commentLoopID = $("#comments-container .comment#"+mainCommentID).attr("data-comment-id");

        setTimeout(function(){

            // Play the video
            strimePlayer.play();

            // Go to the time of the comment
            strimePlayer.currentTime( commentTime );

            // Pause the video
            strimePlayer.pause();

            // Display the corresponding marker in the video and hide all the others
            $("#video-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#video-inner #comments-layer .marker#marker-"+commentLoopID).removeClass("inactive").addClass("active").fadeIn();

            // Change the color of the marker in the timeline
            $("#video-inner .marker-progress").removeClass("active").addClass("inactive");
            $("#video-inner .marker-progress#marker-progress-"+commentLoopID).addClass("active").removeClass("inactive");
        }, 4000);

    }

});
