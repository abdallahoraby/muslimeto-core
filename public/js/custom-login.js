jQuery(function($) {

  function getCookie(cname) {
      var name = cname + "=";
      var ca = document.cookie.split(';');
      for(var i=0; i<ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0)==' ') c = c.substring(1);
          if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
      }
      return "";
  }
  if(getCookie('u_email')){
    $('#user_login').val( unescape(getCookie('u_email')) )
  }


}); //end jQuery
