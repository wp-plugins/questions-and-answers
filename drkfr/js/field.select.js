var drkfrSelectInit = function() {

	jQuery( '.drkfr_select' ).each( function() {

		var el = jQuery(this);
 		var fieldID = el.attr( 'data-field-id'); // JS Friendly ID

 		// If fieldID is set
 		// If fieldID options exist
 		// If Element is not hidden template field.
 		// If elemnt has not already been initialized.
 		if ( fieldID && fieldID in window.drkfr_select_fields && el.is( ':visible' ) && ! el.hasClass( 'select2-added' ) ) {

 			// Get options for this field.
 			options = window.drkfr_select_fields[fieldID];

			el.addClass( 'select2-added' ).select2( options );

 		}

	})

};

// Hook this in for all the required fields.
drkfr.addCallbackForInit( drkfrSelectInit );
drkfr.addCallbackForClonedField( 'drkfr_Select', drkfrSelectInit );
drkfr.addCallbackForClonedField( 'drkfr_Post_Select', drkfrSelectInit );
drkfr.addCallbackForClonedField( 'drkfr_Taxonomy', drkfrSelectInit );
