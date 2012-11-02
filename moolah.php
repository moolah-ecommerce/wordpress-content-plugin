<?php

/*
 * Plugin Name: Moolah
 * Plugin URI: http://moolah-ecommerce.com
 * Version: 2.0.0
 * Description: Moolah E-Commerce
 * Author: Moolah E-Commerce
 * Author Email: dev@moolah-ecommerce.com
 * Author URI: http://moolah-ecommerce.com
 */

define('MOOLAH_VERSION', '2.0.0');

// Action hook to initialize the plugin
add_action('admin_init', 'moolah_init');
// Action hook to register our option settings
add_action('admin_init', 'moolah_register_settings');

// Action hook to save the meta box data when the post is saved
add_action('save_post', 'moolah_save_meta_box');
// Action hook to create the post products shortcode
add_shortcode('moolah', 'moolah_shortcode');

// Require the administrative file
if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/admin.php';
}

function moolah_home($test=false)
{
	static $home;

	if (!$home) {
		$local = $_SERVER['HTTP_HOST'];

        if ( $local == 'mec-demo' ) {
            //$home = $test ? 'mec-test' : 'mec-store';
            $home = 'mec-test';
        } else
        if ( $local == 'demo.moolah-ecommerce.com' ) {
            $home = 'test.moolah-ecommerce.com';
        } else {
            $home = $test ? 'test.moolah-ecommerce.com' : 'store.moolah-ecommerce.com';
        }

	}

	return $home;
}

// create post meta box
function moolah_init()
{
	// create our custom meta box
	static $already;
    if ( ! $already ) {
        add_meta_box('moolah-meta', __('Moolah Store', 'moolah-plugin'), 'moolah_meta_box', 'post', 'side', 'default');
        $already = true;
    }
}

// Parse the shortcode
function moolah_shortcode($atts, $content = null)
{
	global $post;

	$category   = @ $atts['category'];
	$product    = @ $atts['product'];
	$store      = @ $atts['store'];
    $target     = @ $atts['target'];
    $options    = get_option('moolah_options');

    // Fetch the store ID
    if ( ! $store )
    {
        $store      = $options['store'];
        $test       = $options['test'];
    }

    // Perhaps a category was provided in the metadata ?
    if ( ! $category ) {
        $category = get_post_meta($post->ID, 'moolah_category', true);

    }

    // Perhaps a product was provided in the metadata ?
    if ( ! $product ) {
        $product = get_post_meta($post->ID, 'moolah_product', true);
    }

    // queue up admin ajax and styles
    $m = 'http://' . moolah_home($test) . '/' . $store;
    $l = $m . '/extjs/';
    $c = $m . '/css';
    $j = $m . '/js';
    $version = MOOLAH_VERSION;



    if ( ! $target ) {
        $target     = 'moolah';
    }

    $url    = sprintf('%s/js/load.js?category=%s&product=%s&target=%s', $m, $category, $product,$target);
    $script = sprintf('<script type="text/javascript" src="%s" ></script>',$url);
    $return = sprintf("\n%s\n<div id=\"%s\"></div>\n", $script, $target);

    return $return;
}

// build post product meta box
function moolah_meta_box($post, $box)
{

	// retrieve our custom meta box values
    $categoryId = get_post_meta($post->ID, 'moolah_category', true);
	$productId = get_post_meta($post->ID, 'moolah_product', true);

    ?>
<table>
	<tr>
		<td><?php echo  __('Category', 'moolah-plugin') ?></td>
		<td><input type="text" name="moolah_category" value="<?php echo esc_attr($categoryId) ?>"/></td>
	</tr>
	<tr>
		<td><?php echo  __('Product', 'moolah-plugin') ?></td>
		<td><input type="text" name="moolah_product" value="<?php echo esc_attr($productId) ?>"/></td>
	</tr>
</table>
<?php
}

// save meta box data
function moolah_save_meta_box($post_id, $post=null)
{
	// if post is a revision skip saving our meta box data
	if ($post->post_type == 'revision') {
		return;
	}
	// process form data if $_POST is set
	update_post_meta($post_id, 'moolah_category', (int) esc_attr($_POST['moolah_category']));
	update_post_meta($post_id, 'moolah_product', (int) esc_attr($_POST['moolah_product']));
}

function moolah_register_settings()
{
	// register our array of settings
	register_setting('moolah-settings-group', 'moolah_options');
}

function moolah_settings_page()
{
	// load our options array
	$moolah_options = get_option('moolah_options');
	// if the show inventory option exists the checkbox needs to be checked
	$store = $moolah_options ['store'];

    $msg = 'Enter your Store ID below. If you do not have one, you can register for a free account at <a href="%s" title="Moolah E-Commerce" target="_blank">%s</a>.';
    $site = 'http://moolah-ecommerce.com/sign-up';
    $msg = sprintf(__($msg),$site,$site);
	?>
<div class="wrap">
    <div class="icon32 icon-settings" >&nbsp;</div>

    <h2><?php _e('Moolah E-Commerce', 'moolah-plugin') ?></h2>

    <p><?php echo $msg ?></p>
    <form method="post" action="options.php">
        <?php settings_fields('moolah-settings-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Store ID', 'moolah-plugin') ?></th>
                <td><input type="text" name="moolah_options[store]" value="<?php echo $store ?>" size="32"/></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'moolah-plugin') ?>"/>
        </p>
    </form>

</div>
<?php
}


add_action('wp_enqueue_scripts', 'moolah_enqueue_scripts');

function moolah_enqueue_scripts()
{
	wp_register_style('moolah-style', plugins_url('moolah.css', __FILE__));
	wp_enqueue_style('moolah-style');
}

/*
add_action('wp_head', 'moolah_head');
function moolah_head()
{

}
*/
