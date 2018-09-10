
// Function which allow elements to be dropped into himself
function allowDropAsset(ev) {
    ev.preventDefault();
}

// When an asset is dragged
function dragAsset(ev) {

    // Set the draggable element ID
    var draggableEltID = ev.target.id;
    var parent = findAncestor(ev.target, "asset");

    // Set the variable which says that we are dragging an asset to TRUE
    isDraggingAsset = true;

    // Set the ID of the parent
    var draggableEltParentID = parent.id;

    // Transfer the parent ID
    ev.dataTransfer.setData("assetHTMLId", draggableEltParentID);

    // Add a class to the parendID
    $("#"+draggableEltParentID).addClass("dragged-elt-parent");

    // Get the size of the div
    var assetDivWidth = $("#"+draggableEltParentID).width();
    var assetDivHeight = $("#"+draggableEltParentID).height();
    var assetDivBackground = $("#"+draggableEltParentID).css("background");
    var assetContainerDivWidth = assetDivWidth + 40;
    var assetContainerDivHeight = assetDivHeight + 40;

    // Get the position of the mouse on the asset element
    var divOffset = $("#"+draggableEltParentID).offset();
    var mousePositionTop = ev.pageY - divOffset.top + 10;
    var mousePositionLeft = ev.pageX - divOffset.left + 10;

    // Copy the content of the div into the dragged element to display
    var copiedContent = parent.cloneNode(true);
    // copiedContent.style.boxShadow = "0 20px 30px 0 rgba(0,0,0,0.2), 0 10px 30px 0 rgba(0,0,0,0.19)";
    $("#dragged-content-to-display").empty().html(copiedContent).css("width", assetDivWidth+"px").css("height", assetDivHeight+"px").show();
    $("#dragged-content-to-display .asset").removeAttr("id").css("width", assetContainerDivWidth+"px").css("height", assetContainerDivHeight+"px").css("background", "none");
    $("#dragged-content-to-display .asset a").css("width", assetDivWidth+"px").css("height", assetDivHeight+"px").css("background", assetDivBackground);
    ev.dataTransfer.setDragImage(copiedContent, mousePositionLeft, mousePositionTop);
}

function dragEnterZoneAsset(ev) {
    ev.preventDefault();

    // Get the parent element
    var parent = findAncestor(ev.target, "project");

    // Get the parent ID
    var projectID = parent.id;

    // Remove the class of the all the other recipients
    $("#drop-asset-reset").removeClass("project-drop-zone");
    $(".project").removeClass("project-drop-zone");

    // Set a class on the project that will receive the asset
    if(isProjectPage == true) {
        $("#drop-asset-reset").addClass("project-drop-zone");
    }
    else if(isDraggingAsset == true) {
        $(".project#"+projectID).addClass("project-drop-zone");
    }
}

function dragLeaveZoneAsset(ev) {
    ev.preventDefault();

    // Get the parent element
    var parent = findAncestor(ev.target, "project");

    // Get the parent ID
    var projectID = parent.id;

    // Set a class on the project that will receive the asset
    if(isProjectPage == true) {
        $("#drop-asset-reset").removeClass("project-drop-zone");
    }
    else {
        $(".project#"+projectID).removeClass("project-drop-zone");
    }

    // Remove the class of the dragged element
    $("#drop-asset-reset").removeClass("project-drop-zone");
    $(".project").removeClass("project-drop-zone");
}

function dragEndAsset(ev) {
    ev.preventDefault();

    // Remove the class of the dragged element
    $(".dragged-elt-parent").removeClass("dragged-elt-parent");
    $("#drop-asset-reset").removeClass("project-drop-zone");
    $(".project").removeClass("project-drop-zone");

    // Set the variable which says that we are dragging an asset to TRUE
    isDraggingAsset = false;

    // Empty the div with the content to display during the drag of an asset
    $("#dragged-content-to-display").empty().hide();
}

