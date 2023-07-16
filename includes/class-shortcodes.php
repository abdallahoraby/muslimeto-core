<?php


// shortcode to fix missing BB groups that has no appointments records on bookly_customer_appointments table
function fix_missing_bb_groups() {

    ?>

    <?php
    // get all teachers
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    // get all teachers
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table"
    );
    $wpdb->flush();

    $teachers_options = '';
    foreach ( $staff_results as $staff_result ):
        $teacher_id = $staff_result->wp_user_id;
        $teacher_name = $staff_result->full_name;
        $teacher_email = $staff_result->email;
        $teachers_options .= '<option value="'. $teacher_id .'"> '.$teacher_id.' - '. $teacher_name .' - '. $teacher_email .'  </option>';
    endforeach;


    ?>

    <form action="" id="fix_bb_groups_ca_records">
        <div class="ajax_image_section"> <div class="ajaxloader"></div> </div>
        <select name="fix_teacher" class="select2" id="fix_teacher">
            <option selected disabled> -- choose teacher -- </option>
            <?php  echo $teachers_options; ?>
        </select>

        <select name="fix_bb_group" class="select2" id="fix_bb_group">
            <option selected disabled> -- choose group -- </option>
        </select>

        <button type="submit" class="fix_missing_bb_groups"> Fix Group </button>
        <a href="#" class="refresh_groups_select btn"> Refresh </a>
    </form>


    <div class="modal micromodal-slide" id="fix-modal" aria-hidden="true">
        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                <header class="modal__header">
                    <h2 class="modal__title" id="modal-1-title">
                        Fix Single Program Appointments Status
                    </h2>
                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <main class="modal__content" id="modal-1-content">

                </main>
                <footer class="modal__footer">
                    <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window">Close</button>
                </footer>
            </div>
        </div>
    </div>
    <div class="fix-bbgroups-result"></div>

<?php }
add_shortcode('fix_missing_bb_groups', 'fix_missing_bb_groups');


function check_teacher_missing_appointments(){ ?>

    <?php
    // get all teachers
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_appointments_customer_table = $wpdb->prefix . 'bookly_customer_appointments';
    // get all teachers
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table"
    );
    $wpdb->flush();

    $teachers_options = '';
    foreach ( $staff_results as $staff_result ):
        $teacher_id = $staff_result->id;
        $teacher_name = $staff_result->full_name;
        $teacher_email = $staff_result->email;
        $teachers_options .= '<option value="'. $teacher_id .'"> '.$teacher_id.' - '. $teacher_name .' - '. $teacher_email .'  </option>';
    endforeach;

    ?>

    <form action="">
        <select name="teachers" class="select2" id="teachers" multiple>
            <option selected disabled>choose teacher</option>
            <?php  echo $teachers_options; ?>
        </select>
        <button type="submit" class="check_appointments"> check </button>
    </form>

    <div class="check-teachers-result"></div>


<?php }
add_shortcode('check_teacher_missing_appointments', 'check_teacher_missing_appointments');


// shortcode for open makeup balance credit

function open_makeup_balance_form_callback() {
    $wp_user_id = get_current_user_id();
    $user = wp_get_current_user(); // getting & setting the current user
    $roles = ( array ) $user->roles; // obtaining the role
    // if user has one role of these ( support, enrollment, administrator, assistant_principle, hr ) set as "staff"
    if( in_array( 'support', $roles) || in_array( 'enrollment', $roles) || in_array( 'administrator', $roles) || in_array( 'assistant_principle', $roles) || in_array( 'hr', $roles) ):
        $user_role = 'staff';
    else:
        $user_role = '';
    endif;


    ?>

    <div class="row opening-balance-form p-5">

        <div class="col-md-6">
            <form action="" class="d-flex flex-flow-wrap load_gif submit_open_makeup">

                <input type="hidden" value="open-balance" id="trans_type">
                <input type="hidden" value="<?php echo $wp_user_id; ?>" id="user_id">
                <input type="hidden" value="<?php echo $user_role; ?>" id="user_role">

                <div class="ajax_image_section"> <div class="ajaxloader"></div> </div>

                <div class="col-md-12 d-flex parent-select">
                    <a href="#" class="find-learners"> <i class="fas fa-search"></i> Find Parent  </a>
                    <div class="find-parent-result"> <div class="danger"> please select parent first</div> </div>
                    <select class="custom-select parent_id" id="bookly_students" name="bookly_students" hidden required> </select>
                </div>

                <div class="col-md-6">
                    <label for="trans_amount">  Total Time (In Minutes) : </label>
                    <input type="number" placeholder="enter opening balance" id="trans_amount" required>
                </div>

                <div class="col-md-6">
                    <label for="hrs_mins">  Hrs : Mins </label>
                    <input type="text" placeholder="Hrs : Mins" id="hrs_mins" disabled>
                </div>



                <div class="col-md-12">
                    <label for="trans_notes"> Notes: </label>
                    <textarea name="trans_notes" id="trans_notes" cols="30" class="full-width" rows="5" placeholder="enter trans notes"></textarea>
                </div>

                <button type="submit" class="submit_blue" id="submit_open_form"> Save </button>

            </form>
        </div>

        <div class="col-md-6">

            <div class="parent_makeup_logs_table">  </div>


        </div>



    </div>


    <div class="makeup-log-result"></div>

    <div class="modal micromodal-slide" id="find-learners-modal" aria-hidden="true">
        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                <header class="modal__header">
                    <h2 class="modal__title" id="modal-1-title">
                        select parent:
                    </h2>
                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <main class="modal__content" id="modal-1-content">

                    <div class="ajax_image_section find-users"> <div class="ajaxloader"></div> </div>
                    <label for="parent_user_email">
                        Find Users:
                        <input type="text" value="" id="parent_user_email" placeholder="parent user email"> <i class="fas fa-search find-user"></i>
                        <input type="hidden" id="get_parent_only" value="parent_only">
                    </label>

                    <br><br>
                    <span class="childs-result" style="display: block"></span>

                </main>
                <footer class="modal__footer">
                    <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window">Close</button>
                </footer>
            </div>
        </div>
    </div>

    <div class="modal micromodal-slide" id="result-modal" aria-hidden="true">
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
                    <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window">Close</button>
                </footer>
            </div>
        </div>
    </div>



    <?php


}
add_shortcode('open_makeup_balance_form', 'open_makeup_balance_form_callback');


