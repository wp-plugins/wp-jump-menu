<?php
/**
 * @package WP_Jump_Menu
 * @author Jim Krill
 * @version 3.2.2
 */
/*
Plugin Name: WP Jump Menu
Plugin URI: http://wpjumpmenu.com
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Version: 3.2.2
Author: Jim Krill
Author URI: http://krillwebdesign.com
License: GPL
Copyright: Jim Krill
*/


// Only run this code if we are NOT within the Network pages on multisite.
if (!is_network_admin()) {
	if (function_exists('current_user_can')) {

		require_once( 'settings.php' );

		$wpjm = new WpJumpMenu();

	}

}

class WpJumpMenu
{
	var $dir,
		$path,
		$version,
		$upgrade_version,
		$tooltip_html,
		$cache,
		$options,
		$current_user,
		$transient_name;


	/*
	*  Constructor
	*
	*  @description:
	*  @since 3.0
	*  @created: 12/12/12
	*/

	function __construct()
	{

		// vars
		$this->path = plugin_dir_path(__FILE__);
		$this->dir = plugins_url('',__FILE__);
		$this->version = '3.2.2';
		$this->upgrade_version = '';
		$this->cache = array();
		// Maybe I should set default options, then array_merge with what is in get_option('wpjm_options') in case it's not there?
		$this->options = get_option('wpjm_options');
		$this->transient_name = 'wpjm_transient';

		// This versions tooltip html
		$this->tooltip_html = '<p><strong>WP Jump Menu Make-Over!</strong></p><ul style="padding: 0 15px; margin: 1em; list-style: disc;"><li>Speed improvements</li><li>Added <a href="http://harvesthq.github.com/chosen/" target="_blank">Chosen</a> JavaScript plugin. Select it on the options page to use a much improved UI on the jump menu.</li><li>Added an option to change the title of the Jump Menu (the title appears just to the left of the menu)</li></ul><p><a href="'.get_admin_url('','options-general.php?page=wpjm-options').'">WP Jump Menu Options</a></p>';

		// set text domain
		load_plugin_textdomain('wp-jump-menu', false, basename(dirname(__FILE__)).'/lang' );

		// actions
		add_action('init', array($this, 'init'));

		// Install/Uninstall
		register_activation_hook( __FILE__, array($this, 'wpjm_install') );
		register_uninstall_hook( __FILE__, 'wpjm_uninstall' );


		return true;
	}


	/*
	*  Init
	*  @description:
	*  @since 3.0
	*  @created: 12/12/12
	*/

