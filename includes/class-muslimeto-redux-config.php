<?php
/**
 * ReduxFramework Barebones Sample Config File
 * For full documentation, please visit: http://devs.redux.io/
 *
 * @package Redux Framework
 */

if ( ! class_exists( 'Redux' ) ) {
    return null;
}


// This is your option name where all the Redux data is stored.
$opt_name = 'muslimeto';
Redux::init("muslimeto"); // opt_name is your opt_name
Redux::disable_demo();


/**
 * GLOBAL ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to: @link https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 */

/**
 * ---> BEGIN ARGUMENTS
 */



$args = array(
    // REQUIRED!!  Change these values as you need/desire.
    'opt_name'                  => $opt_name,

    // Name that appears at the top of your panel.
    'display_name'              => '<span class="redux-heading"> <img src="'. site_url() . '/wp-content/plugins/muslimeto-core-main/public/images/muslimeto-icon.png' .'"> Muslimeto Core Options </span>',

    // Version that appears at the top of your panel.
    'display_version'           => MUSLIMETO_CORE_VERSION,

    // Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only).
    'menu_type'                 => 'menu',

    // Show the sections below the admin menu item or not.
    'allow_sub_menu'            => true,

    'menu_title'                => esc_html__( 'Muslimeto Core Options', 'muslimeto-core' ),
    'page_title'                => esc_html__( 'Muslimeto Core Options', 'muslimeto-core' ),

    // Use a asynchronous font on the front end or font string.
    'async_typography'          => true,

    // Disable this in case you want to create your own google fonts loader.
    'disable_google_fonts_link' => false,

    // Show the panel pages on the admin bar.
    'admin_bar'                 => false,

    // Choose an icon for the admin bar menu.
    'admin_bar_icon'            => 'dashicons-portfolio',

    // Choose an priority for the admin bar menu.
    'admin_bar_priority'        => 50,

    // Set a different name for your global variable other than the opt_name.
    'global_variable'           => '',

    // Show the time the page took to load, etc.
    'dev_mode'                  => false,

    // Enable basic customizer support.
    'customizer'                => true,

    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
    'page_priority'             => null,

    // For a full list of options, visit: @link http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters.
    'page_parent'               => 'themes.php',

    // Permissions needed to access the options panel.
    'page_permissions'          => 'manage_options',

    // Specify a custom URL to an icon.
    'menu_icon'                 => site_url() . '/wp-content/plugins/muslimeto-core-main/public/images/muslimeto-icon.png',

    // Force your panel to always open to a specific tab (by id).
    'last_tab'                  => '',

    // Icon displayed in the admin panel next to your menu_title.
    'page_icon'                 => 'icon-themes',

    // Page slug used to denote the panel.
    'page_slug'                 => 'muslimeto_core_options',

    // On load save the defaults to DB before user clicks save or not.
    'save_defaults'             => true,

    // If true, shows the default value next to each field that is not the default value.
    'default_show'              => false,

    // What to print by the field's title if the value shown is default. Suggested: *.
    'default_mark'              => '',

    // Shows the Import/Export panel when not used as a field.
    'show_import_export'        => true,

    // CAREFUL -> These options are for advanced use only.
    'transient_time'            => 60 * MINUTE_IN_SECONDS,

    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output.
    'output'                    => true,

    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head.
    'output_tag'                => true,

    // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
    // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
    'database'                  => '',

    // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.
    'use_cdn'                   => true,
    'compiler'                  => true,

    // HINTS.
    'hints'                     => array(
        'icon'          => 'el el-question-sign',
        'icon_position' => 'right',
        'icon_color'    => 'lightgray',
        'icon_size'     => 'normal',
        'tip_style'     => array(
            'color'   => 'light',
            'shadow'  => true,
            'rounded' => false,
            'style'   => '',
        ),
        'tip_position'  => array(
            'my' => 'top left',
            'at' => 'bottom right',
        ),
        'tip_effect'    => array(
            'show' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'mouseover',
            ),
            'hide' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'click mouseleave',
            ),
        ),
    ),
);

// ADMIN BAR LINKS -> Setup custom links in the admin bar menu as external items.
$args['admin_bar_links'][] = array(
    'id'    => 'redux-docs',
    'href'  => '//devs.redux.io/',
    'title' => esc_html__( 'Documentation', 'muslimeto-core' ),
);

$args['admin_bar_links'][] = array(
    'id'    => 'redux-support',
    'href'  => '//github.com/ReduxFramework/redux-framework/issues',
    'title' => esc_html__( 'Support', 'muslimeto-core' ),
);

$args['admin_bar_links'][] = array(
    'id'    => 'redux-extensions',
    'href'  => 'redux.io/extensions',
    'title' => esc_html__( 'Extensions', 'muslimeto-core' ),
);

// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
$args['share_icons'][] = array(
    'url'   => '//github.com/ReduxFramework/ReduxFramework',
    'title' => 'Visit us on GitHub',
    'icon'  => 'el el-github',
);
$args['share_icons'][] = array(
    'url'   => '//www.facebook.com/pages/Redux-Framework/243141545850368',
    'title' => esc_html__( 'Like us on Facebook', 'muslimeto-core' ),
    'icon'  => 'el el-facebook',
);
$args['share_icons'][] = array(
    'url'   => '//twitter.com/reduxframework',
    'title' => esc_html__( 'Follow us on Twitter', 'muslimeto-core' ),
    'icon'  => 'el el-twitter',
);
$args['share_icons'][] = array(
    'url'   => '//www.linkedin.com/company/redux-framework',
    'title' => esc_html__( 'FInd us on LinkedIn', 'muslimeto-core' ),
    'icon'  => 'el el-linkedin',
);

// Panel Intro text -> before the form.
if ( ! isset( $args['global_variable'] ) || false !== $args['global_variable'] ) {
    if ( ! empty( $args['global_variable'] ) ) {
        $v = $args['global_variable'];
    } else {
        $v = str_replace( '-', '_', $args['opt_name'] );
    }
    //$args['intro_text'] = '<p>' . sprintf( __( 'Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: $s', 'muslimeto-core' ) . '</p>', '<strong>' . $v . '</strong>' );
} else {
    //$args['intro_text'] = '<p>' . esc_html__( 'This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.', 'muslimeto-core' ) . '</p>';
}

// Add content after the form.
//$args['footer_text'] = '<p>' . esc_html__( 'This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.', 'muslimeto-core' ) . '</p>';

Redux::set_args( $opt_name, $args );

/*
 * ---> END ARGUMENTS
 */

/*
 * ---> BEGIN HELP TABS
 */

$help_tabs = array(
    array(
        'id'      => 'redux-help-tab-1',
        'title'   => esc_html__( 'Theme Information 1', 'muslimeto-core' ),
        'content' => '<p>' . esc_html__( 'This is the tab content, HTML is allowed.', 'muslimeto-core' ) . '</p>',
    ),

    array(
        'id'      => 'redux-help-tab-2',
        'title'   => esc_html__( 'Theme Information 2', 'muslimeto-core' ),
        'content' => '<p>' . esc_html__( 'This is the tab content, HTML is allowed.', 'muslimeto-core' ) . '</p>',
    ),
);

Redux::set_help_tab( $opt_name, $help_tabs );

// Set the help sidebar.
$content = '<p>' . esc_html__( 'This is the sidebar content, HTML is allowed.', 'muslimeto-core' ) . '</p>';
Redux::set_help_sidebar( $opt_name, $content );

/*
 * <--- END HELP TABS
 */

/*
 *
 * ---> BEGIN SECTIONS
 *
 */

/* As of Redux 3.5+, there is an extensive API. This API can be used in a mix/match mode allowing for. */

/* -> START Basic Fields. */

$kses_exceptions = array(
    'a'      => array(
        'href' => array(),
    ),
    'strong' => array(),
    'br'     => array(),
);




// sections start

global $wpdb;
$active_forms = [];
$gforms_table = $wpdb->prefix . 'gf_form';
$gFormsresult = $wpdb->get_results(
    "SELECT * FROM $gforms_table WHERE is_active = 1 AND is_trash = 0 "
);
$wpdb->flush();
foreach ( $gFormsresult as $form ):
    $form_id = $form->id;
    $form_title = $form->title;
    $active_forms[$form_id] = 'ID: ' . $form_id . ' - ' . $form_title;
endforeach;


// Global Settings section start
Redux::set_section( 'muslimeto', array(
    'title'   => 'Global Settings',
    'icon'    => 'el-tasks',
    'heading' => '',
    'desc'    => '',
    'fields'  => array(
        array(
            'id' => 'enable_wp_debug',
            'type' => 'switch',
            'title' => __( 'Enable WP Debug' , 'redux_docs_generator' )
        )
    ),
) );



// Gravity Forms Settings start
Redux::set_section( 'muslimeto', array(
    'title'   => 'Gravity Form Settings',
    'icon'    => 'el-icon-website',
    'heading' => '',
    'desc'    => '',
    'fields'  => array(
    ),
) );





