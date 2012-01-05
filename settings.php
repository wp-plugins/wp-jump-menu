<?php
/**
 * settings.php
 *
 * The options page for WP Jump Menu
 *
 */

function wpjm_admin_init() {
	
	// Register our setting
	register_setting( 'wpjm_options', 'wpjm_options', 'wpjm_options_validate' );

	// Add the main section
	add_settings_section( 'wpjm_post_types', 'Post Types', 'wpjm_post_type_section_text', 'wpjm' );
	add_settings_section( 'wpjm_main', 'Styling Options', 'wpjm_section_text', 'wpjm-2' );
	

	// Add the fields
	add_settings_field( 'wpjm_position', 
			'Position of Jump Menu Bar', 
			'wpjm_position_radio', 
			'wpjm-2', 
			'wpjm_main' );

	add_settings_field( 'wpjm_showID',
			'Show ID next to post/page title',
			'wpjm_showID_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_barColors',
			'Jump Menu Bar Colors',
			'wpjm_barColors_checkbox',
			'wpjm-2',
			'wpjm_main' );

	/*
	add_settings_field( 'wpjm_backgroundColor',
			'Background Color',
			'wpjm_backgroundColor_text',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_fontColor',
			'Font Color',
			'wpjm_fontColor_text',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_borderColor',
			'Border Color',
			'wpjm_borderColor_text',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_linkColor',
			'Link Color',
			'wpjm_linkColor_text',
			'wpjm-2',
			'wpjm_main' );
	*/

	add_settings_field( 'wpjm_statusColors',
			'Status Colors',
			'wpjm_statusColors_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_logoIcon',
			'Logo Icon URL',
			'wpjm_logoIcon_text',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_message',
			'Message',
			'wpjm_message_textarea',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_postTypes',
			'Post Types to Include',
			'wpjm_postTypes_checkbox',
			'wpjm',
			'wpjm_post_types' );
/*
	add_settings_field( 'id',
			'Label',
			'callback',
			'wpjm',
			'wpjm_main' );
*/
	

} // wpjm_admin_init()



function wpjm_section_text() {
	echo '<p class="description">These settings will change the position and colors of the Jump Menu.</p>';
}

function wpjm_post_type_section_text() {
	echo '<p class="description">Choose the post types you want to include in the Jump Menu.<br/>Click and drag the rows to change the order in which they appear in the Jump Menu.</p>';
}

// --------------------------------
// Callbacks for fields
// --------------------------------
//

// Position
function wpjm_position_radio() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="radio" value='top' name="wpjm_options[position]" id="wpjm_position" <?php checked($options['position'], 'top'); ?> />
		 Top of screen<br/>
	<input type="radio" value="bottom" name="wpjm_options[position]" id="wpjm_position" <?php checked($options['position'], 'bottom'); ?> />
		 Bottom of screen<br/>
	<input type="radio" value="wpAdminBar" name="wpjm_options[position]" id="wpjm_position" <?php checked($options['position'], 'wpAdminBar'); ?> />
		WP Admin Bar
	
</div>
<?php
}

// Show ID
function wpjm_showID_checkbox() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[showID]" id="wpjm_showID" <?php checked($options['showID'], 'true'); ?> />
</div>
<?php
}


// Sort Pages by
//
function wpjm_sortpagesby_select() {
	$options = get_option('wpjm_options');
?>
<div>
	<select name="wpjm_options[sortpagesby]" id="wpjm_sortpagesby">
		<option value="menu_order"<?php echo ($options['sortpagesby']=='menu_order'?' selected="selected"':''); ?>>Menu Order</option>
		<option value="post_author"<?php echo ($options['sortpagesby']=='post_author'?' selected="selected"':''); ?>>Author</option>
		<option value="post_date"<?php echo ($options['sortpagesby']=='post_date'?' selected="selected"':''); ?>>Date</option>
		<option value="ID"<?php echo ($options['sortpagesby']=='ID'?' selected="selected"':''); ?>>ID</option>
		<option value="post_modified"<?php echo ($options['sortpagesby']=='post_modified'?' selected="selected"':''); ?>>Modified</option>
		<option value="post_name"<?php echo ($options['sortpagesby']=='post_name'?' selected="selected"':''); ?>>Name</option>
		<option value="post_parent"<?php echo ($options['sortpagesby']=='post_parent'?' selected="selected"':''); ?>>Parent</option>
		<option value="post_title"<?php echo ($options['sortpagesby']=='post_title'?' selected="selected"':''); ?>>Title</option>
		
	</select>
	<span class="description">Pages default is "Menu Order" which maintains hierachy.</span>
</div>
<?php
}

