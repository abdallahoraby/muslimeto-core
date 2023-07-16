<?php
// Set BP to use wp_mail
add_filter( 'bp_email_use_wp_mail', '__return_true' );

// enqueue lostpassword script
function my_themejs_scripts() {
    if(isset($_GET['action']) && $_GET['action'] === 'lostpassword'):
        wp_enqueue_script( 'custom-js', plugin_dir_url( __FILE__ ) . '../public/js/custom.js', array( 'jquery' ), rand(), true );
    endif;
}
add_action( 'login_enqueue_scripts', 'my_themejs_scripts' );

// enqueue login script
function my_login_scripts() {
    wp_enqueue_script( 'custom-login', plugin_dir_url( __FILE__ ) . '../public/js/custom-login.js', array( 'jquery' ), rand(), true );
}
add_action( 'login_enqueue_scripts', 'my_login_scripts' );


remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
add_filter( 'wp_mail_content_type', 'set_html_content_type' );
function set_html_content_type() {
    return 'text/html';
}

function back_to_site_text( $translated ) {
    if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) )
        $translated = str_ireplace(  'Request reset link', 'Find My Account',  $translated );
    return $translated;
}
add_filter('gettext', 'back_to_site_text');



function clean_phone($string) {
    $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    //  preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
function check_u_email() {
    if(email_exists($_POST['email'])){

        $user_id = email_exists($_POST['email']);
        $code =  rand(11111,99999);
        $u_ids = getParentChilds($user_id);
        $user_info = get_userdata($user_id);
        $phone = get_user_meta($user_id, 'primary_phone', true );


        if (substr($_POST['email'], 0, 3) === 'std'){
            $prnt_id = email_exists(get_user_meta($user_id, 'memb__ParentEmail', true ));
            echo '<div class="ch_m_e" style="padding: 10px;"><p>A learner account found!</p><p> Primary Account (or Parent) is required to reset your password. </p><br><div class="form-check">
  <input class="form-check-input choose_acc prnt_acc" type="radio" name="choose_acc" checked value="'.$prnt_id.'" id="flexRadioDefault1"><label class="form-check-label" for="flexRadioDefault1">My Parent Is Here</label></div>
  <div class="form-check">
  <input class="form-check-input choose_acc" type="radio" name="choose_acc" value="help" id="flexRadioDefault2"><label class="form-check-label" for="flexRadioDefault2">Get help?</label></div><input type="hidden" class="child_id" value="'.$user_id.'"/><input type="button" id="child_choose" class="button button-primary button-large" value="Confirm"></div>
  <div class="ch_m_e2" style="padding: 10px;display:none"><div class="form-check">
  <p>How would you like to receive OTP code? </p> <input class="form-check-input check_way" type="radio" value="email" name="check_way" id="flexRadioDefault1" checked> <label class="form-check-label" for="flexRadioDefault1"> By Email </label> </div><div class="form-check"> <input class="form-check-input check_way" name="check_way" type="radio" value="phone" id="flexRadioDefault2"> <label class="form-check-label" for="flexRadioDefault2"> By SMS to Phone on Profile </label> </div>
  <div class="form-check"> <input name="check_way" class="form-check-input check_way" type="radio" value="both" id="flexRadioDefault3"> <label class="form-check-label" for="flexRadioDefault3">By Both </label> </div><input type="button" id="ch_way" class="button button-primary button-large" value="Confirm"><style>.form-check-input{height: 15px!important;}#wp-submit{display:none!important;}</style></div>' ;
            exit;
        }
        if($u_ids){
            echo '<div class="ch_m_e" style="padding: 10px;"><p>Account found!</p><p> Would you like to reset password for your account or one of your family members?</p><div class="form-check">
      <input class="form-check-input choose_acc prnt_acc" type="radio" name="choose_acc" checked value="'.$user_id.'" id="flexRadioDefault1"><label class="form-check-label" for="flexRadioDefault1">My Account</label></div>' ;
            foreach ($u_ids as $u_id):$user_info = get_userdata($u_id); $ff = str_split($user_info->first_name,3) ;?>
                <div class="form-check">
                    <input class="form-check-input choose_acc" type="radio" name="choose_acc" value="<?php echo $u_id ?>" id="flexRadioDefault<?php echo $u_id ?>" data-parent="<?php echo $user_id ?>"><label class="form-check-label" for="flexRadioDefault<?php echo $u_id ?>"><?php echo
                            $ff[0].'*****' ;?>
                    </label>
                </div>
            <?php endforeach; echo '<input type="button" id="prnt_choose1" class="button button-primary button-large" value="Confirm"></div>
      <div class="ch_m_e2" style="padding: 10px;display:none"><div class="form-check">
      <p>How would you like to receive OTP code? </p> <input class="form-check-input check_way" type="radio" value="email" name="check_way" id="flexRadioDefault1" checked> <label class="form-check-label" for="flexRadioDefault1"> By Email </label> </div><div class="form-check"> <input class="form-check-input check_way" name="check_way" type="radio" value="phone" id="flexRadioDefault2"> <label class="form-check-label" for="flexRadioDefault2"> By SMS to Phone on Profile </label> </div>
      <div class="form-check"> <input name="check_way" class="form-check-input check_way" type="radio" value="both" id="flexRadioDefault3"> <label class="form-check-label" for="flexRadioDefault3">By Both </label> </div><input type="button" id="ch_way" class="button button-primary button-large" value="Confirm"><style>.form-check-input{height: 15px!important;}#wp-submit{display:none!important;}</style></div>';
        }
        //return;a
        if($phone && !$u_ids){
            echo '<div class="ch_m_e" style="padding: 10px;"><div class="form-check"><p>Account found!</p><br>
      <p>How would you like to receive OTP code? </p> <input class="form-check-input" type="radio" name="check_way" value="email" id="flexRadioDefault1" checked/> <label class="form-check-label" for="flexRadioDefault1"> By Email </label> </div><div class="form-check"> <input class="form-check-input" type="radio" name="check_way" value="phone" id="flexRadioDefault2"> <label class="form-check-label" for="flexRadioDefault2"> By SMS to Phone on Profile </label> </div>
      <div class="form-check"> <input class="form-check-input" type="radio" name="check_way" value="both" id="flexRadioDefault3"> <label class="form-check-label" for="flexRadioDefault3">By Both </label> </div><input type="button" id="ch_way" class="button button-primary button-large" value="Confirm"><style>.form-check-input{height: 15px!important;}#wp-submit{display:none!important;}</style></div>';
        }
        if(!$phone && !$u_ids) {


            // var_dump(send_otp_test('ibrahim','himamohamed1991@gmail.com','00201060185320','PHONE',false,  array('status' => 'SUCCESS' ,'txId'=> rand())));

            $content = '<p>Salaam,</p><p>' . $code . ' is your one time password (OTP)</p><p>The one time password is valid for 5 minutes from the time it is generated.</p><p>JAK</p>';

            $bp_templte =  bp_email_core_wp_get_template( $content ,  $user_info );
            setcookie('u_email', $_POST['email'] , time() + 90 , "/");
            setcookie('otp_code', $code  , time() +  300 , "/");
            setcookie('user_id', $user_id  , time() + 300  , "/");

            wp_mail($_POST['email'] , 'Your One Time Password (OTP)' , $bp_templte );
            echo '<div class="ch_m_e" style="padding: 10px;"><p>OTP code has been sent to your email</p><br>
      <input type="text" name="otp_code" class="otp_code" placeholder="Enter code here"/>
      <input type="button" id="wp-submit2" class="button button-primary button-large" value="Confirm">
          <style>#wp-submit{display:none}</style> </div>';
        }

    }else{
        echo '<div class="ch_m_e" style="padding: 10px;"><p style="text-align: center;color:red">Email Address Does Not Exist!</p></div>' ;
    }
    exit;
}

add_action('wp_ajax_check_u_email', 'check_u_email');
add_action( 'wp_ajax_nopriv_check_u_email', 'check_u_email' );



function check_u_code() {
    $code = $_COOKIE['otp_code'];
    if($code == $_POST['code']){
        echo '<div class="ch_m_e" style="padding: 10px;"><p style="text-align: center;">Please set the new password below</p><br>
 <input type="text" name="new_pass" class="new_pass" />
 <input type="button" id="wp-submit3" class="button button-primary button-large" value="Confirm">
  <style>#wp-submit{display:none}</style></div>' ;
    }else{
        echo '<div class="ch_m_e" style="padding: 10px;"><p style="text-align: center;color:red">OTP code is invalid!</p>
  <br>
  <input type="text" name="otp_code" class="otp_code" placeholder="Enter code here"/>
  <input type="button" id="wp-submit2" class="button button-primary button-large" value="Confirm"><style>#wp-submit{display:none}</style></div>' ;

    }
    exit;
}
add_action('wp_ajax_check_u_code', 'check_u_code');
add_action( 'wp_ajax_nopriv_check_u_code', 'check_u_code' );


function check_u_way() {
    $code =  rand(11111,99999);
    $content = '<p>Salaam,</p><p>' . $code . ' is your one time password (OTP)</p><p>The one time password is valid for 5 minutes from the time it is generated.</p><br><p>JAK</p>';
    $way = $_POST['way'];
    $uid = $_POST['uid'];
    $prnt = $_POST['prnt'];
    $prnt_obj = get_user_by('id', $prnt);
    $ch_obj = get_user_by('id', $uid);
    $email = $prnt_obj->user_email;

    $dd =  bp_email_core_wp_get_template( $content ,  $author_obj );

    setcookie('otp_code', $code  , time() +  3600 , "/");
    setcookie('user_id', $uid  , time() +  3600 , "/");
    setcookie('u_email', $ch_obj->user_email , time() + 90 , "/");
    if($way == "email"){
        $w = $way;
        wp_mail($email , 'Your One Time Password (OTP)' , $dd  );
    }elseif($way == "phone") {
        $phone = get_user_meta($prnt, 'primary_phone', true );
        $c_phone = clean_phone($phone);
        $w = $way;
        $mess = 'Your OTP code is '.$code.' Please use this code to complete your transaction. -JAK';
        $response = twl_send_sms( array( 'number_to' => $c_phone , 'message' => $mess ) );
        // pre_dump($response);
    }else {
        $w = 'email and your phone';
        $phone = get_user_meta($prnt, 'primary_phone', true );
        $c_phone = clean_phone($phone);
        $mess = 'Your OTP code is '.$code.' Please use this code to complete your transaction. -JAK';
        $response = twl_send_sms( array( 'number_to' => $c_phone ,  'message' => $mess ) );
        wp_mail($email , 'Your One Time Password (OTP)' , $dd  );
    }
    echo '<div class="ch_m_e" style="padding: 10px;"><p>OTP code has been sent to your '.$w.'</p><br>
  <input type="text" name="otp_code" class="otp_code" placeholder="Enter code here" />
  <input type="button" id="wp-submit2" class="button button-primary button-large" value="Confirm">
        <style>#wp-submit{display:none}</style></div>';
    exit;
}

add_action('wp_ajax_check_u_way', 'check_u_way');
add_action( 'wp_ajax_nopriv_check_u_way', 'check_u_way' );


function set_new_pass() {
    $user_id = $_COOKIE['user_id'];
    $user_info = get_userdata($user_id);
    setcookie('u_email', $user_info->user_email , time() + 90 , "/");
    wp_set_password($_POST['new_pass'], (int)$user_id );
    echo '<div class="ch_m_e style="padding: 10px;"<p style="text-align: center;color:green;">Password has been set up </p><br><p style="text-align: center;">Redirect to login page now...</p><style>#wp-submit{display:none}</style></div>' ;
    exit;
}
add_action('wp_ajax_set_new_pass', 'set_new_pass');
add_action( 'wp_ajax_nopriv_set_new_pass', 'set_new_pass' );




function add_new_learner_for_prnt(){

    $user_data = get_userdata(get_current_user_id());
    if( in_array('parent', $user_data->roles)){
        $prnt_id = get_current_user_id();
    }

    if( $_POST['prnt_id'] ){
        $prnt_id = $_POST['prnt_id'];
    }

    if(get_user_meta($prnt_id ,'memb_ReferralCode', true) && !get_user_meta($prnt_id ,'account_code', true)){
        add_user_meta($prnt_id ,'account_code', get_user_meta($prnt_id ,'memb_ReferralCode', true));
    }

    $prnt_ref = get_user_meta($prnt_id ,'account_code', true)?get_user_meta($prnt_id ,'account_code', true):get_user_meta($prnt_id ,'memb_ReferralCode', true);


    if(!$prnt_id){wp_send_json_error( 'no parent found !' );}


    if(!$prnt_ref){
        $new_code =  'prnt-' . rand(111111111,9999999999) . '_uid_' . $prnt_id;

        if( !update_user_meta($prnt_id ,'account_code', $new_code) ){
            add_user_meta($prnt_id ,'account_code', $new_code);
        }

    }

    $prnt_ref = get_user_meta($prnt_id ,'account_code', true);
    $prnt_ref = substr($prnt_ref, 5);
    $child_ref = 'chld-' . $prnt_ref;
    $userdata = array(
        'user_login' => $_POST['u_email'],
        'user_pass'  =>  wp_hash_password( $_POST['u_pass'] ) ,
        'user_email' => $_POST['u_email'],
        'first_name' => $_POST['FirstName'],
        'last_name'  => $_POST['LastName'],
        'role' => 'tutoring_student',
        // no plain password here!
    );
    $chld_id = wp_insert_user( $userdata ) ;
    if (  $chld_id  ){
        global $wpdb;
        add_user_meta($chld_id, 'pw_string', $_POST['u_pass']);
        add_user_meta($chld_id, 'primary_phone', $_POST['primary_phone']);
        add_user_meta($chld_id, 'secondary_email', $_POST['secondary_email']);
        add_user_meta($chld_id, 'birthday', $_POST['birthday']);
        add_user_meta($chld_id, 'gender', $_POST['gender']);
        add_user_meta($chld_id, 'relation', $_POST['relation']);
        add_user_meta($chld_id ,'account_code', $child_ref);
        update_user_meta( $chld_id, 'nickname', 'std'.$chld_id );
        $wpdb->update(
            $wpdb->users,
            ['user_login' => 'std'.$chld_id ,'user_email' => 'std' .$chld_id. '@muslimeto.com'],
            ['ID' => $chld_id]
        );
    }


    if ( ! is_wp_error( $chld_id ) ) {
        wp_send_json_success( $chld_id );
    }else{
        wp_send_json_error( $chld_id );
    }
    exit;
}
add_action('wp_ajax_add_new_learner_for_prnt', 'add_new_learner_for_prnt');
add_action( 'wp_ajax_nopriv_add_new_learner_for_prnt', 'add_new_learner_for_prnt' );

function link_learner_to_prnt(){
    $prnt_id = $_POST['prnt_id']  ;
    $learner_id = $_POST['learner_id']  ;

    if( isset($prnt_id) && isset($learner_id) ){

        if(get_user_meta($prnt_id ,'memb_ReferralCode', true) && !get_user_meta($prnt_id ,'account_code', true)){
            $updated = update_user_meta($prnt_id ,'account_code', get_user_meta($prnt_id ,'memb_ReferralCode', true));
            if(!$updated) {
                add_user_meta($prnt_id ,'account_code', get_user_meta($prnt_id ,'memb_ReferralCode', true));
            }
        }

        $prnt_ref = get_user_meta($prnt_id ,'account_code', true);

        if(!$prnt_ref){
            $new_code =  'prnt-' . rand(111111111,9999999999) . '_uid_' . $prnt_id;
            add_user_meta($prnt_id ,'account_code', $new_code);
        }

        $prnt_ref = get_user_meta($prnt_id ,'account_code', true);
        $prnt_ref = substr($prnt_ref, 5);
        $child_ref = 'chld-' . $prnt_ref;

        $updated = update_user_meta($learner_id ,'account_code', $child_ref);

        $child_code = get_user_meta($learner_id ,'account_code', true);

        if( !$child_code ){
            add_user_meta($learner_id ,'account_code', $child_ref);
        }

        wp_send_json_success( $learner_id );

    }

    exit;
}
add_action('wp_ajax_link_learner_to_prnt', 'link_learner_to_prnt');
add_action( 'wp_ajax_nopriv_link_learner_to_prnt', 'link_learner_to_prnt' );


function muslimito_create_web_hooks_log() {
    global $wpdb;
    $table_name =  "web_hooks_log";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      event_name text NOT NULL,
	    event_type text NOT NULL,
      payload text NOT NULL,
      user_id bigint(20) UNSIGNED NOT NULL,
      send_date datetime NOT NULL,
      PRIMARY KEY id (id)
      ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'muslimito_create_web_hooks_log');

//muslimito_Add_Payment_Method
function muslimito_Add_Payment_Method() {

    $wp_user_id = get_current_user_id();
    $ContactId  = wpf_get_contact_id($wp_user_id);
    $token = get_token_from_dev();
    $user_info = get_userdata( $wp_user_id );

    $body =  array(
        'card_number' => $_POST['cc_number'],
        'card_type' => $_POST['cardtype'],
        'email_address' => $user_info->user_email,
        'expiration_month' => $_POST['expirationmonth'],
        'expiration_year' => $_POST['expirationyear'],
        'name_on_card' => $_POST['nameoncard'],
        'address' => array(
            'line1' => $_POST['streetaddress1'],
            'line2' => $_POST['streetaddress2'],
            'postal_code' => $_POST['postalcode'],
            'region' => $_POST['state'],
            'country_code' => $_POST['country'],
            'locality' => $_POST['city'],
        )
    );

    $response = wp_remote_post( "https://api.infusionsoft.com/crm/rest/v1/contacts/$ContactId/creditCards", array(
        'body'    => wp_json_encode( $body ),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer ".$token
        ),
    ));
    $apiBody = json_decode( wp_remote_retrieve_body( $response ) );
    if( isset($apiBody->validation_status)  && $apiBody->validation_status == "Good"){
        $return = array(
            'message' => 'Credit Card Added Successfully.'
        );
        wp_send_json_success( $return );
    }else{
        $return = array(
            'message' => 'Something Went Wrong!.'
        );
        wp_send_json_error($return);
    }
    exit;
}
add_action('wp_ajax_muslimito_Add_Payment_Method', 'muslimito_Add_Payment_Method');
add_action( 'wp_ajax_nopriv_muslimito_Add_Payment_Method', 'muslimito_Add_Payment_Method' );

