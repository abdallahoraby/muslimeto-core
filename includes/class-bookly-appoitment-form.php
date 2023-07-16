<?php

// register event calendar scripts
function muslimeto_modal_scripts_register() {

//    wp_register_style( 'muslimeto-modal-style', plugin_dir_url(__DIR__) . 'public/css/jquery.modal.min.css', array(), rand(), 'all' );
    wp_register_style( 'muslimeto-micromodal-style', plugin_dir_url(__DIR__) . 'public/css/micromodal.css', array(), rand(), 'all' );
//    wp_register_script( 'muslimeto-modal-script', plugin_dir_url(__DIR__) . 'public/js/jquery.modal.min.js', array('jquery'), rand(), true );
    wp_register_script( 'muslimeto-micromodal-script', plugin_dir_url(__DIR__) . 'public/js/micromodal.min.js', array('jquery'), rand(), true );
}
add_action( 'wp_enqueue_scripts', 'muslimeto_modal_scripts_register' );

//// load muslimeto-modal scripts at shortcode only
//wp_enqueue_style( 'muslimeto-micromodal-style' );
////    wp_enqueue_style( 'muslimeto-modal-style' );
////    wp_enqueue_script( 'muslimeto-modal-script' );
//wp_enqueue_script( 'muslimeto-micromodal-script' );

