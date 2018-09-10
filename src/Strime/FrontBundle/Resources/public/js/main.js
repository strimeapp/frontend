$(document).ready(function() {

	// Prevent the user to include spaces in email addresses
	$(document).on("keypress", 'input[type=email]', function (e) {
		if(e.which == 32) {
			e.preventDefault();
		}
	});

});
