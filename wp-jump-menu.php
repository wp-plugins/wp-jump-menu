<?php
/**
 * @package WP_Jump_Menu
 * @author Jim Krill
 * @version 2.1.4
 */
/*
Plugin Name: WP Jump Menu
Plugin URI: http://www.synotac.com/wp-jump-menu/
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Author: Jim Krill
Version: 2.1.4
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


/** CUSTOM FUNCTIONS **/
/**********************/

require_once( WP_PLUGIN_DIR . '/wp-jump-menu/settings.php' );

define('WPJM_VERSION','2.1.4');

// Call the plugin's main functions
function beam_me_up_wpjm() {

	register_activation_hook( __FILE__, 'wpjm_install' );

	$current_version = get_option('wpjm_version');
	if (empty($current_version) || $current_version < WPJM_VERSION) {
		wpjm_install();
	}

	// Needs uninstall hook to delete files
	
	add_action( 'admin_footer', 'wpjm_custom_footer' );
	add_action( 'admin_print_scripts','wpjm_js' );
	add_action( 'admin_print_styles', 'wpjm_editpost_css' );
	add_filter( 'plugin_action_links', 'wpjm_add_settings_link', 10, 2);
	add_action( 'admin_menu', 'wpjm_menu' );

	// Options page settings form
	add_action( 'admin_init', 'wpjm_admin_init' );

	register_deactivation_hook( __FILE__, 'wpjm_uninstall' );

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
	<link rel='stylesheet' href='".get_option('siteurl')."/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/css/colorpicker.css' type='text/css' />
   <!-- <link rel='stylesheet' media='screen' type='text/css' href='".get_option('siteurl')."/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/css/layout.css' /> -->
	<style type='text/css'>
	#jump_menu { position: fixed; ".$options['position'].": ".(is_admin_bar_showing()?"28px":"0")."; left: 0; height: 40px; overflow: hidden; background: #".$options['backgroundColor']."; color: #".$options['fontColor']."; width: 100%; z-index: 1500; border-".($options['position']=='top'?'bottom':'top').": 2px solid #".$options['borderColor']."; }
	#jump_menu p { padding: 5px 15px; font-size: 12px; margin: 0; }
	#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover { color: #".$options['linkColor']."; text-decoration: none; }
	#jump_menu p.wpjm_need_help { float: right; text-align: right; }
	#jump_menu p.wpjm_need_help span.wpjm-logo-title { font-family: Georgia; font-style: italic; padding-right: 10px; }
	#jump_menu p.jm_credits { font-style: italic; padding-top: 10px; line-height: 13px; }
	#jump_menu p.jm_credits img.wpjm_logo { ".($options['logoWidth']?'width: '.$options['logoWidth'].'px;':'width: 35px;')." height: auto; max-height: 30px; vertical-align: middle; margin-right: 10px; }
	#jump_menu_clear { height: 30px; }
	body { ".($options['position']=='top'?'padding-top: 42px !important;':'padding-bottom: 42px !important;')." }
	#wp-pdd { max-width: 400px;  }
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
	// jqueryfunctions.js (general jquery scripts) & jquery
	wp_enqueue_script( 'jquery-functions', get_option( 'siteurl' ).'/wp-content/plugins/wp-jump-menu/assets/js/jqueryfunctions.js', array( 'jquery' ) );
	// colorpicker.js - used for the color picker
	wp_enqueue_script( 'jquery-colorpicker', get_option( 'siteurl' ).'/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/js/colorpicker.js', array( 'jquery' ) );


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
			wpjm_page_dropdown();

		echo '</p>';
		echo '<p class="jm_credits">';
			echo ( !empty($options['logoIcon']) ? '<a href="'.get_bloginfo( 'url' ).'"><img class="wpjm_logo" src="'.$options['logoIcon'].'" alt="" /></a>' : '');
			echo $options['message'];
			//echo ' Go to your <a href="'.get_bloginfo( 'url' ).'">site</a>.';
		echo '</p>';
	echo '</div>';

}