// shortcode for attendance table capture for teacher
function attendance_capture_table_callback(){
    global $wpdb;
    $user = wp_get_current_user(); // getting & setting the current user
    $roles = ( array ) $user->roles; // obtaining the role
    // if user has one role of these ( support, enrollment, administrator, assistant_principle, hr ) set as "staff"
    if( in_array( 'support', $roles) || in_array( 'enrollment', $roles) || in_array( 'administrator', $roles) || in_array( 'assistant_principle', $roles) || in_array( 'hr', $roles) ):
        $user_role = 'staff';
    else:
        $user_role = 'teacher';
    endif;

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $current_date_object->format('Y-m-d H:i:s');
    $current_datetime = $current_date_object->format('Y-m-d H:i:s');
    $current_month = (int) date('m', strtotime($current_datetime));
    $current_month_long = date('m', strtotime($current_datetime));
    $prev_month_long = $current_month_long - 1;
    $current_month_name = date('M', strtotime($current_datetime));
    $current_year = date('Y', strtotime($current_datetime));

    // if current day >= 22 => report_month = current_month
    if( (int) date('d', strtotime($current_datetime)) >= 22 ):
        $report_start_month = $current_month_long;
        $report_end_month = $current_month_long + 1;
    else:
        $report_start_month = $current_month_long - 1;
        $report_end_month = $current_month_long;
    endif;

    $report_start_date = $current_year . '-' . $report_start_month . '-22';
    $next_month_long = date('m', strtotime('+1 month', strtotime($report_start_date)));
    $report_end_date = $current_year . '-' . $report_end_month . '-21';


    if( !empty($_GET['attendance_date']) ):
        $current_month_long = date('m', strtotime($_GET['attendance_date']));
        $prev_month_long = $current_month_long - 1;
        $current_month_name = date('M', strtotime($_GET['attendance_date']));
        $current_year_report = date('Y', strtotime($_GET['attendance_date']));

        // if current day >= 22 => report_month = current_month
        if( (int) date('d', strtotime($_GET['attendance_date'])) >= 22 ):
            $report_start_month = $current_month_long;
            $report_end_month = $current_month_long + 1;
        else:
            $report_start_month = $current_month_long - 1;
            $report_end_month = $current_month_long;
        endif;

        $report_start_date = $current_year . '-' . $report_start_month . '-22';
        $report_end_date = $current_year . '-' . $report_end_month . '-21';
    endif;


    // get staff id

    $wp_user_id = get_current_user_id();
    $selected_date = date('Y-m-d');

    if( !empty($_GET['team_staff_id']) && isset($_GET['team_staff_id']) ):
        $staff_id = $_GET['team_staff_id'];
    else:
        $staff_id = getStaffId($wp_user_id);
    endif;


    // get teacher full name
    $teacher_full_name = getBooklyStaffFullName($staff_id);


    if( !empty($_GET['attendance_date']) && isset($_GET['attendance_date']) ):
        $selected_date = $_GET['attendance_date'];
    endif;


    // convert selected date time to EST
    //$selected_date = date('Y-m-d' , strtotime( convertTimeZone($selected_date . ' 00:00:00', 'Africa/Cairo') ) );


    $prev_date = date('Y-m-d', strtotime($selected_date .' -1 day'));
    $next_date = date('Y-m-d', strtotime($selected_date .' +1 day'));
    $current_day_short = date('D', strtotime($selected_date ));


    // get -60 days before current date
    $no_show_days_limit = new DateTime($selected_date);
    $no_show_days_limit->sub(new DateInterval('P60D'));
    $no_show_days_limit = $no_show_days_limit->format('Y-m-d H:i:s');

    global $wp;
    $current_page_url = home_url( $wp->request );

    $user_is_support = user_has_role(get_current_user_id(), 'support');
    $user_is_enrollment = user_has_role(get_current_user_id(), 'enrollment');
    $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');
    $access_alert = '<br> <div class="alert text-center"> Sorry! you do not have access to this page, or you have to select teacher first. </div> <br>';

    if(  $user_is_support || $user_is_enrollment ):
        $all_staff = getAllBooklyStaff();
        if( !empty($all_staff) ):
            $teachers_options = '';
            $selected_id = !empty($_GET['team_staff_id']) ? (int) $_GET['team_staff_id'] : '';
            foreach ($all_staff as $staff):
                if( $selected_id ===  (int) $staff->id ):
                    $selected = 'selected';
                else:
                    $selected = '';
                endif;
                // generate options for select
                $staff_full_name = "U. " .  ucwords(str_replace("."," ",strstr($staff->email, '@', true)));
                $teachers_options .= '<option '.$selected.' value="'. $staff->id .'"> ' . $staff->id . ' ' . $staff_full_name . ' ' . $staff->email .' </option>';
            endforeach;
        endif;
        $hide_teachers_select = '';
        $reset_url = $current_page_url;
    elseif ( $user_is_team_leader ):
        // is teacher is team leader
        $team_teachers = get_team_teachers( get_current_user_id() );
        if( !empty($team_teachers) ):
            $current_teacher_bookly_id = getStaffId( get_current_user_id() );
            $current_user_email = get_user_by('id', get_current_user_id())->data->user_email;
            $current_staff_full_name = "U. " .  ucwords(str_replace("."," ",strstr($current_user_email, '@', true)));
            $current_selected = !isset($_GET['team_staff_id']) ? 'selected' : '';
            $teachers_options = "<option value='$current_teacher_bookly_id' $current_selected> $current_staff_full_name $current_user_email </option>";
            $selected_id = !empty($_GET['team_staff_id']) ? (int) $_GET['team_staff_id'] : '';
            foreach ($team_teachers as $team_teacher):
                if( $selected_id ===  $team_teacher['bookly_staff_id'] ):
                    $selected = 'selected';
                else:
                    $selected = '';
                endif;
                // generate options for select
                $staff_full_name = "U. " .  ucwords(str_replace("."," ",strstr($team_teacher['email'], '@', true)));
                $teachers_options .= '<option '.$selected.' value="'. $team_teacher['bookly_staff_id'] .'"> ' . $team_teacher['bookly_staff_id'] . ' ' . $staff_full_name . ' ' . $team_teacher['email'] .' </option>';
            endforeach;
        endif;
        $reset_url = "?team_staff_id=" . getStaffId( get_current_user_id() );
    else:
        $teachers_options = '';
        $hide_teachers_select = 'hidden';
    endif;



    if( $selected_date == date('Y-m-d') ):
        $disable_today_btn = 'disabled';
    else:
        $disable_today_btn = '';
    endif;


    ?>

    <div class="attendance-teacher-table-section">

    <style>
        h1.entry-title{
            display: none;
        }

        a.reset_appointments_view.button.disabled {
            opacity: 0.5;
        }

    </style>


    <div class="team_teachers attendance-mode <?php echo $hide_teachers_select; ?>">
        <select class="select2 get_staff_appointments" id="team_staff_id">
            <option disabled selected> -- select teacher --</option>
            <?php echo $teachers_options; ?>
        </select>
        <input type="hidden" id="current_page_url" value="<?php echo $current_page_url; ?>">
        <input type="hidden" id="display_teacher_timezone" value="true">

        <a href="<?= $reset_url ?>" class="reset_appointments_view btn"> Reset </a>
    </div>



    <div class="attendance-container">
    <input type="hidden" id="current_page_url" value="<?php echo $current_page_url; ?>">
    <div class="attendance-header">
        <span>
            <?php echo $teacher_full_name; ?>
            <div class="monthly-report-section">
                <?php echo date('M d', strtotime($report_start_date)) . ' - ' . date('M d', strtotime($report_end_date)); ?> (Total Hrs: <?php echo getMonthlyStaffTotalHrs($staff_id, $report_start_date, $report_end_date); ?>)
            </div>
        </span>
        <div class="attendate-datepicker-section">

            <a class="reset_appointments_view button <?= $disable_today_btn ?>" <?= $disable_today_btn ?> > Today </a>
            <span> <?php echo $current_day_short; ?> </span>
            <a href="" data-selected-date="<?php echo $prev_date; ?>" class="previous_date swap_date"> <i class="fas fa-angle-left"></i> </a>
            <input data-toggle="datepicker" class="attendance-date-picker"  value="<?php echo $selected_date; ?>" type="text" >
            <a href="" data-selected-date="<?php echo $next_date; ?>"  class="next_date swap_date"> <i class="fas fa-angle-right"></i> </a>

        </div>


    </div>

    <?php
    if( !empty($staff_id) ):

        $staff_timezone = getStaffTimezone($staff_id);

        // add +1day and -1day tot selected
        $next_day_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
        $prev_day_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));


        // get staff appointments where date like today
        $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
        $staff_appointments_results = $wpdb->get_results(
            "SELECT * FROM $bookly_appointments_table WHERE staff_id = {$staff_id} AND start_date LIKE '%{$selected_date}%'
            UNION SELECT * FROM $bookly_appointments_table WHERE staff_id = {$staff_id} AND start_date LIKE '%{$next_day_date}%'
            UNION SELECT * FROM $bookly_appointments_table WHERE staff_id = {$staff_id} AND start_date LIKE '%{$prev_day_date}%'"
        );
        $wpdb->flush();


        if( !empty($staff_appointments_results) ):
            ?>
            <input type="hidden" value="<?php echo $user_role; ?>" class="user-role" >
            <input type="hidden" value="<?php echo $wp_user_id; ?>" class="user-id" >
            <input type="hidden" value="<?php echo $created_at; ?>" class="created-at" >

            <form class="attendance-table-form">

                <table class="attendance-capture collapse-search">
                    <thead>
                    <tr>
                        <th class="cid"> CID </th>
                        <!--                        <th class="learning-program">Course</th>-->
                        <th class="student-name">Student</th>
                        <th class="datetime sorting_asc"> Time (Cairo) </th>
                        <th class="status"> Status</th>
                        <th class="notes">Notes</th>
                        <th class="actions">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    foreach ( $staff_appointments_results as $staff_appointments_result ):


                        $appointmnet_id = (int) $staff_appointments_result->id;
                        $start_time = date( 'h:i A' , strtotime($staff_appointments_result->start_date) );
                        $start_date_time = date( 'Y-m-d H:i:s' , strtotime($staff_appointments_result->start_date) );
                        $end_time = date( 'h:i A' , strtotime($staff_appointments_result->end_date) );
                        $end_date_time = date( 'Y-m-d H:i:s' , strtotime($staff_appointments_result->end_date) );
                        $start_day = date( 'l' , strtotime($staff_appointments_result->start_date) );
                        $start_date = date( 'Y-m-d' , strtotime($staff_appointments_result->start_date) );
                        $actual_min = ( strtotime($staff_appointments_result->end_date) - strtotime($staff_appointments_result->start_date) ) / 60;
                        $service_id = $staff_appointments_result->service_id;
                        $service_name = getBooklyServiceName($service_id);
                        $category_id = getBooklyServiceCategoryId($service_id);
                        $category_name = getBooklyServiceCategoryName($category_id);

                        $converted_start_time = date( 'h:i A' ,strtotime(convertToUserTimeZone($staff_appointments_result->start_date, $staff_timezone) ) );
                        $converted_end_time = date( 'h:i A' , strtotime( convertToUserTimeZone($staff_appointments_result->end_date, $staff_timezone) ) );

                        $converted_start_date = date( 'Y-m-d' ,strtotime(convertToUserTimeZone($staff_appointments_result->start_date, $staff_timezone) ) );

                        // convert date from UTC to staff_timezone to show in attendance table
                        $start_date_converted = date('Y-m-d' , strtotime( convertToUserTimeZone($start_date_time, $staff_timezone) ) );

                        $today_date = date('Y-m-d', strtotime($created_at));

                        if( strtotime($start_date_converted) <= strtotime($today_date) ):
                            // user can edit attendance
                            $user_can_edit = 'user_can_edit';
                        else:
                            $user_can_edit = 'user_cannot_edit';
                        endif;



                        if( $start_date_converted === $selected_date ):
                            // get customer info
                            $customer_appt_record = getBooklyCA($appointmnet_id);
                            $hide_record = '';
                        else:
                            $customer_appt_record = false;
                            $hide_record = 'hidden';
                        endif;


                        if( !empty($customer_appt_record) ):



                            $ca_id = $customer_appt_record[0]->id;
                            $customer_id = $customer_appt_record[0]->customer_id;
                            $customer_wp_user_id = getBooklyWpUserId($customer_id);
                            $customer_full_name = getCustomerFullName2lines($customer_wp_user_id);
                            $attendance_status = $customer_appt_record[0]->status;
                            $progress_notes = $customer_appt_record[0]->notes;


                            if(checkIfParent($customer_wp_user_id)):
                                $parent_id = $customer_wp_user_id;
                            else:
                                // get parent user id
                                $parent_id = getParentID($customer_wp_user_id);
                            endif;

                            if( !empty($parent_id) ):
                                // get makeup balance for parent
                                $parent_makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $parent_id );
                            else:
                                $parent_makeup_balance = 0;
                            endif;


                            // no show s check
                            // 1- get all CA records with status != approved
                            $bookly_appts_table = $wpdb->prefix . 'bookly_appointments';
                            $appts_results = $wpdb->get_results(
                                "SELECT * FROM `zrsap_bookly_appointments`
                                WHERE (start_date BETWEEN '{$no_show_days_limit}' AND '{$selected_date}') AND staff_id = {$staff_id} ORDER BY start_date DESC"
                            );
                            $wpdb->flush();

                            $previous_ca_status = [];
                            if( !empty($appts_results) ):

                                foreach ( $appts_results as $appts_result ):

                                    $no_show_appointment_id = $appts_result->id;

                                    // check if this appointment id related to current customer id
                                    $no_show_ca_record = getBooklyCA($no_show_appointment_id);
                                    if( !empty($no_show_ca_record) ):

                                        if($customer_id === $no_show_ca_record[0]->customer_id):
                                            if( $no_show_ca_record[0]->status !== 'approved' ):
                                                $previous_ca_status[] = $no_show_ca_record[0]->status;
                                            endif;
                                        endif;

                                    endif;

                                endforeach;

                            endif;



                            // check no show status
                            if( $previous_ca_status[0] === 'no-show-s' && $previous_ca_status[1] === 'no-show-s'  ):
                                // disable no show s option
                                $no_show_s_disabled = 'disabled';
                                $extended_no_show_s_disabled = '';
                            elseif ( $previous_ca_status[0] === 'extended-no-show-s' && $previous_ca_status[1] === 'no-show-s' ):
                                $no_show_s_disabled = 'disabled';
                                $extended_no_show_s_disabled = '';
                            elseif ( $previous_ca_status[0] === 'no-show-s' && $previous_ca_status[1] === 'extended-no-show-s' ):
                                $no_show_s_disabled = 'disabled';
                                $extended_no_show_s_disabled = '';
                            elseif ( $previous_ca_status[0] === 'extended-no-show-s' && $previous_ca_status[1] === 'extended-no-show-s' ):
                                $no_show_s_disabled = 'disabled';
                                $extended_no_show_s_disabled = 'disabled';
                            else:
                                $no_show_s_disabled = '';
                                $extended_no_show_s_disabled = 'disabled';
                            endif;


                            // get bb group from custom fields
                            $stored_bb_group_custom_field = json_decode($customer_appt_record[0]->custom_fields);
                            $stored_late_mins = '';
                            $stored_actual_min = '';
                            $private_notes = '';
                            //get bookly custom fields data
                            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
                            foreach ( $stored_bb_group_custom_field as $field_data ):
                                $custom_field_id = (int) $field_data->id;
                                if( $custom_field_id === $bb_custom_field_id ):
                                    $stored_bb_group_id = (int) $field_data->value;
                                endif;

                                if( $field_data->id === 95778 ): // late mins value
                                    $stored_late_mins = $field_data->value;
                                endif;

                                if( $field_data->id === 2583 ): // actual mins value
                                    $stored_actual_min = $field_data->value;
                                endif;

                                if( $field_data->id === 24491 ): // private notes value
                                    $private_notes = $field_data->value;
                                endif;

                            endforeach;
                        else:
                            //echo "<div class='alert'> Error: no bookly customer event found for appointment# $appointmnet_id. </div>";
                        endif;

                        $stored_parent_makeup_balance = (int) get_field( 'mslm_makeup_balance', 'user_' . $parent_id );

                        // search makeup log for edits for this group with user id, if found lock every thing
                        $makeup_table = $wpdb->prefix . 'muslimeto_makeup_log';
                        $makeup_results = $wpdb->get_results(
                            "SELECT * FROM $makeup_table WHERE ca_id = {$ca_id}"
                        );
                        $wpdb->flush();

                        $teacher_can_edit = 'true';
                        if( !empty($makeup_results) ):
                            $edited = 'edited';
                            $makeup_log_id = $makeup_results[0]->id;
                            $makeup_log_trans_amount = (int) $makeup_results[0]->trans_amount;

                            // check update lock status for makeup log table
                            $stored_created_at = $makeup_results[0]->created_at;
                            $stored_updated_at = $makeup_results[0]->updated_at;

                            if( empty($stored_updated_at) && !empty($stored_created_at) ):
                                // check if ( current time - created_at ) >= 24 hrs
                                $stored_makeup_month = (int) date('m', strtotime($stored_created_at));
                                $time_diff = time() - strtotime($stored_created_at);

                            elseif( !empty($stored_updated_at) && !empty($stored_created_at) ):
                                // check if ( current time - updated_at ) >= 24 hrs
                                $stored_makeup_month = (int) date('m', strtotime($stored_created_at));
                                $time_diff = time() - strtotime($stored_created_at);
                            else:
                                $time_diff = 0;
                            endif;


                            if( $time_diff <= 86400 && ( $stored_makeup_month === $current_month )): // 86400 equals to 24 hrs in secs
                                // teacher can edit
                                //echo 'less than 24 hours <br>';
                                $teacher_can_edit = 'true';
                                $lock_edit_class = '';
                            else:
                                // edit is locked forever
                                $teacher_can_edit = 'false';
                                $lock_edit_class = 'locked';
                            endif;



                            $parent_makeup_balance = (int) $parent_makeup_balance - (int) $makeup_log_trans_amount;
                            $new_parent_makeup_balance = (int) $parent_makeup_balance + (int) $makeup_log_trans_amount ;

                        else:
                            $edited = '';
                            $makeup_log_id = '';
                            $time_diff = 0;
                            $new_parent_makeup_balance = 0;
                            $parent_makeup_balance = $parent_makeup_balance;
                        endif;

                        // if session status = cancelled => edit is locked
                        if( $customer_appt_record[0]->status == 'cancelled'  ):
                            $lock_status = 'locked_due_to_cancelled';
                        else:
                            $lock_status = '';
                        endif;

                        $bb_group_type = getBBgroupType($stored_bb_group_id);

                        if( $bb_group_type === 'one-on-one' || $bb_group_type === 'one-to-one' ):

                            ?>


                            <tr class="<?php echo $edited . ' ' . $lock_edit_class . ' ' . $hide_record . ' ' . $user_can_edit . ' ' . $lock_status; ?>">
                                <td style="cursor: pointer">
                                    <span data-toggle="tooltip" title="<?php echo $service_name; ?>"> <?php echo $stored_bb_group_id; ?>  </span>
                                </td>
                                <td> <?php echo $customer_full_name; ?> </td>
                                <td>
                                    <?php echo  $converted_start_time . '-' . $converted_end_time; ?>
                                    <br>
                                    <?php  echo $current_day_short . ' ' . $converted_start_date;?>

                                </td>
                                <td class="attendance-tr-edit">
                                    <?php //echo 'time diff: ' . $time_diff . ' sec <br> teacher can edit: '. $teacher_can_edit .' - makeup id: '. $makeup_log_id .'<br>'; ?>

                                    <p> <?php
                                        if( $attendance_status !== 'approved' ):
                                            $attendance_status_with_spaces = str_replace('-', ' ', $attendance_status);
                                            $pieces = explode(' ', $attendance_status_with_spaces);
                                            $last_word = strtoupper( array_pop($pieces) );
                                            echo ucwords( $attendance_status_with_spaces );
                                        endif;
                                        ?> </p>
                                    <?php if(!empty($stored_actual_min)): ?>
                                        <p> Actual Mins. <?php echo $stored_actual_min; ?> </p>
                                    <?php endif; ?>

                                    <?php if(!empty($stored_late_mins)): ?>
                                        <p> Late Mins. <?php echo $stored_late_mins; ?> </p>
                                    <?php endif; ?>

                                    <select class="attendance-status">
                                        <option selected disabled> - select status - </option>
                                        <option value="attended" style="background: #6ed373; color: #488d4b;" <?php echo $attendance_status === 'attended' ? 'selected' : ''; ?> > Attended </option>
                                        <option value="attended-sl" style="background: #6ed373; color: #488d4b;" <?php echo $attendance_status === 'attended-sl' ? 'selected' : ''; ?> > Attended (Student Late) </option>
                                        <option value="attended-tl" style="background: #6ed373; color: #488d4b;" <?php echo $attendance_status === 'attended-tl' ? 'selected' : ''; ?> > Attended (Teacher Late)</option>
                                        <option value="no-show-s" <?php echo $no_show_s_disabled; ?> style="background: #f39191; color: #bd6565;" <?php echo $attendance_status === 'no-show-s' ? 'selected' : ''; ?> > No Show Student </option>
                                        <option value="extended-no-show-s" <?php echo $extended_no_show_s_disabled; ?> style="background: #ffff7c; color: #c5c516;" <?php echo $attendance_status === 'extended-no-show-s' ? 'selected' : ''; ?> > Extended No Show Student </option>
                                        <option value="cancelled" style="background: #ffff7c; color: #c5c516;" <?php echo $attendance_status === 'cancelled' ? 'selected' : ''; ?> > Excused Student </option>
                                        <option value="vacation-s" style="background: #ffff7c; color: #c5c516;" <?php echo $attendance_status === 'vacation-s' ? 'selected' : ''; ?> > Vacation Student </option>
                                        <option value="no-show-t" style="color: #599cc5; background: #c3fdff;" <?php echo $attendance_status === 'no-show-t' ? 'selected' : ''; ?> > No Show Teacher </option>
                                        <option value="excused-t" style="background: #d1be93; color: #897a5b;" <?php echo $attendance_status === 'excused-t' ? 'selected' : ''; ?> > Excused Teacher </option>
                                        <option value="vacation-t" style="background: #d1be93; color: #897a5b;" <?php echo $attendance_status === 'vacation-t' ? 'selected' : ''; ?> > Vacation Teacher </option>
                                        <option value="holiday" style="background: #dfbeff; color: #9570b9;" <?php echo $attendance_status === 'holiday' ? 'selected' : ''; ?> > Holiday </option>
                                    </select>

                                    <label for="actual-mins" class="actual-mins-div">
                                        <span>Actual Mins.</span>
                                        <input class="disabled actual-mins" type="number" min="0" value="<?php echo $actual_min; ?>">
                                        <input type="hidden" class="actual-mins-class" value="<?php echo $actual_min; ?>" >
                                    </label>

                                    <label for="late-mins" class="late-mins-div">
                                        <span>Late Mins. </span>&nbsp;&nbsp;
                                        <input type="number" min="0" class="late-mins" value="0">
                                    </label>

                                </td>
                                <td class="notes">

                        <textarea class="progress-notes" name="" id="" cols="30" rows="2" placeholder="Describe yourself here..." disabled required><?php
                            if(!empty($progress_notes)):
                                echo $progress_notes;
                            endif;
                            ?> </textarea>
                                    <textarea class="private-notes" name="" id="" cols="30" rows="2" disabled><?php
                                        if(!empty($private_notes)):
                                            echo $private_notes;
                                        endif;
                                        ?></textarea>

                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo $stored_bb_group_id; ?>" class="bb-group-id" >
                                    <input type="hidden" value="<?php echo $edited; ?>" class="edited" >
                                    <input type="hidden" value="<?php echo $new_parent_makeup_balance; ?>" class="parent-makeup-balance" >
                                    <input type="hidden" value="<?php echo $parent_makeup_balance; ?>" class="actual-parent-makeup-balance" >
                                    <input type="hidden" value="<?php echo $stored_parent_makeup_balance; ?>" class="meta-parent-makeup-balance" >
                                    <input type="hidden" value="<?php echo $parent_id; ?>" class="parent-id" >
                                    <input type="hidden" value="<?php echo $appointmnet_id; ?>" class="appointmnet-id" >
                                    <input type="hidden" value="<?php echo $customer_id; ?>" class="customer-id" >
                                    <input type="hidden" value="<?php echo $ca_id; ?>" class="ca-id" >
                                    <input type="hidden" value="<?php echo $makeup_log_id; ?>" class="makeup_log_id" >
                                    <input type="hidden" value="<?php echo $teacher_can_edit; ?>" class="teacher_can_edit" >


                                    <div class="edit-actions">
                                        <a class="edit-attendance button"> <i class="far fa-edit"></i> </a>
                                        <button type="submit" class="save-attendance btn button hidden"> <i class="far fa-save"></i> </button>
                                        <a class="cancel-attendance btn button hidden"> <i class="fas fa-ban"></i> </a>
                                    </div>

                                </td>
                            </tr>



                        <?php
                        endif;
                    endforeach; ?>
                    </tbody>
                </table>

            </form>

            <div class="modal micromodal-slide" id="result-modal" aria-hidden="true">
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
                            <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window">Close</button>
                        </footer>
                    </div>
                </div>
            </div>

            </div>
        <?php
        else:
            echo '<p class="empty-attendance"> No classes found on this day. </p>';
        endif;
    else:
        echo $access_alert;
        return ;
    endif;

    echo '</div>';


}
add_shortcode('attendance_capture_table', 'attendance_capture_table_callback');


