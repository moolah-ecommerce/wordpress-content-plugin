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


// build post product meta box
function moolah_meta_box($post, $box)
{

    // retrieve our custom meta box values
    $showStore  = get_post_meta($post->ID, 'moolah_show', true);
    $categoryId = get_post_meta($post->ID, 'moolah_category',true);
    $productId  = get_post_meta($post->ID, 'moolah_product',true);

    $selected = $showStore ? 'checked="checked"' : '';
    ?>
<table>
    <tr>
        <td><?php echo  __('Show', 'moolah-plugin') ?></td>
        <td><input type="checkbox" name="moolah_show" value="1" <?php echo $selected ?> /></td>
    </tr>
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
    update_post_meta($post_id, 'moolah_show', (int) esc_attr($_POST['moolah_show']));
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
    $store  = $moolah_options['store'];
    $open   = $moolah_options['open'];

    $site   = 'http://moolah-ecommerce.com/sign-up';

    ?>
<div class="wrap">
    <div class="icon32 icon-settings" >&nbsp;</div>

    <h2><?php _e('Moolah E-Commerce', 'moolah-plugin') ?></h2>
    <?php

    if ( ! $store ) {
        //$store = 2642953450;
        $anchor = '<a href="%s" title="Moolah E-Commerce" target="_blank">%s</a>';
        $link   = sprintf($anchor,$site,$site);
        $msg    = __('Enter your Store ID below. If you do not have one, you can register for a free account at %s.');

        echo '<p>'.sprintf($msg,$link).'</p>';
    }

    ?>

    <form method="post" action="options.php">
        <?php settings_fields('moolah-settings-group'); ?>
        <table class="form-table" style="width:440px;">
            <tr valign="top">
                <th scope="row"><?php _e('Store ID', 'moolah-plugin') ?></th>
                <td><input type="text" name="moolah_options[store]" value="<?php echo $store ?>" size="12"/></td>
                <td>
                    <select name="moolah_options[open]" >
                        <option value="window" <?php if ($open == 'window') echo 'selected="selected"' ?> ><?php echo __('New Window') ?></option>
                        <option value="iframe" <?php if ($open == 'iframe') echo 'selected="selected"' ?> ><?php echo __('Current page') ?></option>
                    </select>
                </td>
                <td class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes', 'moolah-plugin') ?>"/></td>
            </tr>
        </table>
    </form>

</div>
<?php

    if ( $store ) {
        $home = moolah_home();
        $openUrl = "http://$home/$store/manage";

        if ( $open == 'iframe') {
            $iframeArgs = 'style="overflow:hidden;height:700px;width:100%" height="700px" width="100%"';
            $openHtml = sprintf('<iframe src="%s" %s></iframe>',$openUrl,$iframeArgs);
        } else {
            $openJs = "window.open('$openUrl','new_window'); return false";
            $openClass = 'button button-primary button-hero';
            $openStyle = 'display:block; text-align:center; height:40px; width:200px; margin-left: 100px;';
            $openText = __('View Your Store');
            $openHtml = sprintf('<p><a href="#" onclick="%s" class="%s" style="%s">%s</a></p>',$openJs,$openClass,$openStyle,$openText);
        }

        echo $openHtml;

    } else {
        echo "<br/>";
    }

    echo __('In your WordPress post, simply insert the code <strong>[moolah]</strong> into the post to load your store.');

}