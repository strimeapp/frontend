$(document).ready(function() {


	// Adapt the size of modal overlays
	$('.modal').on('shown.bs.modal', function (e) {

		// Get the content element
		var content = $(this).find(".modal-content");

		// Get the overlay element
		var overlay = content.find(".modal-loading-overlay");

		// Get the height of the parent
		var contentHeight = content.height();

		// Set the height of the overlay
		overlay.height(contentHeight);

	});

});
