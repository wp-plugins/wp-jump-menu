<?php
// Custom Walker Class to walk through the page/custom post type hierarchy tree
class WPJM_Walker_PageDropDown extends Walker_PageDropDown {

	var $tree_type = "page";

	function start_el(&$output, $page, $depth, $args) {

		global $current_user;
		
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

		$pad = str_repeat(' &#8212;', $depth * 1);


		$output .= "\t<option class=\"level-$depth\" value=\"".get_edit_post_link($page->ID)."\"";
		if (isset($_GET['post']) && ($page->ID == $_GET['post']))
			$output .= ' selected="selected"';

		$post_type_object = get_post_type_object( $args['post_type'] );

		if (!current_user_can($post_type_object->cap->edit_post,$page->ID))
			$output .= ' disabled="disabled"';

			$output .= ' style="color: '.$status_color['publish'].' !important;"';
		$output .= '>';
		$title = apply_filters( 'list_pages', $page->post_title );
		if ($options['useChosen'] == 'true') {
			$output .= ( $options['showID'] == true ? "<span class='post-id'>(" .$page->ID . ")</span> " : '' ) . esc_html( $title ) . $pad;
		} else {
			$output .= $pad . ' ' . esc_html( $title ) . ( $options['showID'] == true ? "<span class='post-id'>(" .$page->ID . ")</span> " : '' );
		}
		
		$output .= "</option>\n";
	}
}
// end WPJM_Walker_PageDropDown class
?>