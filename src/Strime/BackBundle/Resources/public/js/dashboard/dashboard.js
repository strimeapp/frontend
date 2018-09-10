$(document).ready(function() {


	// On click on the delete asset button,
	// redirect the user to the target page
	$(document).on("click", ".asset .asset-actions .asset-delete", function(e) {

		e.preventDefault();

		// Get the parameters
		var targetPage = $(this).attr("data-target");
		var assetName = $(this).attr("data-asset-name");
		var assetType = $(this).attr("data-asset-type");

		// Change the content of the modal based on the type of the file.
		if(assetType == "video") {
			$("#deleteAssetModal h4").empty().text(modalDeleteAssetVideo);
			$("#deleteAssetModal p.sure-wanna-delete").empty().html(modalDeleteAssetSureWannaDeleteVideo);
			$("#deleteAssetModal p.will-free-space").empty().text(modalDeleteAssetWillFreeSpaceVideo);
			$("#deleteAssetModal a#confirm-asset-deletion").attr("title", modalDeleteAssetDeleteThisVideo);
			$("#deleteAssetModal button.yes-delete").empty().text(modalDeleteAssetYesDeleteVideo);
		}
		else if(assetType == "image") {
			$("#deleteAssetModal h4").empty().text(modalDeleteAssetImage);
			$("#deleteAssetModal p.sure-wanna-delete").empty().html(modalDeleteAssetSureWannaDeleteImage);
			$("#deleteAssetModal p.will-free-space").empty().text(modalDeleteAssetWillFreeSpaceImage);
			$("#deleteAssetModal a#confirm-asset-deletion").attr("title", modalDeleteAssetDeleteThisImage);
			$("#deleteAssetModal button.yes-delete").empty().text(modalDeleteAssetYesDeleteImage);
		}
		else {
			$("#deleteAssetModal h4").empty().text(modalDeleteAssetFile);
			$("#deleteAssetModal p.sure-wanna-delete").empty().html(modalDeleteAssetSureWannaDeleteFile);
			$("#deleteAssetModal p.will-free-space").empty().text(modalDeleteAssetWillFreeSpaceFile);
			$("#deleteAssetModal a#confirm-asset-deletion").attr("title", modalDeleteAssetDeleteThisFile);
			$("#deleteAssetModal button.yes-delete").empty().text(modalDeleteAssetYesDeleteFile);
		}

		// Set the parameters in the modal
		$("#deleteAssetModal .asset-name").empty().text(assetName);
		$("#deleteAssetModal #confirm-asset-deletion").attr("href", targetPage);

		// Make sure the user is ready to delete this asset
		// Show a modal
		$('#deleteAssetModal').modal({
			'backdrop': false,
			'keyboard': false
		});
	});


	// On click on the delete project button,
	// redirect the user to the target page
	$(document).on("click", ".asset .asset-actions .project-delete", function(e) {

		e.preventDefault();

		// Get the parameters
		var targetPage = $(this).attr("data-target");
		var projectName = $(this).attr("data-project-name");

		// Set the parameters in the modal
		$("#deleteProjectModal .project-name").empty().text(projectName);
		$("#deleteProjectModal #confirm-project-deletion").attr("href", targetPage);

		// Make sure the user is ready to delete this project
		// Show a modal
		$('#deleteProjectModal').modal({
			'backdrop': false,
			'keyboard': false
		});
	});


	// If the project creation form is submitted
	$("form#add-project-form").on("submit", function(e) {

		// Display the loader and hide the submit button
		$("form#add-project-form button[type=submit]").hide();
		$("form#add-project-form .loader-container").show();
	});


	// If the user clicks on the button to resend the confirmation link
	$(document).on("click", ".modal#confirmEmailModal button", function(e) {

		// Send a request to the endpoint to resend this message
    	$.ajax({
			type: 'GET',
			url: ajaxResendEmailConfirmationMessageAction,
			success: function(data, textStatus, jqXHR){

				// Get the response
				var obj = $.parseJSON(data);
				// console.log(obj);

				// Check the status
				// If the message has not been sent
				if(obj.status == "error") {

					var message = dashboardJsTextErrorSending;
				}

				// If the details of the encoding job have been properly gathered
				else {
					var message = dashboardJsTextMailSent;
				}

				// Display the message in the button
				$(".modal#confirmEmailModal button div").fadeOut(function() {
					$(this).empty().text(message).fadeIn();
				});

				// Re-display the original message
				setTimeout(function() {
					message = dashboardJsTextResendConfirmationEmail;
					$(".modal#confirmEmailModal button div").fadeOut(function() {
						$(this).empty().text(message).fadeIn();
					});
				}, 3000);
			},
			error: function(data, textStatus, jqXHR){
				var obj = $.parseJSON(data);
				// console.log(obj);
			}
		});

	});


	// Periodically check if there are enconding jobs
	// If they are new, append them
	// Update the encoding status
	var updateEncodingJobs = setInterval( function(){
		updateEncodingJobsTimer();
		updateAudioEncodingJobsTimer();
	}, 3000 );

	function updateEncodingJobsTimer() {

	    // We get all the encoding jobs in the page
	    $(".asset.video .encoding-overlay").each(function() {

	    	var encodingJobId = $(this).attr("data-encoding-job-id");

	    	// Get the status of the encoding job
	    	$.ajax({
				type: 'POST',
				url: ajaxGetEncodingJobStatusURL,
				data: {
					encoding_job_id: encodingJobId,
					asset_type: "video"
				},
				success: function(data, textStatus, jqXHR){

					// Get the response
					var obj = $.parseJSON(data);
					// console.log(obj);

					// Check the status
					// If the message has not been sent
					if(obj.response_code != 200) {

						// Check the reason of the error
						if(obj.error_source == "not_logged_in")
							var message = dashboardJsTextYouMustBeLoggedIn;

						// In this case the reason is probably that the encoding is done
						// Therefore, we hide the overlay
						else if(obj.error_source == "encoding_job_not_found") {
							var message = dashboardJsTextWeCouldntFindInformationForThisEncoding;

							// Add the links to the asset
							var assetLinksPrepend = '<a href="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-url")+'" ondragstart="dragAsset(event)" draggable="true" ondragend="dragEndAsset(event)">';
							assetLinksPrepend += '<div class="asset-actions">';
							assetLinksPrepend += '<div class="asset-delete" data-target="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-delete-url")+'" data-asset-name="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-name")+'" data-asset-type="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-type")+'">';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="asset-share" data-asset-id="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-id")+'" data-asset-url="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-url")+'" data-content-type="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-type")+'" data-content-id="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-id")+'" data-content-name="'+$(".encoding-overlay#encoding-job-"+encodingJobId).attr("data-asset-name")+'">';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="clear"></div>';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="see-asset">';
							assetLinksPrepend += '<div class="see-asset-inner">';
							assetLinksPrepend += '<button>';
							assetLinksPrepend += dashboardJsTextSeeTheVideo;
							assetLinksPrepend += '</button>';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '</div>';

							var assetLinksAppend = '</a>';

							$(".encoding-overlay#encoding-job-"+encodingJobId).parent(".asset").prepend(assetLinksPrepend).append(assetLinksAppend);

							// Remove the encoding overlay
							$(".encoding-overlay#encoding-job-"+encodingJobId).remove();
						}

						else {
							var message = dashboardJsTextErrorWhileCollectingInformationFromEncoding;
						}
					}

					// If the details of the encoding job have been properly gathered
					else {
						$(".encoding-overlay#encoding-job-"+encodingJobId).children().children("span.encoding-progress").empty().text( obj.encoding_status );
						$(".encoding-overlay#encoding-job-"+encodingJobId).children().children(".encoding-job-progress-bar").children(".encoding-job-progress-bar-inner").css("width", obj.encoding_status+"%");
					}
				},
				error: function(data, textStatus, jqXHR){
					var obj = $.parseJSON(data);
					// console.log(obj);
				}
			});
	    });
	}

	function updateAudioEncodingJobsTimer() {

	    // We get all the encoding jobs in the page
	    $(".asset.audio .encoding-overlay").each(function() {

	    	var encodingJobId = $(this).attr("data-encoding-job-id");

	    	// Get the status of the encoding job
	    	$.ajax({
				type: 'POST',
				url: ajaxGetEncodingJobStatusURL,
				data: {
					encoding_job_id: encodingJobId,
					asset_type: "audio"
				},
				success: function(data, textStatus, jqXHR){

					// Get the response
					var obj = $.parseJSON(data);
					// console.log(obj);

					// Check the status
					// If the message has not been sent
					if(obj.response_code != 200) {

						// Check the reason of the error
						if(obj.error_source == "not_logged_in")
							var message = dashboardJsTextYouMustBeLoggedIn;

						// In this case the reason is probably that the encoding is done
						// Therefore, we hide the overlay
						else if(obj.error_source == "encoding_job_not_found") {
							var message = dashboardJsTextWeCouldntFindInformationForThisEncoding;

							// Add the links to the asset
							var assetLinksPrepend = '<a href="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-url")+'" ondragstart="dragAsset(event)" draggable="true" ondragend="dragEndAsset(event)">';
							assetLinksPrepend += '<div class="asset-actions">';
							assetLinksPrepend += '<div class="asset-delete" data-target="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-delete-url")+'" data-asset-name="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-name")+'" data-asset-type="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-type")+'">';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="asset-share" data-asset-id="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-id")+'" data-asset-url="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-url")+'" data-content-type="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-type")+'" data-content-id="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-id")+'" data-content-name="'+$(".encoding-overlay#encoding-job-audio-"+encodingJobId).attr("data-asset-name")+'">';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="clear"></div>';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '<div class="see-asset">';
							assetLinksPrepend += '<div class="see-asset-inner">';
							assetLinksPrepend += '<button>';
							assetLinksPrepend += dashboardJsTextSeeTheAudio;
							assetLinksPrepend += '</button>';
							assetLinksPrepend += '</div>';
							assetLinksPrepend += '</div>';

							var assetLinksAppend = '</a>';

							$(".encoding-overlay#encoding-job-audio-"+encodingJobId).parent(".asset").prepend(assetLinksPrepend).append(assetLinksAppend);

							// Remove the encoding overlay
							$(".encoding-overlay#encoding-job-audio-"+encodingJobId).remove();
						}

						else {
							var message = dashboardJsTextErrorWhileCollectingInformationFromEncoding;
						}
					}

					// If the details of the encoding job have been properly gathered
					else {
						$(".encoding-overlay#encoding-job-audio-"+encodingJobId).children().children("span.encoding-progress").empty().text( obj.encoding_status );
						$(".encoding-overlay#encoding-job-audio-"+encodingJobId).children().children(".encoding-job-progress-bar").children(".encoding-job-progress-bar-inner").css("width", obj.encoding_status+"%");
					}
				},
				error: function(data, textStatus, jqXHR){
					var obj = $.parseJSON(data);
					// console.log(obj);
				}
			});
	    });
	}

});
