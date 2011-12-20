<?php
/**
 * @package WP_Jump_Menu
 * @author Jim Krill
 * @version 2.0
 */
/*
Plugin Name: WP Jump Menu
Plugin URI: http://www.synotac.com/wp-jump-menu/
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Author: Jim Krill
Version: 2.0
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

// Call the plugin's main functions
function beam_me_up_wpjm() {

	register_activation_hook( __FILE__, 'wpjm_install' );

	// Needs uninstall hook to delete files
	
	add_action( 'admin_footer', 'wpjm_custom_footer' );
	add_action( 'admin_print_scripts','wpjm_js' );
	add_action( 'admin_print_styles', 'wpjm_editpost_css' );
	add_filter( 'plugin_action_links', 'wpjm_add_settings_link', 10, 2);
	add_action( 'admin_menu', 'wpjm_menu' );

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

	echo "
	<link rel='stylesheet' href='".get_option('siteurl')."/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/css/colorpicker.css' type='text/css' />
   <!-- <link rel='stylesheet' media='screen' type='text/css' href='".get_option('siteurl')."/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/css/layout.css' /> -->
	<style type='text/css'>
	#jump_menu { position: fixed; ".get_option('wpjm_position').": ".(is_admin_bar_showing()?"28px":"0")."; left: 0; height: 40px; overflow: hidden; background: #".get_option('wpjm_backgroundColor')."; color: #".get_option('wpjm_fontColor')."; width: 100%; z-index: 1500; border-".(get_option('wpjm_position')=='top'?'bottom':'top').": 2px solid #".get_option('wpjm_borderColor')."; }
	#jump_menu p { padding: 5px 15px; font-size: 12px; margin: 0; }
	#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover { color: #".get_option('wpjm_linkColor')."; text-decoration: none; }
	#jump_menu p.wpjm_need_help { float: right; text-align: right; }
	#jump_menu p.jm_credits { font-style: italic; padding-top: 10px; line-height: 13px; }
	#jump_menu p.jm_credits img.wpjm_logo { ".(get_option('wpjm_logoWidth')?'width: '.get_option('wpjm_logoWidth').'px;':'width: 35px;')." height: auto; max-height: 30px; vertical-align: middle; margin-right: 10px; }
	#jump_menu_clear { height: 30px; }
	body { ".(get_option('wpjm_position')=='top'?'padding-top: 42px !important;':'padding-bottom: 42px !important;')." }
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

	// jqueryfunctions.js (general jquery scripts) & jquery
	wp_enqueue_script( 'jquery-functions', get_option( 'siteurl' ).'/wp-content/plugins/wp-jump-menu/assets/js/jqueryfunctions.js', array( 'jquery' ) );
	// colorpicker.js - used for the color picker
	wp_enqueue_script( 'jquery-colorpicker', get_option( 'siteurl' ).'/wp-content/plugins/wp-jump-menu/assets/js/colorpicker/js/colorpicker.js', array( 'jquery' ) );

}

// Put a bar across the bottom of the screen that offers to help...
// This is the plugins display function that shows the jump menu bar
function wpjm_custom_footer() {

	echo '<div id="jump_menu">';
		echo '<p class="wpjm_need_help">';
		
			echo 'Edit an existing page/post: ';
			// Jump to page edit
			wpjm_page_dropdown();

		echo '</p>';
		echo '<p class="jm_credits">';
			echo ( get_option( 'wpjm_logoIcon' ) ? '<a href="'.get_bloginfo( 'url' ).'"><img class="wpjm_logo" src="'.get_option( 'wpjm_logoIcon' ).'" alt="" /></a>' : '');
			echo get_option( 'wpjm_message' ).' Go to your <a href="'.get_bloginfo( 'url' ).'">site</a>.';
		echo '</p>';
	echo '</div>';

}


function wpjm_page_dropdown(){
	
	require_once( ABSPATH . 'wp-config.php' );

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
	$pd_pages = get_pages('sort_column='.get_option('wpjm_sortpagesby').'&sort_order='.get_option('wpjm_sortpages'));
	$pd_total_pages = count($pd_pages);
	// Get Posts
	$pd_posts = get_posts('orderby='.get_option('wpjm_sortpostsby').'&order='.get_option('wpjm_sortposts').'&numberposts='.get_option('wpjm_numberposts'));
	$pd_total_posts = count($pd_posts);

	// Get Custom Post Types settings (will iterate through later)
	$custom_post_types = get_option('wpjm_customPostTypes');

	// Start echoing the select menu
	echo '<select id="wp-pdd">';
	echo '<option>-- Choose an Entry to Edit --</option>';
	echo '<option>-- Page Name (ID) --</option>';
	echo '<option>-- Post Name (Date | ID) --</option>';
	
	// Loop through pages
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
	
	// Loop through custom posts types, and echo them out
	if ($custom_post_types) {
	
		$wpjm_cpts = explode(',',$custom_post_types);
		if ($wpjm_cpts) {
			foreach($wpjm_cpts as $wpjm_cpt) {
				// Get Posts
				$pd_posts = get_posts('orderby=menu_order&order=ASC&numberposts=-1&post_type='.$wpjm_cpt);
				$pd_total_posts = count($pd_posts);
				
				$cpt_obj = get_post_type_object($wpjm_cpt);
				$cpt_labels = $cpt_obj->labels;
				
				// Loop through custom posts
				$pd_i = 0;
				echo '<optgroup label="--'.$cpt_labels->name.'--">';
				foreach ($pd_posts as $pd_post) {
					$pd_i++;
					echo '<option value="';
					echo $pd_post->ID;
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

		add_option("wpjm_position",'top');
		add_option("wpjm_sortpagesby",'menu_order');
		add_option("wpjm_sortpages",'ASC');
		add_option("wpjm_sortpostsby",'date');
		add_option("wpjm_sortposts",'DESC');
		add_option("wpjm_numberposts",'-1');
		add_option("wpjm_backgroundColor",'333333');
		add_option("wpjm_fontColor",'ffffff');
		add_option("wpjm_borderColor",'aaaaaa');
		add_option("wpjm_customPostTypes",'');
		add_option("wpjm_logoIcon",'');
		add_option("wpjm_logoWidth",'0');
		add_option("wpjm_linkColor",'aaaaaa');
		add_option("wpjm_message","Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>.");

}



// Add the WPJM Menu
function wpjm_menu() {

	add_options_page('Jump Menu Options','Jump Menu Options', 8, 'wpjm-options', 'wpjm_options');

}

// Update Options on Save
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


/**
 * The options page
 */
