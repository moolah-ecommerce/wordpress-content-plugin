<?php

/*
 * Plugin Name: Moolah
 * Plugin URI: http://moolah-ecommerce.com
 * Version: 2.0.0
 * Description: Moolah E-Commerce
 * Plugin Author: Toby Patterson
 */

define('MOOLAH_VERSION', '2.0.0');

// Call function when plugin is activated
register_activation_hook(__FILE__, 'moolah_install');
// Action hook to initialize the plugin
add_action('admin_init', 'moolah_init');
// Action hook to register our option settings
add_action('admin_init', 'moolah_register_settings');
// Action hook to add the post products menu item
add_action('admin_menu', 'moolah_menu');
// Action hook to save the meta box data when the post is saved
add_action('save_post', 'moolah_save_meta_box');
// Action hook to create the post products shortcode
add_shortcode('moolah', 'moolah_shortcode');
// Action hook to create plugin widget
add_action('widgets_init', 'moolah_register_widgets');

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
	add_meta_box('moolah-meta', __('Post Product Information', 'moolah-plugin'), 'moolah_meta_box', 'post', 'side', 'default');
}

// create shortcode
function moolah_shortcode($atts, $content = null)
{
	global $post;
    $return = '';

	$category   = @ $atts['category'];
	$product    = @ $atts['product'];
	$store      = @ $atts['store'];
    $target     = @ $atts['target'];

    if ( ! $store )
    {
        $options    = get_option('moolah_options');
        $store      = $options['store_id'];
        $test       = $options['testing'];
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
	$moolah_category = get_post_meta($post->ID, 'moolah_category', true);
	$moolah_product = get_post_meta($post->ID, 'moolah_product', true);

	// display meta box form
	echo '<table>';
	echo '<tr>';
	echo '<td>' . __('Category', 'moolah-plugin') . ':</td><td><input type="text" name="moolah_category" value="' . esc_attr($moolah_category) . '" ></td>';
	echo '</tr><tr>';
	echo '<td>' . __('Product', 'moolah-plugin') . ':</td><td><input type="text" name="moolah_product" value="' . esc_attr($moolah_product) . '" ></td>';
	echo '</tr>';
	// display the meta box shortcode legend section
	echo '</table>';
	?>
<table>
	<tr>
		<td><?php echo  __('Category', 'moolah-plugin') ?></td>
		<td><input type="text" name="moolah_category" value="<?php echo esc_attr($moolah_product) ?>"/></td>
	</tr>
	<tr>
		<td><?php echo  __('Product', 'moolah-plugin') ?></td>
		<td><input type="text" name="moolah_product" value="<?php echo esc_attr($moolah_product) ?>"/></td>
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
	update_post_meta($post_id, 'moolah_category', esc_attr($_POST ['moolah_category']));
	update_post_meta($post_id, 'moolah_product', esc_attr($_POST ['moolah_product']));
}

// register our widget
function moolah_register_widgets()
{
	register_widget('moolah_widget');
}

// moolah_widget class
class moolah_widget extends WP_Widget
{
	// process our new widget
	function moolah_widget()
	{
		$widget_ops = array(
			'classname' => 'moolah_widget',
			'description' => __('Display Post Products', 'moolah-plugin')
		);
		$this->WP_Widget('moolah_widget', __('Post Products Widget', 'moolah-plugin'), $widget_ops);
	}

	// build our widget settings form
	function form($instance)
	{

	}

	// save our widget settings
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance ['title'] = strip_tags(esc_attr($new_instance ['title']));
		$instance ['number_products'] = intval($new_instance ['number_products']);
		return $instance;
	}

	// display our widget
	function widget($args, $instance)
	{

	}
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
	$store_id = $moolah_options ['store_id'];
	?>
<div class="wrap">
	<h2><?php _e('Moolah Options', 'moolah-plugin') ?></h2>

	<form method="post" action="options.php">
		<?php settings_fields('moolah-settings-group'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Store ID', 'moolah-plugin') ?></th>
				<td><input type="text" name="moolah_options[store_id]" value="<?php echo $store_id ?>" size="32"/></td>
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

add_action('wp_head', 'moolah_head');
function moolah_head()
{

}
