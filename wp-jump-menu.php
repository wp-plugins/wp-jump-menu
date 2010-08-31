<?php
/**
 * @package WP_Jump_Menu
 * @author Jim Krill
 * @version 1.0
 */
/*
Plugin Name: WP Jump Menu
Plugin URI: http://synotac.com/wp-jump-menu
Description: Creates a drop-down menu (jump menu) in a bar across the bottom of the screen that makes it easy to jump to a page or post in the admin area for editing.
Author: Jim Krill
Version: 1.0
Author URI: http://synotac.com/
*/

/*  Copyright 2010  Jim Krill  (email : jimkrill@gmail.com)

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

// Prepare for lift-off... all systems go!
// These set things in motion.
function beam_me_up_wpjm() {

	add_action('admin_footer', 'wpjm_custom_footer');
	add_action('admin_head','wpjm_js');
	add_action('admin_print_styles', 'wpjm_editpost_css');
}

// Can we get some better decals please?  Thank you.
// Swap the little Wordpress Logo for the Synotac Logo
function wpjm_editpost_css() {
	echo "
	<style type='text/css'>
	#synotac_footer { position: fixed; ".get_option('wpjm_position').": 0; left: 0; height: 40px; overflow: hidden; background: ".get_option('wpjm_backgroundColor')."; color: ".get_option('wpjm_fontColor')."; width: 100%; z-index: 1500; border-".(get_option('wpjm_position')=='top'?'bottom':'top').": 2px solid ".get_option('wpjm_borderColor')."; }
	#synotac_footer p { padding: 5px 15px; font-size: 12px; margin: 0; }
	#synotac_footer p a:link, #synotac_footer p a:visited, #synotac_footer p a:hover { color: ".get_option('wpjm_linkColor')."; text-decoration: none; }
	#synotac_footer p.syn_need_help { float: right; text-align: right; }
	#synotac_footer p.syn_credits { font-style: italic; ".(get_option('wpjm_logoIcon')?'background: url('.get_option('wpjm_logoIcon').') no-repeat 15px 10px; padding-left: '.get_option('wpjm_logoWidth').'px;':'')."padding-top: 10px; line-height: 13px; }
	#synotac_footer_clear { height: 30px; }
	body { ".(get_option('wpjm_position')=='top'?'padding-top: 42px !important;':'padding-bottom: 42px !important;')." }
	#wp-pdd { max-width: 400px;  }
	</style>
	<!--[if IE 6]>
	<style type='text/css'>
	#synotac_footer { position: relative; }
	#synotac_footer_clear { display: none; }
	</style>
	<![endif]-->
	";
}

// Some javascript to help the edit drop down list work
function wpjm_js() {
wp_enqueue_script( 'jquery-functions',get_option('siteurl').'/wp-content/plugins/wp-jump-menu/assets/js/jqueryfunctions.js',array('jquery') );
}

// Put a bar across the bottom of the screen that offers to help...
function wpjm_custom_footer() {

	echo '<div id="synotac_footer">';
	echo '<p class="syn_need_help">';
	echo 'Edit an existing page/post: ';
		// Jump to page edit
		wpjm_page_dropdown();
	echo '</p>';
	echo '<p class="syn_credits">';
	echo get_option('wpjm_message').' Go to your <a href="'.get_bloginfo('url').'">site</a>.';
	echo '</p>';
	echo '</div>';
	// echo '<div id="synotac_footer_clear"></div>';

}


function wpjm_page_dropdown(){
	
	require_once(ABSPATH.'wp-config.php');

	// Drop Down Function
	if (!function_exists('wpjm_pdd_get_page_level')) {
		function wpjm_pdd_get_page_level ($pd_page) {	
			if ($pd_page->post_parent == 0)
				return 0;
			else {
				$pd_i = 0;
				$pd_parent = get_post($pd_page->post_parent); // Get initial page's parent
				$pd_loop = 1;
				while ($pd_i < 10) {
					$pd_i++;
					if ($pd_parent->post_parent == 0) // If this post parent is a root, then end the loop
						return $pd_i;
					else // Otherwise, get the parent to the parent and try again
						$pd_parent = get_post($pd_parent->post_parent);
				}
			}
		}
	}

	// Page Title Function
	if (!function_exists('wpjm_get_page_title')) {
		function wpjm_get_page_title ($pd_title) {
			if (strlen($pd_title) > 50) {
				return substr($pd_title, 0, 50)."...";
			} else {
				return $pd_title;
			}
		}
	}
	
	// Get Pages
	$pd_pages = get_pages('sort_column=menu_order');
	$pd_total_pages = count($pd_pages);
	// Get Posts
	$pd_posts = get_posts('orderby=menu_order&order=ASC&numberposts=-1');
	$pd_total_posts = count($pd_posts);

	// Get Custom Post Types settings (will iterate through later)
	$custom_post_types = get_option('wpjm_customPostTypes');

	// Start echoing the select menu
	echo '<select id="wp-pdd">';
	echo '<option>-- Choose a Page/Post --</option>';
	
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
		echo ' ('.$pd_post->ID.') ';
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

// Launch in 5... 4... 3... 2... 1...
beam_me_up_wpjm();


/* Admin Area */
function syn_install() {
		add_option("wpjm_position",'bottom');
		add_option("wpjm_backgroundColor",'#333');
		add_option("wpjm_fontColor",'#fff');
		add_option("wpjm_borderColor",'#ff9933');
		add_option("wpjm_customPostTypes",'');
		add_option("wpjm_logoIcon",'../wp-content/plugins/wp-jump-menu/assets/images/syn-logo-icon.png');
		add_option("wpjm_logoWidth",'35');
		add_option("wpjm_linkColor",'#ff9933');
		add_option("wpjm_message","Brought to you by <a href='http://www.synotac.com/' target='_blank'>Synotac Web Design</a>.");
}