// Sort Pages Order
//
function wpjm_sortpages_radio() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="radio" value="ASC" name="wpjm_options[sortpages]" id="wpjm_sortpages"<?php echo ($options['sortpages']=='ASC'?' checked="checked"':''); ?> /> Ascending<br/>
	<input type="radio" value="DESC" name="wpjm_options[sortpages]" id="wpjm_sortpages"<?php echo ($options['sortpages']=='DESC'?' checked="checked"':''); ?> /> Descending
</div>

<?php
}


// Sort Posts by
//
function wpjm_sortpostsby_select() {
	$options = get_option('wpjm_options');
?>
<div>
	<select name="wpjm_options[sortpostsby]" id="wpjm_sortpostsby">
		<option value="menu_order"<?php echo ($options['sortpostsby']=='menu_order'?' selected="selected"':''); ?>>Menu Order</option>
		<option value="author"<?php echo ($options['sortpostsby']=='author'?' selected="selected"':''); ?>>Author</option>
		<option value="category"<?php echo ($options['sortpostsby']=='category'?' selected="selected"':''); ?>>Category</option>
		<option value="content"<?php echo ($options['sortpostsby']=='content'?' selected="selected"':''); ?>>Content</option>
		<option value="date"<?php echo ($options['sortpostsby']=='date'?' selected="selected"':''); ?>>Date</option>
		<option value="ID"<?php echo ($options['sortpostsby']=='ID'?' selected="selected"':''); ?>>ID</option>
		<option value="mime_type"<?php echo ($options['sortpostsby']=='mime_type'?' selected="selected"':''); ?>>Mime Type</option>
		<option value="modified"<?php echo ($options['sortpostsby']=='modified'?' selected="selected"':''); ?>>Modified</option>
		<option value="name"<?php echo ($options['sortpostsby']=='name'?' selected="selected"':''); ?>>Name</option>
		<option value="parent"<?php echo ($options['sortpostsby']=='parent'?' selected="selected"':''); ?>>Parent</option>
		<option value="password"<?php echo ($options['sortpostsby']=='password'?' selected="selected"':''); ?>>Password</option>
		<option value="rand"<?php echo ($options['sortpostsby']=='rand'?' selected="selected"':''); ?>>Random</option>
		<option value="status"<?php echo ($options['sortpostsby']=='status'?' selected="selected"':''); ?>>Status</option>
		<option value="title"<?php echo ($options['sortpostsby']=='title'?' selected="selected"':''); ?>>Title</option>
		<option value="type"<?php echo ($options['sortpostsby']=='type'?' selected="selected"':''); ?>>Type</option>
	</select>
	<span class="description">Posts default is "Date" which orders by post date.</span>
</div>
<?php
}


// Sort Posts Order
//
function wpjm_sortposts_radio() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="radio" value="ASC" name="wpjm_options[sortposts]" id="wpjm_sortposts"<?php echo ($options['sortposts']=='ASC'?' checked="checked"':''); ?> /> Ascending<br/>
	<input type="radio" value="DESC" name="wpjm_options[sortposts]" id="wpjm_sortposts"<?php echo ($options['sortposts']=='DESC'?' checked="checked"':''); ?> /> Descending
</div>
<?php
}


// Number of Posts
//
function wpjm_numberposts_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="text" name="wpjm_options[numberposts]" id="wpjm_numberposts" value="<?php echo $options['numberposts']; ?>" size="3" />
	<span class="description">-1 to display all posts.</span>
</div>
<?php
}

// Jump Menu Bar Colors
//
function wpjm_barColors_checkbox() {
	$options = get_option('wpjm_options');
?>
<div>
	<span class="description">Click on the input to select a color, or enter the hex value.<br/>When you are choosing a color, the jump menu (if top or bottom is selected) will give you a live preview of your color changes.<br/>Changes are NOT saved until you click the "Save Changes" button.</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor" value="<?php echo $options['backgroundColor']; ?>" rel="#jump_menu|backgroundColor" size="6" />
	<span class="description">Background Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor" value="<?php echo $options['borderColor']; ?>" rel="#jump_menu|borderColor" size="6" />
	<span class="description">Border Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor" value="<?php echo $options['fontColor']; ?>" rel="#jump_menu|color" size="6" />
	<span class="description">Font Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor" value="<?php echo $options['linkColor']; ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6" />
	<span class="description">Link Color</span>
<div>
<?php
}

