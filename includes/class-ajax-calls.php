<?php
/******
 * Ajax action to get bookly category services
 ******/

add_action('wp_ajax_get_bookly_category', 'get_bookly_category');
add_action( 'wp_ajax_nopriv_get_bookly_category', 'get_bookly_category' );
function get_bookly_category(){

    $bookly_category_id = $_POST['bookly_category_id'];

    // get services in this category as options
    global $wpdb;
    $table_name = $wpdb->prefix . 'bookly_services';
    $services = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE category_id = {$bookly_category_id}"
    );
    $wpdb->flush();

    $services_options = '<option selected disabled>Choose Service...</option>';
    foreach ( $services as $service ):
        $services_options .= '<option value="'. $service->id .'"> ' . $service->title .' </option>';
    endforeach;

    echo $services_options;

    wp_die();


}

/******
 * Ajax action to get bookly class teacher
 ******/

add_action('wp_ajax_get_class_teacher', 'get_class_teacher');
add_action( 'wp_ajax_nopriv_get_class_teacher', 'get_class_teacher' );
function get_class_teacher(){

    $bookly_service_id = $_POST['bookly_service_id'];

    // get teachers related to subject
    global $wpdb;
    $bookly_staff_services_table = $wpdb->prefix . 'bookly_staff_services';
    $wphr_hr_employees_table = $wpdb->prefix . 'wphr_hr_employees';

    // get only active teachers from HR table
    $active_teachers_results = $wpdb->get_results(
        "SELECT user_id FROM $wphr_hr_employees_table WHERE status = 'active' AND department = 8 "
    );
    $wpdb->flush();

    if( empty($active_teachers_results) ):
        echo '<option value=""> -- no active teachers found -- </option>';
    else:

        $active_teachers = implode(',', array_column($active_teachers_results, 'user_id') );

        $staff_results = $wpdb->get_results(
            "SELECT staff_id FROM $bookly_staff_services_table WHERE service_id = {$bookly_service_id} "
        );
        $wpdb->flush();

        // return with list of staff members
        $staff_select_options = '<option selected disabled>Choose Teacher...</option>';

        if( empty($staff_results) ):
            echo '<option value=""> -- no teachers assigned for this service -- </option>';
        else:
            $staff_ids = implode(',', array_column($staff_results, 'staff_id') );
            // query in bookly_staff table
            $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
            $staff_table_results = $wpdb->get_results(
                "SELECT id, wp_user_id, full_name, email FROM $bookly_staff_table WHERE id IN({$staff_ids}) AND wp_user_id IN($active_teachers)"
            );
            $wpdb->flush();

            if( empty( $staff_table_results ) ):
                echo '<option value=""> -- no teachers available found -- </option>';
            else:
                foreach ( $staff_table_results as $staff_table_result ):
                    $staff_id = (int) $staff_table_result->id;
                    $staff_wp_user_id = (int) $staff_table_result->wp_user_id;
                    //$staff_full_name = $staff_table_result->full_name;
                    $staff_wp_user_obj = get_user_by( 'id', $staff_wp_user_id );
                    $staff_email = $staff_wp_user_obj->data->user_email;
                    $staff_full_name = "U. " .  ucwords(str_replace("."," ",strstr($staff_email, '@', true)));
                    $staff_select_options .= '<option value="'. $staff_id .'"> ' . $staff_full_name . ' - ' . $staff_email .' </option>';
                endforeach;
            endif;

        endif;

        echo $staff_select_options;

    endif;

    wp_die();


}


/******
 * Ajax action to check time overlap for teacher_id
 ******/

add_action('wp_ajax_check_time_overlap', 'check_time_overlap');
add_action( 'wp_ajax_nopriv_check_time_overlap', 'check_time_overlap' );
function check_time_overlap(){

    global $wpdb;

    $bookly_teacher_ids= explode(',', $_POST['bookly_teacher_ids'] );
    $bookly_user_timezone = $_POST['bookly_user_timezone'];
    $bookly_start_time = $_POST['bookly_start_time'];
    $bookly_class_duration = $_POST['bookly_class_duration'];
    $bookly_effective_date = $_POST['bookly_effective_date'];
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_end_hours = $bookly_start_time + convertToHoursMins($bookly_class_duration)['hours'];
    $bookly_end_minutes = convertToHoursMins($bookly_class_duration)['minutes'];
    $bookly_start_minutes = '00';
    $string_start_date = strtotime( $bookly_effective_date . ' ' . $bookly_start_time . ':' . $bookly_start_minutes ); // mm/dd/yyyy H:m
    $booking_user_start_date = convertTimeZone( date ("Y-m-d H:i:s", $string_start_date), $bookly_user_timezone);
    $final_available_teachers = [];

    if( $bookly_end_hours == 24 ){
        $string_end_date = strtotime( $bookly_effective_date . ' 23:59:00'  ); // mm/dd/yyyy H:m
    } else {
        $string_end_date = strtotime( $bookly_effective_date . ' ' . $bookly_end_hours . ':' . $bookly_end_minutes ); // mm/dd/yyyy H:m
    }

    $booking_user_end_date = convertTimeZone( date ("Y-m-d H:i:s", $string_end_date), $bookly_user_timezone );
    $bookly_class_days = $_POST['bookly_class_days'];
    $start_after_break= [];
    $end_before_break = [];

    // search effective_day in week_days_list
    $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);
    foreach ($bookly_class_days as $bookly_class_day):
        if( $bookly_end_hours >= 24 ){
            $bookly_end_hours = 23;
            $bookly_end_minutes = 59;
        }
        $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
        if( $day_index > $effective_day_index ){
            $new_effective_start_date = getNextDayDate( $bookly_effective_date, $bookly_class_day );
            $string_start_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_start_time . ':' . $bookly_start_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_start_date = convertTimeZone( date ("Y-m-d H:i:s", $string_start_effective_date), $bookly_user_timezone);
            $string_end_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_end_hours . ':' . $bookly_end_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_end_date = convertTimeZone( date ("Y-m-d H:i:s", $string_end_effective_date), $bookly_user_timezone );

            $new_effective_dates[] = array(
                'start_user_datetime' => $new_booking_user_start_date,
                'end_user_datetime' => $new_booking_user_end_date
            );

        } else if( $day_index < $effective_day_index ){
            $day_index_difference = 7 - ( $effective_day_index - $day_index );

            $new_effective_start_date = getPrevDayDate( $bookly_effective_date, $day_index_difference );
            $string_start_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_start_time . ':' . $bookly_start_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_start_date = convertTimeZone( date ("Y-m-d H:i:s", $string_start_effective_date), $bookly_user_timezone);
            $string_end_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_end_hours . ':' . $bookly_end_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_end_date = convertTimeZone( date ("Y-m-d H:i:s", $string_end_effective_date), $bookly_user_timezone );

            $new_effective_dates[] = array(
                'start_user_datetime' => $new_booking_user_start_date,
                'end_user_datetime' => $new_booking_user_end_date
            );
        } else {
            $new_effective_start_date = date('Y-m-d', strtotime($bookly_effective_date));
            $string_start_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_start_time . ':' . $bookly_start_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_start_date = convertTimeZone( date ("Y-m-d H:i:s", $string_start_effective_date), $bookly_user_timezone);
            $string_end_effective_date = strtotime( $new_effective_start_date . ' ' . $bookly_end_hours . ':' . $bookly_end_minutes ); // mm/dd/yyyy H:m
            $new_booking_user_end_date = convertTimeZone( date ("Y-m-d H:i:s", $string_end_effective_date), $bookly_user_timezone );

            $new_effective_dates[] = array(
                'start_user_datetime' => $new_booking_user_start_date,
                'end_user_datetime' => $new_booking_user_end_date
            );

        }

    endforeach;


    /*******************    NEW LOOP all teachers  *************************/



    // *** Step 1
    // get teachers has holidays during appointment recurring days
    foreach( $new_effective_dates as $new_effective_start_date):
        // get list with recurring effective days for each class day
        $reccurringDates = getReccurringDates( date('m/d/Y', strtotime($new_effective_start_date['start_user_datetime'])) ,500, 'Y-m-d');
        $reccurringDates[] = date( 'Y-m-d', strtotime( $new_effective_start_date['start_user_datetime'] ) );

        // get teacher holiday ( off days )
        foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
            $teacher_holiday_status = [];

            $bookly_staff_holidays_table = $wpdb->prefix . 'bookly_holidays';
            $staff_holidays_results = $wpdb->get_results(
                "SELECT * FROM $bookly_staff_holidays_table WHERE staff_id = {$bookly_teacher_id}"
            );
            $wpdb->flush();

            foreach ( $staff_holidays_results as $staff_holidays_result ):
                $teacher_holiday = $staff_holidays_result->date;
                if( array_search($teacher_holiday, $reccurringDates) !== false ):
                    //echo 'teacher '. $bookly_teacher_id .' cant start in that day ' . $teacher_holiday . '<br>';
                    $teacher_holiday_status[] = true;
                else:
                    $teacher_holiday_status[] = false;
                endif;
            endforeach;

            if( array_search(true, $teacher_holiday_status) !== false ):
//                echo 'teacher: ' . $bookly_teacher_id . ' is not available. <br>';
                $teachers_has_holidays[] = $bookly_teacher_id;
            endif;

        endforeach;

    endforeach;

    if( empty($teachers_has_holidays) ):
        $teachers_has_holidays = [];
    endif;


    // *** Step 2
    // check teachers schedule excluding $teachers_has_holidays
    foreach ( $bookly_teacher_ids as $bookly_teacher_id ):

        // check if teacher has holidays skip it
        if( array_search($bookly_teacher_id, $teachers_has_holidays) !== false ):
            //echo 'teacher: ' . $bookly_teacher_id . ' exculded. <br>';
        else:
            //echo 'teacher: ' . $bookly_teacher_id . ' with us. <br>';

            // loop through new effective dates and check teacher overlap
            foreach( $new_effective_dates as $new_effective_start_date):
                $new_effective_start_day_name = strtolower( date('D', strtotime( $new_effective_start_date['start_user_datetime']) ) );
                $new_effective_start_time = date('H:i:s', strtotime( $new_effective_start_date['start_user_datetime'] ));
                $new_effective_end_time = date('H:i:s', strtotime( $new_effective_start_date['end_user_datetime'] ));
                // run check teacher overlap function
                if( checkTeacherIDSchedule($bookly_teacher_id, $new_effective_start_time, $new_effective_end_time, $new_effective_start_day_name) === true ):
                    // teacher available, check appointment overlap
                    //echo 'teacher: '.  $bookly_teacher_id . ' schedule is Okay on '. $new_effective_start_date['start_user_datetime'] .'<br>';
                    $teacher_schedule[$bookly_teacher_id][] = true;
                    $teacher_ids_schedule_ok[] = $bookly_teacher_id;
                else:
                    //echo 'teacher: '.  $bookly_teacher_id . ' schedule has no time on '. $new_effective_start_date['start_user_datetime'] .' <br>';
                    $teacher_schedule[$bookly_teacher_id][] = false;
                endif;
            endforeach;

        endif;

    endforeach;

    if( empty($teacher_ids_schedule_ok) ):
//        echo 'no teachers available';
        $check_time_status = json_encode(
            array(
                'success' => false,
                'message' => 'no teacher is available',
                'over_lap_days' => [],
                'final_available_teachers' => []
            )
        );
    else:

        //echo 'there are some teachers';
        // teacher has available time slot in his schedule
        // check teachers appointments overlap
        foreach ( $teacher_ids_schedule_ok as $teacher_id ):

            $teacher_schedule_status = $teacher_schedule[$teacher_id];

            if( in_array(false, $teacher_schedule_status) ):
                //echo 'Teacher: ' . $teacher_id . 'is NOT VALID <br>';
            else:
                //echo 'Teacher: ' . $teacher_id . 'is ok<br>';
                // teacher schedule is okay , let's search in his appointment and see if overlap found
                // get all appointments for staff_id ( teacher_id )
                // get all appointments for staff_id ( teacher_id )
                $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                $appointments_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$teacher_id}"
                );
                $wpdb->flush();

                if( !empty( $appointments_results ) ):
                    // loop through new effective days
                    foreach ( $new_effective_dates as $new_effective_date ):
                        // get recurring list foreach effective date
                        $reccurringStartDates = getReccurringDates( date('m/d/Y H:i:s', strtotime($new_effective_date['start_user_datetime'])) ,500, 'Y-m-d H:i:s');
                        $reccurringEndDates = getReccurringDates( date('m/d/Y H:i:s', strtotime($new_effective_date['end_user_datetime'])) ,500, 'Y-m-d H:i:s');

                        $reccurringStartDates[] = $new_effective_date['start_user_datetime'];
                        $reccurringEndDates[] = $new_effective_date['end_user_datetime'];


                        for( $i=1; $i<=count($reccurringStartDates); $i++ ):

                            $recurring_start_date = $reccurringStartDates[$i];
                            $recurring_end_date = $reccurringEndDates[$i];


                            // loop through teacher appointment
                            foreach ( $appointments_results as $appointments_result ):

                                $booking_stored_id = $appointments_result->id;
                                $booking_stored_start_date = $appointments_result->start_date;
                                $booking_stored_end_date = $appointments_result->end_date;

                                // get timezone for appointment id
                                $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
                                $appointments_customer_results = $wpdb->get_results(
                                    "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$booking_stored_id}"
                                );
                                $wpdb->flush();

                                $user_appointments_timezone = $appointments_customer_results[0]->time_zone;

                                if( empty($user_appointments_timezone) ):
                                    $check_time_status = json_encode(
                                        array(
                                            'success' => false,
                                            'message' => 'no timezone set in appointment(s)',
                                            'over_lap_days' => [],
                                            'final_available_teachers' => []
                                        )
                                    );
                                    echo $check_time_status;
                                    wp_die();
                                endif;

                                // convert teacher appointment start and end timezone
                                $booking_stored_start_date = convertTimeZone($booking_stored_start_date, $user_appointments_timezone);
                                $booking_stored_end_date = convertTimeZone($booking_stored_end_date, $user_appointments_timezone);

                                $overlap_minutes = overlapInMinutes($recurring_start_date, $recurring_end_date, $booking_stored_start_date, $booking_stored_end_date);


                                if( $overlap_minutes > 0 ):
                                    //echo 'Booking: ' . $booking_stored_id . ' start: ' . $booking_stored_start_date . ' - end: ' . $booking_stored_end_date . ' has overlap = ' . $overlap_minutes .'<br>';
                                    $overlap_days[] = $recurring_start_date;
                                    $check_time_status = json_encode(
                                        array(
                                            'success' => false,
                                            'message' => 'teacher has overlap in appointment(s)',
                                            'over_lap_days' => array_unique( $overlap_days ),
                                            'final_available_teachers' => []
                                        )
                                    );
                                else:
                                    // no overlap in appointments for teacher insert in final_available_teachers array
                                    $teachers_has_no_overlap_in_bookings[] = $teacher_id;
                                endif;

                            endforeach;



                        endfor;

                    endforeach;
                    foreach ($teachers_has_no_overlap_in_bookings as $key=>$value):
                        $final_available_teachers[] = (int) $value;
                    endforeach;
                    $final_available_teachers = array_merge(array_unique($final_available_teachers));
                else:
                    //echo 'teacher: '. $teacher_id. ' has no appointments.';
                    // teacher(s) has no appointment, proceed
                    $final_available_teachers[] = (int) $teacher_id;
                endif;
            endif;

        endforeach;



    endif;




    if( !empty($check_time_status) ):
        echo $check_time_status;
    else:
        $check_time_status = json_encode(
            array(
                'success' => true,
                'message' => 'some teachers are available',
                'over_lap_days' => [],
                'final_available_teachers' =>  array_unique( $final_available_teachers )
            )
        );
        echo $check_time_status;
    endif;

    wp_die();


}


/******
 * Ajax action to get child's from parent referral code
 ******/

add_action('wp_ajax_get_childs_for_referral_code', 'get_childs_for_referral_code');
add_action( 'wp_ajax_nopriv_get_childs_for_referral_code', 'get_childs_for_referral_code' );
function get_childs_for_referral_code(){
    global $wpdb;
    $parent_user_email = $_POST['parent_user_email'];
    if( isset($_POST['get_parent_only']) ):
        $get_parent_only = $_POST['get_parent_only'];
    else:
        $get_parent_only = '';
    endif;
    $user = get_user_by( 'email', $parent_user_email );
    if( empty($user) ):
        echo '<div class="alert text-center"> No Data Available </div>';
        wp_die();
    endif;
    $userId = $user->ID;
    // search to check if this is user in bookly customers table
    $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
    $bookly_customers_results = $wpdb->get_results(
        "SELECT * FROM $bookly_customers_table WHERE wp_user_id = '{$userId}'"
    );
    $wpdb->flush();
    if( empty($bookly_customers_results) ):
        $customer_error = '<span class="customer-error"> Customer Not Found. please contact administrator </span>';
        $add_disabled = 'disabled';
    else:
        $customer_error = '';
        $add_disabled = '';
    endif;

    $memb_ReferralCode_parent = get_user_meta($userId ,'account_code', true);
    $memb_ReferralCode = substr($memb_ReferralCode_parent, 5);
    if( empty($userId)):
        echo '<p class="alert"> User is not found in Muslimeto. </p>';
    else:
        $userObj = get_userdata($userId);
        $user_full_name = $userObj->data->display_name;

        $option_text = '<span class="option-text">' . $user_full_name . ' - ' . $parent_user_email . ' - ' . $userId  . ' </span> ';
        echo '<p class="single-child"><button class="button add-to-learners '. $add_disabled .'" data-user-id="'. $userId .'" '.$add_disabled.'> Add To Class</button>' . $option_text . ' </p>' . $customer_error;


        //echo 'is parent';
        // get all users has meta starts with 'chld-'.$referral-code
        // Query for users based on the meta data



        if(substr($memb_ReferralCode_parent, 0, 4) === "prnt"): // is parent
            $user_query = new WP_User_Query(
                array(
                    'meta_key'	  =>	'account_code',
                    'meta_value'	=>	'chld-'.$memb_ReferralCode
                )
            );

            // Get the results from the query, returning the first user
            $childs_users = $user_query->get_results();

            if( !empty($childs_users) ):
                $childs_users = array_column($childs_users,'id');
                $childs_users = array_unique($childs_users);
            endif;

            if( empty($get_parent_only) && $get_parent_only !== 'parent_only' ):

                if( !empty($childs_users) ):
                    foreach ( $childs_users as $childs_user_id ):
                        $childs_user = get_user_by('id', $childs_user_id);
                        $child_user_id = $childs_user->data->ID;
                        // search to check if this is user in bookly customers table
                        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
                        $bookly_customers_results = $wpdb->get_results(
                            "SELECT * FROM $bookly_customers_table WHERE wp_user_id = '{$child_user_id}'"
                        );
                        $wpdb->flush();
                        if( empty($bookly_customers_results) ):
                            $customer_error = '<span class="customer-error"> Customer Not Found. please contact administrator </span>';
                            $add_disabled = 'disabled';
                        else:
                            $customer_error = '';
                            $add_disabled = '';
                        endif;

                        $child_user_email = $childs_user->data->user_email;
                        $child_user_name = $childs_user->data->display_name;
                        $option_text = '<span class="option-text"> ' . $child_user_name . ' - ' . $child_user_email . ' - '  . $child_user_id . '</span>';
                        echo '<p class="single-child">
                            <button class="button add-to-learners '.$add_disabled.'" data-user-id="'. $child_user_id .'" '.$add_disabled.'>Add To Class</button>'
                            . $option_text .
                            ' </p>' . $customer_error;
                    endforeach;
                else:
                    echo '<p class="alert text-center"> No Children found for this parent email. </p>';
                endif;

            endif;

        else:
            echo '<p class="alert text-center"> User is not a Parent account. </p>';
        endif;


    endif;

    wp_die();
}

/******
 * Ajax action to check time overlap for single program for given teachers_ids
 ******/

add_action('wp_ajax_check_time_overlap_single_program', 'check_time_overlap_single_program');
add_action( 'wp_ajax_nopriv_check_time_overlap_single_program', 'check_time_overlap_single_program' );
function check_time_overlap_single_program(){

    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';

    $bookly_teacher_ids= explode(',', $_POST['bookly_teacher_ids'] );
    $bookly_user_timezone = $_POST['bookly_user_timezone'];
    $bookly_start_time = $_POST['bookly_start_hours'];
    $bookly_start_minutes = $_POST['bookly_start_minutes'];
    $bookly_class_duration = $_POST['bookly_class_duration'];
    $bookly_effective_date = $_POST['bookly_effective_date'];
    $bookly_service_id = $_POST['bookly_service_id'];
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_end_hours = $bookly_start_time + convertToHoursMins($bookly_class_duration)['hours'];
    $bookly_end_minutes = $bookly_start_minutes + convertToHoursMins($bookly_class_duration)['minutes'];

    // if bookly end minutes > 60, add +1 hour to bookly end hours
    if( $bookly_end_minutes >= 60 ):
        $bookly_end_hours++;
        $bookly_end_minutes = $bookly_end_minutes - 60;
    endif;

    $bookly_class_days = $_POST['bookly_class_days'];
    // get week number and year number to generate effective dates for each row
    $effective_week_number = date("W", strtotime($bookly_effective_date));
    $effective_year_number = date('Y', strtotime($bookly_effective_date));


    // get recurring days for each schedule
    foreach ( $bookly_class_days as $bookly_class_day ):

        // get each day date
        $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
        $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
        $gendate = new DateTime();
        $gendate->setISODate($effective_year_number,$effective_week_number,$day_index); //year , week num , day
        $row_effective_start_dates[] =  $gendate->format('Y-m-d '. $bookly_start_time . ':' . $bookly_start_minutes . ':00');
        $row_effective_end_dates[] =  $gendate->format('Y-m-d '. $bookly_end_hours . ':' . $bookly_end_minutes . ':00');

    endforeach;



    foreach ( $row_effective_start_dates as $key=>$start_datetime ):

        // get end date afetr 60 days from start date
        $start_datetime = date('m/d/Y H:i:s', strtotime($start_datetime));
        $end_datetime = date('m/d/Y H:i:s', strtotime($row_effective_end_dates[$key]));
        $date = new DateTime($start_datetime);
        $date->add(new DateInterval('P60D'));
        $stop_reccurring_start_datetime = $date->format('m/d/Y H:i:s');

        $date = new DateTime($end_datetime);
        $date->add(new DateInterval('P60D'));
        $stop_reccurring_end_datetime = $date->format('m/d/Y H:i:s');

        // get recurring days for start and end
        $recurringDatesStartArray[] = getReccurringDatesUntilinTimezone(  $start_datetime, $stop_reccurring_start_datetime,'Y-m-d H:i:s', $bookly_user_timezone);
        $recurringDatesEndArray[] = getReccurringDatesUntilinTimezone( $end_datetime ,$stop_reccurring_end_datetime, 'Y-m-d H:i:s', $bookly_user_timezone);

        array_push($recurringDatesStartArray[$key], convertTimeZoneToUTC( date('Y-m-d H:i:s', strtotime($start_datetime)), $bookly_user_timezone));
        array_push($recurringDatesEndArray[$key], convertTimeZoneToUTC( $row_effective_end_dates[$key], $bookly_user_timezone ));

//        // get recurring days for start and end
//        $recurringDatesStartArray[] = getReccurringDates( date('m/d/Y H:i:s', strtotime($start_datetime)) ,500, 'Y-m-d H:i:s' ,$bookly_user_timezone);
//        $recurringDatesEndArray[] = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_end_dates[$key])) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);


//        array_push($recurringDatesStartArray[$key], convertTimeZoneToUTC( $start_datetime, $bookly_user_timezone ) );
//        array_push($recurringDatesEndArray[$key], convertTimeZoneToUTC( $row_effective_end_dates[$key], $bookly_user_timezone ) );

    endforeach;

    // skip previous dates from recurring start for booking row 1 only
    foreach ( $recurringDatesStartArray as $key=>$rowReccurringStartDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringStartDate as $i=>$rowReccurringStartDatecheck ):
            if( strtotime($bookly_effective_date) > strtotime($rowReccurringStartDatecheck) ):
                unset( $recurringDatesStartArray[$key][$i] );
            endif;
        endforeach;

    endforeach;



    // skip previous dates from recurring end for booking row 1 only
    foreach ( $recurringDatesEndArray as $key=>$rowReccurringEndDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringEndDate as $i=>$rowReccurringEndDatecheck ):
            if( strtotime($bookly_effective_date) > strtotime($rowReccurringEndDatecheck) ):
                unset( $recurringDatesEndArray[$key][$i] );
            endif;
        endforeach;
    endforeach;



    foreach ( $recurringDatesStartArray as $key=>$start_days ):

        $end_days = $recurringDatesEndArray[$key];

        //echo 'day: ' . $key .'<br>';
        foreach ( $start_days as $i=>$start_day ):
            //echo $start_day . ' --- ' . $end_days[$i].'<br>';
            // check staff status
            foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
                $bookly_teacher_id = (int) $bookly_teacher_id;
                $staff_wp_user_id = getStaffwp_user_id($bookly_teacher_id);
                $staff_timezone = getUserTimezone($staff_wp_user_id);
//                echo 'teacher: ' . $bookly_teacher_id . '<br>UTC user start ' . $start_day . ' -  user end ' . $end_days[$i];
//                pre_dump( checkAppointmentErrors($start_day, $end_days[$i], $bookly_teacher_id, $bookly_service_id, '', '', []) );
                $check_status = checkAppointmentErrors($start_day, $end_days[$i], $bookly_teacher_id, $bookly_service_id, '', '', []) ;
                //$staff_appointment_overlap_status[$bookly_teacher_id][$start_day] = $check_status['staff_appointment_overlap_status'];
                $interval_not_in_staff_schedule[$bookly_teacher_id][$start_day] = $check_status['interval_not_in_staff_schedule'];
                // check if overlap in schedule is true, delete whole status array for this teacher, do not check his appaointments
                if( $check_status['interval_not_in_staff_schedule'] === true ):
//                    unset($interval_not_in_staff_schedule[$bookly_teacher_id]);

                    $date_has_error = convertTimezone1ToTimezone2 ( $start_day, 'UTC', $staff_timezone );
                    $date_has_error = date('Y-m-d h:i a', strtotime($date_has_error));
                    echo json_encode(
                        array(
                            'success' => true,
                            'message' => 'no teachers are available',
                            'final_available_teachers' =>  [],
                            'staff_schedule_error' => "Teacher schedule is not available on: <br> $date_has_error $staff_timezone <br> please check calendar",
                            'staff_appointments_error' => '',
                            'overlap_appointments_ids' => '',
                        )
                    );
                    wp_die();
                endif;

            endforeach;
        endforeach;
    endforeach;


    // set bookly_teacher_ids to new teachers has no schedule overlap
    unset($bookly_teacher_ids);
    foreach ( $interval_not_in_staff_schedule as $teacher_id=>$interval_not_in_staff_result ):
        $bookly_teacher_ids[] = $teacher_id;
    endforeach;


    // check appointments overlap with another function
    foreach ( $bookly_teacher_ids as $teacher_key=>$bookly_teacher_id ):

        $appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$bookly_teacher_id}"
        );
        $wpdb->flush();
        if( !empty($appointments_results) ):
            foreach ( $appointments_results as $appointments_result ):

                $booking_stored_id = $appointments_result->id;

                // get timezone for appointment id

                $appointments_customer_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$booking_stored_id}"
                );
                $wpdb->flush();


                $user_appointments_timezone = $appointments_customer_results[0]->time_zone;


                foreach ( $recurringDatesStartArray as $key=>$start_days ):
                    $end_days = $recurringDatesEndArray[$key];
                    foreach ( $start_days as $i=>$start_day ):

                        // do not convert stored as they stored in UTC
                        $booking_stored_start_date =  $appointments_result->start_date;
                        $booking_stored_end_date =  $appointments_result->end_date;

                        //echo $start_day. ' --- '. $end_days[$i] . ' / stored: ' . $booking_stored_start_date . ' *** ' . $booking_stored_end_date . ' --- ' . overlapInMinutes($start_day, $end_days[$i], $booking_stored_start_date, $booking_stored_end_date) . '<br>';