//muslimito_update_user_profile
function muslimito_update_user_profile() {

    $wp_user_id = get_current_user_id();
    $user_info = get_userdata( $wp_user_id );

    $user_data = wp_update_user(array(
        'ID' => $wp_user_id,
        'user_email' => $_POST[ 'u_email' ],
        'first_name'    =>   $_POST[ 'f_name' ],
        'last_name'     =>   $_POST[ 'l_name' ],
    ));
    $metas = $_POST;
    unset($metas['u_email']);
    unset($metas['f_name']);
    unset($metas['l_name']);

    foreach ($metas as $key => $value) {
        if(!update_user_meta( $wp_user_id, $key, $value ) && !get_user_meta( $wp_user_id, $key, true )){
            add_user_meta( $wp_user_id, $key, $value );
        }
    }
    $prnt_ref = get_user_meta($wp_user_id ,'account_code', true)?get_user_meta($wp_user_id ,'account_code', true):get_user_meta($wp_user_id ,'memb_ReferralCode', true);
    $new_code = $prnt_ref ? $prnt_ref :  'prnt-' . rand(111111111,9999999999) . '_uid_' . $wp_user_id;
    $account_code = update_user_meta( $wp_user_id, 'account_code', $new_code );

    if(!$account_code && !$prnt_ref){
        add_user_meta( $wp_user_id, 'account_code', $new_code );
    }


    if ( is_wp_error( $user_data ) ) {
        echo 'Error.';
    } else {
        wp_send_json_success('User profile updated.') ;
    }

    exit;
}
add_action('wp_ajax_muslimito_update_user_profile', 'muslimito_update_user_profile');
add_action( 'wp_ajax_nopriv_muslimito_update_user_profile', 'muslimito_update_user_profile' );


function muslimito_get_parent_invoices( $u_id = null , $type = null){
    $type =  $type ? $type : 'false';
    $user_id = $u_id ? $u_id : get_current_user_id();
    $contact_id = wpf_get_contact_id( $user_id );
    $ch = curl_init();
    $token = get_token_from_dev();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/orders?paid=$type&contact_id=$contact_id"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->orders)) return $data->orders   ;
    return false;
}


function muslimito_get_parent_subscriptions( $u_id = null){

    $user_id = $u_id ? $u_id : get_current_user_id();
    $contact_id = wpf_get_contact_id( $user_id );
    $ch = curl_init();
    $token = get_token_from_dev();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?contact_id=$contact_id"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->subscriptions)):
        $subscriptions = $data->subscriptions;
        $actives = array_keys(array_column($subscriptions ,  'active' ) , true);
        // foreach ($actives as $act) {
        //   $id = $subscriptions[$act]->product_id ;
        //   $url2="https://api.infusionsoft.com/crm/rest/v1/products/$id";
        //   curl_setopt($ch, CURLOPT_URL, $url2);
        //   $result2[] = json_decode(curl_exec($ch));
        // }
        foreach ($actives as $act) {
            $active_subs[] =  $subscriptions[$act];
        }
        return $active_subs;

    endif;
}
function muslimito_get_sub_name( $s_id ){
    $ch = curl_init();
    $token = get_token_from_dev();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/products/$s_id"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->product_name)) return $data->product_name;
    return false;
}


//muslimito_send_session_remind
function muslimito_send_session_remind() {
    $prnt_ids = array();
    $phones = array();
    $wp_user_id = get_current_user_id();
    $args = array(
        'group_id' => $_POST['cid'],
        'max' => 999,
        'exclude_admins_mods' => true
    );
    $group_members = groups_get_group_members($args);
    $group_ids = array_column( $group_members['members'] , 'ID');

    foreach ($group_ids as $group_id) {
        $prnt_ids[] = getParentID($group_id);
    }
    $prnt_ids = array_unique($prnt_ids);
    foreach ($prnt_ids as $prnt_id) {
        $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
        $created_at = $current_date_object->format('Y-m-d H:i:s');

        $args = array(
            "sender_id" => $wp_user_id,
            "receiver_id" => $prnt_id ,
            "cid" => $_POST['cid'],
            "aid" => $_POST['aid'],
            "type" => 'session_reminder',
            //  'message'=> $email_msg,
            "send_date" => $created_at
        );
        send_notification_for_user($args);
    }
    wp_send_json( $prnt_ids );
    wp_die();
}
add_action('wp_ajax_muslimito_send_session_remind', 'muslimito_send_session_remind');
add_action( 'wp_ajax_nopriv_muslimito_send_session_remind', 'muslimito_send_session_remind' );




function update_parent_code_at_login( $user_login, $user ){
    if(in_array('parent',$user->roles)){
        $user_id = $user->ID ;
        if(!get_user_meta( $user_id, 'account_code ', true )){
            $prnt_ref = get_user_meta($user_id ,'account_code', true);
            $new_code = 'prnt-' . rand(111111111,9999999999) . '_uid_' . $user_id;
            $code = $prnt_ref ? $prnt_ref : $new_code;
            add_user_meta( $user_id, 'account_code ', $code );
        }
    }
}
add_action('wp_login', 'update_parent_code_at_login', 10, 2);



function keap_opportunity_webhook(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $headers = apache_request_headers();
    if (array_key_exists('X-Hook-Secret', $headers)) {
        $xHookSecret = $headers['X-Hook-Secret'];
        header('X-Hook-Secret: ' . $xHookSecret);
        header("HTTP/1.1 200 OK");
        exit;
    }
}
add_action( 'wp_ajax_nopriv_keap_opportunity_webhook', 'keap_opportunity_webhook' );




function keap_webhook_url(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $headers = apache_request_headers();
    if (array_key_exists('X-Hook-Secret', $headers)) {
        $xHookSecret = $headers['X-Hook-Secret'];
        header('X-Hook-Secret: ' . $xHookSecret);
        header("HTTP/1.1 200 OK");
        exit;
    }
    $entries =  file_get_contents('php://input') ;
    $entries_d = json_decode($entries,true);
    $contact_id = $entries_d['object_keys'][0]['id'];
    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            =>  "https://api.infusionsoft.com/crm/rest/v1/contacts/$contact_id?optional_properties=custom_fields"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data_bef = curl_exec($ch);
    $data = json_decode($data_bef);
    curl_close($ch);
    if(isset($data->email_addresses[0]->email)){
        $user_id = email_exists($data->email_addresses[0]->email);
        if($user_id){
            if( !get_user_meta( $user_id, 'account_code', true ) ){
                $prnt_ref = get_user_meta($user_id ,'memb_ReferralCode', true);
                $new_code = 'prnt-' . rand(111111111,9999999999) . '_uid_' . $user_id;
                $code = $prnt_ref ? $prnt_ref : $new_code;

                if(!update_user_meta( $user_id, 'account_code', $code )){
                    add_user_meta( $user_id, 'account_code', $code );
                }

            }
        }else{
            $custom_idz = array_column( $data->custom_fields, 'id' ) ;
            $custom_contacts = array_column( $data->custom_fields, 'content' ) ;
            $custom_indx =  array_search(132,$custom_idz);
            $custom_val = $custom_contacts[$custom_indx];

            if(   $custom_val == 1  ){
                $rand_pass= rand(1111111,9999999);
                $userdata = array(
                    'user_login' => $data->email_addresses[0]->email,
                    'first_name' => $data->given_name,
                    'last_name' =>  $data->family_name,
                    'user_email' => $data->email_addresses[0]->email,
                    'role' => 'parent',
                    'user_pass'  =>  wp_hash_password($rand_pass) // no plain password here!
                );
                $user_id = wp_insert_user( $userdata ) ;
                if ( ! is_wp_error( $user_id ) ){
                    $new_code = 'prnt-' . rand(111111111,9999999999) . '_uid_' . $user_id;
                    add_user_meta($user_id ,'account_code', $new_code);
                    add_user_meta($user_id ,'primary_phone', $data->phone_numbers[0]->number);
                    add_user_meta($user_id ,'street_address_1', $data->addresses[0]->line1);
                    add_user_meta($user_id ,'street_address_2', $data->addresses[0]->line2);
                    add_user_meta($user_id ,'contact_type', $data->contact_type);
                    add_user_meta($user_id ,'postal_code', $data->addresses[0]->postal_code);
                    add_user_meta($user_id, 'pw_string', $rand_pass);
                }
            }
        }
        // wp_mail('himamohamed1991@gmail.com','conact edit' , $user_id);
    }
    exit;
}
add_action( 'wp_ajax_nopriv_keap_webhook_url', 'keap_webhook_url' );


function keap_webhook_payment_url(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    global $wpdb;
    $headers = apache_request_headers();
    if (array_key_exists('X-Hook-Secret', $headers)) {
        $xHookSecret = $headers['X-Hook-Secret'];
        header('X-Hook-Secret: ' . $xHookSecret);
        header("HTTP/1.1 200 OK");
        exit;
    }
    $entries =  file_get_contents('php://input') ;
    $entries_d = json_decode($entries,true);
    $apiUrl = $entries_d['object_keys'][0]['apiUrl'];

    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => $apiUrl
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->orders[0]->contact->email)):
        $u_email = $data->orders[0]->contact->email ;
        $c_id = $data->orders[0]->contact->id ;
        $user_id = email_exists($u_email);
        if($user_id){
            $args= array(
                "user_id" => $user_id,
                "event_name" => $entries_d['event_key'],
                "event_type" => $entries_d['object_type'],
                "payload" => $entries,
                "send_date" => date('Y-m-d H:i:s')
            );
            $wpdb->insert("web_hooks_log", $args);
            updateUserBillingIndicator('' ,$u_email);
            //  wp_mail('himamohamed1991@gmail.com','keap_payment_webhook' , $u_email);

        }else{
            add_tag_for_auser(424,$c_id);
        }
    endif;
    exit;
}
add_action( 'wp_ajax_nopriv_keap_webhook_payment_url', 'keap_webhook_payment_url' );


function keap_subs_webhook(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    global $wpdb;
    $headers = apache_request_headers();
    if (array_key_exists('X-Hook-Secret', $headers)) {
        $xHookSecret = $headers['X-Hook-Secret'];
        header('X-Hook-Secret: ' . $xHookSecret);
        header("HTTP/1.1 200 OK");
        exit;
    }
    $entries_d =  file_get_contents('php://input') ;

    $entries = json_decode($entries_d,true);
    $token = get_token_from_dev();
    $sub_id = $entries['object_keys'][0]['id'];
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => 'https://api.infusionsoft.com/crm/rest/v1/transactions/'.$sub_id
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->contact_id)):
        $contact_id = $data->contact_id ;
        $user = get_user_by_meta_data('infusionsoft_contact_id', $contact_id);
        if($user->user_email){
            $args= array(
                "user_id" => $user->ID,
                "event_name" => $entries['event_key'],
                "event_type" => $entries['object_type'],
                "payload" => $entries_d,
                "send_date" => date('Y-m-d H:i:s')
            );
            $wpdb->insert("web_hooks_log", $args);
            updateUserBillingIndicator('' ,$user->user_email);
        }
//   wp_mail('himamohamed1991@gmail.com','keap hook data' , $user->user_email);
        // $events = $entries['events'];
    endif;
    exit;
}
add_action( 'wp_ajax_nopriv_keap_webhook_subs_url', 'keap_subs_webhook' );





