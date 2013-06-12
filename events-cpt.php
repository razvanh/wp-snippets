<?php
// Hook into the 'init' action
add_action( 'init', 'custom_post_type', 0 );
/**
 * Implement the Custom Header feature
 */
//require( get_template_directory() . '/inc/custom-header.php' );

//register CPT for events

function register_custom_event_type() {
    $labels = array(
        'name'                => _x( 'Events', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Event', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Events', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Event:', 'text_domain' ),
		'all_items'           => __( 'All Events', 'text_domain' ),
		'view_item'           => __( 'View Event', 'text_domain' ),
		'add_new_item'        => __( 'Add New Event', 'text_domain' ),
		'add_new'             => __( 'New Event', 'text_domain' ),
		'edit_item'           => __( 'Edit Event', 'text_domain' ),
		'update_item'         => __( 'Update Event', 'text_domain' ),
		'search_items'        => __( 'Search events', 'text_domain' ),
		'not_found'           => __( 'No events found', 'text_domain' ),
		'not_found_in_trash'  => __( 'No events found in Trash', 'text_domain' ),
    );
    
    $rewrite = array(
		'slug'                => 'events',
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	
    $args = array(
        'label'               => __( 'event', 'text_domain' ),
		'description'         => __( 'Upcoming events', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt','revisions',),
		'taxonomies'          => array('post_tag' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 6,
		//'menu_icon'           => get_stylesheet_directory_uri() . '/img/events.png',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => $rewrite,
		'capability_type'     => 'post',
    );
    register_post_type('event', $args);
}
add_action('init', 'register_custom_event_type',0);

//Add meta box for the container of our custom fields

add_action('add_meta_boxes', 'add_events_fields_box');
 
function add_events_fields_box() {
    add_meta_box('events_fields_box_id', 'Event Info', 'display_event_info_box', 'event');
}

//Add custom events fields (start date & end date) 
// I am using jquery.timepicker here (http://jonthornton.github.io/jquery-timepicker/)
function display_event_info_box() {
    global $post;
    $values = get_post_custom($post->ID); // this is used for the edit event screen so we need to get the current values
    $eve_sta_date = isset($values['_eve_sta_date']) ? date("m/d/Y",esc_attr($values['_eve_sta_date'][0])) : ''.date("m/d/Y").'';
    $eve_end_date = isset($values['_eve_end_date']) ? date("m/d/Y",esc_attr($values['_eve_end_date'][0])) : ''.date("m/d/Y").'';
    $eve_sta_time = isset($values['_eve_sta_time']) ? esc_attr($values['_eve_sta_time'][0]) : ''.date("H:i").'';
    $eve_end_time = isset($values['_eve_end_time']) ? esc_attr($values['_eve_end_time'][0]) : ''.date("H:i").'';
    $eve_all_day = isset($values['_eve_all_day']) ? esc_attr($values['_eve_all_day'][0]) : '';
 	
    wp_nonce_field('event_frm_nonce', 'event_frm_nonce');
 	if ($eve_all_day == 'on') {$allday="checked='checked'";} else {$allday="";};
    $html = "
    	<p class='custom-event datepair' data-language='javascript'>
    	 <input id='EventStartDate' class='date start' type='text' name='EventStartDate' value='$eve_sta_date' /> 
    	 <input id='EventStartTime' class='time start' type='text' name='EventStartTime' value='$eve_sta_time' /> to
    	 <input id='EventEndDate' class='date end'  type='text' name='EventEndDate' value='$eve_end_date' />          
         <input id='EventEndTime' class='time end' type='text' name='EventEndTime' value='$eve_end_time' /> 
        </p>
        <p>
        <input id='AllDay' type='checkbox' name='AllDay' $allday /> All day event	
        </p>
        <script>
        jQuery(document).ready(function () {
			var ischecked=jQuery('#AllDay').is(':checked'); 
			if(ischecked){jQuery('#EventStartTime, #EventEndTime').hide();} else{jQuery('#EventStartTime, #EventEndTime').show();}
			
			jQuery('#AllDay').change (function(){
				var ischecked=jQuery('#AllDay').is(':checked'); 
				if(ischecked){jQuery('#EventStartTime, #EventEndTime').hide();} else{jQuery('#EventStartTime, #EventEndTime').show();}
			});
			
			jQuery('#EventStartDate').change (function(){
				jQuery('#EventEndDate').val(jQuery('#EventStartDate').val());
			});
			
			jQuery('#EventStartTime').timepicker({ 'scrollDefaultNow': true });
			jQuery('#EventEndTime').timepicker({ 'scrollDefaultNow': true });
			jQuery( '#EventStartDate, #EventEndDate' ).datepicker();
			
		});
        </script>
        ";
    echo $html;
}

add_action('save_post', 'save_event_information');
 
function save_event_information($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
 
    // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['event_frm_nonce']) || !wp_verify_nonce($_POST['event_frm_nonce'], 'event_frm_nonce'))
        return;
 
    // if our current user can't edit this post, bail
    if (!current_user_can('edit_post'))
        return;
 
    if (isset($_POST['EventStartDate']))
        update_post_meta($post_id, '_eve_sta_date', strtotime(esc_attr($_POST['EventStartDate'])));
    if (isset($_POST['EventStartTime']))
        update_post_meta($post_id, '_eve_sta_time', esc_attr($_POST['EventStartTime']));
    if (isset($_POST['EventEndDate']))
        update_post_meta($post_id, '_eve_end_date', strtotime(esc_attr($_POST['EventEndDate'])));    
    if (isset($_POST['EventEndTime']))
        update_post_meta($post_id, '_eve_end_time', esc_attr($_POST['EventEndTime']));   
    $chk = isset($_POST['AllDay']) ? 'on' : 'off';
        update_post_meta($post_id, '_eve_all_day', $chk);        
}
?>