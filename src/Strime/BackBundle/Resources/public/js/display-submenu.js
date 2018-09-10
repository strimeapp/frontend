$(document).ready(function() {

	// Display the submenu
	$(document).on("click", "#header #header-submenu ul#header-user li", function() {
	    $("#header #header-submenu ul#header-user-submenu").toggle();

	    // After 500ms, add the visible class, which will allow the user to hide the submenu
	    setTimeout(function() {
	    	if( $("#header #header-submenu ul#header-user-submenu").is(":visible") ) {
				$("#header-submenu ul#header-user-submenu").addClass("visible");
	    	}
	    }, 500);
	});



	// We close the submenu of the profile, if the user clicks somewhere on the screen.
	$(document).on("click", "body", function() {
		if( $("#header #header-submenu ul#header-user-submenu").hasClass("visible") ) {
			$("#header-submenu ul#header-user-submenu").hide();
			$("#header-submenu ul#header-user-submenu").removeClass("visible");
		}
	});

});