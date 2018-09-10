// Function which saves in the session the volume of the video when it's changed
function saveVideoVolume() {

    // Set the current player
    var strimePlayer = videojs("strime-video");

    // Get the new volume
    var newVolume = strimePlayer.volume();

    // Send the ajax requesto to save it in the session.
    $.ajax({
        type: 'POST',
        url: ajaxSaveVideoVolumeURL,
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