//                        $booking_stored_start_date = convertTimeZoneDaylight( $appointments_result->start_date, 'America/New_York' );
//                        $booking_stored_end_date = convertTimeZoneDaylight( $appointments_result->end_date, 'America/New_York' );
                        // do not convert teacher appointment start and end timezone as it stored in Server timezone

                        if( overlapInMinutes($start_day, $end_days[$i], $booking_stored_start_date, $booking_stored_end_date) > 0 ):
                            $staff_appointment_overlap_status_2[$bookly_teacher_id][$start_day][$booking_stored_id] = true;
                            $date_has_error = convertTimezone1ToTimezone2 ( $start_day, 'UTC', $staff_timezone );
                            $overlap_data[] = date('Y-m-d h:i a', strtotime($date_has_error));
                            //unset($bookly_teacher_ids[$teacher_key]);
                        endif;
                    endforeach;
                endforeach;

            endforeach;
        endif;
    endforeach;


    $overlap_appointments_ids = [];

    foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
        if( !empty($staff_appointment_overlap_status_2) ):

            $appointments_overlap_results = $staff_appointment_overlap_status_2[$bookly_teacher_id];


            foreach ( $appointments_overlap_results as $day=>$appointments_overlap_result ):

                foreach ( $appointments_overlap_result as $stored_booking_id=>$value ):
                    if( $value !== true ):
                        $staff_appointment_overlap_status[$bookly_teacher_id][] = false; // teacher has no overlap
                    else:
                        $staff_appointment_overlap_status[$bookly_teacher_id][] = true; // teacher has overlap
                        $overlap_appointments_ids[] = $stored_booking_id;
                    endif;
                endforeach;
            endforeach;
        else:
            $staff_appointment_overlap_status[$bookly_teacher_id][] = false;
        endif;

    endforeach;


    // filter available teachers based on schedule
    foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
        if (array_search(true, $interval_not_in_staff_schedule[$bookly_teacher_id]) !== false) {
            // then I found error in staff schedule
            //echo 'teacher: ' . $bookly_teacher_id . ' is NOT ok <br>';
        } else {
            // staff schedule is ok
            //echo 'teacher: ' . $bookly_teacher_id . ' is OKAY <br>';
            $teachers_after_schedule_check[] = (int) $bookly_teacher_id;
        }
    endforeach;

    $teachers_after_schedule_check = $bookly_teacher_ids;

    if( !empty($teachers_after_schedule_check) ):
        // filter available teachers based on appointments overlap
        foreach ( $teachers_after_schedule_check as $bookly_teacher_id ):
            $staff_wp_user_id = getStaffwp_user_id($bookly_teacher_id);
            $staff_timezone = getUserTimezone($staff_wp_user_id);
            if (array_search(true, $staff_appointment_overlap_status[$bookly_teacher_id]) !== false) {
                // then I found error in staff appointments
                //echo 'teacher: ' . $bookly_teacher_id . ' is NOT ok <br>';
                $staff_appointments_error = "Teacher has overlap in appointment on: <br> $overlap_data[0] $staff_timezone <br> please check calendar";

            } else {
                // staff appointments is ok
                //echo 'teacher: ' . $bookly_teacher_id . ' is OKAY <br>';
                $teachers_after_appointments_check[] = (int) $bookly_teacher_id;
                $staff_appointments_error = null;
            }
        endforeach;
        $staff_schedule_error = null;
    else:
        $staff_schedule_error = 'Teacher schedule is not available.';
    endif;



    if( !empty($teachers_after_appointments_check) ):
        $check_time_status = json_encode(
            array(
                'success' => true,
                'message' => 'some teachers are available',
                'final_available_teachers' =>  array_merge( array_unique( $teachers_after_appointments_check ) )
            )
        );
    else:
        $check_time_status = json_encode(
            array(
                'success' => true,
                'message' => 'no teachers are available',
                'final_available_teachers' =>  [],
                'staff_schedule_error' => $staff_schedule_error,
                'staff_appointments_error' => $staff_appointments_error,
                'overlap_appointments_ids' => $overlap_appointments_ids
            )
        );
    endif;
    echo $check_time_status;

    wp_die();


}

/******
 * Ajax action to check time overlap for single program for given teachers_ids ( all techers check )
 ******/

add_action('wp_ajax_check_time_overlap_single_program_all_teachers', 'check_time_overlap_single_program_all_teachers');
add_action( 'wp_ajax_nopriv_check_time_overlap_single_program_all_teachers', 'check_time_overlap_single_program_all_teachers' );
function check_time_overlap_single_program_all_teachers(){

    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';

    $bookly_teacher_ids = explode(',', $_POST['bookly_teacher_ids'] );
    $bookly_user_timezone = $_POST['bookly_user_timezone'];
    $bookly_start_time = $_POST['bookly_start_hours'];
    $bookly_start_minutes = $_POST['bookly_start_minutes'];
    $bookly_class_duration = $_POST['bookly_class_duration'];
    $bookly_effective_date = $_POST['bookly_effective_date'];
    $bookly_service_id = $_POST['bookly_service_id'];
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_end_hours = $bookly_start_time + convertToHoursMins($bookly_class_duration)['hours'];
    $bookly_end_minutes = $bookly_start_minutes + convertToHoursMins($bookly_class_duration)['minutes'];

    if(
        empty($bookly_teacher_ids) ||
        empty($bookly_user_timezone) ||
        empty($bookly_start_time) ||
        empty($bookly_class_duration) ||
        empty($bookly_effective_date)

    ):
        echo 'Empty fileds, please check again.';
        wp_die();
    endif;


    // if bookly end minutes > 60, add +1 hour to bookly end hours
    if( $bookly_end_minutes >= 60 ):
        $bookly_end_hours++;
        $bookly_end_minutes = $bookly_end_minutes - 60;
    endif;

    $bookly_class_days = $_POST['bookly_class_days'];
    // get week number and year number to generate effective dates for each row
    $effective_week_number = date("W", strtotime($bookly_effective_date));
    $effective_year_number = date('Y', strtotime($bookly_effective_date));

    // get recurring days for each schedule
    foreach ( $bookly_class_days as $bookly_class_day ):

        // get each day date
        $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
        $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
        $gendate = new DateTime();
        $gendate->setISODate($effective_year_number,$effective_week_number,$week_day_index); //year , week num , day
        $row_effective_start_dates[] = $gendate->format('Y-m-d '. $bookly_start_time . ':' . $bookly_start_minutes . ':00');
        $row_effective_end_dates[] = $gendate->format('Y-m-d '. $bookly_end_hours . ':' . $bookly_end_minutes . ':00');

    endforeach;


    foreach ( $row_effective_start_dates as $key=>$start_datetime ):
        // get end date afetr 30 days from start date
        $start_datetime = date('m/d/Y H:i:s', strtotime($start_datetime));
        $end_datetime = date('m/d/Y H:i:s', strtotime($row_effective_end_dates[$key]));
        $date = new DateTime($start_datetime);
        $date->add(new DateInterval('P30D'));
        $stop_reccurring_start_datetime = $date->format('m/d/Y H:i:s');

        $date = new DateTime($end_datetime);
        $date->add(new DateInterval('P30D'));
        $stop_reccurring_end_datetime = $date->format('m/d/Y H:i:s');



        // get recurring days for start and end
        $recurringDatesStartArray[] = getReccurringDatesUntilinTimezone(  $start_datetime, $stop_reccurring_start_datetime,'Y-m-d H:i:s', $bookly_user_timezone);
        $recurringDatesEndArray[] = getReccurringDatesUntilinTimezone( $end_datetime ,$stop_reccurring_end_datetime, 'Y-m-d H:i:s', $bookly_user_timezone);

        array_push($recurringDatesStartArray[$key], convertTimeZoneToUTC( date('Y-m-d H:i:s', strtotime($start_datetime)), $bookly_user_timezone));
        array_push($recurringDatesEndArray[$key], convertTimeZoneToUTC( $row_effective_end_dates[$key], $bookly_user_timezone ));


    endforeach;




    foreach ( $recurringDatesStartArray as $key=>$start_days ):

        $end_days = $recurringDatesEndArray[$key];

        //echo 'day: ' . $key .'<br>';
        foreach ( $start_days as $i=>$start_day ):
            //echo $start_day . ' --- ' . $end_days[$i].'<br>';
            // check staff status
            foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
                //echo 'teacher: ' . $bookly_teacher_id . '<br> start ' . $start_day . ' end ' . $end_days[$i];
                //pre_dump( checkAppointmentErrors($start_day, $end_days[$i], $bookly_teacher_id, $bookly_service_id, '', '', []) );
                $check_status = checkAppointmentErrors($start_day, $end_days[$i], $bookly_teacher_id, $bookly_service_id, '', '', []) ;
                $staff_appointment_overlap_status[$bookly_teacher_id][$start_day] = $check_status['staff_appointment_overlap_status'];
                $interval_not_in_staff_schedule[$bookly_teacher_id][$start_day] = $check_status['interval_not_in_staff_schedule'];
                $check_schedule[$bookly_teacher_id][] = $check_status['interval_not_in_staff_schedule'];
                // check if overlap in schedule is true, delete whole status array for this teacher, do not check his appaointments
//                if( $check_status['interval_not_in_staff_schedule'] === true ):
//                    unset($interval_not_in_staff_schedule[$bookly_teacher_id]);
//                endif;

            endforeach;
        endforeach;
    endforeach;



    // set bookly_teacher_ids to new teachers has no schedule overlap
    unset($bookly_teacher_ids);
    foreach ( $check_schedule as $teacher_id=>$check_single_schedule ):
        if( ! in_array(true, $check_single_schedule) ):
            $bookly_teacher_ids[] = $teacher_id;
        endif;
    endforeach;


    // check appointments overlap with another function
    foreach ( $bookly_teacher_ids as $teacher_key=>$bookly_teacher_id ):

        $appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$bookly_teacher_id}"
        );
        $wpdb->flush();

        if( !empty($appointments_results) ):
            foreach ( $appointments_results as $appointments_result ):

                $booking_stored_id = $appointments_result->id;

                // get timezone for appointment id
                $appointments_customer_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$booking_stored_id}"
                );
                $wpdb->flush();

                $user_appointments_timezone = $appointments_customer_results[0]->time_zone;

                foreach ( $recurringDatesStartArray as $key=>$start_days ):
                    $end_days = $recurringDatesEndArray[$key];
                    foreach ( $start_days as $i=>$start_day ):

                        $start_day = date('Y-m-d H:i:s', strtotime($start_day));

                        // do not convert stored as they stored in UTC
                        $booking_stored_start_date = $appointments_result->start_date;
                        $booking_stored_end_date = $appointments_result->end_date;
                        // do not convert teacher appointment start and end timezone as it stored in Server timezone

//                        $test = overlapInMinutes($start_day, $end_days[$i], $booking_stored_start_date, $booking_stored_end_date);
//                        if( $test > 0 ){
//                            echo $booking_stored_start_date . ' - ' . $booking_stored_end_date .' * ' . $start_day . ' - ' . $end_days[$i] . ' ---- '. $test .'<br>';
//                        }

                        if( overlapInMinutes($start_day, $end_days[$i], $booking_stored_start_date, $booking_stored_end_date) > 0 ):
                            $staff_appointment_overlap_status_2[$bookly_teacher_id][$start_day][$booking_stored_id] = true;
                            //unset($bookly_teacher_ids[$teacher_key]);
                        endif;

                    endforeach;
                endforeach;

            endforeach;
        endif;
    endforeach;


    $overlap_appointments_ids = [];


    foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
        if( !empty($staff_appointment_overlap_status_2) ):

            $appointments_overlap_results = $staff_appointment_overlap_status_2[$bookly_teacher_id];

            foreach ( $appointments_overlap_results as $day=>$appointments_overlap_result ):

                foreach ( $appointments_overlap_result as $stored_booking_id=>$value ):
                    if( $value !== true ):
                        $staff_appointment_overlap_status[$bookly_teacher_id][] = false; // teacher has no overlap
                    else:
                        $staff_appointment_overlap_status[$bookly_teacher_id][] = true; // teacher has overlap
                        $overlap_appointments_ids[] = $stored_booking_id;
                    endif;
                endforeach;
            endforeach;
        else:
            $staff_appointment_overlap_status[$bookly_teacher_id][] = false;
        endif;

    endforeach;

    // filter available teachers based on schedule
    foreach ( $bookly_teacher_ids as $bookly_teacher_id ):
        if (array_search(true, $interval_not_in_staff_schedule[$bookly_teacher_id]) !== false) {
            // then I found error in staff schedule
            //echo 'teacher: ' . $bookly_teacher_id . ' is NOT ok <br>';
        } else {
            // staff schedule is ok
            //echo 'teacher: ' . $bookly_teacher_id . ' is OKAY <br>';
            $teachers_after_schedule_check[] = (int) $bookly_teacher_id;
        }
    endforeach;



    if( !empty($teachers_after_schedule_check) ):
        // filter available teachers based on appointments overlap
        foreach ( $teachers_after_schedule_check as $bookly_teacher_id ):
            if (array_search(true, $staff_appointment_overlap_status[$bookly_teacher_id]) !== false) {
                // then I found error in staff appointments
                //echo 'teacher: ' . $bookly_teacher_id . ' is NOT ok <br>';
                $staff_appointments_error = 'Teacher has overlap in some appointments.';
            } else {
                // staff appointments is ok
                //echo 'teacher: ' . $bookly_teacher_id . ' is OKAY <br>';
                $teachers_after_appointments_check[] = (int) $bookly_teacher_id;
                $staff_appointments_error = null;
            }
        endforeach;
        $staff_schedule_error = null;
    else:
        $staff_schedule_error = 'Teacher schedule is not available.';
    endif;


    if( !empty($teachers_after_appointments_check) ):
        $check_time_status = json_encode(
            array(
                'success' => true,
                'message' => 'some teachers are available',
                'final_available_teachers' =>  array_merge( array_unique( $teachers_after_appointments_check ) )
            )
        );
    else:
        $check_time_status = json_encode(
            array(
                'success' => true,
                'message' => 'no teachers are available',
                'final_available_teachers' =>  [],
                'staff_schedule_error' => $staff_schedule_error,
                'staff_appointments_error' => $staff_appointments_error,
                'overlap_appointments_ids' => $overlap_appointments_ids
            )
        );
    endif;
    echo $check_time_status;

    wp_die();


}


/******
 * Ajax action to Submit Add program Data and create social groups with learndash groups
 ******/

add_action('wp_ajax_submit_single_program_booking_form', 'submit_single_program_booking_form');
add_action( 'wp_ajax_nopriv_submit_single_program_booking_form', 'submit_single_program_booking_form' );
function submit_single_program_booking_form(){

    global $wpdb;

    $program_type = $_POST['program_type'];
    $program_status = $_POST['program_status'];
    $bb_group_id = $_POST['bb_group_id'];
    $zoom_meeting_id = $_POST['zoom_meeting_id'];
    $bookly_teacher_id= $_POST['bookly_teacher_id'];
    $bookly_student_ids = $_POST['bookly_student_id'];
    $bookly_user_timezone = $_POST['bookly_user_timezone'];
    $bookly_start_hours = $_POST['bookly_start_hours'];
    $bookly_start_minutes = $_POST['bookly_start_minutes'];
    $bookly_class_duration = $_POST['bookly_class_duration'];
    $bookly_effective_date = $_POST['bookly_effective_date'];
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_class_days = $_POST['bookly_class_days'];
    $bookly_student_name = $_POST['bookly_student_name'];
    $bookly_service_name = $_POST['bookly_service_name'];
    $bookly_service_id = $_POST['bookly_service_id'];
    $bookly_category_id = $_POST['bookly_category_id'];
//    $bookly_user_timezone_offset = getNowTimeZoneOffset($bookly_user_timezone);
    $bookly_user_timezone_offset = 0;
    $catch_error = '';
    $group_family = $_POST['group_family'];

    if( !empty($_POST['link_to_group_option']) ):
        $link_to_group_option = $_POST['link_to_group_option'];
    else:
        $link_to_group_option = false;
    endif;


    // calculate program total hours
    $program_future_total_mins = 0;
    foreach ( $bookly_class_days as $key=>$bookly_class_day ):
        $program_future_total_mins += $bookly_class_duration[$key] * count($bookly_class_day) * 4;
    endforeach;
    if( $program_future_total_mins > 0 ):
        $program_future_total_hrs = $program_future_total_mins / 60;
    else:
        $program_future_total_hrs = 0;
    endif;


    if( empty($program_type) ||
        empty($program_status) ||
        empty($bookly_teacher_id) ||
        empty($bookly_student_ids) ||
        empty($bookly_user_timezone) ||
        empty($bookly_start_hours) ||
        empty($bookly_start_minutes) ||
        empty($bookly_class_duration) ||
        empty($bookly_effective_date) ||
        empty($bookly_class_days) ||
        empty($bookly_service_id) ||
        empty($bookly_category_id)
    ):
        echo 'empty-fields';
        wp_die();
    endif;


    // get customer id from bookly_customers table for wp_user_id
    if( !is_array($bookly_student_ids) ):
        $bookly_student_ids = array($bookly_student_ids);
    endif;
    foreach ( $bookly_student_ids as $bookly_student_id ):
        $bookly_student_id = (int) $bookly_student_id;
        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
        $bookly_customers = $wpdb->get_results(
            "SELECT id FROM $bookly_customers_table WHERE wp_user_id = {$bookly_student_id}"
        );
        $wpdb->flush();
        $bookly_customer_ids[] = (int) $bookly_customers[0]->id;

        // get parents ids
        $parent_ids[] = getParentID($bookly_student_id);
    endforeach;


    // get week number and year number to generate effective dates for each row
    $effective_week_number = date("W", strtotime($bookly_effective_date));
    $effective_year_number = date('Y', strtotime($bookly_effective_date));
    $effective_month_number = (int) date('m', strtotime($bookly_effective_date));

    // fix wrong week number if in day in last week of previous year
    if( $effective_month_number === 1 && (int) $effective_week_number > 50 ):
        $effective_week_number = 1;
    endif;

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    // calculate units count from class duration
    foreach ( $bookly_class_duration as $duration ):
        $units[] = ( (int) $duration ) / 15 ;
    endforeach;


    for ( $i=0; $i<count($bookly_start_hours); $i++):
        $bookly_end_minutes[] = convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['minutes'];
        $bookly_end_hours[] = (int) $bookly_start_hours[$i] + convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['hours'];
        $string_start_date[] = strtotime( $bookly_effective_date . ' ' . (int) $bookly_start_hours[$i] . ':' . (int) $bookly_start_minutes[$i] ); // mm/dd/yyyy H:m
    endfor;

    foreach ( $string_start_date as $start_date ):
        $booking_user_start_date[] =  date ("Y-m-d H:i:s", $start_date);
    endforeach;



    for( $i=0; $i<count($bookly_end_hours); $i++ ):
        if( $bookly_end_hours[$i] == 24 ){
            $string_end_date = strtotime( $bookly_effective_date . ' 23:59:00'  ); // mm/dd/yyyy H:m
        } else {
            $string_end_date = strtotime( $bookly_effective_date . ' ' . $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] ); // mm/dd/yyyy H:m
        }
        $booking_user_end_date[] = date ("Y-m-d H:i:s", $string_end_date);
    endfor;


    for ( $i=0; $i<count($bookly_class_days); $i++ ):
        $class_day_name[] = implode( ', ', $bookly_class_days[$i]);
    endfor;

    for( $i=0; $i<count($class_day_name); $i++ ):

        $schedule_name[] = $class_day_name[$i] . ' - ' . date('h:i A', strtotime($booking_user_start_date[$i]) );

    endfor;



    // get new effective start and end dates for each booking row
    $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);
    for( $i=0; $i<count($bookly_class_days); $i++ ):
        if( $bookly_end_hours[$i] >= 24 ){
            $bookly_end_hours[$i] = 23;
            $bookly_end_minutes[$i] = 59;
        }
        foreach( $bookly_class_days[$i] as $bookly_class_day ):
            $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
            $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
            $gendate = new DateTime();
            $gendate->setISODate($effective_year_number,$effective_week_number,$day_index); //year , week num , day
            $row_effective_start_dates[$i][] = $gendate->format('Y-m-d '. $bookly_start_hours[$i] . ':' . $bookly_start_minutes[$i] . ':00');
            $row_effective_end_dates[$i][] =  $gendate->format('Y-m-d '. $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] . ':00');

        endforeach;
    endfor;



    // check if effective date has days before it in schedule, and get previous dates to skip from recurring
    $bookly_effective_day_index =  array_search($bookly_effective_day, WEEK_DAYS_INDEX);

    foreach ( $row_effective_start_dates[0] as $row_effective_start_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_start_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_start_previous_days[] = $row_effective_start_date;
        }
    endforeach;

    foreach ( $row_effective_end_dates[0] as $row_effective_end_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_end_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_end_previous_days[] = $row_effective_end_date;
        }
    endforeach;

    // get recurring days for each row_start_date
    // get current month => add 2 months => $calculated_end_date = end of following month => $end_date_to_regenerate = $calculated_end_date
    // 28/8/2022 => 28/10/2022 => $calculated_end_date = 31/10/2022
    $current_date_to_compare = date('Y-m', strtotime($created_at)) . '-01';
    $calculated_end_date = date('Y-m-d', strtotime($current_date_to_compare . ' +3 months - 1 day'));

    for( $i=0; $i<count($row_effective_start_dates); $i++ ):

        foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
            // get reccurring dates for each start and end date
//            $recurringDatesStartArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_start_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesStartArray = getReccurringDatesUntilinTimezone(  date('m/d/Y H:i:s', strtotime($row_effective_start_date)), $calculated_end_date,'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesStartArray[] = convertTimeZoneToUTC( $row_effective_start_date , $bookly_user_timezone);
            $rowReccurringStartDates[$i][] = $recurringDatesStartArray;
        endforeach;

        foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
            // get reccurring dates for each start and end date
