/**
 * ColorPickers
 */

drkfr.addCallbackForInit( function() {

	// Colorpicker
	jQuery('input:text.drkfr_colorpicker').wpColorPicker();

} );

drkfr.addCallbackForClonedField( 'drkfr_Color_Picker', function( newT ) {

	// Reinitialize colorpickers
    newT.find('.wp-color-result').remove();
	newT.find('input:text.drkfr_colorpicker').wpColorPicker();

} );