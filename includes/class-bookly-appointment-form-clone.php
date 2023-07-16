<?php

function new_bookly_form()
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
    for($i = 6; $i <= 24; $i++):
        $hours_options .= '<option value="'. sprintf("%02d", $i).'">'. date("h.iA", strtotime("$i:00")).'</option>';
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
                                <li class="col-md-3 text-center"> <a href="#" class="find_teacher"> <i class="fas fa-chalkboard-teacher"></i> Find Teacher  </a>  </li>
                                <li class="col-md-6 teacher_select">
                                    <label class="mr-sm-2" for="bookly_teacher">Available Teachers:</label>
                                    <select class="custom-select mr-sm-2 select2" name="bookly_teacher[]">

                                    </select>
                                </li>
                                <li class="col-md-3 text-center"> <a href="#" class="check_time_overlap"> <i class="fas fa-user-clock"></i> Check Availability  </a> </li>
                            </ul>
                        </div>



                    </div>

                </div>





            </div>
        </div>

        <input type="text" value="1" id="overlap_value">

        <button type="submit" class="submit_booking" name="submit_booking"> Submit </button>
    </form>

    <div class="ajax_result"></div>


    <?php



}

add_shortcode('new_bookly_form', 'new_bookly_form');