/*
function new_bookly_form_callback()
{

    require_once plugin_dir_path( __FILE__ ) . 'class-getTimeZones.php';

    global $wpdb;

    // get students
    $students = get_users( array( 'role__in' => array( 'student' ) ) );
    foreach ( $students as $student ):
        $students_options = '<option value="'. $student->ID .'"> '. $student->display_name . ' - ' . $student->user_email .' </option>';
    endforeach;


    // get services
    $table_name = $wpdb->prefix . 'bookly_services';
    $services = $wpdb->get_results(
        "SELECT * FROM $table_name"
    );

    $services_options = '';
    foreach ( $services as $service ):
        $services_options .= '<option value="'. $service->id .'"> ' . $service->title .' </option>';
    endforeach;

    // get categories
    $table_name = $wpdb->prefix . 'bookly_categories';
    $categories = $wpdb->get_results(
        "SELECT * FROM $table_name"
    );

    $categories_options = '';
    foreach ( $categories as $category ):
        $categories_options .= '<option value="'. $category->id .'"> ' . $category->name .' </option>';
    endforeach;


    // get timezones
    $timezones = new getTimeZones;
    $timeZonesList = $timezones->getTimeZoneValues();


    $countriesTimeZone = array_merge(
        $timeZonesList['Africa'],
        $timeZonesList['America'],
        $timeZonesList['Antarctica'],
        $timeZonesList['Arctic'],
        $timeZonesList['Asia'],
        $timeZonesList['Atlantic'],
        $timeZonesList['Australia'],
        $timeZonesList['Europe'],
        $timeZonesList['Indian'],
        $timeZonesList['Pacific']
    );


    $timezones_options = '';
    foreach($countriesTimeZone as $key => $value):
        $timeZoneOffset = ( $timezones->getTimeZoneOffset($key) == 0 ) ? $timezones->getTimeZoneOffset($key) : sprintf("%+d",$timezones->getTimeZoneOffset($key));
        $timezones_options .= '<option value="'. $key .'"> '. $value. ' UTC/GMT / ' . $timeZoneOffset .' </option>';
    endforeach;


    $hours_options = '';
    for($i = 1; $i <= 24; $i++):
        $time_value = sprintf("%02d", $i);

        if($i === 24){
            $time_value = 0;
        }

        $hours_options .= '<option value="'. $time_value .'">'. date("h.i A", strtotime("$i:00")).'</option>';
    endfor;






    ?>

    <!--    <div class="ajax_image_section">-->
    <!--        <div class="ajaxloader"></div>-->
    <!--    </div>-->


    <form id="new_booking_form" action="" method="post">
        <div class="container">
            <div class="new_bookly_form form-row align-items-center row">

                <div class="col-md-6">
                    <label class="mr-sm-2" for="bookly_students">Student:</label>
                    <div>
                        <select class="custom-select mr-sm-2 select2" id="bookly_students" name="bookly_students" required>
                            <option selected selected disabled>Choose Student...</option>
                            <?php echo $students_options?>
                        </select>
                    </div>
                </div>


                <div class="col-md-6">
                    <label class="mr-sm-2" for="bookly_timezones">TimeZone:</label>
                    <div>
                        <select class="custom-select mr-sm-2 select2" id="bookly_timezones" name="bookly_timezones" required>
                            <option selected disabled>Choose Timezone...</option>
                            <?php echo $timezones_options?>
                        </select>
                    </div>
                </div>


                <div class="booking_rows">
                    <div class="schedule_booking_section first_booking_row col-md-12">
                        <div class="col-md-12 d-flex">
                            <div class="col-md-6 bookly_effective_date_clone">
                                <label class="mr-sm-2" for="bookly_effective_date">Start Date:</label>
                                <div class="effective_date_section">
                                    <input data-toggle="datepicker" class="bookly_effective_date" name="bookly_effective_date[]" class="effective_date" value="" type="text" placeholder="enter start date" required disabled>
                                    <i class="far fa-calendar-alt datepicker_trigger"></i>
                                </div>
                            </div>

                            <i class="far fa-calendar-plus"></i>
                        </div>
                        <div class="col-md-12 d-flex">
                            <div class="col-md-12 class_days">
                                <div class="class_day">
                                    <lebel> Mon </lebel>
                                    <input type="checkbox" id="Mon" name="class_days[]" value="mon" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Tue </lebel>
                                    <input type="checkbox" id="Tue" name="class_days[]" value="tue" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Wed </lebel>
                                    <input type="checkbox" id="Wed" name="class_days[]" value="wed" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Thu </lebel>
                                    <input type="checkbox" id="Thu" name="class_days[]" value="thu" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Fri </lebel>
                                    <input type="checkbox" id="Fri" name="class_days[]" value="fri" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Sat </lebel>
                                    <input type="checkbox" id="Sat" name="class_days[]" value="sat" required>
                                </div>

                                <div class="class_day">
                                    <lebel> Sun </lebel>
                                    <input type="checkbox" id="Sun" name="class_days[]" value="sun" required>
                                </div>


                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="mr-sm-2" for="bookly_categories">Categories:</label>
                            <div class="bookly_categories">
                                <select class="custom-select mr-sm-2 select2" name="bookly_categories" required>
                                    <option selected disabled>Choose Category...</option>
                                    <?php echo $categories_options?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="mr-sm-2" for="bookly_services">Services:</label>
                            <div class="bookly_services_cloned">
                                <select class="custom-select mr-sm-2 select2" name="bookly_services[]" required>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 bookly_start_time">
                            <label class="mr-sm-2" for="bookly_start_time">Start Time:</label>
                            <div>
                                <select class="custom-select mr-sm-2 select2 hours_selector_options" name="bookly_start_time[]" id="bookly_start_time" required>
                                    <option selected disabled>Choose Start Hour...</option>
                                    <?php echo $hours_options; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="mr-sm-2" for="bookly_class_duration">Class Duration:</label>
                            <div class="bookly_class_duration">
                                <select class="custom-select mr-sm-2 select2" name="bookly_class_duration[]" id="bookly_class_duration" required>
                                    <option selected disabled>Choose Duration...</option>
                                    <option value="15"> 15 Minutes </option>
                                    <option value="30"> 30 Minutes </option>
                                    <option value="45"> 45 Minutes </option>
                                    <option value="60"> 1 Hour </option>
                                    <option value="75"> 1 Hour & 15 Minutes </option>
                                    <option value="90"> 1 Hour & 30 Minutes </option>
                                    <option value="105"> 1 Hour & 45 Minutes </option>
                                    <option value="120"> 2 Hours </option>
                                </select>
                            </div>
                        </div>


                        <!--                    <div class="col-md-12 d-flex align-items-center teacher_section teacher_section_cloned ">-->
                        <!--                        <div class="col-md-12 text-center"><a href="#" class="find_teacher"> Find Teacher <i class="fas fa-chalkboard-teacher"></i> </a> </div>-->
                        <!---->
                        <!--                        <div class="col-md-12 teacher_select" hidden>-->
                        <!--                            <label class="mr-sm-2" for="bookly_teacher">Available Teachers:</label>-->
                        <!--                            <div>-->
                        <!--                                <select class="custom-select mr-sm-2 select2" name="bookly_teacher[]">-->
                        <!---->
                        <!--                                </select>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                        <!---->
                        <!--                    </div>-->

                        <div class="col-md-12 d-flex align-items-center teacher_section teacher_section_cloned ">
                            <ul id="progress">

                                <li class="col-md-8 teacher_select">
                                    <label class="mr-sm-2" for="bookly_teacher">Available Teachers:</label>
                                    <select class="custom-select mr-sm-2 select2" name="bookly_teacher[]" required>

                                    </select>
                                </li>
                                <li class="col-md-4 text-center"> <a href="#" class="find_teacher"> <i class="fas fa-chalkboard-teacher"></i> Find Available Teacher  </a>  </li>
                            </ul>
                        </div>
                        <input type="hidden" class="overlap_status" value="1">

                    </div>

                </div>





            </div>
        </div>

        <button type="submit" class="submit_booking" name="submit_booking"> Submit </button>
    </form>

    <div class="ajax_result"></div>


    <?php



}

add_shortcode('new_bookly_form', 'new_bookly_form_callback');

*/