	function init()
	{

		global $wp_version;

		// Do not load if this is the network admin
		if (is_network_admin()) {
			return false;
		}

		// Permission Testing
		$this->current_user = wp_get_current_user();
		if ( ! current_user_can('edit_posts') )
		{
			return false;
		}

		// actions
		add_action('admin_menu', array($this,'admin_menu'));
		add_action('admin_print_scripts', array($this,'admin_head'));
		add_action('admin_print_styles', array($this, 'wpjm_css'));
		add_action('plugin_action_links', array($this,'plugin_action_links'), 10, 2);
		add_action('wp_print_scripts', array($this, 'wpjm_js'));
		add_action('save_post', array($this, 'wpjm_remove_transient'));
		add_action('update_option_wpjm_options', array($this, 'wpjm_remove_transient'));

		if ( version_compare($wp_version, '3.2.1', '>'))
		{
			add_action('admin_enqueue_scripts', array($this,'wpjm_enqueue_tooltips'));
		}

		if ( $this->options['position'] == 'wpAdminBar' )
		{
			add_action('admin_bar_menu', array($this, 'admin_bar_menu'), 25);
			add_action('wp_print_styles', array($this, 'wpjm_css'));
		} else {
			if ( isset($this->options['frontend']) && $this->options['frontend'] == 'true' ) {
				add_action('wp_footer', array($this, 'wpjm_footer'));
			}
			add_action('admin_footer', array($this, 'wpjm_footer'));
			add_action('wp_print_styles', array($this, 'wpjm_css'));
		}

		// Options page settings form
		add_action( 'admin_init', 'wpjm_admin_init' );

		// filters


		// ajax

		// register scripts
		$scripts = array(
			'wpjm-jquery-ui-position' => $this->dir . '/assets/js/jquery.ui.position.js',
			'wpjm-jquery-functions' => $this->dir . '/assets/js/jqueryfunctions.js',
			'wpjm-jquery-colorpicker' => $this->dir . '/assets/js/colorpicker/js/colorpicker.js',
			'chosenjs' => $this->dir . '/assets/js/chosen/chosen.jquery.js'
		);

		foreach( $scripts as $k => $v )
		{
			wp_register_script( $k, $v, array('jquery'), $this->version );
		}


		// register styles
		$styles = array(
			'wpjm-colorpicker-css' => $this->dir . '/assets/js/colorpicker/css/colorpicker.css',
			'chosencss' => $this->dir . '/assets/js/chosen/chosen.css',
			'chosencss-wpadminbar' => $this->dir . '/assets/js/chosen/chosen-wpadmin.css'
		);

		foreach( $styles as $k => $v )
		{
			wp_register_style( $k, $v, false, $this->version );
		}


		// Upgrade
		$current_version = get_option('wpjm_version');
		if (empty($current_version) || $current_version < $this->version) {

			// initiate install/update
			$this->wpjm_install();

			// Tooltip temporarily disabled
			/*add_action( 'admin_print_footer_scripts', 'wpjm_tooltip' );

			$dismissed_tooltips = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			if ( in_array( 'wpjm_tooltip', $dismissed_tooltips)) {
				foreach($dismissed_tooltips as $key => $value) {
					if ($value == 'wpjm_tooltip') unset($dismissed_tooltips[$key]);
				}
				if (is_array($dismissed_tooltips))
					$dismissed_tooltips = implode( ',', $dismissed_tooltips );
				update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed_tooltips );
			}*/

		}


	}


	/*
	*  admin_menu
	*
	*  @description:
	*  @since 1.0.0
	*  @created: 12/12/12
	*/

	function admin_menu()
	{
		add_options_page('Jump Menu Options','Jump Menu Options', 'edit_posts', 'wpjm-options', array($this, 'wpjm_options_page'));
	}


	/*
	*  admin_head
	*
	*  @description:
	*  @since 3.0
	*  @created: 12/12/12
	*/

	function admin_head()
	{
		// jquery ui - sortable
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'wpjm-jquery-ui-position' );
		wp_enqueue_script( 'wpjm-jquery-functions' );
		wp_enqueue_script( 'wpjm-jquery-colorpicker' );

