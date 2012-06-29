<?php
/**
 * @package WP_Jump_Menu
 * @author Jim Krill
 * @version 2.4.3
 */
/*
Plugin Name: WP Jump Menu
Plugin URI: http://www.synotac.com/wp-jump-menu/
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Author: Jim Krill
Version: 2.4.3
Author URI: http://krillwebdesign.com
*/

/*  Copyright 2011  Jim Krill  (email : jimkrill@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
 TODO:
 * perhaps add an option to sort posts in the drop down by status and date e.g. all Published grouped together, all Future grouped etc
 * perhaps include date of publication (whether past or future) with posts in dropdown. Or, might be better to show dates for Scheduled posts only? (just a thought).
 * Allow the user to select the color to use for the different post status'
 */


/** CUSTOM FUNCTIONS **/
/**********************/

require_once( WP_PLUGIN_DIR . '/wp-jump-menu/settings.php' );

define('WPJM_VERSION','2.4.3');

global $wp_version;

register_activation_hook( __FILE__, 'wpjm_install' );

// Needs uninstall hook to delete files
	

	add_action( 'admin_print_scripts','wpjm_js' );
	add_action( 'admin_print_styles', 'wpjm_editpost_css' );
	add_filter( 'plugin_action_links', 'wpjm_add_settings_link', 10, 2);
	add_action( 'admin_menu', 'wpjm_menu' );

	// Testing tooltip
	function wpjm_enqueue_tooltips() {
		$dismissed_tooltips = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( !in_array('wpjm_tooltip',$dismissed_tooltips) )
			add_action( 'admin_print_footer_scripts', 'wpjm_tooltip' );

	}
	if ( version_compare($wp_version, '3.2.1', '>'))
		add_action('admin_enqueue_scripts','wpjm_enqueue_tooltips');

	// Options page settings form
	add_action( 'admin_init', 'wpjm_admin_init' );

	register_deactivation_hook( __FILE__, 'wpjm_uninstall' );


// Call the plugin's main functions
function beam_me_up_wpjm() {

	$options = get_option( 'wpjm_options' );

	if ( ($options['position'] == 'wpAdminBar') ) { 
		add_action('admin_bar_menu', 'wpjm_add_admin_bar',25);
		add_action( 'wp_print_scripts','wpjm_js' );
		add_action( 'wp_print_scripts', 'wpjm_editpost_css' );

	} else {
		add_action( 'admin_footer', 'wpjm_custom_footer' );
	}


	$current_version = get_option('wpjm_version');
	if (empty($current_version) || $current_version < WPJM_VERSION) {
		wpjm_install();
		add_action( 'admin_print_footer_scripts', 'wpjm_tooltip' );

		$dismissed_tooltips = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( in_array( 'wpjm_tooltip', $dismissed_tooltips)) {
			foreach($dismissed_tooltips as $key => $value) {
				if ($value == 'wpjm_tooltip') unset($dismissed_tooltips[$key]);
			}
			if (is_array($dismissed_tooltips))
				$dismissed_tooltips = implode( ',', $dismissed_tooltips );
			update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed_tooltips );
		}
		
	}


	

}

/**
 * Add Settings link to plugin listing
 */
