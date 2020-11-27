$("#acceptLegalCookies").click(function () {
    // Set a cookie
    Cookies.set('acceptCookies', true, { expires : 364 });

    $("#legalCookies").hide();
});

$("#denyLegalCookies").click(function () {
    window.location.replace("https://google.fr");
});

$(function () {
   let myCookies = Cookies.get('acceptCookies');

   if(myCookies != "true") {
       $("#legalCookies").show();
   }
});