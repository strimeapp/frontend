$(document).ready(function() {

	// When the form to edit the profil is submitted
	$("form#edit-profile-form").on("submit", function(e) {

		// Display the loader and hide the submit button
		$("form#edit-profile-form button[type=submit]").hide();
		$("form#edit-profile-form .loader-container").show();
	});

	// When the form to edit the password is submitted
	$("form#edit-profile-password-form").on("submit", function(e) {

		// Display the loader and hide the submit button
		$("form#edit-profile-password-form button[type=submit]").hide();
		$("form#edit-profile-password-form .loader-container").show();
	});

    // When the form to change billing details is submitted
    $("form#edit-billing-profile-form").on("submit", function(e) {

        // Display the loader and hide the submit button
        $("form#edit-billing-profile-form button[type=submit]").hide();
        $("form#edit-billing-profile-form .loader-container").show();
    });

    // When the form to apply a coupon is submitted
    $("form#apply-discount-form").on("submit", function(e) {

        // Display the loader and hide the submit button
        $("form#apply-discount-form button[type=submit]").hide();
        $("form#apply-discount-form .loader-container").show();
    });

	// When the user clicks on the radio button to change his locale
	$(document).on("click", "form#edit-profile-form #choose-locale .language", function(e) {

		// Get the selected locale
		var newLocale = $(this).attr("data-locale");

		// Remove the class current from the selected language
		$("form#edit-profile-form #choose-locale .language.current").removeClass("current");
		$("form#edit-profile-form #choose-locale .language input").prop("checked", false);

		// Set the new locale
		$("form#edit-profile-form input#form_locale").val(newLocale);

		// Add the class current to the selected language
		$(this).addClass("current");
		$(this).find("input").prop("checked", true);
	});


    // On click on the delete account link,
    // redirect the user to the users page
    $(document).on("click", ".account-delete", function(e) {

        e.preventDefault();

        // Get the parameters
        var targetPage = $(this).attr("data-target");

        // Set a default link
        $("#deleteAccountModal #confirm-account-deletion").attr("href", "#");

        $(document).on("keyup", "#deleteAccountModal input#deletion-confirmation", function() {
        	var deletionConfirmation = $(this).val();

        	if(deletionConfirmation == profileJsTextDelete) {

				// Signout the user from Google
				var auth2 = gapi.auth2.getAuthInstance();
			    auth2.signOut();

				// Redirect the user
        		$("#deleteAccountModal #confirm-account-deletion").attr("href", targetPage);
        	}
        	else {
        		$("#deleteAccountModal #confirm-account-deletion").attr("href", "#");
        	}
        });

        // Make sure the user is ready to delete this video
        // Show a modal
        $('#deleteAccountModal').modal({
            'keyboard': false
        });
    });


    // When the user clicks on the button to change his payment method
    $(document).on("click", "button#change-credit-card", function() {

        // Hide previous errors
        $('#payment-form .payment-errors').fadeOut().empty();

        // Display the name of the offer
        var action = $(this).attr("data-action");
        var title = $(this).attr("data-title");
        $("#changeCreditCardModal h4").empty().text(title);
        $("#changeCreditCardModal form#payment-form button").empty().text(action);
    });



    // Function which handles Stripe response
    var stripeResponseHandler = function(status, response) {
        var $form = $('#payment-form');

        // If an error occured while processing the card by Stripe
        if(response.error) {

            // Show the errors on the form
            // $form.find('.payment-errors').text(response.error.message);
            $form.find('button').prop('disabled', false);

            switch(response.error.code) {
                case "invalid_number":
                case "incorrect_number":
                    $form.find('.payment-errors').text(profileJsTextCreditCardNumberInvalid);
                    break;
                case "invalid_expiry_month":
                    $form.find('.payment-errors').text(profileJsTextCreditCardExpirationDateInvalid);
                    break;
                case "invalid_expiry_year":
                    $form.find('.payment-errors').text(profileJsTextCreditCardExpirationDateInvalid);
                    break;
                case "invalid_cvc":
                    $form.find('.payment-errors').text(profileJsTextCreditCardSecurityCodeInvalid);
                    break;
                case "incorrect_number":
                    $form.find('.payment-errors').text(profileJsTextErrorInCreditCardNumber);
                    break;
                case "expired_card":
                    $form.find('.payment-errors').text(profileJsTextCreditCardExpired);
                    break;
                case "incorrect_cvc":
                    $form.find('.payment-errors').text(profileJsTextErrorInSecurityCode);
                    break;
                case "incorrect_zip":
                    $form.find('.payment-errors').text(profileJsTextErrorInZipCode);
                    break;
                case "card_declined":
                    $form.find('.payment-errors').text(profileJsTextCreditCardRejected);
                    break;
                case "missing":
                    $form.find('.payment-errors').text(profileJsTextNoCreditCardAssociatedToUser);
                    break;
                case "processing_error":
                    $form.find('.payment-errors').text(profileJsTextErrorOccuredWhileProcessingCreditCard);
                    break;

                default:
                    $form.find('.payment-errors').text(profileJsTextErrorOccuredWhilePaymentTransaction);
                    break;
            }

            $form.find('.payment-errors').show();
        }

        // The card has been processed by Stripe
        else {

            // response contains id and card, which contains additional card details
            var token = response.id;

            // Insert the token into the form so it gets submitted to the server
            $('form#edit-credit-card input#form_stripe_token').val(token);

            // and submit

            // Display the loader and hide the submit button
            $("form#payment-form button[type=submit]").hide();
            $("form#payment-form .loader-container").show();

            // Submit the form
            $('form#edit-credit-card').submit();
        }
    };


    // Process the payment form through Ajax
    $('#payment-form').submit(function(event) {
        var $form = $(this);

        // Disable the submit button to prevent repeated clicks
        $form.find('button').prop('disabled', true);

        Stripe.card.createToken($form, stripeResponseHandler);

        // Prevent the form from submitting with the default action
        return false;
    });

});
