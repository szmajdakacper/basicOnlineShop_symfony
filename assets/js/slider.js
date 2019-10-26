//CSS Slider
require('../css/slider.css');

//JS Code
document.getElementById('page').style.display = 'none';
document.getElementById('loader').style.display = 'block';

$(document).ready(function() {
  document.getElementById('page').style.display = 'block';
  document.getElementById('loader').style.display = 'none';
  //options
  var speed = 500;
  var autoswitch = true;
  var autoswitch_speed = 4000;

  //show first slide
  $('.slide')
    .first()
    .addClass('activeSlide');

  $('.slide').hide();

  $('.activeSlide').show();

  //handlers

  $('.next').on('click', nextSlide);
  $('.prev').on('click', prevSlide);

  if (autoswitch) {
    setInterval(nextSlide, autoswitch_speed);
  }

  //functions
  function nextSlide() {
    $('.activeSlide')
      .removeClass('activeSlide')
      .addClass('oldActiveSlide');
    if ($('.oldActiveSlide').is(':last-child')) {
      $('.slide')
        .first()
        .addClass('activeSlide');
    } else {
      $('.oldActiveSlide')
        .next()
        .addClass('activeSlide');
    }
    $('.oldActiveSlide').removeClass('oldActiveSlide');
    $('.slide').fadeOut(speed);
    $('.activeSlide').fadeIn(speed);
  }

  function prevSlide() {
    $('.activeSlide')
      .removeClass('activeSlide')
      .addClass('oldActiveSlide');
    if ($('.oldActiveSlide').is(':first-child')) {
      $('.slide')
        .last()
        .addClass('activeSlide');
    } else {
      $('.oldActiveSlide')
        .prev()
        .addClass('activeSlide');
    }
    $('.oldActiveSlide').removeClass('oldActiveSlide');
    $('.slide').fadeOut(speed);
    $('.activeSlide').fadeIn(speed);
  }
});