//            $recurringDatesEndArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_end_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesEndArray = getReccurringDatesUntilinTimezone(  date('m/d/Y H:i:s', strtotime($row_effective_end_date)), $calculated_end_date,'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesEndArray[] = convertTimeZoneToUTC( $row_effective_end_date , $bookly_user_timezone);
            $rowReccurringEndDates[$i][] = $recurringDatesEndArray;
        endforeach;

    endfor;


    // skip previous dates from recurring start for booking row 1 only
    foreach ( $rowReccurringStartDates[0] as $key=>$rowReccurringStartDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringStartDate as $i=>$rowReccurringStartDatecheck ):
            if( strtotime($bookly_effective_date) > strtotime($rowReccurringStartDatecheck) ):
                unset( $rowReccurringStartDates[0][$key][$i] );
            endif;
        endforeach;

    endforeach;



    // skip previous dates from recurring end for booking row 1 only
    foreach ( $rowReccurringEndDates[0] as $key=>$rowReccurringEndDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringEndDate as $i=>$rowReccurringEndDatecheck ):
            if( strtotime($bookly_effective_date) > strtotime($rowReccurringEndDatecheck) ):
                unset( $rowReccurringEndDates[0][$key][$i] );
            endif;
        endforeach;
    endforeach;


    foreach ( $row_effective_start_dates as $key=>$row_effective_start_date ):
        foreach ( $row_effective_start_date as $date ):
            $input_date[$key][] = date( 'Y-m-d', strtotime($date));
        endforeach;
    endforeach;

    foreach ( $bookly_class_days as $key=>$bookly_class_day ):
        foreach ( $bookly_class_day as $day ):
            $day_index = "input_1." . array_search($day, WEEK_DAYS_INDEX);
            $schedule_days_input[$key][$day_index] = $day;
        endforeach;
    endforeach;


    // create one social group and learan dash group
    $user_ID = get_current_user_id();
    $organizer_user = get_user_by( 'email', 'academy@muslimeto.com' );
    if( !empty($organizer_user) ):
        $organizer_user_id = $organizer_user->ID;
    else:
        $organizer_user_id = 0;
    endif;
    $group_name = $bookly_service_name;
    $group_description = $bookly_service_name . ' - ' . ' ( ' . implode(' * ', $schedule_name) . ' ) ' ;


    $bb_group_args = array(
        'group_id' => 0,
        'creator_id' => $organizer_user_id,
        'name' => $group_name,
        'description' => $group_description,
        'slug' => 'test-from-back',
        'status' => 'private',
    );

    if( empty($bb_group_id) ):
        $bb_group_id = groups_create_group( $bb_group_args );
        if( !empty($bb_group_id) ):
            // set group type as Classroom (class)
            if( ! bp_groups_set_group_type( $bb_group_id, 'class', false ) ):
                $catch_error .= 'Error Set BB group Type as Classroom <br>';
            endif;

            // update bb group name and slug
            $bb_groups_table = $wpdb->prefix . 'bp_groups';
            $group_name = $group_name . ' - CID ' . $bb_group_id;
            if( $wpdb->update($bb_groups_table, array('id'=>$bb_group_id, 'name'=>$group_name), array('id'=>$bb_group_id)) !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating BB group name. <br>' .$wpdb->print_error();
            endif;

            $group_slug = 'cid-' . $bb_group_id;
            if( $wpdb->update($bb_groups_table, array('id'=>$bb_group_id, 'slug'=>$group_slug), array('id'=>$bb_group_id)) !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating BB group slug. <br>' .$wpdb->print_error();
            endif;

            // update Learn Dash group name and slug
            $ld_group_id = groups_get_groupmeta( $bb_group_id, '_sync_group_id', true);
            $ld_data = array(
                'ID' => $ld_group_id,
                'post_title' => $group_name,
                'slug' => $group_slug
            );

            wp_update_post( $ld_data );

            // add family group meta if exist
            if( !empty($group_family) ):
                groups_update_groupmeta( $bb_group_id, 'group_family', true );
            endif;



        endif;
    endif;
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];

    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );

    if( !empty($bb_group_id) ):

        // get mvs group id
        if( !empty(BP_Groups_Group::get_id_from_slug('mvs')) ):
            $mvs_parent_group_id = BP_Groups_Group::get_id_from_slug('mvs');
        endif;

        // join students as members
        foreach ( $bookly_student_ids as $bookly_student_id ):
            if( ! groups_join_group( $bb_group_id, $bookly_student_id ) ): // groups_join_group( int $group_id, int $user_id )
                $catch_error .= 'Error in join learner(s) ' . $bookly_student_id . ' as bb groub member.<br>';
            endif;

            if( !empty($mvs_parent_group_id) && $program_type == 'mvs' ):
                // join members to 'mvs' bb group
                if( ! groups_join_group( $mvs_parent_group_id, $bookly_student_id ) ): // groups_join_group( int $group_id, int $user_id )
                    $catch_error .= 'Error in join learner(s) to parent MVS group ' . $bookly_student_id . ' as bb groub member.<br>';
                endif;

                // add role 'student' to all customers in 'mvs'
                $user = get_user_by( 'id', $bookly_student_id );
                $user->add_role('student');
                $user = null;

            endif;
        endforeach;

        // join staff as moderator
        // get wp_user_id for teacher_id from bookly_staff table
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $wp_teacher_id = $wpdb->get_results(
            "SELECT wp_user_id FROM $bookly_staff_table WHERE id = {$bookly_teacher_id}"
        );
        $wpdb->flush();
        if( !empty($wp_teacher_id) ):
            $wp_teacher_id = $wp_teacher_id[0]->wp_user_id;
            if( ! groups_join_group( $bb_group_id, $wp_teacher_id ) ): // groups_join_group( int $group_id, int $user_id )
                $catch_error .= 'Error in joining Teacher in BB group<br>';
            endif;

            //update group moderator
            $bb_groups_members_table = $wpdb->prefix . 'bp_groups_members';
            $update_sql ="UPDATE $bb_groups_members_table
            SET `is_mod`= '1',
            `date_modified` = '".$created_at."'
            WHERE  `user_id` = '". $wp_teacher_id ."' AND `group_id` = '". $bb_group_id ."' ";

            $update_moderator = $wpdb->query($update_sql);
            if( $update_moderator !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating group moderator'. $wpdb->print_error();
            endif;

            // create new zoom meeting id if option is selected and group type is mvs
            $teacher_obj = get_user_by('id', $wp_teacher_id);
            $teacher_email = $teacher_obj->data->user_email;
            if( $program_type == 'mvs' && !empty($link_to_group_option) && $link_to_group_option == 'msl_create_zoom_meeting' ):
                $zoom_meeting_id = msl_create_zoom_meeting( $teacher_email, $group_name, 'mvs', $bb_group_id);
                if( $zoom_meeting_id == false ):
                    $catch_error .= 'Error: in createing zoom meeting for group: '. $bb_group_id . '<br>';
                endif;
            endif;

        endif;

        // insert Gravity Form entry for Single Program
        $sp_url = rest_url( 'gf/v2/forms/'. SP_PARENT_FORM_ID() .'/submissions' );
        $sp_form_data = array(
            "input_9" => $program_type,
            "input_10" => $program_status,
            "input_26" => 'Active',
            "input_29" => $program_future_total_hrs,
            "input_3" => $bookly_user_timezone,
            "input_7" => $bb_group_id,
            "input_5" => $bookly_category_id,
            "input_6" => $bookly_service_id,
            "input_8" => $bookly_teacher_id,
            "input_11" => $zoom_meeting_id,
            "field_values" => "",
            "source_page" => 1,
            "target_page" =>  1
        );

        $sp_response = wp_remote_post( $sp_url, array(
            'body'    => $sp_form_data,
            'headers' => REST_HEADERS(),
        ) );
        

        // Check the response code.
        if ( empty( wp_remote_retrieve_body( $sp_response ) ) ) {
            // If not a 200, HTTP request failed.
            $catch_error .= 'Error: Single Program Form: There was an error attempting to access the API.<br>';
        } elseif( !empty($sp_response['body'] ) ) {
            $sp_form_entry_status = json_decode($sp_response['body'])->is_valid;
        }

        if( $sp_form_entry_status !== true && !empty( $sp_form_entry_status ) ):
            $catch_error .= 'Error: Single Program Entry insertion process not Completed<br>';
        else:
            // change status to sp entry to approved
            $sp_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. SP_PARENT_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
            $sp_entries_response = wp_remote_get( $sp_url_entries, array(
                'headers' => REST_HEADERS(),
            ) );

            $sp_last_entry_obj =json_decode($sp_entries_response['body']);

            if( !empty($sp_last_entry_obj) ):
                $sp_last_entry_id = $sp_last_entry_obj->entries[0]->id;

//                if( ! gform_update_meta( $sp_last_entry_id, 'is_approved', 1 ) ):
//                    $catch_error .= 'Error in updating Single Form entry to Approved<br>';
//                endif;

                // update created by current user
                if ( ! setGFentryCreatedBy($sp_last_entry_id, get_current_user_id() ) ):
                    $catch_error .= 'Error: in update SP GF entry Created By';
                endif;
            else:
                $catch_error .= 'Error in getting Single Parent form Entries<br>';
            endif;



            // insert entry in learners form
            $learners_url = rest_url( 'gf/v2/forms/'. LEARNERS_FORM_ID() .'/submissions' );
            $learners_form_data = array(
                "input_3" => $bookly_student_ids,
                "field_values" => "",
                "source_page" => 1,
                "target_page" =>  1
            );

            $learners_form_response = wp_remote_post( $learners_url, array(
                'body'    => $learners_form_data,
                'headers' => REST_HEADERS(),
            ) );

            // Check the response code.
            if (  empty( wp_remote_retrieve_body( $learners_form_response ) )  ) {
                // If not a 200, HTTP request failed.
                $catch_error .= 'Error in submitting Learners(s) Gravity Form entry. There was an error attempting to access the API.<br>';
            } else {
                $learners_form_entry_status = json_decode($learners_form_response['body'])->is_valid;
            }

            if( $learners_form_entry_status !== true && !empty($learners_form_entry_status) ):
                $catch_error .= 'Error: Learner(s) entry insertion not Completed<br>';
            else:
                // change status to sp entry to approved
                $learners_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. LEARNERS_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
                $learners_entries_response = wp_remote_get( $learners_url_entries, array(
                    'headers' => REST_HEADERS(),
                ) );

                $learners_last_entry_obj =json_decode($learners_entries_response['body']);
                // approve entry
                if( !empty($learners_last_entry_obj) ):
                    $learners_last_entry_id = $learners_last_entry_obj->entries[0]->id;

//                    if( ! gform_update_meta( $learners_last_entry_id, 'is_approved', 1 ) ):
//                        $catch_error .= 'Error in updating Learner(s) gravity form to Approved<br>';
//                    endif;

                    // update created by current user
                    if ( ! setGFentryCreatedBy($learners_last_entry_id, get_current_user_id() ) ):
                        $catch_error .= 'Error: in update Learners GF entry Created By';
                    endif;

                else:
                    $catch_error .= 'Error in getting Learner(s) Gravity Form Entries<br>';
                endif;


                // link entry to SP entry parent form
                $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
                if( ! gform_add_meta($learners_last_entry_id, $parent_meta_key, $sp_last_entry_id) ):
                    $catch_error .= 'Error in linking learners(s) form entry with Single program form entry<br>';
                endif;
            endif;
        endif;

        $schedule_last_entry_ids = []; // for roll back

        // create appointments records
        for( $i=0; $i<count($rowReccurringStartDates); $i++ ):
            //echo '------------------ Row ' . $i . ' ----------------------- <br>';
            // for each booking row create new series id
            // insert new record in bookly series table
            $bookly_series_table = $wpdb->prefix . 'bookly_series';

            $insert_new_series = $wpdb->insert($bookly_series_table,
                array(
                    'repeat' => '',
                    'token' => generateUniqueToken(),
                )
            );

            if( $insert_new_series ):
                // get last series id from bookly_series to attach to new schedule
                $series_results = $wpdb->get_results(
                    "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
                );
                $wpdb->flush();

                if( !empty($series_results) ):
                    $series_id = (int) $series_results[0]->id;
                else:
                    $catch_error .= 'Error: bookly series id not found<br>';
                endif;

                // insert Gravity Form entry for Single Program
                $schedules_url = rest_url( 'gf/v2/forms/'. SCHEDULE_FORM_ID() .'/submissions' );
                $schedule_form_data = array(
                    "input_7" => $series_id,
                    "input_6" => $bookly_class_duration[$i],
                    "input_5" => date('H:i', strtotime( $row_effective_start_dates[$i][0] )),
                    "input_3" => serialize($input_date[$i]),
                    "input_9" => $bookly_effective_date,
                    "input_10" => $bookly_teacher_id,
                    "field_values" => "",
                    "source_page" => 1,
                    "target_page" =>  1
                );

                $schedule_form_data = array_merge($schedule_days_input[$i], $schedule_form_data);

                $schedule_response = wp_remote_post( $schedules_url, array(
                    'body'    => $schedule_form_data,
                    'headers' => REST_HEADERS(),
                ) );

                // Check the response code.
                if ( empty( wp_remote_retrieve_body( $schedule_response ) ) ) {
                    // If not a 200, HTTP request failed.
                    $catch_error .= 'Error in inserting Schedule(s) gravity form entry. There was an error attempting to access the API.<br>';
                } else {
                    $schedule_form_entry_status = json_decode($schedule_response['body'])->is_valid;
                }


                if( $schedule_form_entry_status !== true && !empty( $schedule_form_entry_status )):
                    $catch_error .= 'Error: Schedule Entry inserting is not Completed<br>';
                else:
                    // change status to sp entry to approved
                    $schedule_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. SCHEDULE_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
                    $schedule_entries_response = wp_remote_get( $schedule_url_entries, array(
                        'headers' => REST_HEADERS(),
                    ) );

                    $schedule_last_entry_obj =json_decode($schedule_entries_response['body']);
                    if( !empty($schedule_last_entry_obj) ):
                        $schedule_last_entry_id = $schedule_last_entry_obj->entries[0]->id;
                        $schedule_last_entry_ids[] = $schedule_last_entry_id;
                        // approve entry
//                        if( ! gform_update_meta( $schedule_last_entry_id, 'is_approved', 1 ) ):
//                            $catch_error .= 'Error in updating Schedule(s) form as Approved<br>';
//                        endif;

                        // update created by current user
                        if ( ! setGFentryCreatedBy($schedule_last_entry_id, get_current_user_id() ) ):
                            $catch_error .= 'Error: in update Schedule GF entry Created By';
                        endif;

                        // link entry to SP parent entry
                        $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
                        if( ! gform_add_meta($schedule_last_entry_id, $parent_meta_key, $sp_last_entry_id) ):
                            $catch_error .= 'Error in linking Single program form entry to Schedule(s) entry. <br>';
                        endif;

                    else:
                        $catch_error .= 'Error: in getting last entry for Schedule(s) Form<br>';
                    endif;

                endif;


            else:
                $wpdb->show_errors();
                $catch_error .= 'Error: insert new bookly series record. ' . $wpdb->last_error.'<br>';
            endif;

            for( $x=0; $x<count($rowReccurringStartDates[$i]); $x++ ):
                //echo '==== Day ' . $x .' ====<br>';
                for( $d=1; $d<=count($rowReccurringStartDates[$i][$x]); $d++ ):
                    //echo 'start: ' . $rowReccurringStartDates[$i][$x][$d]. ' ---  end: ' . $rowReccurringEndDates[$i][$x][$d] .'<br>';

                    $booking_start_date = $rowReccurringStartDates[$i][$x][$d];
                    $booking_end_date   = $rowReccurringEndDates[$i][$x][$d];

                    // for each class_day in row get recurring dates ( start and end ) -> create appointment record
                    // insert appointments into bookly_appointments table
                    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                    $appointments = array(
                        array(
                            'staff_id' => $bookly_teacher_id,
                            'staff_any' => 0,
                            'service_id' => $bookly_service_id,
                            'start_date' => $booking_start_date,
                            'end_date' => $booking_end_date,
                            'extras_duration' => 0,
                            'internal_note' => '',
                            'created_from' => 'bookly',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ),
                    );


                    if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1 ):
                        //if record true, get appointment id
                        $appointment_id = $wpdb->insert_id;

                        // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
                        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

                        foreach ( $bookly_customer_ids as $key=>$bookly_customer_id ):

                            $customer_appointments_array[$key] = array(
                                'series_id' => $series_id,
                                'customer_id' => $bookly_customer_id,
                                'appointment_id' => $appointment_id,
                                'number_of_persons' => 1,//count( $bookly_customer_ids ),
                                'units' => $units[$i],
                                'extras' => [],
                                'extras_multiply_nop' => 1,
                                'extras_consider_duration' => 1,
                                'custom_fields' => $custom_fields,
                                'status' => 'approved',
                                'token' => generateUniqueToken(),
                                'time_zone' => 'UTC',
                                'time_zone_offset' => $bookly_user_timezone_offset,
                                'created_from' => 'frontend',
                                'created_at' => $created_at,
                                'updated_at' => $created_at
                            );

                        endforeach;

                        $customer_appointments = $customer_appointments_array;

                        if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments) !== 1 ):
                            // do nothing, insert success
                            //$catch_error .= 'Error in inserting customer appointment <br>';
                        endif;
                    else:
                        $wpdb->show_errors();
                        $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
                    endif;

                endfor;
            endfor;
        endfor;

    else:
        $catch_error .= 'Error creating BB group<br>';
    endif;

    if( empty($catch_error) ):
        // update childs parents parent stats table
        if( !empty($parent_ids) ):
            $parent_ids = array_unique($parent_ids);
            foreach ( $parent_ids as $parent_id ):
                updateUserBillingIndicator($parent_id, '');
            endforeach;
        endif;
        echo 'status ok';
    else:
        echo $catch_error;
    endif;

    wp_die();
}


/******
 * Ajax action to get overlap appointmnets data for teacher
 ******/

add_action('wp_ajax_get_overlap_appointments_data', 'get_overlap_appointments_data');
add_action( 'wp_ajax_nopriv_get_overlap_appointments_data', 'get_overlap_appointments_data' );
function get_overlap_appointments_data(){
    $overlap_appointments_ids = $_POST['overlap_appointments_ids'];
    // if found overlap appointments, get their data
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    if( !empty($overlap_appointments_ids) ):

        foreach ( $overlap_appointments_ids as $overlap_appointments_id ):

            // get their data from bookly appointments table
            $appts_result = $wpdb->get_results(
                "SELECT * FROM $bookly_appointments_table WHERE id = {$overlap_appointments_id}"
            );
            $wpdb->flush();

            $start_date[$overlap_appointments_ids] = $appts_result[0]->start_date;
            $end_date[$overlap_appointments_ids] = $appts_result[0]->end_date;

            // get timezone for meeting
            $ca_appts_result = $wpdb->get_results(
                "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$overlap_appointments_id}"
            );
            $wpdb->flush();
            $timezone[$overlap_appointments_ids] = $ca_appts_result[0]->time_zone;


        endforeach;

    endif;

    $overlap_data = json_encode(
        array(
            'success' => true,
            'message' => 'overlap appointments data',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'timezone' => $timezone
        )
    );
    echo $overlap_data;
    wp_die();

}


/******
 * Ajax action to Delete Data for a single program
 ******/

add_action('wp_ajax_delete_single_program', 'delete_single_program');
add_action( 'wp_ajax_nopriv_delete_single_program', 'delete_single_program' );
function delete_single_program(){

    global $wpdb;
    $appointment_id = $_POST['appointment_id'];
    $deleteBBgroup = $_POST['deleteBBgroup'];
    $deleteLDgroup = $_POST['deleteLDgroup'];
    $deleteGFentries = $_POST['deleteGFentries'];
    $deleteBooklyAppointments = $_POST['deleteBooklyAppointments'];
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $delete_error = '';

    if( !empty($appointment_id) ):

        // get bb group id from appointment id
        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
        $customer_appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_customer_appointments_table WHERE appointment_id = {$appointment_id}"
        );
        $wpdb->flush();

        $custom_fields = json_decode( $customer_appointments_results[0]->custom_fields );
        foreach ( $custom_fields as $key=>$custom_field ):
            if( $custom_field->id === $bb_custom_field_id ):
                $bb_group_id = $custom_field->value;
            endif;
        endforeach;


        if( !empty($bb_group_id) ):
            $linked_ld_group_id = groups_get_groupmeta($bb_group_id, '_sync_group_id', true);

            if( $deleteBBgroup === 'true' ):
                if( ! deleteBBgroup($bb_group_id) ):
                    $delete_error .= 'Error in deleteing BB group with id: ' . $bb_group_id . '<br>';
                endif;
            endif;

            if( $deleteLDgroup === 'true' ):
                if( ! deleteLDgroup($linked_ld_group_id) ):
                    $delete_error .= 'Error in deleteing LD group with id: ' . $linked_ld_group_id . '<br>';
                endif;
            endif;

            if( $deleteGFentries === 'true' ):
                // get SP entry id
                $gf_meta_entry = $wpdb->prefix . 'gf_entry_meta';
                $sp_form_id = SP_PARENT_FORM_ID();
                $sp_entry_result = $wpdb->get_results(
                    "SELECT * FROM $gf_meta_entry WHERE meta_key = 7 AND meta_value ={$bb_group_id} AND form_id = {$sp_form_id}"
                );
                $wpdb->flush();

                $sp_entry_id = $sp_entry_result[0]->entry_id;


                if( !empty( $sp_entry_id ) ):
                    $sp_entry_delete = GFAPI::delete_entry( $sp_entry_id );
                    if( ! $sp_entry_delete ):
                        $delete_error .= 'Error deleting GF entry with id: ' . $sp_entry_id .'<br>';
                    endif;
                else:
                    $delete_error .= 'Error no GF entry found for single form <br>';
                endif;

                // get entry for learner form
                $meta_key = 'workflow_parent_form_id_'. $sp_form_id .'_entry_id';
                $learner_form_id = LEARNERS_FORM_ID();
                $learner_entry_result = $wpdb->get_results(
                    "SELECT * FROM $gf_meta_entry WHERE meta_key LIKE '{$meta_key}' AND meta_value = {$sp_entry_id} AND form_id = {$learner_form_id}"
                );
                $wpdb->flush();

                $learner_entry_id = $learner_entry_result[0]->entry_id;
                if( !empty( $learner_entry_id ) ):
                    $learner_entry_delete = GFAPI::delete_entry( $learner_entry_id );
                    if( ! $learner_entry_delete ):
                        $delete_error .= 'Error deleting GF learner entry with id: ' . $learner_entry_id .'<br>';
                    endif;
                else:
                    $delete_error .= 'Error no GF learner entry found for entry with id: ' . $sp_entry_id .' <br>';
                endif;


                // get entry for schedule form
                $meta_key = 'workflow_parent_form_id_'. $sp_form_id .'_entry_id';
                $schedule_form_id = SCHEDULE_FORM_ID();
                $schedule_entry_result = $wpdb->get_results(
                    "SELECT * FROM $gf_meta_entry WHERE meta_key LIKE '{$meta_key}' AND meta_value = {$sp_entry_id} AND form_id = {$schedule_form_id}"
                );
                $wpdb->flush();

                if( !empty($schedule_entry_result) ):
                    foreach ( $schedule_entry_result as $schedule_entry ):
                        $schedule_entry_id = $schedule_entry->entry_id;
                        $schedule_entry_delete = GFAPI::delete_entry( $schedule_entry_id );
                        if( ! $schedule_entry_delete ):
                            $delete_error .= 'Error deleting GF schedule entry with id: ' . $schedule_entry_id .'<br>';
                        endif;
                    endforeach;
                else:
                    $delete_error .= 'Error no schedule entries found for parent entry with id: ' . $sp_entry_id. '<br>';
                endif;

            endif;



            // delete appointments frim customers table
            $custom_fields_value = '[{"id":'. $bb_custom_field_id .',"value":"'.$bb_group_id.'"}]';
            $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
            $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

            $customer_appointments_results = $wpdb->get_results(
                "SELECT * FROM $bookly_customer_appointments_table WHERE custom_fields LIKE '%{$custom_fields_value}%'"
            );
            $wpdb->flush();

            if( !empty($customer_appointments_results) ):
                foreach ($customer_appointments_results as $customer_appointments_result):
                    $ca_ids[] = $customer_appointments_result->id;
                    $appts_ids[] = $customer_appointments_result->appointment_id;
                endforeach;
            else:
                $delete_error .= 'Error no customer appointments records found for BB group with id: ' . $bb_group_id .'<br>';
            endif;

            if( !empty($ca_ids) ):
                $ca_ids = implode(",", $ca_ids);
                if( ! $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_ids)") ):
                    $delete_error .= 'Error in deleting bookly_customer_appointments_table for BB group with id: ' . $bb_group_id .'<br>';
                endif;
            endif;

            // delete appointmnets from appointmnets table
            if( !empty($appts_ids) ):
                $appts_ids = implode(",", $appts_ids);
                if( ! $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") ):
                    $delete_error .= 'Error in deleting bookly_appointments_table for BB group with id: ' . $bb_group_id .'<br>';
                endif;
            endif;



        else:
            $delete_error .= 'Error no BB group found for this appointment. <br>';
        endif; //



    endif;


    if( !empty($delete_error) ):
        echo $delete_error;
    else:
        echo 'deleted';
    endif;

    wp_die();

}

/******
 * Ajax action to get missing bb groups for teacher
 ******/


add_action('wp_ajax_get_missing_bb_groups', 'get_missing_bb_groups');
add_action( 'wp_ajax_nopriv_get_missing_bb_groups', 'get_missing_bb_groups' );
function get_missing_bb_groups () {

    $teacher_wp_user_id = $_POST['teacher_wp_user_id'];
    $missing_bb_groups = getMissingBBgroups($teacher_wp_user_id);
    $bb_groups_options = '<option selected disabled> -- choose group -- </option>';
    foreach ( $missing_bb_groups as $missing_bb_group ):
        $bb_group_id = $missing_bb_group;
        $bb_groups_options .= '<option value="'. $bb_group_id .'"> '.$bb_group_id.'  </option>';
    endforeach;
    echo $bb_groups_options;
    wp_die();

}



add_action('wp_ajax_fix_bb_group_ca_records', 'fix_bb_group_ca_records');
add_action( 'wp_ajax_nopriv_fix_bb_group_ca_records', 'fix_bb_group_ca_records' );
function fix_bb_group_ca_records(){

    $catch_error = '';
    $teacher_wp_user_id = $_POST['teacher_wp_user_id'];
    $bb_group_id = $_POST['bb_group_id'];
    // delete bookly_appts wrong records
    $missing_appointments = getMissingAppointments($teacher_wp_user_id);
    if( !empty($missing_appointments) ):
        // delete these records
        $delete_records = deleteMissingAppointments($teacher_wp_user_id);
        if( !$delete_records ):
            $catch_error .= $delete_records;
        endif;
    else:
        $catch_error .= 'Note: no missing records were found';
    endif;

    //regenerate missing appointmnets

    $regenerate_bookly_records = reGenerateMissingAppointments( $teacher_wp_user_id, $bb_group_id );
    if( ! $regenerate_bookly_records ):
        $catch_error .= $regenerate_bookly_records;
    endif;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'true';
    endif;

    wp_die();

}


add_action('wp_ajax_check_teachers_appointments', 'check_teachers_appointments');
add_action( 'wp_ajax_nopriv_check_teachers_appointments', 'check_teachers_appointments' );
function check_teachers_appointments () {
    $teachers_ids = $_POST['teachers_ids'];
    // get staff appointments
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    foreach ( $teachers_ids as $teachers_id ):

        $appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$teachers_id}"
        );
        $wpdb->flush();
        foreach ( $appointments_results as $appointments_result ):
            $appointment_id = $appointments_result->id;
            // get customer appointments for this id
            $customer_appointment_results = $wpdb->get_results(
                "SELECT * FROM $bookly_appointments_customer_table WHERE appointment_id = {$appointment_id}"
            );
            $wpdb->flush();
            if( empty($customer_appointment_results) ):
                $empty_records[$teachers_id][] = $appointment_id;
            endif;
        endforeach;

    endforeach;


    wp_die();

}


// ajax call for storing makeup log

add_action('wp_ajax_store_makeup_log', 'store_makeup_log');
add_action( 'wp_ajax_nopriv_store_makeup_log', 'store_makeup_log' );
function store_makeup_log() {
    $trans_amount = (int) $_POST['trans_amount'];
    $trans_type = $_POST['trans_type'];
    $user_role = $_POST['user_role'];
    $parent_id = (int) $_POST['parent_id'];
    $user_id = $_POST['user_id'];
    $trans_notes = $_POST['trans_notes'];
    $catch_error = '';

    if( empty($trans_amount) ||
        empty($trans_type) ||
        empty($user_role) ||
        empty($parent_id) ||
        empty($user_id)
    ):
        echo 'Error: some values are empty. Please check again.';
        wp_die();
    else:
        $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
        $created_at = $current_date_object->format('Y-m-d H:i:s');

        $makeup_log_data = array(
            array(
                'trans_amount' => (int) $trans_amount,
                'parent_id' => (int) $parent_id,
                'trans_type' => $trans_type,
                'trans_notes' => $trans_notes,
                'user_role' => $user_role,
                'user_id' => (int) $user_id,
                'aid' => '',
                'created_at' => $created_at
            )
        );


        global $wpdb;
        $makeup_log_table = $wpdb->prefix . 'muslimeto_makeup_log';
        $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
        $final_makeup_balance = 0;

        // get parent old makeup balance
        $makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $wp_user_id );

        // insert into table
        if( !empty($makeup_log_data) ):
            // search if parent has a previous stored open-balance record in makeup table
            $open_balance_result = $wpdb->get_results(
                "SELECT trans_amount, created_at FROM $makeup_log_table WHERE parent_id = {$parent_id} AND trans_type = 'open-balance'"
            );
            $wpdb->flush();


            if( isset($open_balance_result) && !empty($open_balance_result) ):
                // parent has an open balance before,
                $open_balance_trans_amount = $open_balance_result[0]->trans_amount;
                $open_balance_created_at = $open_balance_result[0]->created_at;
                //  get any records from makeup_log table >= created_at for open-balance record
                $parent_makeup_logs = $wpdb->get_results(
                    " SELECT trans_amount FROM $makeup_log_table WHERE parent_id = {$parent_id} AND created_at >= '{$open_balance_created_at}'"
                );
                $wpdb->flush();


                // and sum ( trans_amount ) => new_makeup_balance
                if( isset($parent_makeup_logs) && !empty($parent_makeup_logs) ):
                    $final_makeup_balance = $trans_amount;
                    foreach ( $parent_makeup_logs as &$parent_makeup_log ):
                        $final_makeup_balance += (int) $parent_makeup_log->trans_amount;
                    endforeach;
                else: // parent has no previous makeup history after open balance, new makeup balance => open balance ( trans amount entered from UI )
                    $final_makeup_balance = $trans_amount;
                endif;
                $has_opening_balance = 0;
            else: // parent has no open balance record , insert new one
                $final_makeup_balance = $trans_amount;
                $has_opening_balance = 1;
                if( wpdb_bulk_insert($makeup_log_table, $makeup_log_data) === false ):
                    $wpdb->show_errors();
                    $wpdb->print_error();
                    $catch_error .= '<div class="alert alert-danger" role="alert"> Error in inserting makeup log data </div>';
                endif;
            endif;

            // set a boolean flag in the parent stats record => that parent has an opening balance record
            $parent_stats_results = $wpdb->get_results(
                " SELECT id FROM $parent_stats_table WHERE parent_wp_user_id = {$parent_id}"
            );
            $wpdb->flush();

            if( isset($parent_stats_results) && !empty($parent_stats_results) ):
                // parent has a record in parent stats table => update has_opening_balance column
                $wpdb->update($parent_stats_table, array('has_opening_balance'=>$has_opening_balance), array('parent_wp_user_id'=>$parent_id));
            else:
                // parent has no record in parent stats table, throw error
                $catch_error .= 'Error: parent has no stats record. <br>';
            endif;


            // update parent user makeup balance
            update_field('mslm_makeup_balance', $final_makeup_balance, 'user_'.$parent_id);

        endif;
    endif;


    if( empty($catch_error) ):
        echo true;
    else:
        echo $catch_error;
    endif;

    wp_die();


}

// get parent user makeup balance

add_action('wp_ajax_get_parent_user_makeup_balance', 'get_parent_user_makeup_balance');
add_action( 'wp_ajax_nopriv_get_parent_user_makeup_balance', 'get_parent_user_makeup_balance' );
function get_parent_user_makeup_balance() {
    $parent_id = $_POST['parent_id'];
    // get makeup balance for parent
    $parent_makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $parent_id );
    if( !empty($parent_makeup_balance) ):
        echo $parent_makeup_balance;
    else:
        echo 0;
    endif;
    wp_die();
}


