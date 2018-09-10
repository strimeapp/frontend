// Function which allows the user to reorder comments
function reorderComments() {

    // When the user clicks on the button
    $(document).on("click", "#comments #comments-filters .order-option", function() {

        // If the button is the hourglass
        if( $(this).hasClass("hourglass") ) {

            // If the user wants them in chronological order
            if( $(this).hasClass("top") || !$(this).hasClass("active") ) {

                // Reorder the elements
                var commentsList = $("#comments #comments-container .comment");
                commentsList.sort(function(a, b){
                    return $(a).data("comment-id")-$(b).data("comment-id")
                });
                $("#comments #comments-container").html(commentsList);

                // Change the direction of the button
                $(this).removeClass("top").addClass("down");
            }

            // If the user wants them in non-chronological order
            else {

                // Reorder the elements
                var commentsList = $("#comments #comments-container .comment");
                commentsList.sort(function(a, b){
                    return $(b).data("comment-id")-$(a).data("comment-id")
                });
                $("#comments #comments-container").html(commentsList);

                // Change the direction of the button
                $(this).removeClass("down").addClass("top");
            }
        }

        // If the user clicked on the timer button
        else {

            // If the user wants them in chronological order
            if( $(this).hasClass("top") || !$(this).hasClass("active") ) {

                // Reorder the elements
                var commentsList = $("#comments #comments-container .comment");
                commentsList.sort(function(a, b){
                    return $(a).data("time")-$(b).data("time")
                });
                $("#comments #comments-container").html(commentsList);

                // Change the direction of the button
                $(this).removeClass("top").addClass("down");
            }

            // If the user wants them in non-chronological order
            else {

                // Reorder the elements
                var commentsList = $("#comments #comments-container .comment");
                commentsList.sort(function(a, b){
                    return $(b).data("time")-$(a).data("time")
                });
                $("#comments #comments-container").html(commentsList);

                // Change the direction of the button
                $(this).removeClass("down").addClass("top");
            }

        }

        // Remove the active class of the other order button
        $("#comments #comments-filters .order-option").removeClass("active");

        // Add the active class to the current order button
        $(this).addClass("active");

    });
}
