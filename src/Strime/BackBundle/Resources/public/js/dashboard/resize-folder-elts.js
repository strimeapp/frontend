$(document).ready(function() {

	// For each folder
	$(".asset.project").each(function() {

		// Get the size of the details
		var folderDetailsHeight = $(this).find(".asset-details-inner").height();

		// Define the height of the hover div
		var folderHeight = $(this).height();
		var seeAssetHeight = folderHeight - folderDetailsHeight;

		// Set the new height
		$(this).find(".see-asset").css("height", seeAssetHeight+"px");

	});

});