// ajax call to save attendance class record

add_action('wp_ajax_save_attendance_class', 'save_attendance_class');
add_action( 'wp_ajax_nopriv_save_attendance_class', 'save_attendance_class' );
function save_attendance_class(){
    $attendance_status = $_POST['attendance_status'];
    $actual_mins = $_POST['actual_mins'];
    $late_mins = $_POST['late_mins'];
    $parent_makeup_balance = $_POST['parent_makeup_balance'];
    $meta_parent_makeup_balance = $_POST['meta_parent_makeup_balance'];
    $parent_id = $_POST['parent_id'];
    $bb_group_id = $_POST['bb_group_id'];
    $ca_id = $_POST['ca_id'];
    $trans_amount = $_POST['trans_amount'];
    $trans_type = $_POST['trans_type'];
    $trans_notes = $_POST['trans_notes'];
    $user_role = $_POST['user_role'];
    $user_id = $_POST['user_id'];
    $appointmnet_id = $_POST['appointmnet_id'];
    $customer_id = $_POST['customer_id'];
    $progress_notes = $_POST['progress_notes'];
    $private_notes = $_POST['private_notes'];
    $created_at = $_POST['created_at'];
    $update_makeup_log_record = $_POST['update_makeup_log_record'];
    $makeup_log_record = $_POST['makeup_log_record'];
    $teacher_can_edit = $_POST['teacher_can_edit'];
    $catch_error = '';
    global $wpdb;

    // update parent user makeup balance
    $stored_parent_makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $parent_id );
    if( (int) $meta_parent_makeup_balance === $stored_parent_makeup_balance):
        update_field('mslm_makeup_balance', $parent_makeup_balance, 'user_'.$parent_id);
    else:
        $catch_error .= 'meta_conflict';
        echo $catch_error;
        wp_die();
    endif;

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $updated_at = $current_date_object->format('Y-m-d H:i:s');

    // get custom field values
    $customer_appt_record = getBooklyCA($appointmnet_id);
    if( !empty($customer_appt_record) ):
        // get bb group from custom fields
        $stored_custom_field_string = $customer_appt_record[0]->custom_fields;
        $stored_custom_field = json_decode($stored_custom_field_string);
        if( !empty($stored_custom_field) ):

            foreach ( $stored_custom_field as $key=>$custom_field ):

                //unset all
                if( $custom_field->id === 95778 ): // late mins value
                    unset($stored_custom_field[$key]);
                endif;

                if( $custom_field->id === 2583 ): // actual mins value
                    unset($stored_custom_field[$key]);
                endif;

                if( $custom_field->id === 24491 ): // private notes value
                    unset($stored_custom_field[$key]);
                endif;


            endforeach;

            // save new custom fields values
            $late_mins_field = array(
                'id' => 95778,
                'value' => $late_mins
            );

            $actual_mins_field = array(
                'id' => 2583,
                'value' => $actual_mins
            );

            $private_notes_field = array(
                'id' => 24491,
                'value' => $private_notes
            );


            // save all
            array_push($stored_custom_field, $late_mins_field);
            array_push($stored_custom_field, $actual_mins_field);
            array_push($stored_custom_field, $private_notes_field);


            $new_custom_field = json_encode(array_merge($stored_custom_field));
        endif;

    endif;


    // check $update_makeup_log_record if true save all data and update makeup_log_record
    $makeup_log_table = $wpdb->prefix . 'muslimeto_makeup_log';
    if($update_makeup_log_record === 'true' ):

        if( $teacher_can_edit === 'false' ):
            // update only updated at column
            //update makeup log record with id
            $update_makeup_sql ="UPDATE $makeup_log_table
                SET `updated_at`= '". $updated_at ."'
                WHERE  `id` = '". $makeup_log_record ."'";

            $update_makeup_record_status = $wpdb->query($update_makeup_sql);
            if( $update_makeup_record_status !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating makeup record <br>'. $wpdb->print_error();
            endif;
        else:
            // update whole record
            //update makeup log record with id
            $update_makeup_sql ="UPDATE $makeup_log_table
                SET `trans_amount`= '". (int) $trans_amount ."',
                `makeup_balance` = '".(int) $parent_makeup_balance."',
                `updated_at` = '". $updated_at ."'
                WHERE  `id` = '". $makeup_log_record ."'";

            $update_makeup_record_status = $wpdb->query($update_makeup_sql);
            if( $update_makeup_record_status !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating makeup record <br>'. $wpdb->print_error();
            endif;
        endif;



    else:
        // insert new makeup_log record
        // enter makeup log record
        $makeup_log_data = array(
            array(
                'trans_amount' => (int) $trans_amount,
                'makeup_balance' => (int) $parent_makeup_balance,
                'ca_id' => (int) $ca_id,
                'aid' => (int) $appointmnet_id,
                'cid' => (int) $bb_group_id,
                'parent_id' => (int) $parent_id,
                'trans_type' => $trans_type,
                'trans_notes' => $trans_notes,
                'user_role' => $user_role,
                'user_id' => (int) $user_id,
                'created_at' => $created_at
            )
        );


        // insert into table
        if( !empty($makeup_log_data) ):
            if( wpdb_bulk_insert($makeup_log_table, $makeup_log_data) === false ):
                $wpdb->show_errors();
                $wpdb->print_error();
                $catch_error .= '<div class="alert alert-danger" role="alert"> Error in inserting makeup log data </div><br>';
            endif;

        endif;

    endif;


    // update attendance record in bookly CA table
    if(
        empty($ca_id) ||
        empty($attendance_status) ||
        empty($bb_group_id) ||
        empty($trans_type) ||
        empty($trans_notes) ||
        empty($user_role) ||
        empty($user_id) ||
        empty($appointmnet_id) ||
        empty($customer_id) ||
        empty($created_at)
    ):
        $catch_error .= 'Error: Empty Fields <br>';
    endif;

    // save all

    //update customer appointmnet record status
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $update_sql ="UPDATE $bookly_customer_appointments_table
        SET `status`= '". $attendance_status ."',
        `custom_fields` = '".$new_custom_field."',
        `notes` = '". $progress_notes ."'
        WHERE  `appointment_id` = '". $appointmnet_id ."' AND `customer_id` = '". $customer_id ."'";

    $update_ca_record = $wpdb->query($update_sql);
    if( $update_ca_record !== 1 ):
        $wpdb->show_errors();
        $catch_error .= 'Error: in updating bookly customer appointment record <br>'. $wpdb->print_error();
    endif;


    if( empty($catch_error) ):
        echo 'success';
    else:
        echo $catch_error;
    endif;


    wp_die();
}

// validate status for all appointments with same stored_bb_group_id and fetch resutlt in modal

add_action('wp_ajax_validate_delete_program', 'validate_delete_program');
add_action( 'wp_ajax_nopriv_validate_delete_program', 'validate_delete_program' );
function validate_delete_program() {
    $stored_bb_group_id = $_POST['stored_bb_group_id'];
    //$admin_delete = $_POST['admin_delete'];

    // get all appointments with stored_bb_group_id
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($stored_bb_group_id)
            )
        )
    );

    $custom_fields = substr($custom_fields, 1, -1);

    global $wpdb;
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    $customer_appointment_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_customer_table WHERE custom_fields LIKE '%{$custom_fields}%'"
    );
    $wpdb->flush();

    $ca_status = [];
    $catch_error = '';
    if( !empty($customer_appointment_results) ):
        foreach ( $customer_appointment_results as $customer_appointment_result ):
            if( $customer_appointment_result->status !== 'approved' ):
                $ca_status[] = $customer_appointment_result->status;
            endif;
        endforeach;
        if( empty($ca_status) ):
            // you can delete safely, all statuses are 'approved'

        else:
            // show error message
            $catch_error .= 'Alert: attendance found for this program';
        endif;

    endif;


    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'delete_program_is_valid';
    endif;

    wp_die();

}

/******
 * Ajax action to Delete Data for a single program Permamnently
 ******/

add_action('wp_ajax_permanent_delete_single_program', 'permanent_delete_single_program');
add_action( 'wp_ajax_nopriv_permanent_delete_single_program', 'permanent_delete_single_program' );
function permanent_delete_single_program(){

    global $wpdb;
    $appointment_id = $_POST['appointment_id'];
    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $delete_error = '';

    if( !empty($appointment_id) ):

        // get bb group id from appointment id
        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
        $customer_appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_customer_appointments_table WHERE appointment_id = {$appointment_id}"
        );
        $wpdb->flush();

        $custom_fields = json_decode( $customer_appointments_results[0]->custom_fields );
        foreach ( $custom_fields as $key=>$custom_field ):
            if( $custom_field->id === $bb_custom_field_id ):
                $bb_group_id = $custom_field->value;
            endif;
        endforeach;


        if( !empty($bb_group_id) ):
            $linked_ld_group_id = groups_get_groupmeta($bb_group_id, '_sync_group_id', true);

            if( ! deleteBBgroup($bb_group_id) ):
                $delete_error .= 'Error in deleteing BB group with id: ' . $bb_group_id . '<br>';
            endif;


            if( ! deleteLDgroup($linked_ld_group_id) ):
                $delete_error .= 'Error in deleteing LD group with id: ' . $linked_ld_group_id . '<br>';
            endif;


            // get SP entry id
            $gf_meta_entry = $wpdb->prefix . 'gf_entry_meta';
            $sp_form_id = SP_PARENT_FORM_ID();
            $sp_entry_result = $wpdb->get_results(
                "SELECT * FROM $gf_meta_entry WHERE meta_key = 7 AND meta_value ={$bb_group_id} AND form_id = {$sp_form_id}"
            );
            $wpdb->flush();

            $sp_entry_id = $sp_entry_result[0]->entry_id;


            if( !empty( $sp_entry_id ) ):
                $sp_entry_delete = GFAPI::delete_entry( $sp_entry_id );
                if( ! $sp_entry_delete ):
                    $delete_error .= 'Error deleting GF entry with id: ' . $sp_entry_id .'<br>';
                endif;
            else:
                $delete_error .= 'Error no GF entry found for single form <br>';
            endif;

            // get entry for learner form
            $meta_key = 'workflow_parent_form_id_'. $sp_form_id .'_entry_id';
            $learner_form_id = LEARNERS_FORM_ID();
            $learner_entry_result = $wpdb->get_results(
                "SELECT * FROM $gf_meta_entry WHERE meta_key LIKE '{$meta_key}' AND meta_value = {$sp_entry_id} AND form_id = {$learner_form_id}"
            );
            $wpdb->flush();

            $learner_entry_id = $learner_entry_result[0]->entry_id;
            if( !empty( $learner_entry_id ) ):
                $learner_entry_delete = GFAPI::delete_entry( $learner_entry_id );
                if( ! $learner_entry_delete ):
                    $delete_error .= 'Error deleting GF learner entry with id: ' . $learner_entry_id .'<br>';
                endif;
            else:
                $delete_error .= 'Error no GF learner entry found for entry with id: ' . $sp_entry_id .' <br>';
            endif;


            // get entry for schedule form
            $meta_key = 'workflow_parent_form_id_'. $sp_form_id .'_entry_id';
            $schedule_form_id = SCHEDULE_FORM_ID();
            $schedule_entry_result = $wpdb->get_results(
                "SELECT * FROM $gf_meta_entry WHERE meta_key LIKE '{$meta_key}' AND meta_value = {$sp_entry_id} AND form_id = {$schedule_form_id}"
            );
            $wpdb->flush();

            if( !empty($schedule_entry_result) ):
                foreach ( $schedule_entry_result as $schedule_entry ):
                    $schedule_entry_id = $schedule_entry->entry_id;
                    $schedule_entry_delete = GFAPI::delete_entry( $schedule_entry_id );
                    if( ! $schedule_entry_delete ):
                        $delete_error .= 'Error deleting GF schedule entry with id: ' . $schedule_entry_id .'<br>';
                    endif;
                endforeach;
            else:
                $delete_error .= 'Error no schedule entries found for parent entry with id: ' . $sp_entry_id. '<br>';
            endif;




            // delete appointments frim customers table
            $custom_fields_value = '[{"id":'. $bb_custom_field_id .',"value":"'.$bb_group_id.'"}]';
            $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
            $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

            $customer_appointments_results = $wpdb->get_results(
                "SELECT * FROM $bookly_customer_appointments_table WHERE custom_fields LIKE '%{$custom_fields_value}%'"
            );
            $wpdb->flush();

            if( !empty($customer_appointments_results) ):
                foreach ($customer_appointments_results as $customer_appointments_result):
                    $ca_ids[] = $customer_appointments_result->id;
                    $appts_ids[] = $customer_appointments_result->appointment_id;
                endforeach;
            else:
                $delete_error .= 'Error no customer appointments records found for BB group with id: ' . $bb_group_id .'<br>';
            endif;

            if( !empty($ca_ids) ):
                $ca_ids = implode(",", $ca_ids);
                if( ! $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_ids)") ):
                    $delete_error .= 'Error in deleting bookly_customer_appointments_table for BB group with id: ' . $bb_group_id .'<br>';
                endif;
            endif;

            // delete appointmnets from appointmnets table
            if( !empty($appts_ids) ):
                $appts_ids = implode(",", $appts_ids);
                if( ! $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") ):
                    $delete_error .= 'Error in deleting bookly_appointments_table for BB group with id: ' . $bb_group_id .'<br>';
                endif;
            endif;


        else:
            $delete_error .= 'Error no BB group found for this appointment. <br>';
        endif; //


    endif;

    if( !empty($delete_error) ):
        echo $delete_error;
    else:
        echo 'deleted';
    endif;

    wp_die();

}


/******
 * Ajax action to Delete schedule in edit mode from new effective date
 ******/

add_action('wp_ajax_delete_schedule_edit_mode', 'delete_schedule_edit_mode');
add_action( 'wp_ajax_nopriv_delete_schedule_edit_mode', 'delete_schedule_edit_mode' );
function delete_schedule_edit_mode(){

    $stored_bookly_series_id = $_POST['stored_bookly_series_id'];
    $stored_schedule_entry_id = $_POST['stored_schedule_entry_id'];
    $stored_gf_timezone = $_POST['stored_gf_timezone'];
    $stored_start_time_converted = $_POST['stored_start_time_converted'];
    $new_effective_date = date('Y-m-d H:i:s', strtotime($_POST['new_effective_date'] . ' ' . $stored_start_time_converted));

    if( empty($new_effective_date) || empty($stored_bookly_series_id) || empty($stored_gf_timezone) ):
        echo 'Error: Empty Fields';
        wp_die();
    endif;

    $catch_error = '';
    $new_effective_date = date('Y-m-d', strtotime($new_effective_date));
    // get all CA records with series_id and new effective from
    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_customer_appointments_table WHERE series_id = {$stored_bookly_series_id}"
    );
    $wpdb->flush();

    if( !empty($customer_appointments_results) ):

        $customers_ids = array_unique( array_column($customer_appointments_results, 'customer_id') );

        foreach ( $customers_ids as $customers_id ):
            // get parents ids
            $parent_ids[] = getParentID( getWPuserIDfromBookly($customers_id) );
        endforeach;


        foreach ( $customer_appointments_results as $ca_result ):
            $appointment_id = $ca_result->appointment_id;
            $appointment_status = $ca_result->status;
            if( $appointment_status === 'approved' ):
                // get start date from appontment_table

                $appointments_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_table WHERE id = {$appointment_id}"
                );
                $wpdb->flush();

                $start_date = $appointments_results[0]->start_date;
                $test_data[$appointment_id] = $appointment_status;
                if( strtotime($start_date) >= strtotime($new_effective_date) ):
                    $delete_appointments_data[$appointment_id] = $start_date;
                    $delete_appointments_ids[] = $appointment_id;
                    $delete_ca_appointments_ids[] = $ca_result->id;
                endif;

            endif;

        endforeach;

        // delete appointments id and ca_ids, and make schedule entry status = disapproved

        $appts_ids = implode(",", $delete_appointments_ids);
        if( $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") === false ):
            $catch_error .= "Error in deleting $appts_ids from bookly_appointments_table. <br>";
        endif;

        $ca_appts_ids = implode(",", $delete_ca_appointments_ids);
        if( $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_appts_ids)") === false ):
            $catch_error .= "Error in deleting $ca_appts_ids from bookly_ca_appointments_table. <br>";
        endif;

        // change GF entry end date to new effective date
        if( ! updateGFentryEndDate( $stored_schedule_entry_id,  date('Y-m-d', strtotime($new_effective_date) ) ) ):
            $catch_error .= 'Error: in updating end date for schedule entry: ' . $stored_schedule_entry_id .' <br>';
        endif;


    endif;




    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        // update childs parents parent stats table
        if( !empty($parent_ids) ):
            $parent_ids = array_unique($parent_ids);
            foreach ( $parent_ids as $parent_id ):
                updateUserBillingIndicator($parent_id, '');
            endforeach;
        else:
            echo "Error: no parents found to update billing indicator ";
        endif;
        echo 'true';
    endif;


    wp_die();
}


/******
 * Ajax action to Fix and Delete appointments for schedule in edit mode from new effective date that has issue
 ******/

add_action('wp_ajax_fix_delete_schedule_edit_mode', 'fix_delete_schedule_edit_mode');
add_action( 'wp_ajax_nopriv_fix_delete_schedule_edit_mode', 'fix_delete_schedule_edit_mode' );
function fix_delete_schedule_edit_mode(){
    $catch_error = '';
    $series_id = $_POST['series_id'];
    $bb_group_id = $_POST['bb_group_id'];
    $schedule_end_date = $_POST['schedule_end_date'];

    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';

    $appointments_to_delete = getBooklyEventsAfter( $schedule_end_date, $bb_group_id, $series_id );

    if( !empty($appointments_to_delete) && count($appointments_to_delete) ):
        $appointments_ids_to_delete = array_column($appointments_to_delete, 'appointment_id');
        $ca_appointments_ids_to_delete = array_column($appointments_to_delete, 'ca_id');
        // delete appointments id and ca_ids

        $appts_ids = implode(",", $appointments_ids_to_delete);
        if( $wpdb->query("DELETE FROM $bookly_appointments_table WHERE id IN ($appts_ids)") === false ):
            $catch_error .= "Error in deleting these records $appts_ids from bookly_appointments_table. <br>";
        endif;

        $ca_appts_ids = implode(",", $ca_appointments_ids_to_delete);
        if( $wpdb->query("DELETE FROM $bookly_customer_appointments_table WHERE id IN ($ca_appts_ids)") === false ):
            $catch_error .= "Error in deleting these records $caapppts_ids from bookly_ca_appointments_table. <br>";
        endif;

    else:
        $catch_error .= "Error: no appointments found for bb group: $bb_group_id with series id: $series_id from: $schedule_end_date";
    endif;


    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'true';
    endif;


    wp_die();
}

/******
 * Ajax action to Submit Data for edit mode
 ******/

