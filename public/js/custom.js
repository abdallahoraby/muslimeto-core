jQuery(function($) {


  function validateEmail(email)
      {
          var re = /\S+@\S+\.\S+/;
          return re.test(email);
      }

 $('#mo_wp_send_otp_pass').attr("id","wp-submit");
$('body').on('click', '#wp-submit',function(e){
		e.preventDefault();
    $('.ch_m_e').remove();
    $('.ch_m_e2').remove();
    let email = $('#user_login').val();
    if(validateEmail(email)){

    $('#lostpasswordform > p:nth-child(1)').append('<div style="text-align: center;" class="ch_m_e"><i class="fa fa-2x fa-spinner fa-pulse"></i></div>')

		$.post('/wp-admin/admin-ajax.php', {
			action: 'check_u_email',
			email: email,
		}, function (response){
       //console.log(response);
      $('.ch_m_e').remove();
      $('.ba_for').remove();
      $('#lostpasswordform > p:nth-child(1)').after(response);
      $('.bs-sign-in').after('<a href="/wp-login.php?action=lostpassword" class="bs-sign-in ba_for">Back to Forgot Password?</a>');
      // $('#wp-submit').remove();

		})
  }else {
    $('#lostpasswordform > p:nth-child(1)').after('<div class="ch_m_e"><p style="text-align: center;color:red">Enter a valid email address !</p></div>');
  }

	});


$('body').on('click', '#wp-submit2',function(e){
  e.preventDefault();
   const uid = $('#wp-submit2').attr('uid');
   const prnt = $('#wp-submit2').attr('prnt');
  $('#lostpasswordform > p:nth-child(1)').append('<div style="text-align: center;" class="ch_m_e"><i class="fa fa-2x fa-spinner fa-pulse"></i></div>');
  const code = $('.otp_code').val();
  const way = $('.check_way').val();
  $.post('/wp-admin/admin-ajax.php', {
  action: 'check_u_code',
  code: code,
  }, function (response){
  $('#user_login').val(unescape(getCookie('u_email')));
  console.log(getCookie('u_email'));
  $('.ch_m_e').remove();
  $('#lostpasswordform > p:nth-child(1)').after(response);
  })
});


$('body').on('click', '#wp-submit3', function (e) {
e.preventDefault();
$('.ch_m_e2').remove();
const new_pass = $('.new_pass').val();
if(new_pass.length < 6){
  $('#lostpasswordform > p:nth-child(1)').after('<div class="ch_m_e2"><p style="text-align: center;color:red">Your password must be at least 6 characters long !</p></div>');
}else if(new_pass.length >= 6){
  $('#lostpasswordform > p:nth-child(1)').after('<div style="text-align: center;" class="ch_m_e"><i class="fa fa-2x fa-spinner fa-pulse"></i></div>');
  $.post('/wp-admin/admin-ajax.php', {
  action: 'set_new_pass',
  new_pass: new_pass,
  }, function (response){
  $('.ch_m_e').remove();
  $('#lostpasswordform > p:nth-child(1)').after(response);
  window.setTimeout(function(){
     window.location.href = "/wp-login.php";
 }, 4000);
  })
}

});

$('body').on('click', '#prnt_choose1', function (e) {
  e.preventDefault();
const uid = $('.choose_acc:checked').val();
const prnt = $('.prnt_acc').val();
$('#ch_way').attr('uid',uid);
$('#ch_way').attr('prnt',prnt);
$('.ch_m_e').remove();
$('.ch_m_e2').show();
});

$('body').on('click', '#child_choose', function (e) {
  e.preventDefault();
  const uid = $('.child_id').val();
  const prnt = $('.prnt_acc').val();
  $('#ch_way').attr('uid',uid);
  $('#ch_way').attr('prnt',prnt);
  if($('.choose_acc:checked').val() == 'help'){
    alert('invoke help function !');
  }else{
    $('.ch_m_e').remove();
    $('.ch_m_e2').show();
  }
});


$('body').on('click', '#ch_way', function (e) {
  e.preventDefault();
   const uid = $('#ch_way').attr('uid');
   const prnt = $('#ch_way').attr('prnt');
  $('#lostpasswordform > p:nth-child(1)').append('<div style="text-align: center;" class="ch_m_e"><i class="fa fa-2x fa-spinner fa-pulse"></i></div>');
  const code = $('.otp_code').val();
  const way = $('.check_way:checked').val();
  $.post('/wp-admin/admin-ajax.php', {
  action: 'check_u_way',
  way:way,
  uid:uid,
  prnt:prnt,
  }, function (response){
  $('.ch_m_e').remove();
  $('.ch_m_e2').remove();
  $('#lostpasswordform > p:nth-child(1)').after(response);
  })
});
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