function keap_orders_webhook(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    global $wpdb;
    $headers = apache_request_headers();
    if (array_key_exists('X-Hook-Secret', $headers)) {
        $xHookSecret = $headers['X-Hook-Secret'];
        header('X-Hook-Secret: ' . $xHookSecret);
        header("HTTP/1.1 200 OK");
        exit;
    }
    $entries =  file_get_contents('php://input') ;
    $entries_d = json_decode(file_get_contents('php://input'),true);
    $apiUrl = $entries_d['object_keys'][0]['apiUrl'];

    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => $apiUrl
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->contact->email)):
        $u_email = $data->contact->email ;
        $user_id = email_exists($u_email);
        if($user_id){
            $args= array(
                "user_id" => $user_id,
                "event_name" => $entries_d['event_key'],
                "event_type" => $entries_d['object_type'],
                "payload" => $entries,
                "send_date" => date('Y-m-d H:i:s')
            );
            $wpdb->insert("web_hooks_log", $args);
            updateUserBillingIndicator('' ,$u_email);
        }
        //wp_mail('himamohamed1991@gmail.com','keap hook data' , $u_email);
        // $events = $entries['events'];
    endif;
    exit;
}
add_action( 'wp_ajax_nopriv_keap_webhook_orders_url', 'keap_orders_webhook' );


function connect_zoho_webhook(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $raw_data = file_get_contents('php://input');
    $entries = json_decode($raw_data,true);

    if( $entries[0]['eventType'] == 'Ticket_Thread_Add' || $entries[0]['eventType'] == 'Ticket_Update'){
        $ticketId = $entries[0]['payload']['ticketId'] ;
        $auth_token = 'Bearer '.get_zoho_token_from_dev();
        $headers=array(
            "Authorization: $auth_token",
            "contentType: application/json; charset=utf-8",
        );
        $url='https://desk.zoho.com/api/v1/tickets/'.$ticketId ;
        $ch= curl_init($url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_HTTPGET,TRUE);
        $data = json_decode(curl_exec($ch));
        $u_email = $data->email;
        $ticketNumber = $data->ticketNumber;
        $user_id = email_exists( $u_email );
        $link = site_url('ticket-details?id='.$ticketId) ;
        $tiick_num = '<a target="_blank" href="'.$link.'">'.$ticketNumber.'<a/>' ;
        if($user_id):
            bp_notifications_add_notification( array(
                'user_id'           => $user_id,
                'component_name'    => 'gamipress_buddyboss_notifications',
                'item_id'           => 1878,
                'component_action'  => 'Your ticket with number '.$tiick_num.' has been updated.',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1,
                'allow_duplicate'   => false,
            ));
        endif;
    }
    //wp_mail('himamohamed1991@gmail.com','zoho hook data' , $raw_data);
    exit;
}

add_action( 'wp_ajax_nopriv_zoho_webhook', 'connect_zoho_webhook' );

function gen_uuid(){
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}


//end refresh
function add_tag_for_auser($tag,$c_id){
    $ch = curl_init();
    $url='https://api.infusionsoft.com/crm/rest/tags/'.$tag.'/contacts';
    $authorization = "Authorization: Bearer ".get_token_from_dev();
    $fields = json_encode( array( "ids"=> [$c_id] ) );
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);curl_close($ch);
}
function remove_tag_for_auser($tag,$c_id){
    $ch = curl_init();
    $url='https://api.infusionsoft.com/crm/rest/tags/'.$tag.'/contacts/'.$c_id;
    $authorization = "Authorization: Bearer ".get_token_from_dev();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);curl_close($ch);
}


//get_active_subs_for_parent
function get_active_subs_for_parent(){
    $parents_id = $_POST['parent_id'];
    $ContactId  = wpf_get_contact_id($parents_id);
//  $token = get_option('keap_access_token');
    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer " . $token ;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    $actives=array();
    if(isset($data->subscriptions)):
        $subscriptions = $data->subscriptions;
        foreach ($subscriptions as $subscription) {
            if($subscription->active){
                $actives[]= $subscription;
            }
        }
        wp_send_json(array('data'=> $actives));
    else:
        wp_send_json(array('error'=>"parent has no subscriptions."));
    endif;
    exit;
}
add_action('wp_ajax_nopriv_get_active_subs_for_parent', 'get_active_subs_for_parent');
add_action('wp_ajax_get_active_subs_for_parent', 'get_active_subs_for_parent');
// get token from portal
function muslimeto_at_rest_init(){
    $namespace = 'wp';
    $route     = 'data_tokens';
    register_rest_route($namespace, $route, array(
        'methods'   => 'GET',
        'callback'  => 'muslimeto_at_rest_testing_endpoint'
    ));
}
add_action('rest_api_init', 'muslimeto_at_rest_init');
function muslimeto_at_rest_testing_endpoint($req){
    $token_data= get_option('keap_access_token') ;
    $data = array(
        "keap_token" => $token_data['token'] ,
        "keap_refresh" => get_option('keap_refresh_token') ,
        "zoho_token"=> get_option('zoho_access_token'),
        "zoom_token"=> get_option('zoom_access_token'),
    );
    if( !empty($data) )
        return wp_send_json_success($data);
    return false;

}

// get_balance_due get_balance_due get_balance_due get_balance_due

function get_paid_subscriptions($parent_id , $canceled=false){
    $token = get_token_from_dev();
    $ContactId  = wpf_get_contact_id($parent_id);
    if(!$ContactId){return false;}
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?limit=-1&contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);

    if(isset($data->subscriptions)){
        $subscriptions = $data->subscriptions;
        $active_tot=0;
        $recently_inactive_tot=0;
        $date2=date_create(date('Y-m-d'));
        foreach ($subscriptions as $subscription) {
            $date1=date_create($subscription->start_date);
            $diff=date_diff($date1,$date2);
            $diff_date = abs($diff->format("%R%a"));
            if($subscription->active) {
                $active_tot += ($subscription->billing_amount * $subscription->quantity);
            }elseif( $diff_date < 60 ){
                $recently_inactive_tot += ($subscription->billing_amount * $subscription->quantity) ;
            }
        }
        return $canceled ? $recently_inactive_tot : $active_tot;
    }else{
        return false;
    }
}
function get_canceled_amount($parent_id){
    return get_paid_subscriptions($parent_id,true);
}


function get_balance_due($parent_id){
    $token = get_token_from_dev();
    $ContactId  = wpf_get_contact_id($parent_id);
    if(!$ContactId){return false;}
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/orders?limit=-1&paid=false&contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->orders)):
        $orders = $data->orders;
        $tot_due=0;
        foreach ($orders as $ord) {
            $tot_due += ($ord->total_due - $ord->total_paid);
        }
        update_field('mslm_balancedue',$tot_due,'user_'.$parent_id);
        return $tot_due;
    else: return false;
    endif;

}



function get_parent_stats_from_keap($parent_id , $cid = null){

    $ContactId  = $cid ? $cid : wpf_get_contact_id($parent_id);
    if(!$ContactId){
        return false;
    }
    $token = get_token_from_dev() ;

    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/orders?limit=-1&paid=true&contact_id=".$ContactId
    ];
    $balance_due_opt = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/orders?limit=-1&paid=false&contact_id=".$ContactId
    ];
    $options2 = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?limit=-1&contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $last_pay = json_decode(curl_exec($ch));

    curl_setopt_array($ch, $options2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $all_subs = json_decode(curl_exec($ch));

    curl_setopt_array($ch, $balance_due_opt);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $overdue_opt = json_decode(curl_exec($ch));

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if( $httpCode == 200 ){
        if(isset($overdue_opt->orders)):
            $orders = $overdue_opt->orders;
            $tot_due=0;
            foreach ($orders as $ord) {
                $tot_due += ($ord->total_due - $ord->total_paid);
            }
            update_field('mslm_balancedue',$tot_due,'user_'.$parent_id);

        endif;
        if(isset($last_pay->orders)):
            $paid_orders = $last_pay->orders;
            $last_date = $paid_orders[0]->order_date;
            update_field('mslm_last_payment_on',$last_date,'user_'.$parent_id);
        endif;

        if(isset($all_subs->subscriptions)):
            $subscriptions = $all_subs->subscriptions;
            $today = date("Y-m-d");
            $active_dates = array_keys(array_filter(array_column($subscriptions , 'active' , 'next_bill_date')));
            $n_date = find_closest($active_dates, $today);
            $active_tot=0;
            $recently_inactive_tot=0;
            $date2=date_create(date('Y-m-d'));
            foreach ($subscriptions as $subscription) {
                if($subscription->active){
                    $actives[]= $subscription;
                }
                $date1=date_create($subscription->start_date);
                $diff=date_diff($date1,$date2);
                $diff_date = abs($diff->format("%R%a"));
                if($subscription->active) {
                    $active_tot += ($subscription->billing_amount * $subscription->quantity);
                }elseif( $diff_date < 60 ){
                    $recently_inactive_tot += ($subscription->billing_amount * $subscription->quantity) ;
                }
            }
            update_field('mslm_renews_on',$n_date,'user_'.$parent_id);

        endif;

        $re_data = array(
            "balance_due" => $tot_due ,
            "get_last_payment_on" => $last_date ,
            "get_renews_on"=> $n_date ,
            "inactive_total_subs" => $recently_inactive_tot ,
            "active_total_subs" => $active_tot ,
            "active_subs"=> $actives
        );
        return $re_data;



    }elseif ( $httpCode == 429 ){
        addLog(array(
            'event_title' => "Error: Keap API Quota limit  exceeded",
            'event_desc' => "Rate limit quota violation. Errorcode: policies.ratelimit.QuotaViolation",
        ));
        return false;
    }
    else {
        addLog(array(
            'event_title' => "Error: No Keap Connection",
            'event_desc' => "Error Connection with keep",
        ));
        return false;
    }// end check conecction

}



function scratchcode_create_payment_table() {
    global $wpdb;
    $table_name =  "notification_log";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    sender_id VARCHAR(50) NOT NULL,
    receiver_id bigint(20) UNSIGNED NOT NULL,
    cid bigint(20) UNSIGNED NOT NULL,
    aid bigint(20) UNSIGNED NOT NULL,
    type text NOT NULL,
    message text NOT NULL,
    send_date datetime NOT NULL,
    PRIMARY KEY id (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'scratchcode_create_payment_table');


function send_notification_for_user($args=array()){
    global $wpdb;
    // $args= array(
    //    "sender_id" => $sender_id,
    //    "receiver_id" => $receiver_id,
    //    "cid" => $cid,
    //    "aid" => $aid,
    //    "type" => $type,
    //    "send_date" => $send_date ,
    // );

    $user_id = $args['receiver_id'];
    $sender_id = $args['sender_id'];
    $type_group = $args['type'] . '_g';
    $message = get_field($type_group,3212);
    $portal_msg = $args['message']?$args['message']:$message['portal'];
    $sms_msg = $args['message']?$args['message']:$message['sms'];
    $email_msg = $args['message']?$args['message']:$message['email'];

    $user = get_userdata( $user_id );
    if (strpos($email_msg, '{sender_id}') !== false) {
        $email_msg  = str_replace("{sender_id}",$sender_id,$email_msg);
    }
    if (strpos($email_msg, '{receiver_id}') !== false) {
        $email_msg  = str_replace("{receiver_id}",$user_id,$email_msg);
    }
    if (strpos($email_msg, '{cid}') !== false) {
        $email_msg  = str_replace("{cid}",$args['cid'],$email_msg);
    }
    if (strpos($email_msg, '{aid}') !== false) {
        $email_msg  = str_replace("{aid}",$args['aid'],$email_msg);
    }

    if (strpos($portal_msg, '{sender_id}') !== false) {
        $portal_msg  = str_replace("{sender_id}",$sender_id,$portal_msg);
    }
    if (strpos($portal_msg, '{receiver_id}') !== false) {
        $portal_msg  = str_replace("{receiver_id}",$user_id,$portal_msg);
    }
    if (strpos($portal_msg, '{cid}') !== false) {
        $portal_msg  = str_replace("{cid}",$args['cid'],$portal_msg);
    }
    if (strpos($portal_msg, '{aid}') !== false) {
        $portal_msg  = str_replace("{aid}",$args['aid'],$portal_msg);
    }

    if (strpos($sms_msg, '{sender_id}') !== false) {
        $sms_msg  = str_replace("{sender_id}",$sender_id,$sms_msg);
    }
    if (strpos($sms_msg, '{receiver_id}') !== false) {
        $sms_msg  = str_replace("{receiver_id}",$user_id,$sms_msg);
    }
    if (strpos($sms_msg, '{cid}') !== false) {
        $sms_msg  = str_replace("{cid}",$args['cid'],$sms_msg);
    }
    if (strpos($sms_msg, '{aid}') !== false) {
        $sms_msg  = str_replace("{aid}",$args['aid'],$sms_msg);
    }
    bp_notifications_add_notification( array(
        'user_id'           =>  $user_id,
        'component_name'    => 'gamipress_buddyboss_notifications',
        'component_action'  =>  $portal_msg,
        'date_notified'     => bp_core_current_time(),
        'is_new'            => 1,
        'allow_duplicate'   => true,
    ));
    //email

    //$args['message'] = $args['message']?$args['message']: $email_msg;

    $args['send_date'] = date('Y-m-d h:i:s a', time());

    if(!empty(get_field($args['type'],'user_'.$user_id)) && in_array('sms',get_field($args['type'],'user_'.$user_id)) ){
        $phone = get_user_meta( $user_id, 'primary_phone', true );
        clean_phone($phone);
        twl_send_sms( array( 'number_to' => $phone , 'message' => $sms_msg ) );
    }

    if(!empty(get_field($args['type'],'user_'.$user_id)) && in_array('email',get_field($args['type'],'user_'.$user_id)) ){
        $body = bp_email_core_wp_get_template($email_msg);
        wp_mail($user->user_email  , "Notification" , $body);
    }

    $res = $wpdb->insert("notification_log", $args);
    if($res) return 'success';
    return false;
}



function register_my_custom_menu_page(){
    add_menu_page( 'Notifications Log','Notifications Log', 'manage_options', 'notification_log', 'my_custom_menu_page', 'dashicons-feedback', 80 );
}
function my_custom_menu_page(){
    include('notification_log.php');
}
add_action( 'admin_menu', 'register_my_custom_menu_page' );


function get_token_from_dev(){

    if($_SERVER['SERVER_NAME']  == 'portal.muslimeto.com') {
        $token_data = get_option('keap_access_token');
        return $token_data["token"];

    }else{
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
            )
        );
        $dataaa = wp_remote_request( 'https://portal.muslimeto.com/wp-json/wp/data_tokens', $args );
        $responseBody = wp_remote_retrieve_body( $dataaa );
        $result = json_decode( $responseBody );
        if(isset($result->data->keap_token))
            return $result->data->keap_token;
        return false;
    }

}

function get_zoom_token_from_portal(){
    if($_SERVER['SERVER_NAME']  == 'portal.muslimeto.com') {
        return get_option('zoom_access_token');
    }else{
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
            )
        );
        $dataaa = wp_remote_request( 'https://portal.muslimeto.com/wp-json/wp/data_tokens', $args );
        $responseBody = wp_remote_retrieve_body( $dataaa );
        $result = json_decode( $responseBody );
        if(isset($result->data->zoom_token))
            return $result->data->zoom_token;
        return false;
    }
}

