<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components;
use Bookly\Backend\Modules as Backend;
use Bookly\Backend\Modules\Calendar\Proxy;

/**
 * @var Bookly\Lib\Entities\Staff[] $staff_members
 * @var array                       $staff_dropdown_data
 * @var array                       $services_dropdown_data
 * @var int                         $refresh_rate
 */



    global $wp;
    $current_page_url = home_url( $wp->request );
    $bb_group_id = bp_get_current_group_id();

    $calendar_name = 'parent';

    $user_is_support = user_has_role(get_current_user_id(), 'support');
    $user_is_hr = user_has_role(get_current_user_id(), 'hr');
    $user_is_assistant_principle = user_has_role(get_current_user_id(), 'assistant_principle');
    $user_is_administrator = user_has_role(get_current_user_id(), 'administrator');
    $user_is_enrollment = user_has_role(get_current_user_id(), 'enrollment');

    if( !isset($bb_group_id) || $bb_group_id === 0 ):

        $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');

        if( $user_is_team_leader ):
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
                    $teachers_options .= '<option '.$selected.' value="'. $team_teacher['bookly_staff_id'] .'">  ' . $staff_full_name . ' ' . $team_teacher['email'] .' </option>';
                endforeach;
            endif;
        elseif(  $user_is_support || $user_is_hr || $user_is_assistant_principle || $user_is_administrator || $user_is_enrollment ):
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
                    $teachers_options .= '<option '.$selected.' value="'. $staff->id .'"> ' . $staff_full_name . ' ' . $staff->email .' </option>';
                endforeach;
            endif;
            $hide_select = '';
        else:
            $teachers_options = '';
            $hide_select = 'hidden';
        endif;
        

        // admin can delete without validation
        if( $user_is_administrator ):
            $delete_without_validation = 'true';
        else:
            $delete_without_validation = 'false';
        endif;

        // is teacher in support or team_leader show delete

        $user_is_team_leader = user_has_role(get_current_user_id(), 'team_leader');
        if( $user_is_support || $user_is_team_leader || $user_is_hr || $user_is_assistant_principle || $user_is_administrator || $user_is_enrollment ):
?>
    <div class="team_teachers">
        <select class="select2 get_staff_appointments" id="team_staff_id">
            <option disabled selected> -- select teacher --</option>
            <?php echo $teachers_options; ?>
        </select>
        <input type="hidden" id="current_page_url" value="<?php echo $current_page_url; ?>">
        <input type="hidden" id="display_teacher_timezone" value="true">
        <a href="#" class="reset_appointments_view btn"> Reset </a>
    </div>


<?php
        endif;
    endif; ?>

<div id="bookly-tbs" class="wrap">
    <div class="card">
        <div class="card-body">
            <div class="mt-3 position-relative">
                <div class="bookly-ec-loading" style="display: none">
                    <div class="bookly-ec-loading-icon"></div>
                </div>
                <div class="bookly-js-calendar <?= $calendar_name ?>"></div>
               
                <?php Bookly\Backend\Components\Dialogs\Appointment\Edit\Dialog::render() ?>
                <?php Bookly\Backend\Modules\Calendar\Proxy\Shared::renderAddOnsComponents() ?>
            </div>
        </div>
    </div>

</div>

<div class="modal micromodal-slide" id="delete-single-program-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title">
                    Delete Single Program
                </h2>
                <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
            </header>
            <main class="modal__content" id="modal-1-content">
                <div class="ajax_image_section delete"> <div class="ajaxloader"></div> </div>

<!--                <label for="deleteAllProgram"> <input type="checkbox" name="selectAll" id="deleteAllProgram" /> Delete All </label>-->
<!--                <label for="deleteBBgroup"> <input type="checkbox" name="delete_program" id="deleteBBgroup" value="" /> BuddyBoss Group </label>-->
<!--                <label for="deleteLDgroup"> <input type="checkbox" name="delete_program" id="deleteLDgroup" value="" /> LearnDash Group </label>-->
<!--                <label for="deleteGFentries"> <input type="checkbox" name="delete_program" id="deleteGFentries" value="" /> Gravity Form Entries </label>-->
<!--                <label for="deleteBooklyAppointments"> <input type="checkbox" name="delete_program" id="deleteBooklyAppointments" value="" /> Bookly Appointments </label>-->
<!--                <button href="" class="modal__btn confirm-delete-program disabled" disabled> <i class="far fa-trash-alt"></i> Delete </button>-->
                <input type="hidden" id="appointment_id" value="">
                <input type="hidden" id="stored_bb_group_id" value="">
                <input type="hidden" id="validate_delete_program" value="">
                <input type="hidden" id="delete_without_validation" value="<?php echo $delete_without_validation ?>">
                <div class="validate_delete_result text-center">  </div>

            </main>
            <footer class="modal__footer">
                <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window"> No, go back  </button>
                <button href="" class="modal__btn permanent-delete-program disabled" disabled> <i class="far fa-trash-alt"></i> Yes, proceed </button>
            </footer>
        </div>
    </div>
</div>

<div class="modal micromodal-slide" id="single-event-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1">
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title"> </h2>
            </header>
            <main class="modal__content" id="modal-1-content">
                <input type="hidden" id="appointment_id" value="">

                <div class="radioBtns duplicate-actions hidden">
                    <input type="radio" value="prev_day" class="select_day" name="select_day" id="prev_day" checked>
                    <input type="radio" value="next_day" class="select_day" name="select_day" id="next_day">

                    <label for="prev_day" class="option prev_day">
                        <div class="dot"></div>
                        <span>Previous Day</span>
                    </label>
                    <label for="next_day" class="option next_day">
                        <div class="dot"></div>
                        <span>Next Day</span>
                    </label>
                </div>

                <input type="hidden" class="single_appointment_id" value="">
                <input type="hidden" class="single_event_bb_group_id" value="">
                <input type="hidden" class="single_event_action" value="">

                <div class="single-event-result text-center"> </div>
                <div class="action_loader hidden"> <div id="loader"></div> </div>

            </main>
            <footer class="modal__footer">
                <button class="modal__btn modal__btn-danger" data-micromodal-close aria-label="Close this dialog window"> No, go back  </button>
                <button href="" class="modal__btn single_event_proceed" > Yes, proceed </button>
            </footer>
        </div>
    </div>
</div>


<div class="modal micromodal-slide" id="success-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" >
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title">
                    Delete Single Program Status
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


<div class="modal micromodal-slide" id="error-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" >
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
            <header class="modal__header">
                <h2 class="modal__title" id="modal-1-title">
                    Delete Single Program Status
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