add_action('wp_ajax_submit_single_program_booking_form_edit_mode', 'submit_single_program_booking_form_edit_mode');
add_action( 'wp_ajax_nopriv_submit_single_program_booking_form_edit_mode', 'submit_single_program_booking_form_edit_mode' );
function submit_single_program_booking_form_edit_mode(){

    global $wpdb;

    $program_type = $_POST['program_type'];
    $program_status = $_POST['program_status'];
    $bb_group_id = $_POST['bb_group_id'];
    $zoom_meeting_id = $_POST['zoom_meeting_id'];
    $bookly_teacher_id= $_POST['bookly_teacher_id'];
    $bookly_student_ids = $_POST['bookly_student_id'];
    $bookly_user_timezone = $_POST['bookly_user_timezone'];
    $bookly_start_hours = $_POST['bookly_start_hours'];
    $bookly_start_minutes = $_POST['bookly_start_minutes'];
    $bookly_class_duration = $_POST['bookly_class_duration'];
    $bookly_effective_date = $_POST['new_effective_date'];
    $bookly_effective_day = strtolower( date('D', strtotime($bookly_effective_date)) );
    $bookly_class_days = $_POST['bookly_class_days'];
    $bookly_student_name = $_POST['bookly_student_name'];
    $bookly_service_name = $_POST['bookly_service_name'];
    $bookly_service_id = $_POST['bookly_service_id'];
    $bookly_category_id = $_POST['bookly_category_id'];
    $bookly_user_timezone_offset = getNowTimeZoneOffset($bookly_user_timezone);
    $catch_error = '';
    $group_family = $_POST['group_family'];
    $gf_sp_entry_id = $_POST['gf_sp_entry_id'];
    $edit_option = $_POST['edit_option'];
    $stored_schedule_entry_ids = $_POST['stored_schedule_entry_ids'];
    $old_teacher_id = $_POST['old_teacher_id'];
    $clone_gf_schedule = $_POST['clone_gf_schedule'];
    $bookly_series_ids = $_POST['bookly_series_id'];



    if( empty($program_status) ||
        ( empty($bookly_teacher_id) && $edit_option !== 'cancel' ) ||
        empty($bookly_student_ids) ||
        empty($bookly_user_timezone) ||
        empty($bookly_start_hours) ||
        empty($bookly_start_minutes) ||
        empty($bookly_class_duration) ||
        empty($bookly_effective_date) ||
        empty($bookly_class_days) ||
        empty($bookly_service_id) ||
        empty($bookly_category_id)
    ):
        echo 'empty-fields';
        wp_die();
    endif;

    // get customer id from bookly_customers table for wp_user_id
    if( !is_array($bookly_student_ids) ):
        $bookly_student_ids = array($bookly_student_ids);
    endif;
    foreach ( $bookly_student_ids as $bookly_student_id ):
        $bookly_student_id = (int) $bookly_student_id;
        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
        $bookly_customers = $wpdb->get_results(
            "SELECT id FROM $bookly_customers_table WHERE wp_user_id = {$bookly_student_id}"
        );
        $wpdb->flush();
        $bookly_customer_ids[] = (int) $bookly_customers[0]->id;

        // get parents ids
        $parent_ids[] = getParentID($bookly_student_id);
    endforeach;


    // calculate program total hours
    $program_future_total_mins = 0;
    foreach ( $bookly_class_days as $key=>$bookly_class_day ):
        $program_future_total_mins += $bookly_class_duration[$key] * count($bookly_class_day) * 4;
    endforeach;
    if( $program_future_total_mins > 0 ):
        $program_future_total_hrs = $program_future_total_mins / 60;
    else:
        $program_future_total_hrs = 0;
    endif;

    // get old total housr for program
    $stored_total_hrs = getProgramTotalHours($bb_group_id);
    $starting_total_hours = $stored_total_hrs['starting_total_hrs'];
    $new_starting_total_hrs = $starting_total_hours + $program_future_total_hrs;


    // if user choose Transfer option in edit mode
    if( $edit_option === 'transfer' ):

        // convert user dates to UTC
        $bookly_effective_date = convertTimeZoneToUTC($bookly_effective_date, $bookly_user_timezone);

        $bookly_series_table = $wpdb->prefix . 'bookly_series';


        foreach ( $bookly_class_days as $key=>$bookly_class_day ):
            foreach ( $bookly_class_day as $day ):
                $day_index = "input_1." . array_search($day, WEEK_DAYS_INDEX);
                $schedule_days_input[$key][$day_index] = $day;
            endforeach;
        endforeach;

        // set end date for $stored_schedule_entry_ids as new effective date
        foreach ( $stored_schedule_entry_ids as $i=>$stored_schedule_entry_id ):

            $start_time = date('Y-m-d', strtotime( $bookly_effective_date ) ) . ' ' . $bookly_start_hours[$i] . ':' . $bookly_start_minutes[$i];
            $start_time = date('H:i', strtotime( $start_time ));

            if( $clone_gf_schedule[$i] === 'clone' ):
                // update end date for old gf entries
                if( ! updateGFentryEndDate( $stored_schedule_entry_id,  date('Y-m-d', strtotime($bookly_effective_date) ) ) ):
                    $catch_error .= 'Error: in updating end date for schedule entry: ' . $stored_schedule_entry_id .' <br>';
                endif;

                // create new schedule entries for new teacher

                $insert_new_series = $wpdb->insert($bookly_series_table,
                    array(
                        'repeat' => '',
                        'token' => generateUniqueToken(),
                    )
                );

                if( $insert_new_series ):
                    // get last series id from bookly_series to attach to new schedule
                    $series_results = $wpdb->get_results(
                        "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
                    );
                    $wpdb->flush();

                    if( !empty($series_results) ):
                        $series_id = (int) $series_results[0]->id;
                    else:
                        $catch_error .= 'Error: bookly series id not found<br>';
                    endif;

                    // insert Gravity Form entry for Single Program
                    $schedules_url = rest_url( 'gf/v2/forms/'. SCHEDULE_FORM_ID() .'/submissions' );
                    $schedule_form_data = array(
                        "input_7" => $series_id,
                        "input_6" => $bookly_class_duration[$i],
                        "input_5" => $start_time,
                        "input_9" => date('m/d/Y', strtotime( $bookly_effective_date ) ),
                        "input_10" => $bookly_teacher_id,
                        "field_values" => "",
                        "source_page" => 1,
                        "target_page" =>  1
                    );

                    $schedule_form_data = array_merge($schedule_days_input[$i], $schedule_form_data);

                    $schedule_response = wp_remote_post( $schedules_url, array(
                        'body'    => $schedule_form_data,
                        'headers' => REST_HEADERS(),
                    ) );


                    // Check the response code.
                    if ( empty( wp_remote_retrieve_body( $schedule_response ) ) ) {
                        // If not a 200, HTTP request failed.
                        $catch_error .= 'Error in inserting Schedule(s) gravity form entry. There was an error attempting to access the API.<br>';
                    } else {
                        $schedule_form_entry_status = json_decode($schedule_response['body'])->is_valid;
                    }

                    if( $schedule_form_entry_status !== true && !empty( $schedule_form_entry_status )):
                        $catch_error .= 'Error: Schedule Entry inserting is not Completed<br>';
                    else:
                        // change status to sp entry to approved
                        $schedule_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. SCHEDULE_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
                        $schedule_entries_response = wp_remote_get( $schedule_url_entries, array(
                            'headers' => REST_HEADERS(),
                        ) );

                        $schedule_last_entry_obj =json_decode($schedule_entries_response['body']);
                        if( !empty($schedule_last_entry_obj) ):
                            $schedule_last_entry_id = $schedule_last_entry_obj->entries[0]->id;
                            $schedule_last_entry_ids[] = $schedule_last_entry_id;
                            // approve entry
//                            if( ! gform_update_meta( $schedule_last_entry_id, 'is_approved', 1 ) ):
//                                $catch_error .= 'Error in updating Schedule(s) form as Approved<br>';
//                            endif;

                            // update created by current user
                            if ( ! setGFentryCreatedBy($schedule_last_entry_id, get_current_user_id() ) ):
                                $catch_error .= 'Error: in update Schedule GF entry Created By';
                            endif;

                            // link entry to SP parent entry
                            $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
                            if( ! gform_add_meta($schedule_last_entry_id, $parent_meta_key, $gf_sp_entry_id) ):
                                $catch_error .= 'Error in linking Single program form entry to Schedule(s) entry. <br>';
                            endif;

                        else:
                            $catch_error .= 'Error: in getting last entry for Schedule(s) Form<br>';
                        endif;

                    endif;

                endif;

            else:
                // update teacher id in old gf schedule entry
                if( ! updateGFentryTeacherId($stored_schedule_entry_id, $bookly_teacher_id) ):
                    $catch_error .= 'Error in updating teacher in schedule entry id: ' . $stored_schedule_entry_id . '<br>';
                endif;

            endif;

        endforeach;


        // query ca appointment table to get records and get start date for appointment_id >= new_effective_date
        $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];

        $custom_fields = json_encode(
            array(
                array(
                    'id' => $bb_custom_field_id,
                    'value' => strval($bb_group_id)
                )
            )
        );

        // get ca appts with bb_group_id
        $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
        $bookly_ca_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
        $ca_appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_ca_appointments_table WHERE custom_fields LIKE '%{$custom_fields}%'"
        );
        $wpdb->flush();

        if( !empty($ca_appointments_results) ):
            foreach ( $ca_appointments_results as $ca_appointments_result ):
                //get appt_id and start_date
                $appointment_id = $ca_appointments_result->appointment_id;
                // get start_date for appointment id
                $appointments_results = $wpdb->get_results(
                    "SELECT * FROM $bookly_appointments_table WHERE id = {$appointment_id}"
                );
                $wpdb->flush();
                if( !empty($appointments_results) ):
                    $appointment_start_date = $appointments_results[0]->start_date; // in UTc
                    //if start_date UTC >= new_effective_date UTC => insert in update_array
                    $bookly_effective_date = convertTimeZoneToUTC($bookly_effective_date, $bookly_user_timezone);
                    if( strtotime($appointment_start_date) >= strtotime($bookly_effective_date) ):
                        $appointment_ids_to_update[$appointment_start_date] = $appointment_id;
                    endif;

                endif;


            endforeach;

            if( !empty($appointment_ids_to_update) ):
                // update staff_id in ( appt_ids )
                $appointment_ids_to_update = implode(' , ', $appointment_ids_to_update);
                $appointment_ids_updated = $wpdb->query(
                    "UPDATE $bookly_appointments_table SET staff_id = {$bookly_teacher_id} WHERE id IN ({$appointment_ids_to_update})"
                );
                //echo $appointment_ids_updated; // will output 0 (actually $num_rows)
            endif;

            // for each schedule row, create new gf schedule entry and assign teacher to new one

        endif;

        // update SP gf entry xTeacher with $old_teacher_id
        if( ! updateGFentryxTeacher( $gf_sp_entry_id, $old_teacher_id ) ):
            $catch_error .= 'Error: in updating xTeacher for schedule entry: ' . $stored_schedule_entry_id .' <br>';
        endif;

        // update SP gf entry Teacher with $bookly_teacher_id
        if( ! updateGFentryTeacher( $gf_sp_entry_id, $bookly_teacher_id ) ):
            $catch_error .= 'Error: in updating Teacher for schedule entry: ' . $stored_schedule_entry_id .' <br>';
        endif;

        // if group type = mvs, msl_update_zoom_meeting
        if( !empty($zoom_meeting_id) && $program_type == 'mvs' ):
            // get wp_user_id for teacher_id from bookly_staff table
            $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
            $wp_teacher_id = $wpdb->get_results(
                "SELECT wp_user_id FROM $bookly_staff_table WHERE id = {$bookly_teacher_id}"
            );
            $wpdb->flush();
            if( !empty($wp_teacher_id) ):
                $teacher_wp_user_id = $wp_teacher_id[0]->wp_user_id;
                $user_obj = get_user_by('id', $teacher_wp_user_id);
                $teacher_email = $user_obj->data->user_email;
                $msl_update_zoom_meeting = msl_update_zoom_meeting($zoom_meeting_id, $teacher_email);

                if( $msl_update_zoom_meeting == false ):
                    $catch_error .= "Error: in updating zoom meeting id: $zoom_meeting_id for teacher: $teacher_email";
                endif;
            else:
                $catch_error .= "Error: teacher: $bookly_teacher_id has no wp user id. <br>";
            endif;

        elseif (empty($zoom_meeting_id) && $program_type == 'mvs' ):
            $catch_error .= 'Error: missing zoom meeting id. <br>';
        endif;


        if( empty($catch_error) ):
            // update childs parents parent stats table
            if( !empty($parent_ids) ):
                $parent_ids = array_unique($parent_ids);
                foreach ( $parent_ids as $parent_id ):
                    updateUserBillingIndicator($parent_id, '');
                endforeach;
            endif;
            echo 'status transfer ok';
        else:
            echo $catch_error;
        endif;
        wp_die();

    elseif ( $edit_option === 'cancel' ): // ending program permanently

        // convert $bookly_effective_date from user timezone to UTC
        $bookly_effective_date = convertTimeZoneToUTC($bookly_effective_date, $bookly_user_timezone);

        // update Effective Date => End Date for each schedule entry for only entries with no end_date value
        foreach ( $stored_schedule_entry_ids as $i=>$stored_schedule_entry_id ):
            // check end_date first
            $end_date_meta = getGFentryMetaValue($stored_schedule_entry_id, 8); // get end_date value

            if( empty($end_date_meta) ):

                // update end date for gf entries with empty end date value
                if( ! updateGFentryEndDate( $stored_schedule_entry_id,  date('Y-m-d', strtotime($bookly_effective_date) ) ) ):
                    $catch_error .= 'Error: in updating end date for schedule entry: ' . $stored_schedule_entry_id .' <br>';
                endif;

            endif;
        endforeach;


        // Remove Appointments for BB group id => Only for approved status
        // if approved and makeup flag true => delete and return minutes to balance => store in makeup log table

        if( !empty($bb_group_id) ):
            $bookly_effective_date = date('Y-m-d H:i:s', strtotime($bookly_effective_date));

            if( ! deleteBooklyAppointmentsInCancelMode($bb_group_id, $bookly_effective_date) ):
                $catch_error .= 'Error in deleting bookly appointmnets for series id: ' . $bookly_series_id . '<br>';
            endif;
        endif;


        // if group type mvs and check if he has any other mvs groups => remove learner from mvs group and remove role student
        // get mvs group id
        if( !empty(BP_Groups_Group::get_id_from_slug('mvs')) ):
            $mvs_parent_group_id = BP_Groups_Group::get_id_from_slug('mvs');
        endif;

        if( !is_array($bookly_student_ids) ):
            $bookly_student_ids = array($bookly_student_ids);
        endif;

        foreach ( $bookly_student_ids as $bookly_student_id ):
            // if group type = mvs => add_role 'student' and join mvs group
            if( !empty($mvs_parent_group_id) && getBBgroupType($bb_group_id) == 'mvs' && checkIflearnerHasMvs($bookly_student_id) == false ):
                // remove member from 'mvs' bb group
                if( ! groups_leave_group( $mvs_parent_group_id, $bookly_student_id ) ):
                    $catch_error .= 'Error in removing learner(s) ' . $bookly_student_id . ' from parent MVS group.';
                endif;

                // remove role 'student'
                $user = get_user_by( 'id', $bookly_student_id );
                $user->remove_role('student');
                $user = null;

            endif;
        endforeach;



        if( empty($catch_error) ):
            // set sp entry id Status => Inactive
            if( ! updateGFentryProgramStatus( $gf_sp_entry_id,  'Inactive' ) ):
                echo 'Error: in updating program status for SP entry: ' . $stored_schedule_entry_id .' <br>';
            endif;


            // update childs parents parent stats table
            if( !empty($parent_ids) ):
                $parent_ids = array_unique($parent_ids);
                foreach ( $parent_ids as $parent_id ):
                    updateUserBillingIndicator($parent_id, '');
                endforeach;
            endif;
            echo 'status delete ok';
        else:
            echo $catch_error;
        endif;
        wp_die();
    endif;




    // get week number and year number to generate effective dates for each row
    $effective_week_number = date("W", strtotime($bookly_effective_date));
    $effective_year_number = date('Y', strtotime($bookly_effective_date));
    $effective_month_number = (int) date('m', strtotime($bookly_effective_date));



    // fix wrong week number if in day in last week of previous year
    if( $effective_month_number === 1 && (int) $effective_week_number > 50):
        $effective_week_number = 1;
    endif;

    $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    // calculate units count from class duration
    foreach ( $bookly_class_duration as $duration ):
        $units[] = ( (int) $duration ) / 15 ;
    endforeach;


    for ( $i=0; $i<count($bookly_start_hours); $i++):
        $bookly_end_minutes[] = convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['minutes'];
        $bookly_end_hours[] = (int) $bookly_start_hours[$i] + convertToHoursMins( (int) $bookly_start_minutes[$i] + (int) $bookly_class_duration[$i] )['hours'];
        $string_start_date[] = strtotime( $bookly_effective_date . ' ' . (int) $bookly_start_hours[$i] . ':' . (int) $bookly_start_minutes[$i] ); // mm/dd/yyyy H:m
    endfor;

    foreach ( $string_start_date as $start_date ):
        $booking_user_start_date[] = convertTimeZone( date ("Y-m-d H:i:s", $start_date), $bookly_user_timezone);
    endforeach;


    for( $i=0; $i<count($bookly_end_hours); $i++ ):
        if( $bookly_end_hours[$i] == 24 ){
            $string_end_date = strtotime( $bookly_effective_date . ' 23:59:00'  ); // mm/dd/yyyy H:m
        } else {
            $string_end_date = strtotime( $bookly_effective_date . ' ' . $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] ); // mm/dd/yyyy H:m
        }
        $booking_user_end_date[] = convertTimeZone( date ("Y-m-d H:i:s", $string_end_date), $bookly_user_timezone );
    endfor;


    for ( $i=0; $i<count($bookly_class_days); $i++ ):
        $class_day_name[] = implode( ', ', $bookly_class_days[$i]);
    endfor;

    for( $i=0; $i<count($class_day_name); $i++ ):

        $schedule_name[] = $class_day_name[$i] . ' - ' . date('h:i A', strtotime($booking_user_start_date[$i]) );

    endfor;



    // get new effective start and end dates for each booking row
    $effective_day_index = array_search($bookly_effective_day, WEEK_DAYS_INDEX);
    for( $i=0; $i<count($bookly_class_days); $i++ ):
        if( $bookly_end_hours[$i] >= 24 ){
            $bookly_end_hours[$i] = 23;
            $bookly_end_minutes[$i] = 59;
        }
        foreach( $bookly_class_days[$i] as $bookly_class_day ):
            $day_index = array_search($bookly_class_day, WEEK_DAYS_INDEX);
            $week_day_index = array_search($bookly_class_day, SUN_WEEK_DAYS_INDEX);
            $gendate = new DateTime();
            $gendate->setISODate($effective_year_number,$effective_week_number,$week_day_index); //year , week num , day
            $row_effective_start_dates[$i][] = $gendate->format('Y-m-d '. $bookly_start_hours[$i] . ':' . $bookly_start_minutes[$i] . ':00');
            $row_effective_end_dates[$i][] = $gendate->format('Y-m-d '. $bookly_end_hours[$i] . ':' . $bookly_end_minutes[$i] . ':00');

        endforeach;
    endfor;



    // check if effective date has days before it in schedule, and get previous dates to skip from recurring
    $bookly_effective_day_index =  array_search($bookly_effective_day, WEEK_DAYS_INDEX);

    foreach ( $row_effective_start_dates[0] as $row_effective_start_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_start_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_start_previous_days[] = $row_effective_start_date;
        }
    endforeach;

    foreach ( $row_effective_end_dates[0] as $row_effective_end_date ):
        $class_day_name = strtolower( date('D', strtotime($row_effective_end_date)) );
        $class_day_index = array_search($class_day_name, WEEK_DAYS_INDEX);
        if( $class_day_index < $bookly_effective_day_index ){
            $skip_end_previous_days[] = $row_effective_end_date;
        }
    endforeach;

    // get recurring days for each row_start_date
    // get recurring days for each row_start_date
    // get current month => add 2 months => $calculated_end_date = end of following month => $end_date_to_regenerate = $calculated_end_date
    // 28/8/2022 => 28/10/2022 => $calculated_end_date = 31/10/2022
    $current_date_to_compare = date('Y-m', strtotime($created_at)) . '-01';
    $calculated_end_date = date('Y-m-d', strtotime($current_date_to_compare . ' +3 months - 1 day'));

    for( $i=0; $i<count($row_effective_start_dates); $i++ ):

        foreach ( $row_effective_start_dates[$i] as $row_effective_start_date ):
            // get reccurring dates for each start and end date
//            $recurringDatesStartArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_start_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesStartArray = getReccurringDatesUntilinTimezone(  date('m/d/Y H:i:s', strtotime($row_effective_start_date)), $calculated_end_date,'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesStartArray[] = convertTimeZoneToUTC($row_effective_start_date, $bookly_user_timezone);
            $rowReccurringStartDates[$i][] = $recurringDatesStartArray;
        endforeach;

        foreach ( $row_effective_end_dates[$i] as $row_effective_end_date ):
            // get reccurring dates for each start and end date
//            $recurringDatesEndArray = getReccurringDates( date('m/d/Y H:i:s', strtotime($row_effective_end_date)) ,500, 'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesEndArray = getReccurringDatesUntilinTimezone(  date('m/d/Y H:i:s', strtotime($row_effective_end_date)), $calculated_end_date,'Y-m-d H:i:s', $bookly_user_timezone);
            $recurringDatesEndArray[] = convertTimeZoneToUTC($row_effective_end_date, $bookly_user_timezone);
            $rowReccurringEndDates[$i][] = $recurringDatesEndArray;
        endforeach;

    endfor;






    // skip previous dates from recurring start for booking row 1 only
    foreach ( $rowReccurringStartDates as $key=>$rowReccurringStartDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringStartDate as $i=>$rowReccurringStartDatecheck ):
            foreach( $rowReccurringStartDatecheck as $x=>$start_date_value ):
                if( strtotime($bookly_effective_date) > strtotime($start_date_value) ):
                    unset( $rowReccurringStartDates[$key][$i][$x] );
                endif;
            endforeach;
        endforeach;

    endforeach;



    // skip previous dates from recurring end for booking row 1 only
    foreach ( $rowReccurringEndDates as $key=>$rowReccurringEndDate):
        // loop for each class date
        // check if date in skip
        foreach ( $rowReccurringEndDate as $i=>$rowReccurringEndDatecheck ):
            foreach ( $rowReccurringEndDatecheck as $x=>$end_date_value ):
                if( strtotime($bookly_effective_date) > strtotime($end_date_value) ):
                    unset( $rowReccurringEndDates[$key][$i][$x] );
                endif;
            endforeach;
        endforeach;
    endforeach;




    foreach ( $row_effective_start_dates as $key=>$row_effective_start_date ):
        foreach ( $row_effective_start_date as $date ):
            $input_date[$key][] = date( 'Y-m-d', strtotime($date));
        endforeach;
    endforeach;

    foreach ( $bookly_class_days as $key=>$bookly_class_day ):
        foreach ( $bookly_class_day as $day ):
            $day_index = "input_1." . array_search($day, WEEK_DAYS_INDEX);
            $schedule_days_input[$key][$day_index] = $day;
        endforeach;
    endforeach;


    // create one social group and learan dash group
    $user_ID = get_current_user_id();
    $organizer_user = get_user_by( 'email', 'academy@muslimeto.com' );
    $organizer_user_id = $organizer_user->ID;
    $group_name = $bookly_service_name;
    $group_description = $bookly_service_name . ' - ' . ' ( ' . implode(' * ', $schedule_name) . ' ) ' ;


    $bb_group_args = array(
        'group_id' => 0,
        'creator_id' => $organizer_user_id,
        'name' => $group_name,
        'description' => $group_description,
        'slug' => 'test-from-back',
        'status' => 'private',
    );


    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];

    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            )
        )
    );

    if( !empty($bb_group_id) ):

        // join students as members
        foreach ( $bookly_student_ids as $bookly_student_id ):
            if( ! groups_join_group( $bb_group_id, $bookly_student_id ) ): // groups_join_group( int $group_id, int $user_id )
                $catch_error .= 'Error in join learner(s) ' . $bookly_student_id . ' as bb groub member.<br>';
            endif;
        endforeach;

        // join student as moderator
        // get wp_user_id for teacher_id from bookly_staff table
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $wp_teacher_id = $wpdb->get_results(
            "SELECT wp_user_id FROM $bookly_staff_table WHERE id = {$bookly_teacher_id}"
        );
        $wpdb->flush();
        if( !empty($wp_teacher_id) ):
            $wp_teacher_id = $wp_teacher_id[0]->wp_user_id;
            if( ! groups_join_group( $bb_group_id, $wp_teacher_id ) ): // groups_join_group( int $group_id, int $user_id )
                $catch_error .= 'Error in joining Teacher in BB group<br>';
            endif;

            //update group moderator
            $bb_groups_members_table = $wpdb->prefix . 'bp_groups_members';
            $update_sql ="UPDATE $bb_groups_members_table
            SET `is_mod`= '1',
            `date_modified` = '".$created_at."'
            WHERE  `user_id` = '". $wp_teacher_id ."' AND `group_id` = '". $bb_group_id ."' ";

            $update_moderator = $wpdb->query($update_sql);
            if( $update_moderator !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating group moderator'. $wpdb->print_error();
            endif;


        endif;


        // update single form entry with new data


        // create appointments records
        for( $i=0; $i<count($rowReccurringStartDates); $i++ ):
            //echo '------------------ Row ' . $i . ' ----------------------- <br>';
            // for each booking row create new series id
            // insert new record in bookly series table
            $bookly_series_table = $wpdb->prefix . 'bookly_series';

            $insert_new_series = $wpdb->insert($bookly_series_table,
                array(
                    'repeat' => '',
                    'token' => generateUniqueToken(),
                )
            );

            if( $insert_new_series ):
                // get last series id from bookly_series to attach to new schedule
                $series_results = $wpdb->get_results(
                    "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
                );
                $wpdb->flush();

                if( !empty($series_results) ):
                    $series_id = (int) $series_results[0]->id;
                else:
                    $catch_error .= 'Error: bookly series id not found<br>';
                endif;

                // insert Gravity Form entry for Single Program
                $schedules_url = rest_url( 'gf/v2/forms/'. SCHEDULE_FORM_ID() .'/submissions' );
                $schedule_form_data = array(
                    "input_7" => $series_id,
                    "input_6" => $bookly_class_duration[$i],
                    "input_5" => date('H:i', strtotime( $row_effective_start_dates[$i][0] )),
                    "input_3" => serialize($input_date[$i]),
                    "input_9" => $bookly_effective_date,
                    "field_values" => "",
                    "source_page" => 1,
                    "target_page" =>  1
                );

                $schedule_form_data = array_merge($schedule_days_input[$i], $schedule_form_data);

                $schedule_response = wp_remote_post( $schedules_url, array(
                    'body'    => $schedule_form_data,
                    'headers' => REST_HEADERS(),
                ) );

                // Check the response code.
                if ( empty( wp_remote_retrieve_body( $schedule_response ) ) ) {
                    // If not a 200, HTTP request failed.
                    $catch_error .= 'Error in inserting Schedule(s) gravity form entry. There was an error attempting to access the API.<br>';
                } else {
                    $schedule_form_entry_status = json_decode($schedule_response['body'])->is_valid;
                }

                if( $schedule_form_entry_status !== true && !empty( $schedule_form_entry_status )):
                    $catch_error .= 'Error: Schedule Entry inserting is not Completed<br>';
                else:
                    // change status to sp entry to approved
                    $schedule_url_entries = rest_url( 'gf/v2/entries/?form_ids[0]='. SCHEDULE_FORM_ID() . '&sorting[key]=id&sorting[direction]=DESC&sorting[is_numeric]=true&paging[page_size]=1' );
                    $schedule_entries_response = wp_remote_get( $schedule_url_entries, array(
                        'headers' => REST_HEADERS(),
                    ) );

                    $schedule_last_entry_obj =json_decode($schedule_entries_response['body']);
                    if( !empty($schedule_last_entry_obj) ):
                        $schedule_last_entry_id = $schedule_last_entry_obj->entries[0]->id;
                        $schedule_last_entry_ids[] = $schedule_last_entry_id;

                        // approve entry
//                        if( ! gform_update_meta( $schedule_last_entry_id, 'is_approved', 1 ) ):
//                            $catch_error .= 'Error in updating Schedule(s) form as Approved<br>';
//                        endif;

                        // update created by current user
                        if ( ! setGFentryCreatedBy($schedule_last_entry_id, get_current_user_id() ) ):
                            $catch_error .= 'Error: in update Schedule GF entry Created By';
                        endif;

                        // link entry to SP parent entry
                        $parent_meta_key = 'workflow_parent_form_id_' . SP_PARENT_FORM_ID() . '_entry_id';
                        if( ! gform_add_meta($schedule_last_entry_id, $parent_meta_key, $gf_sp_entry_id) ):
                            $catch_error .= 'Error in linking Single program form entry to Schedule(s) entry. <br>';
                        endif;

                        // update teacher id in new gf schedule entry
                        if( ! updateGFentryTeacherId($schedule_last_entry_id, $bookly_teacher_id) ):
                            $catch_error .= 'Error in updating teacher in schedule entry id: ' . $stored_schedule_entry_id . '<br>';
                        endif;

                    else:
                        $catch_error .= 'Error: in getting last entry for Schedule(s) Form<br>';
                    endif;

                endif;


            else:
                $wpdb->show_errors();
                $catch_error .= 'Error: insert new bookly series record. ' . $wpdb->last_error.'<br>';
            endif;

            for( $x=0; $x<count($rowReccurringStartDates[$i]); $x++ ):
                //echo '==== Day ' . $x .' ====<br>';
                for( $d=1; $d<=count($rowReccurringStartDates[$i][$x]); $d++ ):
                    //echo 'start: ' . $rowReccurringStartDates[$i][$x][$d]. ' ---  end: ' . $rowReccurringEndDates[$i][$x][$d] .'<br>';

                    $booking_start_date = $rowReccurringStartDates[$i][$x][$d];
                    $booking_end_date   = $rowReccurringEndDates[$i][$x][$d];

                    // for each class_day in row get recurring dates ( start and end ) -> create appointment record
                    // insert appointments into bookly_appointments table
                    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
                    $appointments = array(
                        array(
                            'staff_id' => $bookly_teacher_id,
                            'staff_any' => 0,
                            'service_id' => $bookly_service_id,
                            'start_date' => $booking_start_date,
                            'end_date' => $booking_end_date,
                            'extras_duration' => 0,
                            'internal_note' => '',
                            'created_from' => 'bookly',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ),
                    );


                    if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1 ):
                        //if record true, get appointment id
                        $appointment_id = $wpdb->insert_id;

                        // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
                        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

                        foreach ( $bookly_customer_ids as $key=>$bookly_customer_id ):

                            $customer_appointments_array[$key] = array(
                                'series_id' => $series_id,
                                'customer_id' => $bookly_customer_id,
                                'appointment_id' => $appointment_id,
                                'number_of_persons' => 1,//count( $bookly_customer_ids ),
                                'units' => $units[$i],
                                'extras' => [],
                                'extras_multiply_nop' => 1,
                                'extras_consider_duration' => 1,
                                'custom_fields' => $custom_fields,
                                'status' => 'approved',
                                'token' => generateUniqueToken(),
                                'time_zone' => 'UTC',
                                'time_zone_offset' => 0,
                                'created_from' => 'frontend',
                                'created_at' => $created_at,
                                'updated_at' => $created_at
                            );

                        endforeach;

                        $customer_appointments = $customer_appointments_array;

                        if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments) !== 1 ):
                            // do nothing, insert success
                            //$catch_error .= 'Error in inserting customer appointment <br>';
                        endif;
                    else:
                        $wpdb->show_errors();
                        $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
                    endif;

                endfor;
            endfor;
        endfor;

    else:
        $catch_error .= 'Error creating BB group<br>';
    endif;

    if( empty($catch_error) ):
        // update childs parents parent stats table
        if( !empty($parent_ids) ):
            $parent_ids = array_unique($parent_ids);
            foreach ( $parent_ids as $parent_id ):
                updateUserBillingIndicator($parent_id, '');
            endforeach;
        endif;
        echo 'status ok';
    else:
        echo $catch_error;
    endif;

    wp_die();
}



add_action('wp_ajax_fix_appointments_timezone', 'fix_appointments_timezone');
add_action( 'wp_ajax_nopriv_fix_appointments_timezone', 'fix_appointments_timezone' );
function fix_appointments_timezone() {
    $staff_id = $_POST['staff_id'];

    global $wpdb;
    $catch_error = '';
    // get all teacher sp entries
    $schedule_form_id = SCHEDULE_FORM_ID();
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_entry_meta_table = $wpdb->prefix . 'gf_entry_meta';
    $gf_meta_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '8' AND meta_value={$staff_id} AND form_id ={$SP_PARENT_FORM_ID}"
    );
    $wpdb->flush();
    if( !empty($gf_meta_results) ):
        foreach ( $gf_meta_results as $gf_meta_result ):
            $sp_entry_id = $gf_meta_result->entry_id;
            $schedule_entry_ids = getScheduleEntryID($sp_entry_id);
            foreach ( $schedule_entry_ids as $schedule_entry_id ):
                // run fix start time
                $fix_schedule_start_time = fixGFStartTimeforStaff($schedule_entry_id);
                if( ! $fix_schedule_start_time ):
                    $catch_error .= 'Error: in fixing start time for schedule entry id: ' . $schedule_entry_id . '<br>';
                endif;

            endforeach;
        endforeach;
    else:
        $catch_error .= 'Error: teacher has no SP entries <br>';
    endif;


    $regenerate_appts = regenerateSchedulefromGFentries($staff_id);
    if( $regenerate_appts === true && empty($catch_error) ):
        echo 'true';
    else:
        echo $regenerate_appts . '<br>' . $catch_error;
    endif;

    wp_die();
}

function fixGFStartTimeforStaff($schedule_entry_id) {
    // loop all entries in schedule form
    $schedule_form_id = SCHEDULE_FORM_ID();
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $parent_meta_key = 'workflow_parent_form_id_'. $SP_PARENT_FORM_ID .'_entry_id';
    global $wpdb;
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_entry_meta_table = $wpdb->prefix . 'gf_entry_meta';

    $catch_error = '';

    // get start time for schedule entry id
    $start_time = getStartTime($schedule_entry_id);

    // get sp_entry_id for each schedule entry
    $gf_meta_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '{$parent_meta_key}' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
    );
    $wpdb->flush();
    if( !empty( $gf_meta_results) ):
        foreach ($gf_meta_results as $meta_result):
            if( !empty( $meta_result->meta_value ) ):
                $sp_entry_id = $meta_result->meta_value;
            else:
                //$catch_error .= 'Error: no timezone set for GF sp entry id: ' . $sp_entry_id .'<br>';
            endif;
        endforeach;
        // get timezone for sp_entry_id
        $gf_timezone = getSPentryTimezone($sp_entry_id);
    endif;

    // convert start time from EST to sp_timezone
    $start_time_date = '2022-03-01 ' . $start_time;
    $converted_start_time = convertTimezone1ToTimezone2 ( $start_time_date, 'America/New_York', $gf_timezone );
    $converted_start_time = date('H:i', strtotime($converted_start_time));

    //echo 'Before: ' . $schedule_entry_id . ' -- ' . $start_time . ' -- EST' . '<br>';
    //echo 'Afetr: ' . $schedule_entry_id . ' -- ' . $converted_start_time . ' -- ' . $gf_timezone . '<br>';

    // update schedule_entry_id with new start time ( converted )
    $gf_new_meta_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '5' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
    );
    $wpdb->flush();
    $id_to_update = $gf_new_meta_results[0]->id;

    // get original start time
    $gf_original_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '11' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
    );
    $wpdb->flush();

    if( empty( $gf_original_results ) ):
        // original is empty , clone start to original
        gform_update_meta( $schedule_entry_id, 11, $start_time );
        if( $start_time !== $converted_start_time ):
            if( $wpdb->update($gf_entry_meta_table, array('meta_value'=>$converted_start_time), array('id'=>$id_to_update)) !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating converted start time for schedule entry id: ' . $schedule_entry_id . '<br>' .$wpdb->print_error();
            endif;
        endif;
    else:
        // regard original as EST and convert it again and update
        $original_id_to_update = $gf_original_results[0]->id;


        $original_start_time = $gf_original_results[0]->meta_value;
        // convert start time from EST to sp_timezone
        $start_time_date = '2022-03-01 ' . $original_start_time;
        $converted_start_time = convertTimezone1ToTimezone2 ( $start_time_date, 'America/New_York', $gf_timezone );
        $converted_start_time = date('H:i', strtotime($converted_start_time));
        if( $start_time !== $converted_start_time ):
            if( $wpdb->update($gf_entry_meta_table, array('meta_value'=>$converted_start_time), array('id'=>$id_to_update)) !== 1 ):
                $wpdb->show_errors();
                $catch_error .= 'Error: in updating converted start time for schedule entry id: ' . $schedule_entry_id . '<br>' .$wpdb->print_error();
            endif;
        endif;

    endif;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        return true;
    endif;
    wp_die();
}

