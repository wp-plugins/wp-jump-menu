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
			},
			onChange: function(hsb, hex, rgb, el) {
				//console.log('hex change: '+hex);
				//console.log(el);
				var elRel = jQuery(el).attr('rel');
				//console.log(elRel);
				elRel = elRel.split('|');
				//console.log(elRel);
				jQuery( elRel[0] )
					.css( elRel[1], '#' + hex);
			}
		}).bind('keyup', function() {
			jQuery(this).ColorPickerSetColor(this.value);
		});
		/*jQuery('#wpjm_backgroundColor').ColorPicker({
			onSubmit: function(hsb,hex,rgb,el) {
				jQuery(el).val(hex);
				jQuery(el).ColorPickerHide();
			},
			onBeforeShow: function() {
				jQuery(this).ColorPickerSetColor(this.value);
			},
			onChange: function (hsb, hex, rgb) {
				jQuery('#jump_menu').css('backgroundColor', '#' + hex);
			}
			}).bind('keyup',function() {
				jQuery(this).ColorPickerSetColor(this.value);
			});*/

	}

});