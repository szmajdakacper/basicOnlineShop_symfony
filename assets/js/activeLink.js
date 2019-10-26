$(document).ready(function() {
  //set active link to current nav-item
  var currentLocation = $(location).attr('pathname');
  var path = currentLocation.split('/');
  $('li.nav-item').each(function() {
    var navLink = $('a.nav-link', this).attr('href');
    var navLinkSplitted = navLink.split('/');
    if (navLinkSplitted[1] == path[1]) {
      $(this).addClass('active');
    }
  });
});