// Add the WPJM Menu
function wpjm_menu() {

	add_options_page('Jump Menu Options','Jump Menu Options', 8, 'wpjm-options', 'wpjm_options');

}

// Update Options on Save
if (isset($_POST['save_post_page_values'])) {
	update_option("wpjm_position", $_POST['wpjm_position']);
	update_option("wpjm_backgroundColor", $_POST['wpjm_backgroundColor']);
	update_option("wpjm_fontColor",$_POST['wpjm_fontColor']);
	update_option("wpjm_borderColor",$_POST['wpjm_borderColor']);
	update_option("wpjm_customPostTypes", $_POST['wpjm_customPostTypes']);
	update_option("wpjm_logoIcon", $_POST['wpjm_logoIcon']);
	update_option("wpjm_logoWidth", $_POST['wpjm_logoWidth']);
	update_option("wpjm_linkColor", $_POST['wpjm_linkColor']);
	update_option("wpjm_message", stripslashes($_POST['wpjm_message']));
	$message = "Options updated successfully!";
}

function wpjm_options() {

if (isset($_POST['save_post_page_values'])) {
	$message = "Options updated successfully!";
}

// Get Options
$wpjm_position = get_option("wpjm_position");
$wpjm_backgroundColor = get_option("wpjm_backgroundColor");
$wpjm_fontColor = get_option("wpjm_fontColor");
$wpjm_borderColor = get_option("wpjm_borderColor");
$wpjm_logoIcon = get_option("wpjm_logoIcon");
$wpjm_logoWidth = get_option("wpjm_logoWidth");
$wpjm_message = get_option("wpjm_message");
$wpjm_linkColor = get_option("wpjm_linkColor");
$wpjm_customPostTypes = get_option("wpjm_customPostTypes");

?>

<style type="text/css">
	#wpjm-options-form {
		width: 600px;
	}
	#wpjm-options-form fieldset {
		border: 1px solid #aaa;
		padding: 10px;
	}
	#wpjm-options-form fieldset legend {
		font: bold italic 14px Georgia;
		padding: 0px 10px;
	}
	#wpjm-options-form label {
		font-weight: bold;
		display: block;
	}
	#wpjm-options-form small {
		font-style: italic;
	}
	#wpjm-options-form ol li {
		list-style: none;
		margin-bottom: 15px;
	}
	#wpjm-options-form ol {
		margin-bottom: 20px;
	}
	
