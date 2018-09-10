$(document).ready(function() {

    // Activate the Emoji buttons
    $("#comments #comments-container .comment .comment-answer-field textarea").emojioneArea({
        pickerPosition: "top",
        filtersPosition: "top",
        tonesStyle: "checkbox"
    });
    $("#comments-layer .comment-box .comment-field textarea").emojioneArea({
        pickerPosition: "bottom",
        filtersPosition: "top",
        tonesStyle: "checkbox"
    });
    $(".modal#editCommentModal .modal-body textarea").emojioneArea({
        pickerPosition: "left",
        filtersPosition: "top",
        tonesStyle: "checkbox"
    });

});