function get_zoho_token_from_dev(){
    if($_SERVER['SERVER_NAME']  == 'portal.muslimeto.com') {
        return get_option('zoho_access_token');
    }else{
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
            )
        );
        $dataaa = wp_remote_request( 'https://portal.muslimeto.com/wp-json/wp/data_tokens', $args );
        $responseBody = wp_remote_retrieve_body( $dataaa );
        $result = json_decode( $responseBody );
        if(isset($result->data->zoho_token))
            return $result->data->zoho_token;
        return false;
    }

}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function user_tickets_count($user_id){
    $current_user = get_user_by('id', $user_id);

    if($current_user){
        $email = $current_user->user_email;
        $auth_token = 'Bearer ' . get_zoho_token_from_dev();
        $headers=array(
            "Authorization: $auth_token",
            "contentType: application/json; charset=utf-8",
        );
        $url='https://desk.zoho.com/api/v1/contacts/search?limit=1&email='.$email;
        $ch= curl_init($url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        $data = json_decode(curl_exec($ch));
        if(isset($data->data[0]->id)){
            $url2='https://desk.zoho.com/api/v1/contacts/'.$data->data[0]->id.'/tickets';
            curl_setopt($ch, CURLOPT_URL, $url2);
            $result2 = json_decode(curl_exec($ch));
        }
        curl_close($ch);
        if(isset($result2->data)){
            $over_due = count(array_column($result2->data, 'isOverDue')) ;
            $over = array('overdue'=> $over_due);
            $all_ticks = array_column($result2->data, 'status');
            $all = array_count_values($all_ticks);
            return array_merge($all, $over);
        }else {
            return false;
        }
    }else{
        return false;
    }
}

function get_user_happiness_rate($user_id){
    $current_user = get_user_by('id', $user_id);
    $email = $current_user->user_email;
    $auth_token = 'Bearer ' . get_zoho_token_from_dev();
    $headers=array(
        "Authorization: $auth_token",
        "contentType: application/json; charset=utf-8",
    );
    $url='https://desk.zoho.com/api/v1/contacts/search?limit=1&email='.$email;
    $ch= curl_init($url);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->data[0]->customerHappiness)){
        return $data->data[0]->customerHappiness ;
    }else {
        return false;
    }
}


function get_online_agents(){
    $auth_token = 'Bearer ' . get_zoho_token_from_dev();
    $headers=array(
        "Authorization: $auth_token",
        "contentType: application/json; charset=utf-8",
    );
    $url='https://desk.zoho.com/api/v1/onlineAgents?departmentId=-1&include=mailStatus,phoneStatus,chatStatus,phoneMode,presenceStatus' ;
    $ch= curl_init($url);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    $data = json_decode(curl_exec($ch));

    if(isset($data->data[0]->agentId)):
        foreach($data->data as $agents){
            $url2 = "https://desk.zoho.com/api/v1/agents/$agents->agentId" ;
            curl_setopt($ch, CURLOPT_URL, $url2);
            $data2[] = json_decode(curl_exec($ch));
        }
        $names['names'] =   array_column($data2, 'name') ;
        $names['details'] = $data->data;
        return  $names  ;
    else: return false;
    endif;
    curl_close($ch);
}

//get_ticket_file_url
function get_ticket_file_url(){
    $url =  $_POST['url'];
    $file_name = $_POST['name'];
    $auth_token = 'Bearer '. get_zoho_token_from_dev();
    $myfile = get_stylesheet_directory() . '/' . $file_name ;
    $headers=array(
        "Authorization: $auth_token"
    );
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER , TRUE);
    $data = curl_exec($ch) ;
    curl_close($ch);
    $fp = fopen($myfile ,"wb");
    fwrite($fp,$data);
    fclose($fp);
    echo get_stylesheet_directory_uri() .  '/' . $file_name ;
    exit;
}
add_action( 'wp_ajax_get_ticket_file_url', 'get_ticket_file_url' );
add_action( 'wp_ajax_nopriv_get_ticket_file_url', 'get_ticket_file_url' );


function last_successfull_pay_date($user_id){
    $ContactId  = wpf_get_contact_id($user_id);
    if(!$ContactId) return false;
    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/orders?limit=-1&contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch));
    curl_close($ch);
    if(isset($data->orders)):
        $orders = $data->orders;
        $dates = array_column($orders,  'status','order_date');
//  $dates2 = array_column($orders, 'order_date');
        $new = array_filter($dates, function ($var) {
            return ($var == 'PAID');
        });
        $dd = array_keys($new);
        return  $dd[0]  ;
    else: return false;
    endif;
}



//add new new_assignment
function add_new_assignment_wp(){
    $uid = get_current_user_id() ;
    $bb_group_id = $_POST['cid'];
    $user_info = get_userdata($uid);
    $course = get_page_by_path( 'practice', OBJECT, 'sfwd-courses' );
    $lesson = get_page_by_path( 'lets-practice', OBJECT, 'sfwd-lessons' );

    if(isset($lesson->ID)){$lid = $lesson->ID;}
    if(isset($course->ID)){$cid = $course->ID;}

    $my_post = array(
        'post_title'  => $user_info->first_name.' '.$user_info->last_name . ' - Practice on '.date('Y-m-d h:i:s') ,
        'post_type'   => 'sfwd-assignment',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    );

    $pid =  wp_insert_post( $my_post )  ;

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    $files = $_FILES["aud_file"];

    $file = array(
        'name' => rand(111111111111,9999999999999) . '_' . strtotime(date('Y-m-d H:i:s', time())) . '_' .$files['name']  ,
        'type' => $files['type'],
        'tmp_name' => $files['tmp_name'],
        'error' => $files['error'],
        'size' => $files['size']
    );
    $_FILES = array("upload_file" => $file);

    $attachment_idz =  media_handle_upload("upload_file", 0);
    $aws_url = get_post_meta( $attachment_idz, '_wp_nou_leopard_wom_s3_path' , true );
    // wp_delete_attachment( $attachment_idz, false );
    update_post_meta( $pid, 'lesson_id', intval( $lid ));
    update_post_meta( $pid, 'course_id', intval( $cid ));
    update_post_meta( $pid, 'practice_file', $aws_url);

    if(isset($bb_group_id)){
        $sp_entry_id = getBBgroupGFentryID($bb_group_id) ;
        $entry_meta = getGFentryMetaValue($sp_entry_id , 8);
        $staff_id = $entry_meta[0]->meta_value;
        $staff_wp_user_id = getStaffwp_user_id($staff_id);
        $message = 'Learner '.$user_info->first_name.' '.$user_info->last_name . ' just uploaded a new practice!' ;
    }
    if(isset($staff_wp_user_id)){
        send_notification_for_user( array(
            'sender_id'=>get_current_user_id(),
            'receiver_id'=> $staff_wp_user_id,
            'message'=> $message,
            "send_date" => bp_core_current_time()
        ));
    }

    $file_url = $aws_url ? $aws_url : wp_get_attachment_url( $attachment_idz );
    groups_post_update(array(
        'group_id'=> $bb_group_id,
        'content' => '<div class="aud_d_w"><strong>'.$user_info->first_name.' '.$user_info->last_name . ' - Practice on '.date('Y-m-d h:i:s') .'</strong><br><br><a class="c_t_audio" href="'.$file_url.'">Click here to listen to practice.</a><br></div>' ,
        'user_id' => get_current_user_id(),
    ));


    wp_send_json_success($attachment_idz);
    exit;
}
add_action( 'wp_ajax_add_new_assignment_wp', 'add_new_assignment_wp' );
add_action( 'wp_ajax_nopriv_add_new_assignment_wp', 'add_new_assignment_wp' );


add_action( 'wp_loaded', 'myprefix_sendform', 15 );
function myprefix_sendform() {

    if(isset($_POST['Message_announce'])):

        $message = $_POST['Message_announce'] ? $_POST['Message_announce'] : 'Add text to be sent.';

        if(isset($_POST['test_mode'])){
            $c_user = wp_get_current_user();
//test email
            if( in_array('email',$_POST['type']) && isset($c_user->user_email) ){
                $sub = $_POST['subject'] ? $_POST['subject'] : "Test subject" ;
                $body = bp_email_core_wp_get_template($message);
                wp_mail($c_user->user_email , $sub , $body);
            }
//test sms
            if(in_array('sms',$_POST['type']) && isset($phone)){
                $phone = get_user_meta($c_user->ID, 'primary_phone', true );
                $phones  = clean_phone($phone);
                twl_send_sms( array( 'number_to' => $phone , 'message' => $message ) );
            }


            if(in_array('portal',$_POST['type'])){
                bp_notifications_add_notification( array(
                    'user_id'           => $c_user->ID,
                    'component_name'    => 'gamipress_buddyboss_notifications',
                    'item_id'           => 1878,
                    'component_action'  => $message,
                    'date_notified'     => bp_core_current_time(),
                    'is_new'            => 1,
                    'allow_duplicate'   => true,
                ));
            }

            $_POST['test-txt'] = 'Announcement sent successfully in test mode.';

            //live modeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee

        }else{

            if(isset($_POST['send_to'])){
                if(in_array('staff' , $_POST['send_to'])){
                    $staff =  array('administrator','teamleader');
                    $roles = array_merge( $staff,$_POST[ 'send_to' ]) ;
                }else{
                    $roles = $_POST['send_to'] ;
                }

                if (($key = array_search('staff', $roles)) !== false) {
                    unset($roles[$key]);
                }

                $users = get_users(['role__in' => $roles ]);
                $users_ids = array_column($users, 'ID');
            }

            if(isset($_POST['sub_prnt'])){
                foreach ( $_POST['sub_prnt'] as  $s_id ) {
                    if(!empty(wpf_get_users_with_tag( $s_id ))){
                        $arraysMerged = array_merge([], wpf_get_users_with_tag( $s_id ) );
                    }
                }
            }

            if(!empty($arraysMerged) && !empty($users_ids)){
                $all_users = array_merge($arraysMerged ,$users_ids);
            }elseif( empty($arraysMerged) && !empty($users_ids)){
                $all_users =  $users_ids ;
            }elseif(!empty($arraysMerged) &&  empty($users_ids)){
                $all_users =  $arraysMerged ;
            }
            $all_userzz = array_unique($all_users);

            //send sms
            if(in_array('sms',$_POST['type'])){
                $all_phone = get_all_userphones() ;
                foreach ($all_phones as $phone) {
                    twl_send_sms( array( 'number_to' => $phone , 'message' => $message ) );
                }
            }

            //send email
            if(in_array('email',$_POST['type'])){
                $sub = $_POST['subject'];
                $body = bp_email_core_wp_get_template($message);
                foreach ($all_userzz as $id) {
                    $user_info = get_userdata($id);
                    wp_mail($user_info->user_email , $sub , $body);
                }
            }
            //send portal
            if(in_array('portal',$_POST['type'])){
                foreach ( $all_userzz as $id) {
                    bp_notifications_add_notification( array(
                        'user_id'           => $id,
                        'component_name'    => 'gamipress_buddyboss_notifications',
                        'item_id'           => 1878,
                        'component_action'  => $message,
                        'date_notified'     => bp_core_current_time(),
                        'is_new'            => 1,
                        'allow_duplicate'   => true,
                    ));
                }
            }

            $_POST['success'] = 'Announcement sent successfully.';

        }

    endif;
}

//refresh acccess-token infusionsoft refresh acccess-token infusionsoftrefresh acccess-token infusionsoft


if ( ! wp_next_scheduled( 'muslimeto_refresh_keap_token' ) ) {
    wp_schedule_event(time(), 'hourly', 'muslimeto_refresh_keap_token');
}

add_action( 'muslimeto_refresh_keap_token', 'refresh_keap_token_func' );
function refresh_keap_token_func() {
    if(site_url() !== 'https://portal.muslimeto.com') {return;}

    $current_date_object = new DateTime('now', new DateTimeZone('UTC'));
    $today_date = $current_date_object->format('Y-m-d H:i:s');

    $old_date = get_option('date_token_confirm');

    $refresh = get_option('keap_refresh_token');
    $token = get_option('keap_access_token');

    $t1 = strtotime( $today_date );
    $t2 = strtotime( $token['refresh_time'] );
    $totalSecondsDiff = abs($t1-$t2);
    $totalHoursDiff   = $totalSecondsDiff/60/60;

    if( round($totalHoursDiff) >= 20 ){
        $apiUrl = 'https://api.infusionsoft.com/token';
        $apiResponse = wp_remote_post( $apiUrl, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode(   'unHsVg1NVIemYTA45mihkzn18SIGv6rP:JUx8ucLjnGhJjtDg' ),
                'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
            ),
            'body' => 'grant_type=refresh_token&refresh_token='.$refresh ,
        ));
        $data = json_decode( wp_remote_retrieve_body( $apiResponse ) );
        if(isset($data->refresh_token)) :
            $token_data = array('token' => $data->access_token ,'refresh_time' => $today_date )  ;
            update_option( 'keap_refresh_token', $data->refresh_token );
            update_option( 'keap_access_token',  $token_data );
            wp_mail('himamohamed1991@gmail.com','token refresh',$data->refresh_token);
        else :
            wp_mail('himamohamed1991@gmail.com','token','no keap token !');
        endif;

    }else{
        wp_mail('himamohamed1991@gmail.com','token','not yet time = '.$totalHoursDiff);
    }

}



// refresh zoho token refresh zoho tokenrefresh zoho tokenrefresh zoho token