Redux::set_section( 'muslimeto', array(
    'subsection' => true, // this sub section of previuos one
    'title'   => 'Mapping Forms',
    'icon'    => 'el-icon-link',
    'heading' => 'Link Gravity Forms To Core Plugin',
    'desc'    => '',
    'fields'  => array(
        // select to list all gravity forms
        array(
            'id'       => 'SP_PARENT_FORM_ID',
            'type'     => 'select',
            'title'    => esc_html__('Single Program Form', 'muslimeto-core'),
            'subtitle' => '',
            'desc'     => esc_html__('select Single Program Form ID', 'muslimeto-core'),
            // Must provide key => value pairs for select options
            'options'  => $active_forms,
            'default'  => '',
        ),
        array(
            'id'       => 'SCHEDULE_FORM_ID',
            'type'     => 'select',
            'title'    => esc_html__('Schedule(s) Form', 'muslimeto-core'),
            'subtitle' => '',
            'desc'     => esc_html__('select Schedule(s) Form ID', 'muslimeto-core'),
            // Must provide key => value pairs for select options
            'options'  => $active_forms,
            'default'  => '',
        ),
        array(
            'id'       => 'LEARNERS_FORM_ID',
            'type'     => 'select',
            'title'    => esc_html__('Learner(s) Form', 'muslimeto-core'),
            'subtitle' => '',
            'desc'     => esc_html__('select Learner(s) Form ID', 'muslimeto-core'),
            // Must provide key => value pairs for select options
            'options'  => $active_forms,
            'default'  => '',
        ),
        array(
            'id'       => 'STAFF_FORM_ID',
            'type'     => 'select',
            'title'    => esc_html__('Staff Form', 'muslimeto-core'),
            'subtitle' => '',
            'desc'     => esc_html__('select staff Form ID', 'muslimeto-core'),
            // Must provide key => value pairs for select options
            'options'  => $active_forms,
            'default'  => '',
        ),

    ),
) );



Redux::set_section( 'muslimeto', array(
    'subsection' => true, // this sub section of previuos one
    'title'   => 'REST API Keys',
    'icon'    => 'el-icon-key',
    'heading' => 'Link Gravity Forms REST API to core plugin',
    'desc'    => '',
    'fields'  => array(
        array(
            'id' => 'gf_rest_api',
            'type' => 'text',
            'data' => array(
                'consumer_key' => 'Consumer Key',
                'consumer_secret' => 'Consumer Secret'
            )

    ),
            )
    )
);


// custom Bookly Settings start

// get all bookly custom fields
$bookly_custom_fields = get_option('bookly_custom_fields_data');
foreach ( json_decode($bookly_custom_fields) as $custom_field ):
    $custom_field_id = $custom_field->id;
    $custom_field_name = $custom_field->label;
    $custom_fields[$custom_field_id] = $custom_field_name . ' - ' . $custom_field_id;
endforeach;


// get first and last user id
$users_table = $wpdb->prefix . 'users';
$user_result = $wpdb->get_results(
    "SELECT * FROM $users_table ORDER BY ID ASC LIMIT 1"
);
$wpdb->flush();
$first_user_id = $user_result[0]->ID;

$user_result = $wpdb->get_results(
    "SELECT * FROM $users_table ORDER BY ID DESC LIMIT 1"
);
$wpdb->flush();
$last_user_id = $user_result[0]->ID;

$learnersSyncContent = '
                        <div id="myProgress">
                          <div id="myBar"></div>
                        </div>
                <div class="text-center sync-container">
                    <a href="#" class="button button-primary learners-sync"> Sync </a>
                    <div class="result-status learner">
                        <span class="sync_result learner">  </span> 
                    </div>
                    
                    <input type="text" value="" placeholder="from records" id="last_records">
                    <input type="text" value="'.$last_user_id.'" placeholder="from records" id="next_records">
                </div>
                
                <input type="hidden" value="'.$first_user_id.'" id="first_user_id">
                <input type="hidden" value="'.$last_user_id.'" id="last_user_id">
               <input type="hidden" id="resume_status">
               
              
               
                ';




$staffSyncContent = '
                <div class="text-center sync-container">
                    <a href="#" class="button button-primary staff-sync"> Sync </a>
                    <div class="result-status staff">
                        <span class="sync_result staff">  </span> 
                    </div>
                </div>
                ';





Redux::set_section( 'muslimeto', array(
    'title'   => 'Custom Bookly Settings',
    'icon'    => 'el-icon-calendar',
    'heading' => '',
    'desc'    => '',
    'fields'  => array(
        // select to bookly custom fields
        array(
            'id'       => 'bookly_custom_field_bb_gid',
            'type'     => 'select',
            'title'    => esc_html__('BB Group ID Custom Field', 'muslimeto-core'),
            'subtitle' => '',
            'desc'     => esc_html__('select custom field for buddyboss group id ID', 'muslimeto-core'),
            // Must provide key => value pairs for select options
            'options'  => $custom_fields,
            'default'  => '',
        ),
        array(
            'id'           => 'bookly-sync-learners',
            'type'         => 'raw',
            'title'        => esc_html__('Learners Sync', 'muslimeto-core'),
            'subtitle'     => '',
            'desc'         => '',
            'content' => $learnersSyncContent
        ),
        array(
            'id'           => 'bookly-sync-staff',
            'type'         => 'raw',
            'title'        => esc_html__('Staff Sync', 'muslimeto-core'),
            'subtitle'     => '',
            'desc'         => '',
            'content' => $staffSyncContent
        )
    ),
) );








// sections End




/*
 * <--- END SECTIONS
 */