function wpjm_add_settings_link( $links, $file ) {
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=wpjm-options">'.__("Settings").'</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

// CSS needed for the jump menu
// Places the menu on the top or the bottom of the screen (depending on options)
function wpjm_editpost_css() {

	// Get the options
	$options = get_option( 'wpjm_options' );

	echo "
	<link rel='stylesheet' href='".plugins_url()."/wp-jump-menu/assets/js/colorpicker/css/colorpicker.css' type='text/css' />
	<style type='text/css'>
	#jump_menu { position: fixed; ".$options['position'].": ".($options['position']=='top'?(is_admin_bar_showing()?"28px":"0"):"0")."; left: 0; height: 40px; overflow: hidden; background: #".$options['backgroundColor']."; color: #".$options['fontColor']."; width: 100%; z-index: 1500; border-".($options['position']=='top'?'bottom':'top').": 2px solid #".$options['borderColor']."; }
	#jump_menu p { padding: 5px 15px; font-size: 12px; margin: 0; }
	#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover { color: #".$options['linkColor']."; text-decoration: none; }
	#jump_menu p.wpjm_need_help { float: right; text-align: right; }
	#jump_menu p.wpjm_need_help span.wpjm-logo-title { font-family: Georgia; font-style: italic; padding-right: 10px; }
	#jump_menu p.jm_credits { font-style: italic; padding-top: 10px; line-height: 13px; }
	#jump_menu p.jm_credits img.wpjm_logo { ".($options['logoWidth']?'width: '.$options['logoWidth'].'px;':'width: 35px;')." height: auto; max-height: 30px; vertical-align: middle; margin-right: 10px; }
	#jump_menu_clear { height: 30px; }
	body { ".($options['position']=='top'?'padding-top: 42px !important;':'padding-bottom: 42px !important;')." }
	".($options['position']=='bottom'?'#footer { bottom: 42px !important; }':'')
	."
	#wp-pdd { max-width: 400px;  }
	#wpadminbar #wp-pdd, #wpadminbar #wp-pdd * { color: #333 !important; text-shadow: none;}
	#wpadminbar span.wpjm-logo-title { padding-right: 10px; }
		@media only screen and (max-width: 960px) {
			#wpadminbar span.wpjm-logo-title { display: none; }
			
		}
		@media only screen and (max-width: 800px) {
			#wpadminbar #wp-pdd { width: 150px; }
		}
	#wpadminbar #wp-jump-menu { padding: 0px 10px; }
	</style>
	<!--[if IE 6]>
	<style type='text/css'>
	#jump_menu { position: relative; }
	#jump_menu_clear { display: none; }
	</style>
	<![endif]-->
	";

}

// Some javascript to help the edit drop down list work
function wpjm_js() {

	// jquery ui - sortable
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'wpjm-jquery-ui-position', plugins_url().'/wp-jump-menu/assets/js/jquery.ui.position.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ) );
	// jqueryfunctions.js (general jquery scripts) & jquery
	wp_enqueue_script( 'wpjm-jquery-functions', plugins_url().'/wp-jump-menu/assets/js/jqueryfunctions.js', array( 'jquery' ) );
	// colorpicker.js - used for the color picker
	wp_enqueue_script( 'wpjm-jquery-colorpicker', plugins_url().'/wp-jump-menu/assets/js/colorpicker/js/colorpicker.js', array( 'jquery' ) );


	// Testing tooltip
	wp_enqueue_style('wp-pointer');
	wp_enqueue_script('wp-pointer');

}

// Testing Tooltip
function wpjm_tooltip(){

	// Get the options
	$options = get_option( 'wpjm_options' );

	$pointer_content = '<h3>New in Wp Jump Menu '.WPJM_VERSION.'</h3>';
	$pointer_content .= '<p>Now you can attach the jump menu to the WordPress admin bar! Goto <a href="options-general.php?page=wpjm-options">WPJM settings</a> and change location to WP Admin Bar</p>';
	// $pointer_content .= '<p><a href="#" id="wpjm-tooltip-close">Dismiss</a></p>';
	?>
	<script>
	jQuery(document).ready(function($){
		$('<?php echo ($options["position"] == "wpAdminBar"?"#wp-admin-bar-wp-jump-menu":"#jump_menu"); ?>').pointer({
			content: '<?php echo addslashes( $pointer_content ); ?>',
			<?php if ($options['position'] == 'top') { ?>
			position: {
				offset: '0 0',
				edge: 'top',
				align: 'center'
			},
			<?php } else if ($options['position'] == 'bottom') { ?>
			position: {
				offset: '0 -15',
				edge: 'bottom',
				align: 'center'
			},
			<?php } ?>
			close: function() {
				// Once the close button is hit
				$.post(ajaxurl, {
					pointer: 'wpjm_tooltip',
					action: 'dismiss-wp-pointer'
				});
			}
		}).pointer('open');
	});
	</script>
	<?php

}

// Put a bar across the bottom of the screen that offers to help...
// This is the plugins display function that shows the jump menu bar
function wpjm_custom_footer() {

	// Get the options
	$options = get_option( 'wpjm_options' );
	

	echo '<div id="jump_menu">';
		echo '<p class="wpjm_need_help">';
		
			echo '<span class="wpjm-logo-title">WP Jump Menu &raquo;</span>';
			// Jump to page edit
			echo wpjm_page_dropdown();

		echo '</p>';
		echo '<p class="jm_credits">';
			echo ( !empty($options['logoIcon']) ? '<a href="'.get_bloginfo( 'url' ).'"><img class="wpjm_logo" src="'.$options['logoIcon'].'" alt="" /></a>' : '');
			echo $options['message'];
			//echo ' Go to your <a href="'.get_bloginfo( 'url' ).'">site</a>.';
		echo '</p>';
	echo '</div>';

}

function wpjm_add_admin_bar() {
		global $wp_admin_bar;

		if (is_admin_bar_showing()) {

		$html = '<span class="wpjm-logo-title">WP Jump Menu &raquo;</span>';
		$html .= wpjm_page_dropdown();

		 $wp_admin_bar->add_menu( array(
	                'id'    => 'wp-jump-menu',
	                'parent'    => 'top-secondary',
	                'title' => $html
	        ) );

	    }
}


function wpjm_page_dropdown(){
	
	require_once( ABSPATH . 'wp-load.php' );

	// Get the options
	$options = get_option( 'wpjm_options' );

	// Page Title Function
	if ( ! function_exists( 'wpjm_get_page_title' ) ) {
		function wpjm_get_page_title ($pd_title) {
			if ( strlen($pd_title) > 50 ) {
				return substr( $pd_title, 0, 50)."...";
			} else {
				return $pd_title;
			}
		}
	}

	// Get Custom Post Types settings (will iterate through later)
	$custom_post_types = $options['postTypes'];

	// Set post status colors
	$status_color = array(
		'publish' => (!empty($options['statusColors']['publish'])?'#'.$options['statusColors']['publish']:'#000000'),
		'pending' => (!empty($options['statusColors']['pending'])?'#'.$options['statusColors']['pending']:'#999999'),
		'draft' => (!empty($options['statusColors']['draft'])?'#'.$options['statusColors']['draft']:'#999999'),
		'auto-draft' => (!empty($options['statusColors']['auto-draft'])?'#'.$options['statusColors']['auto-draft']:'#999999'),
		'future' => (!empty($options['statusColors']['future'])?'#'.$options['statusColors']['future']:'#398f2c'),
		'private' => (!empty($options['statusColors']['private'])?'#'.$options['statusColors']['private']:'#999999'),
		'inherit' => (!empty($options['statusColors']['inherit'])?'#'.$options['statusColors']['inherit']:'#333333'),
		'trash' => (!empty($options['statusColors']['trash'])?'#'.$options['statusColors']['trash']:'#ff0000')
		);

	$wpjm_string = '';

	// Start echoing the select menu
	$wpjm_string .= '<select id="wp-pdd">';
	$wpjm_string .= '<option>-- Select a post/page to edit it --</option>';

	// Loop through custom posts types, and echo them out
	if ($custom_post_types) {
	
		//$wpjm_cpts = explode(',',$custom_post_types);
		$wpjm_cpts = $custom_post_types; // should be array
		if ($wpjm_cpts) {
			// foreach($wpjm_cpts as $wpjm_cpt) {

				// Custom Walker Class to walk through the page/custom post type hierarchy tree
				class WPJM_Walker_PageDropDown extends Walker_PageDropDown {

					var $tree_type = "page";

					function start_el(&$output, $page, $depth, $args) {
						
						// Get options to determine whether or not to show ID
						$options = get_option( 'wpjm_options' );

						$status_color = array(
						'publish' => (!empty($options['statusColors']['publish'])?'#'.$options['statusColors']['publish']:'#000000'),
						'pending' => (!empty($options['statusColors']['pending'])?'#'.$options['statusColors']['pending']:'#999999'),
						'draft' => (!empty($options['statusColors']['draft'])?'#'.$options['statusColors']['draft']:'#999999'),
						'auto-draft' => (!empty($options['statusColors']['auto-draft'])?'#'.$options['statusColors']['auto-draft']:'#999999'),
						'future' => (!empty($options['statusColors']['future'])?'#'.$options['statusColors']['future']:'#398f2c'),
						'private' => (!empty($options['statusColors']['private'])?'#'.$options['statusColors']['private']:'#999999'),
						'inherit' => (!empty($options['statusColors']['inherit'])?'#'.$options['statusColors']['inherit']:'#333333'),
						'trash' => (!empty($options['statusColors']['trash'])?'#'.$options['statusColors']['trash']:'#ff0000')
						);

						$pad = str_repeat('-', $depth * 2);

						$output .= "\t<option class=\"level-$depth\" value=\"".get_edit_post_link($page->ID)."\"";
						if (isset($_GET['post']) && ($page->ID == $_GET['post']))
							$output .= ' selected="selected"';

							$output .= ' style="color: '.$status_color['publish'].' !important;"';
						$output .= '>';
						$title = apply_filters( 'list_pages', $page->post_title . ( $options['showID'] == true ? " (" .$page->ID . ") " : '' ) );
						$output .= $pad . esc_html( $title );
						$output .= "</option>\n";
					}
				}
				// end WPJM_Walker_PageDropDown class

			// Loop through each post type as $key, $value
			// --------------------------------------------------------------------------------------
			// The $key is the name of the post type: i.e. 'page', 'post', or 'custom_post_type_name'
			// The $value is an array of options
			//		$value['sortby']
			//		$value['sort']
			//		$value['numberposts']
			// --------------------------------------------------------------------------------------
			foreach($wpjm_cpts as $key => $value ) {
				
				// Set variables
				$wpjm_cpt = $key;						// name of the post type
				$sortby = $value['sortby'];				// orderby value
				$sort = $value['sort'];					// order value
				$numberposts = $value['numberposts'];	// number of posts to display
				$showdrafts = $value['showdrafts'];		// show drafts, true or false
				$post_status = $value['poststatus'];

				
				// Get Posts
				// Attempting to use wp_cache
				$cache_name = "wpjm_{$wpjm_cpt}_post";
				$pd_posts = wp_cache_get( $cache_name, "wpjm_cache" );
				if ( false == $pd_posts ) {
					$args = array(
						'orderby' => $sortby,
						'order' => $sort,
						'numberposts' => $numberposts,
						'post_type' => $wpjm_cpt,
						'post_status' => (is_array($post_status)?(in_array('any',$post_status)?'any':$post_status):$post_status)
						);
					$pd_posts = get_posts($args);

					wp_cache_set( $cache_name, $pd_posts, "wpjm_cache" );
				}

				// Count the posts
				$pd_total_posts = count($pd_posts);
				
				// Get the labels for this post type
				$cpt_obj = get_post_type_object($wpjm_cpt);
				$cpt_labels = $cpt_obj->labels;
				
				// Set the iterator to zero
				$pd_i = 0;

				

				// If this is not hierarchical, get list of posts and display the <option>s
				if (!is_post_type_hierarchical($wpjm_cpt)) {
					
					$wpjm_string .= '<optgroup label="--'.$cpt_labels->name.'--">';

					if ($cpt_labels->name != 'Media') {

						if ($options['showaddnew']) {
							$wpjm_string .= '<option value="post-new.php?post_type=';
							$wpjm_string .= $cpt_obj->name;
							$wpjm_string .= '">--Add New '.$cpt_labels->singular_name.'--</option>';
						}

					}

					// Loop through posts
					foreach ($pd_posts as $pd_post) {
						
						// Increase the interator by 1
						$pd_i++;

						// Open the <option> tag
						$wpjm_string .= '<option value="';
							// echo the edit link based on post ID
							$wpjm_string .= get_edit_post_link($pd_post->ID);
						$wpjm_string .= '"';

						// Check to see if you are currently editing this post
						// If so, make it the selected value
						if (isset($_GET['post']) && ($pd_post->ID == $_GET['post']))
							$wpjm_string .= ' selected="selected"';

						// Set the color
						$wpjm_string .= ' style="color: '.$status_color[$pd_post->post_status].' !important;"';

						$wpjm_string .= '>';

						// Print the post title
						$wpjm_string .= wpjm_get_page_title($pd_post->post_title);
						
						if ($pd_post->post_status != 'publish')
							$wpjm_string .= ' - '.$pd_post->post_status;

						if ($pd_post->post_status == 'future')
							$wpjm_string .= ' - '.$pd_post->post_date;

						// If the setting to show ID's is true, show the ID in ()
						if ($options['showID'] == true) 
							$wpjm_string .= ' ('.$pd_post->ID.') ';
						
						// close the <option> tag
						$wpjm_string .= '</option>';
					} // foreach ($pd_posts as $pd_post)

					$wpjm_string .= '</optgroup>';

				} else {
										
					// If this a hierarchical post type, use the custom Walker class to create the page tree
					$orderedListWalker = new WPJM_Walker_PageDropDown();

					$wpjm_string .= '<optgroup label="--'.$cpt_labels->name.'--">';

					if ($options['showaddnew']) {
						$wpjm_string .= '<option value="post-new.php?post_type=';
						$wpjm_string .= $cpt_obj->name;
						$wpjm_string .= '">--Add New '.$cpt_labels->singular_name.'--</option>';
					}
					
					// Go through the non-published pages
					foreach ($post_status as $status) {

						if ($status == 'publish')
							continue;

						// Get pages
						$pd_posts_drafts = get_posts('orderby='.$sortby.'&order='.$sort.'&numberposts='.$numberposts.'&post_type='.$wpjm_cpt.'&post_status='.$status);
						
						
						// Loop through posts
						foreach ($pd_posts_drafts as $pd_post) {
							
							// Increase the interator by 1
							$pd_i++;

							// Open the <option> tag
							$wpjm_string .= '<option value="';
								// echo the edit link based on post ID
								$wpjm_string .= get_edit_post_link($pd_post->ID);
							$wpjm_string .= '"';

							// Check to see if you are currently editing this post
							// If so, make it the selected value
							if (isset($_GET['post']) && ($pd_post->ID == $_GET['post']))
								$wpjm_string .= ' selected="selected"';

							// Set the color
							$wpjm_string .= ' style="color: '.$status_color[$pd_post->post_status].' !important;"';

							$wpjm_string .= '>';

							// Print the post title
							$wpjm_string .= wpjm_get_page_title($pd_post->post_title);
							
							if ($pd_post->post_status != 'publish')
								$wpjm_string .= ' - '.$status;

							if ($pd_post->post_status == 'future')
								$wpjm_string .= ' - '.$pd_post->post_date;

							// If the setting to show ID's is true, show the ID in ()
							if ($options['showID'] == true) 
								$wpjm_string .= ' ('.$pd_post->ID.') ';
							
							// close the <option> tag
							$wpjm_string .= '</option>';
						} // foreach ($pd_posts as $pd_post)

						
					} 
					// Done with non-published pages
					if (is_array($post_status)) {

						if (in_array('publish',$post_status)) {
						
							$wpjm_string .= wp_list_pages(array('walker' => $orderedListWalker, 'post_type' => $wpjm_cpt, 'echo' => 0, 'depth' => 0));

						}

					} else if ($post_status == 'publish') {
						$wpjm_string .= wp_list_pages(array('walker' => $orderedListWalker, 'post_type' => $wpjm_cpt, 'echo' => 0, 'depth' => 0));
					}
					

					$wpjm_string .= '</optgroup>';
				} // end if (is_hierarchical)
			
			} // end foreach($wpjm_cpts)

		} // end if ($wpjm_cpts)
	
	} // end if ($custom_post_types)
	
	// Print the options page link
	$wpjm_string .= '<optgroup label="-- WP Jump Menu Options --">';
	$wpjm_string .= '<option value="options-general.php?page=wpjm-options">Jump Menu Options Page</option>';
	$wpjm_string .= '</optgroup>';

	// Close the select drop down
	$wpjm_string .= '</select>';

	return $wpjm_string;

} // end wpjm_page_dropdown() 


/* Plugin Installation Function */
function wpjm_install() {

	// Populate with default values
	if (get_option('wpjm_position')) {

		$newPostTypes = array(
				'page' => array(
					'show' => '1',
					'sortby' => 'menu_order',
					'sort' => 'ASC',
					'poststatus' => array('publish','draft')
				),
				'post' => array(
					'show' => '1',
					'sortby' => 'date',
					'sort' => 'DESC',
					'poststatus' => array('publish','draft')
				)
			);

		// Get old custom post types option, append to new variable
		$customPostTypes = get_option('wpjm_customPostTypes');
		$cpt_arr = explode(',',$customPostTypes);
		if (!empty($cpt_arr)) {
			if (is_array($cpt_arr)) {
				foreach($cpt_arr as $cpt) {
					$newPostTypes[$cpt] = array(
						'show' => '1',
						'sortby' => 'menu_order',
						'sort' => 'ASC',
						'numberposts' => '-1',
						'poststatus' => array('publish','draft')
						);
				}
			} else {
				$newPostTypes[$cpt_arr] = array(
					'show' => '1',
					'sortby' => 'menu_order',
					'sort' => 'ASC',
					'numberposts' => '-1',
					'poststatus' => array('publish','draft')
				);
			}
		}
		
		$arr = array(
			'position' => get_option('wpjm_position'),
			'showID' => 'false',
			'showaddnew' => 'true',
			'backgroundColor' => get_option('wpjm_backgroundColor'),
			'fontColor' => get_option('wpjm_fontColor'),
			'borderColor' => get_option('wpjm_borderColor'),
			'postTypes' => $newPostTypes,
			'logoIcon' => get_option('wpjm_logoIcon'),
			'logoWidth' => get_option('wpjm_logoWidth'),
			'linkColor' => get_option('wpjm_linkColor'),
			'message' => get_option('wpjm_message') 
		);

		update_option('wpjm_options',$arr);

		delete_option('wpjm_position');
		delete_option('wpjm_sortpagesby');
		delete_option('wpjm_sortpages');
		delete_option('wpjm_sortpostsby');
		delete_option('wpjm_sortposts');
		delete_option('wpjm_numberposts');
		delete_option('wpjm_backgroundColor');
		delete_option('wpjm_fontColor');
		delete_option('wpjm_borderColor');
		delete_option('wpjm_customPostTypes');
		delete_option('wpjm_logoIcon');
		delete_option('wpjm_logoWidth');
		delete_option('wpjm_linkColor');
		delete_option('wpjm_message');
	
	} else {
		$options = get_option('wpjm_options');
		if (empty($options)) {
			$arr = array(
				'position' => 'wpAdminBar',
				'showID' => 'false',
				'showaddnew' => 'true',
				'backgroundColor' => 'e0e0e0',
				'fontColor' => '787878',
				'borderColor' => '666666',
				'postTypes' => array(
					'page' => array(
						'show' => '1',
						'sortby' => 'menu_order',
						'sort' => 'ASC',
						'numberposts' => '-1',
						'poststatus' => array('publish','draft')
					),
					'post' => array(
						'show' => '1',
						'sortby' => 'date',
						'sort' => 'DESC',
						'numberposts' => '-1',
						'poststatus' => array('publish','draft')
					)
				),
				'logoIcon' => 'http://www.krillwebdesign.com/wp-content/uploads/2011/06/logo-small-no-tag1.png',
				'logoWidth' => '0',
				'linkColor' => '1cd0d6',
				'message' => "Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>." 
			);
			update_option('wpjm_options',$arr);
		} else {
			if (!isset($options['postTypes']['post']['poststatus'])) {
				foreach($options['postTypes'] as $key => $value) {
					$options['postTypes'][$key]['poststatus'] = array('publish','draft');
				}
				update_option('wpjm_options',$options);
			}
		}

	}

	update_option('wpjm_version',WPJM_VERSION);

}


// Uninstall
function wpjm_uninstall() {
	delete_option('wpjm_options');
}

// Add the WPJM Menu
function wpjm_menu() {

	add_options_page('Jump Menu Options','Jump Menu Options', 8, 'wpjm-options', 'wpjm_options');

}


/**
 * The options page
 */
function wpjm_options() {

	// Update success message 
	if ( isset( $_POST['save_post_page_values'] ) ) {
		$message = "Options updated successfully!";
	}

?>


<?php if ($message) : ?>
	<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32">
			<br/>
		</div>
		<h2>WP Jump Menu <?php echo WPJM_VERSION; ?></h2>	

		<form action="options.php" method="post" id="wpjm-options-form">
		<?php settings_fields('wpjm_options'); ?>
		<?php do_settings_sections('wpjm'); ?>
		<p class="submit">
			<input type="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" />
		</p>
		<?php do_settings_sections('wpjm-2'); ?>

		<?php
		// Clear the cache when viewing the options page //
		wp_cache_delete('','wpjm_cache');
		?>

		<p class="submit">
			<input type="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" />
		</p>
		</form>
	</div>

<?php
}

// Launch in 5... 4... 3... 2... 1...
add_action('admin_init', 'beam_me_up_wpjm');
?>