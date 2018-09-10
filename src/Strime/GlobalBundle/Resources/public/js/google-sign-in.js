function onSuccess(googleUser) {

    // If the user is not yet logged in the app
    if(userLoggedIn == false) {

        // Set the variables of the profile
        var profile = googleUser.getBasicProfile();

        if(environmentIsProd == false) {
            console.log(profile.getName());
        }

        // Get the details of the user
        var userEmail = profile.getEmail();
        var userImage = profile.getImageUrl();
        var userFirstName = profile.getGivenName();
        var userLastName = profile.getFamilyName();
        var userIdToken = googleUser.getAuthResponse().id_token;

        sendAjaxSigninRequest(ajaxSocialSigninURL, userEmail, userImage, userFirstName, userLastName, userIdToken, 'google', 'Google', mainJsTextUserAccountSameEmail, mainJsTextMissingData, mainJsTextInvalidEmailAddress, mainJsTextAnErrorOccuredWhileLoggingYouIn);
    }
}

function onFailure(error) {
    if(environmentIsProd == false) {
        console.log(error);
    }
}

function renderGoogleButton() {

    // If there is an internet connection
    // And if the user is not yet logged in the app
    if(navigator.onLine) {

        // Load Google API
        gapi.load('auth2', function() {
            gapi.auth2.init();

            // If the user is logged in Google but not the app, sign him out of Google first.
            if(userLoggedIn == false) {
                var GoogleAuth = gapi.auth2.getAuthInstance();

                if(GoogleAuth.isSignedIn.get() == true) {
                    googleSignOut();
                }
            }
        });

        // Render the buttons
        gapi.signin2.render('google-signup', {
            'scope': 'profile email',
            'width': 240,
            'height': 50,
            'longtitle': true,
            'theme': 'dark',
            'onsuccess': onSuccess,
            'onfailure': onFailure
        });
        gapi.signin2.render('google-signin', {
            'scope': 'profile email',
            'width': 240,
            'height': 50,
            'longtitle': true,
            'theme': 'dark',
            'onsuccess': onSuccess,
            'onfailure': onFailure
        });
    }
}
