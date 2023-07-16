<?php

function randomHex() {
    $chars = 'ABCDEF0123456789';
    $color = '#';
    for ( $i = 0; $i < 6; $i++ ) {
        $color .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $color;
}




/******
 * Ajax action to sync users from wordpress to bookly as customers rules included (Students, School Student, parent )
 ******/

add_action('wp_ajax_sync_learners', 'sync_learners');
add_action( 'wp_ajax_nopriv_sync_learners', 'sync_learners' );


function sync_learners (){

    global $wpdb;
    $last_records = $_POST['last_records'];
    $next_records = $_POST['next_records'];


    // do sync process

    // query all users from last and next limit
    $wp_users_table = $wpdb->prefix . 'users';
    $students = $wpdb->get_results(
        "SELECT * FROM $wp_users_table WHERE ID >= {$last_records} AND ID <= {$next_records}"
    );
    $wpdb->flush();

    // get all wp users with rules (Students, School Student, parent )
    //    $students = get_users( array( 'role__in' => array( 'student', 'parent', 'school_student', 'tutoring_student' ) ) );
    //$students = get_users( );

    //echo 'users count: ' . count($students).'<br>';
    $bookly_customers = [];
    foreach ($students as $key=>$student):
        $wp_user_id = $student->ID;
        $first_name = get_user_meta($wp_user_id, 'first_name', true);
        $last_name = get_user_meta($wp_user_id, 'last_name', true);
//            $first_name = $student->first_name;
//            $last_name = $student->last_name;
        $email = $student->user_email;
        $country = get_user_meta($wp_user_id ,'country', true);
        $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'account_code', true);
        $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
        $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
        $parent_user = get_user_by_meta_data('account_code', $memb_ReferralCode_prnt);
        $parent_user_id = (int) $parent_user->data->ID;
//            $parent_user_display_name = $parent_user->data->display_name;
//            $child_last_name = $last_name . ' / ' . $parent_user_display_name;
//            $full_name = $first_name . ' ' . $child_last_name;
        $full_name = getCustomerFullName($wp_user_id);
        $phone = get_user_meta($wp_user_id ,'primary_phone', true);
        if( empty( $phone ) ):
            // get parent phone
            $phone = get_user_meta($parent_user_id ,'primary_phone', true);
        endif;

        $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
        $created_at = $current_date_object->format('Y-m-d H:i:s');

        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
        $bookly_customer_result = $wpdb->get_results(
            "SELECT * FROM $bookly_customers_table WHERE wp_user_id = {$wp_user_id}"
        );
        $wpdb->flush();
        $bookly_customer_id = (int) $bookly_customer_result[0]->wp_user_id;

        //echo 'user: ' . $wp_user_id . ' - bookly id: ' . $bookly_customer_result[0]->id . '<br>';

        if( empty($bookly_customer_id) ):

            $bookly_customers[$key]['wp_user_id'] = $wp_user_id;
            $bookly_customers[$key]['full_name'] = $full_name;
            $bookly_customers[$key]['first_name'] = $first_name;
            $bookly_customers[$key]['last_name'] = $last_name;
            $bookly_customers[$key]['phone'] = $phone;
            $bookly_customers[$key]['email'] = $email;
            $bookly_customers[$key]['country'] = $country;
            $bookly_customers[$key]['created_at'] = $created_at;

        endif;
    endforeach;


    // insert into table bookly_customers
    if( !empty($bookly_customers) ):
        $bookly_customers = array_merge($bookly_customers);
        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
        if( wpdb_bulk_insert($bookly_customers_table, $bookly_customers) !== false ):
            echo '<div class="alert alert-success" role="alert"> <strong> '. count($bookly_customers) .' </strong> customer(s) has been inserted. </div>';
        else:
            $wpdb->show_errors();
            $wpdb->print_error();
            echo '<div class="alert alert-danger" role="alert"> Error in insert customers </div>';
        endif;
    else:
        echo '<div class="alert alert-success" role="alert"> Customers Sync successfully ! </div>';
    endif;

//        $response_data = array(
//            'message' => true,
//            'sync' => 'synced'
//        );
//    return wp_send_json_success($response_data);

    wp_die();

}



