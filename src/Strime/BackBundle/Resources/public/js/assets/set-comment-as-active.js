// Function which sets a comment as active
function setCommentAsActive(sound) {

    $(document).on("click", "#comments-container .comment", function() {

        // Remove the active class from all the comments
        $("#comments-container .comment").removeClass("active");

        // Get the ID of the comment
        var correspondingMarkerId = $(this).attr("data-comment-id");

        // Add the active class to this specific comment
        $(this).addClass("active");

        // If the asset is not a video
        if(assetType != "video") {

            // Remove the active class from all the markers
            $("#comments-layer .marker").removeClass("active");

            // Add the active class to the corresponding marker
            $("#comments-layer .marker#marker-"+correspondingMarkerId).addClass("active");
        }

        // If the asset is an audio file
        if(assetType == "audio") {

            // Get the time of the current comment
            var currentTime = $(this).attr("data-time");

            // Position the player on to this time
            sound.seek( currentTime );
        }

        if((assetType == "audio") || (assetType == "video")) {

            // Set the progress marker as active
            $(".marker-progress").removeClass("active");
            $(".marker-progress#marker-progress-"+correspondingMarkerId).addClass("active");
        }
    });

}



// Function which makes a comment active when the user clicks on the marker
function setCommentAsActiveWhenMarkerIsClicked(sound) {

    $(document).on("click", "#comments-layer .marker", function(e) {

        e.preventDefault();

        activateThisCommentInSidebar(e.target, $(this));

        // Deactivate any other marker
        $("#comments-layer .marker").removeClass("active");

        // Activate this marker
        $(this).addClass("active");

        // If the asset is an audio file
        if(assetType == "audio") {

            // Get the ID of the comment
            var commentAPIId = $(this).attr("data-api-comment-id");

            // Get the time of the current comment
            var currentTime = $("#comments-container .comment#comment-"+commentAPIId).attr("data-time");

            // Position the player on to this time
            sound.seek( currentTime );
        }

        if((assetType == "audio") || (assetType == "video")) {

            // Get the ID of the comment
            var correspondingMarkerId = $(this).attr("data-comment-id");

            // Set the progress marker as active
            $(".marker-progress").removeClass("active");
            $(".marker-progress#marker-progress-"+correspondingMarkerId).addClass("active");
        }
    });

    $(document).on("click", ".marker-progress", function(e) {

        e.preventDefault();

        activateThisCommentInSidebar(e.target, $(this));

        // Deactivate any other marker
        $(".marker-progress").removeClass("active");

        // Activate this marker
        $(this).addClass("active");

        // If the asset is an audio file
        if(assetType == "audio") {

            // Get the ID of the comment
            var commentAPIId = $(this).attr("data-api-comment-id");

            // Get the time of the current comment
            var currentTime = $("#comments-container .comment#comment-"+commentAPIId).attr("data-time");

            // Position the player on to this time
            sound.seek( currentTime );
        }

        if((assetType == "audio") || (assetType == "video")) {

            // Get the ID of the comment
            var correspondingMarkerId = $(this).attr("data-comment-id");

            // Set the progress marker as active
            $("#comments-layer .marker").removeClass("active");
            $("#comments-layer .marker#marker-"+correspondingMarkerId).addClass("active");
        }
    });

}



// Function which activates a specific comment in the sidebar
function activateThisCommentInSidebar(target, comment) {

    // Get the ID of the chosen comment
    var thisCommentID = comment.attr("data-api-comment-id");

    // Remove the active parameter of comments
    $("#comments-container .comment").removeClass("active");

    // Add the active parameter to the chosen comment
    $("#comments-container #comment-"+thisCommentID).addClass("active");

    // Check if the click happens in the answer textarea
    var clickInAnswerField = false;
    var clickOnButton = false;
    var clickOnEmojiTextarea = false;
    var clickOnAnswerLink = false;

    if(target.tagName == "textarea") {
        clickInAnswerField = true;
    };
    if(target.tagName == "button") {
        clickOnButton = true;
    };
    if($(target).hasClass("emojionearea-editor")) {
        clickOnEmojiTextarea = true;
    };
    if($(target).hasClass("answer-to-comment")) {
        clickOnAnswerLink = true;
    };

    // If the click was not on the emoji picker
    // and not in the textarea
    if( ($(target).parents(".emojionearea-picker").size() == 0)
        && (clickOnEmojiTextarea == false)
        && (clickOnAnswerLink == false)
        && (clickOnButton == false)
        && (clickOnAnswerLink == false)) {

        $("#comments #comments-container").animate({
            scrollTop: $("#comments #comments-container").scrollTop() + $("#comments-container #comment-"+thisCommentID).position().top
        }, 2000);
    }
}