add_filter( 'cron_schedules', 'cron_every_one_hour_for_zoho' );
function cron_every_one_hour_for_zoho( $schedules ) {
    $schedules['every_10_minutes'] = array(
        'interval'  => 300,
        'display'   => __( 'Every 10 min', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'cron_every_one_hour_for_zoho' ) ) {
    wp_schedule_event( time(), 'every_10_minutes', 'cron_every_one_hour_for_zoho' );
}

add_action( 'cron_every_one_hour_for_zoho', 'refresh_zoho_token_func' );


function refresh_zoho_token_func() {
    if(site_url() !== 'https://portal.muslimeto.com') {return;}

    $apiUrl = 'https://accounts.zoho.com/oauth/v2/token?refresh_token=1000.42459b5c4443518e1411b19050f40c17.8e6dfb0e011ce4907ce8fbf60b40a00a&client_id=1000.L94YY4Z60L0ZVLBJYLYSSKQ8L6YD7Q&client_secret=de87cff3567dbdb33e7f350c599c5f1063e8c92511&scope=Desk.tickets.ALL,Desk.contacts.READ,Desk.contacts.WRITE,Desk.search.READ,Desk.basic.READ,Desk.tickets.CREATE,Desk.basic.CREATE,Desk.settings.READ,Desk.events.ALL&redirect_uri=https://mslmcomdev.wpengine.com/&grant_type=refresh_token';
    $apiResponse = wp_remote_post( $apiUrl,
        [
            'method'    => 'POST',
            'headers'   => [
                'content-type' => 'application/json',
            ],
        ]
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    if(isset($apiBody->access_token))
    {
        update_option('zoho_access_token',$apiBody->access_token);
        update_option('zoho_refresh_token', $apiBody->refresh_token);
    }else{
        //  wp_mail('himamohamed1991@gmail.com','zoho_refresh_token','failed');
    }
}

//update token from dev
function muslimeto_update_token_init(){
    $namespace = 'wp';
    $route = 'update_tokens';
    register_rest_route($namespace, $route, array(
        'methods' => 'POST',
        'callback' => 'muslimeto_update_token'
    ));
}

add_action('rest_api_init', 'muslimeto_update_token_init');
function muslimeto_update_token($req){

    $type = $req['token_type'] ;
    if($type === 'zoho' || $type === 'keap'){
        update_option($type.'_access_token', $req[$type.'_access']);
        update_option($type.'_refresh_token', $req[$type.'_refresh']);
        $success = "success";
    }
    return $success ? $success : 'Failed';
}


require_once WP_CONTENT_DIR . '/muslimeto-background-processing/wp-async-request.php'  ;
require_once WP_CONTENT_DIR . '/muslimeto-background-processing/wp-background-process.php';

class BackgroundAsyncTags extends Mus_Background_Process{
    protected $action = 'sync_tags';
    protected function task( $item ){
        global $wpdb;
        update_keap_tags_for_parent($item);
        $updateUser = updateUserBillingIndicator($item, '');
        if( $updateUser  !== true ):
            $cron_log_table = $wpdb->prefix . 'muslimeto_error_log';
            $cron_log_data = array(
                array(
                    'event_title' => 'cron_muslimeto_updateUserBillingIndicator_daily',
                    'event_desc' => 'user_id: ' . $item . ' - error: ' .$updateUser
                ),
            );
            wpdb_bulk_insert($cron_log_table, $cron_log_data);
        endif;
        return false;
    }
}

class BackgroundTast{
    public function __construct() {
        $this->task = new BackgroundAsyncTags;
        add_action( 'wp_ajax_get_data_from_api'  ,  [$this, 'get_data_from_api']  );
        add_action( 'wp_ajax_nopriv_get_data_from_api' , [$this, 'get_data_from_api'] );
    }
    public function get_data_from_api(){
        if(isset($_POST['sync_tags']) && isset($_POST['start']) == "YES"){

            $user_query = new WP_User_Query( array(
                'role'    => 'parent',
                'orderby' => 'ID',
                'order' => 'ASC',
            ));
            $parents = $user_query->get_results();
            $parents_ids = array_column($parents, 'id');
            foreach ( $parents_ids as $parents_id ) {
                $this->task->push_to_queue( $parents_id );
            }
            $this->task->save()->dispatch();
        }
        // wp_mail('himamohamed1991@gmail.com','keap tag started','keap tag');
        exit;
    }
}

add_action('plugins_loaded', function() {
    new BackgroundTast();
});


//// test post via background task queue

add_filter( 'cron_schedules', 'muslimeto_background_task_que' );
function muslimeto_background_task_que( $schedules ) {
    $schedules['every_24_hours'] = array(
        'interval'  => 86400,
        'display'   => __( 'Every 24 hours', 'textdomain' )
    );
    return $schedules;
}
if ( ! wp_next_scheduled( 'muslimeto_background_task_que' ) ) {
    wp_schedule_event( time(), 'every_24_hours', 'muslimeto_background_task_que' );
}

add_action( 'muslimeto_background_task_que', 'post_via_background_task_func' );

function post_via_background_task_func(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $apiUrl = admin_url('admin-ajax.php?action=get_data_from_api');
    wp_remote_post( $apiUrl, array(
        'body'        => array(
            'sync_tags' => 1,
            'start'=>'YES'
        ),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
        ),
    ) );
}


function update_keap_tags_for_parent($parents_id) {
    $ContactId  = wpf_get_contact_id( $parents_id, true );

    if( !$ContactId ) {return;}

    $token = get_token_from_dev();
    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token;
    $options = [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL            => "https://api.infusionsoft.com/crm/rest/v1/subscriptions?contact_id=".$ContactId
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = json_decode(curl_exec($ch)); // all subs
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($httpCode == "200" ){
        //filter 380 tag add Unknown users 388

        // Cleanup all tags Active Subscription, Trialing, All Cancelled, and the 4 RC tags
        //remove 424 because the user is found in db
        wp_fusion()->user->remove_tags( array(424,380,490,488,256,362,462,456,458,460,258), $parents_id );


        $multiCurl = array();
        $result = array();
        $never_signedup=array();
        $trailings= array();
        $stages = ['30','22','24','26','20'];
        $authorization = "Authorization: Bearer ".get_token_from_dev();
        $mh = curl_multi_init();
        for ($i=0; $i < count($stages); $i++){
            $fetchURL = 'https://api.infusionsoft.com/crm/rest/v1/opportunities?stage_id='.$stages[$i];
            $multiCurl[$i] = curl_init();
            curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($multiCurl[$i], CURLOPT_URL,$fetchURL);
            curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);
            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }
        $index=null;
        do {
            curl_multi_exec($mh,$index);
            curl_multi_select($mh);
        } while($index > 0);
        foreach($multiCurl as $k => $ch) {
            $result[$k] = json_decode(curl_multi_getcontent($ch));
            if(isset($result[$k]->opportunities)){
                if($k < 4){
                    $contacts = array_column( array_column($result[$k]->opportunities,'contact'), 'id');
                    array_push($trailings,...$contacts);
                }elseif($k == 4){
                    $never_signedup = array_column( array_column($result[$k]->opportunities,'contact'), 'id');
                }

            }
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);


        for ($i=0; $i < count($trailings); $i++){
            $fetchURL = 'https://api.infusionsoft.com/crm/rest/v1/contacts/' . $trailings[$i] ;
            $multiCurl[$i] = curl_init();
            curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($multiCurl[$i], CURLOPT_URL,$fetchURL);
            curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);
            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }
        $index=null;
        do {
            curl_multi_exec($mh,$index);
            curl_multi_select($mh);
        } while($index > 0);

        foreach($multiCurl as $k => $ch) {
            $result[$k] = json_decode(curl_multi_getcontent($ch));
            if(isset($result[$k]->email_addresses[0]->email)){
                if(!email_exists($result[$k]->email_addresses[0]->email)) {

                    $rand_pass= rand(1111111,9999999);
                    $userdata = array(
                        'user_login' => $result[$k]->email_addresses[0]->email,
                        'first_name' => $result[$k]->given_name,
                        'last_name' =>  $result[$k]->family_name,
                        'user_email' => $result[$k]->email_addresses[0]->email,
                        'role' => 'parent',
                        'user_pass'  =>  wp_hash_password($rand_pass) // no plain password here!
                    );
                    $user_id = wp_insert_user( $userdata ) ;
                    if ( ! is_wp_error( $user_id ) ){
                        $new_code = 'prnt-' . rand(111111111,9999999999) . '_uid_' . $user_id;
                        add_user_meta($user_id ,'account_code', $new_code);
                        add_user_meta($user_id ,'primary_phone', $result[$k]->phone_numbers[0]->number);
                        add_user_meta($user_id ,'street_address_1', $result[$k]->addresses[0]->line1);
                        add_user_meta($user_id ,'street_address_2', $result[$k]->addresses[0]->line2);
                        add_user_meta($user_id ,'contact_type', $result[$k]->contact_type);
                        add_user_meta($user_id ,'postal_code', $result[$k]->addresses[0]->postal_code);
                        add_user_meta($user_id, 'pw_string', $rand_pass);
                    }
                }

            }
            curl_multi_remove_handle($mh, $ch);
        }

        if(in_array( $ContactId, $trailings)){
            add_tag_for_auser( 380 , $ContactId);
            wp_fusion()->user->apply_tags( array(380), $parents_id );
        }else{
            remove_tag_for_auser( 380 , $ContactId);
            wp_fusion()->user->remove_tags( array(380), $parents_id );
        }

        //  488 unkoonwn users
        //  490 never_signedup user
        $parent_stats =  get_parent_stats_from_keap($parents_id);

        $due = $parent_stats['balance_due'];
        if( !is_bool($due) && ( !empty($due) || $due == 0 ) ) {
            if( $due > 0 ){
                wp_fusion()->user->apply_tags( array(258), $parents_id );
                add_tag_for_auser( 258 , $ContactId);
            }
            else{
                wp_fusion()->user->remove_tags( array(258), $parents_id );
                remove_tag_for_auser( 258 , $ContactId);
            }
        }

        // Check for subscriptions
        if( isset($data->subscriptions) && !empty($data->subscriptions) ){

            $has_any_sub=false;

            foreach ($data->subscriptions as $value) {
                if($value->active){$has_any_sub = true;}
            }
            if($has_any_sub){ // Active subscription found
                wp_fusion()->user->apply_tags( array(256), $parents_id );  // add active subscriptions tag
                add_tag_for_auser( 256 , $ContactId);
            }else{ // No active subscription found
                wp_fusion()->user->apply_tags( array(362), $parents_id ); // add All Cancalled tag
                add_tag_for_auser( 362 , $ContactId);
                if ($parent_stats['get_last_payment_on'])
                {
                    $date1=date_create($parent_stats['get_last_payment_on']);
                    $date2=date_create(date('Y-m-d'));
                    $diff=date_diff($date1,$date2);
                    $diff_date = $diff->format("%R%a") ;
                    if($diff_date < 30 && $diff_date >= 0 ){
                        wp_fusion()->user->apply_tags( array(456), $parents_id ); //Add RC (456)
                        add_tag_for_auser( 456 , $ContactId);
                    }elseif($diff_date >= 30 && $diff_date < 60){
                        wp_fusion()->user->apply_tags( array(458), $parents_id ); //Add RC 30 (458)
                        add_tag_for_auser( 458 , $ContactId);
                    }elseif($diff_date >= 60 && $diff_date < 90){
                        wp_fusion()->user->apply_tags( array(460), $parents_id ); //Add RC 60 (460)
                        add_tag_for_auser( 460 , $ContactId);
                    }elseif($diff_date >= 90 && $diff_date < 120){
                        wp_fusion()->user->apply_tags( array(462), $parents_id ); //Add RC 90  (462)
                        add_tag_for_auser( 462 , $ContactId);
                    }
                }
            }
        }else{  // No subscriptions found
            wp_fusion()->user->remove_tags( array(256), $parents_id ); // remove active sub tag
            remove_tag_for_auser( 256 , $ContactId);
            if( !wpf_has_tag( 380 , $parents_id ) && !in_array( $ContactId, $never_signedup )){
                wp_fusion()->user->apply_tags( array(488), $parents_id ); //add unknonwn user tag
                add_tag_for_auser( 488 , $ContactId);
            }elseif( !wpf_has_tag( 380 , $parents_id ) &&  in_array( $ContactId, $never_signedup) ){
                wp_fusion()->user->apply_tags( array(490), $parents_id ); //add never_signedup tag
                add_tag_for_auser( 490 , $ContactId);
            }

        }
    }else{   // add notification for connection error  ---> Keap Connection Error

    }
}



function reg_page_shortcode() {
    ob_start();
    get_template_part('template-parts/template-register-page');
    return ob_get_clean();
}
add_shortcode( 'reg_page_shortcode', 'reg_page_shortcode' );


function attendance_page_shortcode() {
    ob_start();
    get_template_part('template-parts/teacher-attendance');
    return ob_get_clean();
}
add_shortcode( 'attendance_page_shortcode', 'attendance_page_shortcode' );

function Teacher_Assessment_feedback() {
    ob_start();
    get_template_part('template-parts/feedback/feedback');
    return ob_get_clean();
}
add_shortcode( 'Teacher_Assessment_feedback', 'Teacher_Assessment_feedback' );


function check_first_name(){
    $u_ids = getParentChilds($_POST['prnt_id']);
    if(!empty($u_ids)){
        $fname=array();
        foreach ($u_ids as $u_id) {
            $ii = get_userdata($u_id);
            $fname[]= $ii->first_name;
        }
    }
    if(!empty($fname)){
        foreach ($fname as $fnam) {
            if(substr(strtolower($fnam), 0, 3) === strtolower($_POST['first_name'])){
                echo $fnam;
                break;
            }
        }
    }
    exit;
}
add_action( 'wp_ajax_check_first_name', 'check_first_name' );
add_action( 'wp_ajax_nopriv_check_first_name', 'check_first_name' );



function get_user_by_fn(){

    $users = get_users(
        array(
            'role' => $_POST['role'],
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name' ,
                    'value' => $_POST['first_name'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'last_name' ,
                    'value' => $_POST['first_name'],
                    'compare' => 'LIKE'
                )
            ),
            //'number' => 5
        )
    );
    if(!empty($users)){
        if($_POST['role'] == "parent"){
            $name = 'name="prnt_id"';
        }else{
            $name = 'name="learner_id"';
        }
        echo "<select $name>";
        echo '<option>Choose an account...</option>';
        foreach ($users as $user) {
            $memb_ids = getParentChilds($user->ID);
            if (($key = array_search($user->ID, $memb_ids)) !== false) {
                unset($memb_ids[$key]);
            }
            $fnames=[];
            foreach ($memb_ids as $u_id) {
                $ii = get_userdata($u_id);
                $fnames[] = $ii->display_name ;
            }

            if($_POST['role'] == "parent")
            {
                if(!empty($memb_ids)){
                    $memb_txt = "Current Memebers: ". implode(" || ",$fnames);
                    echo '<option value="'.$user->ID.'">' . $user->display_name . " | ". $user->user_email . ' ... ' . $memb_txt.'</option>';
                }else{
                    echo '<option value="'.$user->ID.'">' . $user->display_name . " | ". $user->user_email . '</option>';
                }
            }
            else{
                $ll_name = getCustomerFullName($user->ID)?getCustomerFullName($user->ID):$user->display_name;
                echo '<option value="'.$user->ID.'">' .$ll_name .'</option>';
            }
        }
        echo '</select>';

    }else{
        echo '<select><option>No account found !</option></select>';
    }

    exit;
}
add_action( 'wp_ajax_get_user_by_fn', 'get_user_by_fn' );
add_action( 'wp_ajax_nopriv_get_user_by_fn', 'get_user_by_fn' );


// function to get total hrs for teacher monthly
function get_teacher_hour_with_learner2($staff_id, $start, $end){
    global $wpdb;
    $cust =  getcustomerID($learner_id);
    if(!$cust){return;}
    $res = $wpdb->get_results("SELECT zrsap_bookly_customer_appointments.appointment_id, zrsap_bookly_customer_appointments.customer_id, zrsap_bookly_customer_appointments.custom_fields,zrsap_bookly_customer_appointments.status , zrsap_bookly_appointments.staff_id, zrsap_bookly_appointments.start_date, zrsap_bookly_appointments.end_date FROM zrsap_bookly_customer_appointments INNER JOIN zrsap_bookly_appointments ON zrsap_bookly_customer_appointments.appointment_id = zrsap_bookly_appointments.id HAVING zrsap_bookly_appointments.staff_id = 369 AND zrsap_bookly_appointments.start_date >= $start AND zrsap_bookly_appointments.end_date <= $end ORDER BY zrsap_bookly_appointments.start_date ASC");
    $wpdb->flush();

    $bb_custom_field_id = (int) redux_global_var()['bookly_custom_field_bb_gid'];
    $total = 0;
    if( !empty($res) ):


        $stored_bb_group_custom_field = json_decode($res[0]->custom_fields);
        foreach ( $stored_bb_group_custom_field as $field_data ):
            $custom_field_id = (int) $field_data->id;
            if( $custom_field_id->id === $bb_custom_field_id ):
                $bb_group_id = $field_data->value;
            endif;
            if( $field_data->id === 2583 ): // actual mins value
                $stored_actual_min = $field_data->value;
                $stored_actual_minarr[$attendance_status][] = $field_data->value;
            endif;
            if( $field_data->id === 95778 ): // late mins value
                $stored_late_mins = $field_data->value;
            endif;
        endforeach;

        $total = array();
        if( $attendance_status === 'attended' || $attendance_status === 'attended-tl' || $attendance_status === 'no-show-s' || $attendance_status === 'holiday' ||  $attendance_status === 'attended-sl' ):
            $total = array('time'   => $stored_actual_min,
                'status' => $attendance_status
            );
        endif;    // type attent

    endif;
    return $total;
}


