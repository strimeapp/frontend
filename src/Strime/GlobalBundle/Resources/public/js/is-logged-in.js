$(document).ready(function() {


	// Check every minute if the user is logged in
	var checkEveryMinute = setInterval(
		function() {
			isUserLoggedIn();
		}, 30000);

	// Function to check if the user is logged in
	function isUserLoggedIn() {
	    $.ajax({
			type: 'POST',
			url: ajaxIsLoggedInURL,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the result is false, display the loggin modal, prevent it to be closed, and stop the check
				if(obj.logged_in == false) {

					// Change the value of the logged in variable
					userLoggedIn = false;

					// Signout the user from Google
					var auth2 = gapi.auth2.getAuthInstance();
				    auth2.signOut();

					// Display the modal
					$(".modal#loginModal").modal({
						'backdrop': 'static',
						'keyboard': 'false'
					});

					// Hide the close button
					$(".modal#loginModal button.close").hide();

					// Focus on the login form, not the form to recover password
					$(".modal#loginModal #forgotten-password-block").hide();
					$(".modal#loginModal #login-block").show();

					// Update the CSRF token
					$(".modal#loginModal form#login-form input#form__token").val(obj.token);

					// Stop the check
					stopCheckEveryMinute();
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);


			}
		});
	}

	// Function which stops to check if the user is logged in every minute
	function stopCheckEveryMinute() {
	    clearInterval(checkEveryMinute);
	}

});
