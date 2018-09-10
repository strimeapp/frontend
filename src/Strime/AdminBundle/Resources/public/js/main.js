$(document).ready(function() {

	// Display the submenu
    $(document).on("click", "#header #header-submenu ul#header-user li", function() {
        $("#header #header-submenu ul#header-user-submenu").toggle();
    });

    // Position the submenu on the left
    $(window).scroll(function(){

    	// Get the body position
    	var bodyTop = $(document).scrollTop();

    	// Reset the top of the submenu if needed
        if(bodyTop >= 100) {
        	$("#admin-menu ul").css("top", 0);
        }
        else {
        	var menuTop = 100 - bodyTop;
        	$("#admin-menu ul").css("top", menuTop+"px");
        }
    });


    // On click on the delete user button,
    // redirect the user to the users page
    $(document).on("click", ".user-delete", function(e) {

        e.preventDefault();

        // Get the parameters
        var targetPage = $(this).attr("data-target");

        // Set a default link
        $("#deleteUserModal #confirm-user-deletion").attr("href", "#");

        $(document).on("keyup", "#deleteUserModal input#deletion-confirmation", function() {
            var deletionConfirmation = $(this).val();

            if(deletionConfirmation == "SUPPRIMER") {
                $("#deleteUserModal #confirm-user-deletion").attr("href", targetPage);
            }
            else {
                $("#deleteUserModal #confirm-user-deletion").attr("href", "#");
            }
        });

        // Make sure the user is ready to delete this video
        // Show a modal
        $('#deleteUserModal').modal({
            'keyboard': false
        });
    });


    // On click on the delete asset button,
    // redirect the user to the corresponding page
    $(document).on("click", ".asset-delete", function(e) {

        e.preventDefault();

        // Get the parameters
        var targetPage = $(this).attr("data-target");

        // Set a default link
        $("#deleteAssetModal #confirm-asset-deletion").attr("href", "#");

        $(document).on("keyup", "#deleteAssetModal input#deletion-confirmation", function() {
            var deletionConfirmation = $(this).val();

            if(deletionConfirmation == "SUPPRIMER") {
                $("#deleteAssetModal #confirm-asset-deletion").attr("href", targetPage);
            }
            else {
                $("#deleteAssetModal #confirm-asset-deletion").attr("href", "#");
            }
        });

        // Make sure the user is ready to delete this video
        // Show a modal
        $('#deleteAssetModal').modal({
            'keyboard': false
        });
    });


    // On click on the delete user button,
    // redirect the user to the users page
    $(document).on("click", ".user-change-offer", function(e) {

        e.preventDefault();

        // Set a default link
        $("#changeOfferModal #confirm-offer-change").attr("href", "#");

        $(document).on("click", "#changeOfferModal li", function() {

            // Get the parameters
            var targetPage = $(this).attr("data-target");
            $("#changeOfferModal #confirm-offer-change").attr("href", targetPage);
            $("#changeOfferModal #confirm-offer-change button").removeAttr("disabled");
            $("#changeOfferModal li").removeClass("selected");
            $(this).addClass("selected");
        });

        // Make sure the user is ready to delete this video
        // Show a modal
        $('#changeOfferModal').modal({
            'keyboard': false
        });
    });


    // On click on the delete coupon button,
    // show a confirmation box
    $(document).on("click", ".coupon-delete", function() {

        var deleteCoupon = confirm("Es-tu sûr de vouloir supprimer ce coupon ?");
        if(deleteCoupon == false)
            return false;
        else
            return true;
    });

});
