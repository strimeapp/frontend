// Function which checks if a hash has been passed in the URL and redirects the user to the corresponding comment
function goToHashInUrl(sound) {

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

        // Get the time of the audio
        var commentTime = $("#comments-container #" + mainCommentID ).attr("data-time");

        // Get the loop ID of the comment
        var commentLoopID = $("#comments-container .comment#"+mainCommentID).attr("data-comment-id");

        setTimeout(function(){

            // Go to the time of the comment
            sound.seek( commentTime );

            // Pause the audio
            sound.pause();

            // Display the corresponding marker in the video and hide all the others
            $("#audio-inner #comments-layer .marker").removeClass("active").addClass("inactive");
            $("#audio-inner #comments-layer .marker#marker-"+commentLoopID).removeClass("inactive").addClass("active");
        }, 4000);

    }

}
