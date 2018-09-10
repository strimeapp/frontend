$(document).ready(function() {

	// When the form to edit the profil is submitted
	$("form#edit-mail-notification-settings-form").on("submit", function(e) {

		// Display the loader and hide the submit button
		$("form#edit-mail-notification-settings-form button[type=submit]").hide();
		$("form#edit-mail-notification-settings-form .loader-container").show();
	});


	// When the user clicks on the radio button to change his locale
	$(document).on("click", "form#edit-mail-notification-settings-form .comment-notification", function(e) {

		// Get the selected setting
		var newNotificationSetting = $(this).attr("data-value");

		// Uncheck the corresponding input
		$("form#edit-mail-notification-settings-form .comment-notification input").prop("checked", false);

		// Set the new setting in the form
		$("form#edit-mail-notification-settings-form input#form_mail_notification").val(newNotificationSetting);

		// Check the corresponding input
		$(this).find("input").prop("checked", true);
	});


	// When the user clicks on the swith button to change his newsletter settings
	$(document).on("click", "form#edit-mail-notification-settings-form .newsletter .switch .slider", function(e) {

		// Get the current value
		var currentNewsletterSetting = $("form#edit-mail-notification-settings-form input#form_opt_in").val();

		// Change the value in the form
		if(currentNewsletterSetting == 0) {
			$("form#edit-mail-notification-settings-form input#form_opt_in").val(1);
			$("form#edit-mail-notification-settings-form .newsletter .switch-description").empty().text(backJsTextNewsletterActivated);
		}
		else {
			$("form#edit-mail-notification-settings-form input#form_opt_in").val(0);
			$("form#edit-mail-notification-settings-form .newsletter .switch-description").empty().text(backJsTextNewsletterDeactivated);
		}
	});


	// When the user clicks on the button to test the webhook
	$(document).on("click", "a#slack-test-webhook", function(e) {

		e.preventDefault();

		// Send the request to send the test notification
		$.ajax({
			type: 'GET',
			url: ajaxEditProjectURL,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the comment has not been updated
				if(obj.response_code == 200) {

					// Change the content of the button
	    			$("a#slack-test-webhook span").fadeOut().empty().text(backJsTextNotificationSent).fadeIn();

	    			setTimeout(function(){
	    				$("a#slack-test-webhook span").fadeOut().empty().text(backJsTextTestTheWebhook).fadeIn();
	    			}, 3000);
				}
				else {

					// Change the content of the button
	    			$("a#slack-test-webhook span").fadeOut().empty().text(backJsTextTestFailed).fadeIn();

	    			setTimeout(function(){
	    				$("a#slack-test-webhook span").fadeOut().empty().text(backJsTextTestTheWebhook).fadeIn();
	    			}, 3000);
				}

	    		// Reset the classes
				$("body.project h1").removeClass("editing").addClass("editable");
			},
			error: function(data, textStatus, jqXHR){
				// console.log(data);
			}
		});
	});


	// Load Youtube iframe API
	// This code loads the IFrame Player API code asynchronously.
	var tag = document.createElement('script');

	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	setTimeout(function(){
		onYouTubeIframeAPIReady();
	}, 2000);

	// This function creates an <iframe> (and YouTube player)
	// after the API code downloads.
	var player;
	function onYouTubeIframeAPIReady() {
		player = new YT.Player('youtube-player', {
			height: '315',
			width: '100%',
			videoId: 'pO9VghYQUlQ',
			events: {
				'onReady': onPlayerReady
			}
		});
	}

	// The API will call this function when the video player is ready.
	function onPlayerReady(event) {
		// event.target.playVideo();
	}

	function playVideo() {
		player.playVideo();
	}

	function stopVideo() {
		player.stopVideo();
	}


	// When the user clicks on the link to see Slack's video tutorial
	$(document).on("click", "a.see-slack-video-tutorial", function(e){

		// Open the modal
		$('#slackVideoTutorialModal').modal({
			'keyboard': true
		});
		setTimeout(playVideo, 100);

		// If the user closes the modal, stop the video
		$('#slackVideoTutorialModal').on('hide.bs.modal', function (e) {
			setTimeout(stopVideo, 100);
		})
	});


});