function wpjm_page_dropdown(){
	
	require_once( ABSPATH . 'wp-config.php' );

	// Get the options
	$options = get_option( 'wpjm_options' );

	// Drop Down Function
	if ( ! function_exists( 'wpjm_pdd_get_page_level' ) ) {
		function wpjm_pdd_get_page_level( $pd_page ) {	
			if ( $pd_page->post_parent == 0 )
				return 0;
			else {
				$pd_i = 0;
				$pd_parent = get_post( $pd_page->post_parent ); // Get initial page's parent
				$pd_loop = 1;
				while ( $pd_i < 10 ) {
					$pd_i++;
					if ( $pd_parent->post_parent == 0 ) // If this post parent is a root, then end the loop
						return $pd_i;
					else // Otherwise, get the parent to the parent and try again
						$pd_parent = get_post( $pd_parent->post_parent );
				}
			}
		}
	}

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
	
	// Get Pages
	/*
	$pd_pages = get_pages('sort_column='.$options['sortpagesby'].'&sort_order='.$options['sortpages']);
	$pd_total_pages = count($pd_pages);
	// Get Posts
	$pd_posts = get_posts('orderby='.$options['sortpostsby'].'&order='.$options['sortposts'].'&numberposts='.$options['numberposts']);
	$pd_total_posts = count($pd_posts);
	*/

	// Get Custom Post Types settings (will iterate through later)
	$custom_post_types = $options['postTypes'];

	// Start echoing the select menu
	echo '<select id="wp-pdd">';
	echo '<option>-- Choose an Entry to Edit --</option>';
	//echo '<option>-- Page Name (ID) --</option>';
	//echo '<option>-- Post Name (Date | ID) --</option>';
	
	// Loop through pages
	/*
	$pd_i = 0;
	echo '<optgroup label="--Pages--">';
	foreach ($pd_pages as $pd_post) {
		$pd_i++;
		echo '<option value="';
		echo $pd_post->ID;
		echo '"';
		if (isset($_GET['post']) && ($pd_post->ID == $_GET['post']))
			echo ' selected="selected"';
		echo '>';
		echo str_repeat('--', wpjm_pdd_get_page_level($pd_post));
		echo wpjm_get_page_title($pd_post->post_title);
		echo ' ('.$pd_post->ID.') ';
		echo '</option>';
	}
	echo '</optgroup>';

	// Loop through Posts
	echo '<optgroup label="--Posts--">';
	$pd_i = 0;
	foreach ($pd_posts as $pd_post) {
		$pd_i++;
		echo '<option value="';
		echo $pd_post->ID;
		echo '"';
		if (isset($_GET['post']) && ($pd_post->ID == $_GET['post']))
						echo ' selected="selected"';
		echo '>';
		echo wpjm_get_page_title($pd_post->post_title);
		echo ' ('.$pd_post->post_date.' | '.$pd_post->ID.') ';
		echo '</option>';
	}
	echo '</optgroup>';
	*/


	// Loop through custom posts types, and echo them out
	if ($custom_post_types) {
	
		//$wpjm_cpts = explode(',',$custom_post_types);
		$wpjm_cpts = $custom_post_types; // should be array
		if ($wpjm_cpts) {
			// foreach($wpjm_cpts as $wpjm_cpt) {
			foreach($wpjm_cpts as $key => $value ) {
				$wpjm_cpt = $key;
				$sortby = $value['sortby'];
				$sort = $value['sort'];
				$numberposts = $value['numberposts'];
				
				// Get Posts
				$pd_posts = get_posts('orderby='.$sortby.'&order='.$sort.'&numberposts='.$numberposts.'&post_type='.$wpjm_cpt);
				$pd_total_posts = count($pd_posts);
				
				$cpt_obj = get_post_type_object($wpjm_cpt);
				$cpt_labels = $cpt_obj->labels;
				
				// Loop through custom posts
				$pd_i = 0;
				echo '<optgroup label="--'.$cpt_labels->name.'--">';
				foreach ($pd_posts as $pd_post) {
					$pd_i++;
					$admin_url = ADMIN_COOKIE_PATH;
					echo '<option value="./';
					switch ($wpjm_cpt) {
						case 'attachment':
						echo 'media.php?attachment_id='.$pd_post->ID.'&action=edit';
						break;
						default:
						echo 'post.php?action=edit&post='.$pd_post->ID;
					}
					echo '"';
					if (isset($_GET['post']) && ($pd_post->ID == $_GET['post']))
						echo ' selected="selected"';
					echo '>';
					if (is_post_type_hierarchical($wpjm_cpt)) {
						echo str_repeat('--', wpjm_pdd_get_page_level($pd_post));
					}
					echo wpjm_get_page_title($pd_post->post_title);
					echo ' ('.$pd_post->ID.') ';
					echo '</option>';
				}
				echo '</optgroup>';
			}
		}
	
	}
	
	
	// Close the select drop down
	echo '</select>';
} 