function filter_teacher_classes($teacher_id , $start , $end){
    $teacher_id = $teacher_id?$teacher_id:get_current_user_id();
    $staff_id   = getStaffId( $teacher_id );
    $classes = getStaffBBGroups($staff_id);
    $new_clases = [];
    $filter_classes = [];
    foreach ($classes as $key => $value) {
        $types = ["one-on-one","one-to-one","family-group"] ;

        if(in_array(getBBgroupType($value), $types)){
            $sp_entry_id = getBBgroupGFentryID($value);
            $schedules_entries = getScheduleEntryID($sp_entry_id);

            foreach ( (array)$schedules_entries as $schedules_entry_id ): // filter classes by period
                $end_date_meta = getGFentryMetaValue($schedules_entry_id, 8);
                $start_date_meta = getGFentryMetaValue($schedules_entry_id, 9);

                if(isset($end_date_meta[0]->meta_value)){
                    $end_date = $end_date_meta[0]->meta_value;
                }else{
                    $end_date = false;
                }
                if(isset($start_date_meta[0]->meta_value)){
                    $start_date = $start_date_meta[0]->meta_value;
                }else{
                    $start_date = false;
                }
                $schedule_end_date = date('Y-m-d', strtotime($end_date));
                $schedule_start_date = date('Y-m-d', strtotime($start_date));
                $Period_Begin = date('Y-m-d', strtotime($start));
                $Period_End = date('Y-m-d', strtotime($end));
                if ( ($schedule_start_date < $Period_End) &&
                    ( ! $end_date  ||  $schedule_end_date > $Period_Begin) ){
                    $filter_classes[]=$value;
                }
            endforeach;
        }
    }
    return array_unique($filter_classes) ;
}

function load_teacher_attd_table(){
  global $wpdb;
  $date = $_POST['month'];
  $newdate =  date('Y-m-d',strtotime($date.' -1 months'));
  $st =  date('Y-m-d', strtotime($newdate. '+ 21 days'));
  $en =  date('Y-m-d', strtotime("+1 months", strtotime($st)));
  $teacher_id = $_POST['teacher'];
  $teacher_info = get_userdata($teacher_id);
  $all_childs = [];
  $c_data=[];
  $staff_id = getStaffId( $teacher_id );
  $res = $wpdb->get_results("SELECT zrsap_bookly_customer_appointments.appointment_id, zrsap_bookly_customer_appointments.customer_id, zrsap_bookly_customer_appointments.custom_fields,zrsap_bookly_customer_appointments.status , zrsap_bookly_appointments.staff_id, zrsap_bookly_appointments.start_date, zrsap_bookly_appointments.end_date FROM zrsap_bookly_customer_appointments INNER JOIN zrsap_bookly_appointments ON zrsap_bookly_customer_appointments.appointment_id = zrsap_bookly_appointments.id HAVING zrsap_bookly_appointments.staff_id = $staff_id AND zrsap_bookly_appointments.start_date >= '{$st}' AND zrsap_bookly_appointments.end_date <= '{$en}' ORDER BY zrsap_bookly_appointments.start_date ASC");


  $all_childs = array_unique(array_column( $res, 'customer_id' ));

  foreach ($res as $val){
    if(in_array($val->customer_id , $all_childs)
    && ($val->status === "attended" ||  $val->status === "holiday" ||  $val->status === "attended-tl" ||  $val->status === "no-show-s" || $val->status === "attended-sl" )){
        $c_data[$val->customer_id][] = $val;
    }
  }
  $all_childs = array_keys($c_data);  if(!empty($all_childs)):  ?>
  <table class="table table-bordered table-responsive attendence-table">
    <thead>
      <tr class="parent_tr">
        <td>##</td>
        <?php if( $all_childs ): foreach ($all_childs as $val) : $child = get_userdata($val);
            $prnt_id = getParentID($val); $pnrt_info = get_userdata($prnt_id);?>
            <td colspan="" data-balloon-pos="down" data-balloon="<?php echo $pnrt_info->display_name?$pnrt_info->display_name:$pnrt_info->ID  ?>" class="parent_td">
            <span><?php echo $pnrt_info->display_name?$pnrt_info->display_name:$pnrt_info->ID  ?></span>
            </td>
             <?php endforeach;endif; ?>
       </tr>
      <tr>
        <th></th>
      <?php if( $all_childs ): foreach ($all_childs as $val) : $child = get_userdata($val); ?>
           <th scope="col"><span><?php echo $child->first_name?$child->first_name:$val; ?></span></th>
         <?php endforeach;endif; ?>
           <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $begin = new DateTime($st);
      $end = new DateTime($en);
      $interval = DateInterval::createFromDateString('1 day');
      $period = new DatePeriod($begin, $interval, $end);
      $x=0;foreach ($period as $dt) : $total=0;$att_hours=0;$total_ijaza=0; ?>
              <tr>
               <td><span><?php echo $dt->format("d") .'</span>-<span>'. $dt->format("M") .'</span>'; ?></td>
              <?php if(!empty($c_data)): foreach ($all_childs as $value):
                    $child = getCustomerFullName($value);
                    echo '<td><div class="popover_td_attendance">';
                    $t=1;$o_time = 0; $statues=" ";
                    foreach ($c_data[$value] as $nval):
                    $class = json_decode($nval->custom_fields);
                    $total_inx = array_search(2583, array_column($class, 'id'));
                    $group_name = bp_get_group_name($class[0]->value);
                    $c_Date = new DateTime($nval->start_date);
                    if( $dt->format("Y-m-d") == $c_Date->format("Y-m-d") ):
                      $sp_entry_id = getBBgroupGFentryID($class[0]->value);
                      $bookly_service_id = getBooklyServiceId($sp_entry_id);
                      $minutes = $class[$total_inx]->value; $total+=$minutes;
                      $o_time+=$minutes; $statues .= " " . $nval->status;
                      if($bookly_service_id == 18){$total_ijaza+=$minutes;}?>
                     <section>
                       <p class="class_status"><span class="status-<?php echo $nval->status;?>"></span></p>
                        <div class='class_time_Attendance'>
                             <p><span class="sec-title"><i class='bb-icon-calendar bb-icon-l'></i>Date:</span><?php echo $dt->format("Y-m-d") ; ?></p>
                             <p data-status='<?php echo $nval->status;?>' class='attendance_details'>
                               <span><span class="sec-title"><i class='bb-icon-checkbox bb-icon-l'></i>Status:</span><?php echo $nval->status;?></span>
                             </p>
                             <p class="class_mins"><span class="sec-title"><i class='bb-icon-user-clock bb-icon-l'></i>Class Time:</span><?php echo $minutes ?></p>
                         </div>
                       <div class='class_info_Attendance'>
                         <h3><span class="sec-title"><i class='bb-icon-user-card bb-icon-l'></i>CID:</span><?php echo $group_name?$group_name:$class[0]->value; ?></h3>
                             <p>
                               <span><span class="sec-title"><i class='bb-icon-user bb-icon-l'></i>Teacher</span><?php echo $teacher_info->display_name ?></span>
                               <span>
                                <ul>
                                  <li><span class="sec-title"><i class='bb-icon-users bb-icon-l'></i>Learners:</span><?php echo $child ?></li>
                                </ul>
                               </span>
                             </p>
                        </div>
                     </section>
                  <?php $t++; endif;endforeach; echo '</div>';if($o_time):?>
                    <p class="<?php echo $statues ?>"> <span> <?php echo $o_time ; ?> </span> </p>
                  <?php endif; echo '</td>'; endforeach; endif; ?>
                  <td><?php echo $total ?></td>
                </tr>
         <?php $x++;  $att_hours+=$total;  endforeach;  ?>
             <tr>
               <td>Total</td>
               <?php $tot_hours=0; foreach ($all_childs as $all_child) :$times=0; echo '<td>';
                      foreach($c_data[$all_child] as $nval):
                    $datetime1 = strtotime($nval->start_date);
                    $datetime2 = strtotime($nval->end_date);
                    $interval  = abs($datetime2 - $datetime1);
                    $minutes   = round($interval / 60); $times += $minutes; ?>
              <?php endforeach; echo $times . '</td>'; $tot_hours+=$times; endforeach;  ?>
              <td class="tt_hours" style="background-color:green!important"><?php echo $tot_hours/60 .' h'; ?></td>
             </tr>
    </tbody>
  </table>
  <input type="hidden" class="hours_inp" value="<?php echo ($tot_hours/60) . ' h'?>">
  <input type="hidden" class="ijaza_inp" value="<?php echo ($total_ijaza/60) . ' h'?>">
  <input type="hidden" class="total_inp" value="<?php echo ($tot_hours-$total_ijaza) / 60 . ' h'; ?>">
<?php else: echo '<h3 class="p-5 text-center">No attendance data found for this month.</h3>';
endif;
exit;
}
add_action('wp_ajax_nopriv_load_teacher_attd_table', 'load_teacher_attd_table');
add_action('wp_ajax_load_teacher_attd_table', 'load_teacher_attd_table');

//start  Appointment queue
class BackgroundClassAppointment extends Mus_Background_Process{
    protected $action = 'sync_appointment';
    protected function task( $item ) {
        regenerateAppointmentsFor2Months($item);
        return false;
    }
}

class BackgroundAppointment{
    public function __construct() {
        $this->task = new BackgroundClassAppointment;
        add_action( 'wp_ajax_send_cid_for_update'  ,  [$this, 'send_cid_for_update']  );
        add_action( 'wp_ajax_nopriv_send_cid_for_update' , [$this, 'send_cid_for_update'] );
    }
    public function send_cid_for_update(){
        if( isset($_POST['sync_cids']) &&  isset($_POST['starting']) ){
            global $wpdb;
            $table_name =  $wpdb->prefix . "bp_groups";
            $retrieve_data = $wpdb->get_results( "SELECT id FROM $table_name" );
            foreach( $retrieve_data as $val ){
                $this->task->push_to_queue( $val->id );
            }
            $this->task->save()->dispatch();
        }
        //wp_mail('himamohamed1991@gmail.com','cid appoint started','cid appoint');
        //exit;
    }
}

add_action('plugins_loaded', function() {
    new BackgroundAppointment();
});


//// test post via background task queue

// Custom Cron Recurrences
function muslimeto_cron_job_appointment( $schedules ) {
    $schedules['every_week'] = array(
        'display' => __( 'Once weekly', 'textdomain' ),
        'interval' => 604800,
    );
    return $schedules;
}

add_filter( 'cron_schedules', 'muslimeto_cron_job_appointment' );
if (!wp_next_scheduled( 'muslimeto_cron_job_appointment' )){
    wp_schedule_event(time(), 'every_week', 'muslimeto_cron_job_appointment');
}

add_action( 'muslimeto_cron_job_appointment', 'job_appointment_func' );

function job_appointment_func(){
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $apiUrl = admin_url('admin-ajax.php?action=send_cid_for_update');
    wp_remote_post( $apiUrl, array(
        'body'        => array(
            'sync_cids' => 1,
            'starting'=>'YES'
        ),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
        ),
    ));
}


function zoom_webhook_endpoint(){

    $entries =  file_get_contents('php://input');
    $entries_d = json_decode($entries,true);
    $event = $entries_d['event'];

    if($event == "recording.completed" ){

        global $wpdb;


        $recordings = $entries_d['payload']['object']['recording_files'] ;

        foreach ($recordings as $recording){

            if($recording['file_type'] == 'MP4' || $recording['file_type'] == 'M4A'){


                $rec_size = ((int)$recording['file_size'])/1024/1024;

                $tracking_fields = get_meeting_tracking_fields( $entries_d['payload']['object']['id'] );

                if( $tracking_fields && isset($tracking_fields[1]->value) ){
                    $class_id = $tracking_fields[0]->value ;
                    $class_type = $tracking_fields[1]->value ;
                }

                $args = array(
                    "mtg_id" => $entries_d['payload']['object']['id'],
                    "mtg_uuid" => $entries_d['payload']['object']['uuid'],
                    "mtg_host_email" => $entries_d['payload']['object']['host_email'],
                    "mtg_class_type" => $class_type ? $class_type : '',
                    "mtg_class_cid" => $class_id ? $class_id : '',
                    "rec_id" => $recording['id'],
                    "rec_type" => $recording['recording_type'],
                    "rec_start" => $recording['recording_start'],
                    "rec_end" => $recording['recording_end'],
                    "rec_passcode" => $entries_d['payload']['object']['password'],
                    'rec_download_token' => $entries_d['download_token'],
                    "rec_url" => $recording['download_url'],
                    "rec_file_name" => '',
                    "rec_file_size" => round( $rec_size , 2),
                    "rec_file_type" => $recording['file_type'],
                    "mtg_class_aip" => '',
                    "rec_aws_url" => '',
                    "rec_aws_file_size" => '',
                );
                $row = $wpdb->insert("msl_recordings", $args);


            }
        }
        // wp_mail('himamohamed1991@gmail.com', 'zoom wbehook', $entries);
    }
    exit;
}
add_action('wp_ajax_nopriv_zoom_webhook_endpoint', 'zoom_webhook_endpoint');
add_action('wp_ajax_zoom_webhook_endpoint', 'zoom_webhook_endpoint');

function get_meeting_tracking_fields($mtg_id){
    $apiUrl = 'https://api.zoom.us/v2/meetings/'.$mtg_id;
    $apiResponse = wp_remote_get( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_zoom_token_from_portal()
            ),
        )
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    $response_code = wp_remote_retrieve_response_code( $apiResponse );
    if(isset($apiBody->tracking_fields)){
        return $apiBody->tracking_fields;
    }else{
        return false;
    }

}


// Schedule zoom token an action if it's not already scheduled

add_filter( 'cron_schedules', 'muslimeto_cron_every_one_hour_for_zoom' );
function muslimeto_cron_every_one_hour_for_zoom( $schedules ) {
    $schedules['every_45_min_zoom'] = array(
        'interval'  => 2700,
        'display'   => __( 'Every 45 min zoom', 'textdomain' )
    );
    return $schedules;
}
if ( ! wp_next_scheduled( 'muslimeto_cron_every_one_hour_for_zoom' ) ) {
    wp_schedule_event( time(), 'every_45_min_zoom', 'muslimeto_cron_every_one_hour_for_zoom' );
}
add_action( 'muslimeto_cron_every_one_hour_for_zoom', 'refresh_zoom_token_func' );
function refresh_zoom_token_func() {
    if(site_url() !== 'https://portal.muslimeto.com') {return;}
    $apiUrl = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=1YQbKuYXRvK2Y3Kkwn98rQ';
    $apiResponse = wp_remote_post( $apiUrl,
        [
            'method'    => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'tGHzAgk9TGWq8INi2Ykhhw' . ':' . 'qucqXw6rGKdvTQpjmFJImTc13QwsWCGi' )
            )
        ]
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    if(isset($apiBody->access_token))
    {
        update_option('zoom_access_token',$apiBody->access_token);
    }else{
        wp_mail('himamohamed1991@gmail.com','zoom token','failed');
    }
}