		// Testing tooltip
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		// Colorpicker
		wp_enqueue_style( 'wpjm-colorpicker-css' );

	}


	/*
	*  wpjm_options_page
	*
	*	 @description: the options page
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_options_page()
	{

		// Update success message
		if ( isset( $_POST['save_post_page_values'] ) ) {
			$message = "Options updated successfully!";
		}

		?>


		<?php if (!empty($message)) : ?>
			<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php endif; ?>

			<div class="wrap">
				<div id="icon-options-general" class="icon32">
					<br/>
				</div>
				<h2>WP Jump Menu <?php echo $this->version; ?></h2>

				<form action="options.php" method="post" id="wpjm-options-form">
				<?php settings_fields('wpjm_options'); ?>
				<?php do_settings_sections('wpjm'); ?>
				<p class="submit">
					<input type="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" />
				</p>
				<?php do_settings_sections('wpjm-2'); ?>

				<?php
				// Clear the cache when viewing the options page //
				//wp_cache_delete('','wpjm_cache');
				?>

				<p class="submit">
					<input type="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" />
				</p>
				</form>
			</div>

		<?php
	}


	/*
	*  wpjm_enqueue_tooltips
	*
	*	 @description: Displays updated messages in a tooltip.
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_enqueue_tooltips()
	{
		$dismissed_tooltips = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( !in_array('wpjm_tooltip',$dismissed_tooltips) )
		{
			//add_action( 'admin_print_footer_scripts', array($this, 'wpjm_tooltip') );
		}
	}

	function wpjm_tooltip()
	{

		$pointer_content = '<h3>New in Wp Jump Menu '.$this->version.'</h3>';
		$pointer_content .= $this->tooltip_html;
		// $pointer_content .= '<p><a href="#" id="wpjm-tooltip-close">Dismiss</a></p>';
		?>
		<script>
		jQuery(document).ready(function($){
			$('<?php echo ($this->options["position"] == "wpAdminBar"?"#wp-admin-bar-wp-jump-menu":"#jump_menu"); ?>').pointer({
				content: '<?php echo addslashes( $pointer_content ); ?>',
				<?php if ($this->options['position'] == 'top') { ?>
				position: {
					offset: '0 0',
					edge: 'top',
					align: 'center'
				},
				<?php } else if ($this->options['position'] == 'bottom') { ?>
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

	/*
	* plugin_action_links
	*
	* @description: adds "settings" link on plugins page
	* @since: 3.0
	* @created: 12/12/12
	*/

	function plugin_action_links( $links, $file )
	{
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=wpjm-options">'.__("Settings").'</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}


	/*
	*  wpjm_css
	*
	*  @description:
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_css()
	{

		echo "<style type='text/css'>";

		if ($this->options['position'] == 'wpAdminBar')
		{
			echo "
			#wp-admin-bar-wp-jump-menu { height: 28px !important; }
			#wp-admin-bar-wp-jump-menu div.ab-item { float: left; }
			#wpadminbar #wp-pdd, #wpadminbar #wp-pdd * { color: #333 !important; text-shadow: none;}
			#wpadminbar span.wpjm-logo-title { padding-right: 10px; }
			@media only screen and (max-width: 1000px) {
				#wpadminbar #wp-admin-bar-wp-jump-menu div.ab-item { display: none; }

			}
			@media only screen and (max-width: 850px) {
				#wpadminbar #wp-admin-bar-wp-jump-menu div.chosen-container { width: 100% !important; }
			}
			#wpadminbar #wp-jump-menu { padding: 0px 10px; }";
		} else {
			echo "#jump_menu { position: fixed; ".$this->options['position'].": ".($this->options['position']=='top'?(is_admin_bar_showing()?"28px":"0"):"0")."; left: 0; height: 40px; background: #".$this->options['backgroundColor']."; color: #".$this->options['fontColor']."; width: 100%; z-index: 1500; border-".($this->options['position']=='top'?'bottom':'top').": 2px solid #".$this->options['borderColor']."; }
		#jump_menu p { padding: 5px 15px; font-size: 12px; margin: 0; }
		#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover { color: #".$this->options['linkColor']."; text-decoration: none; }
		#jump_menu p.wpjm_need_help { float: right; text-align: right; }
		#jump_menu p.wpjm_need_help span.wpjm-logo-title { font-family: Georgia; font-style: italic; padding-right: 10px; }
		#jump_menu p.jm_credits { font-style: italic; padding-top: 10px; line-height: 13px; }
		#jump_menu p.jm_credits img.wpjm_logo { ".(isset($this->options['logoWidth'])?'width: '.$this->options['logoWidth'].'px;':'width: 35px;')." height: auto; max-height: 30px; vertical-align: middle; margin-right: 10px; }
		#jump_menu_clear { height: 30px; }
		@media only screen and (max-width: 768px) {
			#jump_menu .jm_credits { display: none; }
		}
		@media only screen and (max-width: 480px) {
			#jump_menu span.wpjm-logo-title { display: none; }
		}
		body { ".($this->options['position']=='top'?'padding-top: 42px !important;':'padding-bottom: 42px !important;')." }
		".($this->options['position']=='bottom'?'#footer { bottom: 42px !important; }':'');
		}

		echo "
		#wp-pdd { max-width: 400px;  }
		#wpadminbar #wp-admin-bar-top-secondary #wp-admin-bar-wp-jump-menu .chosen-container * {
			text-align: " . (isset($this->options['chosenTextAlign']) ? $this->options['chosenTextAlign'] : 'right') . " !important;
		}
		.chosen-container { vertical-align: middle; }
		.chosen-container .chosen-results li span.post-id {
			font-size: 12px;
			color: #aaa !important;
		}
		";


		echo "</style>
		<!--[if IE 6]>
		<style type='text/css'>
		#jump_menu { position: relative; }
		#jump_menu_clear { display: none; }
		</style>
		<![endif]-->
		";

	}


	/*
	*  wpjm_js
	*
	*  @description:
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_js() {

		wp_enqueue_script( 'chosenjs' );
		if ($this->options['position'] == 'wpAdminBar') {
			wp_enqueue_style( 'chosencss-wpadminbar' );
		} else {
			wp_enqueue_style( 'chosencss' );
		}

	}


	/*
	*  admin_bar_menu
	*
	*  @description: Adds the jump-menu into the admin toolbar
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function admin_bar_menu()
	{
		global $wp_admin_bar;

		if (is_admin_bar_showing())
		{

			//$html = '<span class="wpjm-logo-title">'.$this->options['title'].'</span>';
			$html = $this->wpjm_page_dropdown();
			$html .= "<script>
			jQuery(document).ready(function($){";

			$html .= "jQuery('#wp-pdd').on('change',function() {
						window.location = this.value;
					})";
			if ($this->options['useChosen'] == 'true') {
				$html .= ".chosen({position:'".$this->options['position']."'})";
			}
				$html .= ";";

			$html .= "});
			</script>";

			/*$wp_admin_bar->add_menu( array(
	      'id'    => 'wp-jump-menu',
	      'parent'    => 'top-secondary',
	      'title' => $html
	    ) );*/

			$wp_admin_bar->add_menu( array(
				'id' 		 	=> 'wp-jump-menu',
				'parent' 	=> 'top-secondary',
				'title' 	=> $this->options['title'],
				'meta'		=> array(
											'html' => $html
										 )
			));

	  }
	}


	/*
	*  wpjm_footer
	*
	*  @description:
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_footer()
	{
		echo '<div id="jump_menu">';
			echo '<p class="wpjm_need_help">';

				echo '<span class="wpjm-logo-title">'.$this->options['title'].'</span>';
				// Jump to page edit
				echo $this->wpjm_page_dropdown();

			echo '</p>';
			echo '<p class="jm_credits">';
				echo ( !empty($this->options['logoIcon']) ? '<a href="'.get_bloginfo( 'url' ).'"><img class="wpjm_logo" src="'.$this->options['logoIcon'].'" alt="" /></a>' : '');
				echo $this->options['message'];
				//echo ' Go to your <a href="'.get_bloginfo( 'url' ).'">site</a>.';
			echo '</p>';
			?>
			<script>
			jQuery(document).ready(function($){

					<?php if ($this->options['useChosen']=='true') { ?>
					jQuery('#wp-pdd').bind('liszt:ready',function(){
						jQuery('ul.chosen-results li').prepend('<span class="front-end"></span>');
					});

					<?php } ?>

					jQuery('#wp-pdd').on('change',function() {
						window.location = this.value;
					})<?php if ($this->options['useChosen']=='true') { ?>.chosen({position:"<?php echo $this->options['position']; ?>"})<?php } ?>;



			});
			</script>
			<?php
		echo '</div>';
	}

	/*
	*  wpjm_page_dropdown
	*
	*  @description: the main function to display the drop-down menu
	*	 @since: 3.0
	*	 @created: 12/12/12
	*/

	function wpjm_page_dropdown()
	{

		// Is this needed?
		//require_once( ABSPATH . 'wp-load.php' );
		require_once( 'assets/WpjmWalkerClass.php' );

		global $current_user, $post;

		// Get Custom Post Types settings (will iterate through later)
		$custom_post_types = $this->options['postTypes'];

		// Set post status colors
		$status_color = array(
			'publish' => (!empty($this->options['statusColors']['publish'])?'#'.$this->options['statusColors']['publish']:'#000000'),
			'pending' => (!empty($this->options['statusColors']['pending'])?'#'.$this->options['statusColors']['pending']:'#999999'),
			'draft' => (!empty($this->options['statusColors']['draft'])?'#'.$this->options['statusColors']['draft']:'#999999'),
			'auto-draft' => (!empty($this->options['statusColors']['auto-draft'])?'#'.$this->options['statusColors']['auto-draft']:'#999999'),
			'future' => (!empty($this->options['statusColors']['future'])?'#'.$this->options['statusColors']['future']:'#398f2c'),
			'private' => (!empty($this->options['statusColors']['private'])?'#'.$this->options['statusColors']['private']:'#999999'),
			'inherit' => (!empty($this->options['statusColors']['inherit'])?'#'.$this->options['statusColors']['inherit']:'#333333'),
			'trash' => (!empty($this->options['statusColors']['trash'])?'#'.$this->options['statusColors']['trash']:'#ff0000')
			);

		$wpjm_string = '';

		// Attempt to get transient
		// $wpjm_transient = get_transient( $this->transient_name );

		// if ( $wpjm_transient === false ) {

			// Start echoing the select menu
			if (isset($this->option['useChosen']) && $this->options['useChosen']=='true') {
				$wpjm_string .= '<select id="wp-pdd" data-placeholder="Select to Edit" style="width: 250px;">';
			} else {
				$wpjm_string .= '<select id="wp-pdd" data-placeholder="Select to Edit">';
			}
			$wpjm_string .= '<option>Select to Edit</option>';

			// Loop through custom posts types, and echo them out
			if ($custom_post_types) {

				//$wpjm_cpts = explode(',',$custom_post_types);
				$wpjm_cpts = $custom_post_types; // should be array
				if ($wpjm_cpts) {

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
						$post_type_object = get_post_type_object( $wpjm_cpt );
						$sortby = $value['sortby'];				// orderby value
						$sort = $value['sort'];					// order value
						$numberposts = $value['numberposts'];	// number of posts to display
						$showdrafts = (isset($value['showdrafts'])?$value['showdrafts']:'');		// show drafts, true or false
						$post_status = $value['poststatus'];
						$postmimetype = array();
						if (isset($value['postmimetypes']) && is_array($value['postmimetypes'])) {
							foreach($value['postmimetypes'] as $mime) {
								switch ($mime) {
									case 'images':
										$postmimetype[] = 'image/jpeg';
										$postmimetype[] = 'image/png';
										$postmimetype[] = 'image/gif';
										$postmimetype[] = 'image';
									break;

									case 'videos':
										$postmimetype[] = 'video/mpeg';
										$postmimetype[] = 'video/mp4';
										$postmimetype[] = 'video/quicktime';
										$postmimetype[] = 'video';
									break;

									case 'audio':
										$postmimetype[] = 'audio/mpeg';
										$postmimetype[] = 'audio/mp3';
										$postmimetype[] = 'audio';

									case 'documents':
										$postmimetype[] = 'text/csv';
										$postmimetype[] = 'text/plain';
										$postmimetype[] = 'text/xml';
										$postmimetype[] = 'text';
									break;

									default:
										$postmimetype = 'all';
									break;
								}
							}

							if (!is_array($postmimetype)) {
								$postmimetype = '';
							}
						}

						// Get Posts
						// Attempting to use wp_cache
						// $cache_name = "wpjm_{$wpjm_cpt}_post";
						// $pd_posts = wp_cache_get( $cache_name, "wpjm_cache" );
						// if ( false == $pd_posts ) {
							$args = array(
								'orderby' => $sortby,
								'order' => $sort,
								'posts_per_page' => $numberposts,
								'post_type' => $wpjm_cpt,
								'post_mime_type' => $postmimetype,
								'post_status' => (is_array($post_status)?(in_array('any',$post_status)?'any':$post_status):$post_status)
								);
							$pd_posts = get_posts($args);

						// 	wp_cache_set( $cache_name, $pd_posts, "wpjm_cache" );
						// }

						// Count the posts
						$pd_total_posts = count($pd_posts);

						// Get the labels for this post type
						$cpt_obj = get_post_type_object($wpjm_cpt);
						$cpt_labels = $cpt_obj->labels;

						// Set the iterator to zero
						$pd_i = 0;



						// If this is not hierarchical, get list of posts and display the <option>s
						if (!is_post_type_hierarchical($wpjm_cpt)) {

							$wpjm_string .= '<optgroup label="'.$cpt_labels->name.'">';

							if ($cpt_labels->name != 'Media') {

								if (isset($this->options['showaddnew']) && $this->options['showaddnew'] && current_user_can($post_type_object->cap->edit_posts)) {
									$wpjm_string .= '<option value="post-new.php?post_type=';
									$wpjm_string .= $cpt_obj->name;
									$wpjm_string .= '">+ Add New '.$cpt_labels->singular_name.' +</option>';
								}

							}

							// Order the posts by mime/type if this is attachments
							if ( ($wpjm_cpt == 'attachment') && ($sortby == 'mime_type') ) {
								function mime_sort($a, $b) {
									return strcmp($a->post_mime_type, $b->post_mime_type);
								}
								usort($pd_posts, "mime_sort");
							}

							// Loop through posts
							foreach ($pd_posts as $pd_post) {

								// Increase the interator by 1
								$pd_i++;

								// Open the <option> tag
								$wpjm_string .= '<option data-permalink="'.get_permalink($pd_post->ID).'" value="';
									// echo the edit link based on post ID
									$editLink = (is_admin() || (!isset($this->options['frontEndJump']) || !$this->options['frontEndJump']) ? get_edit_post_link($pd_post->ID) : get_permalink($pd_post->ID));
									$wpjm_string .= $editLink;
								$wpjm_string .= '"';

								// Check to see if you are currently editing this post
								// If so, make it the selected value
								if ( (isset($_GET['post']) && ($pd_post->ID == $_GET['post'])) || (isset($post) && ($pd_post->ID == $post->ID)) )
									$wpjm_string .= ' selected="selected"';

								if (!current_user_can($post_type_object->cap->edit_post,$pd_post->ID))
									$wpjm_string .= ' disabled="disabled"';

								// Set the color
								$wpjm_string .= ' style="color: '.$status_color[$pd_post->post_status].' !important;"';

								$wpjm_string .= '>';

								// If the setting to show ID's is true, show the ID in ()
								if (isset($this->options['showID'])) {
									if ( ($this->options['showID'] == true) && ($this->options['useChosen'] == 'true') && ($this->options['chosenTextAlign'] == 'right') ) {
										$wpjm_string .= '<span class="post-id">('.$pd_post->ID.')</span> ';
									}
								}

								// Print the post title
								$wpjm_string .= $this->wpjm_get_page_title($pd_post->post_title);

								if ($pd_post->post_status != 'publish' && $pd_post->post_status != 'inherit')
									$wpjm_string .= ' - '.$pd_post->post_status;

								if ($pd_post->post_type == 'attachment')
									$wpjm_string .= ' (' . $pd_post->post_mime_type . ')';

								if ($pd_post->post_status == 'future')
									$wpjm_string .= ' - '.$pd_post->post_date;

								// If the setting to show ID's is true, show the ID in ()
								if (isset($this->options['showID'])) {
									if ( ($this->options['showID'] == true) && ( (!$this->options['useChosen'] || $this->options['chosenTextAlign'] == 'left') ) ) {
										$wpjm_string .= ' <span class="post-id">('.$pd_post->ID.')</span>';
									}
								}



								// close the <option> tag
								$wpjm_string .= '</option>';
							} // foreach ($pd_posts as $pd_post)

							$wpjm_string .= '</optgroup>';

						} else {

							// If this a hierarchical post type, use the custom Walker class to create the page tree
							$orderedListWalker = new WPJM_Walker_PageDropDown();

							$wpjm_string .= '<optgroup label="'.$cpt_labels->name.'">';

							if (isset($this->options['showaddnew']) && $this->options['showaddnew'] && ( current_user_can($post_type_object->cap->edit_posts) || current_user_can($post_type_object->cap->edit_pages) ) ) {
								$wpjm_string .= '<option value="post-new.php?post_type=';
								$wpjm_string .= $cpt_obj->name;
								$wpjm_string .= '">+ Add New '.$cpt_labels->singular_name.' +</option>';
							}

							// Go through the non-published pages
							foreach ($post_status as $status) {

								if ($status == 'publish')
									continue;

								// Get pages
								$pd_posts_drafts = get_posts('orderby='.$sortby.'&order='.$sort.'&posts_per_page='.$numberposts.'&post_type='.$wpjm_cpt.'&post_status='.$status);


								// Loop through posts
								foreach ($pd_posts_drafts as $pd_post) {

									// Increase the interator by 1
									$pd_i++;

									// Open the <option> tag
									$wpjm_string .= '<option data-permalink="'.get_permalink($pd_post->ID).'" value="';
										// echo the edit link based on post ID
										$editLink = (is_admin() || (!isset($this->options['frontEndJump']) || !$this->options['frontEndJump']) ? get_edit_post_link($pd_post->ID) : get_permalink($pd_post->ID));
										$wpjm_string .= $editLink;
									$wpjm_string .= '"';

									// Check to see if you are currently editing this post
									// If so, make it the selected value
									if ( (isset($_GET['post']) && ($pd_post->ID == $_GET['post'])) || (isset($post) && ($pd_post->ID == $post->ID)) )
										$wpjm_string .= ' selected="selected"';

									if (!current_user_can($post_type_object->cap->edit_post,$pd_post->ID))
										$wpjm_string .= ' disabled="disabled"';

									// Set the color
									$wpjm_string .= ' style="color: '.$status_color[$pd_post->post_status].' !important;"';

									$wpjm_string .= '>';

								// If the setting to show ID's is true, show the ID in ()
								if ( (isset($this->options['showID']) && $this->options['showID'] == true) && (isset($this->options['useChosen']) && $this->options['useChosen'] == 'true') ) {
									$wpjm_string .= '<span class="post-id">('.$pd_post->ID.')</span> ';
								}
									// Print the post title
									$wpjm_string .= $this->wpjm_get_page_title($pd_post->post_title);

									if ($pd_post->post_status != 'publish')
										$wpjm_string .= ' - '.$status;

									if ($pd_post->post_status == 'future')
										$wpjm_string .= ' - '.$pd_post->post_date;

									// If the setting to show ID's is true, show the ID in ()
								if ( (isset($this->options['showID']) && $this->options['showID'] == true) && (isset($this->options['useChosen']) && isset($this->options['chosenTextAlign']) &&  (!$this->options['useChosen'] || $this->options['chosenTextAlign'] == 'left') ) ) {
									$wpjm_string .= ' <span class="post-id">('.$pd_post->ID.')</span>';
								}



									// close the <option> tag
									$wpjm_string .= '</option>';
								} // foreach ($pd_posts as $pd_post)


							}
							// Done with non-published pages
							if (is_array($post_status)) {

								if (in_array('publish',$post_status)) {

									$wpjm_string .= wp_list_pages(array('walker' => $orderedListWalker, 'post_type' => $wpjm_cpt, 'echo' => 0, 'depth' => $numberposts, 'sort_column' => $sortby, 'sort_order' => $sort));

								}

							} else if ($post_status == 'publish') {
								$wpjm_string .= wp_list_pages(array('walker' => $orderedListWalker, 'post_type' => $wpjm_cpt, 'echo' => 0, 'depth' => $numberposts, 'sort_column' => $sortby, 'sort_order' => $sort));
							}


							$wpjm_string .= '</optgroup>';
						} // end if (is_hierarchical)

					} // end foreach($wpjm_cpts)

				} // end if ($wpjm_cpts)

			} // end if ($custom_post_types)

			// Print the options page link
			if (current_user_can('activate_plugins')) {
				$wpjm_string .= '<optgroup label="// Jump Menu Options //">';
				$wpjm_string .= '<option value="'.admin_url().'options-general.php?page=wpjm-options">Jump Menu Options Page</option>';
				$wpjm_string .= '</optgroup>';
			}

			// Close the select drop down
			$wpjm_string .= '</select>';

			// set the transient
			// set_transient( $this->transient_name, $wpjm_string, 7*24*60*60 );

		// } else { // if there is a transient

		// 	$wpjm_string = $wpjm_transient;
		// 	$wpjm_string .= '<!-- This is using a transient -->';

		// }

		return $wpjm_string;

	} // end wpjm_page_dropdown()

	function wpjm_remove_transient() {
		delete_transient( $this->transient_name );
	}



	function wpjm_get_page_title( $pd_title )
	{
		if ( strlen($pd_title) > 50 )
		{
			return substr( $pd_title, 0, 50)."...";
		}
		else
		{
			return $pd_title;
		}
	}


	/*
	*  wpjm_install
	*
	*  @description: Installs the options
	*  @since: 3.0
	*  @created: 12/12/12
	*/

	function wpjm_install()
	{

		// Populate with default values
		if (get_option('wpjm_position'))
		{

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
				'useChosen' => 'true',
				'chosenTextAlign' => 'left',
				'showID' => 'false',
				'showaddnew' => 'true',
				'frontend' => 'true',
				'frontEndJump' => 'true',
				'backgroundColor' => get_option('wpjm_backgroundColor'),
				'fontColor' => get_option('wpjm_fontColor'),
				'borderColor' => get_option('wpjm_borderColor'),
				'postTypes' => $newPostTypes,
				'logoIcon' => get_option('wpjm_logoIcon'),
				'linkColor' => get_option('wpjm_linkColor'),
				'message' => get_option('wpjm_message'),
				'title' => "WP Jump Menu &raquo;",
				'statusColors' => array(
					'publish' => '',
					'pending' => '',
					'draft' => '',
					'auto-draft' => '',
					'future' => '',
					'private' => '',
					'inherit' => '',
					'trash' => ''
				)
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

			// If this is a new install, set the default options
			if (empty($this->options)) {
				$arr = array(
					'position' => 'wpAdminBar',
					'useChosen' => 'true',
					'chosenTextAlign' => 'left',
					'showID' => 'false',
					'showaddnew' => 'true',
					'frontend' => 'true',
					'frontEndJump' => 'true',
					'backgroundColor' => 'e0e0e0',
					'fontColor' => '787878',
					'borderColor' => '666666',
					'postTypes' => array(
						'page' => array(
							'show' => '1',
							'sortby' => 'menu_order',
							'sort' => 'ASC',
							'numberposts' => '0',
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
					'linkColor' => '1cd0d6',
					'message' => "Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>.",
					'title' => "WP Jump Menu &raquo;",
					'statusColors' => array(
						'publish' => '',
						'pending' => '',
						'draft' => '',
						'auto-draft' => '',
						'future' => '',
						'private' => '',
						'inherit' => '',
						'trash' => ''
					)
				);
				update_option('wpjm_options',$arr);
			} else {

				// Not a new install, but not an upgrade from old version, update post type status'
				if (!isset($this->options['postTypes']['post']['poststatus'])) {
					foreach($this->options['postTypes'] as $key => $value) {
						$this->options['postTypes'][$key]['poststatus'] = array('publish','draft');
					}
					update_option('wpjm_options',$this->options);
				}

				// Remove logo width if it is set
				if (isset($this->options['logoWidth'])) {
					unset($this->options['logoWidth']);
					update_option('wpjm_options',$this->options);
				}

				// Add title if it is not set
				if (!isset($this->options['title'])) {
					$this->options['title'] = "WP Jump Menu &raquo;";
					update_option('wpjm_options',$this->options);
				}

			}

		}

		update_option('wpjm_version',$this->version);
		return true;

	}

	static private function wpjm_uninstall() {
		delete_option('wpjm_options');
		delete_option('wpjm_version');
		return true;
	}

}


?>
