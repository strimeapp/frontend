$(document).ready(function() {

	// Launch the modal to identify the commenter
	// If there is already no contact in the session
	if((userID == null) && (contactID == null)) {
	    $('#changeIdentityModal').modal({
	        'backdrop': 'static',
	        'keyboard': false,
	        'show': true
	    });
	}


    // Change the color of the check icon
    $('img.choose-identity').each(function(){
        var $img = $(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        $.get(imgURL, function(data) {

            // Get the SVG tag, ignore the rest
            var $svg = $(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Check if the viewport is set, if the viewport is not set the SVG wont't scale.
            if(!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
                $svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
            }

            // Replace image with new SVG
            $img.replaceWith($svg);

        }, 'xml');

    });


	// If the user clicks on a contact, save the data in the variables and in the session
	$(document).on("click", "#changeIdentityModal .contact", function() {

		contactID = $(this).attr("data-contact-id");
		var contactEmail = $(this).attr("data-contact-email");
		userAvatar = $(this).attr("data-contact-avatar");
		author = contactEmail;
		authorType = "contact";

		// Save the contact in the session
		saveContactInSession(contactID, contactEmail);

		// Display the avatar and the email in the header
		displayEmailAndAvatarInHeader(contactEmail, backVideoJsTextChangeId, userAvatar)

		// Hide the comment edition tools if the user is not the owner of the comment
		var editionTools = '<div class="comment-edition-tools">';
        editionTools += '<div class="comment-delete" data-toggle="modal" data-target="#deleteCommentModal"></div>';
        editionTools += '<div class="comment-edit" data-toggle="modal" data-target="#editCommentModal"></div>';
        editionTools += '<div class="clear"></div>';

		$("#comments .comment").each(function() {
			// console.log(contactID);

			if( ( $(this).attr("data-comment-author-type") != "contact" ) || ( $(this).attr("data-comment-author-id") != contactID ) ) {
				$(this).find(".comment-edition-tools").remove();
			}

			if( ( $(this).attr("data-comment-author-type") == "contact" ) && ( $(this).attr("data-comment-author-id") == contactID ) ) {
				$(this).find(".text").append(editionTools);
			}

		});

        // Change the links in the language bar
        $("#languages-bar a").each(function() {

            // Get the URL of the link
            var languageUrl = $(this).attr("href");

            // Check if there is already any contact in it
            if( languageUrl.match(/\/contact\/.*/) == null ) {

                // If not, add it at the end of the URL
                languageUrl += "/contact/" + contactID;
                $(this).attr("href", languageUrl);
            }
        });

		// Close the modal
		$("#changeIdentityModal").modal('hide');
	});


	// If the user adds an address
	// Process the share by email form through ajax
	$("form#add-video-contact-form").on("submit", function(e) {

		// Prevent the form to be submitted
		e.preventDefault();

		// Hide the previous message
		$("#add-video-contact-result").empty().removeClass("alert-danger").removeClass("alert-succes").hide();

		// Set the variables
		var formdata = $('form#add-video-contact-form').serialize();
		var ajaxAddVideoContactURL = $('form#add-video-contact-form').attr("action");

		// Block the fields
		$('form#add-video-contact-form input#form_email').attr("disabled", "disabled");

		$.ajax({
			type: 'POST',
			url: ajaxAddVideoContactURL,
			data: formdata,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the message has not been sent
				if(obj.response_code != 200) {

					// Check the reason of the error
					if(obj.error_source == "email_not_valid")
						var message = backVideoJsTextEmailAddressNotValid;

					else if(obj.error_source == "form_not_updated")
						var message = backVideoJsTextErrorSendingData;

					else if(obj.error_source == "contact_not_created")
						var message = backVideoJsTextErrorSavingEmailAddress;

					else
						var message = backVideoJsTextErrorOccuredDuringIdentification;

					// Display the message
					$("#add-video-contact-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					$("#add-video-contact-result").text(message).addClass("alert-danger").fadeIn();

					// Unlock the fields
					$('form#add-video-contact-form input#form_email').removeAttr("disabled");
				}

				// If the user has been created
				else {
					var message = backVideoJsTextThanksCanBeginCommenting;

					contactID = obj.contact_id;
					var contactEmail = obj.contact_email;
					userAvatar = obj.contact_avatar;
					author = contactEmail;

					// Save the contact in the session
					saveContactInSession(contactID, contactEmail);

					// Display the avatar and the email in the header
					displayEmailAndAvatarInHeader(contactEmail, backVideoJsTextChangeId, userAvatar);

					// Display the message
					// $("#add-video-contact-result").empty().removeClass("alert-danger").removeClass("alert-succes");
					// $("#add-video-contact-result").text(message).addClass("alert-success").fadeIn();

					// Hide the comment edition tools if the user is not the owner of the comment
					$("#comments .comment").each(function() {

						if( ( $(this).attr("data-comment-author-type") != "contact" ) || ( $(this).attr("data-comment-author-id") != contactID ) ) {
							$(this).find(".comment-edition-tools").remove();
						}

					});

					// Hide the modal and show the confirmation message
					$("#changeIdentityModal").modal('hide');
					$('#changeIdentityConfirmationMessageModal').modal({
				        'backdrop': 'static',
				        'keyboard': false,
				        'show': true
				    });

					setTimeout(function(){

						// Hide the confirmation modal
						$("#changeIdentityConfirmationMessageModal").modal('hide');

						// Hide the confirmation message
						$("#add-video-contact-result").empty().removeClass("alert-danger").removeClass("alert-succes");

						// Empty the field
						$("form#add-video-contact-form input#form_email").val("");

						// Unblock the field
						$('form#add-video-contact-form input#form_email').removeAttr("disabled");

						// Add the new contact to the contact list
						var newContact = '<div class="contact" data-contact-id="'+contactID+'" data-contact-email="'+contactEmail+'" data-contact-avatar="'+userAvatar+'">';
                        newContact += '<div class="row">';
						newContact += '<div class="col-xs-2">';
						newContact += '<img src="'+userAvatar+'" class="avatar">';
						newContact += '</div>';
						newContact += '<div class="col-xs-8">';
						newContact += '<div class="email">'+contactEmail+'</div>';
						newContact += '</div>';
						newContact += '<div class="col-xs-2">';
						newContact += '<img src="'+iconCheck+'" title="'+backVideoJsTextChooseThisProfile+'" class="svg choose-identity">';
						newContact += '<div class="clear"></div>';
						newContact += '</div>';
						newContact += '</div>';
						newContact += '</div>';

						$("#changeIdentityModal #contacts-list").append( newContact );

					}, 3000);
				}
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// When the user clicks on the link to change his ID
	$(document).on("click", "#header-change-id #change-id", function() {

		// Launch the modal
		$('body.video .modal#changeIdentityModal button.close').show();
		$('#changeIdentityModal').modal({
	        'backdrop': true,
	        'keyboard': true,
	        'show': true
	    });
	});



	// Function which saves the details of a contact in the session
	function saveContactInSession(contactID, contactEmail) {

		$.ajax({
			type: 'POST',
			url: ajaxSaveContactInSessionURL,
			data: {
				'contact_id': contactID,
				'contact_email': contactEmail
			},
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	}


	// Function which displays the email and avatar in the header
	function displayEmailAndAvatarInHeader(contactEmail, backVideoJsTextChangeId, userAvatar) {
		var html = '';
		html += '<div class="contact-profile">';
		html += '<div class="contact-info">';
		html += '<div class="contact-email">'+contactEmail+'</div>';
		html += '<a href="#" id="change-id" title="' + backVideoJsTextChangeId + '">' + backVideoJsTextChangeId + '</a>';
		html += '</div>';
		html += '<div class="contact-avatar">';
		html += '<img src="'+userAvatar+'" class="avatar">';
		html += '</div>';
		html += '<div class="clear"></div>';
		html += '</div>';
		html += '<div class="clear"></div>';
		$("#header #header-change-id").html(html);
		var html = '';
	}

});
