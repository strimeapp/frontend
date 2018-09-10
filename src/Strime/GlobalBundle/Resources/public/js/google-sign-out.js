function googleSignOut() {
    var signoutUrl = $("ul#header-user-submenu a.signout").attr("data-signout-url");

    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
        window.location.href = signoutUrl;
    });
}
