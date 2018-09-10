$(document).ready(function() {

    // When someone clicks on the button in the footer to subscribe to the newsletter
    // Display the fields
    $(document).on("click", "footer button#subscribe-to-newsletter", function() {
    	$("#subscribe-to-newsletter-container").fadeIn();
    });
    $(document).on("click", "#subscribe-to-newsletter-container .close", function() {
    	$("#subscribe-to-newsletter-container").fadeOut();
    });


    // When the user clicks on the submit button to subscrive to the newsletter
    // Detect what is Mailchimp answer
    // And display a customized message.
    $(document).on("click", "#subscribe-to-newsletter-container form input[type=submit]", function() {

    	// Display a message
    	$("#subscribe-to-newsletter-container form .fields").fadeOut();

    	setTimeout(function() {
    		$("#subscribe-to-newsletter-container form .custom-message").text( mainJsTextRequestBeingProcessed ).fadeIn();
    	}, 400);

    	setTimeout(function() {
    		var mailchimpErrorMessage = $("#subscribe-to-newsletter-container form #mce-error-response").html();
    		var mailchimpSuccessMessage = $("#subscribe-to-newsletter-container form #mce-success-response").html();

    		if(mailchimpErrorMessage.length > 0) {
	    		$("#subscribe-to-newsletter-container form .custom-message").fadeOut(function() {
	    			$("#subscribe-to-newsletter-container form .custom-message").empty();
	    			$("#subscribe-to-newsletter-container form .custom-message").html( mainJsTextAnErrorOccured + "<br />" + mailchimpErrorMessage);
	    			$("#subscribe-to-newsletter-container form .custom-message").fadeIn();
	    		});
	    	}
	    	else if(mailchimpSuccessMessage.length > 0) {
	    		$("#subscribe-to-newsletter-container form .custom-message").fadeOut(function() {
	    			$("#subscribe-to-newsletter-container form .custom-message").empty();
	    			$("#subscribe-to-newsletter-container form .custom-message").html( mainJsTextOneLastThingClickOnLink );
	    			$("#subscribe-to-newsletter-container form .custom-message").fadeIn();

            		// Send an event to Google
            		if(environmentIsProd == true) {
            			ga('send', 'event', 'Form', 'submit', 'Newsletter');
            		}
	    		});
	    	}
    	}, 1500);
    });

});
