/*
 *
 * If the user posted a link into a comment, automatically add a link tag to the HTML
 *
 */


$(document).ready(function() {

	$('.comment .text-inner').linkify({
	    target: "_blank"
	});
	$('.comment-answer .text').linkify({
	    target: "_blank"
	});

});
