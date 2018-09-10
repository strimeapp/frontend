/*
 *
 * If the user clicks on the toggle to hide or display the resolved comments, act accordingly
 *
 */


$(document).ready(function() {

	$(document).on("click", "#comments #comments-filters #resolved-comments-toggle label", function() {

		if( ( $('#comments #comments-filters #resolved-comments-toggle label input:checked').length == 1 ) && ( $("#comments #comments-container .comment.done").is(":visible") ) )
			$("#comments #comments-container .comment.done").fadeOut();
		else if( ( $('#comments #comments-filters #resolved-comments-toggle label input:checked').length == 0 ) && ( $("#comments #comments-container .comment.done").is(":hidden") ) )
			$("#comments #comments-container .comment.done").fadeIn();

	});

});
