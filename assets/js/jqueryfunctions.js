jQuery(document).ready(function() {

	jQuery('#wp-pdd').change(function() {
	window.location = "post.php?action=edit&post="+jQuery(this).val();
	});

});