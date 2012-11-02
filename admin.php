<?php


// Call function when plugin is activated
register_activation_hook(__FILE__, 'moolah_install');

// Action hook to add the post products menu item
add_action('admin_menu', 'moolah_menu');

function moolah_install()
{
    // setup our default option values
    $moolah_options_arr = array(
    );

    // save our default option values
    update_option('moolah_options', $moolah_options_arr);
}

// create the post products sub-menu
function moolah_menu()
{
    add_options_page(
        __('Moolah Settings Page', 'moolah-plugin'),
        __('Moolah', 'moolah-plugin'),
        'administrator',
        __FILE__,
        'moolah_settings_page'
    );
}