// shortcode for editing program
function edit_single_program_callback()
{

    require_once plugin_dir_path( __FILE__ ) . 'class-getTimeZones.php';

    global $wpdb;

    // extract stored info for this program
    $stored_bb_group = !empty($_GET['bb_group_id']) ? $_GET['bb_group_id'] : '';

    if( empty($stored_bb_group) ):
        echo '<div class="alert"> No BB group set </div>';
        return;
    endif;

    // get single form entry id for this bb group id
    $sp_entry_id = getBBgroupGFentryID($stored_bb_group);
    if( empty($sp_entry_id) ):
        echo '<div class="alert"> No Single Form Entry ID set for this BB group </div>';
        return;
    endif;

    // get timezone stored in GF form
    $gf_timezone = getSPentryTimezone($sp_entry_id);
    if( empty($gf_timezone) ):
        echo '<div class="alert"> No Timezone GF entry found for this BB group </div>';
        return;
    endif;

    // get schedule from GF form
    $gf_schedules = getScheduleEntryID($sp_entry_id);
    if( empty($gf_schedules) ):
        echo '<div class="alert"> No GF schedules entries found for this BB group </div>';
        return;
    else:
        foreach ( $gf_schedules as $schedule_key=>$single_schedule ):
            // get start time
            $stored_start_time = getStartTime($single_schedule);
            // convert stored time from server to user timezone
            //$stored_start_time_converted = date('H:i', strtotime( convertToUserTimeZone($stored_start_time, $gf_timezone) ) );
            $stored_start_time_converted = date('H:i', strtotime( $stored_start_time ) );


            $start_hour = explode(':', $stored_start_time_converted)[0];
            $start_mins = explode(':', $stored_start_time_converted)[1];

            $hours_options[$schedule_key] = '';
            for($i = 1; $i <= 24; $i++):
                $time_value = sprintf("%02d", $i);

                if($i === 24){
                    $time_value = 0;
                }

                if( $time_value === $start_hour ):
                    $selected_hour = 'selected';
                else:
                    $selected_hour = '';
                endif;

                $hours_options[$schedule_key] .= '<option value="'. $time_value .'" '. $selected_hour .'>'. date("h A", strtotime("$i:00")).'</option>';
            endfor;


            $minutes_options[$schedule_key] = '';
            for ( $i=0; $i<4; $i++ ):
                $mins_vale = ( $i*15 );
                if( (int) $start_mins === $mins_vale ):
                    $selected_mins = 'selected';
                else:
                    $selected_mins = '';
                endif;

                $minutes_options[$schedule_key] .= '
                    <option value="'. $mins_vale .'" '. $selected_mins .' > '. $mins_vale .' </option>
                ';
            endfor;

        endforeach;
    endif;




    // get gf learners entry
    $gf_learners_id = getLearnersEntryID($sp_entry_id);
    if( empty($gf_learners_id) ):
        echo '<div class="alert"> No GF learners entry found for this BB group </div>';
        return;
    else:
        $learners_list = getLearnersWPuserIds($gf_learners_id);
        $learners_ids_string = substr( $learners_list, 1, -1 );
        $learners_ids_array = explode(',', $learners_ids_string);
        // genreate options for learners select
        $learners_options = '';
        $old_learners = '';
        foreach ( $learners_ids_array as $learner_wp_user_id ):
            $wp_user_id = (int) substr( $learner_wp_user_id, 1, -1 );;
            $user_object = get_user_by('id', $wp_user_id);
            $first_name = get_user_meta($wp_user_id, 'first_name');
            $last_name = get_user_meta($wp_user_id, 'last_name');
            $full_name = getCustomerFullName($wp_user_id);
            $email = $user_object->user_email;
            $learners_options .= '<option value="'. $wp_user_id .'" selected> '. $full_name . $email  .' </option>';
            $old_learners .= "<li> $full_name $email </li>";
        endforeach;
    endif;

    // get group type
    $bb_group_type = getBBgroupType($stored_bb_group);



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


    // get bookly service id from SP entry id
    $stored_service_id = getBooklyServiceId($sp_entry_id);
    if( empty($stored_service_id) ):
        echo '<div class="alert"> No GF bookly service id found for this BB group </div>';
        return;
    endif;

    // get services in this category as options
    $table_name = $wpdb->prefix . 'bookly_services';
    $services = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE id = {$stored_service_id}"
    );
    $wpdb->flush();

    $services_options = '';
    foreach ( $services as $service ):
        $services_options .= '<option value="'. $service->id .'" selected> ' . $service->title .' </option>';
    endforeach;



    // get bookly category id from SP entry id
    $stored_category_id = getBooklyCategoryId($sp_entry_id);
    if( empty($stored_category_id) ):
        echo '<div class="alert"> No GF bookly category id found for this BB group </div>';
        return;
    endif;

    // get categories
    $table_name = $wpdb->prefix . 'bookly_categories';
    $categories = $wpdb->get_results(
        "SELECT * FROM $table_name"
    );
    $wpdb->flush();

    $categories_options = '';
    foreach ( $categories as $category ):
        if( (int) $stored_category_id === (int) $category->id ):
            $selected_category = 'selected';
        else:
            $selected_category = '';
        endif;
        $categories_options .= '<option value="'. $category->id .'" '. $selected_category .'> ' . $category->name .' </option>';
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

        if( $key === $gf_timezone ):
            $selected_timezone = 'selected';
        else:
            $selected_timezone = '';
        endif;

        $timeZoneOffset = ( $timezones->getTimeZoneOffset($key) == 0 ) ? $timezones->getTimeZoneOffset($key) : sprintf("%+d",$timezones->getTimeZoneOffset($key));
        $timezones_options .= '<option value="'. $key .'" '. $selected_timezone .' > '. $value. ' UTC/GMT / ' . $timeZoneOffset .' </option>';
    endforeach;





    // get bb group select options
    $bb_groups_options = '';
    $bb_groups = groups_get_groups( array(
        'per_page' => 9999
    ) );
    foreach( $bb_groups['groups'] as $group ):
        $group_title = $group->name;
        $group_id = $group->id;
        $group_slug = $group->slug;


        if( (int) $stored_bb_group === (int) $group_id ):
            $selected_bb_group = 'selected';
        else:
            $selected_bb_group = '';
        endif;


        $bb_groups_options .= '<option value="'. $group_id .'" '. $selected_bb_group .' > '. $group_id . ' - ' . $group_title . $group_slug .' </option>';
    endforeach;


    // get program status from SP entry id
    $stored_program_status = getSPentryStatus($sp_entry_id);
    if( empty($stored_program_status) ):
        echo '<div class="alert"> No GF program status found for this BB group </div>';
        return;
    endif;


    // get gf stored teacher id
    $stored_teacher_id = getSPentryStaffId($sp_entry_id);
    if( empty($stored_teacher_id) ):
        echo '<div class="alert"> No GF teacher id found for this BB group </div>';
        return;
    else:
        global $wpdb;
        $staff_select_options = '';
        $staff_id = (int) $stored_teacher_id;
        // query in bookly_staff table
        $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
        $staff_table_results = $wpdb->get_results(
            "SELECT * FROM $bookly_staff_table WHERE id = {$staff_id}"
        );
        $wpdb->flush();

        foreach ( $staff_table_results as $staff_table_result ):
            $staff_wp_user_id = (int) $staff_table_result-> wp_user_id;
            $staff_full_name = $staff_table_result->full_name;
            $staff_wp_user_obj = get_user_by( 'id', $staff_wp_user_id );
            $staff_select_options .= '<option value="'. $staff_id .'"> ' . $staff_full_name . ' - ' . $staff_wp_user_obj->data->user_email .' </option>';
        endforeach;

    endif;

    // get $zoom_meeting_id for group
    $zoom_meeting_id = getZoomMeetingID($stored_bb_group);

    ?>

    <style>
        .edit_programm_page .fix_schedule {
            background: #d2f3e4;
            display: inline-flex;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            margin-left: -11px;
            right: -15px;
            border: 3px solid #fff;
            font-size: 1.4rem !important;
            position: absolute;
            padding: 0.2rem;
            top: 50%;
            cursor: pointer;
            transform: translateY(-50%);
        }
    </style>

    <div class="edit_programm_page">

        <h3 class="add-new-title"> <?php echo getBBgroupName($stored_bb_group); ?> </h3>

        <form id="single_program_booking_form" class="load_gif edit-mode" action="" method="post">

            <input type="hidden" value="<?php echo $stored_teacher_id; ?>" class="old_teacher_id">

            <div class="switch-field text-center edit-options">
                <input type="radio" id="edit-schedule" name="edit-options" value="edit" checked/>
                <label for="edit-schedule">Edit Schedule(s)</label>

                <?php if( in_array($bb_group_type, ['mvs', 'family-group', 'open-group']) ): ?>
                    <input type="radio" id="edit-learners" name="edit-options" value="edit-learners" />
                    <label for="edit-learners">Edit Learner(s)</label>
                <?php endif; ?>

                <input type="radio" id="transfer-program" name="edit-options" value="transfer" />
                <label for="transfer-program">Transfer Program</label>

                <input type="radio" id="cancel-program" name="edit-options" value="cancel" class=""/>
                <label for="cancel-program" class="">Cancel Program</label>
            </div>


            <?php wp_nonce_field( 'submit_single_program_booking_form', 'nonce_submit_single_program_booking_form' ); ?>

            <input id="stored_bb_group_id" value="<?php echo $stored_bb_group; ?>" type="hidden">
            <div class="container">
                <div class="new_bookly_form form-row align-items-center row">
                    <div class="col-md-12">
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-6 hidden">
                                <div class="switch-row">
                                    <label> 1 on 1 </label>
                                    <label class="switch">
                                        <?php if( $bb_group_type === 'one-to-one' ): ?>
                                            <input type="checkbox" value="one-to-one" class="program-type" checked disabled>
                                        <?php else: ?>
                                            <input type="checkbox" value="group" class="program-type" disabled>
                                        <?php endif; ?>
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
                                        <?php if( $stored_program_status === 'new' ): ?>
                                            <input type="checkbox" value="new" class="program-status" checked disabled>
                                        <?php else: ?>
                                            <input type="checkbox" value="transferred" class="program-status" disabled>
                                        <?php endif; ?>
                                        <div>
                                            <span></span>
                                        </div>
                                    </label>
                                    <label> Transferred </label>

                                </div>
                            </div>
                        </div>
                    </div>


                    <?php
                    if( $bb_group_type !== 'one-to-one' ):
                        if( $bb_group_type === 'family-group' ):
                            $family_checked = 'checked';
                        else:
                            $family_checked = '';
                        endif;

                        if( $bb_group_type === 'open' ):
                            $open_checked = 'checked';
                        else:
                            $open_checked = '';
                        endif;

                        if( $bb_group_type === 'mvs' ):
                            $mvs_checked = 'checked';
                        else:
                            $mvs_checked = '';
                        endif;
                        ?>



                        <div class="col-md-12 group-select-section">
                            <div class="bb-group-select col-md-6" style="display: block">
                                <div class="group_select">
                                    <label for="family-group"><input type="radio" id="family-group" name="group_type_select" class="group_type_select" value="family-group" <?php echo $family_checked; ?> disabled>Family</label>
                                    <label for="open"> <input type="radio" id="open" name="group_type_select" class="group_type_select" value="open-group" <?php echo $open_checked; ?> disabled> Open</label>
                                    <label for="mvs"><input type="radio" id="mvs" name="group_type_select" class="group_type_select" value="mvs" <?php echo $mvs_checked; ?> disabled> MVS</label>
                                </div>

                                <label for="existing"> <input type="checkbox" id="existing" value="existing" checked disabled> Link Existing Group </label>
                                <select class="custom-select mr-sm-2 select2" id="bb_group_id" name="bb_group_id" disabled>
                                    <?php echo $bb_groups_options; ?>
                                </select>
                            </div>
                            <div class="group_zoom_meeting_id col-md-6">
                                <label for="zoom_meeting_id"> zoom meeting id: </label>
                                <input type="text" id="zoom_meeting_id" value="<?= $zoom_meeting_id ?>" disabled>
                            </div>
                        </div>
                    <?php endif; ?>


                    <div class="col-md-12 d-flex learners-div">
                        <div class="col-md-10 d-flex gap-one-rem">
                            <label class="mr-sm-2" for="bookly_students">Learner(s):</label>
                            <div style="width: 100%">
                                <select class="custom-select mr-sm-2 select2" id="bookly_students_disabled" name="bookly_students" multiple required disabled>
                                    <?php if( empty($learners_options) ): ?>
                                        <option selected disabled > No Learner(s) found </option selected disabled>
                                    <?php else: ?>
                                        <?php echo $learners_options; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-3 d-flex justify-content-start gap-one-rem">
                        <label class="mr-sm-2" for="bookly_categories">Category:</label>
                        <div class="bookly_categories">
                            <select class="custom-select mr-sm-2 select2" name="bookly_categories" required disabled>
                                <?php echo $categories_options?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex justify-content-end gap-one-rem bookly_program">
                        <label class="mr-sm-2" for="bookly_services">Program:</label>
                        <div class="bookly_services_cloned">
                            <select class="custom-select mr-sm-2 select2" name="bookly_services[]" required disabled>
                                <?php echo $services_options; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex justify-content-start gap-one-rem">
                        <label class="mr-sm-2" for="bookly_timezones">Timezone:</label>
                        <div>
                            <select class="custom-select mr-sm-2 select2" id="bookly_timezones" name="bookly_timezones" disabled required>
                                <option selected disabled>Choose Timezone...</option>
                                <?php echo $timezones_options?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex justify-content-end gap-one-rem bookly_effective_date_clone">
                        <label class="mr-sm-2" for="bookly_effective_date">Effective From:</label>
                        <div class="effective_date_section">
                            <input data-toggle="datepicker" class="bookly_effective_date effective_date" name="bookly_effective_date[]" value="" type="text" placeholder="enter start date" required disabled>
                            <i class="far fa-calendar-alt datepicker_trigger"></i>
                        </div>
                    </div>



                    <div class="booking_rows">
                        <?php if( !empty($gf_schedules) ):


                            foreach ( $gf_schedules as $schedule_key=>$single_schedule ):
                                $mon_checked = '';
                                $tue_checked = '';
                                $wed_checked = '';
                                $thu_checked = '';
                                $fri_checked = '';
                                $sat_checked = '';
                                $sun_checked = '';
                                // get class days from schedule entry
                                $gf_class_days[$schedule_key] = getClassDays($single_schedule);
                                foreach ( $gf_class_days[$schedule_key] as $single_class_day ):
                                    if( $single_class_day === 'mon' ):
                                        $mon_checked = 'checked';
                                    elseif ( $single_class_day === 'tue' ):
                                        $tue_checked = 'checked';
                                    elseif ( $single_class_day === 'wed' ):
                                        $wed_checked = 'checked';
                                    elseif ( $single_class_day === 'thu' ):
                                        $thu_checked = 'checked';
                                    elseif ( $single_class_day === 'fri' ):
                                        $fri_checked = 'checked';
                                    elseif ( $single_class_day === 'sat' ):
                                        $sat_checked = 'checked';
                                    elseif ( $single_class_day === 'sun' ):
                                        $sun_checked = 'checked';
                                    endif;
                                endforeach;

                                // get class duration
                                $stored_class_duration = getClassDuration($single_schedule);

                                // get bookly series id from schedule_entry_id
                                $stored_series_id = getBooklySeriesId($single_schedule);
                                if( empty($stored_series_id) ):
                                    echo '<div class="alert"> No GF series id found for this BB group schedule </div>';
                                    $disabled_row = '';
                                    return;
                                else:
                                    $disabled_row = 'disabled';
                                endif;

                                $schedule_start_date = getBooklyStartDate($single_schedule);
                                if( !empty($schedule_start_date) ):
                                    $schedule_start_date = date('Y-m-d', strtotime($schedule_start_date) );
                                endif;


                                $appointments_to_delete = null;
                                $schedule_end_date = getBooklyEndDate($single_schedule);

                                if( !empty($schedule_end_date) ):
                                    $schedule_end_date = date('Y-m-d', strtotime($schedule_end_date) );
                                    // check if there are appointments for this bb_group_id and series_id after this end_date, if found show fix button
                                    // that delete all these appointments in this series after that date

                                    $appointments_to_delete = getBooklyEventsAfter( $schedule_end_date, $stored_bb_group, $stored_series_id );

                                endif;

                                $gf_entry_created_by = getGFentryCreatedBy($single_schedule);
                                if( !empty($gf_entry_created_by) ):
                                    $full_name = get_user_meta($gf_entry_created_by, 'first_name', true) . ' ' . get_user_meta($gf_entry_created_by, 'last_name', true);
                                endif;


                                // get start time
                                $stored_start_time = getStartTime($single_schedule);
                                // convert stored time from server to user timezone
                                $stored_start_time_converted = date('H:i', strtotime( convertToUserTimeZone($stored_start_time, $gf_timezone) ) );


                                // set new start effective date and end date
                                if( $schedule_start_date ):
                                    $new_effective_start_date = $schedule_start_date . ' ' . $stored_start_time_converted;
                                else:
                                    $new_effective_start_date = '';
                                endif;

                                if( $schedule_end_date ):
                                    $new_effective_end_date = $schedule_end_date . ' ' . $stored_start_time_converted;
                                else:
                                    $new_effective_end_date = '';
                                endif;

                                // get assigned teacher
                                $gf_assigned_teacher_id = getScheduleEntryStaffId($single_schedule);

                                // get date created for entry
                                $date_created = getGFentryCreatedOn($single_schedule);

                                // check if schedule has end_date in past add class inactive
                                $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
                                $nowUTC = $current_date_object->format('Y-m-d');
                                if( !empty($schedule_end_date) && strtotime($schedule_end_date) < strtotime($nowUTC) ):
                                    $schedule_status_class = 'inactive';
                                else:
                                    $schedule_status_class = 'active';
                                endif;


                                ?>


                                <div class="schedule_booking_section stored_schedule stored_row_<?php echo $schedule_key ;?> col-md-12 <?= $schedule_status_class ?>">

                                    <div class="delete-confirm">
                                        <h3> Are you sure ? </h3>

                                        <div class="delete-action-btns">
                                            <a href="#" class="confirm-delete-action"> <i class="fas fa-check"></i> Yes, delete </a>
                                            <a href="#" class="cancel-delete-action"> <i class="fas fa-ban"></i> No, go back </a>
                                        </div>

                                    </div>

                                    <div class="col-md-12">

                                        <table class="GeneratedTable">
                                            <thead>
                                            <tr>
                                                <th>SID</th>
                                                <th>Assigned Teacher</th>
                                                <th>Created On</th>
                                                <th>By</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td> <?php echo $stored_series_id; ?> </td>
                                                <td> <?php echo getStaffFullName($gf_assigned_teacher_id); ?> </td>
                                                <td> <?php echo date('d-m-Y', strtotime($date_created)); ?> </td>
                                                <td> <?php echo $full_name; ?> </td>
                                                <td> <?php echo date('d-m-Y', strtotime( $schedule_start_date ) ); ?> </td>
                                                <td class="new_end_date"> <?php echo !empty($schedule_end_date) ? date('d-m-Y', strtotime( $schedule_end_date ) ) : ''; ?> </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-12 d-flex align-items-end days_and_duration">

                                        <div class="col-md-12 d-flex">
                                            <div class="col-md-5 d-flex">
                                                <div class="col-md-12 class_days">
                                                    <div class="class_day">
                                                        <lebel> Mon </lebel>
                                                        <input type="checkbox" id="Mon" name="class_days[]" value="mon" <?php echo $mon_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Tue </lebel>
                                                        <input type="checkbox" id="Tue" name="class_days[]" value="tue" <?php echo $tue_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Wed </lebel>
                                                        <input type="checkbox" id="Wed" name="class_days[]" value="wed" <?php echo $wed_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Thu </lebel>
                                                        <input type="checkbox" id="Thu" name="class_days[]" value="thu" <?php echo $thu_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Fri </lebel>
                                                        <input type="checkbox" id="Fri" name="class_days[]" value="fri" <?php echo $fri_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Sat </lebel>
                                                        <input type="checkbox" id="Sat" name="class_days[]" value="sat" <?php echo $sat_checked; ?> >
                                                    </div>

                                                    <div class="class_day">
                                                        <lebel> Sun </lebel>
                                                        <input type="checkbox" id="Sun" name="class_days[]" value="sun" <?php echo $sun_checked; ?> >
                                                    </div>


                                                </div>
                                            </div>
                                            <div class="col-md-7 d-flex time-duration">
                                                <div class="col-md-12 d-flex">
                                                    <div class="bookly_start_time d-flex align-items-center">
                                                        <div class="d-flex start-time">
                                                            <label class="mr-sm-2" for="bookly_start_time">Start Time: </label>
                                                            <select class="custom-select mr-sm-2 select2 hours_selector_options" name="bookly_start_time[]" style="width: 5rem" required <?php echo $disabled_row; ?> >
                                                                <?php echo $hours_options[$schedule_key]; ?>
                                                            </select>
                                                            <select class="custom-select mr-sm-2 select2 minutes_selector_options" name="bookly_start_time_minutes[]" style="width: 5rem" required <?php echo $disabled_row; ?> >
                                                                <?php echo $minutes_options[$schedule_key]; ?>
                                                            </select>
                                                        </div>

                                                    </div>
                                                    <div class="d-flex duration-div">
                                                        <label class="mr-sm-2" for="bookly_class_duration">Duration:</label>
                                                        <div class="bookly_class_duration">
                                                            <select class="custom-select mr-sm-2 select2" name="bookly_class_duration[]" required <?php echo $disabled_row; ?> >

                                                                <option value="15" <?php echo ( $stored_class_duration === '15' ) ? 'selected' : ''; ?> > 15 Minutes </option>
                                                                <option value="30" <?php echo ( $stored_class_duration === '30' ) ? 'selected' : ''; ?> > 30 Minutes </option>
                                                                <option value="45" <?php echo ( $stored_class_duration === '45' ) ? 'selected' : ''; ?> > 45 Minutes </option>
                                                                <option value="60" <?php echo ( $stored_class_duration === '60' ) ? 'selected' : ''; ?> > 1 Hour </option>
                                                                <option value="75" <?php echo ( $stored_class_duration === '75' ) ? 'selected' : ''; ?> > 1 Hour & 15 Minutes </option>
                                                                <option value="90" <?php echo ( $stored_class_duration === '90' ) ? 'selected' : ''; ?> > 1 Hour & 30 Minutes </option>
                                                                <option value="105" <?php echo ( $stored_class_duration === '105' ) ? 'selected' : ''; ?> > 1 Hour & 45 Minutes </option>
                                                                <option value="120" <?php echo ( $stored_class_duration === '120' ) ? 'selected' : ''; ?> > 2 Hours </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <input type="hidden" value="<?php echo $stored_series_id; ?>" class="stored_bookly_series_id">
                                    <input type="hidden" value="<?php echo $single_schedule; ?>" class="stored_schedule_entry_id">
                                    <input type="hidden" value="<?php echo $gf_timezone; ?>" class="stored_gf_timezone">
                                    <input type="hidden" value="<?php echo $stored_start_time_converted; ?>" class="stored_start_time_converted">
                                    <input type="hidden" value="<?php echo $new_effective_start_date; ?>" class="start_effective_date">
                                    <input type="hidden" value="<?php echo $new_effective_end_date; ?>" class="end_effective_date">
                                    <input type="hidden" value="<?php echo $sp_entry_id; ?>" class="gf_sp_entry_id">

                                    <input type="hidden" value="<?php echo $stored_series_id; ?>" class="bookly_series_id">
                                    <input type="hidden" value="<?php echo $schedule_end_date ? date('m/d/Y', strtotime($schedule_end_date)) : 'false'; ?>" class="schedule_end_date">
                                    <input type="hidden" value="<?php echo $schedule_start_date ? date('m/d/Y', strtotime($schedule_start_date)) : 'false'; ?>" class="schedule_start_date">
                                    <input type="hidden" class="clone_gf_schedule" value="">
                                    <input type="hidden" class="send_to_backend" value="">


                                    <?php if( empty($schedule_end_date) ): ?>
                                        <i class="far fa-trash-alt remove_row" data-toggle="tooltip" title="Update end date and delete future appointments"></i>
                                    <?php endif; ?>

                                    <?php if( !empty($appointments_to_delete) && count($appointments_to_delete) > 0 ): ?>
                                        <i class="bb-icon-cogs bb-icon-l fix_schedule" data-schedule-end-date="<?= $schedule_end_date ?>"
                                           data-bb-group-id="<?= $stored_bb_group ?>" data-series-id="<?= $stored_series_id ?>" data-toggle="tooltip" title="Fix schedule and delete future appointments"></i>
                                    <?php endif; ?>

                                    <input type="hidden" class="final_teacher_0 final_teachers_array">


                                </div>
                            <?php
                            endforeach; // end schedules loop
                        endif; ?>
                    </div>


                    <input type="hidden" value="" id="validate_submit_btn">
                    <a href="#" class="validate_submit_data"></a>




                    <div class="col-md-12 text-center add_new_schedule_section">
                        <button class="add_new_row"> <i class="far fa-calendar-plus"></i> </button>
                    </div>

                    <div class="bordered-section">
                        <div class="new_schedule_section_rows">

                        </div>

                        <div class="teacher_section teacher_select ">
                            <div class="col-md-12">
                                <div class="switch-row teachers-switch">
                                    <label> Check All Teachers </label>
                                    <label class="switch">
                                        <input type="checkbox" value="" class="teacher-check" >
                                        <div>
                                            <span></span>
                                        </div>
                                    </label>
                                    <label> Check Single Teacher </label>

                                </div>
                            </div>
                            <select class="custom-select mr-sm-2 select2" name="bookly_teacher[]" required>
                                <?php echo $staff_select_options; ?>
                            </select>
                        </div>

                        <div class="check_action_btns d-flex">
                            <a href="#" class="find_teacher all edit-mode-check hidden"> <i class="bb-icon-users bb-icon-l"></i> Find Available Teacher  </a>
                            <a href="#" class="find_teacher single edit-mode-check"> <i class="bb-icon-check bb-icon-l"></i> Confirm Availabilty  </a>
                            <button type="submit" class="submit_booking" name="submit_booking"> Save New Schedule(s) </button>
                        </div>
                    </div>


                </div>

                <?php if( in_array($bb_group_type, ['mvs', 'family-group', 'open-group']) ): ?>
                    <div class="edit-learners-section row">

                    <div class="col-md-6 d-flex justify-content-end gap-one-rem bookly_effective_date_clone">
                        <?php
                            $min_date = date("Y-m-d", strtotime('tomorrow'));
                            $max_date = date('Y-m-d', strtotime('+4 week', strtotime($min_date)));
                        ?>
                        <label class="mr-sm-2" for="bookly_effective_date">Effective From:</label>
                        <div class="effective_date_section">
                            <input type="date" class="new-effective-date" value="" min="<?= $min_date ?>" max="<?= $max_date ?>" placeholder="enter start date" required>
                        </div>
                    </div>


                    <div class="col-md-12 d-flex learners-div">
                        <div class="col-md-10 d-flex gap-one-rem">
                            <label class="mr-sm-2" for="bookly_students">Learner(s):</label>
                            <div style="width: 100%">

                                <select class="custom-select mr-sm-2 bookly_students_before_edit hidden" multiple disabled required>
                                    <?php if( empty($learners_options) ): ?>
                                        <option selected disabled > choose learner(s) </option>
                                    <?php else: ?>
                                        <?php echo $learners_options; ?>
                                    <?php endif; ?>
                                </select>

                                <select class="custom-select mr-sm-2 select2 bookly_students_after_edit" id="bookly_students" multiple required >
                                    <?php if( empty($learners_options) ): ?>
                                        <option selected disabled > choose learner(s) </option>
                                    <?php else: ?>
                                        <?php echo $learners_options; ?>
                                    <?php endif; ?>
                                </select>

                            </div>
                        </div>

                        <div class="col-md-2">
                            <a href="#" class="find-learners" > <i class="fas fa-search"></i> Find Learner(s)  </a>
                        </div>

                    </div>

                    <div class="col-md-12 d-flex  learners-list ">
                        <div class="col-md-6 old-learners">
                            <span> Current List of Learners: </span>
                            <ul>    <?= $old_learners; ?> </ul>
                        </div>

                        <div class="col-md-6 new-learners">
                            <span> Updated List of Learners: </span>
                            <ul> <?= $old_learners; ?> </ul>
                        </div>

                    </div>

                    <div class="check_action_btns d-flex">
                        <button type="submit" class="update-learners disabled" name="submit_booking" disabled> Confirm Updated List of Learners </button>
                    </div>

                </div>
                <?php endif; ?>

            </div>

            <input type="hidden" id="mode" value="edit">

        </form>

        <?php  get_template_part('template-parts/common/template-find-learners-modal');  ?>

        <!-- Modals start-->
