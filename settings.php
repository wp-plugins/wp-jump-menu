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
	add_settings_section( 'wpjm_main', 'Styling Options', 'wpjm_section_text', 'wpjm' );
	

	// Add the fields
	add_settings_field( 'wpjm_position', 
			'Position of Jump Menu Bar', 
			'wpjm_position_radio', 
			'wpjm', 
			'wpjm_main' );

	/*add_settings_field( 'wpjm_sortpagesby',
			'Sort Pages By',
			'wpjm_sortpagesby_select',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_sortpages',
			'Sort Pages Order',
			'wpjm_sortpages_radio',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_sortpostsby',
			'Sort Posts By',
			'wpjm_sortpostsby_select',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_sortposts',
				'Sort Posts Order',
			'wpjm_sortposts_radio',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_numberposts',
			'Number of Posts to Display',
			'wpjm_numberposts_text',
			'wpjm',
			'wpjm_main' );
	*/

	add_settings_field( 'wpjm_backgroundColor',
			'Background Color',
			'wpjm_backgroundColor_text',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_fontColor',
			'Font Color',
			'wpjm_fontColor_text',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_borderColor',
			'Border Color',
			'wpjm_borderColor_text',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_linkColor',
			'Link Color',
			'wpjm_linkColor_text',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_logoIcon',
			'Logo Icon URL',
			'wpjm_logoIcon_text',
			'wpjm',
			'wpjm_main' );

	add_settings_field( 'wpjm_message',
			'Message',
			'wpjm_message_textarea',
			'wpjm',
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
		 Bottom of screen
	
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
<span class="description">Short message to include on left side of Jump bar.  HTML is ok.</span>
<?php
}


// Post Types
//
function wpjm_postTypes_checkbox() {
	$options = get_option('wpjm_options');
?>
	<script>
	jQuery(function($){
		$('#wpjm-post-types-table').parent().parent().prev().hide();
	});
	</script>
	<div>
		
		<table id="wpjm-post-types-table" class="wp-list-table widefat fixed">
			<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" class="wpjm-post-types-title-col">Post Types</th>
				<th scope="col" class="wpjm-order-by-col">Order By</th>
				<th scope="col" class="wpjm-order-col">Order</th>
				<th scope="col" class="wpjm-numberposts-col">Show</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th scope="col" class="wpjm-post-types-title-col">Post Types</th>
				<th scope="col" class="wpjm-order-by-col">Order By</th>
				<th scope="col" class="wpjm-order-col">Order</th>
				<th scope="col" class="wpjm-numberposts-col">Show</th>
			</tr>
			</tfoot>
			<tbody>
		<?php 
			$post_types = get_post_types(array('_builtin'=>true),'objects'); 
			// $selected_post_types_arr = explode(",",$options['postTypes']);
			$selected_post_types_arr = $options['postTypes'];
		?>
		
			<?php foreach ($post_types as $pt) { 
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
						<option value="post_author" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_author'); ?>>Author</option>
						<option value="post_date" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_date'); ?>>Date</option>
						<option value="ID" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'ID'); ?>>ID</option>
						<option value="post_modified" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_modified'); ?>>Modified</option>
						<option value="post_name" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_name'); ?>>Name</option>
						<option value="post_parent" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_parent'); ?>>Parent</option>
						<option value="post_title" <?php selected( $options['postTypes'][$pt->name]['sortby'], 'post_title'); ?>>Title</option>
					</select>
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
						<input type="text" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][numberposts]" id="wpjm_number<?php echo $pt->name; ?>" value="<?php echo $options['postTypes'][$pt->name]['numberposts']; ?>" size="3" /><br/>
						<span class="description">-1 to display all</span>
					</div>
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
		}
		
	}
	return $newinput;
}


?>