// fucntion to fix start time stored in gf schedule form to be converted from EST to customer time zone
function fixGFStartTime() {
    // loop all entries in schedule form
    $schedule_form_id = SCHEDULE_FORM_ID();
    $SP_PARENT_FORM_ID = SP_PARENT_FORM_ID();
    $parent_meta_key = 'workflow_parent_form_id_'. $SP_PARENT_FORM_ID .'_entry_id';
    global $wpdb;
    $gf_entry_table = $wpdb->prefix . 'gf_entry';
    $gf_entry_meta_table = $wpdb->prefix . 'gf_entry_meta';
    // get all teachers
    $gf_results = $wpdb->get_results(
        "SELECT * FROM $gf_entry_table WHERE form_id = {$schedule_form_id} AND status = 'active'"
    );
    $wpdb->flush();
    $catch_error = '';
    if( !empty($gf_results) ):
        foreach ( $gf_results as $gf_result ):
            $schedule_entry_id = $gf_result->id;

            // get start time for schedule entry id
            $start_time = getStartTime($schedule_entry_id);

            // get sp_entry_id for each schedule entry
            $gf_meta_results = $wpdb->get_results(
                "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '{$parent_meta_key}' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
            );
            $wpdb->flush();
            if( !empty( $gf_meta_results) ):
                foreach ($gf_meta_results as $meta_result):
                    if( !empty( $meta_result->meta_value ) ):
                        $sp_entry_id = $meta_result->meta_value;
                    else:
                        //$catch_error .= 'Error: no timezone set for GF sp entry id: ' . $sp_entry_id .'<br>';
                    endif;
                endforeach;
                // get timezone for sp_entry_id
                $gf_timezone = getSPentryTimezone($sp_entry_id);
            endif;

            // convert start time from EST to sp_timezone
            $start_time_date = '2022-03-01 ' . $start_time;
            $converted_start_time = convertTimezone1ToTimezone2 ( $start_time_date, 'America/New_York', $gf_timezone );
            $converted_start_time = date('H:i', strtotime($converted_start_time));

//            echo 'Before: ' . $schedule_entry_id . ' -- ' . $start_time . ' -- EST' . '<br>';
//            echo 'Afetr: ' . $schedule_entry_id . ' -- ' . $converted_start_time . ' -- ' . $gf_timezone . '<br>';

            // update schedule_entry_id with new start time ( converted )
            $gf_new_meta_results = $wpdb->get_results(
                "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '5' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
            );
            $wpdb->flush();
            $id_to_update = $gf_new_meta_results[0]->id;

            // get original start time
            $gf_original_results = $wpdb->get_results(
                "SELECT * FROM $gf_entry_meta_table WHERE meta_key = '11' AND entry_id = {$schedule_entry_id} AND form_id ={$schedule_form_id}"
            );
            $wpdb->flush();

            if( empty( $gf_original_results ) ):
                // original is empty , clone start to original
                gform_update_meta( $schedule_entry_id, 11, $start_time );
                if( $start_time !== $converted_start_time ):
                    if( $wpdb->update($gf_entry_meta_table, array('meta_value'=>$converted_start_time), array('id'=>$id_to_update)) !== 1 ):
                        $wpdb->show_errors();
                        $catch_error .= 'Error: in updating converted start time for schedule entry id: ' . $schedule_entry_id . '<br>' .$wpdb->print_error();
                    endif;
                endif;
            else:
                // regard original as EST and convert it again and update
                $original_id_to_update = $gf_original_results[0]->id;


                $original_start_time = $gf_original_results[0]->meta_value;
                // convert start time from EST to sp_timezone
                $start_time_date = '2022-03-01 ' . $original_start_time;
                $converted_start_time = convertTimezone1ToTimezone2 ( $start_time_date, 'America/New_York', $gf_timezone );
                $converted_start_time = date('H:i', strtotime($converted_start_time));
                if( $start_time !== $converted_start_time ):
                    if( $wpdb->update($gf_entry_meta_table, array('meta_value'=>$converted_start_time), array('id'=>$id_to_update)) !== 1 ):
                        $wpdb->show_errors();
                        $catch_error .= 'Error: in updating converted start time for schedule entry id: ' . $schedule_entry_id . '<br>' .$wpdb->print_error();
                    endif;
                endif;

            endif;




        endforeach;
    endif;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'Success: Start Time Updates';
    endif;
    wp_die();
}
add_action('wp_ajax_fixGFStartTime', 'fixGFStartTime');
add_action( 'wp_ajax_nopriv_fixGFStartTime', 'fixGFStartTime' );


// ajax to fix missing GF learners entries
function fix_missing_learners_entries() {
    // get all groups count
    $index = $_POST['index'];
    $last_records = ( ( ( $index-1) * 50 ) + 1 );
    $next_records = ( $index * 50 );

    echo '<br> fixing from: ' . $last_records . ' - to: ' . $next_records . '<br>';


    $catch_error = '';
    global $wpdb;
    $bb_groups_table = $wpdb->prefix . 'bp_groups';
    // get all teachers
    $bb_groups_results = $wpdb->get_results(
        "SELECT * FROM $bb_groups_table WHERE id >= {$last_records} AND id <= {$next_records}"
    );
    $wpdb->flush();
    if( empty($bb_groups_results) ) $catch_error .= 'Error no bb groups found.';

    $bb_group_ids = array_column($bb_groups_results, 'id');


    foreach ( $bb_groups_results as $bb_groups_result ):
        $bb_group_id = $bb_groups_result->id;
        $bb_group_type = bp_groups_get_group_type($bb_group_id);
        //if( $bb_group_type === 'class' ):
        $fix_learners_entry = fixGFmissingLearnersEntry($bb_group_id);
        if( $fix_learners_entry !== true ):
            $catch_error .= $fix_learners_entry;
        endif;
        //endif;
    endforeach;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'true';
    endif;

    wp_die();
}

add_action('wp_ajax_fix_missing_learners_entries', 'fix_missing_learners_entries');
add_action( 'wp_ajax_nopriv_fix_missing_learners_entries', 'fix_missing_learners_entries' );


/******
 * Ajax action to sync Data for parant stats
 ******/
function sync_single_parent_stats(){
    $wp_user_id = $_POST['wp_user_id'];
    $show_assigned_to_val = $_POST['show_assigned_to_val'];

    updateUserBillingIndicator($wp_user_id, '');
    // get parent stats updated and fetch
    global $wpdb;
    $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
    // get all teachers
    $parent_stats_results = $wpdb->get_results(
        "SELECT * FROM $parent_stats_table WHERE parent_wp_user_id = {$wp_user_id}"
    );
    $wpdb->flush();


    if( !empty($parent_stats_results) ):
        $row_id = $parent_stats_results[0]->id;
        $user_obj = get_user_by('id', $wp_user_id);
        $display_name = $user_obj->display_name;
        $email = $user_obj->user_email;
        $balance_due = $parent_stats_results[0]->due_balance;

        if( $balance_due > 0 ):
            $balance_due = "<strong class='danger'> {$balance_due} </strong>";
        else:
            $balance_due = "<strong> {$balance_due} </strong>";
        endif;

        $non_renewal_indicator = get_field( 'mslm_non_renewal_indicator', 'user_' . $wp_user_id );


        $next_payment = $parent_stats_results[0]->renew_on;
        $last_payment = $parent_stats_results[0]->last_payment;
        $assigned_to = $parent_stats_results[0]->assigned_to;
        $assigned_to_user = get_user_by('id', $assigned_to);
        $active_learners = json_decode($parent_stats_results[0]->active_childs);
        $parent_total_hours = json_decode($parent_stats_results[0]->total_hours);


        $assigned_hours = (float) $parent_total_hours->total_current_hrs;
        $future_hours = (float) $parent_total_hours->total_current_hrs + (float) $parent_total_hours->total_starting_hrs - (float) $parent_total_hours->total_stopping_hrs;
        $paid_hours = (float) $parent_stats_results[0]->paid_hours;


        if( $paid_hours === $future_hours && $paid_hours === $assigned_hours ):
            $paid_status = 'success';
        elseif( $paid_hours !== $assigned_hours && $paid_hours !== $future_hours ):
            $paid_status = 'danger';
        else:
            $paid_status = 'warning';
        endif;


        $updated_at = $parent_stats_results[0]->updated_at;
        $created_at = $parent_stats_results[0]->created_at;
        if( empty($updated_at) ):
            $updated_at = $created_at;
        endif;

        $updated_since = time_elapsed_string($updated_at);

        $support_tickets = json_decode($parent_stats_results[0]->support_tickets);
        if( !empty($support_tickets) ):
            $open_tickets = $support_tickets->Open;
            $closed_tickets = $support_tickets->Closed;
        else:
            $open_tickets = 0;
            $closed_tickets = 0;
        endif;


        $contact_id = wpf_get_contact_id($wp_user_id);
        $keap_link = 'https://mep387.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=' . $contact_id;

        $parent_timezone = get_user_meta($wp_user_id, 'time_zone', true);


        if( empty($parent_timezone) ):
            $converted_time_now = '';
        else:
            $parent_time_now = convertTimezone1ToTimezone2( date('h:i a',  time() ), 'UTC', $parent_timezone );
            $converted_time_now = date('h:i a', strtotime($parent_time_now));
        endif;

        $user_is_hos = user_has_role(get_current_user_id(), 'head_of_support');
        $support_users = get_users( array( 'role__in' => array( 'support' ) ) );

        $tags = wpf_get_tags( $wp_user_id );
        $tag='';
        if(in_array("456", $tags)){$tag.="RC";}
        if(in_array("458", $tags)){$tag.="RC 30";}
        if(in_array("460", $tags)){$tag.="RC 60";}
        if(in_array("462", $tags)){$tag.="RC 90";}
        if(in_array("394", $tags)){$vac=1;}else{$vac=0;}

        ?>

        <td>
            <a href="#" class="parent-name">
                <strong class="panel-toggle"> <?php echo $display_name ; ?> </strong>
                <strong>&nbsp;
                    <span class="sync_user_billing_stats" data-wp-user-id="<?php echo $wp_user_id; ?>"> <i class="bb-icon-sync bb-icon-l"></i> </span>
                    &nbsp; <a href="<?php echo $keap_link; ?>" target="_blank"> <i class="bb-icon-external-link bb-icon-l"></i> </a>
                </strong>
                <p class="size-1"> <?php echo $email; ?>  </p>
            </a>

            <a href="#" data-tooltip="" class="size-1"> <?php echo $open_tickets . ' open - ' . $closed_tickets . ' closed';?> </a>
        </td>
        <td data-tooltip="<?php echo $parent_timezone; ?>"> <strong> <?php echo $converted_time_now; ?> </strong> </td>
        <td> <strong> <?php echo $next_payment; ?> </strong> </td>
        <td> <strong> <?php echo count($active_learners); ?> </strong> </td>
        <td> <strong> <?php echo $assigned_hours; ?> </strong> </td>
        <td> <strong> <?php echo $future_hours; ?> </strong> </td>
        <td> <strong class="<?php echo $paid_status; ?>"> <?php echo !empty($paid_hours) ? $paid_hours : 0; ?> </strong> </td>
        <td> <strong> <?php echo $balance_due; ?> </strong> </td>
        <td> <strong> <?php echo $tag; ?> </strong> </td>
        <td> <strong> <?php echo $last_payment; ?> </strong> </td>
        <td> <strong> <?php echo !empty( $non_renewal_indicator && $non_renewal_indicator === '1' ) ? '<span class="non-renewal"> Yes </span>' : 'No' ; ?> </strong> </td>
        <td> <strong> <?php echo $updated_since; ?> </strong> </td>
        <?php if( $show_assigned_to_val === 'show' ): ?>
        <td>
            <?php
            if( $user_is_hos ): // show select ?>
                <select name="assigned_to" class="select_user_to_assign">
                    <option value="" selected> -- select user to assign --</option>
                    <?php  foreach ( $support_users as $support_user ):
                        if( (int) $assigned_to === (int) $support_user->data->ID ):
                            $user_selected = 'selected';
                        else:
                            $user_selected = '';
                        endif;
                        ?>
                        <option value="<?php echo $support_user->data->ID; ?>" <?php echo $user_selected; ?> > <?php echo $support_user->data->display_name; ?> </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" class="row_id" value="<?php echo $row_id; ?>">
            <?php  else: ?>
                <strong> <?php echo $assigned_to_user->display_name; ?> </strong>
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php

    else:
        echo '<div class="alert"> Error in fetching parent data. </div>';
    endif;

    wp_die();
}

add_action('wp_ajax_sync_single_parent_stats', 'sync_single_parent_stats');
add_action( 'wp_ajax_nopriv_sync_single_parent_stats', 'sync_single_parent_stats' );


/******
 * Ajax action to get child classes
 ******/
function get_child_classes_ajax(){

    $wp_user_id = $_POST['wp_user_id'];
    $child_classes = groups_get_user_groups($wp_user_id);

    if( !empty($child_classes['groups']) ):
        echo '<option selected disabled> Choose ...</option>';
        foreach ( $child_classes['groups'] as &$bb_group_id ):
            $group_obj = groups_get_group($bb_group_id);
            $group_name = bp_get_group_name($group_obj);
            echo "<option value='{$group_name}'> $group_name </option>";
        endforeach;
    else:
        echo '';
    endif;
    wp_die();

}

add_action('wp_ajax_get_child_classes_ajax', 'get_child_classes_ajax');
add_action( 'wp_ajax_nopriv_get_child_classes_ajax', 'get_child_classes_ajax' );


/******
 * Ajax action to get single parent stats
 ******/
function get_single_parent_stats(){

    $wp_user_id = $_POST['wp_user_id'];

    $users_list[] = $wp_user_id;
    $childs = getParentChilds($wp_user_id);
    if( ! empty($childs) ):
        $users_list = array_merge($users_list, $childs);
        foreach ( $users_list as $user_id ):
            $user_id = (int) $user_id;
            $user_groups[$user_id] = groups_get_user_groups($user_id);
        endforeach;
    else:
        $childs = [];
    endif;


    $user_obj = get_user_by('id', $wp_user_id);
    $display_name = $user_obj->display_name;
    $email = $user_obj->user_email;

    echo "<h4> {$display_name} - {$email} </h4>";

    ?>

    <?php get_template_part( 'template-parts/user-profile/template-user-stats', null, array(
            'wp_user_id' => $wp_user_id,
            'renews_on' => null
        )
    ); ?>

    <br>
    <h6> Programs </h6>
    <br>

    <table class="program-data">
        <thead>
        <tr>
            <th> Name </th>
            <th> Status </th>
            <th> Child </th>
            <th> Teacher </th>
            <th> <span data-tooltip="Assigned Hours"> <strong> A. Hrs </strong> </span> <!-- total current hrs  --> </th>
            <th> <span data-tooltip="Future Hours"> <strong> F. Hrs </strong> </span> <!-- total current hrs + total starting hrs - total stopping hrs --> </th>
            <th> Actions </th>
        </tr>
        </thead>
        <tbody>
        <?php
        if( !empty($user_groups) ):
            foreach ( $user_groups as $child_id=>$user_group ):
                if( !empty($user_group['groups']) ):
                    foreach ( $user_group['groups'] as $bb_group_id ):
                        $group_name = getBBgroupName($bb_group_id);
                        $sp_entry_id = getBBgroupGFentryID($bb_group_id);
                        $teacher_id = getSPentryStaffId($sp_entry_id);
                        $teacher_name = getStaffFullName($teacher_id);
                        $program_status_meta = getGFentryMetaValue($sp_entry_id, 26); // get group status
                        if( !empty($program_status_meta) ):
                            $program_status = $program_status_meta[0]->meta_value;
                        else:
                            $program_status = '';
                        endif;

                        $group_obj = groups_get_group ( $bb_group_id );
                        $bb_group_permalink = bp_get_group_permalink( $group_obj );
                        $edit_url = '/edit-program?bb_group_id=' . $bb_group_id;

                        $child_name = getCustomerName($child_id);

                        // get child creds
                        $child_obj = get_user_by('id', $child_id);
                        $child_email = $child_obj->data->user_email;
                        $child_pass = get_field( 'mslm_pw_string', 'user_'.$child_id );
                        $child_creds = $child_email . ' ' . $child_pass;

                        // get group total hours
                        $program_total_hrs = getProgramTotalHours($bb_group_id);
                        $assigned_hours = $program_total_hrs['current_total_hrs'];
                        $future_hours = $program_total_hrs['current_total_hrs'] + $program_total_hrs['starting_total_hrs'] - $program_total_hrs['stopping_total_hrs'];

                        if( $program_status !== 'Inactive' ):
                            ?>

                            <tr>
                                <td data-tooltip="<?php echo $group_name; ?>"> <?php echo substr($group_name,0,10); ?> </td>
                                <td> <?php echo $program_status; ?> </td>
                                <td> <?php echo $child_name; ?> </td>
                                <td data-tooltip="<?php echo $teacher_name; ?>"> <?php echo substr($teacher_name, 0 ,10); ?> </td>
                                <td> <?php echo $assigned_hours; ?> </td>
                                <td> <?php echo $future_hours; ?> </td>
                                <td>
                                    <a href="<?php echo $bb_group_permalink; ?>" target="_blank" class="view-btn" data-balloon-pos="down" data-balloon="View Class"> <i class="bb-icon-eye bb-icon-l"></i> </a>
                                    <a class="edit-btn" href="<?php echo $edit_url; ?>" target="_blank" data-balloon-pos="down" data-balloon="Edit Class"> <i class="bb-icon-edit bb-icon-l"></i> </a>
                                    <a href="#" class="copy-cred" onclick="copyToClipboard('<?= $child_creds ?>')" data-balloon-pos="down" data-balloon="Copy Credentials" > <i class="bb-icon-copy bb-icon-l"></i> </a>
                                </td>
                            </tr>

                        <?php
                        endif;
                    endforeach;
                endif;
            endforeach;

        else:
            echo '<p class="alert"> No groups found for this user. </p>';
        endif;
        ?>
        </tbody>
    </table>

    <?php
    echo '<hr>';
    echo '<h6> Subscriptions </h6>';
    echo '<br>';

    $user_subs = get_active_subs_for_parent_user($wp_user_id);

    if( empty($user_subs['data']) ):
        echo '<p class="alert"> No active subscriptions found. </p>';
    else:

        ?>

        <table class="subcription-data">
            <thead>
            <tr>
                <th> Status </th>
                <th> Qty (hrs) </th>
                <th> Price Per Hr </th>
                <th> Next Bill Date </th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $user_subs['data'] as $sub ):
                if( $sub->active === true):
                    $sub_status = 'Active';
                else:
                    $sub_status = 'Inactive';
                endif;


                ?>

                <tr>
                    <td> <?php echo $sub_status; ?> </td>
                    <td> <?php echo $sub->quantity; ?> </td>
                    <td> $ <?php echo $sub->billing_amount; ?> </td>
                    <td> <?php echo $sub->next_bill_date; ?> </td>
                </tr>

            <?php
            endforeach;
            ?>
            </tbody>
        </table>

    <?php

    endif;


    wp_die();
}

add_action('wp_ajax_get_single_parent_stats', 'get_single_parent_stats');
add_action( 'wp_ajax_nopriv_get_single_parent_stats', 'get_single_parent_stats' );


/******
 * Ajax action to set assigned to parent stats
 ******/
function assign_to_parent_stats(){

    $wp_user_id = $_POST['wp_user_id'];
    $row_id = $_POST['row_id'];
    $catch_error = '';

    $assigned_to = array(
        'id' => $row_id,
        'assigned_to' => $wp_user_id
    );
    // update
    global $wpdb;
    $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';

    if( !empty($row_id) ):
        $update_assigned_to = $wpdb->update($parent_stats_table, array('assigned_to'=>$wp_user_id), array('id'=>$row_id));
        echo 'updated';
//        if( $update_assigned_to !== 1 ):
//            $wpdb->show_errors();
//            $catch_error .= 'Error: in updating assigned to in parent stats table. <br>' .$wpdb->print_error();
//            echo $catch_error;
////        else:
//            echo 'updated';
//        endif;
    else:
        echo 'Error: please select user to assign';
    endif;

    wp_die();
}

add_action('wp_ajax_assign_to_parent_stats', 'assign_to_parent_stats');
add_action( 'wp_ajax_nopriv_assign_to_parent_stats', 'assign_to_parent_stats' );


/******
 * Ajax action to duplicate, delete bookly event on selected date
 ******/
function single_event_action(){


    $single_event_action = $_POST['single_event_action'];
    $appointment_id = $_POST['appointment_id'];
    $bb_group_id = $_POST['bb_group_id'];
    $required_day = $_POST['required_day'];
    $catch_error = '';

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    global $wpdb;
    $bookly_ca_table = $wpdb->prefix . 'bookly_customer_appointments';
    $bookly_appt_table = $wpdb->prefix . 'bookly_appointments';

    $ca_results = $wpdb->get_results(
        "SELECT * FROM $bookly_ca_table WHERE appointment_id = {$appointment_id}"
    );
    $wpdb->flush();

    $appt_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appt_table WHERE id = {$appointment_id}"
    );
    $wpdb->flush();


    if( !empty($single_event_action) && !empty($appointment_id) && !empty($bb_group_id) ):

        // check first type of bb group
        $bb_group_type = getBBgroupType($bb_group_id);



        if( $single_event_action === 'duplicate' && !empty($required_day) ):

            if( !empty($ca_results) || !empty($appt_results) ):
                // get group id from

                // get data from old appointment record , and change only start_date & end_date as date only keep old time as it was
                $stored_start_date = $appt_results[0]->start_date;
                $stored_end_date = $appt_results[0]->end_date;

                if( $required_day === 'next_day' ):
                    $next_start_date = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($stored_start_date)));
                    $next_end_date = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($stored_end_date)));

                    $appt_results[0]->start_date = $next_start_date;
                    $appt_results[0]->end_date = $next_end_date;


                    $new_appintment_data = array(
                        array(
                            'staff_id' => $appt_results[0]->staff_id,
                            'staff_any' => 0,
                            'service_id' => $appt_results[0]->service_id,
                            'start_date' => $next_start_date,
                            'end_date' => $next_end_date,
                            'extras_duration' => 0,
                            'internal_note' => '',
                            'created_from' => 'bookly',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ),
                    );


                else:

                    $prev_start_date = date('Y-m-d H:i:s',strtotime('-1 day',strtotime($stored_start_date)));
                    $prev_end_date = date('Y-m-d H:i:s',strtotime('-1 day',strtotime($stored_end_date)));

                    $appt_results[0]->start_date = $prev_start_date;
                    $appt_results[0]->end_date = $prev_end_date;


                    $new_appintment_data = array(
                        array(
                            'staff_id' => $appt_results[0]->staff_id,
                            'staff_any' => 0,
                            'service_id' => $appt_results[0]->service_id,
                            'start_date' => $prev_start_date,
                            'end_date' => $prev_end_date,
                            'extras_duration' => 0,
                            'internal_note' => '',
                            'created_from' => 'bookly',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ),
                    );

                endif;

                // insert new record in bookly_appt_table
                if( wpdb_bulk_insert($bookly_appt_table, $new_appintment_data) === 1 ):
                    //if record true, get appointment id
                    $new_appointment_id = $wpdb->insert_id;

                    // get data from old customer appointment record , and change
                    //      appointment_id ( get from new insertion ),
                    //      status => approved,
                    //      token => generate new one

                    // insert new record in bookly series table
                    $bookly_series_table = $wpdb->prefix . 'bookly_series';
                    $insert_new_series = $wpdb->insert($bookly_series_table,
                        array(
                            'repeat' => '',
                            'token' => generateUniqueToken(),
                        )
                    );

                    if( $insert_new_series ):
                        // get last series id from bookly_series to attach to new schedule
                        $series_results = $wpdb->get_results(
                            "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
                        );
                        $wpdb->flush();

                        if( !empty($series_results) ):
                            $series_id = (int) $series_results[0]->id;
                        else:
                            $catch_error .= 'Error: bookly series id not found <br>';
                        endif;
                    else:
                        $wpdb->show_errors();
                        $catch_error .= 'Error: insert new bookly series record. ' . $wpdb->last_error.'<br>';
                    endif;

                    $customer_appointments_array = array(
                        array(
                            'series_id' => $series_id,
                            'customer_id' => $ca_results[0]->customer_id,
                            'appointment_id' => $new_appointment_id,
                            'number_of_persons' => $ca_results[0]->number_of_persons, //count( $bookly_customer_ids ),
                            'units' => $ca_results[0]->units,
                            'extras' => [],
                            'extras_multiply_nop' => 1,
                            'extras_consider_duration' => 1,
                            'custom_fields' => $ca_results[0]->custom_fields,
                            'status' => 'approved',
                            'token' => generateUniqueToken(),
                            'time_zone' => $ca_results[0]->time_zone,
                            'time_zone_offset' => $ca_results[0]->time_zone_offset,
                            'created_from' => 'frontend',
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        )
                    );

                    if( wpdb_bulk_insert($bookly_ca_table, $customer_appointments_array) === 1 ):
                        echo 'duplicate_success';
                    else:
                        $wpdb->show_errors();
                        $catch_error .= 'Error in inserting bookly customer appointments table: '.$wpdb->print_error().'<br>';
                    endif;


                else:
                    $wpdb->show_errors();
                    $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
                endif;

            else:
                $catch_error .= 'Error: empty bookly appointment or ca record. <br>';
            endif;

        elseif ( $single_event_action === 'delete' ):
            // check if appointment one-on-one OR group ( has more ca records attached to it )

            if( count($ca_results) > 1 ): // group appointment

            else: // one-on-one delete CA record and from bookly_appt_table
                if( $wpdb->query("DELETE FROM $bookly_appt_table WHERE id = {$appointment_id}") === false ):
                    $catch_error .= 'Error in deleting bookly_appointments_table. <br>';
                endif;
            endif;

            // delete from CA table
            if( $wpdb->query("DELETE FROM $bookly_ca_table WHERE appointment_id = {$appointment_id}") === false ):
                $catch_error .= 'Error in deleting bookly_ca_appointments_table. <br>';
            endif;

            if( empty($catch_error) ):
                $action_result = 'delete_success';
            endif;


        endif;

    else:
        $catch_error .= 'empty-fields <br>';
    endif;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo $action_result;
    endif;

    wp_die();
}

add_action('wp_ajax_single_event_action', 'single_event_action');
add_action( 'wp_ajax_nopriv_single_event_action', 'single_event_action' );


/******
 * Ajax action to duplicate, delete bookly event on selected date
 ******/
