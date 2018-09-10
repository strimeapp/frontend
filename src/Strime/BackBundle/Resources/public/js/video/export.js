$(document).ready(function() {

    // When the user clicks on the button to share the video, toggle the modal box
    $(document).on("click", ".video-export svg", function() {

        if( $(".video-export #video-export-modal").hasClass("visible") ) {
            $(".video-export #video-export-modal").hide().removeClass("visible");
        }
        else {
            $(".video-export #video-export-modal").show().addClass("ondisplay").addClass("visible");

            setTimeout(function() {
                $(".video-export #video-export-modal").removeClass("ondisplay");
            }, 200);
        }

    });



	// We close the popup with the export links, if the user clicks somewhere on the screen.
	$(document).on("click", "body", function() {
		if( $("#video .video-export #video-export-modal").hasClass("visible") &&  !$("#video .video-export #video-export-modal").hasClass("ondisplay")) {
			$("#video .video-export #video-export-modal").hide().removeClass("visible");
		}
	});



    // When the user clicks on a link to export the video, pause it
    $(document).on("click", ".video-export #video-export-modal ul li", function() {

        // Set the variable for the player
        var strimePlayer = videojs("strime-video");

        // Pause the player
        strimePlayer.pause();

    });



    // When the user clicks on the button to export the video
    $(document).on("click", ".modal#exportYoutubeModal button[type=submit]", function() {

        // Display the loader and hide the submit button
        $(".modal#exportYoutubeModal button").hide();
        $(".modal#exportYoutubeModal .loader-container").show();

        // Get the data
        var youtubeName = $(".modal#exportYoutubeModal input[name='name']").val();
        var youtubeTags = $(".modal#exportYoutubeModal input[name='tags']").val();
        var youtubeStatus = $(".modal#exportYoutubeModal select[name='status']").val();
        var youtubeDescription = $(".modal#exportYoutubeModal textarea[name='description']").val();

        // Send the data to the endpoint dealing with the export
        $.ajax({
            type: 'POST',
            url: ajaxExportYoutubeURL,
            data: {
                'video_id': videoID,
                'youtube_name': youtubeName,
                'youtube_tags': youtubeTags,
                'youtube_status': youtubeStatus,
                'youtube_description': youtubeDescription
            },
            success: function(data, textStatus, jqXHR){

                // Get the response
                var obj = $.parseJSON(data);
                // console.log(obj);

                // Check the status
                // If the video has been exported to Youtube
                if(obj.response_code == 200) {

                    // Set the result message
                    var message = '<div class="alert alert-success">';
                    message += obj.message;
                    message += '</div>';

                    // Show the result message
                    $("#youtube-export-result").empty().html(message).fadeIn();

                    // Add a marker closed to the initial link
                    if(hasBeenExportedOnYoutube == false) {
                        var exportMarkerHTML = '<img src="' + exportMarkerImage + '" class="video-shared" alt="' + exportMarkerTitle + '" title="' + exportMarkerTitle + '">';
                        $("#video-export-modal ul li:nth-child(1) a .clear").before(exportMarkerHTML);
                        hasBeenExportedOnYoutube = true;
                    }

                    // Hide the result message after 5 seconds
                    setTimeout(function() {
                        $("#youtube-export-result").fadeOut().empty();
                    }, 5000);
                }
                else {

                    // If a redirect has been defined
                    if(obj.redirect !== undefined) {
                        window.location.replace( obj.redirect );
                    }

                    // If there is no redirect
                    else {

                        // Set the result message
                        var message = '<div class="alert alert-danger">';
                        message += obj.message;
                        message += '</div>';

                        // Show the result message
                        $("#youtube-export-result").empty().html(message).fadeIn();

                        // Hide the result message after 5 seconds
                        setTimeout(function() {
                            $("#youtube-export-result").fadeOut().empty();
                        }, 10000);
                    }

                }

                // Hide the loader and display the submit button
                $(".modal#exportYoutubeModal .loader-container").hide();
                $(".modal#exportYoutubeModal button").show();
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);

                // Hide the loader and display the submit button
                $(".modal#exportYoutubeModal .loader-container").hide();
                $(".modal#exportYoutubeModal button").show();
            }
        });

    });

});