<!--        <div class="modal micromodal-slide" id="find-learners-modal" aria-hidden="true">-->
<!--            <div class="modal__overlay" tabindex="-1" data-micromodal-close>-->
<!--                <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">-->
<!--                    <header class="modal__header">-->
<!--                        <h2 class="modal__title" id="modal-1-title">-->
<!--                            Add learner(s) to class-->
<!--                        </h2>-->
<!--                        <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>-->
<!--                    </header>-->
<!--                    <main class="modal__content" id="modal-1-content">-->
<!---->
<!--                        <div class="ajax_image_section find-users"> <div class="ajaxloader"></div> </div>-->
<!--                        <label for="parent_user_email">-->
<!--                            Find Users:-->
<!--                            <input type="text" value="" id="parent_user_email" placeholder="parent user email"> <i class="fas fa-search find-user"></i>-->
<!--                        </label>-->
<!---->
<!--                        <br><br>-->
<!--                        <span class="childs-result" style="display: block"></span>-->
<!---->
<!--                    </main>-->
<!--                    <footer class="modal__footer">-->
<!---->
<!--                    </footer>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

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
        <!-- Modals end-->


        <div class="ajax_result"></div>

    </div>

    <script >
        jQuery('#single_program_booking_form').append('<div class="ajax_image_section"> <div class="ajaxloader"></div> </div>');
        jQuery('.ajax_image_section').hide();

        // update new learners list in view
        jQuery('.bookly_students_after_edit').on('change', function(){
             jQuery('.new-learners ul').html('');
            jQuery(this).find(':selected').each(function(){
               jQuery('.new-learners ul').append('<li>' + jQuery(this).text() + '</li>' )
            });

        });

    </script>


    <?php



}
add_shortcode('edit_single_program', 'edit_single_program_callback');