function dropAsset(ev) {
    ev.preventDefault();

    // Collect the data transfered
    var draggableEltParentID = ev.dataTransfer.getData("assetHTMLId");

    // Get the project ID
    var project = findAncestor(ev.target, "project");
    var projectID = project.id;

    // Get the real IDs
    var assetRealID = $("#"+draggableEltParentID).attr("data-elt-id");
    var assetType = $("#"+draggableEltParentID).attr("data-asset-type");
    var projectRealID = $("#"+projectID).attr("data-elt-id");
    var projectNbAssets = $("#"+projectID).attr("data-nb-assets");

    // Display the loader
    var assetLoader = '<div class="loader-overlay">';
    assetLoader += '<div class="loader-overlay-inner">';
    assetLoader += '<div class="loader-container">';
    assetLoader += '<div class="loader-pulse">';
    assetLoader += dashboardJsTextLoading
    assetLoader += '...';
    assetLoader += '</div>';
    assetLoader += '</div>';
    assetLoader += '</div>';
    assetLoader += '</div>';
    $("#"+draggableEltParentID).prepend(assetLoader);

    // Set the variable which says that we are dragging an asset to TRUE
    isDraggingAsset = false;

    // Empty the div with the content to display during the drag of an asset
    $("#dragged-content-to-display").empty().hide();

    // Set the data for the API
    var data = { 'asset_type': assetType, 'asset_id': assetRealID, 'project_id': projectRealID }

    // Update the video
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: data,
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the video has been updated
            if(obj.response_code == 200) {

                // Update the screenshot of the project
                $.ajax({
                    type: 'POST',
                    url: ajaxGetProjectDetailsAction,
                    data: {
                        'project_id': projectRealID
                    },
                    success: function(data, textStatus, jqXHR){

                        // Get the response
                        var obj = $.parseJSON(data);
                        // console.log(obj);

                        // Check the status
                        // If the video has been updated
                        if(obj.response_code == 200) {

                            var projectScreenshot = obj.project.screenshot;
                            $(".project#"+projectID).css("background-image", "url("+projectScreenshot+")").css("background-size", "cover").css("background-position", "center center");
                            $(".project#"+projectID).removeClass("video").removeClass("image").removeClass("audio").addClass(obj.project.screenshot_asset_type);
                        }
                        else {
                        }
                    },
                    error: function(data, textStatus, jqXHR){
                        // console.log(data);
                    }
                });

                $("#assets #"+draggableEltParentID).addClass("slide-out-bck-center");

                // Remove the parent from the DOM
                setTimeout(function() {

                    $("#assets #"+draggableEltParentID).remove();

					// Redefine the positionning of the elements on the right
					$(".asset.last-elt").removeClass("last-elt");

					// Redefine the positionning of the elements on the right
					var countAssets = 0;
					$(".asset").each(function() {
						countAssets += 1;

						if((countAssets == 3) || ((countAssets - 3) % 4 == 0)) {
							$(this).addClass("last-elt");
						}
					});

                    // Change the number of assets in the project
                    projectNbAssets = parseInt( projectNbAssets ) + 1;
                    $("#"+projectID).attr("data-nb-assets", projectNbAssets);

                    if((userRightsArray.indexOf("image") != -1) || (userRightsArray.indexOf("audio") != -1)) {
                        if(projectNbAssets > 1)
                            var textNbAssets = projectNbAssets+" "+dashboardJsTextFiles;
                        else
                            var textNbAssets = projectNbAssets+" "+dashboardJsTextFile;
                    }
                    else {
                        if(projectNbAssets > 1)
                            var textNbAssets = projectNbAssets+" "+dashboardJsTextVideos;
                        else
                            var textNbAssets = projectNbAssets+" "+dashboardJsTextVideo;
                    }

                    $("#"+projectID+" .project-nb-assets").empty().text(textNbAssets);

                    // Remove the class of the parent
                    $(".project#"+projectID).removeClass("project-drop-zone");
                }, 600);
            }
            else {

                // Animate the draggable element so that it goes back to its original position

                // Remove the class of the parent
                $("#"+draggableEltParentID).removeClass("dragged-elt-parent");
                $(".project#"+projectID).removeClass("project-drop-zone");
            }
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });

    $("#drop-asset-reset").removeClass("project-drop-zone");
    $(".project").removeClass("project-drop-zone");
}



function dropAssetReset(ev) {
    ev.preventDefault();

    // Collect the data transfered
    var draggableEltParentID = ev.dataTransfer.getData("assetHTMLId");

    // Get the real IDs
    var assetRealID = $("#"+draggableEltParentID).attr("data-elt-id");
    var assetType = $("#"+draggableEltParentID).attr("data-asset-type");

    // Set the variable which says that we are dragging an asset to TRUE
    isDraggingAsset = false;

    // Empty the div with the content to display during the drag of an asset
    $("#dragged-content-to-display").empty().hide();

    // Set the data for the API
    var data = { 'asset_type': assetType, 'asset_id': assetRealID, 'project_id': 'reset' }

    // Display the loader
    var assetLoader = '<div class="loader-overlay">';
    assetLoader += '<div class="loader-overlay-inner">';
    assetLoader += '<div class="loader-container">';
    assetLoader += '<div class="loader-pulse">';
    assetLoader += dashboardJsTextLoading
    assetLoader += '...';
    assetLoader += '</div>';
    assetLoader += '</div>';
    assetLoader += '</div>';
    assetLoader += '</div>';
    $("#"+draggableEltParentID).prepend(assetLoader);

    // Update the video
    $.ajax({
        type: 'POST',
        url: ajaxEditAssetURL,
        data: data,
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the video has been updated
            if(obj.response_code == 200) {

                $("#assets #"+draggableEltParentID).addClass("slide-out-bck-center");

                // Remove the parent from the DOM
                setTimeout(function() {

                    $("#assets #"+draggableEltParentID).remove();

					// Redefine the positionning of the elements on the right
					$(".asset.last-elt").removeClass("last-elt");

					// Redefine the positionning of the elements on the right
					var countAssets = 0;
					$(".asset").each(function() {
						countAssets += 1;

						if((countAssets == 3) || ((countAssets - 3) % 4 == 0)) {
							$(this).addClass("last-elt");
						}
					});

                    // Remove the class of the parent
                    $("#drop-asset-reset").removeClass("project-drop-zone");
                }, 600);
            }
            else {

                // Animate the draggable element so that it goes back to its original position

                // Remove the class of the parent
                $("#"+draggableEltParentID).removeClass("pulsate-bck");
                $("#"+draggableEltParentID).removeClass("dragged-elt-parent");
                $("#drop-asset-reset").removeClass("project-drop-zone");
            }
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });

    $("#drop-asset-reset").removeClass("project-drop-zone");
    $(".project").removeClass("project-drop-zone");
}