//
//function sync_learners (){
//
//    global $wpdb;
//    $last_user_id = $_POST['last_user_id'];
//
//    $query_times = ceil( $last_user_id / 100 );
//
//    $resume_result = true;
//    $synced_customers = 0;
//    for($x = 1; $x <= $query_times; $x++) {
//        $last_records = (($x-1)*100)+1;
//        $next_records = $x*100;
//        echo "last is:". $last_records ." next is: ". $next_records ."<br>";
//        // do sync process
//
//        // query all users from last and next limit
//        $wp_users_table = $wpdb->prefix . 'users';
//        $students = $wpdb->get_results(
//            "SELECT * FROM $wp_users_table WHERE ID >= {$last_records} AND ID <= {$next_records}"
//        );
//        $wpdb->flush();
//
//        // get all wp users with rules (Students, School Student, parent )
////    $students = get_users( array( 'role__in' => array( 'student', 'parent', 'school_student', 'tutoring_student' ) ) );
//        //$students = get_users( );
//
//        //echo 'users count: ' . count($students).'<br>';
//        $bookly_customers = [];
//        foreach ($students as $key=>$student):
//            $wp_user_id = $student->ID;
//            $first_name = get_user_meta($wp_user_id, 'first_name', true);
//            $last_name = get_user_meta($wp_user_id, 'last_name', true);
////            $first_name = $student->first_name;
////            $last_name = $student->last_name;
//            $email = $student->user_email;
//            $country = get_user_meta($wp_user_id ,'memb_Country', true);
//            $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'memb_ReferralCode', true);
//            $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
//            $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
//            $parent_user = get_user_by_meta_data('memb_ReferralCode', $memb_ReferralCode_prnt);
//            $parent_user_id = (int) $parent_user->data->ID;
////            $parent_user_display_name = $parent_user->data->display_name;
////            $child_last_name = $last_name . ' / ' . $parent_user_display_name;
////            $full_name = $first_name . ' ' . $child_last_name;
//            $full_name = getCustomerFullName($wp_user_id);
//            $phone = get_user_meta($wp_user_id ,'memb_Phone1', true);
//            if( empty( $phone ) ):
//                // get parent phone
//                $phone = get_user_meta($parent_user_id ,'memb_Phone1', true);
//            endif;
//
//            $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
//            $created_at = $current_date_object->format('Y-m-d H:i:s');
//
//            $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
//            $bookly_customer_result = $wpdb->get_results(
//                "SELECT * FROM $bookly_customers_table WHERE wp_user_id = {$wp_user_id}"
//            );
//            $wpdb->flush();
//            $bookly_customer_id = (int) $bookly_customer_result[0]->wp_user_id;
//
//            //echo 'user: ' . $wp_user_id . ' - bookly id: ' . $bookly_customer_result[0]->id . '<br>';
//
//            if( empty($bookly_customer_id) ):
//
//                $bookly_customers[$key]['wp_user_id'] = $wp_user_id;
//                $bookly_customers[$key]['full_name'] = $full_name;
//                $bookly_customers[$key]['first_name'] = $first_name;
//                $bookly_customers[$key]['last_name'] = $last_name;
//                $bookly_customers[$key]['phone'] = $phone;
//                $bookly_customers[$key]['email'] = $email;
//                $bookly_customers[$key]['country'] = $country;
//                $bookly_customers[$key]['created_at'] = $created_at;
//
//            endif;
//        endforeach;
//
//
//        // insert into table bookly_customers
//        if( !empty($bookly_customers) ):
//            $bookly_customers = array_merge($bookly_customers);
//            $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
//            if( wpdb_bulk_insert($bookly_customers_table, $bookly_customers) !== false ):
//                $resume_result = true;
//                $synced_customers = count($bookly_customers) + $synced_customers;
//            else:
//                $wpdb->show_errors();
//                $wpdb->print_error();
//                echo '<div class="alert alert-danger" role="alert"> Error in insert customers </div>';
//                $resume_result = false;
//            endif;
//        else:
//            $resume_result = true;
//            $no_customers_to_sync[] = true;
//        endif;
//
//
//        // if result of sync == resume, then continue
//        if ($resume_result !== true) {
//            break;
//        }
//
//    }
//
//    if($synced_customers > 0):
//        echo '<div class="alert alert-success" role="alert"> <strong> '. $synced_customers .' </strong> customer(s) has been inserted. </div>';
//    endif;
//
//
//    if (array_search(true, $no_customers_to_sync) !== false):
//        echo '<div class="alert alert-success" role="alert"> Customers Sync successfully ! </div>';
//    endif;
//
//    //echo '<div class="alert alert-success" role="alert"> Customers Sync successfully ! </div>';
//
//    wp_die();
//
//}