/*****************
 * Bookly Form Shortcode to Add Single program for student
 ****************/

function single_program_bookly_form_callback()
{

    require_once plugin_dir_path( __FILE__ ) . 'class-getTimeZones.php';

    global $wpdb;

    // get students ( bookly customers )
//    $table_name = $wpdb->prefix . 'bookly_customers';
//    $customers = $wpdb->get_results(
//        "SELECT * FROM $table_name"
//    );
//    $wpdb->flush();
////    $students = get_users( array( 'role__in' => array( 'student' ) ) );
//    $students_options = '';
//    foreach ( $customers as $customer ):
//
//        $wp_user_id = $customer->wp_user_id;
//        $first_name = $customer->first_name;
//        $last_name = $customer->last_name;
//        $country = ' / ' . get_user_meta($wp_user_id ,'memb_Country', true);
//        $memb_ReferralCode_chld = get_user_meta($wp_user_id ,'memb_ReferralCode', true);
//        $memb_ReferralCode_chld = substr($memb_ReferralCode_chld, 5);
//        $memb_ReferralCode_prnt = 'prnt-' . $memb_ReferralCode_chld;
//        $parent_user = get_user_by_meta_data('memb_ReferralCode', $memb_ReferralCode_prnt);
//        $parent_user_id = (int) $parent_user->data->ID;
//        $parent_user_display_name = ' / ' . $parent_user->data->display_name;
//        $child_last_name = $last_name . $parent_user_display_name;
//        $full_name = $first_name . ' ' . $child_last_name;
//
//        if( substr( $customer->email, 0, 3 ) === "std" ):
//            $email = ' / ' . $customer->email;
//        else:
//            $email = '';
//        endif;
//
//        $students_options .= '<option value="'. $wp_user_id .'"> '. $full_name . $email . $country .' <a class="add-to-class btn"> add to class </a></option>';
//    endforeach;


    // get services
    $table_name = $wpdb->prefix . 'bookly_services';
    $services = $wpdb->get_results(
        "SELECT * FROM $table_name"
    );
    $wpdb->flush();

    $services_options = '';
    foreach ( $services as $service ):
        $services_options .= '<option value="'. $service->id .'"> ' . $service->title .' </option>';
    endforeach;

    // get categories
    $table_name = $wpdb->prefix . 'bookly_categories';
    $categories = $wpdb->get_results(
        "SELECT * FROM $table_name"
    );
    $wpdb->flush();

    $categories_options = '';
    foreach ( $categories as $category ):
        $categories_options .= '<option value="'. $category->id .'"> ' . $category->name .' </option>';
    endforeach;


    // get timezones
    $timezones = new getTimeZones;
    $timeZonesList = $timezones->getTimeZoneValues();


    $countriesTimeZone = array_merge(
        $timeZonesList['Africa'],
        $timeZonesList['America'],
        $timeZonesList['Antarctica'],
        $timeZonesList['Arctic'],
        $timeZonesList['Asia'],
        $timeZonesList['Atlantic'],
        $timeZonesList['Australia'],
        $timeZonesList['Europe'],
        $timeZonesList['Indian'],
        $timeZonesList['Pacific']
    );


    $timezones_options = '';
    foreach($countriesTimeZone as $key => $value):
        $timeZoneOffset = ( $timezones->getTimeZoneOffset($key) == 0 ) ? $timezones->getTimeZoneOffset($key) : sprintf("%+d",$timezones->getTimeZoneOffset($key));
        $timezones_options .= '<option value="'. $key .'"> '. $value. ' UTC/GMT / ' . $timeZoneOffset .' </option>';
    endforeach;


    $hours_options = '';
    for($i = 1; $i <= 24; $i++):
        $time_value = sprintf("%02d", $i);

        if($i === 24){
            $time_value = 0;
        }

        $hours_options .= '<option value="'. $time_value .'">'. date("h A", strtotime("$i:00")).'</option>';
    endfor;

    $minutes_options = '
        <option value="0"> 00 </option>
        <option value="15"> 15 </option>
        <option value="30"> 30 </option>
        <option value="45"> 45 </option>
    ';


    // get bb group select options
    $bb_groups_options = '';
    $bb_groups = groups_get_groups( array(
        'per_page' => 9999
    ) );
    foreach( $bb_groups['groups'] as $group ):
        $group_title = $group->name;
        $group_id = $group->id;
        $group_slug = $group->slug;
        $bb_groups_options .= '<option value="'. $group_id .'"> '. $group_id . ' - ' . $group_title . $group_slug .' </option>';
    endforeach;




    ?>


    

    <div class="edit_programm_page">
        <h3 class="add-new-title"> Add New Program </h3>

        <form id="single_program_booking_form" class="load_gif add-mode" action="" method="post">
            <div class="ajax_image_section"> <div class="ajaxloader"></div> </div>
            <?php wp_nonce_field( 'submit_single_program_booking_form', 'nonce_submit_single_program_booking_form' ); ?>

            <div class="container">
                <div class="new_bookly_form form-row align-items-center row">

                    <div class="col-md-12 form-section">
                        <div class="row d-flex justify-content-center group-switch-section">
                            <div class="col-md-12">
                                <div class="switch-row">
                                    <label> 1 on 1 </label>
                                    <label class="switch">
                                        <input type="checkbox" value="one-to-one" class="program-type" checked>
                                        <div>
                                            <span></span>
                                        </div>
                                    </label>
                                    <label> Group </label>
                                </div>

                            </div>

                            <div class="col-md-6 hidden">
                                <div class="switch-row">
                                    <label> New </label>
                                    <label class="switch">
                                        <input type="checkbox" value="new" class="program-status" checked>
                                        <div>
                                            <span></span>
                                        </div>
                                    </label>
                                    <label> Transferred </label>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="bb-group-select col-md-12">
                            <div class="group_select">
                                <label for="family-group"><input type="radio" id="family-group" name="group_type_select" class="group_type_select" value="family-group">Family</label>
                                <label for="open"> <input type="radio" id="open" name="group_type_select" class="group_type_select" value="open-group"> Open</label>
                                <label for="mvs"><input type="radio" id="mvs" name="group_type_select" class="group_type_select" value="mvs"> MVS</label>
                            </div>
                            <!--                        <label for="family"> <input type="checkbox" id="family" value=""> Family Group </label>-->

                            <div class="sub-group-options">

                                <div class="link-group-options">
                                    <div class="link_to_existing-section hidden" hidden>
                                        <label for="link_to_existing"><input type="radio" id="link_to_existing" name="link_to_group" class="link_to_group" value="link_to_existing" disabled> Link Existing Group </label>
                                        <select class="custom-select mr-sm-2 select2" id="bb_group_id" name="bb_group_id">
                                            <option selected disabled > choose group </option>
                                            <?php echo $bb_groups_options; ?>
                                        </select>
                                    </div>
                                    <label for="msl_create_zoom_meeting"><input type="radio" id="msl_create_zoom_meeting" name="link_to_group" class="link_to_group hidden" value="msl_create_zoom_meeting" disabled checked> <i class="bb-icon-checkbox bb-icon-l"></i> Create New Zoom Meeting </label>
                                </div>


                            </div>



                        </div>
                        <div class="zoom_meeting_id col-md-6" hidden>
                            <label for="zoom_meeting_id"> zoom meeting id: </label>
                            <input type="text" id="zoom_meeting_id" value="" >
                        </div>
                    </div>


                    <div class="col-md-12 d-flex learners-div">
                        <div class="col-md-9">

                            <label class="mr-sm-2" for="bookly_students">Learner(s):</label>
                            <div>
                                <select class="custom-select mr-sm-2 select2" id="bookly_students" name="bookly_students" multiple required>
                                    <option selected disabled > choose learner(s) </option selected disabled>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <a href="#" class="find-learners"> <i class="fas fa-search"></i> Find Learner(s)  </a>
                        </div>

                    </div>

                    <div class="modal micromodal-slide" id="find-learners-modal" aria-hidden="true">
                        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                                <header class="modal__header">
                                    <h2 class="modal__title" id="modal-1-title">
                                        Add learner(s) to class
                                    </h2>
                                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                                </header>
                                <main class="modal__content" id="modal-1-content">

                                    <div class="ajax_image_section find-users"> <div class="ajaxloader"></div> </div>

                                    <label for="parent_user_email">
                                        Find Users:
                                        <a href="#" onclick="paste();" class="paste-btn"> <i class="fas fa-paste"></i> paste email </a>
                                        <input type="text" value="" id="parent_user_email" placeholder="parent user email" class="paste-here">  <i class="fas fa-search find-user"></i>
                                    </label>

                                    <span class="childs-result" style="display: block"></span>

                                </main>
                                <footer class="modal__footer">

                                </footer>
                            </div>
                        </div>
                    </div>

                    <div class="modal micromodal-slide" id="error-modal" aria-hidden="true">
                        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                                <header class="modal__header">
                                    <h2 class="modal__title" id="modal-1-title">

                                    </h2>
                                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                                </header>
                                <main class="modal__content" id="modal-1-content">


                                </main>
                                <footer class="modal__footer">

                                </footer>
                            </div>
                        </div>
                    </div>


                    <div class="modal micromodal-slide" id="success-modal" aria-hidden="true">
                        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                                <header class="modal__header">
                                    <h2 class="modal__title" id="modal-1-title">

                                    </h2>
                                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                                </header>
                                <main class="modal__content" id="modal-1-content">



                                </main>
                                <footer class="modal__footer">

                                </footer>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 bookly_categories_section">
                        <label class="mr-sm-2" for="bookly_categories">Categories:</label>
                        <div class="bookly_categories">
                            <select class="custom-select mr-sm-2 select2" name="bookly_categories" required>
                                <option selected disabled>Choose Category...</option>
                                <?php echo $categories_options?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="mr-sm-2" for="bookly_services">Services:</label>
                        <div class="bookly_services_cloned">
                            <select class="custom-select mr-sm-2 select2" name="bookly_services[]" required>

                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 bookly_timezones_section">
                        <label class="mr-sm-2" for="bookly_timezones">TimeZone:</label>
                        <div>
                            <select class="custom-select mr-sm-2 select2" id="bookly_timezones" name="bookly_timezones" required>
                                <option selected disabled>Choose Timezone...</option>
                                <?php echo $timezones_options?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 bookly_effective_date_clone">
                        <label class="mr-sm-2" for="bookly_effective_date">Start Date:</label>
                        <div class="effective_date_section">
                            <input data-toggle="datepicker" class="bookly_effective_date" name="bookly_effective_date[]" class="effective_date" value="" type="text" placeholder="enter start date" required disabled>
                            <i class="far fa-calendar-alt datepicker_trigger"></i>
                        </div>
                    </div>





                    <div class="booking_rows">
                        <div class="schedule_booking_section first_booking_row col-md-12">

                            <div class="col-md-12 d-flex align-items-end">
                                <div class="col-md-12 d-flex">
                                    <div class="col-md-5 d-flex">
                                        <div class="col-md-12 class_days">
                                            <div class="class_day">
                                                <lebel> Mon </lebel>
                                                <input type="checkbox" id="Mon" name="class_days[]" value="mon">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Tue </lebel>
                                                <input type="checkbox" id="Tue" name="class_days[]" value="tue">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Wed </lebel>
                                                <input type="checkbox" id="Wed" name="class_days[]" value="wed">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Thu </lebel>
                                                <input type="checkbox" id="Thu" name="class_days[]" value="thu">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Fri </lebel>
                                                <input type="checkbox" id="Fri" name="class_days[]" value="fri">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Sat </lebel>
                                                <input type="checkbox" id="Sat" name="class_days[]" value="sat">
                                            </div>

                                            <div class="class_day">
                                                <lebel> Sun </lebel>
                                                <input type="checkbox" id="Sun" name="class_days[]" value="sun">
                                            </div>


                                        </div>
                                    </div>
                                    <div class="col-md-7 d-flex time-duration">
                                        <div class="bookly_start_time d-flex align-items-center">
                                            <div class="d-flex start-time">
                                                <label class="mr-sm-2" for="bookly_start_time">Start Time: </label>
                                                <select class="custom-select mr-sm-2 select2 hours_selector_options" name="bookly_start_time[]" required>
                                                    <?php echo $hours_options; ?>
                                                </select>

                                                <select class="custom-select mr-sm-2 select2 minutes_selector_options" name="bookly_start_time_minutes[]"  required>
                                                    <?php echo $minutes_options; ?>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="d-flex duration-div">
                                            <label class="mr-sm-2" for="bookly_class_duration"> Duration:</label>
                                            <div class="bookly_class_duration">
                                                <select class="custom-select mr-sm-2 select2" name="bookly_class_duration[]" id="bookly_class_duration" required>

                                                    <option value="15"> 15 Minutes </option>
                                                    <option value="30"> 30 Minutes </option>
                                                    <option value="45"> 45 Minutes </option>
                                                    <option value="60"> 1 Hour </option>
                                                    <option value="75"> 1 Hour & 15 Minutes </option>
                                                    <option value="90"> 1 Hour & 30 Minutes </option>
                                                    <option value="105"> 1 Hour & 45 Minutes </option>
                                                    <option value="120"> 2 Hours </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>




                            <input type="hidden" class="final_teacher_0 final_teachers_array">

                        </div>

                    </div>


                    <div class="col-md-12 text-center">
                        <button class="add_new_row" disabled> <i class="far fa-calendar-plus"></i> Add New Schedule </button>
                    </div>

                    <br>

                    <input type="hidden" value="" id="validate_submit_btn">
                    <a href="#" class="validate_submit_data"></a>


                    <div class="col-md-12 mt-2">
                        <div class="switch-row teachers-switch">
                            <label> Check All Teachers </label>
                            <label class="switch">
                                <input type="checkbox" value="all" class="teacher-check" checked>
                                <div>
                                    <span></span>
                                </div>
                            </label>
                            <label> Check Single Teacher </label>

                        </div>
                    </div>


                    <div class="col-md-12 d-flex align-items-center teacher_section teacher_section_cloned ">

                        <ul id="progress">
                            <li class="col-md-4 text-center">
                                <a href="#" class="find_teacher all"> <i class="bb-icon-users bb-icon-l"></i> Find Available Teacher  </a>
                                <a href="#" class="find_teacher single"> <i class="bb-icon-check bb-icon-l"></i> Confirm Availabilty  </a>
                            </li>
                            <li class="col-md-8 teacher_select">
                                <select class="custom-select mr-sm-2 select2" name="bookly_teacher[]" required>
                                </select>
                            </li>

                        </ul>

                    </div>




                </div>
            </div>

            <button type="submit" class="submit_booking" name="submit_booking"> Submit </button>
        </form>

        <div class="ajax_result"></div>
    </div>

    <?php



}

add_shortcode('single_program_bookly_form', 'single_program_bookly_form_callback');