function wpjm_options() {

	// Update success message 
	if ( isset( $_POST['save_post_page_values'] ) ) {
		$message = "Options updated successfully!";
	}

	// Get Options
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

?>


<?php if ($message) : ?>
	<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32">
			<br/>
		</div>
		<h2>WP Jump Menu Options</h2>		

			<form method="post" id="wpjm-options-form">

			<table class="form-table">
			<tr>
				<th>
					<label>Position of Jump Menu Bar:</label> 
				</th>
				<td>
					<div>
						<input type="radio" value="bottom" name="wpjm_position" id="wpjm_position"<?php echo ($wpjm_position=='bottom'?' checked="checked"':''); ?> /> Bottom of screen<br/>
						<input type="radio" value="top" name="wpjm_position" id="wpjm_position"<?php echo ($wpjm_position=='top'?' checked="checked"':''); ?> /> Top of screen
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Sort Pages By:</label>
				</th>
				<td>
					<div>
						<select name="wpjm_sortpagesby" id="wpjm_sortpagesby">
							<option value="menu_order"<?php echo ($wpjm_sortpagesby=='menu_order'?' selected="selected"':''); ?>>Menu Order</option>
							<option value="post_author"<?php echo ($wpjm_sortpagesby=='post_author'?' selected="selected"':''); ?>>Author</option>
							<option value="post_date"<?php echo ($wpjm_sortpagesby=='post_date'?' selected="selected"':''); ?>>Date</option>
							<option value="ID"<?php echo ($wpjm_sortpagesby=='ID'?' selected="selected"':''); ?>>ID</option>
							<option value="post_modified"<?php echo ($wpjm_sortpagesby=='post_modified'?' selected="selected"':''); ?>>Modified</option>
							<option value="post_name"<?php echo ($wpjm_sortpagesby=='post_name'?' selected="selected"':''); ?>>Name</option>
							<option value="post_parent"<?php echo ($wpjm_sortpagesby=='post_parent'?' selected="selected"':''); ?>>Parent</option>
							<option value="post_title"<?php echo ($wpjm_sortpagesby=='post_title'?' selected="selected"':''); ?>>Title</option>
							
						</select>
						<span class="description">Pages default is "Menu Order" which maintains hierachy.</span>
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<label>Sort Pages Order:</label>
				</th>
				<td>
					<div>
						<input type="radio" value="ASC" name="wpjm_sortpages" id="wpjm_sortpages"<?php echo ($wpjm_sortpages=='ASC'?' checked="checked"':''); ?> /> Ascending<br/>
						<input type="radio" value="DESC" name="wpjm_sortpages" id="wpjm_sortpages"<?php echo ($wpjm_sortpages=='DESC'?' checked="checked"':''); ?> /> Descending
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<label>Sort Posts By:</label>
				</th>
				<td>
					<div>
						<select name="wpjm_sortpostsby" id="wpjm_sortpostsby">
							<option value="menu_order"<?php echo ($wpjm_sortpostsby=='menu_order'?' selected="selected"':''); ?>>Menu Order</option>
							<option value="author"<?php echo ($wpjm_sortpostsby=='author'?' selected="selected"':''); ?>>Author</option>
							<option value="category"<?php echo ($wpjm_sortpostsby=='category'?' selected="selected"':''); ?>>Category</option>
							<option value="content"<?php echo ($wpjm_sortpostsby=='content'?' selected="selected"':''); ?>>Content</option>
							<option value="date"<?php echo ($wpjm_sortpostsby=='date'?' selected="selected"':''); ?>>Date</option>
							<option value="ID"<?php echo ($wpjm_sortpostsby=='ID'?' selected="selected"':''); ?>>ID</option>
							<option value="mime_type"<?php echo ($wpjm_sortpostsby=='mime_type'?' selected="selected"':''); ?>>Mime Type</option>
							<option value="modified"<?php echo ($wpjm_sortpostsby=='modified'?' selected="selected"':''); ?>>Modified</option>
							<option value="name"<?php echo ($wpjm_sortpostsby=='name'?' selected="selected"':''); ?>>Name</option>
							<option value="parent"<?php echo ($wpjm_sortpostsby=='parent'?' selected="selected"':''); ?>>Parent</option>
							<option value="password"<?php echo ($wpjm_sortpostsby=='password'?' selected="selected"':''); ?>>Password</option>
							<option value="rand"<?php echo ($wpjm_sortpostsby=='rand'?' selected="selected"':''); ?>>Random</option>
							<option value="status"<?php echo ($wpjm_sortpostsby=='status'?' selected="selected"':''); ?>>Status</option>
							<option value="title"<?php echo ($wpjm_sortpostsby=='title'?' selected="selected"':''); ?>>Title</option>
							<option value="type"<?php echo ($wpjm_sortpostsby=='type'?' selected="selected"':''); ?>>Type</option>
						</select>
						<span class="description">Posts default is "Date" which orders by post date.</span>
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<label>Sort Posts Order:</label>
				</th>
				<td>
					<div>
						<input type="radio" value="ASC" name="wpjm_sortposts" id="wpjm_sortposts"<?php echo ($wpjm_sortposts=='ASC'?' checked="checked"':''); ?> /> Ascending<br/>
						<input type="radio" value="DESC" name="wpjm_sortposts" id="wpjm_sortposts"<?php echo ($wpjm_sortposts=='DESC'?' checked="checked"':''); ?> /> Descending
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<label>Number of Posts to Display:</label>
				</th>
				<td>
					<div>
						<input type="text" name="wpjm_numberposts" id="wpjm_numberposts" value="<?php echo $wpjm_numberposts; ?>" />
						<span class="description">-1 to display all posts.</span>
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Background Color:</label>
				</th>
				<td>
					<div>
						<input class="colorPicker" type="text" name="wpjm_backgroundColor" id="wpjm_backgroundColor" value="<?php echo $wpjm_backgroundColor; ?>" rel="#jump_menu|backgroundColor" />
						<span class="description">Click to select hex value</span>
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Font Color:</label>
				</th>
				<td>
					<div>
						<input class="colorPicker" type="text" name="wpjm_fontColor" id="wpjm_fontColor" value="<?php echo $wpjm_fontColor; ?>" rel="#jump_menu|color" />
					<span class="description">Click to select hex value</span>
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Border Color:</label>
				</th>
				<td>
					<div>
						<input class="colorPicker" type="text" name="wpjm_borderColor" id="wpjm_borderColor" value="<?php echo $wpjm_borderColor; ?>" rel="#jump_menu|borderColor" />
					<span class="description">Click to select hex value</span>
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Link Color:</label>
				</th>
				<td>
					<div>
						<input class="colorPicker" type="text" name="wpjm_linkColor" id="wpjm_linkColor" value="<?php echo $wpjm_linkColor; ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" />
					<span class="description">Click to select hex value</span>
					</div>
					
				</td>
			</tr>
			<tr>
				<th>
					<label>Logo Icon URL:</label>
				</th>
				<td>
					<div>
						<input type="text" name="wpjm_logoIcon" id="wpjm_logoIcon" value="<?php echo $wpjm_logoIcon; ?>" size="100" />
					</div>
					<span class="description">*Optional: The URL to the icon displayed next to the message in the jump bar.</span>
				</td>
			</tr>
			<?php
			if (!function_exists('file_exists')) { ?>
			<tr>
				<th>
					<label>Logo Width:</label>
				</th>
				<td>
					<div>
						<input type="text" name="wpjm_logoWidth" id="wpjm_logoWidth" value="<?php echo $wpjm_logoWidth; ?>" />
					<span class="description">*Optional: The width of the icon, used as padding on the left of the message.</span>
					</div>
					
				</td>
			</tr>
			<?php
		}
		?>
			<tr>
				<th>
					<label>Message:</label>
				</th>
				<td>
					<div>
						<textarea name="wpjm_message" id="wpjm_message" cols="60" rows="3" ><?php echo $wpjm_message; ?></textarea>
					</div>
					<span class="description">Short message to include on left side of Jump bar.  HTML is ok.</span>
				</td>
			</tr>
			<tr>
				<th>
					<label>Custom Post Types:</label>
				</th>
				<td>
					
					<div>
						<?php 
							$post_types = get_post_types(array('_builtin'=>false),'objects'); 
							$selected_post_types_arr = explode(",",$wpjm_customPostTypes);
						?>

							<?php foreach ($post_types as $pt) { ?>
							<input type="checkbox" name="wpjm_customPostTypes[]" id="wpjm_customPostTypes" value="<?php echo $pt->name; ?>" <?php echo (in_array($pt->name,$selected_post_types_arr)?"checked='checked'":''); ?> /> <?php echo $pt->labels->name; ?><br/>
							<?php } ?>
						

						<!--<input type="text" name="wpjm_customPostTypes" id="wpjm_customPostTypes" value="<?php echo $wpjm_customPostTypes; ?>" size="50" />-->
					</div>
					<span class="description">Select the Custom Post Types you want to show in the jump menu.</span>
				</td>
			</tr>
		</table>
			<p class="submit">
				<input type="submit" name="save_post_page_values" value="Save Options" class="button button-primary">
			</p>
			
		</form>
			
	</div>

<?php
}


// Launch in 5... 4... 3... 2... 1...
beam_me_up_wpjm();
?>