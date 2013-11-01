<?php

/*
 * Plugin Name: Moolah
 * Plugin URI: http://moolah-ecommerce.com
 * Version: 3.2.1
 * Description: Moolah E-Commerce
 * Author: Moolah E-Commerce
 * Author Email: dev@moolah-ecommerce.com
 * Author URI: http://moolah-ecommerce.com
 */

// Action hook to initialize the plugin
add_action('admin_init', 'moolah_init');
// Action hook to register our option settings
add_action('admin_init', 'moolah_register_settings');

// Action hook to save the meta box data when the post is saved
add_action('save_post', 'moolah_save_meta_box');
// Action hook to create the post products shortcode
add_shortcode('moolah', 'moolah_post');
add_filter( 'the_content', 'moolah_page' );

// Require the administrative file if we're an admin
if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/admin.php';
}

// Test where should we make our API calls to
function moolah_home()
{
	static $home;

	if (!$home) {

        $options = get_option('moolah_options');
        $source  = $options['source'];

        if ( ! $source || $source == 'store' ) {
            $home = 'store.moolah-ecommerce.com';
        } else {
            $home = $source;
        }

	}

	return $home;
}

// Create post meta box
function moolah_init()
{
	// create our custom meta box
	static $already;
    if ( ! $already ) {
        add_meta_box('moolah-meta', __('Moolah Store', 'moolah-plugin'), 'moolah_meta_box', 'post', 'side', 'default');
        $already = true;
    }
}

// Load Moolah in a page
function moolah_page($content)
{
    global $post;

    $show = get_post_meta($post->ID, 'moolah_show', true);
    if ( ! $show )
    {
        return $content;
    }

    $attribs = array();
    // Perhaps a category was provided in the metadata ?
    $attribs['category'] = get_post_meta($post->ID, 'moolah_category', true);

    // Perhaps a product was provided in the metadata ?
    $attribs['product'] = get_post_meta($post->ID, 'moolah_product', true);

    return moolah_embed($content,$attribs);
}

// Load Moolah in a post
function moolah_post($attribs, $content = null) {
    return moolah_embed($content,$attribs);
}

// Embed Moolah into the current content
function moolah_embed($content, $atts)
{

	$category   = @ $atts['category'];
	$product    = @ $atts['product'];
	$store      = @ $atts['store'];
    $target     = @ $atts['target'];
    $options    = get_option('moolah_options');

    // Fetch the store ID
    if ( ! $store )
    {
        $store      = $options['store'];
    }

    // One final check
    if ( ! $store ) {
        return $content;
    }

    // queue up admin ajax and styles
    $m = 'http://' . moolah_home() . '/' . $store;

    if ( ! $target ) {
        $target     = 'moolah';
    }

    $url    = sprintf('%s/js/load.js?category=%s&product=%s&target=%s&system=wordpress', $m, $category, $product,$target);
    $script = sprintf('<script type="text/javascript" src="%s" ></script>',$url);
    $return = sprintf("\n%s\n<div id=\"%s\"></div>\n", $script, $target);

    // Why does WP not allow this ?
    wp_enqueue_style('moolah-style');

    return $content.$return;
}


// Load the style sheet
add_action('wp_head', 'moolah_enqueue_style');
function moolah_enqueue_style()
{
    $url = plugins_url('moolah-e-commerce');
	wp_register_style('moolah-style',$url.'/moolah.css' );
}