</style>

<?php if ($message) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
	<div class="wrap">
		<h2>WP Jump Menu Options</h2>		

			<form method="post" id="wpjm-options-form">
			<fieldset>
			<legend>Options</legend>
			<ol>
				<li>
					<label>Position of Jump Menu Bar:</label> 
					<div>
						<input type="radio" value="bottom" name="wpjm_position" id="wpjm_position"<?php echo ($wpjm_position=='bottom'?' checked="checked"':''); ?> /> Bottom<br/>
						<input type="radio" value="top" name="wpjm_position" id="wpjm_position"<?php echo ($wpjm_position=='top'?' checked="checked"':''); ?> /> Top
					</div>
					<small>This determines where the bar will be placed.</small>
				</li>
				<li>
					<label>Background Color:</label>
					<div>
						<input type="text" name="wpjm_backgroundColor" id="wpjm_backgroundColor" value="<?php echo $wpjm_backgroundColor; ?>" />
					</div>
					<small>Hex color (i.e. #333 or #333333 would be dark grey)</small>
				</li>
				<li>
					<label>Font Color:</label>
					<div>
						<input type="text" name="wpjm_fontColor" id="wpjm_fontColor" value="<?php echo $wpjm_fontColor; ?>" />
					</div>
					<small>Hex color</small>
				</li>
				<li>
					<label>Border Color:</label>
					<div>
						<input type="text" name="wpjm_borderColor" id="wpjm_borderColor" value="<?php echo $wpjm_borderColor; ?>" />
					</div>
					<small>Hex color</small>
				</li>
				<li>
					<label>Link Color:</label>
					<div>
						<input type="text" name="wpjm_linkColor" id="wpjm_linkColor" value="<?php echo $wpjm_linkColor; ?>" />
					</div>
					<small>Hex color</small>
				</li>
				<li>
					<label>Logo Icon URL:</label>
					<div>
						<input type="text" name="wpjm_logoIcon" id="wpjm_logoIcon" value="<?php echo $wpjm_logoIcon; ?>" size="100" />
					</div>
					<small>*Optional: The URL to the icon displayed next to the message in the jump bar.</small>
				</li>
				<li>
					<label>Logo Width:</label>
					<div>
						<input type="text" name="wpjm_logoWidth" id="wpjm_logoWidth" value="<?php echo $wpjm_logoWidth; ?>" />
					</div>
					<small>*Optional: The width of the icon, used as padding on the left of the message.</small>
				</li>
				<li>
					<label>Message:</label>
					<div>
						<textarea name="wpjm_message" id="wpjm_message" cols="40" rows="3" ><?php echo $wpjm_message; ?></textarea>
					</div>
					<small>Short message to include on left side of Jump bar.  HTML is ok.</small>
				</li>
				<li>
					<label>Custom Post Types:</label>
					<div>
						<input type="text" name="wpjm_customPostTypes" id="wpjm_customPostTypes" value="<?php echo $wpjm_customPostTypes; ?>" size="50" />
					</div>
					<small>Comma separated list of custom post type names to display in the Jump Menu. (i.e. news,reviews,authors)</small>
				</li>
			</ol>
			<p class="sb">
				<input type="submit" name="save_post_page_values" value="Save Options" class="button button-primary">
			</p>
			</fieldset>
			
		</form>
			
	</div>
<?php
}

register_activation_hook(__FILE__,'syn_install');
add_action('admin_menu', 'wpjm_menu');

?>