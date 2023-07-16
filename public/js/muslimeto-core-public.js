jQuery(document).ready(function(){

	// $('.load_gif').append('<div class="ajax_image_section">\n' +
	// 	'        <div class="ajaxloader"></div>\n' +
	// 	'    </div>');
	//
	$('.ajax_image_section').hide();


	function load_gif(delay){

		$('.ajax_image_section').bind('ajaxStart', function(){
			$(this).show();
		}).bind('ajaxStop', function(){
			$(this).hide();
		});

	}


	var isEmpty = function(data) {
		if(typeof(data) === 'object') {
			if(JSON.stringify(data) === '{}' || JSON.stringify(data) === '[]') {
				return true;
			} else if(!data) {
				return true;
			}
			return false;
		} else if(typeof(data) === 'string') {
			if(!data.trim()){
				return true;
			}
			return false;
		} else if(typeof(data) === 'undefined') {
			return true;
		} else {
			return false;
		}
	}

	// booking rows generate
	let x = 1;
	let max_rows = 3;
	let hours_selector_options = $('#new_booking_form .hours_selector_options').html();
	let category_select = $('#new_booking_form .bookly_categories').html();
	let services_select = $('#new_booking_form .bookly_services_cloned').html();
	let class_days_checkbox = $('#new_booking_form .class_days').html();
	let class_duration_select = $('#new_booking_form .bookly_class_duration').html();
	let teacher_section = $('#new_booking_form .teacher_section_cloned').html();
	let bookly_effective_date_clone = $('#new_booking_form .bookly_effective_date_clone').html();
	let bookingRow = '<div class="col-md-12 d-flex">\n' +
		'<div class="col-md-6">\n' +
		bookly_effective_date_clone +
		'            </div>\n' +
		'</div>\n' +
		'<div class="col-md-12 d-flex">\n' +
		'            <div class="col-md-12 class_days">\n' +
		class_days_checkbox  +
		'\n' +
		'\n' +
		'            </div>\n' +
		'            <i class="far fa-trash-alt remove_row"></i>\n'+
		'        </div>\n' +
		'\n' +
		'        <div class="col-md-6 bookly_categories">\n' +
		'    		<label class="mr-sm-2" for="bookly_categories">Categories:</label>\n' +
		category_select +
		'        </div>\n' +
		'        <div class="col-md-6 bookly_services_cloned">\n' +
		'    		<label class="mr-sm-2" for="bookly_services">Services:</label>\n' +
		services_select +
		'        </div>\n' +
		'        <div class="col-md-6 bookly_start_time">\n' +
		'            <label class="mr-sm-2" for="bookly_start_time">Start Time:</label>\n' +
		'            <div>\n' +
		'                <select class="custom-select mr-sm-2 select2" name="bookly_start_time" id="bookly_start_time" required>\n' +
		hours_selector_options +
		'                </select>\n' +
		'            </div>\n' +
		'        </div>\n' +
		'\n' +
		'        <div class="col-md-6 bookly_class_duration">\n' +
		'            <label class="mr-sm-2" for="bookly_class_duration">Class Duration:</label>\n' +
		'            <div>\n' +
		class_duration_select +
		'            </div>\n' +
		'        </div>\n' +
		'        <div class="col-md-12 d-flex align-items-center teacher_section">\n' +
		teacher_section +
		'<input type="hidden" class="overlap_status" value="1">\n'+
		'        </div>\n';


	jQuery('#new_booking_form .fa-calendar-plus').click( function (){
		if( x<= max_rows ){
			let new_row = '<div class="schedule_booking_section cloned_row_'+ x +' col-md-12"> '+ bookingRow +' </div>';
			jQuery('#new_booking_form .booking_rows').append(new_row);
			$('#new_booking_form .cloned_row_'+x).find('.class_day input').attr('name', 'class_days_'+ x +'[]');
			$('#new_booking_form .cloned_row_'+x).find('select').select2();
			x++;
		}
	});

	$("body").delegate("#new_booking_form .remove_row", "mouseover", function(){
		$(this).closest('.schedule_booking_section').addClass('removing');
	});

	$("body").delegate("#new_booking_form .remove_row", "mouseleave", function(){
		$(this).closest('.schedule_booking_section').removeClass('removing');
	});

	$("body").delegate("#new_booking_form .remove_row", "click", function(){
		// run confirm first
		if(confirm("Are you sure you want to delete this schedule?")){
			$(this).closest('.schedule_booking_section').remove();
			x--;
		}
		else{
			$(this).closest('.schedule_booking_section').removeClass('removing');
			return false;
		}



	});




	var todayFullDate = new Date();
	var today = (todayFullDate.getMonth()+1)+'/'+todayFullDate.getDate()+'/'+todayFullDate.getFullYear();
	var tomorrow = (todayFullDate.getMonth()+1)+'/'+(todayFullDate.getDate()+1)+'/'+todayFullDate.getFullYear();
	var daysForBooking = (todayFullDate.getMonth()+1)+'/'+(todayFullDate.getDate()+30)+'/'+todayFullDate.getFullYear();
	var dayNextYear = (todayFullDate.getMonth()+1)+'/'+(todayFullDate.getDate())+'/'+(todayFullDate.getFullYear()+1);

	// datepicker init
	jQuery("body").delegate('#new_booking_form .datepicker_trigger', "click", function(e){
		$('[data-toggle="datepicker"]').datepicker({
			startDate: tomorrow, // was tomorrow
			format: 'mm/dd/yyyy',
			weekStart: 1,
			trigger: this,
		});
	});



	jQuery("body").delegate("#new_booking_form .bookly_effective_date", "pick.datepicker", function(e){
		let closest_parent_section = jQuery(this).closest('.schedule_booking_section');
		let effectiveDay = closest_parent_section.find('.bookly_effective_date').datepicker('getDayName', true); // 'Sun'
		let day_selected = closest_parent_section.find('#'+effectiveDay);
		day_selected.prop('checked', true).prop('disabled', true).addClass('checked');
		closest_parent_section.find('.class_day input').not(day_selected).prop('checked', false).prop('disabled', false).removeClass('checked');
		// closest_parent_section.find('.first_booking_row .class_day input').not('#'+effectiveDay).prop('checked', false).prop('disabled', false).removeClass('checked');
		// closest_parent_section.find('#'+effectiveDay).prop('checked', true).prop('disabled', true).addClass('checked');
	});


	$("#bookly_services").change(function(){
		var bookly_services = this.value;
		$("#icon_class, #background_class").hide();// hide multiple sections
	});


	$('#new_booking_form').parsley();


	// ajax calls

	// send category_id to get services in it
	$("body").delegate(".bookly_categories select", "change", function(e){
		e.preventDefault();
		let this_category_select = $(this);
		let bookly_category_id = $(this).val();
		$.post(ajaxurl, {
			action: 'get_bookly_category',
			bookly_category_id: bookly_category_id,
		}, function (response){ // response callback function
			$(this_category_select).closest('.schedule_booking_section').find('.bookly_services_cloned select').html(response);
			load_gif(this_category_select);
			$(this_category_select).closest('.schedule_booking_section').find('.bookly_services_cloned select').select2();
		})
			.done(function() {
				//alert( "second success" );
				//location.reload();
			});

	});


	// find teacher based on service related to him
	$("body").delegate(".bookly_services_cloned select", "change", function(e){
		e.preventDefault();

		let this_find_teacher = $(this);
		// get user data and post to ajax
		let bookly_service_id = $(this).closest('.schedule_booking_section').find('.bookly_services_cloned select').val();

		if( bookly_service_id === null ){
			// show alert to fill all data
		} else {
			// post data
			$.post(ajaxurl, {
				action: 'get_class_teacher',
				bookly_service_id: bookly_service_id,
			}, function (response){ // response callback function
				// show teacher select
				$(this_find_teacher).closest('.teacher_section').find('.teacher_select').show();
				$(this_find_teacher).closest('.schedule_booking_section').find('.teacher_select select').html(response);
				load_gif(this_find_teacher);
				$(this_find_teacher).closest('.schedule_booking_section').find('.teacher_select select').select2();
				$(this_find_teacher).closest('.schedule_booking_section').find('.teacher_select select').prop('disabled', true);
			})
				.done(function() {
					//alert( "second success" );
					//location.reload();
				});
		}

	});


	// check time overlap for a teacher_id
	$("body").delegate(".find_teacher", "click", function(e){
		e.preventDefault();

		let this_check_time_overlap = $(this);
		let bookly_teacher_id = $(this).closest('.teacher_section').find('select').val();
		let bookly_effective_date = $(this).closest('.schedule_booking_section').find('.bookly_effective_date').val();
		let bookly_start_time = $(this).closest('.schedule_booking_section').find('.bookly_start_time select').val();
		let bookly_class_duration = $(this).closest('.schedule_booking_section').find('.bookly_class_duration select').val();
		let bookly_class_days = $(this).closest('.schedule_booking_section').find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get();
		let bookly_user_timezone = $('#bookly_timezones').val();

		var bookly_teacher_ids = $(this).closest('.teacher_section').find('select').find('option').map(function() {
			if ( $.isNumeric(this.value) )
				return this.value;
		}).get().join(",");


		if( bookly_effective_date === null || bookly_start_time === null || bookly_class_duration === null || bookly_class_days.length === 0 ){
			// show alert to fill all data
		} else {

			$.post(ajaxurl, {
				action: 'check_time_overlap',
				bookly_teacher_ids: bookly_teacher_ids,
				bookly_effective_date: bookly_effective_date,
				bookly_start_time: bookly_start_time,
				bookly_class_duration: bookly_class_duration,
				bookly_class_days: bookly_class_days,
				bookly_user_timezone: bookly_user_timezone
			}, function (response) { // response callback function
				load_gif(this_check_time_overlap);
				let overlap_response = JSON.parse(response);
				let final_available_teachers = overlap_response.final_available_teachers;
				$(this_check_time_overlap).closest('.teacher_section').find('select').prop('disabled', false);

				if ( overlap_response.success == false || final_available_teachers.length === 0) { // overlap result found
					$(this_check_time_overlap).closest('.teacher_section').find('#progress li').addClass('overlap');
					$(this_check_time_overlap).closest('.teacher_section').find('#progress li').removeClass('no_overlap');

					let days_values = [];
					$(this_check_time_overlap).closest('.schedule_booking_section').find('.class_day input[type=checkbox]').each( function (){
						days_values.push($(this).val());
					});

					days_values.forEach((check_day) => {
						const match = overlap_response.over_lap_days.find(element => {
							if (element.includes(check_day)) {
								return true;
							}
						});

						if (match !== undefined) {
							// array contains substring match
							$(this_check_time_overlap).closest('.schedule_booking_section').find('.class_day input[value='+ check_day +']').addClass('overlap');
						}
					});

					$(this_check_time_overlap).closest('.teacher_section').find('select').find('option').prop('disabled', true);
					$(this_check_time_overlap).closest('.schedule_booking_section').find('.overlap_status').val(1);
				} else { // available teachers found
					$(this_check_time_overlap).closest('.schedule_booking_section').find('.overlap_status').val(0);
					$(this_check_time_overlap).closest('.teacher_section').find('#progress li').removeClass('overlap');
					$(this_check_time_overlap).closest('.teacher_section').find('#progress li').addClass('no_overlap');
					$(this_check_time_overlap).closest('.schedule_booking_section').find('.class_day input[type=checkbox]').removeClass('overlap');

					$(this_check_time_overlap).closest('.teacher_section').find('select').find('option').each(function (){
						$(this).prop('disabled', true);
						let option_teacher_value = parseInt( $(this).val() );
						if( final_available_teachers.includes(option_teacher_value) ){
							$(this).prop('disabled', false);
						}
					});

				}
			})
				.done(function () {
					//alert( "second success" );
					//location.reload();
				});

		}

	});

	// submit form
	$('#new_booking_form').on('submit', function (e){
		e.preventDefault();
		$('#new_booking_form').parsley().validate();
		let overlap_value = $('.overlap_value').val();
		if( overlap_value === '0' ){ // no over lap
			$.post(ajaxurl, {
				action: 'submit_new_booking_form',
			}, function (response) { // response callback function
				load_gif();
				$('.ajax_result').html(response);
			})
				.done(function () {
					//alert( "second success" );
					//location.reload();
				});
		} else {
			alert('Overlap found please review your form');
		}

	});


	$('.create_bb').on('click', function (){
		$.post(ajaxurl, {
			action: 'create_bb_group',
		}, function (response) { // response callback function
			load_gif();
			$('.ajax_result').html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();
			});
	});


	/****************************************************************************************************************************************
	 * Single Program Booking form ( #single_program_booking_form )
	 **************************************************************************************************************************************/

	$('button.submit_booking').prop('disabled', true).addClass('disabled');

	$('#single_program_booking_form.add-mode [data-toggle="datepicker"]').datepicker({
		startDate: today, // was tomorrow
		endDate: daysForBooking,
		format: 'mm/dd/yyyy',
		weekStart: 1,
		trigger: $('.datepicker_trigger'),
	});

	// class days input not checked until date picker selected
	$('#single_program_booking_form .class_day input').prop('disabled', true);

	$("#single_program_booking_form .bookly_effective_date").on("pick.datepicker", function(e){
		// if in edit mode, disable input
		let form_mode = $(this).parent().parent().parent().parent().parent();
		if( form_mode.hasClass('edit-mode') ){
			$(this).attr('disabled', true);
			$('.datepicker_trigger').hide();
			$('#single_program_booking_form .class_day input').prop('disabled', true);
			//excludeOldStaffId();
			let edit_option = $('.edit-options input:checked').val();
			if( edit_option === 'cancel' ){
				$('button.submit_booking').prop('disabled', false).removeClass('disabled');
			}
		} else {
			$('#single_program_booking_form .class_day input').prop('disabled', false);
		}


		$('#single_program_booking_form .add_new_row').prop('disabled', false);
		let effectiveDay = $('#single_program_booking_form').find('.bookly_effective_date').datepicker('getDayName', true); // 'Sun'
		let day_selected = $('.first_booking_row').find('#'+effectiveDay);
		day_selected.prop('checked', true).prop('disabled', true).addClass('checked');
		$('#single_program_booking_form .first_booking_row').find('.class_day input').not(day_selected).prop('checked', false).prop('disabled', false).removeClass('checked');
		$('.teacher_select select').prop('selectedIndex',0);
	});

	// booking rows generate
	let min_rows_single_program = 1;
	let max_rows_single_program = 5;
	let hours_selector_options_single_program = $('#single_program_booking_form .hours_selector_options').html();
	let minutes_selector_options_single_program = $('#single_program_booking_form .minutes_selector_options').html();
	let class_days_checkbox_single_program = $('#single_program_booking_form .class_days').html();
	let class_duration_select_single_program = $('#single_program_booking_form .bookly_class_duration').html();
	let singleBookingRow =
		'<div class="col-md-5 d-flex">\n' +
		'            <div class="col-md-12 class_days">\n' +
		class_days_checkbox_single_program  +
		'\n' +
		'\n' +
		'            </div>\n' +
		'        </div>\n' +
		'\n' +
		'      <div class="col-md-7  d-flex time-duration ">\n' +
		'        <div class="col-md-12 d-flex bookly_start_time"> \n' +
		'            <div class="d-flex start-time">\n' +
		'                <label class="mr-sm-2" for="bookly_start_time">Start Time: </label>\n' +
		'                <select class="custom-select mr-sm-2 select2 hours_selector_options" name="bookly_start_time"  required>\n' +
		hours_selector_options_single_program +
		'                </select>\n' +
		'                <select class="custom-select mr-sm-2 select2 minutes_selector_options" name=""  required>\n' +
		minutes_selector_options_single_program +
		'                </select>\n' +
		'            </div>\n' +
		'        	<div class="col-md-4 bookly_class_duration d-flex duration-div">\n' +
		'            	<label class="mr-sm-2" for="bookly_class_duration"> Duration:</label>\n' +
		class_duration_select_single_program +
		'        	</div>\n' +
		'        </div>\n' +
		'      </div>\n' +
		'    <i class="col-md-1 fa fa-times remove_row"></i>\n';


	$("body").delegate("#single_program_booking_form .add_new_row", "click", function(e){
		e.preventDefault();

		if( min_rows_single_program <= max_rows_single_program ){
			let new_row = '<div class="schedule_booking_section cloned cloned_row_'+ min_rows_single_program +' col-md-12"> '+ singleBookingRow +' </div>';
			let form_mode = $(this).parent().parent().parent().parent();
			if( form_mode.hasClass('edit-mode') ){
				// edit mode detected

				jQuery('#single_program_booking_form .new_schedule_section_rows').append(new_row);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').prop('disabled', false);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').prop('checked', false);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').attr('name', 'class_days_'+ min_rows_single_program +'[]');
				$('.cloned_row_'+min_rows_single_program).find('select').select2();
				$('.cloned_row_'+min_rows_single_program).append('<input type="hidden" class="final_teachers_array final_teacher_'+min_rows_single_program+'">');

				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.hours_selector_options').prop('selectedIndex',0).trigger('change');
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.mins_selector_options').prop('selectedIndex',0).trigger('change');
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.bookly_class_duration select').prop('selectedIndex',0).trigger('change').attr('disabled', false);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).addClass('new_schedule');
			} else {
				jQuery('#single_program_booking_form .booking_rows').append(new_row);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').prop('disabled', false);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').prop('checked', false);
				$('#single_program_booking_form .cloned_row_'+min_rows_single_program).find('.class_day input').attr('name', 'class_days_'+ min_rows_single_program +'[]');
				$('.cloned_row_'+min_rows_single_program).find('select').select2();
				$('.cloned_row_'+min_rows_single_program).append('<input type="hidden" class="final_teachers_array final_teacher_'+min_rows_single_program+'">');
			}

			min_rows_single_program++;
		}
	});

	$("body").delegate("#single_program_booking_form .remove_row", "mouseover", function(){
		$(this).closest('#single_program_booking_form .schedule_booking_section').addClass('removing');
	});

	$("body").delegate("#single_program_booking_form .remove_row", "mouseleave", function(){
		$(this).closest('#single_program_booking_form .schedule_booking_section').removeClass('removing');
	});

	// working 'remove_row'
	$("body").delegate("#single_program_booking_form .remove_row", "click", function(){

		let this_schedule_row = $(this).parent();

		if( this_schedule_row.hasClass('stored_schedule') ){
			this_schedule_row.find('.delete-confirm').addClass('show');
			$(".stored_schedule").not(this_schedule_row).find('.delete-confirm').removeClass('show');
			this_schedule_row.addClass('removing-col');
			$("#single_program_booking_form .stored_schedule").not(this_schedule_row).removeClass('removing-col');
		} else {

			$(this).closest('#single_program_booking_form .schedule_booking_section').remove();
			$('.teacher_select select').prop('selectedIndex',0);
			min_rows_single_program--;
		}

	});


	// ajax calls

	// send category_id to get services in it
	$("#single_program_booking_form .bookly_categories select").on("change", function(e){
		e.preventDefault();
		$('.ajax_image_section').show();
		$('.teacher_select select').prop('selectedIndex',0);
		let this_category_select = $(this);
		let bookly_category_id = $(this).val();
		$.post(ajaxurl, {
			action: 'get_bookly_category',
			bookly_category_id: bookly_category_id,
		}, function (response){ // response callback function
			$('#single_program_booking_form .bookly_services_cloned select').html(response);
			$('#single_program_booking_form .bookly_services_cloned select').select2();
			$('.teacher_section select').html('').select2();
		})
			.done(function() {
				//alert( "second success" );
				//location.reload();q
				$('.ajax_image_section').hide();
			});

	});

	// find teacher based on service related to him
	$("body").delegate("#single_program_booking_form .bookly_services_cloned select", "change", function(e){
		e.preventDefault();
		$('.ajax_image_section').show();
		let this_find_teacher = $(this);
		// get user data and post to ajax
		let bookly_service_id = $('#single_program_booking_form .bookly_services_cloned select').val();
		let check_teacher_status = $('input.teacher-check').val();

		if( bookly_service_id === null ){
			// show alert to fill all data
		} else {
			// post data
			$.post(ajaxurl, {
				action: 'get_class_teacher',
				bookly_service_id: bookly_service_id,
			}, function (response){ // response callback function
				// show teacher select
				load_gif(1000);
				$('#single_program_booking_form .teacher_section .teacher_select').show();
				$('#single_program_booking_form .teacher_select select').html(response);
				$('#single_program_booking_form .teacher_select select').select2();
				$('#single_program_booking_form .teacher_select select').prop('disabled', true);
				if( check_teacher_status !== 'all' ){
					$('.teacher_select select').prop('disabled', false);
				}
			})
				.done(function() {
					//alert( "second success" );
					//location.reload();
					$('.ajax_image_section').hide();
				});
		}

	});





	// check time overlap for a teacher_id
	$("body").delegate("#single_program_booking_form .find_teacher", "click", function(e){
		e.preventDefault();

		let this_check_time_overlap = $(this);
		// get timezone, effective_start_date, teachers_ids
		let bookly_user_timezone = $('#single_program_booking_form #bookly_timezones').val();
		var bookly_teacher_ids = $('#single_program_booking_form .teacher_section').find('select').find('option').map(function() {
			if ( $.isNumeric(this.value) )
				return this.value;
		}).get().join(",");
		let bookly_effective_date = $('#single_program_booking_form .bookly_effective_date').val();
		let bookly_service_id = $('#single_program_booking_form .bookly_services_cloned select option:selected').val();

		// get class_days, start_time, class_duration for each row
		let bookly_class_days = [];
		let bookly_class_duration = [];
		let bookly_start_hours = [];
		let bookly_start_minutes = [];
		if( $(this).hasClass('edit-mode-check') ){
			// in edit mode
			if( bookly_effective_date === '' || bookly_effective_date === undefined ){
				$.showError('please selecet effective from date');
				return;
			}

			let edit_option = $('.edit-options input:checked').val();

			if( edit_option === 'edit' ){
				// if edit schedule is selected only send new_schedule(s) rows to check and submit
				$('#single_program_booking_form .schedule_booking_section.new_schedule').each(function(){
					bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
					bookly_class_duration.push( $(this).find('.bookly_class_duration select').val() );
					let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
					let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
					bookly_start_hours.push( start_hours );
					bookly_start_minutes.push( start_minutes );
				});
			} else {

				// if transfer option is selected, only send current schedule(s) rows
				$('#single_program_booking_form .schedule_booking_section.send_to_backend').each(function(){
					bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
					bookly_class_duration.push( $(this).find('.bookly_class_duration select').val() );
					let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
					let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
					bookly_start_hours.push( start_hours );
					bookly_start_minutes.push( start_minutes );
				});
			}





		} else {
			$('#single_program_booking_form .schedule_booking_section').each(function(){
				bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
				bookly_class_duration.push( $(this).find('.bookly_class_duration select').val() );
				let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
				let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
				bookly_start_hours.push( start_hours );
				bookly_start_minutes.push( start_minutes );
			});
			$('#single_program_booking_form').parsley().validate();
		}



		let final_available_teachers = [];
		let teacher_has_errors = [];

		// send ajax for each row and get result
		for (let i = 0; i < bookly_start_hours.length; i++) {
			let row_bookly_start_hours = bookly_start_hours[i];
			let row_bookly_start_minutes = bookly_start_minutes[i];
			let row_bookly_class_duration = bookly_class_duration[i];
			let row_bookly_class_days = bookly_class_days[i];
			if( bookly_effective_date === null || row_bookly_start_hours === null || row_bookly_class_duration === null || row_bookly_class_days.length === 0 ){
				// show alert to fill all data
				alert('You must Fill all data first.');
			} else {
				$('.ajax_image_section').show();
				if ($('.teacher-check').val() === 'all') {
					$.post(ajaxurl, {
						action: 'check_time_overlap_single_program_all_teachers',
						bookly_teacher_ids: bookly_teacher_ids,
						bookly_effective_date: bookly_effective_date,
						bookly_start_hours: row_bookly_start_hours,
						bookly_start_minutes: row_bookly_start_minutes,
						bookly_class_duration: row_bookly_class_duration,
						bookly_class_days: row_bookly_class_days,
						bookly_user_timezone: bookly_user_timezone,
						bookly_service_id: bookly_service_id
					}, function (response) { // response callback function
						//$('.ajax_result').html(response);
						$('.ajax_image_section').hide();
						let overlap_response = JSON.parse(response);
						$('#single_program_booking_form .final_teacher_' + i).val(overlap_response.final_available_teachers);
						final_available_teachers.push(overlap_response.final_available_teachers);
						// reinitialized select2 oprion and hide disabled one
						$('#single_program_booking_form .teacher_section').find('select').prop('disabled', false).select2({
							templateResult: function(option, container) {
								if ($(option.element).attr("disabled") == "disabled"){
									$(container).css("display","none");
								}

								return option.text;
							}
						});
						// $('#single_program_booking_form .teacher_section').find('select').select2({
						// 	dropdownCssClass: "teacher_dropdown"
						// });
					})
					.done(function () {
						//alert( "second success" );
						//location.reload();

					});




				} else {
					// single confirm teacher is selected
					bookly_teacher_ids = $('#single_program_booking_form .teacher_section select').val();

					//check is not teacher selected , show alert error
					if( bookly_teacher_ids === null || bookly_teacher_ids === undefined ){
						// $('#errors-modal .modal__content').html('<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p>  <h3 class="text-center"> Please select teacher first </h3>');
						// MicroModal.show('errors-modal');
						$('.ajax_image_section').hide();
						$.showError('Please select teacher first');
					} else {

						$.post(ajaxurl, {
							action: 'check_time_overlap_single_program',
							bookly_teacher_ids: bookly_teacher_ids,
							bookly_effective_date: bookly_effective_date,
							bookly_start_hours: row_bookly_start_hours,
							bookly_start_minutes: row_bookly_start_minutes,
							bookly_class_duration: row_bookly_class_duration,
							bookly_class_days: row_bookly_class_days,
							bookly_user_timezone: bookly_user_timezone,
							bookly_service_id: bookly_service_id
						}, function (response) { // response callback function
							//$('.ajax_result').html(response);
							$('.ajax_image_section').hide();
							let overlap_response = JSON.parse(response);

							if( parseInt(overlap_response.final_available_teachers[0]) === parseInt(bookly_teacher_ids) ){
								// teacher is available ok
								//$('button.submit_booking').prop('disabled', false).removeClass('disabled');
							} else{
								// teacher is not available, show errors
								teacher_has_errors.push(1);
								//$('button.submit_booking').prop('disabled', true).addClass('disabled');
								if( overlap_response.staff_appointments_error === null ){
									staff_appointments_error_result = '';
								} else {
									staff_appointments_error_result = '<p class="alert">'+ overlap_response.staff_appointments_error +'</p>';
								}

								if( overlap_response.staff_schedule_error === null ){
									staff_schedule_error_result = '';
								} else {
									staff_schedule_error_result = '<p class="alert">'+ overlap_response.staff_schedule_error +'</p>';
								}

								// $('#error-modal .modal__content').html(staff_appointments_error_result + staff_schedule_error_result);
								// MicroModal.show('error-modal');
								$.showError(staff_appointments_error_result + staff_schedule_error_result);

							}
							$('#single_program_booking_form .final_teacher_' + i).val(overlap_response.final_available_teachers);
							final_available_teachers.push(overlap_response.final_available_teachers);
							$('#single_program_booking_form .teacher_section').find('select').prop('disabled', false);
						})
							.done(function () {
								//alert( "second success" );
								//location.reload();
								$('#validate_submit_btn').val(teacher_has_errors);
								$('.validate_submit_data').trigger('click');
							});
					}


				} // end if check for single or all

			}
		}




		//$('.teacher_section select').trigger('click');



	});

	// validate submit button
	$('.validate_submit_data').on('click', function (){
		let teacher_has_errors = $('#validate_submit_btn').val();
		if( teacher_has_errors.length > 0 ){
			// submit disabled
			$('button.submit_booking').prop('disabled', true).addClass('disabled');
		} else {
			// submit enabled
			$('button.submit_booking').prop('disabled', false).removeClass('disabled');
		}
	});

	$('.teacher_section select').on('select2:select', function (e) {
		if ($('.teacher-check').val() === 'all') {
			$('button.submit_booking').prop('disabled', false).removeClass('disabled');
		} else {

		}
	});



	$('.teacher_section select').on('select2:opening', function () {
		//$('[id^=select2-bookly_teacher]').attr('class','select2-results__options teachers_select2');

		if ($('.teacher-check').val() === 'all'){
			$('.ajax_image_section').show();
			let final_teachers_arrays = [];
			for (let i = 0; i < $('#single_program_booking_form .schedule_booking_section').length; i++) {
				final_teachers_arrays.push($('.final_teacher_' + i).val().split(','));
				// get matches teacher id from final_available_teachers arrays
			}

			let available_selected_teahcer = final_teachers_arrays.reduce((p, c) => p.filter(e => c.includes(e)));

			if ($('#single_program_booking_form .schedule_booking_section').length > 1) {
				//console.log(available_selected_teahcer);
				$('#single_program_booking_form .teacher_section').find('select').find('option').each(function () {
					$(this).prop('disabled', true);
					let option_teacher_value = $(this).val();
					if (available_selected_teahcer.includes(option_teacher_value)) {
						$(this).prop('disabled', false);
					}
				});
				$('.ajax_image_section').hide();
			} else {
				//console.log(available_selected_teahcer);
				$('#single_program_booking_form .teacher_section').find('select').find('option').each(function () {
					$(this).prop('disabled', true);
					let option_teacher_value = $(this).val();
					if (available_selected_teahcer.includes(option_teacher_value)) {
						$(this).prop('disabled', false);
					}
				});
				$('.ajax_image_section').hide();
			}
		}


	});

	// add class to select2 teacher
	let teacher_select = $('.select2-results__options').attr('id');
	if( teacher_select !== undefined ){
		let position_found = teacher_select.search("bookly_teacher");
		if( position_found > 1 ){
			$('.select2-results__options').addClass('teacher_select2');
		}
	}





	// submit single program form add-mode
	$('#single_program_booking_form.add-mode').on('submit', function (e){
		e.preventDefault();
		$('.ajax_image_section').show();
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
		$('#single_program_booking_form').parsley().validate();
		// get timezone, effective_start_date, teachers_ids
		let program_type = $('.program-type').val();
		if( program_type === 'group' ){
			program_type = $('input[name=group_type_select]:checked').val();
		}
		let program_status = $('.program-status').val();
		let bookly_user_timezone = $('#single_program_booking_form #bookly_timezones').val();
		let bookly_student_id = $('#single_program_booking_form #bookly_students').val();
		var bookly_teacher_id = $('#single_program_booking_form .teacher_section').find('select').val();
		let bookly_effective_date = $('#single_program_booking_form .bookly_effective_date').val();
		let bookly_category_id = $('#single_program_booking_form .bookly_categories select option:selected').val();
		let bookly_service_id = $('#single_program_booking_form .bookly_services_cloned select option:selected').val();
		let bookly_service_name = $('#single_program_booking_form .bookly_services_cloned select option:selected').text();
		let bookly_student_name = $('#single_program_booking_form #bookly_students option:selected').text();
		let bb_group_id = $('#bb_group_id').val();
		let zoom_meeting_id = $('#zoom_meeting_id').val();
		let group_family = $('.group_type_select').val();
		let link_to_group_option = $('.link_to_group:checked').val();

		// get class_days, start_time, class_duration for each row
		let bookly_class_days = [];
		let bookly_class_duration = [];
		let bookly_start_hours = [];
		let bookly_start_minutes = [];
		$('#single_program_booking_form .schedule_booking_section').each(function(){
			bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
			bookly_class_duration.push( parseInt( $(this).find('.bookly_class_duration select').val() ) );
			let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
			let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
			bookly_start_hours.push( parseInt( start_hours ) );
			bookly_start_minutes.push( parseInt( start_minutes ) );
		});

		if( bookly_effective_date === null || bookly_start_hours === null || bookly_class_duration === null || bookly_class_days.length === 0 ){
			// show alert to fill all data
		} else {
			$.post(ajaxurl, {
				action: 'submit_single_program_booking_form',
				bookly_teacher_id: bookly_teacher_id,
				bookly_effective_date: bookly_effective_date,
				bookly_start_hours: bookly_start_hours,
				bookly_start_minutes: bookly_start_minutes,
				bookly_class_duration: bookly_class_duration,
				bookly_class_days: bookly_class_days,
				bookly_user_timezone: bookly_user_timezone,
				bookly_service_name: bookly_service_name,
				bookly_student_name: bookly_student_name,
				bookly_service_id: bookly_service_id,
				bookly_student_id: bookly_student_id,
				bookly_category_id: bookly_category_id,
				program_type: program_type,
				program_status: program_status,
				bb_group_id: bb_group_id,
				zoom_meeting_id: zoom_meeting_id,
				group_family: group_family,
				link_to_group_option: link_to_group_option
			}, function (response) { // response callback function
				if( response === 'status ok' ){
					let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Data Inserted Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
					// $('#success-modal .modal__content').html(success_message);
					// MicroModal.show('success-modal');
					//alert('Data Saved successfully');
					$.showInfo('Data Inserted Successfully. please refresh the page if it does not reload automatically.')
					location.reload();
				} else {
					let error_messafe = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> ' + response + '</p>';
					// $('#error-modal .modal__content').html(error_messafe);
					// MicroModal.show('error-modal');
					//$('.ajax_result').html('<span class="error-catch">' + response + '</span>');
					$.showError(response)
				}
			})
				.done(function () {
					//alert( "second success" );
					//location.reload();
					$('.ajax_image_section').hide();
				});

		}

	});


	// submit single program form edit-mode submit
	$('#single_program_booking_form.edit-mode .submit_booking').on('click', function (e){
		e.preventDefault();
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
		$('#single_program_booking_form').parsley().validate();
		// get timezone, effective_start_date, teachers_ids
		let program_type = $('.program-type').val();
		if( program_type === 'group' ){
			program_type = $('input[name=group_type_select]:checked').val();
		}
		let program_status = $('.program-status').val();
		let bookly_user_timezone = $('#single_program_booking_form #bookly_timezones').val();
		let bookly_student_id = $('#single_program_booking_form #bookly_students_disabled').val();
		var bookly_teacher_id = $('#single_program_booking_form .teacher_section').find('select').val();
		let bookly_category_id = $('#single_program_booking_form .bookly_categories select option:selected').val();
		let bookly_service_id = $('#single_program_booking_form .bookly_services_cloned select option:selected').val();
		let bookly_service_name = $('#single_program_booking_form .bookly_services_cloned select option:selected').text();
		let bookly_student_name = $('#single_program_booking_form #bookly_students option:selected').text();
		let bb_group_id = $('#stored_bb_group_id').val();
		let zoom_meeting_id = $('#zoom_meeting_id').val();
		let group_family = $('.group_type_select').val();
		let gf_sp_entry_id = $('.gf_sp_entry_id').val();
		let new_effective_date = $('#single_program_booking_form .bookly_effective_date').val();
		let old_teacher_id = $('.old_teacher_id').val();


		let edit_option = $('.edit-options input:checked').val();

		// get class_days, start_time, class_duration for each row
		let bookly_class_days = [];
		let bookly_class_duration = [];
		let bookly_start_hours = [];
		let bookly_start_minutes = [];
		let start_effective_date = [];
		let end_effective_date = [];
		let stored_schedule_entry_ids = [];
		let clone_gf_schedule = [];
		let bookly_series_id = [];

		if( edit_option === 'edit' ){
			$('#single_program_booking_form .schedule_booking_section.new_schedule').each(function(){
				bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
				bookly_class_duration.push( parseInt( $(this).find('.bookly_class_duration select').val() ) );
				let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
				let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
				bookly_start_hours.push( parseInt( start_hours ) );
				bookly_start_minutes.push( parseInt( start_minutes ) );
				let row_start_effective_date = $(this).find('.start_effective_date').val();
				start_effective_date.push(row_start_effective_date);
				let row_end_effective_date = $(this).find('.end_effective_date').val();
				end_effective_date.push(row_end_effective_date);
			});
		} else if ( edit_option === 'transfer' ){
			$('#single_program_booking_form .schedule_booking_section.send_to_backend').each(function(){
				bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
				bookly_class_duration.push( parseInt( $(this).find('.bookly_class_duration select').val() ) );
				let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
				let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
				bookly_start_hours.push( parseInt( start_hours ) );
				bookly_start_minutes.push( parseInt( start_minutes ) );
				let row_start_effective_date = $(this).find('.start_effective_date').val();
				start_effective_date.push(row_start_effective_date);
				let row_end_effective_date = $(this).find('.end_effective_date').val();
				end_effective_date.push(row_end_effective_date);
				let stored_schedule_entry_id = $(this).find('.stored_schedule_entry_id').val();
				stored_schedule_entry_ids.push(stored_schedule_entry_id);
				clone_gf_schedule.push( $(this).find('.clone_gf_schedule').val() );
			});

		} else if ( edit_option === 'cancel' ){
			$('#single_program_booking_form .schedule_booking_section').each(function(){
				bookly_class_days.push($(this).find('.class_day input[type=checkbox]:checked').map(function(_, el) { return $(el).val(); }).get());
				bookly_class_duration.push( parseInt( $(this).find('.bookly_class_duration select').val() ) );
				let start_hours = $(this).find('.bookly_start_time select.hours_selector_options').val();
				let start_minutes = $(this).find('.bookly_start_time select.minutes_selector_options').val();
				bookly_start_hours.push( parseInt( start_hours ) );
				bookly_start_minutes.push( parseInt( start_minutes ) );
				let row_start_effective_date = $(this).find('.start_effective_date').val();
				start_effective_date.push(row_start_effective_date);
				let row_end_effective_date = $(this).find('.end_effective_date').val();
				end_effective_date.push(row_end_effective_date);
				let stored_schedule_entry_id = $(this).find('.stored_schedule_entry_id').val();
				stored_schedule_entry_ids.push(stored_schedule_entry_id);
				clone_gf_schedule.push( $(this).find('.clone_gf_schedule').val() );
				bookly_series_id.push( $(this).find('.bookly_series_id').val() )
			});

		}




		if( start_effective_date === null || end_effective_date === null || bookly_start_hours === null || bookly_class_duration === null || bookly_class_days.length === 0 ){
			// show alert to fill all data
			$.showError('Please fill empty data');
		} else {
			$('.ajax_image_section').show();
			$.post(ajaxurl, {
				action: 'submit_single_program_booking_form_edit_mode',
				bookly_teacher_id: bookly_teacher_id,
				new_effective_date: new_effective_date,
				bookly_start_hours: bookly_start_hours,
				bookly_start_minutes: bookly_start_minutes,
				bookly_class_duration: bookly_class_duration,
				bookly_class_days: bookly_class_days,
				bookly_user_timezone: bookly_user_timezone,
				bookly_service_name: bookly_service_name,
				bookly_student_name: bookly_student_name,
				bookly_service_id: bookly_service_id,
				bookly_student_id: bookly_student_id,
				bookly_category_id: bookly_category_id,
				program_type: program_type,
				program_status: program_status,
				bb_group_id: bb_group_id,
				zoom_meeting_id: zoom_meeting_id,
				group_family: group_family,
				gf_sp_entry_id: gf_sp_entry_id,
				edit_option: edit_option,
				stored_schedule_entry_ids: stored_schedule_entry_ids,
				clone_gf_schedule: clone_gf_schedule,
				bookly_series_id: bookly_series_id,
				old_teacher_id: old_teacher_id
			}, function (response) { // response callback function
				$('.ajax_result').html( response );
				if( response === 'status ok' ){
					let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Data Inserted Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
					$('#success-modal .modal__content').html(success_message);
					MicroModal.show('success-modal');

					location.reload();
				} else if ( response === 'status transfer ok' ){
					let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Teacher Updated Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
					$('#success-modal .modal__content').html(success_message);
					MicroModal.show('success-modal');

					location.reload();
				} else if ( response === 'status delete ok' ){
					let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Program Cancelled Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
					$('#success-modal .modal__content').html(success_message);
					MicroModal.show('success-modal');

					location.reload();
				} else {
					let error_messafe = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> ' + response + '</p>';
					$('#error-modal .modal__content').html(error_messafe);
					MicroModal.show('error-modal');
					//$('.ajax_result').html('<span class="error-catch">' + response + '</span>');
				}
			})
				.done(function () {
					//alert( "second success" );
					//location.reload();
					$('.ajax_image_section').hide();
				});

		}

	});




	jQuery('.select2').select2();



	// on change any parameter reset teacher select
	jQuery("body").delegate(".bookly_start_time select", "change", function(e){
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate(".add-mode .bookly_start_time select", "change", function(e){
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$('.final_teachers_array').val(null);
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate(".bookly_class_duration select", "change", function(e){
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate(".add-mode .bookly_class_duration select", "change", function(e){
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$('.final_teachers_array').val(null);
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate("#bookly_timezones", "change", function(e){
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate(".add-mode #bookly_timezones", "change", function(e){
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$('.final_teachers_array').val(null);
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});


	jQuery("body").delegate(".class_day input", "change", function(e){
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	jQuery("body").delegate(".add-mode .class_day input", "change", function(e){
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$('.final_teachers_array').val(null);
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	// $('.schedule_booking_section').each(function(){
	// 	$(this).find('.class_day input').each(function(){
	// 		$(this).on('change', function(){
	// 			//$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
	// 			$('button.submit_booking').prop('disabled', true).addClass('disabled');
	// 		})
	// 	});
	// });


	// on check Group add multiple to learners select
	$('.add-mode #bookly_students').prop('multiple', false);

	$('.program-type').click(function() {
		$('#bookly_students option').remove();
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$("#bookly_students").prop('selectedIndex',0);
		$("#bookly_students").trigger('change');
		// Using jQuery's is() method
		if ($(this).is(':checked')) {
			//alert('1 on 1 selected');
			$(this).val('one-to-one');
			$('#bookly_students').prop('multiple', false).removeClass('multiple');
			$('.bb-group-select').hide();
			$('.zoom_meeting_id').hide();
			$("#bb_group_id").prop('selectedIndex',0).change();
		} else {
			$(this).val('group');
			$('#family-group').prop('checked', true);
			$('#bookly_students').prop('multiple', true).addClass('multiple');
			$('.bb-group-select').css("display", "flex");

		}

	});

	$('.multiple').on('select2:open', function (e) {
		// Do something
		$(this).find('option').eq(0).prop('selected', false);
	});

	// on check New apply booking from next day and 30 days limit
	$('.program-status').click(function() {
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		$(".bookly_effective_date").val(null).trigger('change');
		// Using jQuery's is() method
		if ($(this).is(':checked')) {
			//alert('New is selected');
			$(this).val('new');
			$('#single_program_booking_form [data-toggle="datepicker"]').datepicker('setStartDate', tomorrow); // was tomorrow
			$('#single_program_booking_form [data-toggle="datepicker"]').datepicker('setEndDate', daysForBooking);

		} else {
			$(this).val('transferred');
			$('#single_program_booking_form [data-toggle="datepicker"]').datepicker('setStartDate', today); // was today
			$('#single_program_booking_form [data-toggle="datepicker"]').datepicker('setEndDate', daysForBooking); // it was dayNextYear

		}

	});

	// on check teacher all or single
	$('.teacher-check').click(function() {
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
		$('.teacher_select select').prop('selectedIndex',0).val(null).trigger('change');
		// Using jQuery's is() method
		if ($(this).is(':checked')) {
			//alert('All is selected');
			$(this).val('all');
			$('a.find_teacher.all').show().removeClass('hidden');
			$('a.find_teacher.single').hide().addClass('hidden');
			$('#single_program_booking_form .teacher_section').find('select').prop('disabled', true);
			$('#single_program_booking_form .teacher_section select').select2('destroy');
		} else {
			$(this).val('single');
			$('a.find_teacher.single').show().removeClass('hidden');
			$('a.find_teacher.all').hide().addClass('hidden');
			$('#single_program_booking_form .teacher_section').find('select').prop('disabled', false);
			$('#single_program_booking_form .teacher_section').find('select').find('option').each(function () {
				$(this).prop('disabled', false);
			});
			$('#single_program_booking_form .teacher_section').find('select').find('option').eq(0).prop('disabled', true);
			$('#single_program_booking_form .teacher_section select').select2();
		}

	});


	$('#single_program_booking_form #bb_group_id').hide();
	$('.zoom_meeting_id').hide();
	$('#bb_group_id').next('span').hide();
	$('.sub-group-options').hide();

	$('.link_to_group').on('change', function (){

		let link_to_group_value = $(this).val();
		if( link_to_group_value == 'link_to_existing' ){
			$('#bb_group_id').show();
			$('#bb_group_id').next('span').show();
			$('.zoom_meeting_id').show();
		} else {
			$('#bb_group_id').hide();
			$('#bb_group_id').next('span').hide();
			$("#bb_group_id").prop('selectedIndex',0).change();
			$('.zoom_meeting_id').hide();
		}

	});


	$('.group_type_select').on('change', function (){

		let group_type_value = $(this).val();
		if( group_type_value !== 'family-group' ){
			$('.sub-group-options').show();
		} else {
			$('.sub-group-options').hide();
		}

		// reset link to group radio options
		//$('.link_to_group').prop('checked', false);
		$('#bb_group_id').hide();
		$('#bb_group_id').next('span').hide();
		$("#bb_group_id").prop('selectedIndex',0).change();

		if( group_type_value == 'mvs' ){
			$('a.find_teacher.all').hide();
			$('.teacher-check').prop('checked', true);
			$('.teacher-check').click();
			$('.teacher-check').prop('disabled', true);
			$('a.find_teacher.single').hide();
			$('.teacher_select select').on('change', function(){
				$('button.submit_booking').prop('disabled', false).removeClass('disabled');
			});

		} else {
			$('a.find_teacher.all').show();
			$('.teacher-check').prop('disabled', false);
			$('button.submit_booking').prop('disabled', true).addClass('disabled');
			$('.teacher_select select').on('change', function(){
				$('button.submit_booking').prop('disabled', true).addClass('disabled');
			});
		}

	});

	// $('#existing').click(function() {
	// 	// Using jQuery's is() method
	// 	if ($(this).is(':checked')) {
	// 		//alert('Existing is selected');
	// 		$('#bb_group_id').show();
	// 		$('#bb_group_id').next('span').show();
	// 		$('.zoom_meeting_id').show();
	// 	} else {
	// 		$('#bb_group_id').hide();
	// 		$('#bb_group_id').next('span').hide();
	// 		$("#bb_group_id").prop('selectedIndex',0).change();
	// 		$('.zoom_meeting_id').hide();
	// 	}
	//
	// });



	// data tables init
	$('.data-table').DataTable();

	//get learner schedule from gravity entries
	$('.view-schedule').on('click', function (e){
		e.preventDefault();
		let program_parent_entry_id = $(this).data('program-parent-entry-id');
		this.blur(); // Manually remove focus from clicked link.
		$.post(ajaxurl, {
			action: 'view_learner_scheduke',
			program_parent_entry_id: program_parent_entry_id
		}, function (response) { // response callback function
			$('.view-schedule-modal').html(response).modal();
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();
			});

	});



	// find users based on referral code
	$('.find-user').on('click', function (){
		$('.ajax_image_section.find-users').show();
		let parent_user_email = $('#parent_user_email').val();
		let get_parent_only = '';
		if( $('#get_parent_only') ){
			get_parent_only = $('#get_parent_only').val();
		} else {
			get_parent_only = '';
		}

		$.post(ajaxurl, {
			action: 'get_childs_for_referral_code',
			parent_user_email: parent_user_email,
			get_parent_only: get_parent_only
		}, function (response) { // response callback function
			$('.childs-result').html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();
				$('.ajax_image_section.find-users').hide();
			});

	});


	$("body").delegate(".add-to-learners", "click", function(e){
		e.preventDefault();
		let user_id = $(this).data('user-id');
		let option_text = $(this).next('.option-text').text();
		var data = {
			id: user_id,
			text: option_text
		};

		// check if user in list dont add again
		var old_bookly_students_options = new Array();
		$('#bookly_students option').each(function(){
			old_bookly_students_options.push($(this).val());
		});

		if( ! old_bookly_students_options.includes(String(user_id)) ){
			var newOption = new Option(data.text, data.id, true, true);
			$('#bookly_students').append(newOption).trigger('change');


			if( $('input.program-type').val() == 'one-to-one' ){
				// one to one selected
				$('#bookly_students').val(user_id);
			} else if ( $('input.program-type').val() == 'group' ) {
				// group type selected
				var assigned_users = [];
				// in edit-mode
				if( $('#mode') && $('#mode').val() == 'edit' ){
					assigned_users = $('.bookly_students_before_edit').val();
					assigned_users.push(user_id);
					//$('#bookly_students').val(assigned_users);
				} else {
					assigned_users = $('#bookly_students').val();
					assigned_users.push(user_id);
					$('#bookly_students').val(assigned_users);
				}

			} else {
				$('#bookly_students').val(user_id);
			}


			if ($('#get_parent_only')) { // check if in opening balance shortcode
				// get parent data
				$.post(ajaxurl, {
					action: 'get_user_name_and_email',
					wp_user_id: user_id
				}, function (response) { // response callback function
					$('.find-parent-result').html(response);
				})
					.done(function () {

					});

				// load parent table data
				$.post(ajaxurl, {
					action: 'get_parent_makeup_logs',
					wp_user_id: user_id
				}, function (response) { // response callback function
					$('.parent_makeup_logs_table').html(response);
				})
					.done(function () {

					});


			} // end #get_parent_only
		}



		MicroModal.close('find-learners-modal');


	});



	$(".kanban-iframe").on("load", function() {
		let head = $(".kanban-iframe").contents().find("head");
		let css = '<style>   #tabs-boards { background: #f2f4f4 !important; } ' +
			'#tabs-boards a { background: #47b3e6 !important; color: white !important; }' +
			'#page-footer { background: #47b3e6 !important; }' +
			'#tabs-boards .active a { color: black !important; background: #fff !important; }' +
			'button.btn.btn-primary.btn-status-empty {  display: none !important;}' +
			' </style>';
		$(head).append(css);
	});



	MicroModal.init({
		// onShow: modal => console.info(`${modal.id} is shown`), // [1]
		// onClose: modal => console.info(`${modal.id} is hidden`), // [2]
		openTrigger: 'data-custom-open', // [3]
		closeTrigger: 'data-custom-close', // [4]
		openClass: 'is-open', // [5]
		disableScroll: true, // [6]
		disableFocus: false, // [7]
		awaitOpenAnimation: false, // [8]
		awaitCloseAnimation: false, // [9]
		debugMode: false // [10]
	});

	$('.find-learners').on('click', function (){
		MicroModal.show('find-learners-modal');
	});



	// on change teachers list, make submit btn disabled
	$('#single_program_booking_form .teacher_section select').on('change', function(){
		$('button.submit_booking').prop('disabled', true).addClass('disabled');
	});

	//
	$('.check_appointments').on('click', function (e){
		e.preventDefault();
		let teachers_ids = $('#teachers').val();

		$.post(ajaxurl, {
			action: 'check_teachers_appointments',
			teachers_ids: teachers_ids
		}, function (response) { // response callback function
			$('.check-teachers-result').html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();

			});


	});


	// get overlap appointments data
	$('.get_overlap_appointments').on('click', function (e){
		e.preventDefault();
		let overlap_appointments_ids = $('#overlap_appointments_ids').val();

		$.post(ajaxurl, {
			action: 'check_teachers_appointments',
			teachers_ids: teachers_ids
		}, function (response) { // response callback function
			$('.check-teachers-result').html(response);
		})
			.done(function () {
				//alert( "second success" );
				//location.reload();

			});

	});


	// select all for delete program modal
	$('#deleteAllProgram').click(function() {
		var checkedStatus = this.checked;
		$('.confirm-delete-program').prop('disabled', false).removeClass('disabled');
		$('#delete-single-program-modal').find(':checkbox').each(function() {
			$(this).prop('checked', checkedStatus);
		});
	});

	$('input[name="delete_program"]').on('click', function(){
		$('#deleteAllProgram').prop('checked', false);
		$('.confirm-delete-program').prop('disabled', false).removeClass('disabled');
	})

	// delete program action

	$("body").delegate(".delete-calendar-program", "click", function(e){
		e.preventDefault();
		// reset validate result
		$('#validate_delete_program').val('');
		$('.permanent-delete-program').attr('disabled', true).addClass('disabled').removeClass('active');
		$('.validate_delete_result').text('Validating delete program. Please Wait ...');

		let appointment_id = $(this).data('appointment-id');
		let stored_bb_group_id = $(this).data('stored-bb-group-id');
		let admin_delete = $(this).data('admin-delete');


		$('#appointment_id').val(appointment_id);
		$('#stored_bb_group_id').val(stored_bb_group_id);

		// validate status for all appointments with same stored_bb_group_id and fetch resutlt in modal
		$.post(ajaxurl, {
			action: 'validate_delete_program',
			stored_bb_group_id: stored_bb_group_id,
			admin_delete: admin_delete
		}, function (response) { // response callback function
			// $('.check-teachers-result').html(response);
			$('#validate_delete_program').val(response);
			if (response === 'delete_program_is_valid') {
				// enable delete button
				$('.permanent-delete-program').attr('disabled', false).removeClass('disabled').addClass('active');
				$('.validate_delete_result').html('<span class="success"> You can delete program successfully. </span> ');
			} else {
				$('.validate_delete_result').html('<span class="alert"> '+ response +' </span>');
				if( admin_delete === true ){
					$('.permanent-delete-program').attr('disabled', false).removeClass('disabled').addClass('active');
				} else {
					$('.permanent-delete-program').attr('disabled', true).addClass('disabled').removeClass('active');
					//$('.modal__footer').addClass('hidden').hide();
				}
			}

		})
		.done(function () {

		});

		MicroModal.show('delete-single-program-modal');

	});
	$("body").delegate("button.modal__btn.modal__btn-danger", "click", function(e) {

		MicroModal.close('delete-single-program-modal');
		$('.confirm-delete-program').prop('disabled', false).removeClass('disabled');

	});



	$("body").delegate(".permanent-delete-program", "click", function(e){
		e.preventDefault();
		$('.permanent-delete-program').attr('disabled', true).addClass('disabled').removeClass('active');
		$('.ajax_image_section.delete').show();
		let appointment_id = $('#appointment_id').val();

		// send ajax delte confirmation
		$.post(ajaxurl, {
			action: 'permanent_delete_single_program',
			appointment_id: appointment_id,
		}, function (response) { // response callback function
			// $('.check-teachers-result').html(response);
			$('.ajax_image_section.delete').hide();
			if( response === 'deleted' ){
				let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Program Removed Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
				$('.validate_delete_result').html('<span> '+ success_message +' </span>');
				location.reload();
			} else {
				let error_message = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> ' + response + '</p>';
				$('.validate_delete_result').html('<span class="alert"> '+ error_message +' </span>');
			}

		})
		.done(function () {

		});


	});


	// duplicate single event data
	$("body").delegate(".duplicate-event", "click", function(e){
		e.preventDefault();
		let appointment_id = $(this).data('event-id');
		let bb_group_id = $(this).data('bb-group-id');
		MicroModal.show('single-event-modal');
		$('.single-event-result').html('');
		$('#single-event-modal .modal__title').text(' Duplicate Single Event ');
		$('.duplicate-actions').removeClass('hidden').show();
		$('.single_appointment_id').val(appointment_id);
		$('.single_event_bb_group_id').val(bb_group_id);
		$('.single_event_action').val('duplicate');
		$('.single_event_proceed').removeClass('disabled').prop('disabled', false);
	});

	// delete single event data
	$("body").delegate(".delete-single-event", "click", function(e){
		e.preventDefault();
		let appointment_id = $(this).data('event-id');
		let bb_group_id = $(this).data('bb-group-id');
		MicroModal.show('single-event-modal');
		$('#single-event-modal .modal__title').text(' Delete Single Event ');
		$('.duplicate-actions').addClass('hidden').hide();
		$('.single-event-result').html(' <span> <i class="far fa-trash-alt"></i> Are you sure to delete this session ? </span> ');
		$('.single_appointment_id').val(appointment_id);
		$('.single_event_bb_group_id').val(bb_group_id);
		$('.single_event_action').val('delete');
		$('.single_event_proceed').removeClass('disabled').prop('disabled', false);
	});

	// submit single evenet action
	$("body").delegate("#single-event-modal .single_event_proceed", "click", function(e){
		$(this).addClass('disabled').prop('disabled', true);
		let this_modal = $(this).parent().parent().parent().parent();
		let appointment_id = this_modal.find('.single_appointment_id').val();
		let bb_group_id = this_modal.find('.single_event_bb_group_id').val();
		let single_event_action = this_modal.find('.single_event_action').val();
		let required_day = this_modal.find('.duplicate-actions .select_day:checked').val();

		this_modal.find('.action_loader').removeClass('hidden');
		$.post(ajaxurl, {
			action: 'single_event_action',
			appointment_id: appointment_id,
			bb_group_id: bb_group_id,
			single_event_action: single_event_action,
			required_day: required_day
		}, function (response) { // response callback function
			$('.action_loader').addClass('hidden');
			$('.duplicate-actions').hide();
			if( response === 'delete_success' ){
				let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Event Removed Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
				$('.single-event-result').html('<span> '+ success_message +' </span>');
				location.reload();
			} else if ( response === 'duplicate_success' ){
				let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Event Duplicated Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
				$('.single-event-result').html('<span> '+ success_message +' </span>');
				location.reload();
			} else {
				$('.single-event-result').html('<span> '+ response +' </span>');
			}

		})
		.done(function () {

		});

	});

	// get staff appointmnets from select options
	$('.get_staff_appointments').on('change', function (e){
		e.preventDefault();
		$('.preloader').show();
		let staff_id = $(this).val();
		let current_page_url = $('#current_page_url').val();
		window.location.href = current_page_url+"?team_staff_id="+staff_id;
	});


	// get customer appointmnets from childs select options
	$("body").delegate("#child_customer_id", "click", function(e){
		e.preventDefault();
		let customer_id = $(this).val();
		if( customer_id !== 'null' ){
			let current_page_url = $('#current_page_url').val();
			window.location.href = current_page_url+"?child_customer_id="+customer_id;
		}

	});

	// reset child select options
	$('.reset_appointments_view').on('click', function (e){
		$('.preloader').show();
		return;
		e.preventDefault();
		let page_url = $('#current_page_url').val();
		// if url has parameters set add attendance_date as a second parameter
		if (window.location.href.indexOf("?") > -1) {
			// url parameter found, set its value only
			const params = new Proxy(new URLSearchParams(window.location.search), {
				get: (searchParams, prop) => searchParams.get(prop),
			});
			let team_staff_id = params.team_staff_id;
			if( team_staff_id !== null ){
				window.location.href = page_url + '?team_staff_id=' + team_staff_id;
			} else {
				window.location.href = page_url;
			}

		} else {
			window.location.href = page_url;
		}

	});




	// send teacher wp_user_id to get his missing bb groups
	$("#fix_bb_groups_ca_records #fix_teacher").on("change", function(e){
		e.preventDefault();
		$('.ajax_image_section').show();
		let teacher_wp_user_id = $(this).val();
		$.post(ajaxurl, {
			action: 'get_missing_bb_groups',
			teacher_wp_user_id: teacher_wp_user_id,
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			$('#fix_bb_groups_ca_records #fix_bb_group').html(response);
			$('#fix_bb_groups_ca_records #fix_bb_group').select2();
		})
			.done(function() {

			});

	});

	// send bb group id to fix
	$("#fix_bb_groups_ca_records .fix_missing_bb_groups").on("click", function(e){
		e.preventDefault();
		$('.ajax_image_section').show();
		let teacher_wp_user_id = $("#fix_bb_groups_ca_records #fix_teacher").val();
		let bb_group_id = $('#fix_bb_group').val();
		$.post(ajaxurl, {
			action: 'fix_bb_group_ca_records',
			teacher_wp_user_id: teacher_wp_user_id,
			bb_group_id: bb_group_id
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			if( response === 'true' ){
				// show success modal
				let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Program Appointments Fixed Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
				$('#fix-modal .modal__content').html(success_message);
				MicroModal.show('fix-modal');
				location.reload();
			} else {
				// show error modal
				let error_messafe = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> ' + response + '</p>';
				$('#fix-modal .modal__content').html(error_messafe);
				MicroModal.show('fix-modal');
			}

		})
			.done(function() {

			});

	});


	// refresh selected bb groups on select
	$('.refresh_groups_select').on('click', function (e){
		e.preventDefault();
		// reload select missing bb groups
		$('.ajax_image_section').show();
		let teacher_wp_user_id = $("#fix_bb_groups_ca_records #fix_teacher").val();
		$.post(ajaxurl, {
			action: 'get_missing_bb_groups',
			teacher_wp_user_id: teacher_wp_user_id,
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			$('#fix_bb_groups_ca_records #fix_bb_group').html(response);
			$('#fix_bb_groups_ca_records #fix_bb_group').select2();
		})
			.done(function() {

			});
	});


	// press ctrl + shift + r


	// hide non BB group events on calendar
	$("body").delegate("#bookly-tbs .btn-group, #bookly-tbs .btn-group-vertical", "click", function(e){
		$('.ec-event-title.hide').parent().parent('.ec-events').remove();
	});


	// submit makeup open balance
	// submit makeup form
	$('.submit_open_makeup').on('submit', function (e) {
		e.preventDefault();
		$('.ajax_image_section').show();
		$('#submit_open_form').prop('disabled', true).addClass('disabled');
		$('.submit_open_makeup').parsley().validate();

		// get data and send with ajax
		let trans_amount = $('#trans_amount').val();
		let trans_type = $('#trans_type').val();
		let user_role = $('#user_role').val();
		let parent_id = $('.parent_id').val();
		let user_id = $('#user_id').val();
		let trans_notes = $('#trans_notes').val();

		// send these data to makeup log table
		$.post(ajaxurl, {
			action: 'store_makeup_log',
			trans_amount: trans_amount,
			trans_type: trans_type,
			user_role: user_role,
			parent_id: parent_id,
			user_id: user_id,
			trans_notes: trans_notes,
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			//$('.makeup-log-result').html(typeof response);
			if( response === '1' ){
				// let success_message = '<p><i class="far fa-check-circle"></i></p>  <h3> Data Saved Successfully </h3> <p> please refresh the page if it does not reload automatically. </p>';
				// $('#result-modal .modal__content').html(success_message);
				// MicroModal.show('result-modal');
				$.showInfo('Data Saved Successfully');
				location.reload();
			} else {
				// let error_messafe = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> ' + response + '</p>';
				// $('#result-modal .modal__content').html(error_message);
				// MicroModal.show('result-modal');
				$.showError(response);
			}

		})
			.done(function() {

			});


	});


	// attendance capture data tables init
	$('.attendance-capture').dataTable( {
		"autoWidth": false,
		"order": [[ 2, "asc" ]],
		"pageLength": 50
	});

	






	// attendace actions
	$("body").delegate(".edit-attendance", "click", function(e){
		e.preventDefault();

		// if lock edit is true lock every thing
		$(this).hide();
		$(".edit-attendance").not(this).show();
		let this_row = $(this).closest('tr');
		this_row.addClass('current_edit');
		let lock_edit = $(this).closest('tr').find('.lock_edit').val();
		let parent_id = $(this).closest('tr').find('.parent-id').val();

		if( lock_edit === 'locked' ){
			// show alert you can edit this record
			// only open notes for edit
			this_row.find('.notes textarea').attr('disabled', false);
		} else {
			$(this).closest('tr').addClass('active');
			$('.edit-attendance').not(this).closest('tr').removeClass('active');
		}

		// open edit for notes
		$('.active .notes textarea').attr('disabled', false);

		// update parent user makeup balance in field
		// $.post(ajaxurl, {
		// 	action: 'get_parent_user_makeup_balance',
		// 	parent_id: parent_id,
		// }, function (response){ // response callback function
		// 	$(this_row).find('.parent-makeup-balance').val(response);
		// 	$(this_row).find('.actual-parent-makeup-balance').val(response);
		// })
		// .done(function() {
		//
		// });

		let teacher_can_edit = this_row.find('.teacher_can_edit').val();
		if( teacher_can_edit === 'false' ){
			// hide save and cancel btns and show alert erro
			$.showWarning('You can only edit notes, 24 hrs passed');
		}


	});


	$("body").delegate(".attendance-status", "change", function() {

		let status = $(this).val();
		let actual_mins_class = $('.active .actual-mins-class').val();

		$('.active .late-mins').val(0);

		if (status === 'attended') {
			// show mins and enable edit
			$('.active .late-mins-div').hide();
			$('.active .actual-mins').val(actual_mins_class);

		} else if ( status === 'attended-sl' || status === 'attended-tl' ) {
			// class_time must be greater than or equal (actual + late )
			// makeup balance = class_time - ( actual + late )
			$('.active .late-mins-div').css('display', 'flex');
			$('.active .actual-mins').val(actual_mins_class);
			$('.active .actual-mins').attr('disabled', false).removeClass('disabled');
			let late_mins = $(this).closest('tr').find('.late-mins').val();
		} else if (status === 'holiday' || status === 'no-show-s' ) {
			$('.active .late-mins-div').hide();
			$('.active .actual-mins').val(actual_mins_class);
			$('.active .actual-mins').attr('disabled', true).addClass('disabled');
		} else {
			$('.active .actual-mins').val(0);
			$('.active .actual-mins').attr('disabled', true).addClass('disabled');
		}

		// if actual is zero , add minutes to makeup balance
		let actual_mins = $('.active .actual-mins').val();
		if( parseInt(actual_mins) === 0  ) {
			let actual_parent_makeup_balance = $('.active .actual-parent-makeup-balance').val();
			$('.active .parent-makeup-balance').val(parseInt(actual_mins_class) + parseInt(actual_parent_makeup_balance));
		} else {
			let actual_parent_makeup_balance =  $('.active .actual-parent-makeup-balance').val() ;
			$('.active .parent-makeup-balance').val(actual_parent_makeup_balance);
		}


	});


	// on cancel attendance delete class active
	$("body").delegate(".cancel-attendance", "click", function(e) {
		e.preventDefault();
		$(this).hide();
		let this_row = $(this).closest('tr');
		this_row.removeClass('current_edit');
		$('.edit-attendance').show();
		$('.save-attendance').hide();
		$('.active .late-mins-div').hide();
		$(this_row).removeClass('active');
		// open edit for notes
		$('.notes textarea').attr('disabled', true);

	});

	// if there is parent makeup balance actual time can be greater than class time and less or equal than makeup balance
	$("body").delegate(".actual-mins", "change", function(e) {
		e.preventDefault();
		let actual_mins =  $(this).val();
		$(this).attr('value', actual_mins);
		let status = $(".active .attendance-status").val();
		let class_time =  $('.active .actual-mins-class').val();
		let late_mins =  $('.active .late-mins').val();
		let parent_makeup_balance =  $('.active .parent-makeup-balance').val();
		let actual_parent_makeup_balance =  $('.active .actual-parent-makeup-balance').val();
		updateMakeUpEdit(actual_mins, late_mins, class_time, actual_parent_makeup_balance, parent_makeup_balance, status, 'actual');

	});

	$("body").delegate(".late-mins", "change", function(e) {
		e.preventDefault();
		let late_mins =  $(this).val();
		$(this).attr('value', late_mins);
		let status = $(".active .attendance-status").val();
		let class_time =  $('.active .actual-mins-class').val();
		let actual_mins =  $('.active .actual-mins').val();
		let parent_makeup_balance =  $('.active .parent-makeup-balance').val();
		let actual_parent_makeup_balance =  $('.active .actual-parent-makeup-balance').val();
		updateMakeUpEdit(actual_mins, late_mins, class_time, actual_parent_makeup_balance, parent_makeup_balance, status, 'late');

	});

	$("body").delegate(".save-attendance", "click", function(e) {
		e.preventDefault();
		let this_row = $(this).closest('tr');
		$(this).attr('disabled', true).addClass('disabled');
		let attendance_status = $('.active .attendance-status').val();
		let actual_mins = $('.active .actual-mins').val();
		let late_mins = $('.active .late-mins').val();
		let parent_makeup_balance =  $('.active .parent-makeup-balance').val();
		let actual_parent_makeup_balance =  $('.active .actual-parent-makeup-balance').val();
		let meta_parent_makeup_balance =  $('.active .meta-parent-makeup-balance').val();
		let parent_id = $('.active .parent-id').val();
		let bb_group_id = $('.active .bb-group-id').val();
		let ca_id = $('.active .ca-id').val();
		let trans_amount = 0;
		if( parseInt(actual_parent_makeup_balance) > parseInt(parent_makeup_balance) ){
			trans_amount = parseInt(parent_makeup_balance) - parseInt(actual_parent_makeup_balance);
		} else if ( parseInt(actual_parent_makeup_balance) < parseInt(parent_makeup_balance) ){
			trans_amount = parseInt(parent_makeup_balance) - parseInt(actual_parent_makeup_balance);
		} else {
			trans_amount = 0;
		}

		let trans_type = 'normal';
		let trans_notes = 'captured from teacher attendance table';
		let user_role = $('.user-role').val();
		let user_id = $('.user-id').val();
		let created_at = $('.created-at').val();
		let appointmnet_id = $('.active .appointmnet-id').val();
		let customer_id = $('.active .customer-id').val();

		let progress_notes = $('.active .progress-notes').val();
		let private_notes = $('.active .private-notes').val();

		progress_notes = progress_notes.replace(/(\r\n|\n|\r)/gm," ");
		private_notes = private_notes.replace(/(\r\n|\n|\r)/gm," ");

		if( progress_notes === undefined ){
			$.showError('This is an error message');
		}


		// check if tr is locked only save notes
		let edit_status = this_row.find('.edited').val();
		let update_makeup_log_record = 'true';
		let makeup_log_record = '';
		if( edit_status === 'edited' ){
			// save attendance and update makeup log record
			update_makeup_log_record = 'true';
			makeup_log_record = this_row.find('.makeup_log_id').val();
		} else {
			// save all data
			update_makeup_log_record = 'false';
			makeup_log_record = '';
		}


		let teacher_can_edit = this_row.find('.teacher_can_edit').val();


		//send ajax to save attendace
		$.post(ajaxurl, {
			action: 'save_attendance_class',
			attendance_status: attendance_status,
			actual_mins: actual_mins,
			late_mins: late_mins,
			parent_makeup_balance: parent_makeup_balance,
			meta_parent_makeup_balance: meta_parent_makeup_balance,
			parent_id: parent_id,
			bb_group_id: bb_group_id,
			ca_id: ca_id,
			trans_amount: trans_amount,
			trans_type: trans_type,
			trans_notes: trans_notes,
			user_role: user_role,
			user_id: user_id,
			appointmnet_id: appointmnet_id,
			customer_id: customer_id,
			progress_notes: progress_notes,
			private_notes: private_notes,
			update_makeup_log_record: update_makeup_log_record,
			makeup_log_record: makeup_log_record,
			teacher_can_edit: teacher_can_edit,
			created_at: created_at
		}, function (response){ // response callback function
			if( response === 'success' ){
				$.showInfo('Saved Successfully');
				location.reload();
			} else {

				if( response === 'meta_conflict' ){
					$.showError('Conflict detected please refresh and try again.');
					location.reload();
				} else {

					let error_message = '<p class="text-center"> <i class="fas fa-exclamation-triangle" style="color: red; font-size: 2rem;"></i> </p> <p> '+ response +' </p>';
					$('#result-modal .modal__content').html(error_message);
					MicroModal.show('result-modal');
				}

			}
		})
		.done(function() {

		});

	});


	// attendance table datepicker init
	jQuery("body").delegate('.attendate-datepicker-section input', "click", function(e){
		$('[data-toggle="datepicker"]').datepicker({
			startDate: today, // today
			format: 'yyyy-mm-dd',
			weekStart: 1,
			trigger: this,
		});
	});

	// filter attendance appointments based on datepicker value
	// $('.filter-attendance-date').on('click', function (e){
	$('.attendance-date-picker').on('change', function (e){
		e.preventDefault();
		$('.preloader').show();
		// let selected_date = $('.attendance-date-picker').val();
		let selected_date = $(this).val();
		let page_url = $('#current_page_url').val();
		// if url has parameters set add attendance_date as a second parameter
		if (window.location.href.indexOf("?") > -1) {
			// url parameter found, set its value only
			const params = new Proxy(new URLSearchParams(window.location.search), {
				get: (searchParams, prop) => searchParams.get(prop),
			});
			let team_staff_id = params.team_staff_id;
			if( team_staff_id !== null ){
				window.location.href = page_url + '?attendance_date=' + selected_date + '&team_staff_id=' + team_staff_id;
			} else {
				window.location.href = page_url + '?attendance_date=' + selected_date;
			}

		} else {
			window.location.href = page_url + '?attendance_date=' + selected_date;
		}


	});

	//$('.dataTables_length').after( $('.attendate-datepicker-section') );

	// validate attendance form
	$('.attendance-table-form').parsley();

	// show tooltip
	//$('[data-toggle="tooltip"]').tooltip();

	$('.swap_date').on('click', function (e){
		e.preventDefault();
		let page_url = $('#current_page_url').val();
		let selected_date = $(this).data('selected-date');
		let current_page_url = $(location).attr('href');
		// if url has parameters set add attendance_date as a second parameter
		if (window.location.href.indexOf("?") > -1) {
			// url parameter found, set its value only
			const params = new Proxy(new URLSearchParams(window.location.search), {
				get: (searchParams, prop) => searchParams.get(prop),
			});
			let team_staff_id = params.team_staff_id;
			if( team_staff_id !== null ){
				window.location.href = page_url + '?attendance_date=' + selected_date + '&team_staff_id=' + team_staff_id;
			} else {
				window.location.href = page_url + '?attendance_date=' + selected_date;
			}

		} else {
			window.location.href = page_url + '?attendance_date=' + selected_date;
		}




	});

	// edit single form
	$('#single_program_booking_form.edit-mode #bb_group_id').show().select2();
	$('#single_program_booking_form.edit-mode [data-toggle="datepicker"]').datepicker({
		startDate: tomorrow, // was tomorrow
		endDate: daysForBooking,
		format: 'mm/dd/yyyy',
		weekStart: 1,
		trigger: $('.datepicker_trigger'),
	});

	$('#single_program_booking_form.edit-mode').find('.class_day input').prop('disabled', false);

	// disable class days for edit schedule has stored series id
	$('.stored_schedule').each(function(){

		$(this).find('.class_day input').addClass('disabled').attr('disabled', true);

	});

	//edit mode options
	$('.edit-learners-section').hide();
	$('.edit-mode .find-learners').hide();
	$('.edit-options input').on('click', function (){
		$("#single_program_booking_form .bookly_effective_date").attr('disabled', false);
		$('.datepicker_trigger').show();
		$('.edit-mode .new_bookly_form').show();
		$('.edit-learners-section').hide();
		$('.ajax_image_section').show();
		let edit_option = $(this).val();
		if(  edit_option === 'transfer' ){
			$('.edit-mode .teacher_section').show().removeClass('hidden');
			$('.edit-mode a.find_teacher.single').show().removeClass('hidden');
			$('.edit-mode a.find_teacher.all.edit-mode-check').hide().addClass('hidden');
			$('.teachers-section').removeClass('hidden');
			$('.teachers-section select').prop('disabled', false).select2();
			$('.add_new_row').prop('disabled', true).hide();
			$('.remove_row').prop('disabled', true).hide();
			$('.new_schedule_section_rows').each(function (){
				$(this).remove();
			});
			$('.class_days input').prop('disabled', true);
			$('button.submit_booking').text('Confirm transfer to new teacher');

			// get teachers based on services by ajax
			let bookly_service_id = $('#single_program_booking_form .bookly_services_cloned select').val();
			$.post(ajaxurl, {
				action: 'get_class_teacher',
				bookly_service_id: bookly_service_id,
			}, function (response){ // response callback function
				// show teacher select
				$('#single_program_booking_form .teacher_section .teacher_select').show();
				$('#single_program_booking_form .teacher_select select').html(response);
				$('#single_program_booking_form .teacher_select select').select2();

			})
			.done(function() {
				//alert( "second success" );
				//location.reload();
				$('.ajax_image_section').hide();
			});

		} else if ( edit_option === 'edit' ){
			location.reload();
		} else if ( edit_option === 'cancel' ){
			$('.new_schedule_section_rows').each(function (){
				$(this).remove();
			});
			$('.add_new_row').prop('disabled', true).hide();
			$('.remove_row').prop('disabled', true).hide();
			$('.class_days input').prop('disabled', true);
			$('.edit-mode .teacher_section').hide().addClass('hidden');
			$('.edit-mode a.find_teacher.single').hide().addClass('hidden');
			$('button.submit_booking').text('Confirm cancel program');
			$('.ajax_image_section').hide();
		} else if( edit_option === 'edit-learners' ){
			$('#bookly_students').prop('multiple', true);
			$('.edit-mode .new_bookly_form').hide();
			$('.find-learners').show();
			$('.edit-learners-section').show();

			// if effective date selected and current learners list != updated learners list, enable submit
			$('.new-effective-date').on('change', function (){
				// check if current learners list != updated learners list
				let current_list = $('.bookly_students_before_edit').val();
				let updated_list = $('.bookly_students_after_edit').val();

				if( equals( current_list, updated_list ) != true ) {
					$('.update-learners').prop('disabled', false).removeClass('disabled');
				} else {
					$('.update-learners').prop('disabled', true).addClass('disabled');
				}
			});

			// in change event for new learners list and current learners list != updated learners list, enable submit
			$('#bookly_students').on('change', function (){
				// check that new effective value is found and check if current learners list != updated learners list
				let current_list = $('.bookly_students_before_edit').val();
				let updated_list = $('.bookly_students_after_edit').val();
				let new_effective_date = $('.new-effective-date').val();

				if( new_effective_date.length > 0 && equals( current_list, updated_list ) != true ) {
					$('.update-learners').prop('disabled', false).removeClass('disabled');
				} else {
					$('.update-learners').prop('disabled', true).addClass('disabled');
				}
			});


			$('.ajax_image_section').hide();
		}


	});

	// on submit update learners, send ajax
	$('.update-learners').on('click', function (e){
		e.preventDefault();
		$('.ajax_image_section').show();
		let current_learners = $('.bookly_students_before_edit').val();
		let updated_learners = $('.bookly_students_after_edit').val();
		let new_effective_date = $('.new-effective-date').val();
		let bb_group_id = $('#stored_bb_group_id').val();


		// send data to ajax
		$.post(ajaxurl, {
			action: 'update_learners_edit_mode',
			current_learners: current_learners,
			updated_learners: updated_learners,
			new_effective_date: new_effective_date,
			bb_group_id: bb_group_id
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			$('.update-learners').remove();
			if( response.success == false ){
				$.showError(response.data.message);
			} else {
				$.showInfo(response.data.message);
				location.reload();
			}
		});

	});

	$('.collapse-search').parent('.dataTables_wrapper').addClass('collapse-search');

	$('.dataTables_wrapper.collapse-search .dataTables_filter input').on('mouseover', function(){
		$(this).addClass('expand-search');
	});

	$('.dataTables_wrapper.collapse-search .dataTables_filter input').on('mouseleave', function(){
		$(this).removeClass('expand-search');
	});

	$('.teacher_section.teacher_select').on('change', function(){
		validateScheduleRows();
	});

	$('.cancel-delete-action').on('click', function (e){
		e.preventDefault();
		$('.delete-confirm').removeClass('show');
		$('.schedule_booking_section').removeClass('removing-col');
	});

	$('.confirm-delete-action').on('click', function (e){
		e.preventDefault();
		let this_schedule_row = $(this).parent().parent().parent();
		// check if in edit mode and row has series id, remove by ajax
		let stored_bookly_series_id = this_schedule_row.find('.stored_bookly_series_id').val();
		let stored_schedule_entry_id = this_schedule_row.find('.stored_schedule_entry_id').val();
		let stored_gf_timezone = this_schedule_row.find('.stored_gf_timezone').val();
		let stored_start_time_converted = this_schedule_row.find('.stored_start_time_converted').val();

		this_schedule_row.find('.delete-confirm').addClass('show');
		this_schedule_row.addClass('removing-col');

		// run confirm first
		if( stored_bookly_series_id !== undefined ){
			let new_effective_date = $('#single_program_booking_form.edit-mode .bookly_effective_date').val();
			if( new_effective_date === undefined || new_effective_date === '' ){
				$.showError('Please select new effective from');
			} else {
				$('.ajax_image_section').show();
				// delete this appointments with series id starting from new effective date
				// send ajax
				$.post(ajaxurl, {
					action: 'delete_schedule_edit_mode',
					new_effective_date: new_effective_date,
					stored_bookly_series_id: stored_bookly_series_id,
					stored_schedule_entry_id: stored_schedule_entry_id,
					stored_gf_timezone: stored_gf_timezone,
					stored_start_time_converted: stored_start_time_converted
				}, function (response){ // response callback function
					$('.ajax_image_section').hide();
					if( response === 'true' ){
						// disable current schedule row
						this_schedule_row.find('select').attr('disabled', true);
						this_schedule_row.find('.new_end_date').text(new_effective_date);
						$.showInfo('schedule deleted successfully');
						$('.delete-confirm').removeClass('show');
						$('.schedule_booking_section').removeClass('removing-col');
					} else {
						$.showError(response);
					}
				})
				.done(function() {
					//alert( "second success" );

				});
			}

		} else {
			$(this).closest('#single_program_booking_form .schedule_booking_section').remove();
			$('.teacher_select select').prop('selectedIndex',0);
			min_rows_single_program--;
		}


	});


	// click to fix appointments has end date on schedule and still appears on calendar
	$('.fix_schedule').on('click', function (e){
		e.preventDefault();
		$('.ajax_image_section').show();

		let series_id = $(this).data('series-id');
		let bb_group_id = $(this).data('bb-group-id');
		let schedule_end_date = $(this).data('schedule-end-date');

		// send series id to ajax to delete appointments
		$.post(ajaxurl, {
			action: 'fix_delete_schedule_edit_mode',
			series_id: series_id,
			bb_group_id: bb_group_id,
			schedule_end_date: schedule_end_date,
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			if( response == 'true' ){
				$.showInfo('schedule updated successfully');
				location.reload();
			} else {
				$.showError(response);
			}
		})
		.done(function() {
			//alert( "second success" );

		});


	});


	$('#fix_bb_groups_schedules_form .fix_bb_groups_schedule').on('click', function (e){
		e.preventDefault();
		$('.ajax_image_section').show();
		let staff_id = $('#fix_bb_groups_schedules_form select').val();
		if( staff_id === null || staff_id === undefined ){
			$.showError('please select teacher first');
			$('.ajax_image_section').hide();
			return;
		}
		$.post(ajaxurl, {
			action: 'fix_appointments_timezone',
			staff_id: staff_id,
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			if( response === 'true' ){
				// disable current schedule row
				$.showInfo('Success, teacher appointments updated');
			} else {
				$.showError(response);
			}
		})
			.done(function() {
				//alert( "second success" );
			});

	});

	// fix start time in gf schedule entry
	$('.fix-start-time').on('click', function (){
		$('.fix-start-time-result').html();
		$('.ajax_image_section').show();
		$.post(ajaxurl, {
			action: 'fixGFStartTime',
		}, function (response){ // response callback function
			$('.ajax_image_section').hide();
			console.log(typeof response)
			$('.fix-start-time-result').html(response);
			if( response === 'true' ){
				// disable current schedule row
				$.showInfo('Success, start time updated');
			} else {
				$.showError(response);
			}
		})
		.done(function() {
			//alert( "second success" );
		});
	});


	$('.link-subscription').on('click', function (){

		$.post(ajaxurl, {
			action: 'get_active_subs_for_parent',
			parent_id: 399
		}, function (response){ // response callback function
			$('.link-sub-result').html(response);
		})
		.done(function() {
			//alert( "second success" );
		});

	});


	//fix missing learners gf entries
	$('.fix-missing-learners-entries').on('click', function (){
		//$('.ajax_image_section').show();
		$('.fix-missing-learners-result').html();

		let last_bb_group_id = $('#last_bb_group_id').val();
		let stop_limit = Math.ceil(last_bb_group_id/50);


		let ajax_data = {};
		ajax_data['action'] = "fix_missing_learners_entries";

		var promises = [];
		for (var i = 1; i <= stop_limit; i++) {
			ajax_data['index'] = i;
			var request = $.ajax({
				url: ajaxurl,
				method:'post',
				data: ajax_data,
				async: false,
				success: function (resp) {
					$('.ajax_image_section').hide();
					$('.fix-missing-learners-result').append(resp);
				}
			})

			promises.push(request);

		}

		$.when.apply(null, promises).done(function() {
			$('.fix-missing-learners-result').append('Sync Done.');
		})




	});


	// test ajax
	$('.test-ajax').on('click', function (){
		var ajax_data = {};
		ajax_data['action'] = "test_ajax";
		ajax_data['start_sync'] = 'start_sync';
		sendAjax(ajax_data).then(function(data) {
			// Run this when your request was successful
			$('.test-ajax-result').append(data);
			console.log(JSON.parse(data))
		}).catch(function(err) {
			// Run this when promise was rejected via reject()
			console.log(err)
		})

		// var ajax_data = {};
		// ajax_data['action'] = "test_ajax";
		//
		//
		// for (let i = 1; i <= 5; i++) {
		//
		// 	ajax_data['index'] = i;
		//
		// 	sendAjax(ajax_data).then(function(data) {
		// 		// Run this when your request was successful
		// 		$('.test-ajax-result').append(data);
		// 		console.log(data)
		// 	}).catch(function(err) {
		// 		// Run this when promise was rejected via reject()
		// 		console.log(err)
		// 	})
		//
		//
		// }

	});

	// sync parent stats action
	$("body").delegate(".sync_user_billing_stats", "click", function(e) {
		e.preventDefault();
		animateRotate(180,"infinite");
		let wp_user_id = $(this).data('wp-user-id');
		let this_row = $(this).parent().parent().parent().parent();
		this_row.find('.sync_user_billing_stats i').addClass('rotate');
		let show_assigned_to = $('#show-assigned-to');

		let show_assigned_to_val = '';
		if( show_assigned_to && show_assigned_to.val() == 1 ){
			show_assigned_to_val = 'show';
		} else {
			show_assigned_to_val = 'hide';
		}

		$.post(ajaxurl, {
			action: 'sync_single_parent_stats',
			wp_user_id: wp_user_id,
			show_assigned_to_val: show_assigned_to_val
		}, function (response){ // response callback function

		})
		.done(function(response) {
			//alert( "second success" );
			$('.sync_user_billing_stats i').removeClass('rotate');
			this_row.html(response);
		});


	});

	// ajax action to get child classes on new ticket page
	$('.learner_sel').on('change', function (){

		let wp_user_id = $(this).find(':selected').data('child-user-id');
		$('.class_sel').html('<option>Loading...</option>');
		$.post(ajaxurl, {
			action: 'get_child_classes_ajax',
			wp_user_id: wp_user_id
		}, function (response){ // response callback function

		})
		.done(function(response) {
			//alert( "second success" );

			$('.class_sel').html(response);
		});


	});

	// parent stats show user details on sidepanel
	$("body").delegate(".parent-name .panel-toggle", "click", function(e) {
		e.preventDefault();
		let wp_user_id = $(this).parent().find('.sync_user_billing_stats').data('wp-user-id');
		$('.panel-content').load('/wp-content/themes/buddyboss-child-theme/images/laoders/loader.svg');
		$.post(ajaxurl, {
			action: 'get_single_parent_stats',
			wp_user_id: wp_user_id
		}, function (response){ // response callback function

		})
		.done(function(response) {
			//alert( "second success" );
			$('.panel-content').html(response);
		});

	});


	/**** side panel *****/
	var $menuToggle = jQuery('.panel-toggle'), $body = jQuery('.hidden-panel'), $panel_container =jQuery('.side-panel');

	function menuToggleClickHandler() {
		$body.toggleClass('panel-open');
		$panel_container.toggleClass('side-open');
		$menuToggle.toggleClass('open');
	}

	$("body").delegate( '.panel-toggle' , "click", function(e) {
		menuToggleClickHandler();
	});

	$("body").delegate( '.hidden-panel-close' , "click", function(e) {
		$body.removeClass('panel-open');
		$panel_container.removeClass('side-open');
		$menuToggle.removeClass('open');
	});

	// assign parent stat to support user
	$('body').delegate('.select_user_to_assign', 'click', function (e){
		e.preventDefault();
		let this_row = $(this).parent();
		let wp_user_id = $(this).val();
		let row_id = this_row.find('.row_id').val();

		if( row_id.length > 0){
			$.post(ajaxurl, {
				action: 'assign_to_parent_stats',
				wp_user_id: wp_user_id,
				row_id: row_id
			}, function (response){ // response callback function
				if( response === 'updated' ){
					$.showInfo('Updated successfully');
				} else {
					$.showError(response);
				}
			})
				.done(function() {
					//alert( "second success" );
				});
		}



	});


	// set learner new password
	// $('body').delegate('.set_new_pass', 'click', function (e){
	// 	e.preventDefault();
	// 	let wp_user_id = $(this).data('');
	//
	// 	if( row_id.length > 0){
	// 		$.post(ajaxurl, {
	// 			action: 'assign_to_parent_stats',
	// 			wp_user_id: wp_user_id,
	// 			row_id: row_id
	// 		}, function (response){ // response callback function
	// 			if( response === 'updated' ){
	// 				$.showInfo('Updated successfully');
	// 			} else {
	// 				$.showError(response);
	// 			}
	// 		})
	// 			.done(function() {
	// 				//alert( "second success" );
	// 			});
	// 	}
	//
	// });

	// convert minsutes into hrs:mins in open balance form
	$('input#trans_amount').on('keyup', function(){

		let total_mins_val = $(this).val();
		let hours = (total_mins_val / 60);
		let rhours = Math.floor(hours);
		let minutes = (hours - rhours) * 60;
		let rminutes = Math.round(minutes);

		$('input#hrs_mins').val( rhours + ':' + rminutes );

	});

	// add zoom meeting id to summer camp
	$('body').delegate('.add-summer-camp-zoom-id', 'click', function (e){
		e.preventDefault();
		let this_form = $(this).parent();
		let zoom_meeting_id = this_form.find('#summer-camp-zoom-id').val();
		let summer_camp_group_id = this_form.find('.summer_camp_group_id').val();

		$.post(ajaxurl, {
			action: 'add_summer_camp_zoom_id',
			zoom_meeting_id: zoom_meeting_id,
			summer_camp_group_id: summer_camp_group_id
		}, function (response){ // response callback function
			$.showInfo('Saved Successfully');
			location.reload();
		})
		.done(function() {
			//alert( "second success" );
		});


	});



	// sync all button in all parent stats table
	$('body').delegate('.sync-all', 'click', function (e){
		e.preventDefault();
		var elems = $('.parent-status-table tbody tr'), count = elems.length;
		let current_page = parseInt($('a.paginate_button.current').text());
		let last_page = parseInt($('.dataTables_paginate.paging_simple_numbers span a').last().text());

		elems.each( function(i,el) {

			setTimeout(function(){
				$(el).find('.sync_user_billing_stats').click();
				count--;
				if (count == 0 && current_page !== last_page ){
					$('.paginate_button.next').click();
					$('.sync-all').click();
				}

				if( count == 0 && current_page == last_page ){
					$.showInfo('Sync Done Successfully');
					location.reload();
				}


			},5000 + ( i * 5000 ));

		});

	});


	// sync all button in all parent stats table
	$('body').delegate('.cancel-session', 'click', function (e){
		e.preventDefault();
		$("#cancel-session .modal-body").html('<div id="loader"></div>');
		let ca_id = $(this).data('ca-id');
		// update ca_id for session in modal
		if( $('.cancel_ca_id') && $('.cancel_ca_id').length > 0  ){
			$('.cancel_ca_id').val(ca_id);
		} else {
			$("#cancel-session .modal-body").append( '<input type="hidden" class="cancel_ca_id" value="'+ca_id+'">' );
		}

		// get session details and push to modal
		let get_session_details_for_cancellation = {
			action : 'get_session_details_for_cancellation',
			ca_id: ca_id
		};
		$.post(ajaxurl, get_session_details_for_cancellation, function (response) { // response callback function
			$("#cancel-session .modal-body").html(response);
		})

	});

	// confirm session cancellation
	$('body').delegate('.approve-cancel-session', 'click', function (e){
		e.preventDefault();
		let send_notification = false;

		if( $('#send_notification') && $('#send_notification').length > 0 && $('#send_notification').val() == 'send_notification' ){
			send_notification = true;
		}

		let add_makeup = false;
		if( $('#add_makeup') && $('#add_makeup').length > 0 && $('#add_makeup').val() == 'add_makeup' ){
			add_makeup = true;
		}

		let ca_id = $(this).data('ca-id');
		let bb_group_id = $(this).data('bb-group-id');
		let start_date = $(this).data('start-date');
		let end_date = $(this).data('end-date');
		$("#cancel-session .modal-body").html('<div id="loader"></div>');


		// send ajax to process cancel session
		let process_cancellation = {
			action : 'process_session_cancellation',
			ca_id: ca_id,
			bb_group_id: bb_group_id,
			send_notification: send_notification,
			start_date: start_date,
			end_date: end_date,
			add_makeup: add_makeup
		};
		$.post(ajaxurl, process_cancellation, function (response) { // response callback function
			if( response == 'success' ){
				$('#cancel-session button.btn-close').trigger('click');
				$.showInfo('Session Cancelled Successfully');
				location.reload();
			} else {
				$("#cancel-session .modal-body").html(response);
			}

		})

	});

	// if session status = cancelled => edit is locked, and action btns will be removed
	$('.locked_due_to_cancelled .edit-actions').remove();


	// fix modal overlay under header and sidebar
	$(".event-actions .btn").on("click",function(){$("#content").css('zIndex',99999)})
	$(".modal.micromodal-slide .modal__btn.modal__btn-danger").on("click",function(){$("#content").css('zIndex',1)})
	$(".modal.micromodal-slide .modal__close").on("click",function(){$("#content").css('zIndex',1)})

}); ////////////////////////////////////////////////////////////////////////////// document ready end //////////////////////////////////////////////////////////////////////////////


function updateMakeUpEdit(actual_mins, late_mins, class_time, actual_parent_makeup_balance, parent_makeup_balance, status, event_source){

	//  always check that actual mins + late  <= actual limit
	// class_time must be greater than or equal (actual + late )

	if( status !== 'attended-tl' ){
		if( ( parseInt(actual_mins) + parseInt(late_mins) ) > parseInt(class_time) ){
			$('.active .parent-makeup-balance').val(actual_parent_makeup_balance);
			if( event_source === 'late' ){
				$('.active .late-mins').val(0);
			} else {
				$('.active .actual-mins').val(0);
			}
		}

		// makeup balance = class_time - ( actual + late )
		let new_makeup_balance = parseInt(class_time) - ( parseInt(actual_mins) + parseInt(late_mins) ) + parseInt(actual_parent_makeup_balance);
		if( new_makeup_balance < 0 ){
			$('.active .parent-makeup-balance').val(0);
		} else {
			$('.active .parent-makeup-balance').val(new_makeup_balance);
		}
	} else {
		// status is attended teacer late
		if( parseInt(actual_mins) > parseInt(class_time) ){
			$('.active .actual-mins').val(class_time);
		}

	}



	// if( parseInt( actual_parent_makeup_balance ) > 0 && ( parseInt(actual_mins) > parseInt(class_time) ) ){
	//
	// 	let new_makeup_balance = parseInt(actual_parent_makeup_balance) - ( parseInt(actual_mins) - parseInt(class_time) );
	// 	if( new_makeup_balance > 0 ){
	// 		$('.active .parent-makeup-balance').val(new_makeup_balance);
	// 	} else {
	// 		$('.active .parent-makeup-balance').val(0);
	// 		// teacher can't enter more than this value
	//
	// 		let actual_limit = $('.active .actual-limit').val();
	// 		$('.active .actual-mins').val(actual_limit);
	// 	}
	// } else if ( parseInt(actual_mins) < parseInt(class_time) ) {
	// 	new_makeup_balance = parseInt(actual_parent_makeup_balance) + ( parseInt(class_time) - parseInt(actual_mins) );
	// 	$('.active .parent-makeup-balance').val(new_makeup_balance);
	// } else if ( parseInt(actual_mins) > parseInt(class_time) && parseInt( actual_parent_makeup_balance ) === 0 ) {
	// 	$('.active .parent-makeup-balance').val(0);
	// 	$('.active .actual-mins').val(class_time);
	// }
}


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



function validateScheduleRows(){
	// compare new effective date with each schedule row(s), if bigger assign as
	let effective_date = $(".edit-mode .bookly_effective_date").val()
	$('.schedule_booking_section').each(function (){
		let schedule_end_date = $(this).find('.schedule_end_date').val();
		let schedule_start_date = $(this).find('.schedule_start_date').val();
		Schedule_end_date = new Date(schedule_end_date);
		Schedule_start_date = new Date(schedule_start_date);
		Effective_date = new Date(effective_date);

		if(Effective_date.getTime() < Schedule_end_date.getTime() || schedule_end_date === 'false' ){
			$(this).find('.send_to_backend').val('true');
			$(this).addClass('send_to_backend');
		} else {
			$(this).find('.send_to_backend').val('');
			$(this).removeClass('send_to_backend');
		}

		if( Schedule_start_date.getTime() < Effective_date.getTime() ){
			$(this).find('.clone_gf_schedule').val('clone');
		} else {
			$(this).find('.clone_gf_schedule').val('');
		}

	});


}


function excludeOldStaffId(){
	let old_teacher_id = $('.old_teacher_id').val();
	$('.teacher_section.teacher_select select option').each(function (){
		if( parseInt( $(this).val() ) === parseInt(old_teacher_id) ){
			$(this).prop('disabled', true);
		}
	});
	$('#single_program_booking_form .teacher_section').find('select').select2({
		dropdownCssClass: "teacher_dropdown"
	});
}

async function paste(input) {
	const text = await navigator.clipboard.readText();
	$('.paste-here').val(text);
}


function recursiveAjaxCall(ajax_data, counter_stop)
{
	$.ajax({
		url: ajaxurl,
		data: ajax_data,
		success: function(data,status)
		{
			if(data !== 'true')
			{
				//recursively call this function if the data recieved from backend is less than the input number
				recursiveAjaxCall(ajax_data, counter_stop);
			}
			else
			{
				alert('ajax finished');
			}
		},
		async:   true
	});
}

function sendAjax(ajax_data) {
	return new Promise(function(resolve, reject) {
		$.post({
			url: ajaxurl,
			data: ajax_data,
			async: false,
			success: function(data) {
				resolve(data) // Resolve promise and go to then()
			},
			error: function(err) {
				reject(err) // Reject the promise and go to catch()
			}
		});
	});
}

function animateRotate(angle,repeat) {
	var duration= 1000;
	setTimeout(function() {
		if(repeat && repeat == "infinite") {
			animateRotate(angle,repeat);
		} else if ( repeat && repeat > 1) {
			animateRotate(angle, repeat-1);
		}
	},duration)
	var $elem = $('.rotate');

	$({deg: 0}).animate({deg: angle}, {
		duration: duration,
		step: function(now) {
			$elem.css({
				'transform': 'rotate('+ now +'deg)'
			});
		}
	});
}

function stopRotate() {
	var $elem = $('.rotate');
	let parent = $elem.parent();
	$elem.remove();
	parent.append('<i class="bb-icon-sync bb-icon-l"></i>');

}


function copyToClipboard(copyText) {
	/* Copy the text inside the text field */
	navigator.clipboard.writeText(copyText);
	$.showInfo('copied to clipboard');
}


// conmpare 2 arrays values
const equals = (a, b) => JSON.stringify(a) === JSON.stringify(b);
