 
function showHideFields( show, hide, toggle ) {
// LOOK AT BELOW LINK...
// https://github.com/KristianAbrahamsen/jquery.arrayUtilities

	show = jQuery(show).toUniqueArray();
	hide = jQuery(hide).toUniqueArray();
	
	if ( toggle == "SHOW" ) {
		show.map( function(item) {
				if ( -1 < hide.lastIndexOf(item) ) {
					hide.splice( hide.lastIndexOf(item), 1 );
				}
		});
	} else {
		hide.map( function(item) {
				if ( -1 < show.lastIndexOf(item) ) {
					show.splice( show.lastIndexOf(item), 1 );
				}
		});
	}
	show.map( function(item) {
		if ( jQuery('form #' + item).length == 1 ) {
			var nToggleField = jQuery('form #' + item);
		} else {
			var nToggleField = jQuery('form [name=\"' + item + '\"]');
		}
		if ( jQuery(nToggleField).parents('.control-group').length == 1 ) {
			jQuery(nToggleField).parents('.control-group').formFlowShow();
		} else if ( jQuery(nToggleField).parents('.accordion-heading').length == 1 ) {
			jQuery(nToggleField).parents('.accordion').formFlowShow();
		} else {
			jQuery(nToggleField).formFlowShow();
			jQuery('form label[for=\"' + item + '\"]').formFlowShow();
			jQuery(nToggleField).parents('label').formFlowShow();
			jQuery(nToggleField).next('span').formFlowShow();
		}
		if ( jQuery(nToggleField).data('required') == 1 ) {
			jQuery(nToggleField).attr('required', 'required');
		}
	});
	hide.map( function(item) {
		if ( jQuery('form #' + item).length == 1 ) {
			var nToggleField = jQuery('form #' + item);
		} else {
			var nToggleField = jQuery('form [name=\"' + item + '\"]');
		}
		if ( jQuery(nToggleField).parents('.control-group').length == 1 ) {
			jQuery(nToggleField).removeAttr('required').parents('.control-group').formFlowHide();
		} else if ( jQuery(nToggleField).parents('.accordion-heading').length == 1 ) {
			jQuery(nToggleField).parents('.accordion').formFlowHide();
		} else {
			jQuery(nToggleField).removeAttr('required').formFlowHide();
			jQuery('form label[for=\"' + item + '\"]').formFlowHide();
			jQuery(nToggleField).parents('label').formFlowHide();
			jQuery(nToggleField).next('span').formFlowHide();
		}

	});
}

/*  */
jQuery.fn.formFlowHide = function() {
	//if ( jQuery(this).css('display') == 'none' ) {
	//	jQuery(this).addClass('hide');
	//	jQuery(this).data('dblhide', true);
	//} else {
		jQuery(this).fadeOut( 400 );
	//}
};

/*  */
jQuery.fn.formFlowShow = function() {
	//if ( jQuery(this).data('dblhide') == true ) {
	//	jQuery(this).removeClass('hide');
	//	jQuery(this).data('dblhide', false);
	//} else {
		jQuery(this).fadeIn( 400 );
	//}
};

/*  */
jQuery.fn.toUniqueArray = function() {
	var newArray = []
	jQuery.each( this, function( index, value ) {
		if ( jQuery.inArray( value, newArray ) === -1 ) {
			newArray.push( value );
		}
	});
	return newArray;
};

/* Resets the form data.  Causes all form elements to be reset to their original value. */
jQuery.fn.resetForm = function() {
	return this.each(function() {
		// Guard against an input with the name of 'reset'. Note that IE reports the reset function as an 'object'
		if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType)) {
			this.reset();
		}
	});
};