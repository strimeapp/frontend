$(document).ready(function() {

	// Cache references to DOM elements.
	var elms = ['track', 'timer', 'duration'];
	elms.forEach(function(elm) {
	  window[elm] = document.getElementById(elm);
	});

	var audioUpdateProgress;

	var audioParams = {
		src: [audioWebM, audioMP3],
		html5: true,
		autoplay: false,
		loop: false,
		volume: audioVolume,
        onload: function() {

			// Display the duration.
			var audioDuration = formatTime(Math.round(sound.duration()));
			$("#audio #audio-comments-controls .audio-duration .audio-time").empty().text( audioDuration );
			$("#audio #audio-comments-controls .audio-current-time .audio-time").empty().text("00:00");
        },
		onplay: function() {

			// Update the timer
			audioUpdateProgress = setInterval(
				function(){
					updatePlayerTime(sound);
					updatePlayerProgress(sound);
				},
			20);

			// Remove the paused class on the play button
			$("#content #audio #audio-comments-controls .audio-play-control").removeClass("paused");
        },
        onpause: function() {

			// Stop the progression of the timer and progress bar
			stopUpdatePlayerProgress(audioUpdateProgress);

			// Add the paused class on the play button
			$("#content #audio #audio-comments-controls .audio-play-control").addClass("paused");
        },
        onend: function() {

			// Stop the progression of the timer and progress bar
			stopUpdatePlayerProgress(audioUpdateProgress);
        },
        onstop: function() {

			// Stop the progression of the timer and progress bar
			stopUpdatePlayerProgress(audioUpdateProgress);
        },
        onseek: function() {

			// Change the progress bar position
			updatePlayerProgress(sound);
        },
		onvolume: function() {

			// Save the new volume
			saveAudioVolume(sound);
		}
	}

	var sound = new Howl(audioParams);


	// Set a function to format the time
	function formatTime(secs) {
	    var minutes = Math.floor(secs / 60) || '00';
	    var seconds = (secs - minutes * 60) || '00';

	    return minutes + ':' + (((seconds < 10) && (seconds !== '00')) ? '0' : '') + seconds;
	}


	// Function to update the playing time
	function updatePlayerTime(sound) {
		var audioDuration = formatTime(Math.round(sound.seek()));
		$("#audio #audio-comments-controls .audio-current-time .audio-time").empty().text( audioDuration );
	}

	// Function to update the progress bar
	function updatePlayerProgress(sound) {
		var audioDuration = sound.duration();
		var currentPosition = sound.seek();
		var currentPercentage = (currentPosition * 100) / audioDuration;
		$("#audio #audio-inner #audio-progress #audio-progress-inner").css("width", currentPercentage+"%");
		$("#audio #audio-inner #audio-progress-bar #audio-progress-bar-inner").css("width", currentPercentage+"%");
	}

	// Function to stop updating the progress
	function stopUpdatePlayerProgress(audioUpdateProgress) {
	    clearInterval(audioUpdateProgress);
	}


	resizeAudioAndCommentsContainer();
	$(window).resize(function() {
		resizeAudioAndCommentsContainer();
	});

	resizeWaveformBackground();
	$(window).resize(function() {
		resizeWaveformBackground();
	});

	displayAudioMarkers(sound);
	repositionMarkersOnLoad();
	playPauseAudio(sound);
	activateBackToBeginningButton(sound);
	activateBackFrom10sButton(sound);
	activateGoToNextCommentButton(sound);
	activateGoToPreviousCommentButton(sound);
	activateButtonToGloballyHideMarkersOnAudio(sound);
	activateShortcuts(sound);
	setVolumeOnLoad();
	activateSound(sound);
	changeVolume(sound);
	goToComment(sound);
	goToTime(sound);
	postComment(sound);
	answerToComment();
	postAnswer();
	populateDeleteCommentModal(sound);
	deleteComment();
	setCommentAsActive(sound);
	setCommentAsActiveWhenMarkerIsClicked(sound);
	setCommentAsDone();
	reorderComments();
	closeCommentBoxOnClickOnPlayButton(sound);
	closeCommentBoxOnEscKeypress(sound);
	populateEditCommentModal(sound);
	editComment();
	editAudioName();
	editAudioDescription();
	goToHashInUrl(sound);
	showCommentsSidebar();

});