function muslimito_create_meetings_log(){
    global $wpdb;
    $table_name =  "msl_meetings";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      mtg_id text NOT NULL,
	    mtg_topic text NOT NULL,
      mtg_host_email text NOT NULL,
      mtg_class_type text NOT NULL,
      mtg_class_cid text NOT NULL,
      mtg_recordings text NOT NULL,
      mtg_events text NOT NULL,
      PRIMARY KEY id (id)
      ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'muslimito_create_meetings_log');


function muslimito_create_meetings_recordings_log(){
    global $wpdb;
    $table_name =  "msl_recordings";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      mtg_id text NOT NULL,
	    mtg_uuid text NOT NULL,
      mtg_host_email text NOT NULL,
      mtg_class_type text NOT NULL,
      mtg_class_cid text NOT NULL,
      rec_id text NOT NULL,
      rec_type text NOT NULL,
      rec_start text NOT NULL,
      rec_end text NOT NULL,
      rec_passcode text NOT NULL,
      rec_download_token text NOT NULL,
      rec_url text NOT NULL,
      rec_file_name text NOT NULL,
      rec_file_size text NOT NULL,
      rec_file_type text NOT NULL,
      mtg_class_aip text NOT NULL,
      rec_aws_url text NOT NULL,
      rec_aws_file_size text NOT NULL,
      PRIMARY KEY id (id)
      ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'muslimito_create_meetings_recordings_log');


function msl_create_zoom_meeting($host_email, $topic='', $class_type='', $class_id=''){
    $apiUrl = 'https://api.zoom.us/v2/users/learning@muslimeto.com/meetings';
    $apiResponse = wp_remote_post( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_zoom_token_from_portal()
            ),
            "body"    => wp_json_encode(array(
                "type"=> 3,
                "recurrence" => [ "type" => 3 ],
                "schedule_for" => $host_email,
                "topic" => $topic,
                "tracking_fields" => array(
                    ["field"=>"class_type","value"=>$class_type],
                    ["field"=>"class_id","value"=> $class_id]
                )
            )),
        )
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    $response_code = wp_remote_retrieve_response_code( $apiResponse );
    if($response_code == 201 && isset($apiBody->id)){
        return $apiBody->id;
    }elseif($response_code == 400 && $apiBody->code == 3000){
        $assist = msl_create_zoom_assistant($host_email);
        if($assist == 201){
            $apiUrl = 'https://api.zoom.us/v2/users/learning@muslimeto.com/meetings';
            $apiResponse = wp_remote_post( $apiUrl,
                array(
                    "headers" => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer " .get_zoom_token_from_portal()
                    ),
                    "body"    => wp_json_encode(array(
                        "type"=> 3,
                        "recurrence" => [ "type" => 3 ],
                        "schedule_for" => $host_email,
                        "topic" => $topic,
                        "tracking_fields" => array(
                            ["field"=>"class_type","value"=>$class_type],
                            ["field"=>"class_id","value"=> $class_id]
                        )
                    )),
                )
            );
            $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
            return $apiBody->id?$apiBody->id:false;
        }else{
            return "Email not in system !";
        }
    }
}

function msl_create_zoom_assistant($host_email){
    $apiUrl = "https://api.zoom.us/v2/users/$host_email/assistants";
    $apiResponse = wp_remote_post( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_zoom_token_from_portal()
            ),
            "body"    => wp_json_encode(array(
                "assistants" => array(
                    ["email" => "learning@muslimeto.com"]
                )
            )),
        )
    );
    $response_code = wp_remote_retrieve_response_code( $apiResponse );
    return $response_code;
}

function msl_update_zoom_meeting($mtg_id,$new_host){
    $apiUrl = 'https://api.zoom.us/v2/meetings/'.$mtg_id;
    $apiResponse = wp_remote_post( $apiUrl,
        array(
            'method'      => 'PATCH',
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_zoom_token_from_portal()
            ),
            "body" => wp_json_encode(array(
                "schedule_for"=> $new_host,
            )),
        )
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    $response_code = wp_remote_retrieve_response_code( $apiResponse );
    if($response_code == 204){return true;}
    return false;
}


function wpb_sender_email( $original_email_address ) {
    return 'notifications@muslimeto.com';
}
function wpb_sender_name( $original_email_from ) {
    return 'Muslimeto™ Notifications';
}
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );


add_action( 'init', 'stop_heartbeat', 1 );
function stop_heartbeat() {
    wp_deregister_script('heartbeat');
}

function get_tags_from_keap($parent_id){
    $ContactId  = wpf_get_contact_id( $parent_id, true );
    $apiUrl = "https://api.infusionsoft.com/crm/rest/v1/contacts/$ContactId";
    $apiResponse = wp_remote_get( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_token_from_dev()
            ),
        )
    );
    $data = json_decode( wp_remote_retrieve_body($apiResponse) );

    if( isset($data->email_addresses[0]->email) && email_exists($data->email_addresses[0]->email) )
        return $data->tag_ids;
    return false;
}


function apply_tags_from_keap($parent_id){
    $ContactId  = wpf_get_contact_id( $parent_id, true );
    $apiUrl = "https://api.infusionsoft.com/crm/rest/v1/contacts/$ContactId";
    $apiResponse = wp_remote_get( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_token_from_dev()
            ),
        )
    );
    $data = json_decode( wp_remote_retrieve_body($apiResponse) );


    if( isset($data->email_addresses[0]->email) && email_exists($data->email_addresses[0]->email) ){
        wp_fusion()->user->remove_tags( $data->tag_ids , $parent_id );
        wp_fusion()->user->apply_tags( array(380), $parent_id );
        return true;
    }else {
        return false;
    }

}

function delete_zoom_recording($mtg_uuid){
    $apiUrl = "https://api.zoom.us/v2/meetings/$mtg_uuid/recordings?action=trash";
    $apiResponse = wp_remote_post( $apiUrl,
        array(
            'method'      => 'DELETE',
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " .get_zoom_token_from_portal()
            ),
        )
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    // $response_code = wp_remote_retrieve_response_code( $apiResponse );
    // if($response_code == 204){return true;}
    // return false;
}


function mus_announcement_shortcode() {
    ob_start();
    get_template_part('template-parts/dashboard/template-announcement');
    return ob_get_clean();
}
add_shortcode( 'mus_announcement_shortcode', 'mus_announcement_shortcode' );

function mus_recordings_shortcode() {
    ob_start();
    get_template_part('template-parts/dashboard/template-recordings');
    return ob_get_clean();
}
add_shortcode( 'mus_recordings_shortcode', 'mus_recordings_shortcode' );

function ufmn_add_target_attributes( $activity_allowedtags ) {
    $activity_allowedtags['video']['src']    = array();
    $activity_allowedtags['video']['width']    = array();
    $activity_allowedtags['video']['controls']    = array();
    $activity_allowedtags['source']['src']    = array();
    $activity_allowedtags['source']['type']    = array();
    return $activity_allowedtags;
}
add_filter( 'bp_activity_allowed_tags', 'ufmn_add_target_attributes', 1 );
add_filter( 'bp_forums_allowed_tags', 'ufmn_add_target_attributes', 1 );



//start  zoom  queue
class BackgroundClassZoom extends Mus_Background_Process{
    protected $action = 'sync_recordings';
    protected function task( $item ) {
        upload_zoom_rec_to_aws($item);
        return false;
    }
}

class BackgroundZoom{
    public function __construct() {
        $this->task = new BackgroundClassZoom;
        add_action( 'wp_ajax_send_rec_id_for_update'  ,  [$this, 'send_rec_id_for_update']  );
        add_action( 'wp_ajax_nopriv_send_rec_id_for_update' , [$this, 'send_rec_id_for_update'] );
    }
    public function send_rec_id_for_update(){
        if( isset($_POST['sync_recs']) &&  isset($_POST['start_up'])  ){
            global $wpdb;
            $table_name = "msl_recordings";
            $all_recs = $wpdb->get_results( "SELECT * FROM msl_recordings where uploaded = 0 ORDER BY id DESC");
            foreach( $all_recs as $all_rec ){
                $this->task->push_to_queue( $all_rec->id );
            }
            $this->task->save()->dispatch();
        }
        //wp_mail('himamohamed1991@gmail.com','cid appoint started','cid appoint');
        //exit;
    }
}

add_action('plugins_loaded', function() {
    new BackgroundZoom();
});

//// start queue all not uploaded recs
function muslimeto_cron_job_zoom_record( $schedules ) {
    $schedules['e_one_hour'] = array(
        'display' => __( 'Once hourly', 'textdomain' ),
        'interval' => 3600,
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'muslimeto_cron_job_zoom_record' );
if (!wp_next_scheduled( 'muslimeto_cron_job_zoom_record' )){
    wp_schedule_event(time(), 'e_one_hour', 'muslimeto_cron_job_zoom_record');
}

add_action( 'muslimeto_cron_job_zoom_record', 'zoom_record_cron_func' );

function zoom_record_cron_func(){
    if($_SERVER['SERVER_NAME'] !== 'portal.muslimeto.com') {return;}
    $apiUrl = admin_url('admin-ajax.php?action=send_rec_id_for_update');
    wp_remote_post( $apiUrl, array(
        'body'        => array(
            'sync_recs' => 1,
            'start_up' => 'YES'
        ),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( 'mslmcom' . ':' . 'yteI 2xCv 6k9s elFV v0Ua zrUI' )
        ),
    ));
}


