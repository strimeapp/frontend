$(document).ready(function() {

	// If the user clicks on a question
	$(document).on("click", "a.faq-question", function(e) {

		// Prevent the scroll
		e.preventDefault();

		// Get the target
		var faqTarget = $(this).attr("data-target");

		// Toggle the answer
		$(faqTarget).slideToggle();

		// Toggle a class
		if( $(this).hasClass("visible") )
			$(this).removeClass("visible");
		else
			$(this).addClass("visible");
	});

});