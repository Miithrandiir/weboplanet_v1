$(".hamburger-resp").click(function (e) {
    e.preventDefault();


    if($(".nav").hasClass('active')) {
        $(".hamburger-resp").attr("id", "barsResp");
        $(".nav").removeClass('active');
    } else {
        $(".hamburger-resp").attr("id", "barsRepli");
        $(".nav").addClass('active');
    }
});