// shortcoed to regenreate appointments based on GF entries
function fix_appointments_timezone_shortcode() {

    // get all teachers
    global $wpdb;
    $bookly_staff_table = $wpdb->prefix . 'bookly_staff';
    // get all teachers
    $staff_results = $wpdb->get_results(
        "SELECT * FROM $bookly_staff_table"
    );
    $wpdb->flush();

    $teachers_options = '';
    foreach ( $staff_results as $staff_result ):
        $teacher_id = $staff_result->wp_user_id;
        $staff_id = getStaffId($teacher_id);
        $teacher_name = $staff_result->full_name;
        $teacher_email = $staff_result->email;
        $teachers_options .= '<option value="'. $staff_id .'"> '.$teacher_id.' - '. $teacher_name .' - '. $teacher_email .'  </option>';
    endforeach;


    global $wpdb;
    $bb_groups_table = $wpdb->prefix . 'bp_groups';
    // get all teachers
    $bb_groups_results = $wpdb->get_results(
        "SELECT * FROM $bb_groups_table ORDER BY id DESC LIMIT 1"
    );
    $wpdb->flush();

    $last_group_id = $bb_groups_results[0]->id;

    ?>




    <hr>

    <form action="" id="fix_bb_groups_schedules_form">
        <div class="ajax_image_section"> <div class="ajaxloader"></div> </div>
        <select name="select_teacher_schedule" class="select2" id="select_teacher_schedule">
            <option selected disabled> -- choose teacher -- </option>
            <?php  echo $teachers_options; ?>
        </select>


        <button type="submit" class="fix_bb_groups_schedule"> Fix Schedule(s) </button>
    </form>

    <div class="fix-bbgroups-schedule-result"></div>



    <hr>

    <form action="" class="fix-missing-gf">
        <div class="ajax_image_section"> <div class="ajaxloader"></div> </div>
        <a href="#" class="fix-missing-learners-entries button"> fix mssing GF learners entries </a>
        <input type="text" id="last_bb_group_id" value="<?php echo $last_group_id; ?>">
        <br>
        <div class="fix-missing-learners-result"></div>
    </form>


    <!--    <a href="#" class="fix-start-time button"> Fix GF Start Time </a>-->
    <!--   <div class="fix-start-time-result"></div>-->





    <?php








    //pre_dump(regenerateSchedulefromGFentries(25));



}
add_shortcode('fix_appointments_timezone_shortcode', 'fix_appointments_timezone_shortcode');

