<?php
/**
 * Plugin Name: Shift8 Events
 * Plugin URI: https://github.com/stardothosting/shift8-events
 * Description: This plugin creates a simple events calendar system on your wordpress site
 * Version: 1.0.0
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */

// Create custom post type
add_action( 'init', 'create_events' );
function create_events() {
	register_post_type( 'shift8_events',
		array(
			'labels' => array(
				'name' => 'Shift8 Events',
				'singular_name' => 'Shift8 Event',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Shift8 Event',
				'edit' => 'Edit',
				'edit_item' => 'Edit Shift8 Event',
				'new_item' => 'New Shift8 Event',
				'view' => 'View',
				'view_item' => 'View Shift8 Event',
				'search_items' => 'Search Shift8 Events',
				'not_found' => 'No Shift8 Events found',
				'not_found_in_trash' => 'No Shift8 Events found in Trash',
				'parent' => 'Parent Shift8 Event'
				),
			'public' => true,
			'menu_position' => 15,
			'supports' => array( 'title'),
			'taxonomies' => array( '' ),
			'menu_icon' => 'dashicons-calendar-alt',
			'has_archive' => true
		)
	);
}

// Register meta box
add_action( 'admin_init', 'shift8_events_admin' );
function shift8_events_admin() {
	add_meta_box( 'shift8_event_meta_box',
		'Shift8 Event Details',
		'display_shift8_event_meta_box',
		'shift8_events', 'normal', 'high'
	);
}

// Register admin scripts for custom fields
function load_shift8_events_wp_admin_style() {
	wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'shift8_event_script', plugin_dir_url( __FILE__ ) . '/js/admin.js' );
}
add_action( 'admin_enqueue_scripts', 'load_shift8_events_wp_admin_style' );


// Custom meta box function for events
function display_shift8_event_meta_box( $shift8_events ) {
	// Retrieve current name of the Director and Movie Rating based on review ID
	$event_date = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_date', true ) );
	$event_time = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_time', true ) );
	?>
	<table>
		<tr>
		<td style="width: 20%">Event Date</td>
		<td><input type="text" size="40" name="shift8_event_date" id="datepicker" value="<?php echo $event_date; ?>" /></td>
		</tr>
		<tr>
		<td style="width: 20%">Event Time</td>
		<td><input type="text" size="40" name="shift8_event_time" value="<?php echo $event_time; ?>" /></td>
		</td>
		</tr>
	</table>
	<?php
}

// Save post meta data
add_action( 'save_post', 'add_shift8_event_fields', 10, 2 );
function add_shift8_event_fields( $shift8_event_id, $shift8_events ) {
	// Check post type for movie reviews
	if ( $shift8_events->post_type == 'shift8_events' ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['shift8_event_date'] ) && $_POST['shift8_event_date'] != '' ) {
			update_post_meta( $shift8_event_id, 'shift8_event_date', $_POST['shift8_event_date'] );
		}
		if ( isset( $_POST['shift8_event_time'] ) && $_POST['shift8_event_time'] != '' ) {
			update_post_meta( $shift8_event_id, 'shift8_event_time', $_POST['shift8_event_time'] );
		}
	}
}


function shift8_get_events($atts) {
	extract(shortcode_atts(array(
		'number' => '5'
	), $atts));

	return $out;
}

add_shortcode('shift8_event', 'shift8_get_events');
