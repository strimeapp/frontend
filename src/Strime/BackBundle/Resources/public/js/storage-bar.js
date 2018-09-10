// If the storage used is over 100%
if(storageUsedInPercent > 100) {

    // Define what is the ratio between under 100% and over 100%
    var overQuotaStorageUsedInPercent = storageUsedInPercent - 100;

    // Define the ratios
    var overQuotaRatio = Math.round( overQuotaStorageUsedInPercent * 100 / storageUsedInPercent );
    var inQuotaRatio = Math.round( 100 - overQuotaRatio );
}
else {
    var overQuotaStorageUsedInPercent = 0;
    var overQuotaRatio = 0;
    var inQuotaRatio = storageUsedInPercent;
}

// Progressively load the storage bar
$(document).ready(function() {
    setTimeout(displayStorage, 1000);

    function displayStorage() {
        
        // Fill the in quota storage bar
        var storageUsedBarWidth = 0;
        var storageBarIsFilled = false;
        var storageBar = setInterval(fillStorageBar, 10);

        // Once the regular bar is filled
        // Fill the over quota storage bar
        var overQuotaStorageUsedBarWidth = 0;
        var overQuotaStorageBar;
        var isLaunchedFillOverQuotaStorageBar = setInterval(launchFillOverQuotaStorageBar, 10);

        function fillStorageBar() {

            // Round the storage bar width
            storageUsedBarWidth = Math.round(storageUsedBarWidth * 100) / 100;

            if (storageUsedBarWidth == inQuotaRatio) {
                storageBarIsFilled = true;
                clearInterval(storageBar);
            } else {
                storageUsedBarWidth += 0.1; 
                $("#storage-bar #storage-used-bar").width(storageUsedBarWidth + '%'); 
            }
        }

        function fillOverQuotaStorageBar() {

            // Round the storage bar width
            overQuotaStorageUsedBarWidth = Math.round(overQuotaStorageUsedBarWidth * 100) / 100;

            if (overQuotaStorageUsedBarWidth == overQuotaRatio) {
                clearInterval(overQuotaStorageBar);
            } else {
                overQuotaStorageUsedBarWidth += 0.1; 
                $("#storage-bar #over-quota-storage-used-bar").width(overQuotaStorageUsedBarWidth + '%'); 
            }
        }

        function launchFillOverQuotaStorageBar() {
            if(storageBarIsFilled == true) {
                overQuotaStorageBar = setInterval(fillOverQuotaStorageBar, 10);
                clearInterval(isLaunchedFillOverQuotaStorageBar);
            }
        }
    }
});