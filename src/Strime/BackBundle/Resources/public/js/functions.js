// Equivalent of the nl2br PHP function
function nl2br(str, is_xhtml) {
    //  discuss at: http://phpjs.org/functions/nl2br/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Philip Peterson
    // improved by: Onno Marsman
    // improved by: Atli Þór
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Maximusya
    // bugfixed by: Onno Marsman
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Brett Zamir (http://brett-zamir.me)
    //   example 1: nl2br('Kevin\nvan\nZonneveld');
    //   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
    //   example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
    //   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
    //   example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
    //   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'

    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

    return (str + '')
    .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}


// Function which converts the <br> tag into \n
function br2nl(str) {
    return str.replace(/<br\s*\/?>/mg,"\n");
}


// Function which strips the break lines from a string.
function stripnl(str) {
     return (str).replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '');
}


// Function to get the name of the browser used by the user
function getBrowser() {
    var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0; // Opera 8.0+ (UA detection to detect Blink/v8-powered Opera)
    var isFirefox = typeof InstallTrigger !== 'undefined'; // Firefox 1.0+
    var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0; // At least Safari 3+: "[object HTMLElementConstructor]"
    var isChrome = !!window.chrome && !isOpera; // Chrome 1+
    var isIE = /*@cc_on!@*/false || !!document.documentMode; // At least IE6

    if(isOpera == true)
        return "Opera";
    else if(isFirefox == true)
        return "Firefox";
    else if(isSafari == true)
        return "Safari";
    else if(isChrome == true)
        return "Chrome";
    else if(isIE == true)
        return "Internet Explorer";
}


// Function to get the version of the browser used by the user
function getBrowserVersion() {
    var nVer = navigator.appVersion;
    var nAgt = navigator.userAgent;
    var browserName  = navigator.appName;
    var fullVersion  = ''+parseFloat(navigator.appVersion);
    var majorVersion = parseInt(navigator.appVersion,10);
    var nameOffset,verOffset,ix;

    // In Opera, the true version is after "Opera" or after "Version"
    if ((verOffset=nAgt.indexOf("Opera"))!=-1) {
        browserName = "Opera";
        fullVersion = nAgt.substring(verOffset+6);
        if ((verOffset=nAgt.indexOf("Version"))!=-1)
            fullVersion = nAgt.substring(verOffset+8);
    }

    // In MSIE, the true version is after "MSIE" in userAgent
    else if ((verOffset=nAgt.indexOf("MSIE"))!=-1) {
        browserName = "Microsoft Internet Explorer";
        fullVersion = nAgt.substring(verOffset+5);
    }

    // In Chrome, the true version is after "Chrome"
    else if ((verOffset=nAgt.indexOf("Chrome"))!=-1) {
        browserName = "Chrome";
        fullVersion = nAgt.substring(verOffset+7);
    }

    // In Safari, the true version is after "Safari" or after "Version"
    else if ((verOffset=nAgt.indexOf("Safari"))!=-1) {
        browserName = "Safari";
        fullVersion = nAgt.substring(verOffset+7);
        if ((verOffset=nAgt.indexOf("Version"))!=-1)
            fullVersion = nAgt.substring(verOffset+8);
    }

    // In Firefox, the true version is after "Firefox"
    else if ((verOffset=nAgt.indexOf("Firefox"))!=-1) {
        browserName = "Firefox";
        fullVersion = nAgt.substring(verOffset+8);
    }

    // In most other browsers, "name/version" is at the end of userAgent
    else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) < (verOffset=nAgt.lastIndexOf('/')) ) {
        browserName = nAgt.substring(nameOffset,verOffset);
        fullVersion = nAgt.substring(verOffset+1);
        if (browserName.toLowerCase()==browserName.toUpperCase()) {
           browserName = navigator.appName;
        }
    }

    // trim the fullVersion string at semicolon/space if present
    if ((ix=fullVersion.indexOf(";"))!=-1)
        fullVersion=fullVersion.substring(0,ix);
    if ((ix=fullVersion.indexOf(" "))!=-1)
        fullVersion=fullVersion.substring(0,ix);

    majorVersion = parseInt(''+fullVersion,10);

    if (isNaN(majorVersion)) {
        fullVersion  = ''+parseFloat(navigator.appVersion);
        majorVersion = parseInt(navigator.appVersion,10);
    }

    /* document.write(''
        +'Browser name  = '+browserName+'<br>'
        +'Full version  = '+fullVersion+'<br>'
        +'Major version = '+majorVersion+'<br>'
        +'navigator.appName = '+navigator.appName+'<br>'
        +'navigator.userAgent = '+navigator.userAgent+'<br>'
    )*/

    return fullVersion;
}


// Function to get the OS used by the user
function getOS() {
    var OSName="Unknown OS";
    if (navigator.appVersion.indexOf("Win")!=-1) OSName = "Windows";
    if (navigator.appVersion.indexOf("Mac")!=-1) OSName = "MacOS";
    if (navigator.appVersion.indexOf("X11")!=-1) OSName = "UNIX";
    if (navigator.appVersion.indexOf("Linux")!=-1) OSName = "Linux";

    var ua = navigator.userAgent;
    if ( ua.match(/iPad/i) || ua.match(/iPhone/i) ) OSName = "iOS";
    if ( ua.match(/Android/i) ) OSName = "Android";

    return OSName;
}


// Function to sanitize text
function sanitizeText(text) {
    text = text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    return text;
}


// JS function to get an ancestor with a class
function findAncestor (el, cls) {
    while ((el = el.parentElement) && !el.classList.contains(cls));
    return el;
}


// Function which changes the value of the previous and next comment based on the actual comment
function updatePreviousNextButtonsValues(actualCommentID) {
    if(Date.now() - lastChangePreviousNextButtons > 500) {
        for (var i=0; i<commentsList.length; i++){
            if (commentsList[i] === actualCommentID) {

                if(i > 0) {
                    previousCommentForButton = commentsList[i-1];
                }
                else {
                    previousCommentForButton = commentsList[commentsList.length-1];
                }

                if(i < (commentsList.length-1)) {
                    nextCommentForButton = commentsList[i+1];
                }
                else {
                    nextCommentForButton = commentsList[0];
                }

                lastChangePreviousNextButtons = Date.now();
                break;
            }
        }
    }
}


// Function which updates the list of comments for the previous and next buttons
function updateListOfCommentsForButtons(assetID, assetType) {
    $.ajax({
        type: 'POST',
        url: ajaxGetCommentsParentsIdsURL,
        data: {
            'asset_type': assetType,
            'asset_id': assetID
        },
        success: function(data, textStatus, jqXHR){

            // Get the response
            var obj = $.parseJSON(data);
            // console.log(obj);

            // Check the status
            // If the list of comments has been gathered
            if(obj.response_code == 200) {

                // Update the value
                commentsList = new Array;

                for(var i = 0; i < obj.comments.length; i++) {
                    commentsList.push(obj.comments[i].comment_id);
                }
            }
        },
        error: function(data, textStatus, jqXHR){
            // console.log(data);
        }
    });
}