// Status Colors
//
function wpjm_statusColors_checkbox() {
	$options = get_option('wpjm_options');
?>
<div>
	<span class="description">Click on the input to select a color, or enter the hex value.</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][publish]" id="wpjm_statusColors_publish" value="<?php echo $options['statusColors']['publish']; ?>" size="6" />
	<span class="description">Publish</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][pending]" id="wpjm_statusColors_pending" value="<?php echo $options['statusColors']['pending']; ?>" size="6" />
	<span class="description">Pending</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][draft]" id="wpjm_statusColors_draft" value="<?php echo $options['statusColors']['draft']; ?>" size="6" />
	<span class="description">Draft</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][auto-draft]" id="wpjm_statusColors_auto-draft" value="<?php echo $options['statusColors']['auto-draft']; ?>" size="6" />
	<span class="description">Auto-Draft</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][future]" id="wpjm_statusColors_future" value="<?php echo $options['statusColors']['future']; ?>" size="6" />
	<span class="description">Future</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][private]" id="wpjm_statusColors_private" value="<?php echo $options['statusColors']['private']; ?>" size="6" />
	<span class="description">Private</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][inherit]" id="wpjm_statusColors_inherit" value="<?php echo $options['statusColors']['inherit']; ?>" size="6" />
	<span class="description">Inherit</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][trash]" id="wpjm_statusColors_trash" value="<?php echo $options['statusColors']['trash']; ?>" size="6" />
	<span class="description">Trash</span>
</div>
<?php
}

// Background Color
//
function wpjm_backgroundColor_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor" value="<?php echo $options['backgroundColor']; ?>" rel="#jump_menu|backgroundColor" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Font Color
//
function wpjm_fontColor_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor" value="<?php echo $options['fontColor']; ?>" rel="#jump_menu|color" size="6" />
<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Border Color
//
function wpjm_borderColor_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor" value="<?php echo $options['borderColor']; ?>" rel="#jump_menu|borderColor" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Link Color
//
function wpjm_linkColor_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor" value="<?php echo $options['linkColor']; ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
<div>
<?php
}


// Logo Icon URL
//
function wpjm_logoIcon_text() {
	$options = get_option('wpjm_options');
?>
<div>
	<input type="text" name="wpjm_options[logoIcon]" id="wpjm_logoIcon" value="<?php echo $options['logoIcon']; ?>" size="100" />
</div>
<span class="description">*Optional: The URL to the icon displayed next to the message in the jump bar.</span>
<?php
}


// Message
//
function wpjm_message_textarea() {
	$options = get_option('wpjm_options');
?>
<div>
	<textarea name="wpjm_options[message]" id="wpjm_message" cols="60" rows="3" ><?php echo $options['message']; ?></textarea>
</div>
<span class="description">*Optional: Short message to include on left side of Jump bar.  HTML is ok.</span>
<?php
}