// shortcode to get parents status ( dashboard )
function parent_status_table() {

  //  pre_dump($atts['target']);
    // get data from table
    global $wpdb;
    $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
    // get all teachers
    $parent_stats_results = $wpdb->get_results(
        "SELECT * FROM $parent_stats_table"
    );
    $wpdb->flush();


    if( empty($parent_stats_results) ) return false;


    ?>
    <input type="hidden" id="show-assigned-to" value="1">
    <table class="parent-status-table">
        <thead>
        <tr>
            <th>Account</th>
            <th> <p> Client </p> <p> Time Now </p> </th>
            <th> <p> Renews On </p> </th>
            <th> <p> Active </p> <p> Learners </p> </th>
            <th> <p> Assigned </p> <p> Hrs </p> </th>
            <th> <p> Future </p> <p> Hrs </p> </th>
            <th> <p> Paid </p> <p> Hrs </p> </th>
            <th> <p> Balance </p> <p> Due </p> </th>
            <th> <p> Winning </p> <p> Back </p> </th>
            <th> <p> Last </p> <p> Payment </p> </th>
            <th> <p> Stopping </p> <p> Soon </p> </th>
            <th> <p> Last </p> <p> Updated </p> </th>
            <th> <p> Assigned To </p> </th>
        </tr>
        </thead>
        <tbody>
        <?php if( !empty($parent_stats_results) ): ?>
            <?php

            $user_is_hos = user_has_role(get_current_user_id(), 'head_of_support');
            $support_users = get_users( array( 'role__in' => array( 'support' ) ) );

            foreach ( $parent_stats_results as &$paren_stats_result ):
                $contact_id = $paren_stats_result->keap_user_id;
                $wp_user_id = $paren_stats_result->parent_wp_user_id;
                if(isset($contact_id)):
                    $row_id = $paren_stats_result->id;
                    $user_obj = get_user_by('id', $wp_user_id);
                    $display_name = $user_obj->display_name;
                    $email = $user_obj->user_email;
                    $paid_amount = (float) $paren_stats_result->paid_amount;
                    $balance_due = (float) $paren_stats_result->due_balance;
                    if( empty($balance_due) ) $balance_due = 0.0;

                    if( $balance_due > 0.0 ):
                        $balance_due_html = "<strong class='danger'> {$balance_due} </strong>";
                    else:
                        $balance_due_html = "<strong> {$balance_due} </strong>";
                    endif;


                    $non_renewal_indicator = get_user_meta($wp_user_id, 'non_renewal_indicator', true);



                    $next_payment = $paren_stats_result->renew_on;
                    $last_payment = $paren_stats_result->last_payment;
                    $assigned_to = $paren_stats_result->assigned_to;
                    $assigned_to_user = get_user_by('id', $assigned_to);
                    $active_learners = !empty($paren_stats_result->active_childs) ? json_decode($paren_stats_result->active_childs) : '';
                    $parent_total_hours = !empty($paren_stats_result->total_hours) ? json_decode($paren_stats_result->total_hours) : '';

                    $assigned_hours = (float) $parent_total_hours->total_current_hrs;
                    $future_hours = (float) $parent_total_hours->total_current_hrs + (float) $parent_total_hours->total_starting_hrs - (float) $parent_total_hours->total_stopping_hrs;
                    $paid_hours = (float) $paren_stats_result->paid_hours;

                    if( $paid_hours === $future_hours && $paid_hours === $assigned_hours ):
                        $paid_status = 'success';
                    elseif( $paid_hours !== $assigned_hours && $paid_hours !== $future_hours ):
                        $paid_status = 'danger';
                    else:
                        $paid_status = 'warning';
                    endif;

                    $updated_at = $paren_stats_result->updated_at;
                    $created_at = $paren_stats_result->created_at;
                    if( empty($updated_at) ):
                        $updated_at = $created_at;
                    endif;

                    $updated_since = time_elapsed_string($updated_at);

                    $support_tickets = !empty($paren_stats_result->support_tickets) ? json_decode($paren_stats_result->support_tickets) : '';
                    if( !empty($support_tickets) ):
                        $open_tickets = $support_tickets->Open;
                        $closed_tickets = $support_tickets->Closed;
                    else:
                        $open_tickets = 0;
                        $closed_tickets = 0;
                    endif;
 

                    $happiness_rate = !empty($paren_stats_result->happiness_rate) ? json_decode($paren_stats_result->happiness_rate) : '';
                    $tags = wpf_get_tags( $wp_user_id );
                    $tag='';
                    if(in_array("456", $tags)){$tag.="RC";}
                    if(in_array("458", $tags)){$tag.="RC 30";}
                    if(in_array("460", $tags)){$tag.="RC 60";}
                    if(in_array("462", $tags)){$tag.="RC 90";}

                    $mslm_account_status = get_field( 'mslm_account_status', 'user_' . $wp_user_id );
                    if( !empty($mslm_account_status) && $mslm_account_status == 'on_vacation' ){
                        $vac=1;
                    }else{
                        $vac=0;
                    }



                          $miss = false;
                          $mslm_billing_indicator = get_field( 'mslm_billing_indicator', 'user_' . $wp_user_id );
                          if( !empty($mslm_billing_indicator) && $mslm_billing_indicator == 'mismatch' ):
                            $miss = 1;
                          else:
                            $miss = 0;
                          endif;



                            $keap_link = 'https://mep387.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=' . $contact_id;

                            $parent_timezone = get_user_meta($wp_user_id, 'time_zone', true);


                            if( empty($parent_timezone) ):
                                $converted_time_now = '';
                            else:
                                $parent_time_now = convertTimezone1ToTimezone2( date('h:i a',  time() ), 'UTC', $parent_timezone );
                                $converted_time_now = date('h:i a', strtotime($parent_time_now));
                            endif;


                            $due_ind = ( $balance_due > 0 && $paid_amount > 0) ? 1 : 0;
                        ?>
                        <tr data-due="<?php echo $due_ind?>" data-cancel="<?php echo $tag ?>" data-miss="<?php echo $miss; ?>" data-vacation="<?php echo $vac ?>">

                            <td>
                                <a href="#" class="parent-name">
                                    <strong class="panel-toggle"> <?php echo $display_name ; ?> </strong>
                                    <strong>&nbsp;
                                        <span class="sync_user_billing_stats" data-wp-user-id="<?php echo $wp_user_id; ?>"> <i class="bb-icon-sync bb-icon-l"></i> </span>
                                        &nbsp; <a href="<?php echo $keap_link; ?>" target="_blank"> <i class="bb-icon-external-link bb-icon-l"></i> </a>
                                    </strong>
                                    <p class="size-1"> <?php echo $email; ?>  </p>
                                </a>

                                <a href="#" class="size-1"> <?php echo $open_tickets . ' open - ' . $closed_tickets . ' closed';?> </a>
                            </td>
                            <td data-tooltip="<?php echo $parent_timezone; ?>"> <strong> <?php echo $converted_time_now; ?> </strong> </td>
                            <td> <strong> <?php echo $next_payment; ?> </strong> </td>
                            <td> <strong> <?php echo count($active_learners); ?> </strong> </td>
                            <td> <strong> <?php echo $assigned_hours; ?> </strong> </td>
                            <td> <strong> <?php echo $future_hours; ?> </strong> </td>
                            <td> <strong class="<?php echo $paid_status; ?>"> <?php echo $paid_hours; ?> </strong> </td>
                            <td> <?php echo $balance_due_html; ?> </td>
                            <td> <?php echo $tag;  ?> </td>
                            <td> <strong> <?php echo $last_payment; ?> </strong> </td>
                            <td> <strong> <?php echo !empty( $non_renewal_indicator && $non_renewal_indicator === '1' ) ? '<span class="non-renewal"> Yes </span>' : 'No' ; ?> </strong> </td>
                            <td> <strong> <?php echo $updated_since; ?> <strong> </td>
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
                        </tr>
                    <?php
                //     endif;
                  endif;
            endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <!-- DivTable.com -->
    <script>
        jQuery(document).ready(function(){
            // parent status table
              var ptable = jQuery('.parent-status-table').DataTable( {
                "paging": true,
                "searching": true,
                "pageLength": 50,
                "order": [[ 0, "desc" ]],
                });

              $.fn.dataTable.ext.search.push(
              function(settings, data, dataIndex) {
                return $(ptable.row(dataIndex).node()).attr('data-due') > 0;
              }
              );
              ptable.draw();


            $(document).on('click','#due-tab',function(e){
                e.preventDefault();
                $('.nav-tabs li a').removeClass('active');
                $(this).addClass('active');
                $.fn.dataTable.ext.search.pop();
                ptable.draw();
                $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    return $(ptable.row(dataIndex).node()).attr('data-due') > 0;
                  }
                  );
               ptable.draw();
                // ptable.order([[6, 'desc']]).draw();
            });

            $(document).on('click','#vacation-tab',function(e){
                e.preventDefault();
                $('.nav-tabs li a').removeClass('active');
                $(this).addClass('active');
                $.fn.dataTable.ext.search.pop();
                ptable.draw();
                $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    return $(ptable.row(dataIndex).node()).attr('data-vacation') == 1;
                  }
                  );
               ptable.draw();
            });

            $(document).on('click','#troubled-classes-tab',function(e){
                e.preventDefault();
                $('.nav-tabs li a').removeClass('active');
                $(this).addClass('active');
              //  ptable.order([[6, 'desc']]).draw();
              $.fn.dataTable.ext.search.pop();
              ptable.draw();
              $.fn.dataTable.ext.search.push(
              function(settings, data, dataIndex) {
                  return $(ptable.row(dataIndex).node()).attr('data-miss') == 1;
                }
                );
             ptable.draw();
            });
            $(document).on('click','#winning-back-tab',function(e){
                e.preventDefault();
                $('.nav-tabs li a').removeClass('active');
                $(this).addClass('active');
                //ptable.order([[8, 'desc']]).draw();
                $.fn.dataTable.ext.search.pop();
                ptable.draw();
                $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                  if($(ptable.row(dataIndex).node()).attr('data-cancel') && $(ptable.row(dataIndex).node()).attr('data-vacation') == 0){
                    return true;
                  }
                  }
                  );


               ptable.draw();
            });
        });
    </script>
    <?php

}
add_shortcode('parent_status_table', 'parent_status_table');


