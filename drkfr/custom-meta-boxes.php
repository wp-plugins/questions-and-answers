<?php
if ( ! defined( 'drkfr_DEV') )
	define( 'drkfr_DEV', false );

if ( ! defined( 'drkfr_PATH') )
	define( 'drkfr_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'drkfr_URL' ) )
	define( 'drkfr_URL', plugins_url( '', __FILE__ ) );

include_once( drkfr_PATH . '/classes.fields.php' );
include_once( drkfr_PATH . '/class.drkfr-meta-box.php' );

// Make it possible to add fields in locations other than post edit screen.
include_once( drkfr_PATH . '/fields-anywhere.php' );

// include_once( drkfr_PATH . '/example-functions.php' );

/**
 * Get all the meta boxes on init
 *
 * @return null
 */
function drkfr_init() {

	if ( ! is_admin() )
		return;

	// Load translations
	$textdomain = 'drkfr';
	$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

	// By default, try to load language files from /wp-content/languages/custom-meta-boxes/
	load_textdomain( $textdomain, WP_LANG_DIR . '/custom-meta-boxes/' . $textdomain . '-' . $locale . '.mo' );
	load_textdomain( $textdomain, drkfr_PATH . '/languages/' . $textdomain . '-' . $locale . '.mo' );

	$meta_boxes = apply_filters( 'drkfr_meta_boxes', array() );

	if ( ! empty( $meta_boxes ) )
		foreach ( $meta_boxes as $meta_box )
			new drkfr_Meta_Box( $meta_box );

}
add_action( 'init', 'drkfr_init', 50 );

/**
 * Return an array of built in available fields
 *
 * Key is field name, Value is class used by field.
 * Available fields can be modified using the 'drkfr_field_types' filter.
 *
 * @return array
 */
function _drkfr_available_fields() {

	return apply_filters( 'drkfr_field_types', array(
		'text'				=> 'drkfr_Text_Field',
		'text_small' 		=> 'drkfr_Text_Small_Field',
		'text_url'			=> 'drkfr_URL_Field',
		'url'				=> 'drkfr_URL_Field',
		'radio'				=> 'drkfr_Radio_Field',
		'checkbox'			=> 'drkfr_Checkbox',
		'file'				=> 'drkfr_File_Field',
		'image' 			=> 'drkfr_Image_Field',
		'wysiwyg'			=> 'drkfr_wysiwyg',
		'textarea'			=> 'drkfr_Textarea_Field',
		'textarea_code'		=> 'drkfr_Textarea_Field_Code',
		'select'			=> 'drkfr_Select',
		'taxonomy_select'	=> 'drkfr_Taxonomy',
		'post_select'		=> 'drkfr_Post_Select',
		'date'				=> 'drkfr_Date_Field',
		'date_unix'			=> 'drkfr_Date_Timestamp_Field',
		'datetime_unix'		=> 'drkfr_Datetime_Timestamp_Field',
		'time'				=> 'drkfr_Time_Field',
		'colorpicker'		=> 'drkfr_Color_Picker',
		'title'				=> 'drkfr_Title',
		'group'				=> 'drkfr_Group_Field',
		'gmap'				=> 'drkfr_Gmap_Field',
	) );

}

/**
 * Get a field class by type
 *
 * @param  string $type
 * @return string $class, or false if not found.
 */
function _drkfr_field_class_for_type( $type ) {

	$map = _drkfr_available_fields();

	if ( isset( $map[$type] ) )
		return $map[$type];

	return false;

}

/**
 * For the order of repeatable fields to be guaranteed, orderby meta_id needs to be set.
 * Note usermeta has a different meta_id column name.
 *
 * Only do this for older versions as meta is now ordered by ID (since 3.8)
 * See http://core.trac.wordpress.org/ticket/25511
 *
 * @param  string $query
 * @return string $query
 */
function drkfr_fix_meta_query_order($query) {

    $pattern = '/^SELECT (post_id|user_id), meta_key, meta_value FROM \w* WHERE post_id IN \([\d|,]*\)$/';

    if (
            0 === strpos( $query, "SELECT post_id, meta_key, meta_value" ) &&
            preg_match( $pattern, $query, $matches )
    ) {

            if ( isset( $matches[1] ) && 'user_id' == $matches[1] )
                    $meta_id_column = 'umeta_id';
            else
                    $meta_id_column = 'meta_id';

            $meta_query_orderby = ' ORDER BY ' . $meta_id_column;

            if ( false === strpos( $query, $meta_query_orderby ) )
                    $query .= $meta_query_orderby;

    }

    return $query;

}

if ( version_compare( get_bloginfo( 'version' ), '3.8', '<' ) )
	add_filter( 'query', 'drkfr_fix_meta_query_order', 1 );