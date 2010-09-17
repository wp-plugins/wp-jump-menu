jQuery(document).ready(function() {

	jQuery('#wp-pdd').change(function() {
	window.location = "post.php?action=edit&post="+jQuery(this).val();
	});
	
	if (jQuery('#wpjm-options-form').length > 0) {
		jQuery('input.colorPicker').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				jQuery(el).val(hex);
				jQuery(el).ColorPickerHide();
			},
			onBeforeShow: function() {
				jQuery(this).ColorPickerSetColor(this.value);
			}
		}).bind('keyup', function() {
			jQuery(this).ColorPickerSetColor(this.value);
		});
	}

});