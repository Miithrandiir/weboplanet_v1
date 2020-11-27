//Global var
let nbrSlide = 0;
let iterator = 1;

$("#slider").each(function (e) {

    nbrSlide = $(this).children().length;
});

function readArray() {

    let oldSlide = $($("#slider").children()[iterator - 1]);
    oldSlide.slideUp(200).delay(800).fadeOut(200);

    let slide = $($("#slider").children()[iterator]);
    slide.slideDown(200).delay(800).fadeIn(200);


    if(iterator === nbrSlide-1) {
        iterator = 0;
    } else {
        iterator++;
    }

}

setInterval(readArray, 10000);