function change_user_pass_using_id() {
    $wp_user_id = $_POST['wp_user_id'];
    $user = wp_get_current_user();
    if ( $user && wp_check_password( $_POST['old_pass'], $user->data->user_pass, $user->ID ) ) {
        if( strlen($_POST['new_pass']) < 7){
            $return = array(
                'message'  => 'Password length must be at least 6 characters.',
                'type'       => "warning"
            );
            wp_send_json($return);
        }else{
            wp_set_password( $_POST['new_pass'], $user->ID );
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID );
            // user meta to store password as string
            update_field('mslm_pw_string', $_POST['new_pass'], 'user_'.$user->ID);
            do_action( 'wp_login', $user->user_login, $user );
            $return = array(
                'message'  => 'Your password has been updated.',
                'type'       => "success"
            );
            wp_send_json($return);
        }
    } else {
        $return = array(
            'message'  =>  "Your old password is wrong.",
            'type'       => "error"
        );
        wp_send_json($return);
    }
    exit;
}
add_action('wp_ajax_change_user_pass_using_id', 'change_user_pass_using_id');
add_action( 'wp_ajax_nopriv_change_user_pass_using_id', 'change_user_pass_using_id' );

/******
 * Ajax action to get parent name and email
 ******/
function get_user_name_and_email() {
    $wp_user_id = $_POST['wp_user_id'];
    $user_obj = get_user_by('id', $wp_user_id);
    echo " $user_obj->display_name - $user_obj->user_email";

    wp_die();
}

add_action('wp_ajax_get_user_name_and_email', 'get_user_name_and_email');
add_action( 'wp_ajax_nopriv_get_user_name_and_email', 'get_user_name_and_email' );


/******
 * Ajax action to get parent makeup logs
 ******/
function get_parent_makeup_logs() {
    $wp_user_id = $_POST['wp_user_id'];


    // get parent records from makeup_log table
    global $wpdb;
    $makeup_log_table = $wpdb->prefix . 'muslimeto_makeup_log';

    $makeup_log_results = $wpdb->get_results(
        "SELECT * FROM $makeup_log_table WHERE parent_id = {$wp_user_id} AND trans_type = 'open-balance'"
    );
    $wpdb->flush();

    if (isset($makeup_log_results) && !empty($makeup_log_results)):
        if( $makeup_log_results[0]->trans_type == 'open-balance' ):
            $opening_balance_date = $makeup_log_results[0]->created_at;

            // get all records after opening balance date
            $after_opening_balance_results = $wpdb->get_results(
                "SELECT * FROM $makeup_log_table WHERE parent_id = {$wp_user_id} AND trans_type = 'open-balance'"
            );
            $wpdb->flush();


//        $trans_types = array_map(function ($e) {
//            return is_object($e) ? $e->trans_type : $e['trans_type'];
//        }, $makeup_log_results);
//    if (array_search('open-balance', $trans_types)):

            ?>
            <table class="makeup-shortcode-table">
                <thead>
                <tr>
                    <th> Trans Type </th>
                    <th> Trans Amount </th>
                    <th> User </th>
                    <th> Notes </th>
                    <th> Updated/Created At </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ( $makeup_log_results as &$makeup_log_result ): ?>

                    <tr>
                        <td> <?= $makeup_log_result->trans_type ?> </td>
                        <td> <?= $makeup_log_result->trans_amount ?> </td>
                        <td> <?= $makeup_log_result->user_id ?> </td>
                        <td> <?= $makeup_log_result->trans_notes ?> </td>
                        <td> <?php echo !empty($makeup_log_result->updated_at) ? $makeup_log_result->updated_at : $makeup_log_result->created_at; ?> </td>
                    </tr>

                <?php endforeach; ?>
                </tbody>

            </table>

            <script>
                // makeup shortcode table
                $('.makeup-shortcode-table').dataTable( {
                    "pageLength": 25,
                    "order": [[ 1, "desc" ]],
                    "autoWidth": true,
                });
            </script>

        <?php
        else:
            echo '<div class="alert"> Please review and add the opening balance. </div>';
        endif; ?>
    <?php endif; ?>
    <?php
    wp_die();
}

add_action('wp_ajax_get_parent_makeup_logs', 'get_parent_makeup_logs');
add_action( 'wp_ajax_nopriv_get_parent_makeup_logs', 'get_parent_makeup_logs' );

/******
 * Ajax action to add summer camp zoom meeting id
 ******/
function add_summer_camp_zoom_id() {
    $summer_camp_group_id = $_POST['summer_camp_group_id'];
    $zoom_meeting_id = $_POST['zoom_meeting_id'];
    if( !empty($zoom_meeting_id) && !empty($summer_camp_group_id) ):
        // update group with new meta
        groups_update_groupmeta( $summer_camp_group_id, 'summer_camp_zoom_id', $zoom_meeting_id );
    endif;
    wp_die();
}

add_action('wp_ajax_add_summer_camp_zoom_id', 'add_summer_camp_zoom_id');
add_action( 'wp_ajax_nopriv_add_summer_camp_zoom_id', 'add_summer_camp_zoom_id' );


/******
 * get all parent table with target
 ******/
function get_all_parents_table_target()
{
    $target = $_POST['target'];

    if( !empty($target) ):
        echo do_shortcode('[all_parent_status_table target="'. $target .'"]');
    else:
        echo do_shortcode('[all_parent_status_table]');
    endif;

    exit();
}

// creating Ajax call for WordPress
add_action('wp_ajax_nopriv_get_all_parents_table_target', 'get_all_parents_table_target');
add_action('wp_ajax_get_all_parents_table_target', 'get_all_parents_table_target');


/******
 * Ajax action to update member data on account page
 ******/
function update_member_data() {
    $member_wp_user_id = $_POST['member_wp_user_id'];
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $email = $_POST['email'];

    if( !empty($_POST['new_password']) ):
        $new_password = $_POST['new_password'];
        update_field('mslm_pw_string', $new_password, 'user_'.$member_wp_user_id);
        wp_update_user( array ('ID' => $member_wp_user_id, 'user_pass' => $new_password) ) ;
        echo 'success';
        wp_die();
    endif;

    $user_data = wp_update_user( array( 'ID' => $member_wp_user_id, 'user_email' => $email ) );

    if ( is_wp_error( $user_data ) ) {
        // There was an error; possibly this user doesn't exist.
        foreach ( $user_data->errors as $error):
            echo $error[0] . '<br>';
        endforeach;
    } else {
        // email updated success => update user meta
        $first_name = explode(' ', $full_name)[0];
        $last_name = explode(' ', $full_name)[1];
        update_field('mslm_gender', $gender, 'user_'.$member_wp_user_id);
        update_metadata( 'user', $member_wp_user_id, 'first_name', $first_name );
        update_metadata( 'user', $member_wp_user_id, 'last_name', $last_name );
        wp_update_user( array ('ID' => $member_wp_user_id, 'display_name' => $full_name));
        update_field('mslm_birthday', $date_of_birth, 'user_'.$member_wp_user_id);
        echo 'success';
    }


    wp_die();
}

add_action('wp_ajax_update_member_data', 'update_member_data');
add_action( 'wp_ajax_nopriv_update_member_data', 'update_member_data' );



/******
 * Ajax action to upload member avatar
 ******/
function upload_member_avatar() {
    $wp_user_id = $_POST['wp_user_id'];
    $file = $_FILES;
    $catch_error = '';

    if( empty($file) || empty($wp_user_id) ):
        echo 'Image must be selected';
        wp_die();
    endif;

    $file = $file['member_image_avatar'];
    $file_path = $file['tmp_name'];
    $file_meta = getimagesize($file_path);
    $FILE_EXTENSION = pathinfo($file_path, PATHINFO_EXTENSION);

    if($file_meta !== false){

        // use 'avatar-bp....' instead of default wp_hash($file['name'].time())
        // to avoid having multiple image for each user
        $full_filename  = 'avatar-bpfull.'  . $FILE_EXTENSION;
        $thumb_filename = 'avatar-bpthumb.' . $FILE_EXTENSION;
        $target_dir = wp_get_upload_dir()['basedir'].'/avatars/'.$wp_user_id.'/';

        $source = imagecreatefromstring(file_get_contents($file_path)); // La photo est la source

        $full = imagecreatetruecolor(150, 150);
        $thumb = imagecreatetruecolor(80, 80);

        imagecopyresampled($full, $source, 0, 0, 0, 0, imagesx($full), imagesy($full), imagesx($source), imagesy($source));
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, imagesx($thumb), imagesy($thumb), imagesx($source), imagesy($source));

        if(! imagejpeg($thumb, $target_dir.$thumb_filename.'jpeg') && ! imagejpeg($full, $target_dir.$full_filename.'jpeg')){
            $catch_error .= "Sorry, there was an error uploading your file.";
        }

    }else{
        $catch_error .= 'file is not an image';
    }

    if( empty($catch_error) ):
        echo 'uploaded';
    else:
        echo $catch_error;
    endif;
    wp_die();

}

add_action('wp_ajax_upload_member_avatar', 'upload_member_avatar');
add_action( 'wp_ajax_nopriv_upload_member_avatar', 'upload_member_avatar' );


/******
 * Ajax action to get today classes
 ******/
function get_today_classes() {

    $wp_user_id_ajax = $_POST['wp_user_id_ajax'];

    if( empty($wp_user_id_ajax) ):
        echo 'error';
        wp_die();
    endif;

    // Get  user role
    $user = get_userdata( $wp_user_id_ajax );
    $user_role = $user->roles[0];

    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', $wp_user_id_ajax )->count() > 0 ):
        $user_role = 'teacher';
    endif;

    // get user time zone
    $timezone = getUserTimezone($wp_user_id_ajax);


//    $user_role = 'parent';
//    $timezone = 'Africa/Cairo';
//    $upcoming_classes = array(
//        array(
//            'number' => 1,
//            'name' => 'test',
//            'start' => '2022-10-10 16:30:00',
//            'end' => '2022-10-10 16:40:00',
//            'teacher' => 'ahmed',
//            'learner' => 'hannah / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => 'Attended',
//            'actual_mins' => 10,
//            'late_mins' => 0,
//            'ca_id' => 1378
//        ),
//        array(
//            'number' => 2,
//            'name' => 'test 2',
//            'start' => '2022-10-10 16:50:00',
//            'end' => '2022-10-10 17:00:00',
//            'teacher' => 'mohamed',
//            'learner' => 'hannah / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => 'Student Late',
//            'actual_mins' => 20,
//            'late_mins' => 10
//        ),
//        array(
//            'number' => 3,
//            'name' => 'test 3',
//            'start' => '2022-10-10 17:10:00',
//            'end' => '2022-10-10 17:20:00',
//            'teacher' => 'mohamed',
//            'learner' => 'hannah / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => 'Holiday',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//        array(
//            'number' => 4,
//            'name' => 'test 4',
//            'start' => '2022-10-10 17:30:00',
//            'end' => '2022-10-10 17:40:00',
//            'teacher' => 'mohamed',
//            'learner' => 'hannah / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => '',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//        array(
//            'number' => 5,
//            'name' => 'test 5',
//            'start' => '2022-10-10 17:50:00',
//            'end' => '2022-10-10 18:00:00',
//            'teacher' => 'mohamed',
//            'learner' => 'hannah / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => '',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//        array(
//            'number' => 6,
//            'name' => 'test 6',
//            'start' => '2022-10-10 18:10:00',
//            'end' => '2022-10-10 18:20:00',
//            'teacher' => 'islam',
//            'learner' => 'loai / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => '',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//        array(
//            'number' => 7,
//            'name' => 'test 7',
//            'start' => '2022-10-10 18:30:00',
//            'end' => '2022-10-10 18:40:00',
//            'teacher' => 'islam',
//            'learner' => 'loai / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => '',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//        array(
//            'number' => 8,
//            'name' => 'test 8',
//            'start' => '2022-10-10 18:50:00',
//            'end' => '2022-10-10 19:00:00',
//            'teacher' => 'islam',
//            'learner' => 'loai / loai said',
//            'zoom_join' => 'https://muslimeto.zoom.us/j/84140835587',
//            'status' => '',
//            'actual_mins' => 0,
//            'late_mins' => 0
//        ),
//
//    );

    $upcoming_classes = getUserBBgroupUpcomingClasses($wp_user_id_ajax);

    echo json_encode(
        array(
            'role' => $user_role,
            'timezone' => $timezone,
            'classes' => $upcoming_classes
        )
    );

    wp_die();
}

add_action('wp_ajax_get_today_classes', 'get_today_classes');
add_action( 'wp_ajax_nopriv_get_today_classes', 'get_today_classes' );


/******
 * Ajax action to get session details
 ******/
function get_session_details_for_cancellation() {

    $ca_id = $_POST['ca_id'];
    $wp_user_id = get_current_user_id();

    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    $customer_appointments_results = $wpdb->get_results(
        "SELECT appointment_id, customer_id, custom_fields, time_zone FROM $bookly_customer_appointments_table WHERE id = {$ca_id}"
    );
    $wpdb->flush();
    if( !empty($customer_appointments_results) ):
        $customer_id = $customer_appointments_results[0]->customer_id;
        $wp_tz = $customer_appointments_results[0]->time_zone;
        $appointment_id = $customer_appointments_results[0]->appointment_id;
        $custom_fields = $customer_appointments_results[0]->custom_fields;
        if( !empty($custom_fields) ):
            $bb_group_id_data = json_decode($custom_fields);
            if( !empty($bb_group_id_data) ):
                $bb_group_id = (int) $bb_group_id_data[0]->value;
            else:
                $bb_group_id = false;
            endif;
        else:
            $bb_group_id = false;
        endif;
        // get session start and end time
        global $wpdb;
        $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
        $appointments_results = $wpdb->get_results(
            "SELECT start_date, end_date, staff_id FROM $bookly_appointments_table WHERE id = {$appointment_id}"
        );
        $wpdb->flush();
        if( !empty($appointments_results) ):
            $start_date = $appointments_results[0]->start_date;
            $end_date = $appointments_results[0]->end_date;
            // convert start and end date based on customer timezone
            if( !empty($customer_id) && !empty($bb_group_id)):
                // get timeznoe from single GF entry
                $sp_entry_id = getBBgroupGFentryID ($bb_group_id);
                $user_timezone = getSPentryTimezone($sp_entry_id);
                $start_date_to_display = convertTimezone1ToTimezone2 ( $start_date, $wp_tz, $user_timezone );
                $end_date_to_display = convertTimezone1ToTimezone2 ( $end_date, $wp_tz, $user_timezone );
            endif;

            $date_string = date('l d-m-Y', strtotime($start_date));
            $staff_id = $appointments_results[0]->staff_id;

            $checkSessionCancelTime = checkSessionCancellationDate($start_date);

            // check if learner has +2 cancelled sessions within week
            $weekLimitExceeded = checkIfCancalledExceedLimits(true, $start_date, $customer_id, false, false);

            // check if learner has +2 cancelled sessions within same bb_group_id
            $bbGroupLimitExceeded = checkIfCancalledExceedLimits(false, false, $customer_id, false, $bb_group_id);

            // check if learner has +2 cancelled sessions with same teacher
            $withTeacherLimitExceeded = checkIfCancalledExceedLimits(false, false, $customer_id, $staff_id, false);


            // check if parent cancel at least one hour before session
            if( $checkSessionCancelTime == true  ):
                $check_status = " <strong>Warning! </strong> <br>  You are about to cancel this session, please review before proceeding. <br> This session time will be added to your makeup balance.";
                echo "<input value='add_makeup' id='add_makeup' type='hidden'>";
            else:
                $check_status = '<div class="no-makeup-restore"> <i class="fal fa-exclamation-triangle"></i> <p> The session will start less than one hour , the duration mins will not be restored </p> </div>';
            endif;

            // send notification when cancelled sessions limit exceeds 2 or when before session starts less than an hour
            if( $weekLimitExceeded == true || $bbGroupLimitExceeded == true || $withTeacherLimitExceeded == true ):
                echo "<input value='send_notification' id='send_notification' type='hidden'>";
            endif;


        else:
            echo 'No start/end date found for this session, please contact administrator for help.';
            wp_die();
        endif;



        if( !empty($bb_group_id) ):
            $group_obj = groups_get_group ( $bb_group_id );
            $group_full_title = $group_obj->name;
            if( strlen($group_full_title) > 30 ):
                $bb_group_title = substr($group_full_title, 0, 30) . '...';
            else:
                $bb_group_title = $group_full_title;
            endif;
        else:
            $bb_group_title = '<div class="alert"> No BB group linked.</div>';
        endif;
    else:
        echo 'No data found for this session, please contact administrator for help.';
        wp_die();
    endif;

    // get teacher id from BB group id
    $entry_meta = getGFentryMetaValue($sp_entry_id , 8);
    $staff_id = $entry_meta[0]->meta_value;
    $staff_full_name = getStaffFullName($staff_id );

    ?>

    <div class="header">
        <p> <?php echo date('h:i a', strtotime($start_date_to_display)); ?> to <?php echo date('h:i a', strtotime($end_date_to_display)); ?>, on <?= $date_string ?>  </p>
    </div>

    <div class="d-flex classes-section">

        <div class="classes-select-section">
            <p data-balloon-pos="down" data-balloon="<?= $group_full_title ?>"> <?= $bb_group_title ?>  </p>
        </div>

        <div class="teacher-name-section">
            <span>Teacher: <?= $staff_full_name ?> </span>
        </div>
    </div>

    <div class="child-select-section">
        <label for="">Learner(s) </label>
        <div class="childs_text"> <?= getParentChildsinBBgroupString($wp_user_id, $bb_group_id) ?> </div>
    </div>

    <div class="class-details">

        <div class="footer footer-actions">
            <div class="cancel-message"> <?= $check_status ?> </div>
            <?php if( $checkSessionCancelTime == true ): ?>

                <div class="action-btns">
                    <a href="#" class="reject-cancel-session" data-bs-dismiss="modal">Close</a>
                    <a href="#" class="approve-cancel-session" data-ca-id="<?= $ca_id ?>" data-bb-group-id="<?= $bb_group_id ?>" data-start-date="<?= $start_date ?>" data-end-date="<?= $end_date ?>" >Confirm Cancellation</a>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelector(".approve-cancel-session").addEventListener("click",function(){
            if(document.querySelector(".approve-cancel-session").innerHTML.match(/Are you sure?/gi)){


                document.querySelector("..approve-cancel-session").innerHTML="Confirm Session"
                $(".approve-cancel-session").attr("data-bs-dismiss", '');
                document.querySelector(".approve-cancel-session").classList.remove("clickedRed");





            }else if(document.querySelector(".approve-cancel-session").innerHTML.match(/Confirm Session/gi)){
                document.querySelector(".approve-cancel-session").innerHTML="Are you sure?"
                $(".approve-cancel-session").attr("data-bs-dismiss", 'modal');
                document.querySelector(".approve-cancel-session").classList.add("clickedRed");

                setTimeout(function(){
                    document.querySelector(".approve-cancel-session").innerHTML="Confirm Session"
                    $(".approve-cancel-session").attr("data-bs-dismiss", '');
                    document.querySelector(".approve-cancel-session").classList.remove("clickedRed");


                }, 6000);

            }
        })
    </script>

    <?php

    wp_die();
}

add_action('wp_ajax_get_session_details_for_cancellation', 'get_session_details_for_cancellation');
add_action( 'wp_ajax_nopriv_get_session_details_for_cancellation', 'get_session_details_for_cancellation' );


/******
 * Ajax action to process session cancellation
 ******/
function process_session_cancellation() {

    $ca_id = (int) $_POST['ca_id'];
    $bb_group_id = (int) $_POST['bb_group_id'];
    $send_notification = $_POST['send_notification'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $add_makeup = $_POST['add_makeup'];


    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $minutes_to_add = ($end - $start) / 60;


    $catch_error = '';
    $wp_user_id = get_current_user_id();
    $support_users = get_users( array( 'role__in' => array( 'support' ) ) );
    $support_users_ids = array_column($support_users, 'id');
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';


    // set session status to 'cancelled'
    $data_array_to_update = array(
        'status' => 'cancelled'
    );
    $wpdb->update($bookly_customer_appointments_table, $data_array_to_update, array('id'=>$ca_id));

    // if send_notification == 'true' , call  send_notification_for_user($args)
    if( $send_notification == 'true' ):
        foreach ( $support_users_ids as &$support_user_id ):
            $args = array(
                "sender_id" => $wp_user_id,
                "receiver_id" => $support_user_id ,
                "cid" => $bb_group_id,
                "aid" => $ca_id,
                "type" => 'session_cancelled',
                'message'=> 'Cancelled sessions limit exceeded.',
                "send_date" => $created_at
            );
            if( send_notification_for_user($args) !== 'success' ):
                $catch_error .= 'Error: in adding notification log';
            endif;

        endforeach;

    endif;

    // if add_makeup == 'true' => calculate session duration in mins ( session end - session start ) => add mins to parent makeup_balance
    if( $add_makeup == 'true' ):
        $makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $wp_user_id );
        $makeup_balance = $makeup_balance + $minutes_to_add;
        update_field('mslm_makeup_balance', $makeup_balance, 'user_'.$wp_user_id);
    endif;


    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'success';
    endif;

    wp_die();
}

add_action('wp_ajax_process_session_cancellation', 'process_session_cancellation');
add_action( 'wp_ajax_nopriv_process_session_cancellation', 'process_session_cancellation' );


/******
 * Ajax action to get schedule class modal
 ******/
function get_schedule_class_modal() {

    get_template_part('template-parts/common/template-session-schedule');
    wp_die();
}

add_action('wp_ajax_get_schedule_class_modal', 'get_schedule_class_modal');
add_action( 'wp_ajax_nopriv_get_schedule_class_modal', 'get_schedule_class_modal' );



/******
 * Ajax action to get teacher name, id from BB group select
 ******/
function get_teacher_data_for_bbgroup() {

    $catch_error = '';
    $err_message = 'Error: no teacher assigned.';
    $parent_wp_user_id = $_POST['parent_wp_user_id'];
    $bb_group_id = $_POST['bb_group_id'];
    if( empty($bb_group_id) ):
        echo 'Error: no group selected';
        wp_die();
    endif;
    $sp_entry_id = getBBgroupGFentryID($bb_group_id);
    $staff_id_meta = getGFentryMetaValue($sp_entry_id, 8);
    $bookly_service_id = getBooklyServiceId($sp_entry_id);
    $class_timezone = getSPentryTimezone($sp_entry_id);

    if( !empty($staff_id_meta) ):
        $staff_id = (int) $staff_id_meta[0]->meta_value;
        if( !empty($staff_id) ):
            $staff_full_name = getStaffFullName($staff_id);
            $staff_name = (strlen($staff_full_name) < 20) ? $staff_full_name : substr($staff_full_name, 0, 20) . '...' ;
        else:
            $catch_error = $err_message;
        endif;
    else:
        $catch_error = $err_message;
    endif;

    // if staff get group members and select first one and get its parent
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
        $args = array(
            'group_id' => $bb_group_id,
            'max' => 999,
            'exclude_admins_mods' => true
        );
        $group_members = groups_get_group_members($args);
        $child_id = array_column( $group_members['members'] , 'ID')[0];
        // get child parent
        $parent_wp_user_id = getParentID($child_id);
        // get parent childs joined in bb group
        $childs_options = getParentChildsinBBgroup($parent_wp_user_id, $bb_group_id, 'select-all');
        $childs_text = getParentChildsinBBgroupString($parent_wp_user_id, $bb_group_id);
    else:
        // get parent childs joined in bb group
        $childs_options = getParentChildsinBBgroup($parent_wp_user_id, $bb_group_id, 'select-all');
        $childs_text = getParentChildsinBBgroupString($parent_wp_user_id, $bb_group_id);
    endif;


    if( empty($catch_error) ):
        echo json_encode(
            array(
                'id' => $staff_id,
                'name' => $staff_name,
                'service_id' => $bookly_service_id,
                'childs_options' => $childs_options,
                'childs_text' => $childs_text,
                'wp_parent_user_id' => $parent_wp_user_id,
                'class_timezone' => $class_timezone
            )
        );
    else:
        echo $catch_error;
    endif;

    wp_die();
}

add_action('wp_ajax_get_teacher_data_for_bbgroup', 'get_teacher_data_for_bbgroup');
add_action( 'wp_ajax_nopriv_get_teacher_data_for_bbgroup', 'get_teacher_data_for_bbgroup' );


/******
 * Ajax action to get session feedback options
 ******/
function get_session_feedback_options() {

    // get user role and show different feedback for parent and teacher
    $wp_user_id = get_current_user_id();
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', $wp_user_id )->count() > 0 ): // if staff member show
        $feedback_options = json_encode(
            array(
                array(
                    "value" => 'Learner Joined Class Late',
                    "textOption" => "Learner Joined Class Late"
                ),
                array(
                    "value" => "Learner Left Class Early",
                    "textOption" => "Learner Left Class Early"
                ),
                array(
                    "value" => "Learner Didn't Attend Class",
                    "textOption" => "Learner Didn't Attend Class"
                ),
                array(
                    "value" => "Learner Didn't Open Camera",
                    "textOption" => "Learner Didn't Open Camera"
                ),
                array(
                    "value" => "Learner Had Bad Internet Connectivity",
                    "textOption" => "Learner Had Bad Internet Connectivity"
                ),
                array(
                    "value" => "Learner Cancels Classes Frequently",
                    "textOption" => "Learner Cancels Classes Frequently"
                ),
                array(
                    "value" => "Learner Used Improper Language",
                    "textOption" => "Learner Used Improper Language"
                ),
                array(
                    "value" => "Other",
                    "textOption" => "Other"
                ),
            )
        );
    elseif ( checkIfParent($wp_user_id) == true ): // if parent show another feedback
        $feedback_options = json_encode(
            array(
                array(
                    "value" => 'Teacher Joined Class Late',
                    "textOption" => "Teacher Joined Class Late"
                ),
                array(
                    "value" => "Teacher Left Class Early",
                    "textOption" => "Teacher Left Class Early"
                ),
                array(
                    "value" => "Teacher Didn't Attend Class",
                    "textOption" => "Teacher Didn't Attend Class"
                ),
                array(
                    "value" => "Teacher Didn't Open Camera",
                    "textOption" => "Teacher Didn't Open Camera"
                ),
                array(
                    "value" => "Teacher Is Not Kid Friendly",
                    "textOption" => "Teacher Is Not Kid Friendly"
                ),
                array(
                    "value" => "Teacher Had Bad Internet Connectivity",
                    "textOption" => "Teacher Had Bad Internet Connectivity"
                ),
                array(
                    "value" => "Teacher Cancels Classes Frequently",
                    "textOption" => "Teacher Cancels Classes Frequently"
                ),
                array(
                    "value" => "Teacher Has No Availability For Makeup Classes",
                    "textOption" => "Teacher Has No Availability For Makeup Classes"
                ),
                array(
                    "value" => "Teacher Used Improper Language",
                    "textOption" => "Teacher Used Improper Language"
                ),
                array(
                    "value" => "Teacher Recorded Inaccurate Attendance",
                    "textOption" => "Teacher Recorded Inaccurate Attendance"
                ),
                array(
                    "value" => "Teacher Violated Policy or Code of Honor",
                    "textOption" => "Teacher Violated Policy or Code of Honor"
                ),
                array(
                    "value" => "Other",
                    "textOption" => "Other"
                ),
            )
        );
    endif;

    echo $feedback_options;

    wp_die();
}

add_action('wp_ajax_get_session_feedback_options', 'get_session_feedback_options');
add_action( 'wp_ajax_nopriv_get_session_feedback_options', 'get_session_feedback_options' );


/******
 * Ajax action to get available slots
 ******/
