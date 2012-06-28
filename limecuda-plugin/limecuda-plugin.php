<?php
/*
Plugin Name: LimeCuda Custom Plugin
Plugin URI: http://limecuda.com
Description: Adds misc. tweaks for an initial install. deleting WP install posts, adds special links to the admin bar, shows page/post/category/tag ID in admin bar, disables self-pings.
Version: 0.5
Author: Blake Imeson
Author URI: http://limecuda.com
*/

//First use the add_action to add onto the WordPress menu.
add_action('admin_menu', 'limecuda_add_options');
//Make our function to call the WordPress function to add to the correct menu.
function limecuda_add_options() {
	add_submenu_page('options-general.php', 'LimeCuda Tweaks Plugin Options', 'LimeCuda Tweaks', 6, 'attachedoptions', 'limecuda_options_page');
}

function limecuda_options_page() {
      echo 'Coming Soon!';
}


function set_limecuda_defaults()
{
	global $wpdb;
	
	$o = array(
		'avatar_default'			=> 'blank',
		'avatar_rating'				=> 'G',
		'default_post_edit_rows'	=> 22,
		'timezone_string'			=> 'America/Detroit',
		'use_smilies'				=> 0,	
	);

	foreach ( $o as $k => $v )
	{
		update_option($k, $v);
	}
	
	// Delete dummy post and comment.
	wp_delete_post(1, TRUE);
	wp_delete_comment(1);
	
	// empty blogroll
	$wpdb->query("DELETE FROM $wpdb->links WHERE link_id != ''");

	return;
}
register_activation_hook(__FILE__, 'set_limecuda_defaults');


/**
 * Adds  links to the Admin bar
 */
 
 //Checks if we should add links to the bar.
function lc_admin_bar_init() {
	// Is the user sufficiently leveled, or has the bar been disabled?
	if (!is_super_admin() || !is_admin_bar_showing() )
		return;
	// Good to go, lets do this!
	add_action('admin_bar_menu', 'lc_admin_bar_links', 500);
}
// Get things running!
add_action('admin_bar_init', 'lc_admin_bar_init');

function lc_admin_bar_links() {
	global $wp_admin_bar;

	// Build Links.
	$url = 'http://'. $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$twitter = 'http://search.twitter.com/search?q='. urlencode($url);
	$mailurl = 'http://mail.'. $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$wmturl = 'https://www.google.com/webmasters/tools/dashboard?hl=en&siteUrl=http://'. $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 
	// Links to add, in the form: 'Label' => 'URL'
	$links = array(
		'LimeCuda' => 'http://limecuda.com',
		'Twitter Reactions' => $twitter,
		'Google Analytics' => 'http://www.google.com/analytics/',
		'Mail' => $mailurl,
		'Webmaster Tools' => $wmturl
	);
	
	// Add the Parent link.
	$wp_admin_bar->add_menu( array(
		'title' => 'Links',
		'href' => false,
		'id' => 'lc_links',
		'href' => false
	));
 
	/**
	 * Add the submenu links.
	 */
	foreach ($links as $label => $url) {
		$wp_admin_bar->add_menu( array(
			'title' => $label,
			'href' => $url,
			'parent' => 'lc_links',
			'meta' => array('target' => '_blank')
		));
	}
}
/*
Plugin Name: No Self Pings
Plugin URI: http://blogwaffe.com/2006/10/04/421/
Description: Keeps WordPress from sending pings to your own site.
Version: 0.2
Author: Michael D. Adams
Author URI: http://blogwaffe.com/

License: GPL2 - http://www.gnu.org/licenses/gpl.txt
*/

function no_self_ping( &$links ) {
	$home = get_option( 'home' );
	foreach ( $links as $l => $link )
		if ( 0 === strpos( $link, $home ) )
			unset($links[$l]);
}

add_action( 'pre_ping', 'no_self_ping' );
/*
 * Plugin Name: Admin Bar ID Menu
 * Plugin URI: http://www.tenseg.net/software/adminbarid
 * Description: Makes the ID number of the current page or post visible in the Admin Bar.
 * Version: 0.3
 * Author: Eric Celeste
 * Author URI: http://eric.clst.org/
 * License: GNU Lesser GPL 2.1 (http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt)
 */
/*
 * History:
 * v.0.3: 110224 (efc) moved the id into the edit item
                       appending $wp_admin_bar->menu->edit['title']
 * v.0.2: 110224 (efc) moved to a plugin
 *                     using the $wp_admin_bar->add_menu method
 * v.0.1: 110224 (efc) implemented in theme
 */
/*
 * Relevant WP core files:
 * http://core.trac.wordpress.org/browser/trunk/wp-includes/class-wp-admin-bar.php
 * http://core.trac.wordpress.org/browser/trunk/wp-includes/admin-bar.php
 */
 
function lc_admin_bar_menu() {
	global $wp_admin_bar;
	
	$current_object = get_queried_object();
	
	// IDs live in different places for posts or taxonomy items, why?
	if (! empty ( $current_object->post_type ) ) {
		$id = $current_object->ID;
	} elseif (! empty ( $current_object->taxonomy ) ) {
		$id = $current_object->term_id;
	} else {
		$id = "";
	}
	
	// update the menu title
	if( is_array($wp_admin_bar->menu->edit) && $id ) { // is there an edit menu?
		$wp_admin_bar->menu->edit['title'] .= " $id"; // then append the id
	}
}

// note that this action will fire late, 
// after $wp_admin_bar has been populated
add_action( 'admin_bar_menu', 'lc_admin_bar_menu', 95 );


//Adds a Move To Trash button on the admin bar
function fb_add_admin_bar_trash_menu() {
  global $wp_admin_bar;
  if ( !is_super_admin() || !is_admin_bar_showing() )
      return;
  $current_object = get_queried_object();
  if ( empty($current_object) )
      return;
  if ( !empty( $current_object->post_type ) &&
     ( $post_type_object = get_post_type_object( $current_object->post_type ) ) &&
     current_user_can( $post_type_object->cap->edit_post, $current_object->ID )
  ) {
    $wp_admin_bar->add_menu(
        array( 'id' => 'delete',
            'title' => __('Move to Trash'),
            'href' => get_delete_post_link($current_object->term_id)
        )
    );
  }
}
add_action( 'admin_bar_menu', 'fb_add_admin_bar_trash_menu', 35 );


/**
 * @package TSL iframe unfilter
 * @author Oleg Somphane
 * @version 1.0
 */
/*
Plugin Name: TSL iframe unfilter
Plugin URI: http://www.twoslowlorises.com/
Description: A simple iframe unfilter plugin.
Author: Oleg Somphane
Version: 1.0
Author URI: http://www.twoslowlorises.com/
*/

function unfilter_iframe($initArray) {
	$initArray['extended_valid_elements'] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
	return $initArray;
}

add_filter('tiny_mce_before_init', 'unfilter_iframe');





?>