// shortcode to get all parents status ( accounts page )
function all_parent_status_table($atts) {

    // get data from table
    global $wpdb;
    $parent_stats_table = $wpdb->prefix . 'muslimeto_parent_stats';
    // get all teachers
    if($atts['target'] == "all"){
      $parent_stats_results = $wpdb->get_results(
          "SELECT * FROM $parent_stats_table"
      );
    }else{
      $users_with_tag = wpf_get_users_with_tag( $atts['target'] );
      $users_tags = implode(',' , $users_with_tag);
      $parent_stats_results = $wpdb->get_results(
          "SELECT * FROM $parent_stats_table where parent_wp_user_id in($users_tags)"
      );
    }

    $wpdb->flush();
  //  pre_dump($parent_stats_results);


    ?>
    <div class="all-accounts-stats">

        <div class="side-panel">

            <div class="hidden-panel">

                <div class="panel-header">
                    <span class="hidden-panel-close"> x </span>
                </div>

                <div class="hidden-panel-content panel-content">



                </div>
            </div>
        </div>

        <!-- <a href="#" class="sync-all button"> Sync All </a> -->

        <table class="parent-status-table all-accounts">
            <thead>
            <tr>
                <th>Account</th>
                <th> <p> Client </p> <p> Time Now </p> </th>
                <th> <p> Renews On </p> </th>
                <th> <p> Active </p> <p> Learners </p> </th>
                <th> <p> Assigned </p> <p> Hrs </p> </th>
                <th> <p> Future </p> <p> Hrs </p> </th>
                <th> <p> Paid </p> <p> Hrs </p> </th>
                <th> <p> Balance </p> <p> Due </p> </th>
                <th> <p> Winning </p> <p> Back </p> </th>
                <th> <p> Last </p> <p> Payment </p> </th>
                <th> <p> Stopping </p> <p> Soon </p> </th>
                <th> <p> Last </p> <p> Updated </p> </th>
            </tr>
            </thead>
            <tbody>
            <?php if( !empty($parent_stats_results) ): ?>
                <?php


                // if( isset($atts) && !empty($atts['target']) ):
                //     $target = $atts['target'];
                // else:
                //     $target = null;
                // endif;


                foreach ( $parent_stats_results as &$paren_stats_result ):
                    $contact_id = $paren_stats_result->keap_user_id;
                    $wp_user_id = $paren_stats_result->parent_wp_user_id;
                    if(isset($contact_id)):
                        $user_obj = get_user_by('id', $wp_user_id);
                        $display_name = $user_obj->display_name;
                        $email = $user_obj->user_email;
                        $paid_amount = (float) $paren_stats_result->paid_amount;
                        $balance_due = (float) $paren_stats_result->due_balance;
                        if( empty($balance_due) ) $balance_due = 0.0;

                        if( $balance_due > 0.0 ):
                            $balance_due_html = "<strong class='danger'> {$balance_due} </strong>";
                        else:
                            $balance_due_html = "<strong> {$balance_due} </strong>";
                        endif;


                        $non_renewal_indicator = get_user_meta($wp_user_id, 'non_renewal_indicator', true);


                        $next_payment = $paren_stats_result->renew_on;
                        $last_payment = $paren_stats_result->last_payment;
                        $active_learners = json_decode($paren_stats_result->active_childs);
                        $parent_total_hours = json_decode($paren_stats_result->total_hours);

                        $assigned_hours = (float) $parent_total_hours->total_current_hrs;
                        $future_hours = (float) $parent_total_hours->total_current_hrs + (float) $parent_total_hours->total_starting_hrs - (float) $parent_total_hours->total_stopping_hrs;
                        $paid_hours = (float) $paren_stats_result->paid_hours;

                        if( $paid_hours === $future_hours && $paid_hours === $assigned_hours ):
                            $paid_status = 'success';
                        elseif( $paid_hours !== $assigned_hours && $paid_hours !== $future_hours ):
                            $paid_status = 'danger';
                        else:
                            $paid_status = 'warning';
                        endif;

                        $updated_at = $paren_stats_result->updated_at;
                        $created_at = $paren_stats_result->created_at;
                        if( empty($updated_at) ):
                            $updated_at = $created_at;
                        endif;

                        $updated_since = time_elapsed_string($updated_at);

                        $support_tickets = json_decode($paren_stats_result->support_tickets);
                        if( !empty($support_tickets) ):
                            $open_tickets = $support_tickets->Open;
                            $closed_tickets = $support_tickets->Closed;
                        else:
                            $open_tickets = 0;
                            $closed_tickets = 0;
                        endif;

                        $happiness_rate = json_decode($paren_stats_result->happiness_rate);

                        $keap_link = 'https://mep387.infusionsoft.com/Contact/manageContact.jsp?view=edit&ID=' . $contact_id;

                        $parent_timezone = get_user_meta($wp_user_id, 'time_zone', true);


                        if( empty($parent_timezone) ):
                            $converted_time_now = '';
                        else:
                            $parent_time_now = convertTimezone1ToTimezone2( date('h:i a',  time() ), 'UTC', $parent_timezone );
                            $converted_time_now = date('h:i a', strtotime($parent_time_now));
                        endif;

                    // if( in_array($wp_user_id, $active_accounts) ):
                    //     $active = 'active';
                    // else:
                    //     $active = '';
                    // endif;
                    //
                    // if( in_array($wp_user_id, $trialing_accounts) ):
                    //     $trialing = 'trialing';
                    // else:
                    //     $trialing = '';
                    // endif;

                    $tags = wpf_get_tags( $wp_user_id );
                    $tag='';
                    if(in_array("456", $tags)){$tag.="RC";}
                    if(in_array("458", $tags)){$tag.="RC 30";}
                    if(in_array("460", $tags)){$tag.="RC 60";}
                    if(in_array("462", $tags)){$tag.="RC 90";}
                    if(in_array("394", $tags)){$vac=1;}else{$vac=0;}

                    ?>
                    <tr class="<?= "all" ?>" data-target="<?=$target ?>" data-tag="<?php echo $tag; ?>">
                        <td>
                            <a href="#" class="parent-name">
                                <strong class="panel-toggle"> <?php echo $display_name ; ?> </strong>
                                <strong>&nbsp;
                                    <span class="sync_user_billing_stats" data-wp-user-id="<?php echo $wp_user_id; ?>"> <i class="bb-icon-sync bb-icon-l"></i> </span>
                                    &nbsp; <a href="<?php echo $keap_link; ?>" target="_blank"> <i class="bb-icon-external-link bb-icon-l"></i> </a>
                                </strong>
                                <p class="size-1"> <?php echo $email; ?>  </p>
                            </a>

                            <a href="#" class="size-1"> <?php echo $open_tickets . ' open - ' . $closed_tickets . ' closed';?> </a>
                        </td>
                        <td data-tooltip="<?php echo $parent_timezone; ?>"> <strong> <?php echo $converted_time_now; ?> </strong> </td>
                        <td> <strong> <?php echo $next_payment; ?> </strong> </td>
                        <td> <strong> <?php echo count($active_learners); ?> </strong> </td>
                        <td> <strong> <?php echo $assigned_hours; ?> </strong> </td>
                        <td> <strong> <?php echo $future_hours; ?> </strong> </td>
                        <td> <strong class="<?php echo $paid_status; ?>"> <?php echo $paid_hours; ?> </strong> </td>
                        <td> <?php echo $balance_due_html; ?> </td>
                        <td> <?php echo $tag;  ?> </td>
                        <td> <strong> <?php echo $last_payment; ?> </strong> </td>
                        <td> <strong> <?php echo !empty( $non_renewal_indicator && $non_renewal_indicator === '1' ) ? '<span class="non-renewal"> Yes </span>' : 'No' ; ?> </strong> </td>
                        <td> <strong> <?php echo $updated_since; ?> <strong> </td>
                    </tr>
                <?php
              endif;
                endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <!-- DivTable.com -->
    </div>
    <script>
        jQuery(document).ready(function(){

            // parent status table
       jQuery('.parent-status-table').DataTable( {
                "pageLength": 50,
                "order": [[ 0, "desc" ]],
            });
        });
    </script>
    <?php

}
add_shortcode('all_parent_status_table', 'all_parent_status_table');