// Post Types
//
function wpjm_postTypes_checkbox() {

	// Get the current options array
	$options = get_option('wpjm_options');

?>
	<script>
	// Hide the left TH column next to the table of post types
	jQuery(function($){
		$('#wpjm-post-types-table').parent().parent().prev().hide();
	});
	</script>

	<div>
		
		<table id="wpjm-post-types-table" class="wp-list-table widefat ">
			<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" class="wpjm-post-types-title-col">Post Types</th>
				<th scope="col" class="wpjm-order-by-col">Order By</th>
				<th scope="col" class="wpjm-order-col">Order</th>
				<th scope="col" class="wpjm-numberposts-col">Show</th>
				<th scope="col" class="wpjm-showdrafts-col">Post Status</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" class="wpjm-post-types-title-col">Post Types</th>
				<th scope="col" class="wpjm-order-by-col">Order By</th>
				<th scope="col" class="wpjm-order-col">Order</th>
				<th scope="col" class="wpjm-numberposts-col">Show</th>
				<th scope="col" class="wpjm-showdrafts-col">Post Status</th>
			</tr>
			</tfoot>
			<tbody>
		<?php 
			
			// Get the array of registered post types (array of objects)
			$post_types = get_post_types('','objects'); 

			// Get the array of selected post types
			$selected_post_types_arr = $options['postTypes'];
				
				// Make an array of only the keys from the selected post types
				$array2 = array_keys($selected_post_types_arr);

				// A function to sort the $post_type array by the $selected array
				function sortArrayByArray($array,$orderArray) {
				    $ordered = array();
				    foreach($orderArray as $key) {
				        if(array_key_exists($key,$array)) {
				                $ordered[$key] = $array[$key];
				                unset($array[$key]);
				        }
				    }
				    return $ordered + $array;
				}

				// And... sort it, returning an organized array;
				// with the unselected post types at the end
				$custom_array_order = sortArrayByArray($post_types, $array2);

				 
		?>
		
			<?php foreach ($custom_array_order as $pt) { 
				if ( ($pt->name == 'nav_menu_item') || ($pt->name == 'revision') ) continue;
				?>
			<tr>
				<th class="check-column" scope="row">
					<input type="checkbox" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][show]" id="wpjm_postType_<?php echo $pt->name; ?>" value="1" <?php checked($options['postTypes'][$pt->name]['show'], 1 ); ?> />
				</td>
				<td>
					<?php echo $pt->labels->name; ?>
				</td>
				<td>
					<select name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sortby]" id="wpjm_sort<?php echo $pt->name; ?>by">
						<option value="menu_order" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'menu_order'); ?>>Menu Order</option>
						<option value="author" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'author'); ?>>Author</option>
						<option value="date" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'date'); ?>>Date</option>
						<option value="ID" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'ID'); ?>>ID</option>
						<option value="modified" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'modified'); ?>>Modified</option>
						<option value="comment_count" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'comment_count'); ?>>Comment Count</option>
						<option value="parent" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'parent'); ?>>Parent</option>
						<option value="title" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'title'); ?>>Title</option>
					</select>
					<br/><span class="description"><a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">Documentation</a></span>
				</td>
				<td>
					<div>
						<input type="radio" value="ASC" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]" id="wpjm_sort<?php echo $pt->name; ?>" <?php checked($options['postTypes'][$pt->name]['sort'], 'ASC' ); ?> /> ASC <span class="description">(a-z, 1-10)</span>
						<br>
						<input type="radio" value="DESC" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]" id="wpjm_sort<?php echo $pt->name; ?>" <?php checked($options['postTypes'][$pt->name]['sort'], 'DESC' ); ?> /> DESC <span class="description">(z-a, 10-1)</span>
					</div>
				</td>
				<td>
					<div>
						<input type="text" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][numberposts]" id="wpjm_number<?php echo $pt->name; ?>" value="<?php echo $options['postTypes'][$pt->name]['numberposts']; ?>" size="3" />
						<br/><span class="description">How many posts/pages to show.<br/>-1 to display all.</span>
					</div>
				</td>
				<td>
					<div style="float: left; margin-right: 20px;">
						<input type="checkbox" value="publish" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('publish',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Publish<br/>

						<input type="checkbox" value="pending" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('pending',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Pending<br/>

						<input type="checkbox" value="draft" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus']))  echo (in_array('draft',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Draft<br/>

						<input type="checkbox" value="auto-draft" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('auto-draft',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Auto-Draft<br/>

						<input type="checkbox" value="future" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('future',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Future<br/>

						
						
					</div>
					<div style="float: left;">
					<input type="checkbox" value="private" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('private',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Private<br/>

						<input type="checkbox" value="inherit" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('inherit',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Inherit<br/>

						<input type="checkbox" value="trash" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('trash',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Trash<br/>

						<input type="checkbox" value="any" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($options['postTypes'][$pt->name]['poststatus'])) echo (in_array('any',$options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Any<br/>
						</div>
						<div style="clear: both;"><span class="description">NOTE: Trash items will only display if Any is NOT selected.<br/>NOTE: If your items are not showing up, try choosing "Inherit" or "Any".</span></div>
				</td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		<br>
	</div>
	
<?php
}


// TODO: Continue adding the rest of the fields from over there ----->

// validate our options

function wpjm_options_validate( $input ) {
	$newinput = $input;
	foreach($newinput['postTypes'] as $key => $value) {
		if (!isset($newinput['postTypes'][$key]['show'])) {
			unset($newinput['postTypes'][$key]);
		} else {
			if (!isset($newinput['postTypes'][$key]['sort'])) {
				$newinput['postTypes'][$key]['sort'] = 'ASC';
			}
			if (empty($newinput['postTypes'][$key]['numberposts'])) {
				$newinput['postTypes'][$key]['numberposts'] = '-1';
			}
			if (!isset($newinput['postTypes'][$key]['poststatus'])) {
				$newinput['postTypes'][$key]['poststatus'] = array('publish');
			}
			
		}
		
	}
	return $newinput;
}


?>