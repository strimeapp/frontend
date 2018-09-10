$(document).ready(function() {

	// Set variables
	var zeroClipboardClient;
	var contactsToShareWith = new Array();


	// On click on the share asset button,
	// display the modal
	$(document).on("click", ".asset .asset-share", function(e) {

		e.preventDefault();

		// Get the parameters
		var privateUrl = $(this).attr("data-asset-url");
		var contentType = $(this).attr("data-content-type");
		var contentName = $(this).attr("data-content-name");
		var contentID = $(this).attr("data-content-id");

		// Set the parameters in the modal
		$("#shareAssetModal .private-url .private-url-inner").empty().text(privateUrl);
		$("#shareAssetModal .private-url .copy-button").attr("data-clipboard-text", privateUrl);
		$("#shareAssetModal input#form_type").val(contentType);
		$("#shareAssetModal input#form_url").val(privateUrl);
		$("#shareAssetModal input#form_content_name").val(contentName);
		$("#shareAssetModal input#form_content_id").val(contentID);

		// Check the type of the content and change the text accordingly
		if(contentType == "project") {
			$("#shareAssetModal h4").empty().text( backJsTextShareProject );
		}
		else if(contentType == "video") {
			$("#shareAssetModal h4").empty().text(backJsTextShareVideo);
		}
		else if(contentType == "image") {
			$("#shareAssetModal h4").empty().text(backJsTextShareImage);
		}
		else {
			$("#shareAssetModal h4").empty().text(backJsTextShareFile);
		}

		// Set the ZeroClipboardClient
		zeroClipboardClient = new ZeroClipboard( $("#shareAssetModal .private-url .copy-button") );

		// Show a modal
		$('#shareAssetModal').modal({
        	'backdrop': false,
			'keyboard': false
        });
	});


	// On click on the private URL copy button
	$(document).on("click", "#shareAssetModal .private-url .copy-button", function() {

		var copyButton = $(this).find("span");

		// Change the content of the button
		copyButton.animate({opacity:0}, function() {
			$(this).empty().text( backJsTextShareCopied );
		}).animate({opacity:1});

		// Change the content back
		setTimeout(function() {
			copyButton.animate({opacity:0}, function() {
				$(this).empty().text( backJsTextShareCopy );
			}).animate({opacity:1});
		}, 2000);
	});


	// When the user clicks on the link to add a message to the sharing email
	$(document).on("click", "#shareAssetModal .share-add-message", function() {

		// Change the text of the link
		if( $("#shareAssetModal textarea").is(":visible") ) {
			$(this).empty().text( backJsTextShareAddAMessage );
			$("#shareAssetModal textarea").slideToggle();
		}
		else {
			$(this).empty().text( backJsTextShareDoNotAddMessage );
			$("#shareAssetModal textarea").slideToggle();
		}
	});


	// Process the share by email form through ajax
	$("form#share-by-email-form").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Hide the previous message
		$("#share-by-email-result").empty().removeClass("alert-danger").removeClass("alert-succes").hide();

		// Display the loader and hide the submit button
		$("form#share-by-email-form button[type=submit]").hide();
		$("form#share-by-email-form .loader-container").show();

		// Set the variables
		var formdata = $('form#share-by-email-form').serialize();
		var ajaxShareByEmailURL = $('form#share-by-email-form').attr("action");

		// Block the fields
		$('form#share-by-email-form input#form_email').attr("disabled", "disabled");
		$('form#share-by-email-form textarea#form_message').attr("disabled", "disabled");

		$.ajax({
			type: 'POST',
			url: ajaxShareByEmailURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the message has not been sent
				if(obj.response_code != 200) {

					// Check the reason of the error
					if(obj.error_source == "no_user_found")
						var message = backJsTextShareNoUserFoundForThisRequest;

					else if(obj.error_source == "project_name_already_used")
						var message = backJsTextShareProjectWithThisNameAlreadyExists;

					else if(obj.error_source == "missing_data")
						var message = backJsTextShareAllFieldsNotProvided;

					else if((obj.error_source == "file_error") || (obj.error_source == "upload_failed"))
						var message = backJsTextShareErrorOccuredWhileSendingFile;

					else
						var message = backJsTextShareErrorOccuredWhileSendingUrl;

					// Display the message
					$("#share-by-email-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#share-by-email-result").text(message).addClass("alert-danger").fadeIn();

					// Hide the loader and display the submit button
					$("form#share-by-email-form .loader-container").hide();
					$("form#share-by-email-form button[type=submit]").show();

					// Unlock the fields
					$('form#share-by-email-form input#form_email').removeAttr("disabled");
					$('form#share-by-email-form textarea#form_message').removeAttr("disabled");
				}

				// If the user has been created
				else {
					var message = backJsTextShareMessageHasBeenSent;

					// Display the message
					$("#share-by-email-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#share-by-email-result").text(message).addClass("alert-success").fadeIn();

					// Hide the loader and display the submit button
					$("form#share-by-email-form .loader-container").hide();
					$("form#share-by-email-form button[type=submit]").show();

					// Unlock the fields
					$('form#share-by-email-form input#form_email').removeAttr("disabled");
					$('form#share-by-email-form textarea#form_message').removeAttr("disabled");
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// Prevent the form to be submitted when the user presses enter
	// Prevent the user to include spaces in email addresses
	$(document).on("keypress", 'form#share-by-email-form input#form_email', function (e) {
		if((e.which == 13) || (e.which == 32)) {
			e.preventDefault();
		}
	});


	// When the user presses coma, semicolon or enter, add the email to the list
	$(document).on("keyup", 'form#share-by-email-form input#form_email', function (e) {

		if (e.which == 188 || e.which == 186 || e.which == 13) {

	        // Get the email
	        var emailToAddToTheList = $('form#share-by-email-form input#form_email').val();

	        // If the key pressed is a comma or a semicolon, remove it
	        if (e.which == 188 || e.which == 186)
	        	emailToAddToTheList = emailToAddToTheList.slice(0, -1);

	        // Add the email to the list Array
	        contactsToShareWith.push( emailToAddToTheList );


	        // Display the list
	        $('.modal #share-by-email-form .share-emails-list').show();

	        // Display it in the list
	        var listElt = '<div class="share-email-elt">'+emailToAddToTheList+'<div class="share-email-delete-elt" data-email="'+emailToAddToTheList+'">x</div></div>';
	        $('.modal #share-by-email-form .share-emails-list').prepend( listElt );

	        // Add it to the hidden list
	        var hiddenList = contactsToShareWith.join();
	        $('form#share-by-email-form input#form_emails_list').val( hiddenList );

	        // Empty the field
	        $('form#share-by-email-form .typeahead').typeahead('val', '');
	    }
	});


	// Delete an email from the sharing list
	$(document).on("click", 'form#share-by-email-form .share-emails-list .share-email-elt .share-email-delete-elt', function () {

		// Remove the email from the list
		var emailToRemoveFromTheList = $(this).attr("data-email");
		var eltIndexInList = contactsToShareWith.indexOf( emailToRemoveFromTheList );
		if (eltIndexInList > -1) {
		    contactsToShareWith.splice(eltIndexInList, 1);
		}
		var hiddenList = contactsToShareWith.join();
		$('form#share-by-email-form input#form_emails_list').val( hiddenList );


		// Remove the visual element
		$(this).parent(".share-email-elt").remove();
	});



	// Activate Typeahead
	var substringMatcher = function(strs) {
		return function findMatches(q, cb) {
			var matches, substringRegex;

			// an array that will be populated with substring matches
			matches = [];

			// regex used to determine if a string contains the substring `q`
			substrRegex = new RegExp(q, 'i');

			// iterate through the pool of strings and for any string that
			// contains the substring `q`, add it to the `matches` array
			$.each(strs, function(i, str) {
				if (substrRegex.test(str)) {
					matches.push(str);
				}
			});

			cb(matches);
		};
	};

	$('form#share-by-email-form .typeahead').typeahead({
			hint: true,
			highlight: true,
			minLength: 1
	},
	{
		name: 'contactsList',
		source: substringMatcher(contactsList)
	});


	// When the user closes the modal, empty the fields
	$(document).on("click", "#shareAssetModal button.close", function() {

		$("#shareAssetModal textarea").val("");
		$("#shareAssetModal input[type='email']").val("");
		$("#shareAssetModal .share-emails-list").empty();
	});

});
