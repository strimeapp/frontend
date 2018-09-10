$(document).ready(function() {
    
    // On click on the feedback link
    $(document).on("click", "footer a.feedback", function() {
        
        // Get user information
        var feedbackType = $(this).attr("data-feedback");
        var browser = getBrowser();
        var browserVersion = getBrowserVersion();
        var os = getOS();
        var cookiesEnabled = navigator.cookieEnabled;
        var viewportWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0)
        var viewportHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
        // var installedPlugins = navigator.plugins;

        // Change the title of the box
        if(feedbackType == "improvement")
            $("#feedback-box .header .title").empty().text(backJsTextFeedbackSuggestImprovement);
        else
            $("#feedback-box .header .title").empty().text(backJsTextFeedbackSignalIssue);

        // Set the information in the form
        $("#feedback-box #feedback-form input#form_type").val(feedbackType);
        $("#feedback-box #feedback-form input#form_browser").val(browser);
        $("#feedback-box #feedback-form input#form_browser_version").val(browserVersion);
        $("#feedback-box #feedback-form input#form_browser_size").val(viewportWidth+"x"+viewportHeight);
        $("#feedback-box #feedback-form input#form_os").val(os);
        $("#feedback-box #feedback-form input#form_cookies_enabled").val(cookiesEnabled);
        // $("#feedback-box #feedback-form input#form_installed_plugins").val(installedPlugins);

        // Hide the previous message
        $("#send-feedback-result").empty().hide();

        // Display the box
        $("#feedback-box").fadeIn();
    });



    // When the user submits the feedback form
    $("form#feedback-form").on("submit", function(e) {

        // Prevent the form to be submitted
        e.preventDefault();

        // Display the loader and hide the submit button
        $("form#feedback-form button[type=submit]").hide();
        $("form#feedback-form .loader-container").show();

        // Set the variables
        var formdata = $('form#feedback-form').serialize();
        var ajaxSendFeedbackURL = $('form#feedback-form').attr("action");

        // Block the fields
        $('form#feedback-form input#form_name').attr("disabled", "disabled").fadeOut();
        $('form#feedback-form select#form_project').attr("disabled", "disabled").fadeOut();
        $('form#feedback-form input#form_new_project_name').attr("disabled", "disabled").fadeOut();

        $.ajax({
            type: 'POST',
            url: ajaxSendFeedbackURL,
            data: formdata,
            success: function(data, textStatus, jqXHR){

                // Get the response
                var obj = $.parseJSON(data);
                // console.log(obj);

                // Hide the loader and display the submit button
                $("form#feedback-form .loader-container").hide();
                $("form#feedback-form button[type=submit]").show();

                // Display the result
                $("#send-feedback-result").empty().text(obj.message).removeClass("alert-success").removeClass("alert-danger").addClass("alert-"+obj.status).fadeIn();

                // If the message has been sent, empty the textarea
                if(obj.status == "success") {
                    $("form#feedback-form textarea").val("");
                }
            },
            error: function(data, textStatus, jqXHR){
                // console.log(data);

                // Hide the loader and display the submit button
                $("form#feedback-form .loader-container").hide();
                $("form#feedback-form button[type=submit]").show();
            }
        });
    });


    // Close the feedback box
    $(document).on("click", "#feedback-box .close", function() {
        $("#feedback-box").fadeOut();
    });

});