/* Plugin Installation Function */
function wpjm_install() {

	// Populate with default values
	if (get_option('wpjm_position')) {

		$newPostTypes = array(
				'page' => array(
					'show' => '1',
					'sortby' => 'menu_order',
					'sort' => 'ASC'
				),
				'post' => array(
					'show' => '1',
					'sortby' => 'date',
					'sort' => 'DESC'
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
						'sort' => 'ASC'
						);
				}
			} else {
				$newPostTypes[$cpt_arr] = array(
					'show' => '1',
					'sortby' => 'menu_order',
					'sort' => 'ASC'
				);
			}
		}
		
		$arr = array(
			'position' => get_option('wpjm_position'),
			'backgroundColor' => get_option('wpjm_backgroundColor'),
			'fontColor' => get_option('wpjm_fontColor'),
			'borderColor' => get_option('wpjm_borderColor'),
			'postTypes' => $newPostTypes,
			'logoIcon' => get_option('wpjm_logoIcon'),
			'logoWidth' => get_option('wpjm_logoWidth'),
			'linkColor' => get_option('wpjm_linkColor'),
			'message' => get_option('wpjm_message') 
		);

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
		
		$arr = array(
			'position' => 'top',
			'backgroundColor' => 'e0e0e0',
			'fontColor' => '787878',
			'borderColor' => '666666',
			'postTypes' => array(
				'page' => array(
					'show' => '1',
					'sortby' => 'menu_order',
					'sort' => 'ASC'
				),
				'post' => array(
					'show' => '1',
					'sortby' => 'date',
					'sort' => 'DESC'
				)
			),
			'logoIcon' => 'http://www.krillwebdesign.com/wp-content/uploads/2011/06/logo-small-no-tag1.png',
			'logoWidth' => '0',
			'linkColor' => '1cd0d6',
			'message' => "Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>." 
		);

	}
	update_option('wpjm_options',$arr);
	update_option('wpjm_version','2.1.4');

}


// Uninstall
function wpjm_uninstall() {
	delete_option('wpjm_options');
}

// Add the WPJM Menu
function wpjm_menu() {

	add_options_page('Jump Menu Options','Jump Menu Options', 8, 'wpjm-options', 'wpjm_options');

}

// Update Options on Save
/*
if (isset($_POST['save_post_page_values'])) {
	
	// Update Options
	update_option("wpjm_position", $_POST['wpjm_position']);
	update_option("wpjm_sortpagesby",$_POST['wpjm_sortpagesby']);
	update_option("wpjm_sortpages",$_POST['wpjm_sortpages']);
	update_option("wpjm_sortpostsby",$_POST['wpjm_sortpostsby']);
	update_option("wpjm_sortposts",$_POST['wpjm_sortposts']);
	update_option("wpjm_numberposts",$_POST['wpjm_numberposts']);
	update_option("wpjm_backgroundColor", $_POST['wpjm_backgroundColor']);
	update_option("wpjm_fontColor",$_POST['wpjm_fontColor']);
	update_option("wpjm_borderColor",$_POST['wpjm_borderColor']);
	
	$custom_post_types = (is_array($_POST['wpjm_customPostTypes'])?implode(",",$_POST['wpjm_customPostTypes']):$_POST['wpjm_customPostTypes']);
	update_option("wpjm_customPostTypes", $custom_post_types);
	update_option("wpjm_logoIcon", $_POST['wpjm_logoIcon']);
	
	if (!function_exists('file_exists')) {
		
		if ($_POST['wpjm_logoIcon'] && file_exists($_POST['wpjm_logoIcon'])) {
			$logo_width_obj = getimagesize($_POST['wpjm_logoIcon']);
			$logo_width = $logo_width_obj[0];
			update_option("wpjm_logoWidth", $logo_width);
		}

	} else {
		update_option("wpjm_logoWidth", $_POST['wpjm_logoWidth']);
	}

	update_option("wpjm_linkColor", $_POST['wpjm_linkColor']);
	update_option("wpjm_message", stripslashes($_POST['wpjm_message']));
	$message = "Options updated successfully!";

}
*/

/**
 * The options page
 */
function wpjm_options() {

	// Update success message 
	if ( isset( $_POST['save_post_page_values'] ) ) {
		$message = "Options updated successfully!";
	}

	// Get Options
	/*
	$wpjm_position 			= get_option("wpjm_position");
	$wpjm_sortpagesby 		= get_option("wpjm_sortpagesby");
	$wpjm_sortpages 		= get_option("wpjm_sortpages");
	$wpjm_sortpostsby 		= get_option("wpjm_sortpostsby");
	$wpjm_sortposts 		= get_option("wpjm_sortposts");
	$wpjm_numberposts 		= get_option("wpjm_numberposts");
	$wpjm_backgroundColor 	= get_option("wpjm_backgroundColor");
	$wpjm_fontColor 		= get_option("wpjm_fontColor");
	$wpjm_borderColor 		= get_option("wpjm_borderColor");
	$wpjm_logoIcon 			= get_option("wpjm_logoIcon");
	$wpjm_logoWidth 		= get_option("wpjm_logoWidth");
	$wpjm_message 			= get_option("wpjm_message");
	$wpjm_linkColor 		= get_option("wpjm_linkColor");
	$wpjm_customPostTypes 	= get_option("wpjm_customPostTypes");
	*/

?>


<?php if ($message) : ?>
	<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32">
			<br/>
		</div>
		<h2>WP Jump Menu Options</h2>	

		<form action="options.php" method="post" id="wpjm-options-form">
		<?php settings_fields('wpjm_options'); ?>
		<?php do_settings_sections('wpjm'); ?>

		<p class="submit">
			<input type="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" />
		</p>
		</form>
	</div>

<?php
}

// Launch in 5... 4... 3... 2... 1...
beam_me_up_wpjm();
?>