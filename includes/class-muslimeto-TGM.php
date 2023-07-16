<?php


/**
 * Load TGM support
 */


add_action( 'tgmpa_register', 'muslimeto_tgm_install_plugins' );

function muslimeto_tgm_install_plugins() {


    /*
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
    $plugins = array(

        // This is an example of how to include a plugin bundled with a theme.
        array(
            'name'               => 'Redux Framework', // The plugin name.
            'slug'               => 'redux-framework-4-master', // The plugin slug (typically the folder name).
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'source'             => plugin_dir_path( __FILE__ ) . 'plugins/redux-framework-4-master.zip', // The plugin source.
        ),



    );

    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * TGMPA will start providing localized text strings soon. If you already have translations of our standard
     * strings available, please help us make TGMPA even better by giving us access to these translations or by
     * sending in a pull-request with .po file(s) with the translations.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = array(
        'id'           => 'muslimeto-core',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-muslimeto-plugins', // Menu slug.
        'parent_slug'  => 'themes.php',            // Parent menu slug.
        'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.

    );

    tgmpa( $plugins, $config );

}