//CSS Slider
require('../css/product_one_page.css');

$(document).ready(function() {
  $('.rate').each(function() {
    var stars = $(this).text();
    var rate = '';
    for (var i = 1; i <= 5; i++) {
      if (i <= stars) {
        rate += "<span class='fa fa-star checked'></span>";
      } else {
        rate += "<span class='fa fa-star'></span>";
      }
    }
    $(this).html(rate);
  });
});