function upload_zoom_rec_to_aws($rec_id){
    global $wpdb;
    require WP_CONTENT_DIR. '/aws/vendor/autoload.php' ;

    if($_SERVER['SERVER_NAME'] == 'portal.muslimeto.com') {
        //$credentials = new Aws\Credentials\Credentials('asdasdasdasda', 'asjdnkjansdkjnasjkdnkjsandjknasd');
        $Bucket = 'lmspractice' ;
        $body = 'classes/' ;
    }else{
        $credentials = new Aws\Credentials\Credentials('asdasdasdasda', 'asjdnkjansdkjnasjkdnkjsandjknasd');
        $Bucket = 'lsm-portal-dev' ;
        $body = '' ;
    }

    $s3 = new Aws\S3\S3Client([
        'version'     => 'latest',
        'region'      => 'us-east-2',
        'credentials' => $credentials
    ]);

    $entries_d = $wpdb->get_results( "SELECT * FROM msl_recordings where id = $rec_id", ARRAY_A );
    $url = $entries_d[0]['rec_url'] . '?access_token=' . $entries_d[0]['rec_download_token'];
    $tmpfile = tempnam( sys_get_temp_dir(), gen_uuid() );

    $file_name = "msl_" . strtolower($entries_d[0]['rec_file_type']) . "_" . gen_uuid() . "." .  strtolower($entries_d[0]['rec_file_type']);

    $ch = curl_init($url);
    $fp = fopen((string)$tmpfile, 'r+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);

    $result = $s3->putObject(array(
        'Bucket' => $Bucket,
        'Key'    => $body . (string)$file_name,
        'Body' =>  fopen((string)$tmpfile, 'r+'),
        'ACL' => 'public-read'
    ));

    $obj_data = $s3->headObject([
        'Bucket' => $Bucket,
        'Key'    => $body . (string)$file_name
    ]);
    $aws_url = $result['ObjectURL'];
    $aws_size = ((int)$obj_data['ContentLength'])/1024/1024;

    if( round($aws_size , 2) == $entries_d[0]['rec_file_size'] ){
        delete_zoom_recording($entries_d[0]['mtg_uuid']); //delete zoom record
        $wpdb->update( 'msl_recordings', array( 'uploaded' => true,'rec_aws_url'=>$aws_url ,'rec_aws_file_size'=> round($aws_size , 2) ,'rec_file_name'=>$file_name ), array( 'id' => $rec_id ) ); //add updated


        //add post update with rec
        $class_type = $entries_d[0]['mtg_class_type'];
        $class_id = $entries_d[0]['mtg_class_cid'];
        if( isset($class_type) && $class_type == 'mvs' ){
            $admin_user = get_user_by('login','mslmcom');
            groups_post_update(array(
                'group_id'=> $class_id,
                'content' => '<h2>New Recording</h2><br><video width="100%" controls controlsList="nodownload"><source src="'.$aws_url.'" type="video/mp4"></ video>' ,
                'user_id' => $admin_user->ID,
            ));
        }
    }


}
//load teacher recodrdings


function load_teacher_zoom_recs(){
    global $wpdb;
    $teacher = $_POST['teacher'];
    $month = $_POST['month'];
    $year =$_POST['year'];
    $all_recs =  $wpdb->get_results( "SELECT * FROM msl_recordings WHERE MONTH(created_at) = $month AND YEAR(created_at) = $year AND mtg_host_email = '{$teacher}' AND rec_file_type = 'MP4' AND uploaded = 1 ORDER BY id DESC" );
    $gender = get_user_meta(get_current_user_id() , 'gender' , true);
    $type='video';
    $x=1;foreach ($all_recs as $all_rec):

        if($gender ==  'female'){
            $file = $all_rec->rec_aws_url;
        }elseif($gender ==  'male') {
            $user = get_user_by( 'email', $all_rec->mtg_host_email );
            $userId = $user->ID;
            $host_gender =  get_user_meta($userId , 'gender' , true);
            $niqab = get_user_meta($userId,'niqab',true);
            if($host_gender == 'male'){
                $file = $all_rec->rec_aws_url;
            }elseif($host_gender == 'female' && !$niqab) {
                $file = $all_rec->rec_aws_url;
            }elseif ($host_gender == 'female' && $niqab) {
                $audos = $wpdb->get_results( "SELECT * FROM msl_recordings where rec_file_type = 'M4A' AND mtg_uuid = '{$all_rec->mtg_uuid}'" );
                $file = $audos->rec_aws_url;
                $type='audio';
            }
        }else{
            $file = $all_rec->rec_aws_url;
        }
        $image_class="";
        $reviewer="";
        $notes="";
        $img="";
        if( isset($all_rec->gf_id) &&  $all_rec->gf_id > 0 ) :
            $enrty = GFAPI::get_entry( $all_rec->gf_id );
            $img = $enrty[33];
            $image_class = 'score_img_'.$all_rec->id;
            $reviewer = ucwords(str_replace("."," ",strstr($enrty[30], '@', true)));
            $notes=$enrty[5]; ?>
            <input class="score_img_<?php echo $all_rec->id ?>" type="hidden" value="<?php echo $img;?>" />
        <?php endif; ?>
        <tr class="media_tr" <?php if($_GET['row_id'] == $all_rec->id){echo 'active';} ?> data-type="<?php echo $type ?>" data-row="<?php echo $all_rec->id ?>" data-uuid="<?php echo $all_rec->mtg_uuid?>"
            data-gf="<?php echo $all_rec->gf_id;?>" data-imgClass="<?php echo $image_class ?>" data-reviewer="<?php echo $reviewer ?>" data-notes="<?php echo $notes ?>" img-src="<?php echo $img;?>">
            <th scope="row"><?php echo $x;?></th>
            <td class="td_email" data-val="<?php echo $all_rec->mtg_host_email; ?>"> <?php echo 'U. '. ucwords(str_replace("."," ",strstr($all_rec->mtg_host_email, '@', true)));  ?></td>
            <td class="hidden_td meeting_id_td"> <?php echo $all_rec->mtg_id  ?></td>
            <td> <?php echo $all_rec->mtg_class_cid  ?></td>
            <td class="hidden_td"> <?php echo $all_rec->mtg_class_aip  ?></td>
            <td class="date_td center_td"> <?php echo $all_rec->rec_start  ?></td>
            <td class="start_td center_td"> <?php echo $all_rec->rec_start?></td>
            <td class="end_td center_td"> <?php echo $all_rec->rec_end ?></td>
            <td class="meeting_media center_td">
                <a href="#"  class="madia_popup_btn" src="<?php echo $file; ?>" type="<?php echo $all_rec->rec_type?>" for="playing_video" data-media="video" data-balloon-pos="down" data-balloon="Size:<?php echo $all_rec->rec_aws_file_size  ?>">
                    <i class="bb-icon-video bb-icon-l"></i>
                </a>
            </td>
            <td class="tr_score score_td center_td" scope="col" data-balloon-pos="down" data-balloon-pos="down" data-balloon="Score">
                <?php echo $all_rec->gf_id ?  $all_rec->cqs : '-';  ?></td>
            <input class="score_img_<?php echo $all_rec->id ?>" type="hidden" value="<?php echo $img;?>" />
        </tr>
        <?php $x++; endforeach;
    exit;
}
add_action( 'wp_ajax_nopriv_load_teacher_zoom_recs', 'load_teacher_zoom_recs' );
add_action( 'wp_ajax_load_teacher_zoom_recs', 'load_teacher_zoom_recs') ;


add_action( 'gform_after_submission_37', 'set_post_content', 10, 2 );
function set_post_content( $entry, $form ) {
    global $wpdb;
    $db_id =   rgar( $entry, '32' );
    $rating  =   rgar( $entry, '14' );
    $score  = rgar( $entry, 'gsurvey_score' );
    $total = ( $rating + $score) / 5 ;
    $wpdb->update( 'msl_recordings', array( 'gf_id' => $entry['id'] , 'cqs' => $total), array( 'id' => $db_id ) );
}


add_action( 'gform_after_submission_39', 'set_feedback_stats', 10, 2 );
function set_feedback_stats( $entry, $form ) {
    global $wpdb;
    $row = $wpdb->insert_id;
    $next  = $row + 1;
    $wpdb->update( 'msl_feedback_stats', array( 'gf_id' => $entry['id'] ), array( 'id' => $next ) );
}


add_action( 'gform_pre_submission_37', function ( $form ) {
    $rating_field  =  rgpost( 'input_14' , true );
    $_POST['input_27'] = $rating_field ;
    $_POST['input_25'] = date('m/d/Y');
});

function get_teacher_score_ajax(){
    global $wpdb;
    $row = $_POST['row'];
    $res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM msl_recordings WHERE id = $row" ) );
    $cqs =  $res->cqs;
    $gf_id = $res->gf_id;
    $enrty = GFAPI::get_entry( $gf_id );
    $img = $enrty[33];
    $TID = email_exists( $res->mtg_host_email );

    $date = $res->created_at;
    $year = date('Y', strtotime($date));
    $month = date('F', strtotime($date));
    $q = date('n', strtotime($date));
    $quarter = ceil($q / 3);
    $values = array( 'Poor'=>-1, 'Fair'=>3, 'Good'=>5, 'Very Good'=>6, 'Excellent'=>7 );
    $args = array(
        "UID" => get_current_user_id(),
        "TID" =>  $TID,
        "CID" => $res->mtg_class_cid,
        "gf_id" => $res->gf_id,
        "escalation" =>  ($res->cqs < 0) ? 1 : 0 ,
        "channel" => 'Portal',
        "feedback_classifier" => 'Ad-Hoc',
        "type" => 'CQS',
        "Processed" => 'NO',
        "score" => $res->cqs,
        "notes" => $enrty[5]?$enrty[5]:'',
        "AC_Teaching_Methods" => $values[$_POST['AC_Teaching_Methods']],
        "AC_Presentation_Material" => $values[$_POST['AC_Presentation_Material']],
        "SS_Student_Engagement" => $values[$_POST['SS_Student_Engagement']],
        "SS_English_Fluency" => $values[$_POST['SS_English_Fluency']],
        "NAC_Class_Professionalism" => $values[$_POST['NAC_Class_Professionalism']],
        "VL_Using_Phone" => $_POST['VL_Using_Phone']?1:0,
        "VL_Camera_Off" => $_POST['VL_Camera_Off']?1:0,
        "VL_Multitasking" => $_POST['VL_Multitasking']?1:0,
        "VL_Not_Teaching" => $_POST['VL_Not_Teaching']?1:0,
        "VL_Code_of_Honor" => $_POST['VL_Code_of_Honor']?1:0,
        "month" => $month,
        "quarter" => $quarter,
        "year" => $year,
    );
    $wpdb->insert("msl_feedback_stats", $args);

    wp_send_json_success( array('score' => $cqs, 'img' => $img ) );
    exit;
}
add_action( 'wp_ajax_nopriv_get_teacher_score_ajax', 'get_teacher_score_ajax' );
add_action( 'wp_ajax_get_teacher_score_ajax', 'get_teacher_score_ajax') ;



function muslimito_create_feedback_stats(){
    global $wpdb;
    $table_name =  "msl_feedback_stats";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      UID int(11) NULL,
	    TID int(11) NULL,
      CID int(11) NULL,
      gf_id int(11) NULL,
      escalation boolean not null default 0,
      channel varchar(20) NOT NULL default '',
      feedback_classifier varchar(20) NOT NULL default '',
      type varchar(20) NOT NULL default '',
      Processed varchar(20) NOT NULL default '',
      score int(11) NULL,
      plf_score int(11) NULL,
      notes text NOT NULL default '',

      AC_Teaching_Methods  int(11) NULL,
      AC_Presentation_Material int(11) NULL,
      AC_TM_Class_Preparation int(11) NULL,
      AC_Quranic_Arabic  int(11) NULL,
      AC_Islamic_Studies int(11) NULL,
      AC_Tajweed int(11) NULL,
      AC_Arabic_Language int(11) NULL,

      SS_Student_Engagement int(11) NULL,
      SS_IceBreak int(11) NULL,
      SS_Games int(11) NULL,
      SS_Friendly_Atmosphere int(11) NULL,
      SS_Kids_Friendly int(11) NULL,
      SS_Teaching_Adults int(11) NULL,
      SS_Teaching_DL int(11) NULL,
      SS_Teaching_HTM int(11) NULL,
      SS_Teaching_TM int(11) NULL,

      SS_English_Fluency int(11) NULL,
      SS_English_Communication int(11) NULL,
      SS_English_Pronounciation int(11) NULL,
      SS_English_Listening int(11) NULL,
      SS_English_Vocabs int(11) NULL,
      SS_English_Grammar int(11) NULL,

      NAC_Class_Professionalism int(11) NULL,
      NAC_Internal_Professionalism int(11) NULL,
      NAC_WeeklyMeeting int(11) NULL,
      NAC_QuiteWorkplace int(11) NULL,
      NAC_Punctuality int(11) NULL,
      NAC_Responsiveness int(11) NULL,
      NAC_Camera int(11) NULL,
      NAC_Internet int(11) NULL,
      NAC_MSLM_VBG int(11) NULL,

      VL_Using_Phone boolean not null default 0,
      VL_Camera_Off boolean not null default 0,
      VL_Multitasking boolean not null default 0,
      VL_Not_Teaching boolean not null default 0,
      VL_Code_of_Honor boolean not null default 0,
      VL_Bad_Internet boolean not null default 0,
      VL_TeacherNoShow boolean not null default 0,
      VL_Meetings boolean not null default 0,
      VL_Responsiveness boolean not null default 0,

      month varchar(20) NOT NULL default '',
      quarter varchar(20) NOT NULL default '',
      year varchar(20) NOT NULL default '',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY id (id)
      ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('init', 'muslimito_create_feedback_stats');




add_action('wp_footer', 'load_hr_teacher_form');
function load_hr_teacher_form(){
    $nonce = wp_nonce_field('wp-wphr-hr-employee-nonce');

    echo get_template_part('template-parts/common/template-modal', null, array(
            'id' => 'hr_teacher_form',
            'title' => '',
            'body' => '<div class="form_add_teacher"><input type="text" class="form-control f_name" name="personal[first_name]" value="" placeholder="first name"><input type="text" class="form-control l_name" name="personal[last_name]" value="" placeholder="last name"><input type="text" class="form-control u_email" name="user_email" value="" placeholder="email"> <input type="date" placeholder="Date of Hire" data-date-format="DD-MM-YYYY" class="form-control hr_date">'.$nonce.'<select name="personal[gender]" class="form-control gender_sel"><option value="" selected="selected" disabled="disabled">Gender</option><option value="male">Male</option><option value="female">Female</option></select><div class="form-control niqab_sel" style="display:none"><h4>Niqab</h4><div><label for="nn1">YES</label><input type="radio" id="nn1" name="nn1" value="1"></div><div><label for="nn2">NO</label><input type="radio" id="nn2" name="nn1" value="0" checked="checked"></div></div><button type="button" class="create_emp btn btn-success">Create Employee<i class="fa fa-spinner fa-spin fa-pulse" style="font-size:14px;color:#fff;font-weight:700;display:none"></i></button></div>'
        )
    );
}
function update_teacher_hr_data(){
    $uid = $_POST['user_id'];
    $user = new WP_User( $uid );

    $current = strtotime(date("Y-m-d"));
    $date    = strtotime($user->user_registered);
    $datediff = $date - $current;
    $difference = floor($datediff/(60*60*24));
    if($difference==0){

        $user->add_role( 'teacher' );
        $data = get_userdata( $uid );
        $pass =  $data->first_name .'-'. $data->last_name . '@' . rand(111111,999999);
        wp_set_password( $pass, $uid );
        add_user_meta($uid, 'pw_string', $pass);
        wp_send_json(["success"=>true]);

    }else{
        wp_send_json(["success"=>false]);
    }
    exit;
}
add_action( 'wp_ajax_nopriv_update_teacher_hr_data', 'update_teacher_hr_data' );
add_action( 'wp_ajax_update_teacher_hr_data', 'update_teacher_hr_data') ;


function msl_get_contacts_by_tag($tag_id){
    $apiUrl = "https://api.infusionsoft.com/crm/rest/v1/tags/$tag_id/contacts?limit=1000";
    $apiResponse = wp_remote_get( $apiUrl,
        array(
            "headers" => array(
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ".get_token_from_dev()
            ),
        )
    );
    $apiBody = json_decode( wp_remote_retrieve_body( $apiResponse ) );
    if(isset($apiBody->contacts)){
        return array_column( array_column($apiBody->contacts , 'contact') , 'id' );
    }else{
        return [];
    }
}


function update_teacher_periodic_assessment(){
    global $wpdb;
    $date=date('Y-m-d h:i:s');
    $year = date('Y', strtotime($date));
    $month = date('F', strtotime($date));
    $q = date('n', strtotime($date));
    $quarter = ceil($q / 3);
    $values = array( 'Poor'=>-1, 'Fair'=>3, 'Good'=>5, 'Very Good'=>6, 'Excellent'=>7 );
    $args = array(
        "TID" =>  (int)$_POST['TID'],
        "UID" => get_current_user_id(),
        "AC_Teaching_Methods" => $values[$_POST['AC_Teaching_Methods']],
        "AC_TM_Class_Preparation" => $values[$_POST['AC_TM_Class_Preparation']],
        "AC_Presentation_Material" => $values[$_POST['AC_Presentation_Material']],
        "AC_Quranic_Arabic" => $values[$_POST['AC_Quranic_Arabic']],
        "AC_Tajweed" => $values[$_POST['AC_Tajweed']],
        "AC_Arabic_Language" => $values[$_POST['AC_Tajweed']],
        "AC_Islamic_Studies" => $values[$_POST['AC_Islamic_Studies']],

        "SS_Student_Engagement" => $values[$_POST['SS_Student_Engagement']],
        "SS_IceBreak" => $values[$_POST['SS_IceBreak']],
        "SS_Games" => $values[$_POST['SS_Games']],
        "SS_Friendly_Atmosphere" => $values[$_POST['SS_Friendly_Atmosphere']],
        "SS_Kids_Friendly" => $values[$_POST['SS_Kids_Friendly']],
        "SS_Teaching_Adults" => $values[$_POST['SS_Teaching_Adults']],
        "SS_Teaching_DL" => $values[$_POST['SS_Teaching_DL']],
        "SS_Friendly_Atmosphere" => $values[$_POST['SS_Friendly_Atmosphere']],
        "SS_Teaching_HTM" => $values[$_POST['SS_Teaching_HTM']],
        "SS_Teaching_TM" => $values[$_POST['SS_Teaching_TM']],

        "NAC_Class_Professionalism" => $values[$_POST['NAC_Class_Professionalism']],
        "NAC_WeeklyMeeting" => $values[$_POST['NAC_WeeklyMeeting']],
        "NAC_QuiteWorkplace" => $values[$_POST['NAC_QuiteWorkplace']],
        "NAC_Punctuality" => $values[$_POST['NAC_Punctuality']],
        "NAC_Responsiveness" => $values[$_POST['NAC_Responsiveness']],
        "NAC_Camera" => $values[$_POST['NAC_Camera']],
        "NAC_Internet" => $values[$_POST['NAC_Internet']],
        "NAC_MSLM_VBG" => $values[$_POST['NAC_MSLM_VBG']],

        "SS_English_Communication" => $values[$_POST['SS_English_Communication']],
        "SS_English_Pronounciation" => $values[$_POST['SS_English_Pronounciation']],
        "SS_English_Listening" => $values[$_POST['SS_English_Listening']],
        "SS_English_Vocabs" => $values[$_POST['SS_English_Vocabs']],
        "SS_English_Grammar" => $values[$_POST['SS_English_Grammar']],
        "notes"=> $_POST['notes']?$_POST['notes']:' ',
        "month" => $month,
        "quarter" => $quarter,
        "year" => $year,
    );
    $wpdb->insert("msl_feedback_stats", $args);
    wp_send_json_success( array('success' => true) );
    exit;
}
add_action( 'wp_ajax_nopriv_update_teacher_periodic_assessment', 'update_teacher_periodic_assessment' );
add_action( 'wp_ajax_update_teacher_periodic_assessment', 'update_teacher_periodic_assessment') ;
