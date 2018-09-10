// Hide the new feature disclaimer
$(document).on("click", "#new-feature-disclaimer #close-new-feature-disclaimer", function() {
    var newFeatureID = $(this).attr("data-new-feature-id");

    // Save the data in the database via ajax
    $.ajax({
        type: 'POST',
        url: ajaxMarkNewFeatureDisclaimerAsReadURL,
        data: {
            'feature_id': newFeatureID
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            $("#new-feature-disclaimer").fadeOut();
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
});
