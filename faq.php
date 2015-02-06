<?php
/**
 * Plugin Name: Frequently Asked Questions
 * Plugin URI: http://wpdarko.com/faq/
 * Description: The most simple and effective way to show questions with answers on your website. Create new questions, add answers, drag and drop to reorder and copy-paste the shortcode into any post/page. Find support and information on the <a href="http://wpdarko.com/questions-answers/">plugin's page</a>. This free version is NOT limited and does not contain any ad. Check out the <a href='http://wpdarko.com/items/faq-pro/'>PRO version</a> for more great features.
 * Version: 1.0
 * Author: WP Darko
 * Author URI: http://wpdarko.com
 * License: GPL2
 */

function faq_free_pro_check() {
    if (is_plugin_active('faq-pro/faq_pro.php')) {
        
        function my_admin_notice(){
        echo '<div class="updated">
                <p><strong>PRO</strong> version is activated.</p>
              </div>';
        }
        add_action('admin_notices', 'my_admin_notice');
        
        deactivate_plugins(__FILE__);
    }
}

add_action( 'admin_init', 'faq_free_pro_check' );

/* adds stylesheet and script */
add_action( 'wp_enqueue_scripts', 'add_faq_scripts', 9999 );
function add_faq_scripts() {
	wp_enqueue_style( 'faq', plugins_url('css/faq_custom_style.min.css', __FILE__));
    wp_enqueue_script( 'faq', plugins_url('js/faq.min.js', __FILE__));
}

add_action( 'init', 'create_faq_type' );

function create_faq_type() {
  register_post_type( 'faq',
    array(
      'labels' => array(
        'name' => 'FAQs',
        'singular_name' => 'FAQ'
      ),
      'public' => true,
      'has_archive'  => false,
      'hierarchical' => false,
         'capabilities' => array(
    'edit_post'          => 'update_core',
    'read_post'          => 'update_core',
    'delete_post'        => 'update_core',
    'edit_posts'         => 'update_core',
    'edit_others_posts'  => 'update_core',
    'publish_posts'      => 'update_core',
    'read_private_posts' => 'update_core'
),
      'supports'     => array( 'title' ),
      'menu_icon'    => 'dashicons-plus',
    )
  );
}

/**
* Define the metabox and field configurations.
*
* @param array $meta_boxes
* @return array
*/
function faq_metaboxes( array $meta_boxes ) {
    $fields = array(
        array( 'id' => 'faq_space', 'type' => 'title', 'cols' => 12 ),
        array( 'id' => 'faq_question', 'name' => 'Question', 'type' => 'text', 'cols' => 12 ),
        array( 'id' => 'faq_answer', 'name' => 'Answer', 'type' => 'wysiwyg', 'options' => array(
        'textarea_rows' => 5
    ), 'cols' => 12),
    );
    
    $group_settings = array(
        array( 'id' => 'faq_title', 'name' => 'Block title*', 'desc' => '*Optional', 'type' => 'text' ),
        array( 'id' => 'faq_color', 'name' => 'Title\'s color', 'type' => 'colorpicker', 'default' => '#41db8e' ),
    );
    // Example of repeatable group. Using all fields.
    // For this example, copy fields from $fields, update I
    $group_fields = $fields;
    foreach ( $group_fields as &$field ) {
        $field['id'] = str_replace( 'field', 'gfield', $field['id'] );
    }
    $meta_boxes[] = array(
        'title' => 'Create/remove/sort questions',
        'pages' => 'faq',
        'fields' => array(
            array(
                'id' => 'faq_head',
                'type' => 'group',
                'repeatable' => true,
                'sortable' => true,
                'fields' => $group_fields,
                'desc' => 'Create new questions here and drag and drop to reorder.',
            )
        )
    );
    $meta_boxes[] = array(
        'title' => 'Settings',
        'pages' => 'faq',
        'context' => 'side',
        'priority' => 'high',
        'fields' => array(
            array(
                'id' => 'faq_settings_head',
                'type' => 'group',
                'fields' => $group_settings,
            )
        )
    );
    
    
    function faq_pro_side_meta() {
        return "<p style='font-size:14px; color:#333; font-style:normal;'>This free version is <strong>NOT</strong> limited and does <strong>not</strong> contain any ad. Check out the <a href='http://wpdarko.com/items/faq-pro/'><span style='color:#61d1aa !important;'>PRO version</span></a> for more great features.</p>";
    }
    
     $meta_boxes[] = array(
        'title' => 'Frequently Asked Questions PRO',
        'pages' => 'faq',
        'context' => 'side',
        'priority' => 'low',
        'fields' => array(
            array(
                'id' => 'faq_pro_head',
                'type' => 'group',
                'desc' => faq_pro_side_meta(),
            )
        )
    );
    
    return $meta_boxes;
}
add_filter( 'drkfr_meta_boxes', 'faq_metaboxes' );

if (!class_exists('drkfr_Meta_Box')) {
    require_once( 'drkfr/custom-meta-boxes.php' );
}

//shortcode columns
add_action( 'manage_faq_posts_custom_column' , 'dkfaq_custom_columns', 10, 2 );

function dkfaq_custom_columns( $column, $post_id ) {
    switch ( $column ) {
	case 'shortcode' :
		global $post;
		$slug = '' ;
		$slug = $post->post_name;
   
    
    	    $shortcode = '<span style="border: solid 3px lightgray; background:white; padding:7px; font-size:17px; line-height:40px;">[faq name="'.$slug.'"]</strong>';
	    echo $shortcode; 
	    break;
    }
}

function add_faq_columns($columns) {
    return array_merge($columns, 
              array('shortcode' => __('Shortcode'),
                    ));
}

add_filter('manage_faq_posts_columns' , 'add_faq_columns');

//faq shortcode
function faq_sc($atts) {
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));
	
    query_posts( array( 'post_type' => 'faq', 'name' => $name, ) );
    if ( have_posts() ) : while ( have_posts() ) : the_post();

    global $post;
    
	$questions = get_post_meta( get_the_id(), 'faq_head', false );
    $options = get_post_meta( get_the_id(), 'faq_settings_head', false );
  
    foreach ($options as $key => $option) {
        $faq_color = $option['faq_color'];
        $faq_title = $option['faq_title'];
    }

    $output .= '<div class="faq_wrap">';
        $output .= '<div class="faq faq_'.$name.'">';
    
        if ($option['faq_title']){
            $output .= '<h3 class="faq_title" style="color:'.$faq_color.'">'.$faq_title.'</h3>';
        }
    
            foreach ($questions as $key => $question) {
                
                $output .= '<div class="faq_question">';
                
                    $output .= '<a href="#" class="faq_q">'.$question["faq_question"].'</a><br/>';
                    $output .= '<p class="faq_a" style="display:none; overflow:hidden;">'.$question["faq_answer"];
                    $output .= '</p>';
                
                $output .= '</div>';
            }
    
        $output .= '</div>';
    $output .= '</div>';  

  endwhile; endif; wp_reset_query();
	
  return $output;

}
add_shortcode("faq", "faq_sc"); 

?>