/******
 * Ajax action to sync users from wordpress to bookly as customers rules included (Students, School Student, parent )
 ******/

add_action('wp_ajax_sync_staff', 'sync_staff');
add_action( 'wp_ajax_nopriv_sync_staff', 'sync_staff' );
function sync_staff (){

    global $wpdb;


    // get all wp users with rules 'staff', 'school_teacher'
    $staffs = get_users( array( 'role__in' => array( 'teacher', 'school_teacher' ) ) );

    // get last staff position in bookly_staff
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $bookly_staff_result = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table ORDER BY position DESC LIMIT 1"
    );
    $wpdb->flush();
    $last_position = (int) $bookly_staff_result[0]->position;
    $position = $last_position;

    foreach ($staffs as $key=>$staff):

        $wp_user_id = $staff->ID;
        $full_name =  $staff->display_name;
        $category_id = 1;
        $phone = '';
        $email = $staff->user_email;
        $time_zone = 'Africa/Cairo';
        $visibility = 'private';
        $position = $position + 1;
        $zoom_authentication = 'default';
        $icalendar = 0;
        $icalendar_token = generateUniqueToken();
        $icalendar_days_before = 365;
        $icalendar_days_after = 365;
        $color = randomHex();
        $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
        $created_at = $current_date_object->format('Y-m-d H:i:s');

        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $bookly_staff_results = $wpdb->get_results(
            "SELECT * FROM $bookly_staff_table WHERE wp_user_id = {$wp_user_id}"
        );
        $wpdb->flush();
        $bookly_staff_id = (int) $bookly_staff_results[0]->wp_user_id;

        if( empty($bookly_staff_id) ):
            $bookly_staff_members[$key]['wp_user_id'] = $wp_user_id;
            $bookly_staff_members[$key]['full_name'] = $full_name;
            $bookly_staff_members[$key]['phone'] = $phone;
            $bookly_staff_members[$key]['email'] = $email;
            $bookly_staff_members[$key]['category_id'] = $category_id;
            $bookly_staff_members[$key]['time_zone'] = $time_zone;
            $bookly_staff_members[$key]['visibility'] = $visibility;
            $bookly_staff_members[$key]['position'] = $position;
            $bookly_staff_members[$key]['zoom_authentication'] = $zoom_authentication;
            $bookly_staff_members[$key]['icalendar'] = $icalendar;
            $bookly_staff_members[$key]['icalendar_token'] = $icalendar_token;
            $bookly_staff_members[$key]['icalendar_days_before'] = $icalendar_days_before;
            $bookly_staff_members[$key]['icalendar_days_after'] = $icalendar_days_after;
            $bookly_staff_members[$key]['color'] = $color;


        endif;
    endforeach;

    //insert into table bookly_customers
    if( !empty($bookly_staff_members) ):
        $bookly_staff_members = array_merge($bookly_staff_members);
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';


        if( wpdb_bulk_insert($bookly_staff_table, $bookly_staff_members) !== false ):

            // get all staff ids
            $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
            $zrsap_bookly_staff_schedule_items = $wpdb->prefix . 'bookly_staff_schedule_items';
            $bookly_staff_results = $wpdb->get_results(
                "SELECT * FROM $bookly_staff_table"
            );
            $wpdb->flush();


            foreach ( $bookly_staff_results as $staff_result ):
                $staff_id = $staff_result->id;
                for ( $i=1; $i<=7; $i++ ):
                    $bookly_staff_schedule_results = $wpdb->get_results(
                        "SELECT * FROM $zrsap_bookly_staff_schedule_items WHERE staff_id = {$staff_id} AND day_index = {$i}"
                    );
                    $wpdb->flush();

                    if( empty( $bookly_staff_schedule_results ) ):

                        $staff_schedules[] = array(
                            'staff_id' => $staff_id,
                            'day_index' => $i,
                            'start_time' => '00:00:00',
                            'end_time' => '24:00:00'
                        );

                    endif;
                endfor;

            endforeach;

            if( !empty( $staff_schedules ) ):
                if( wpdb_bulk_insert($zrsap_bookly_staff_schedule_items, $staff_schedules) === false ):
                    $wpdb->show_errors();
                    $wpdb->print_error();
                    echo '<div class="alert alert-danger" role="alert"> Error in insert staff schedules </div>';
                endif;
            endif;

            echo '<div class="alert alert-success" role="alert"> <strong> '. count($bookly_staff_members) .' </strong> staff member(s) has been inserted. </div>';
        else:
            $wpdb->show_errors();
            $wpdb->print_error();
            echo '<div class="alert alert-danger" role="alert"> Error in insert staff members </div>';
        endif;
    else:
        echo '<div class="alert alert-success" role="alert"> Staff Sync successfully ! </div>';
    endif;

    wp_die();

}





