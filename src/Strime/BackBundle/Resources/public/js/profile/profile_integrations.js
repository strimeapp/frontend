$(document).ready(function() {

    // If the user clicks on the link to revoke Google Access
    $(document).on("click", "a.revoke-google", function(e) {

        // Prevent the screen to go up.
        e.preventDefault();

        // Revoke Google scopes
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.disconnect();

        // Edit the user to delete the Google information.
        $.ajax({
            type: 'POST',
            url: ajaxUserRevokeGoogleURL,
            success: function(data, textStatus, jqXHR){
                // console.log(data);

                // Get the response
                var obj = $.parseJSON(data);

                // Check the status
                // If the user has not been updated
                if(obj.response_code != 200) {

                    // Prepare the message
                    var message = '<div id="revoke-google-result" class="alert alert-danger" role="alert">';
                    message += backJsTextErrorOccuredWhileRevokingGoogle;
                    if(isLoggedInWithGoogle == true) {
                        message += '<br />';
                        message += backJsTextYouWillBeLoggedOut;
                    }
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-google-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-google-result").fadeOut().remove();
                    }, 4000);
                }

                // If Google information has been removed from the user profile
                else {

                    // Add the deactivated class to the service icon
                    $(".integration#google img").addClass("deactivated");

                    // Change the text in front of the icon
                    $(".integration#google .action").empty().text(backJsTextDeactivated);

                    // Prepare the message
                    var message = '<div id="revoke-google-result" class="alert alert-success" role="alert">';
                    message += backJsTextGoogleAccessRevoked;
                    if(isLoggedInWithGoogle == true) {
                        message += '<br />';
                        message += backJsTextYouWillBeLoggedOut;
                    }
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-google-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-google-result").fadeOut().remove();
                    }, 4000);

                    // If the user was logged in with Google
                    if(isLoggedInWithGoogle == true) {

                        // Disconnect the user from Google
                        // He will be automatically redirected to the signout URL.

                        // We wait for the message to be hidden
                        setTimeout(function() {
                            googleSignOut();
                        }, 4000);

                    }
                }
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);
            }
        });

    });





    // If the user clicks on the link to revoke Facebook Access
    $(document).on("click", "a.revoke-facebook", function(e) {

        // Prevent the screen to go up.
        e.preventDefault();

        // Revoke Facebook scopes
        FB.api('/'+userFacebookID+'/permissions', "DELETE", function(response) {
            console.log(response);

            if(response.error === undefined) {

                // Edit the user to delete the Google information.
                $.ajax({
                    type: 'POST',
                    url: ajaxUserRevokeFacebookURL,
                    success: function(data, textStatus, jqXHR){
                        // console.log(data);

                        // Get the response
                        var obj = $.parseJSON(data);

                        // Check the status
                        // If the user has not been updated
                        if(obj.response_code != 200) {

                            // Prepare the message
                            var message = '<div id="revoke-facebook-result" class="alert alert-danger" role="alert">';
                            message += backJsTextErrorOccuredWhileRevokingFacebook;
                            message += '</div>';

                            // Display the message
                            $("#profile-inner").prepend(message);
                            $("#profile-inner .alert#revoke-facebook-result").hide().fadeIn();

                            setTimeout(function() {
                                $("#profile-inner .alert#revoke-facebook-result").fadeOut().remove();
                            }, 4000);
                        }

                        // If Google information has been removed from the user profile
                        else {

                            // Add the deactivated class to the service icon
                            $(".integration#facebook img").addClass("deactivated");

                            // Change the text in front of the icon
                            $(".integration#facebook .action").empty().text(backJsTextDeactivated);

                            // Prepare the message
                            var message = '<div id="revoke-facebook-result" class="alert alert-success" role="alert">';
                            message += backJsTextFacebookAccessRevoked;
                            message += '</div>';

                            // Display the message
                            $("#profile-inner").prepend(message);
                            $("#profile-inner .alert#revoke-facebook-result").hide().fadeIn();

                            setTimeout(function() {
                                $("#profile-inner .alert#revoke-facebook-result").fadeOut().remove();
                            }, 4000);
                        }
                    },
                    error: function(data, textStatus, jqXHR){
                        // console.log(data);
                    }
                });
            }
            else {

                // Prepare the message
                var message = '<div id="revoke-facebook-result" class="alert alert-danger" role="alert">';
                message += backJsTextErrorOccuredWhileRevokingFacebook;
                message += '</div>';

                // Display the message
                $("#profile-inner").prepend(message);
                $("#profile-inner .alert#revoke-facebook-result").hide().fadeIn();

                setTimeout(function() {
                    $("#profile-inner .alert#revoke-facebook-result").fadeOut().remove();
                }, 4000);
            }
        });
    });



    // If the user clicks on the link to revoke Slack comment notifications
    $(document).on("click", "a.revoke-slack-comment-notification", function(e) {

        // Prevent the screen to go up.
        e.preventDefault();

        // Edit the user to delete the Slack webhook url.
        $.ajax({
            type: 'POST',
            url: ajaxUserRevokeSlackCommentNotificationURL,
            success: function(data, textStatus, jqXHR){
                // console.log(data);

                // Get the response
                var obj = $.parseJSON(data);

                // Check the status
                // If the user has not been updated
                if(obj.response_code != 200) {

                    // Prepare the message
                    var message = '<div id="revoke-slack-comment-notification-result" class="alert alert-danger" role="alert">';
                    message += backJsTextErrorOccuredWhileDeactivatingSlack;
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-slack-comment-notification-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-slack-comment-notification-result").fadeOut().remove();
                    }, 4000);
                }

                // If Google information has been removed from the user profile
                else {

                    // Add the deactivated class to the service icon
                    $(".integration#slack-comment-notification img").addClass("deactivated");

                    // Change the text in front of the icon
                    $(".integration#slack-comment-notification .action").empty().text(backJsTextDeactivated);

                    // Prepare the message
                    var message = '<div id="revoke-slack-comment-notification-result" class="alert alert-success" role="alert">';
                    message += backJsTextSlackWebhookDeactivated;
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-slack-comment-notification-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-slack-comment-notification-result").fadeOut().remove();
                    }, 4000);
                }
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);
            }
        });

    });



    // If the user clicks on the link to revoke Youtube
    $(document).on("click", "a.revoke-youtube", function(e) {

        // Prevent the screen to go up.
        e.preventDefault();

        // Edit the user to delete the Youtube webhook url.
        $.ajax({
            type: 'POST',
            url: ajaxUserRevokeYoutubeURL,
            success: function(data, textStatus, jqXHR){
                // console.log(data);

                // Get the response
                var obj = $.parseJSON(data);

                // Check the status
                // If the user has not been updated
                if(obj.response_code != 200) {

                    // Prepare the message
                    var message = '<div id="revoke-youtube-result" class="alert alert-danger" role="alert">';
                    message += backJsTextErrorOccuredWhileDeactivatingYoutube;
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-youtube-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-youtube-result").fadeOut().remove();
                    }, 4000);
                }

                // If Google information has been removed from the user profile
                else {

                    // Add the deactivated class to the service icon
                    $(".integration#youtube img").addClass("deactivated");

                    // Change the text in front of the icon
                    $(".integration#youtube .action").empty().text(backJsTextDeactivated);

                    // Prepare the message
                    var message = '<div id="revoke-youtube-result" class="alert alert-success" role="alert">';
                    message += backJsTextYoutubeAccessRevoked;
                    message += '</div>';

                    // Display the message
                    $("#profile-inner").prepend(message);
                    $("#profile-inner .alert#revoke-youtube-result").hide().fadeIn();

                    setTimeout(function() {
                        $("#profile-inner .alert#revoke-youtube-result").fadeOut().remove();
                    }, 4000);
                }
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);
            }
        });

    });

});
