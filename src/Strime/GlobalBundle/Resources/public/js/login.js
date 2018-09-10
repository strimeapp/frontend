$(document).ready(function() {


	// Process the signup form through ajax
	$("form#signup-form").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Send an event to Google
		if(environmentIsProd == true) {
			ga('send', 'event', 'Form', 'submit', 'Sign up attempt');
		}

		// Display the loader and hide the submit button
		$("form#signup-form button[type=submit]").hide();
		$("form#signup-form .loader-container").show();
		$("#signup-result").empty().hide();

		// Set the variables
		var formdata = $('form#signup-form').serialize();
		var ajaxSignupURL = $('form#signup-form').attr("action");

		$.ajax({
			type: 'POST',
			url: ajaxSignupURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){
				// console.log(data);

				// Get the response
				var obj = $.parseJSON(data);

				// Check the status
				// If the user has not been created
				if(obj.response_code != 201) {

					// Check the reason of the error
					if(obj.error_source == "email_already_used")
						var message = mainJsTextUserAccountSameEmail;

					else if(obj.error_source == "missing_data")
						var message = mainJsTextMissingData;

					else if(obj.error_source == "invalid_email")
						var message = mainJsTextInvalidEmailAddress;

					// Display the message
					$("#signup-result").empty().removeClass("alert-danger").removeClass("alert-success");
					$("#signup-result").text(message).addClass("alert-danger").fadeIn();

					// Hide the loader and display the submit button
					$("form#signup-form .loader-container").hide();
					$("form#signup-form button[type=submit]").show().removeAttr("disabled");

					// Send an event to Google
					if(environmentIsProd == true) {
						ga('send', 'event', 'Form', 'submit', 'Sign up error');
					}
				}

				// If the user has been created
				else {
					var message = mainJsTextAccountCreated;

					// Display the message
					$("#signup-result").empty().removeClass("alert-danger").removeClass("alert-success");
					$("#signup-result").text(message).addClass("alert-success").fadeIn();

					// Hide the loader
					$("form#signup-form .loader-container").hide();

					// Send an event to Google
					if(environmentIsProd == true) {
						ga('send', 'event', 'Form', 'submit', 'Sign up success');
					}

					setTimeout(function() {
						window.location.href = obj.redirect;
					}, 1000);
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});



	// Process the signup form of the bottom of the home page through ajax
	$("form#signup-form-bottom").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Send an event to Google
		if(environmentIsProd == true) {
			ga('send', 'event', 'Form', 'submit', 'Sign up attempt');
		}

		// Display the loader and hide the submit button
		$("form#signup-form-bottom button[type=submit]").hide();
		$("form#signup-form-bottom .loader-container").css("display", "inline-block");
		$("#signup-bottom-result").empty().hide();

		// Set the variables
		var formdata = $('form#signup-form-bottom').serialize();
		var ajaxSignupURL = $('form#signup-form-bottom').attr("action");

		$.ajax({
			type: 'POST',
			url: ajaxSignupURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){
				// console.log(data);

				// Get the response
				var obj = $.parseJSON(data);

				// Check the status
				// If the user has not been created
				if(obj.response_code != 201) {

					// Check the reason of the error
					if(obj.error_source == "email_already_used")
						var message = mainJsTextUserAccountSameEmail;

					else if(obj.error_source == "missing_data")
						var message = mainJsTextMissingData;

					// Display the message
					$("#signup-bottom-result").empty().removeClass("alert-danger").removeClass("alert-success");
					$("#signup-bottom-result").text(message).addClass("alert-danger").fadeIn();

					// Hide the loader and display the submit button
					$("form#signup-form-bottom .loader-container").hide();
					$("form#signup-form-bottom button[type=submit]").show().removeAttr("disabled");

					// Send an event to Google
					if(environmentIsProd == true) {
						ga('send', 'event', 'Form', 'submit', 'Sign up error');
					}
				}

				// If the user has been created
				else {
					var message = mainJsTextAccountCreated;

					// Display the message
					$("#signup-bottom-result").empty().removeClass("alert-danger").removeClass("alert-success");
					$("#signup-bottom-result").text(message).addClass("alert-success").fadeIn();

					// Hide the loader
					$("form#signup-form-bottom .loader-container").hide();

					if(environmentIsProd == true) {
						ga('send', 'event', 'Form', 'submit', 'Sign up success');
					}

					setTimeout(function() {
						window.location.href = obj.redirect;
					}, 1000);
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// Process the login form through ajax
	$("form#login-form").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Display the loader and hide the submit button
		$("form#login-form button[type=submit]").hide();
		$("form#login-form .loader-container").show();

		// Set the variables
		var formdata = $('form#login-form').serialize();
		var ajaxLoginURL = $('form#login-form').attr("action");

		$.ajax({
			type: 'POST',
			url: ajaxLoginURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){
				// console.log(data);

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the user has not been created
				if(obj.response_code != 200) {

					// Check the reason of the error
					if(obj.error_source == "email_or_password_invalid")
						var message = mainJsTextEmailOrPwdInvalid;

					else if(obj.error_source == "missing_data")
						var message = mainJsTextMissingData;

					else if(obj.error_source == "account_deactivated")
						var message = mainJsTextYourAccountHasBeenDeactivated;

					else
						var message = mainJsTextAnErrorOccuredWhileLoggingYouIn;

					// Display the message
					$("#login-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#login-result").text(message).addClass("alert-danger").fadeIn();

					// Hide the loader and display the submit button
					$("form#login-form .loader-container").hide();
					$("form#login-form button[type=submit]").show();
				}

				// If the user has been created
				else {
					var message = mainJsTextYouAreNowLoggedIn;

					// Display the message
					$("#login-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#login-result").text(message).addClass("alert-success").fadeIn();

					// Hide the loader
					$("form#login-form .loader-container").hide();

					setTimeout(function() {
						window.location.href = obj.redirect;
					}, 1000);
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// Process the forgotten password form through ajax
	$("form#forgotten-password-form").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Display the loader and hide the submit button
		$("form#forgotten-password-form button[type=submit]").hide();
		$("form#forgotten-password-form .loader-container").show();

		// Set the variables
		var formdata = $('form#forgotten-password-form').serialize();
		var ajaxForgottenPasswordURL = $('form#forgotten-password-form').attr("action");

		$.ajax({
			type: 'POST',
			url: ajaxForgottenPasswordURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){
				// console.log(data);

				// Get the response
				var obj = $.parseJSON(data);

				// Check the status
				// If the form has not be properly processed
				if(obj.response_code != 200) {

					// Check the reason of the error
					if(obj.error_source == "not_put_request")
						var message = mainJsTextErrorOccuredWhileSendingRequest;

					else if(obj.error_source == "user_doesnt_exist")
						var message = mainJsTextEmailAddressDoesntExist;

					else if(obj.error_source == "error_saving_data")
						var message = mainJsTextErrorWhileSavingNewPassword;

					// Display the message
					$("#forgotten-password-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#forgotten-password-result").text(message).addClass("alert-danger").fadeIn();

					// Hide the loader and display the submit button
					$("form#forgotten-password-form .loader-container").hide();
					$("form#forgotten-password-form button[type=submit]").show();
				}

				// If the form has been properly processed
				else {
					var message = mainJsTextNewPasswordHasBeenSendByEmail;

					// Display the message
					$("#forgotten-password-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#forgotten-password-result").text(message).addClass("alert-success").fadeIn();

					// Hide the loader and display the submit button
					$("form#forgotten-password-form .loader-container").hide();
					$("form#forgotten-password-form button[type=submit]").show();

					// Send an event to Google
					if(environmentIsProd == true) {
						ga('send', 'event', 'Form', 'submit', 'Forgotten password');
					}
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// Focus on the email field when the login modal is opened
	$('#loginModal').on('show.bs.modal', function (event) {
		setTimeout(function() {
			$("form#login-form input#form_email").focus();
		}, 500);
	});


	// Focus on the email field when the signup modal is opened
	$('#signupModal').on('show.bs.modal', function (event) {
		setTimeout(function() {
			$("form#signup-form input#form_first_name").focus();
		}, 500);
	});


	// Slide from login form to forgotten password form
	$(document).on("click", ".go-to-forgotten-password", function() {
		$("#login-block").fadeOut();
		setTimeout(function() {
			$("#forgotten-password-block").fadeIn();
			$("#forgotten-password-block input#form_email").focus();
		}, 500);
	});
	$(document).on("click", ".back-to-login", function() {
		$("#forgotten-password-block").fadeOut();
		setTimeout(function() {
			$("#login-block").fadeIn();
			$("#login-block input#form_email").focus();
		}, 500);
	});



    // Switch between login and signup modal
    $(document).on("click", ".modal #go-to-login", function() {
        $('#loginModal').modal('toggle');
        $('#signupModal').modal('toggle');
    });
    $(document).on("click", ".modal #go-to-signup", function() {
        $('#signupModal').modal('toggle');
        $('#loginModal').modal('toggle');
    });

});


// Send the ajax request when the user signs in with a social button
function sendAjaxSigninRequest(ajaxSocialSigninURL, userEmail, userImage, userFirstName, userLastName, userIdToken, social_tool, google_event, mainJsTextUserAccountSameEmail, mainJsTextMissingData, mainJsTextInvalidEmailAddress, mainJsTextAnErrorOccuredWhileLoggingYouIn) {
	setTimeout(function() {
		$(".modal .modal-loading-overlay").css("visibility", "visible").css("opacity", "1");
	}, 1000);

	$.ajax({
		type: 'POST',
		url: ajaxSocialSigninURL,
		data: {
			'email': userEmail,
			'image': userImage,
			'first_name': userFirstName,
			'last_name': userLastName,
			'id_token': userIdToken,
			'social_tool': social_tool
		},
		success: function(data, textStatus, jqXHR){
			// console.log(data);

			// Get the response
			var obj = $.parseJSON(data);
			// console.log(obj);

			// Check the status
			// If the user has not been created
			if(obj.response_code != 200) {

				// Check the reason of the error
				if(obj.error_source == "email_already_used")
					var message = mainJsTextUserAccountSameEmail;

				else if(obj.error_source == "missing_data")
					var message = mainJsTextMissingData;

				else if(obj.error_source == "invalid_email")
					var message = mainJsTextInvalidEmailAddress;

				else {
					var message = mainJsTextAnErrorOccuredWhileLoggingYouIn;
				}

				// Display the message
				$("#signup-result").empty().removeClass("alert-danger").removeClass("alert-success");
				$("#signup-result").text(message).addClass("alert-danger").fadeIn();

				// Hide the loader
				$(".modal#signupModal .modal-loading-overlay").css("visibility", "none").css("opacity", "0");

				// Send an event to Google
				if(environmentIsProd == true) {
					ga('send', 'event', 'Single Signin', google_event, google_event+' Sign up error');
				}
			}

			// If the user has been created
			else {

				// Send an event to Google
				if(environmentIsProd == true) {
					ga('send', 'event', 'Single Signin', google_event, google_event+' Sign up success');
				}

				setTimeout(function() {
					window.location.href = obj.redirect;
				}, 1000);
			}
		},
		error: function(data, textStatus, jqXHR){
			// console.log(data);
		}
	});
}
