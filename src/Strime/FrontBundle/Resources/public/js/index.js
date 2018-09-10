$(document).ready(function() {


	// Animate the stickers and clock on scroll
	// var rellax = new Rellax('.rellax');


	// Reveal the bubbles after a couple of seconds
	setTimeout(function() {
		$(".computer-bubble").show().addClass("animation-jelly");
	}, 2000);


	// Reveal the app description on scroll
	var revealOptions = {
		duration: 800,
		delay: 100,
		scale: 0.8,
		viewFactor: 0.25,
		viewOffset: {top: 74, right: 0, bottom: 0, left: 0}
	}
	window.sr = ScrollReveal();
	sr.reveal('.description-elt', revealOptions);



    /*
     * Show or hide the header
     */

     // When scroll occurs
     $(window).scroll(function(){

        // Get the position of the body
        var bodyTopPosition = document.body.scrollTop;

        // Test if the position is over or below the default menu
        if(bodyTopPosition > 75) {
            $("#fixed-header").fadeIn();
        }
        else {
            $("#fixed-header").fadeOut();
        }
    });


    // Resize the computer div if the window is resized
    var computerDivRatio = parseFloat( $("#computer").height() ) / parseFloat( $("#computer").width() );

    $( window ).resize(function() {
    	resizeComputerDiv();
	});

	function resizeComputerDiv() {
		var computer = $("#computer");
		var computerWidth = computer.width();
		var computerNewHeight = computerWidth * computerDivRatio;

		computer.css("height", computerNewHeight+"px");
	}

});
