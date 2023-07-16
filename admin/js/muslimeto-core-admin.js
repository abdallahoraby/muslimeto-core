(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */



})( jQuery );



jQuery(document).ready(function(){

	var i = 0;
	function move() {
		jQuery("#myProgress").fadeIn();
		if (i == 0) {
			i = 1;
			var progress_bar = document.getElementById("myBar");
			var width = 1;
			var id = setInterval(frame, 10);
			function frame() {
				if (width >= 100) {
					clearInterval(id);
					i = 0;
				} else {
					width++;
					progress_bar.style.width = width + "%";
				}
			}
			jQuery("#myProgress").delay(1000).fadeOut();
		}
	}

	// sync learners from WP users to bookly
	jQuery('.learners-sync').on('click', function (e){
		e.preventDefault();
		jQuery('.sync_result.learner').hide();
		jQuery(".result-status.learner").append(jQuery("#myProgress"));
		move();

		jQuery('.sync_result.learner').fadeIn().html('<p> Syncing please wait ...</p>');

		// get first & last user id
		let first_user_id = jQuery('#first_user_id').val();
		let last_user_id = jQuery('#last_user_id').val();

		let last_records = jQuery('#last_records').val();
		let next_records = jQuery('#next_records').val();


		// let query_times = Math.ceil( last_user_id / 100 );
		//
		// let last_records = ((i-1)*100)+1;
		// let next_records = i*100;
		//send ajax to sync users
		jQuery.post(ajaxurl, {
			action: 'sync_learners',
			last_records: last_records,
			next_records: next_records,
		}, function (response) { // response callback function
			jQuery('.sync_result.learner').fadeIn().html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();
			});

		// let resume_status = jQuery('#resume_status').val();
		//
		// var i = 1;                  //  set your counter to 1
		// function myLoop() {         //  create a loop function
		// 	setTimeout(function() {   //  call a 3s setTimeout when the loop is called
		// 		//  your code here
		// 		let last_records = ((i-1)*100)+1;
		// 		let next_records = i*100;
		// 		let resume_ststus ;
		// 		//console.log(next_records)
		// 		// send ajax to sync users
		// 		jQuery.post(ajaxurl, {
		// 			action: 'sync_learners',
		// 			last_records: last_records,
		// 			next_records: next_records,
		// 		}, function (response) { // response callback function
		// 			jQuery('.sync_result.learner').fadeIn().html(response.data.sync);
		// 			jQuery('#resume_status').val(response.data.message)
		// 		})
		// 			.done(function () {
		// 				//alert( "second success" );
		// 				//location.reload();
		// 			});
		//
		// 		i++;                    //  increment the counter
		// 		if (i <= query_times && resume_status === 'true' ) {           //  if the counter < 10, call the loop function
		// 			myLoop();             //  ..  again which will trigger another
		// 		}                       //  ..  setTimeout()
		// 	}, 5000)
		// }
		//
		// myLoop();






	});

	// sync staff from WP users to bookly
	jQuery('.staff-sync').on('click', function (e){
		e.preventDefault();
		jQuery('.sync_result.staff').hide();
		jQuery(".result-status.staff").append(jQuery("#myProgress"));
		move();
		jQuery.post(ajaxurl, {
			action: 'sync_staff',
		}, function (response) { // response callback function
			jQuery('.sync_result.staff').fadeIn().html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();
			});

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



}); //////////////////////////// end of document ready ///////////////////////////////

