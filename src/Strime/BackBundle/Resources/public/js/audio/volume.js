// Function which sets the volume of the player on page load.
function setVolumeOnLoad() {

    // If the volume is null
    if(audioVolume == 0) {
        $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-0");
    }

    // If the volume is below 35%
    else if(audioVolume < 0.35) {
        $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-1");
    }

    // If the volume is over 70%
    else if(audioVolume > 0.70) {
        $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-3");
    }

    // If the volume is between 35% and 70%
    else {
        $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-2");
    }

    // Change the appearance of the volume bar
    changeVolumeBarAppearance(audioVolume);
}


// Function which activates or deactivates sound
function activateSound(sound) {

    $(document).on("click", "#audio #audio-inner #audio-comments-controls .audio-volume-menu-button", function() {

        // Make sure that the click happened on the button and not on a child div
        if (!$(event.target).is('.audio-volume-menu-button *')) {

            // If the volume is already null, set it to audioVolume previously defined
            if(audioVolume == 0) {
                audioVolume = 0.6;
                $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-2");
            }
            else {
                audioVolume = 0;
                $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-0");
            }

            // Change the appearance of the volume bar
            changeVolumeBarAppearance(audioVolume);

            // Set the new volume for the player
            sound.volume(audioVolume);
        }
    });
}


// Function which deals with the fact that the user changes the volume of the player
function changeVolume(sound) {

    $(document).on("mousedown", "#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar", function(e) {

        // Detect the position from the left of the mouse
        var parentOffset = $(this).offset();
        var relX = e.pageX - parentOffset.left;

        // Get the full width of the volume bar
        var volumeBarWidth = $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar").width();

        // Define which percentage it is
        var leftPositionPercentage = relX / volumeBarWidth;

        // Make sure that we stay inside the range
        if(leftPositionPercentage < 0) {
            leftPositionPercentage = 0
        }
        else if(leftPositionPercentage > 1) {
            leftPositionPercentage = 1;
        }

        // Change the width of the bar
        changeVolumeBarAppearance(leftPositionPercentage);
    });

    $(document).on("mouseup", "#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar", function(e) {

        // Detect the position from the left of the mouse
        var parentOffset = $(this).offset();
        var relX = e.pageX - parentOffset.left;

        // Get the full width of the volume bar
        var volumeBarWidth = $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar").width();

        // Define which percentage it is
        var leftPositionPercentage = relX / volumeBarWidth;

        // Make sure that we stay inside the range
        if(leftPositionPercentage < 0) {
            leftPositionPercentage = 0
        }
        else if(leftPositionPercentage > 1) {
            leftPositionPercentage = 1;
        }

        // If the volume is null
        if(leftPositionPercentage == 0) {
            $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-0");
        }

        // If the volume is below 35%
        else if(leftPositionPercentage < 0.35) {
            $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-1");
        }

        // If the volume is over 70%
        else if(leftPositionPercentage > 0.70) {
            $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-3");
        }

        // If the volume is between 35% and 70%
        else {
            $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button").removeClass("audio-vol-0").removeClass("audio-vol-1").removeClass("audio-vol-2").removeClass("audio-vol-3").addClass("audio-vol-2");
        }

        // Set the new volume for the player
        audioVolume = leftPositionPercentage;
        sound.volume(audioVolume);

        // Change the width of the bar
        changeVolumeBarAppearance(audioVolume);
    });
}



// Function which changes the appearance of the volume bar
function changeVolumeBarAppearance(audioVolume) {

    var audioVolumePercentage = Math.round(audioVolume * 100);
    $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar").attr("aria-valuenow", audioVolumePercentage).attr("aria-valuetext", audioVolumePercentage+"%");
    $("#audio #audio-inner #audio-comments-controls .audio-volume-menu-button .audio-volume-bar .audio-volume-level").css("width", audioVolumePercentage+"%");
}



// Function which saves in the session the volume of the audio when it's changed
function saveAudioVolume(sound) {

    // Get the new volume
    var newVolume = sound.volume();

    // Send the ajax requesto to save it in the session.
    $.ajax({
        type: 'POST',
        url: ajaxSaveAudioVolumeURL,
        data: {
            'volume': newVolume
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the volume has been updated
            if(obj.status == "success") {

                // Don't do anything special
            }

            else {

                // Don't do anything special
            }
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
}