// shortcode for academic attendance table for parent/learner
function academic_attendance_table_callback($atts){
    global $wpdb;
    $bookly_appointments_table = $wpdb->prefix . 'bookly_appointments';
    $bookly_ca_appointments_table = $wpdb->prefix . 'bookly_customer_appointments';

    if( empty($atts['period_start']) || empty($atts['period_end']) || empty($atts['learner_id']) ) return;

    $period_start = $atts['period_start'];
    $period_end = $atts['period_end'];
    $learner_id = $atts['learner_id'];



    // get all appointments for children between period_start and period_end and customer = learner_id
    $bookly_customer_id = getcustomerID($learner_id);
    if( empty($bookly_customer_id) ):
        echo '<div class="alert mx-auto w-50"> customer is not found in bookly.</div>';
        return;
    endif;

    // query and get all appointments from bookly_customer_appointments table
    $appointments_results = $wpdb->get_results(
        "SELECT * FROM $bookly_appointments_table WHERE start_date BETWEEN '{$period_start}' AND '{$period_end}'"
    );
    $wpdb->flush();


    if( empty($appointments_results) ):
     echo '<div class="no-attendance-found"> No data available </div>';
     return;
    endif;


    ?>



    <div class="attendance-teacher-table-section">

    <style>
        h1.entry-title{
            display: none;
        }

        .no-attendance-found {
            text-align: center;
            margin: 5rem 0;
        }

    </style>


    <div class="attendance-container">


        <table class="attendance-capture collapse-search academic-attendance">
            <thead>
            <tr>
                <th> Date </th>
                <th> Time( Cairo ) </th>
                <th> Status </th>
                <th> Notes </th>
            </tr>
            </thead>
            <tbody>

            <?php
            $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
            foreach ( $appointments_results as &$appointments_result ):
                // check if customer_id = selected learner
                $appointmnet_id = $appointments_result->id;
                $appointmnet_start_date = $appointments_result->start_date;
                $appointmnet_end_date = $appointments_result->end_date;
                $ca_record = getBooklyCA($appointmnet_id);

                if( !empty($ca_record) ):
                    if( (int) $ca_record[0]->customer_id == (int) $bookly_customer_id ):

                        // convert class date and time from UTC to Cairo
                        $appointmnet_start_date = convertTimezone1ToTimezone2 ( $appointmnet_start_date, 'UTC', 'Africa/Cairo' );
                        $appointmnet_end_date = convertTimezone1ToTimezone2 ( $appointmnet_end_date, 'UTC', 'Africa/Cairo' );

                        // get data for this ca record
                        $class_date = date('D m-d-Y', strtotime( $appointmnet_start_date ));
                        $class_time = date('h:i a', strtotime($appointmnet_start_date)) . '-' . date('h:i a', strtotime($appointmnet_end_date));


                        $ca_id = $ca_record[0]->id;
                        $progress_notes = $ca_record[0]->notes;
                        $attendance_status = $ca_record[0]->status;

                        if( $attendance_status !== 'approved' && $attendance_status !== 'pending' && $attendance_status !== 'rejected' ):

                            // get bb group from custom fields
                            $stored_bb_group_custom_field = json_decode($ca_record[0]->custom_fields);
                            $stored_late_mins = '';
                            $stored_actual_min = '';
                            $private_notes = '';

                            //get bookly custom fields data
                            foreach ( $stored_bb_group_custom_field as $field_data ):
                                $custom_field_id = (int) $field_data->id;
                                if( $custom_field_id === $bb_custom_field_id ):
                                    $stored_bb_group_id = (int) $field_data->value;
                                endif;

                                if( $field_data->id === 95778 ): // late mins value
                                    $stored_late_mins = $field_data->value;
                                endif;

                                if( $field_data->id === 2583 ): // actual mins value
                                    $stored_actual_min = $field_data->value;
                                endif;

                                if( $field_data->id === 24491 ): // private notes value
                                    $private_notes = $field_data->value;
                                endif;

                            endforeach;

                            ?>

                            <tr>

                                <td> <?= $class_date ?> </td>
                                <td> <?= $class_time ?> </td>
                                <td>
                                    <p> <?= BOOKLY_CUSTOM_STATUS[$attendance_status] ?> </p>
                                    <?php if( $stored_actual_min > 0 ): ?>
                                        <p>Actual Mins. <?= !empty($stored_actual_min) ? $stored_actual_min : 0 ?> </p>
                                    <?php endif; ?>
                                    <?php if( $stored_late_mins > 0 ): ?>
                                        <p>Late Mins. <?= !empty($stored_late_mins) ? $stored_late_mins : 0 ?> </p>
                                    <?php endif; ?>
                                </td>
                                <td class="notes">
                                    <textarea class="progress-notes" name="" id="" cols="30" rows="2" disabled><?= !empty($progress_notes) ? $progress_notes : '' ; ?></textarea>
                                </td>
                            </tr>

                        <?php
                        endif;
                    endif;
                endif;
            endforeach;
            ?>



            </tbody>
        </table>

    </div>

    <script>

        jQuery(document).ready(function(){

            jQuery('.academic-attendance').dataTable({
                "pageLength": 10,
                "order": [[0, "desc"]],
                "autoWidth": true,
            });


            function HandleText (){

                $(".billing-period-title").html("Period");
                let prevBtnPage= '<span class="material-icons">arrow_back_ios</span>';
                let nextBtnPage='<span class="material-icons">arrow_forward_ios</span>';

                $(".next .page-link").html($(nextBtnPage));
                $(".previous .page-link").html($(prevBtnPage));
            }
            HandleText ();
        });
    </script>

    <?php

}
add_shortcode('academic_attendance_table', 'academic_attendance_table_callback');


// shortcode for all accounts page template
function all_parent_status_page() {
    get_template_part('template-parts/template-all-accounts');
}
add_shortcode('all_parent_status_page', 'all_parent_status_page');


// shortcode to get upcoming classes
function upcoming_group_classes($atts) {

    get_template_part('template-parts/template-all-accounts');
}
add_shortcode('upcoming_group_classes', 'upcoming_group_classes');


// shortcode to verify schedule makeup
function verify_schedule_makeup(){
    $token = $_GET['verify_token'];
    $wp_user_id = $_GET['uid'];

    if( empty($token) || empty($wp_user_id) ): ?>
        <div class="alert alert-danger verify-token"> Unauthorized access. Please contact support. </div>
<?php
    else:

       if( checkIfParent($wp_user_id) == false ): ?>
           <div class="alert alert-danger verify-token"> Unauthorized access. Please contact support. </div>
<?php else:

        $pending_session = getSessionDatafromToken($token);
        if( !empty($pending_session) && $pending_session['status'] == 'pending'  ):
        // open confirm modal if session is still pending
?>
        <script >
             jQuery(document).ready(function(){
                $(window).on('load', function() {
                    $('#header-schedual-class').modal('show');
                });
             });
        </script>
    <?php
            elseif( !empty($pending_session) && $pending_session['status'] !== 'pending' ):?>
                <div class="alert alert-danger verify-token"> Session has been <?= $pending_session['status'] ?> before. </div>
<?php
            else: ?>
                <div class="alert alert-danger verify-token"> Session not found. </div>
<?php       endif;
        endif;
    endif;

}
add_shortcode('verify_schedule_makeup', 'verify_schedule_makeup');
