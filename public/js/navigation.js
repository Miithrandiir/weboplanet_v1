$(".subNav").click(function(){
    let ul = $($($(this).parent()[0]).children()[1]);

    if(ul.hasClass('active')) {
        ul.removeClass('active');
    } else {
        ul.addClass('active');
    }
});

/**
    REPONSIVE
 **/

$(".menu-resp").click(function () {
    let menu = $($($(this).parent()[0]).children()[2]);

    if(menu.hasClass('active')) {
        menu.removeClass('active');
    } else {
        menu.addClass('active');
    }
});