function get_available_slots() {

    // start of ajax request to check
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_teacher_id = $_POST['staff_id']; // 25
    $bookly_service_id = $_POST['service_id']; // 1
    $staff_timezone = getUserTimezone(getStaffwp_user_id($bookly_teacher_id));
    $class_duration = $_POST['class_duration']; // 60
    $session_start_date = $_POST['session_start_date'];
    $group_timezone = $_POST['group_timezone'];
    if( !empty($session_start_date) ):
        $session_start_date = date('Y-m-d H:i:s', strtotime($session_start_date));
    endif;

    $slots_options = '<option value="0" selected disabled> -- select -- </option>';
    if( !empty($bookly_teacher_id) && !empty($bookly_service_id) && !empty($class_duration) && !empty($session_start_date)):
        $now = new DateTime($session_start_date); // change selected date with posted data
        $end = clone $now;
        $end->modify("+24 hours");


        while ($now <= $end) {
            $start_date = $now->format('Y-m-d H:i:s');
            $start_dates[] = $start_date;
            $now->modify('+15 minutes');
            $end_date = strtotime("+15 minutes", strtotime($start_date));
            $end_dates[] = date('Y-m-d H:i:s', $end_date);
        }

        array_pop($start_dates); //removes last start date
        array_pop($end_dates); //removes last end date



        foreach ( $start_dates as $key=>$start_date ):
            $end_date = $end_dates[$key];

            $check_schedule = checkAppointmentErrors($start_date, $end_date, $bookly_teacher_id, $bookly_service_id, '', '', []);
            if( !empty($check_schedule) ):
                if( $check_schedule['interval_not_in_staff_schedule'] == false ):

                    // if no schedule issue add start and end date in available slots
                    $available_slots[] = array(
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                    );

                endif;
            else:
                // error in checking teacher schedule

            endif;
        endforeach;


        if( !empty($available_slots) ):


            // check if appointment overlap
            $start_date_to_search = date('Y-m-d', strtotime($start_dates[0]));
            $end_date_to_search = date('Y-m-d', strtotime(end($end_dates)));


            $appointments_results = $wpdb->get_results(
                "SELECT start_date, end_date FROM $bookly_appointments_table WHERE staff_id = {$bookly_teacher_id} 
                                 AND start_date >= '{$start_date_to_search}' AND end_date <= '{$end_date_to_search}' "
            );
            $wpdb->flush();


            foreach ( $available_slots as $key=>$available_slot ):

                if( !empty($appointments_results) ):

                    foreach ( $appointments_results as $appointments_result ):

                        //echo $available_slot['start_date'] . ' ** ' . $available_slot['end_date'] . '<br>';

                        $booking_stored_start_date =  $appointments_result->start_date;
                        $booking_stored_end_date =  $appointments_result->end_date;

                        //echo $booking_stored_start_date . ' ** ' . $booking_stored_end_date . '<br><hr>';


                        $end_slot = strtotime("+$class_duration minutes", strtotime($available_slot['start_date']));
                        $end_slot = date('Y-m-d H:i:s', $end_slot);

                        $check_overlap = overlapInMinutes($available_slot['start_date'], $end_slot, $booking_stored_start_date, $booking_stored_end_date);

                        if ( $check_overlap > 0 ){
                            unset($available_slots[$key]);
                        }

                    endforeach;

                endif;

            endforeach;
        endif;

        if( !empty($available_slots) ):
            $now_utc = date('Y-m-d H:i:s', time());
            $utc_after_24h = strtotime("+24 hours", strtotime($now_utc));
            $utc_after_24h = date('Y-m-d H:i:s', $utc_after_24h);
            $parent_time_after_24h = convertTimezone1ToTimezone2 ( $utc_after_24h, 'UTC', $group_timezone );

            foreach ( $available_slots as $available_slot ):
                // convert available slot datetime to parent timezone
                $slot_datetime = convertTimezone1ToTimezone2 ( $available_slot['start_date'], 'UTC', $group_timezone );
                // limit available slots after 24 hrs from now with parent timezone
                if( strtotime($slot_datetime) >= strtotime($parent_time_after_24h) ):
                    $slot_time = date('h:i A', strtotime($slot_datetime));
                    $slot_time_value = date('H:i', strtotime($slot_datetime));
                    $slots_options .= "<option value='{$slot_time_value}'> {$slot_time} </option>";
                endif;
            endforeach;
        endif;

    endif;

    echo $slots_options;

    wp_die();
}

add_action('wp_ajax_get_available_slots', 'get_available_slots');
add_action( 'wp_ajax_nopriv_get_available_slots', 'get_available_slots' );


/******
 * Ajax action to process session schedule
 ******/
function process_session_schedule(){

    global $wpdb;
    $wp_user_id = $_POST['wp_user_id'];
    $academic_parent_user_id = $_POST['academic_parent_user_id'];
    if( !empty($academic_parent_user_id) ):
        $wp_user_id = $academic_parent_user_id;
    endif;
    $class_duration = $_POST['class_duration'];
    $bookly_teacher_id = $_POST['bookly_teacher_id'];
    $bookly_service_id = $_POST['bookly_service_id'];
    $start_time = $_POST['start_time'];
    $booking_start_date = $_POST['booking_start_date']; // sent from ajax in UTC
    $bookly_customer_ids = $_POST['customer_id'];
    $bb_group_id = $_POST['bb_group_id'];
    $group_timezone = $_POST['group_timezone'];
    $catch_error = '';
    $date_to_display = $booking_start_date . ' ' . $start_time;

    // validate data if any empty
    if(
        empty($wp_user_id) ||
        empty($class_duration) ||
        empty($bookly_teacher_id) ||
        empty($bookly_service_id) ||
        empty($start_time) ||
        empty($booking_start_date) ||
        empty($bookly_customer_ids) ||
        empty($bb_group_id) ||
        empty($group_timezone)

    ):
        echo 'Please fill all fields first.';
        wp_die();
    endif;


    $booking_start_date = date('Y-m-d H:i:s', strtotime( $booking_start_date . ' ' . $start_time  ));
    // convert start date from user timezone into utc
    $parent_timezone = getUserTimezone($wp_user_id);
    $booking_start_date = convertTimezone1ToTimezone2 ( $booking_start_date, $group_timezone, 'UTC' );
    $booking_end_date = date('Y-m-d H:i:s', strtotime("+$class_duration minutes", strtotime($booking_start_date)));


    $bookly_user_timezone_offset = 0;
    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $custom_fields = json_encode(
        array(
            array(
                'id' => $bb_custom_field_id,
                'value' => strval($bb_group_id)
            ),
            array(
                'id' => 32859, // makeup flag
                'value' => 'True'
            )
        )
    );

    $unit = (int) $class_duration  / 15;

    $bookly_series_table = $wpdb->prefix . 'bookly_series';
    $insert_new_series = $wpdb->insert($bookly_series_table,
        array(
            'repeat' => '',
            'token' => generateUniqueToken(),
        )
    );

    if( $insert_new_series ):
        // get last series id from bookly_series to attach to new schedule
        $series_results = $wpdb->get_results(
            "SELECT id FROM $bookly_series_table ORDER BY id DESC LIMIT 1"
        );
        $wpdb->flush();

        if( !empty($series_results) ):
            $series_id = (int) $series_results[0]->id;
        else:
            $catch_error .= 'Error: bookly series id not found<br>';
        endif;
    endif;

    // deduct session duration from makeup_balance
    $makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $wp_user_id );

    if( checkIfParent($wp_user_id) == true ):
        if( $makeup_balance <= 0 ):
            echo 'Error: no makeup balance';
            wp_die();
        else:
            $new_makeup_balance = $makeup_balance - $class_duration;
            update_field('mslm_makeup_balance', $new_makeup_balance, 'user_'.$wp_user_id);
        endif;
    endif;

    // if staff, make status = pending
    if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
        $ca_status = 'pending';
    else:
        $ca_status = 'approved';
    endif;

    // create new session with approved status in bookly_appointments & bookly_customer_appointmnets tables
    // insert appointments into bookly_appointments table
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $appointments = array(
        array(
            'staff_id' => $bookly_teacher_id,
            'staff_any' => 0,
            'service_id' => $bookly_service_id,
            'start_date' => $booking_start_date,
            'end_date' => $booking_end_date,
            'extras_duration' => 0,
            'internal_note' => '',
            'created_from' => 'bookly',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ),
    );


    if( wpdb_bulk_insert($bookly_appointments_table, $appointments) === 1 ):
        //if record true, get appointment id
        $appointment_id = $wpdb->insert_id;

        // get appointment record id to use it in customer_appointments table and use social group id as custom_fields
        $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

        foreach ( $bookly_customer_ids as $bookly_customer_id ):
            $customer_appointments_array = array(
                array(
                    'series_id' => $series_id,
                    'customer_id' => $bookly_customer_id,
                    'appointment_id' => $appointment_id,
                    'number_of_persons' => 1,//count( $bookly_customer_ids ),
                    'units' => $unit,
                    'extras' => [],
                    'extras_multiply_nop' => 1,
                    'custom_fields' => $custom_fields,
                    'status' => $ca_status,
                    'token' => generateUniqueToken(),
                    'time_zone' => 'UTC',
                    'time_zone_offset' => $bookly_user_timezone_offset,
                    'created_from' => 'frontend',
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                )
            );



            if( wpdb_bulk_insert($bookly_customer_appointments_table, $customer_appointments_array) !== 1 ):
                // do nothing, insert success
                $catch_error .= 'Error in inserting customer appointment <br>';
            else:
                $ca_id = $wpdb->insert_id;
            endif;

        endforeach;
    else:
        $wpdb->show_errors();
        $catch_error .= 'Error in inserting bookly appointments table: '.$wpdb->print_error().'<br>';
    endif;


    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        $start_time_to_display = date('h:i a', strtotime($date_to_display));
        $start_date_to_display = date('D, m-d-Y', strtotime($date_to_display));

        $dateTime = new DateTime();
        $dateTime->setTimeZone(new DateTimeZone( $group_timezone ));
        $timezone_abbr = $dateTime->format('T');

        $group_obj = groups_get_group ( $bb_group_id );
        $group_name = $group_obj->name;

        $parentFullname = get_user_meta($wp_user_id, 'first_name' ,true) . ' ' . get_user_meta($wp_user_id, 'last_name' ,true);
        
        // get group members except moderator and admin
        $args = array(
            'group_id' => $bb_group_id,
            'max' => 999,
            'exclude_admins_mods' => true,
            'exclude_banned' => true,
        );
        $members = array_column(groups_get_group_members( $args )['members'], 'id');
        $members[] = $academic_parent_user_id;


        if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ): // if staff/teacher schedule

            $pending_confirmation_msg_parent = '[Pending] Confirm makeup session at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display .')' ; // add EST, .. timezone short after time start
            $pending_confirmation_msg_staff = '[Pending] New makeup session at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')' ; // add EST, .. timezone short after time start
            // send notification to parent and childs
            foreach ( $members as $member_id ):
                $parent_notification_args= array(
                    "sender_id" => 'Scheduler',
                    "receiver_id" => $member_id,
                    "cid" => $bb_group_id,
                    "aid" => $appointment_id,
                    "type" => 'makeup_scheduled',
                    "message" =>  $pending_confirmation_msg_parent ,
                );
                send_notification_for_user($parent_notification_args);
            endforeach;

            // send notification to teacher
            $teacher_notification_args= array(
                "sender_id" => 'Scheduler',
                "receiver_id" => getStaffwp_user_id($bookly_teacher_id),
                "cid" => $bb_group_id,
                "aid" => $appointment_id,
                "type" => 'makeup_scheduled',
                "message" =>  $pending_confirmation_msg_staff ,
            );
            send_notification_for_user($teacher_notification_args);

            // create bb group activity so specific group
            $activity_args = array(
                'content' => '[Pending Confirmation] New makeup session at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')',
                'group_id' => $bb_group_id,
                'user_id' => $wp_user_id
            );
            groups_post_update($activity_args);

        else: // if parent schedule
            $message = 'New makeup session scheduled at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.')' ; // add EST, .. timezone short after time start
            // call notification function to teacher and add as activity in BB group
            // send notification to parent and childs
            foreach ( $members as $member_id ):
                $parent_notification_args= array(
                    "sender_id" => 'Scheduler',
                    "receiver_id" => $member_id,
                    "cid" => $bb_group_id,
                    "aid" => $appointment_id,
                    "type" => 'makeup_scheduled',
                    "message" =>  $message ,
                );
                send_notification_for_user($parent_notification_args);
            endforeach;

            // send notification to teacher
            $teacher_notification_args= array(
                "sender_id" => 'Scheduler',
                "receiver_id" => getStaffwp_user_id($bookly_teacher_id),
                "cid" => $bb_group_id,
                "aid" => $appointment_id,
                "type" => 'makeup_scheduled',
                "message" =>  $message ,
            );
            send_notification_for_user($teacher_notification_args);

            // create bb group activity so specific group
            $activity_args = array(
                'content' => $parentFullname.' scheduled a makeup session at: '. $start_time_to_display . ' ' . $timezone_abbr .' ('. $start_date_to_display.') - ' . $group_name,
                'group_id' => $bb_group_id,
                'user_id' => $wp_user_id
            );
            groups_post_update($activity_args);
        endif;



        // if staff => send verification mail to parent
        if( Bookly\Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0 ):
            sendVerificationTokenScheduleMakeup($wp_user_id, $ca_id);
            // send as sms

        endif;

        echo 'success';

    endif;


    wp_die();
}

add_action('wp_ajax_process_session_schedule', 'process_session_schedule');
add_action( 'wp_ajax_nopriv_process_session_schedule', 'process_session_schedule' );


/******
 * Ajax action to process session feedback
 ******/
function capture_session_feedback(){

    $feedback_selected = $_POST['feedback_selected'];
    $feedback_notes = $_POST['feedback_notes'];
    $ca_id = $_POST['ca_id'];
    $catch_error = '';


    global $wpdb;
    $bookly_customer_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';
    // get old custom_fields data
    $ca_results = $wpdb->get_results(
        "SELECT custom_fields FROM $bookly_customer_appointments_table WHERE id = {$ca_id}"
    );
    $wpdb->flush();

    if( !empty($ca_results) ):
        $old_custom_fields = json_decode($ca_results[0]->custom_fields);

        $stored_custom_fields_ids = array_column($old_custom_fields, 'id');


        // if found feedback options, get old value and unset old value and push new one
        if( in_array(28539, $stored_custom_fields_ids) && !empty($stored_custom_fields_ids) ):
            $feedback_selected_key = array_search(28539, $stored_custom_fields_ids);
            $feedback_selected_old_value = $old_custom_fields[$feedback_selected_key]->value;
            unset($old_custom_fields[$feedback_selected_key]);
        endif;


        // if found feedback notes, get old value and unset old value and push new one
        if( in_array(26244, $stored_custom_fields_ids) && !empty($stored_custom_fields_ids) ):
            $feedback_selected_key = array_search(26244, $stored_custom_fields_ids);
            $feedback_selected_old_value = $old_custom_fields[$feedback_selected_key]->value;
            unset($old_custom_fields[$feedback_selected_key]);
        endif;


        // get feedback data
        $old_custom_fields[] = array(
            'id' => 28539,
            'value' => $feedback_selected
        );

        // save feedback notes
        $old_custom_fields[] = array(
            'id' => 26244,
            'value' => $feedback_notes
        );


        // re arrange custom fields array
        $old_custom_fields = array_values($old_custom_fields);


        $new_custom_fields = json_encode($old_custom_fields);

        //update ca_id with new custom fields
        if( $wpdb->update($bookly_customer_appointments_table, array('id'=>$ca_id, 'custom_fields'=>$new_custom_fields), array('id'=>$ca_id)) !== 1 ):
            $wpdb->show_errors();
            $catch_error .= 'Error: in updating bookly custom fields. <br>' .$wpdb->print_error();
        endif;

    endif;

    if( !empty($catch_error) ):
        echo $catch_error;
    else:
        echo 'success';
    endif;


    wp_die();
}

add_action('wp_ajax_capture_session_feedback', 'capture_session_feedback');
add_action( 'wp_ajax_nopriv_capture_session_feedback', 'capture_session_feedback' );


/******
 * Ajax action to get learndash course template
 ******/
function get_learndash_course_template(){
    $course_id = $_POST['course_id'];
    $cid = $_POST['cid'];
    get_template_part( 'template-parts/class-dashboard/template-learndash-course', null, array(
            'course_id' => $course_id,
            'cid' => $cid
        )
    );
    wp_die();
}

add_action('wp_ajax_get_learndash_course_template', 'get_learndash_course_template');
add_action( 'wp_ajax_nopriv_get_learndash_course_template', 'get_learndash_course_template' );



/******
 * Ajax action to get classes options
 ******/
function get_classes_options(){
    $bb_group_id = $_POST['bb_group_id'];
    get_template_part('template-parts/common/template-parent-child-classes', null, array(
        'bb_group_id' => $bb_group_id,
    ));
    wp_die();
}

add_action('wp_ajax_get_classes_options', 'get_classes_options');
add_action( 'wp_ajax_nopriv_get_classes_options', 'get_classes_options' );



/******
 * Ajax action to get classes options
 ******/
function get_class_duration_btns(){
    $parent_wp_user_id = $_POST['parent_wp_user_id'];
    if( empty($parent_wp_user_id) ):
        echo 'No parent found';
        wp_die();
    endif;
    $parent_makeup_balance = get_field( 'mslm_makeup_balance', 'user_' . $parent_wp_user_id );
    get_template_part('template-parts/common/template-duration-btns', null, array(
            'makeup_balance' => $parent_makeup_balance,
        )
    );
    wp_die();
}

add_action('wp_ajax_get_class_duration_btns', 'get_class_duration_btns');
add_action( 'wp_ajax_nopriv_get_class_duration_btns', 'get_class_duration_btns' );


/******
 * Ajax action to accept makeup session
 ******/
function accept_makeup_session(){

    $wp_user_id = $_POST['wp_user_id'];
    $token = $_POST['token'];


    if( !empty($wp_user_id) && !empty($token) ):
        // call function to verify token
        $validate_token = validateVerificationTokenScheduleMakeup($token, $wp_user_id);
        if( is_array($validate_token) && $validate_token['status'] == true ):
            echo 'success';
        else:
            echo '<div class="alert alert-danger verify-token"> '. $validate_token .' </div>';
        endif;
    else:
        echo 'Error: session data not available. Please contact support.';
    endif;
    wp_die();
}

add_action('wp_ajax_accept_makeup_session', 'accept_makeup_session');
add_action( 'wp_ajax_nopriv_accept_makeup_session', 'accept_makeup_session' );

/******
 * Ajax action to reject makeup session
 ******/
function reject_makeup_session(){

    $bb_group_id = $_POST['bb_group_id'];

    if( !empty($bb_group_id) ):
        $reject_session = rejectBooklySession($bb_group_id);
        if( $reject_session == true ):
            echo 'success';
        else:
            echo 'Error: session can not be rejected.' . $reject_session;
        endif;
    else:
        echo 'Error: session data not available.';
    endif;
    wp_die();
}

add_action('wp_ajax_reject_makeup_session', 'reject_makeup_session');
add_action( 'wp_ajax_nopriv_reject_makeup_session', 'reject_makeup_session' );


/******
 * Ajax action to get selecetd date and time in staff timezone
 ******/
function get_datetime_in_staff_timezone(){
    $session_start_date = $_POST['session_start_date'];
    $start_time = $_POST['start_time'];
    $group_timezone = $_POST['group_timezone'];
    $bookly_teacher_id = $_POST['bookly_teacher_id'];
    $session_start_date_time = $session_start_date. ' ' . $start_time;
    $staff_wp_user_id = getStaffwp_user_id($bookly_teacher_id);
    $teacher_timeozone = getUserTimezone($staff_wp_user_id);
    
    // convert timezone
    $teacher_date_time = convertTimezone1ToTimezone2 ( $session_start_date_time, $group_timezone, $teacher_timeozone );

    echo date('Y-m-d h:i A', strtotime($teacher_date_time)) . ' - ' . $teacher_timeozone;

    wp_die();
}

add_action('wp_ajax_get_datetime_in_staff_timezone', 'get_datetime_in_staff_timezone');
add_action( 'wp_ajax_nopriv_get_datetime_in_staff_timezone', 'get_datetime_in_staff_timezone' );


/******
 * Ajax action to get next and previous class attendance table
 ******/
function get_attendance_table_in_period(){
    $wp_user_id = $_POST['wp_user_id'];
    $renews_period_start = $_POST['renews_period_start'];
    $renews_period_end = $_POST['renews_period_end'];
    $custom_period = $_POST['custom_period'];
    $data = [];

    if( empty($wp_user_id) || empty($renews_period_start) || empty($renews_period_end) || empty($custom_period) ):
        echo 'No data available';
    else:
        if( $custom_period == 'next_month' ):
            $new_period_start = date('Y-m-d', strtotime($renews_period_end . ' +1 day'));
            $new_period_end = date('Y-m-d', strtotime($new_period_start . ' +1 months - 1 day'));
        elseif ( $custom_period == 'prev_month' ):
            $new_period_end = date('Y-m-d', strtotime($renews_period_start . '-1 day'));
            $new_period_start = date('Y-m-d', strtotime($new_period_end . ' -1 months + 1 day'));
        endif;

        $data['new_period_start'] = $new_period_start;
        $data['new_period_end'] = $new_period_end;

        echo json_encode($data);

    endif;

    wp_die();
}

add_action('wp_ajax_get_attendance_table_in_period', 'get_attendance_table_in_period');
add_action( 'wp_ajax_nopriv_get_attendance_table_in_period', 'get_attendance_table_in_period' );


/******
 * Ajax action to update learners in edit mode
 ******/
function update_learners_edit_mode(){

    $current_learners = $_POST['current_learners'];
    $updated_learners = $_POST['updated_learners'];
    $new_effective_date = $_POST['new_effective_date'];
    $bb_group_id = $_POST['bb_group_id'];

    if(
        empty($current_learners) ||
        empty($updated_learners) ||
        empty($new_effective_date) ||
        empty($bb_group_id)
    ):
        echo wp_send_json_error( array(
            'message' => 'Please fill all fields and try again.'
        ), 200 );
        wp_die();
    endif;

    // get diff learners in each list
    $learners_diff_ids = array_merge( array_diff($current_learners, $updated_learners),array_diff( $updated_learners, $current_learners) );


    // extract learners to remove
    foreach ( $learners_diff_ids as $learners_diff_id ):
        if( in_array($learners_diff_id, $current_learners) ):
            $learners_to_remove[] = $learners_diff_id;
        endif;
    endforeach;

    // extract learners to add
    foreach ( $learners_diff_ids as $learners_diff_id ):
        if( in_array($learners_diff_id, $updated_learners) ):
            $learners_to_add[] = $learners_diff_id;
        endif;
    endforeach;

    if( !empty($learners_to_add) ):
        foreach ( $learners_to_add as $learner_to_add ):

            $learner_to_add_full_name = getCustomerName($learner_to_add);

            // re-generate new customer appointments for new learner in group only
            if( regenerateAppointmentsForNewLearner($learner_to_add, $bb_group_id, $new_effective_date) == false ):
                $catch_error[] = "Error: in generating new CA records for learner $learners_to_add for BB group $bb_group_id ";
            endif;

            // add new learner to BB group
            if( ! groups_join_group( $bb_group_id, $learner_to_add ) ): // groups_join_group( int $group_id, int $user_id )
                addLog(
                    array(
                        'event_title' => 'Error in adding learner',
                        'event_desc' => "Error: Learner with id: $learner_to_add was not added to group: $bb_group_id",
                        'user_id' => get_current_user_id()
                    )
                );
            endif;

            // add learner to GF learners entry
            addLearnerfromGF( $learner_to_add, $bb_group_id );

            // adding activity log with addition action
            addLog(
                array(
                    'event_title' => 'Learner Added',
                    'event_desc' => "Learner with id: $learner_to_add has been added to group: $bb_group_id",
                    'user_id' => get_current_user_id()
                )
            );

            // create bb group activity so specific group
            $activity_args = array(
                'content' => "$learner_to_add_full_name joined class, welcome aboard!",
                'group_id' => $bb_group_id,
                'user_id' => get_current_user_id()
            );
            groups_post_update($activity_args);


            // if group type mvs add learner to mvs group and add role student
            // get mvs group id
            if( !empty(BP_Groups_Group::get_id_from_slug('mvs')) ):
                $mvs_parent_group_id = BP_Groups_Group::get_id_from_slug('mvs');
            endif;

            // if group type = mvs => add_role 'student' and join mvs group
            if( !empty($mvs_parent_group_id) && getBBgroupType($bb_group_id) == 'mvs' ):
                // join members to 'mvs' bb group
                if( ! groups_join_group( $mvs_parent_group_id, $learner_to_add ) ): // groups_join_group( int $group_id, int $user_id )
                    $catch_error[] = 'Error in joining learner(s) ' . $learner_to_add . ' to parent MVS group.';
                endif;

                // add role 'student' to all customers in 'mvs'
                $user = get_user_by( 'id', $learner_to_add );
                $user->add_role('student');
                $user = null;

            endif;

        endforeach;
    endif;

    if( ! empty($learners_to_remove) ):
        foreach ( $learners_to_remove as $learner_to_remove ):

            $learner_to_remove_full_name = getCustomerName($learner_to_remove);

            // remove learner process

            // remove appointments for learner starting from new effective date
            if( removeCustomerCAappts( $learner_to_remove, $bb_group_id , $new_effective_date ) == false ):
                $catch_error[] = "Error: in removing learner: $learners_to_remove CA records for BB group $bb_group_id.";
            endif;

            // remove learner from BB group
            groups_remove_member($learner_to_remove, $bb_group_id);

            // remove learner from GF learners entry
            removeLearnerfromGF($learner_to_remove, $bb_group_id);

            // adding activity log with remove action
            addLog(
                array(
                    'event_title' => 'Learner Removed',
                    'event_desc' => "Learner with id: $learner_to_remove has been removed from group: $bb_group_id",
                    'user_id' => get_current_user_id()
                )
            );

            // create bb group activity so specific group $learner_to_remove_full_name
            $activity_args = array(
                'content' => "$learner_to_remove_full_name is no longer in this class.",
                'group_id' => $bb_group_id,
                'user_id' => get_current_user_id()
            );
            groups_post_update($activity_args);


            // if group type mvs and check if he has any other mvs groups => remove learner from mvs group and remove role student
            // get mvs group id
            if( !empty(BP_Groups_Group::get_id_from_slug('mvs')) ):
                $mvs_parent_group_id = BP_Groups_Group::get_id_from_slug('mvs');
            endif;

            // if group type = mvs => add_role 'student' and join mvs group
            if( !empty($mvs_parent_group_id) && getBBgroupType($bb_group_id) == 'mvs' && checkIflearnerHasMvs($learner_to_remove) == false ):
                // remove member from 'mvs' bb group
                if( ! groups_leave_group( $mvs_parent_group_id, $learner_to_remove ) ):
                    $catch_error[] = 'Error in removing learner(s) ' . $learner_to_add . ' from parent MVS group.';
                endif;

                // remove role 'student'
                $user = get_user_by( 'id', $learner_to_remove );
                $user->remove_role('student');
                $user = null;

            endif;


        endforeach;
    endif;



    if( !empty($catch_error) ):
        addLog(
            array(
                'event_title' => "Error in Edit Learners in group $bb_group_id",
                'event_desc' => $catch_error,
                'user_id' => get_current_user_id()
            )
        );
    else:
        echo wp_send_json_success( array(
            'message' => 'Data Updated Successfully. please refresh the page if it does not reload automatically.'
        ), 200 );
    endif;

    wp_die();

}

add_action('wp_ajax_update_learners_edit_mode', 'update_learners_edit_mode');
add_action( 'wp_ajax_nopriv_update_learners_edit_mode', 'update_learners_edit_mode' );