// hook to sync customers on created or updated to bookly
add_action( 'user_register',  'on_new_user_create', 10, 1 );
function on_new_user_create( $user_id ) {

    global $wpdb;
    $wp_user_id = $user_id;
    $user_obj = get_user_by( 'id', $wp_user_id );
    if ( isset( $_POST['first_name'] ) ):
        $email = $_POST['email'];
    else:
        $email = $user_obj->user_email;
    endif;

    if ( isset( $_POST['first_name'] ) ):
        $first_name = $_POST['first_name'];
    else:
        $first_name = get_user_meta($wp_user_id, 'first_name', true);
    endif;

    if ( isset( $_POST['last_name'] ) ):
        $last_name = $_POST['last_name'];
    else:
        $last_name = get_user_meta($wp_user_id, 'last_name', true);
    endif;


    $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'account_code', true);
    if( !empty($memb_ReferralCode_chld) ):
        $country = get_user_meta($wp_user_id ,'country', true);
        $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
        $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
        $parent_user = get_user_by_meta_data('account_code', $memb_ReferralCode_prnt);
        $parent_user_id = (int) $parent_user->data->ID;
        $parent_user_display_name = $parent_user->data->display_name;
        $child_last_name = $last_name . ' / ' . $parent_user_display_name;
        $phone = get_user_meta($wp_user_id ,'primary_phone', true);
        if( empty( $phone ) ):
            // get parent phone
            $phone = get_user_meta($parent_user_id ,'primary_phone', true);
        endif;
    else:
        $child_last_name = $last_name;
    endif;

    $full_name = $first_name . ' ' . $child_last_name;


    $current_date_object = new DateTime('now', new DateTimeZone('America/New_York'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');

    $bookly_customers[0]['wp_user_id'] = $wp_user_id;
    $bookly_customers[0]['full_name'] = $full_name;
    $bookly_customers[0]['first_name'] = $first_name;
    $bookly_customers[0]['last_name'] = $last_name;
    $bookly_customers[0]['phone'] = !empty($phone) ? $phone : '';
    $bookly_customers[0]['email'] = $email;
    $bookly_customers[0]['country'] = '';
    $bookly_customers[0]['created_at'] = $created_at;



    // insert into table bookly_customers
    if( !empty($bookly_customers) ):
        $bookly_customers_table = $wpdb->prefix . 'bookly_customers';
        wpdb_bulk_insert($bookly_customers_table, $bookly_customers);
      //  wp_die();
    endif;
}
