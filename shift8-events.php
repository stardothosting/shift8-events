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
	wp_enqueue_style( 'wickedpicker_css', plugin_dir_url( __FILE__ ) . '/css/wickedpicker.min.css' );
	wp_enqueue_script( 'wickedpicker_js', plugin_dir_url( __FILE__ ) . '/js/wickedpicker.min.js' );
	// admin always last
	wp_enqueue_style( 'shift8_event_css', plugin_dir_url( __FILE__ ) . '/css/shift8_event_admin.css' );
	wp_enqueue_media();
	wp_enqueue_script( 'shift8_event_script', plugin_dir_url( __FILE__ ) . '/js/shift8_event_admin.js' );
}
add_action( 'admin_enqueue_scripts', 'load_shift8_events_wp_admin_style' );

// Custom meta box function for events
function display_shift8_event_meta_box( $shift8_events ) {
	// Wysiwyg settings
	$wysiwyg_settings  = array( 
				'media_buttons' => false
				 );
	// Retrieve current name of the Director and Movie Rating based on review ID
	$event_date = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_date', true ) );
	$event_time = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_time', true ) );
	$event_image = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_image', true ) );
	$event_image_id = shift8_get_image_id($event_image);
	$event_image_thumb = wp_get_attachment_image_src($event_image_id, 'thumbnail');
	$event_shortdesc = get_post_meta( $shift8_events->ID, 'shift8_event_shortdesc', true);
	$event_longdesc = get_post_meta( $shift8_events->ID, 'shift8_event_longdesc', true);
	$event_price = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_price', true) );
	$event_featured = esc_html( get_post_meta( $shift8_events->ID, 'shift8_event_featured', true) );
	?>
	<table class="shift8-event-admin-table">
		<tr>
		<td style="width: 20%">Event Date</td>
		<td><input type="text" size="40" name="shift8_event_date" id="datepicker" value="<?php echo $event_date; ?>" /></td>
		</tr>
		<tr>
		<td style="width: 20%">Event Time</td>
		<td><input type="text" size="40" name="shift8_event_time" id="timepicker" value="<?php echo $event_time; ?>" /></td>
		</tr>
		<tr>
		<td style="width: 20%">Event Image</td>
		<td><img src="<?php echo $event_image_thumb[0];?>"><input type="hidden" name="shift8_event_image" id="shift8_event_image" value="<?php if ( isset ( $event_image ) ) echo $event_image; ?>" />
		<input type="button" id="shift8_event_image_button" class="button" style="vertical-align:bottom;" value="<?php _e( 'Choose or Upload an Event Image', 'prfx-textdomain' )?>" /></TD>
		</tr>
		<tr>
		<td style="width: 20%">Short Description</td>
		<td><?php wp_editor( $event_shortdesc, 'shift8_event_shortdesc', $wysiwyg_settings ); ?></td>
		</tr>
		<tr>
		<td style="width: 20%">Long Description</td>
		<td><?php wp_editor( $event_longdesc, 'shift8_event_longdesc', $wysiwyg_settings ); ?></td>
		</tr>
		<tr>
                <td style="width: 20%">Event Price</td>
                <td><input type="text" size="10" name="shift8_event_price" value="<?php echo $event_price; ?>" /></td>
                </tr>
                <tr>
                <td style="width: 20%">Event Featured</td>
                <td><input type="checkbox" name="shift8_event_featured" value="true" <?php echo($event_featured == 'true' ? 'checked' : null)?> /></td>
                </tr>
	</table>
	<?php
}

// retrieves the attachment ID from the file URL
function shift8_get_image_id($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
	return $attachment[0]; 
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
                if ( isset( $_POST['shift8_event_image'] ) && $_POST['shift8_event_image'] != '' ) {
                        update_post_meta( $shift8_event_id, 'shift8_event_image', $_POST['shift8_event_image'] );
                }
                if ( isset( $_POST['shift8_event_shortdesc'] ) && $_POST['shift8_event_shortdesc'] != '' ) {
                        update_post_meta( $shift8_event_id, 'shift8_event_shortdesc', $_POST['shift8_event_shortdesc'] );
                }
                if ( isset( $_POST['shift8_event_longdesc'] ) && $_POST['shift8_event_longdesc'] != '' ) {
                        update_post_meta( $shift8_event_id, 'shift8_event_longdesc', $_POST['shift8_event_longdesc'] );
                }
                if ( isset( $_POST['shift8_event_price'] ) && $_POST['shift8_event_price'] != '' ) {
                        update_post_meta( $shift8_event_id, 'shift8_event_price', $_POST['shift8_event_price'] );
                }
                if ( isset( $_POST['shift8_event_featured'] ) && $_POST['shift8_event_featured'] != '' ) {
                        update_post_meta( $shift8_event_id, 'shift8_event_featured', $_POST['shift8_event_featured'] );
                } else { 
			update_post_meta( $shift8_event_id, 'shift8_event_featured', $_POST['shift8_event_featured'] );
		}
	}
}

// Main Shortcode
function shift8_get_events($atts) {
	extract(shortcode_atts(array(
		'number' => '5'
	), $atts));

	return $out;
}

add_shortcode('shift8_event', 'shift8_get_events');


// Shortcode for featured events
function shift8_featured_events_shortcode($atts){
	extract(shortcode_atts(array(
		'numposts' => '1',
	), $atts));

	$args = array(
		'numberposts' => $numposts,
		'posts_per_page' => 1,
		'post_type' => 'shift8_events',
		'post_status' => 'publish',
		'orderby' => 'meta_value',
		'meta_query' => array(
					'relation'    => 'AND',
					array(
						'key'     => 'shift8_event_date',
						'value'	  => date("Ymd"),
						'compare' => '>=',
					),
					array(
						'key'     => 'shift8_event_featured',
						'value'   => 'true',
						'compare' => '=',
					),
				),
		'order' => 'ASC',
	);

	global $post;
	$out = '';
	$posts = new WP_Query($args);
	if ($posts->have_posts()) {
		while ($posts->have_posts()) {
			$posts->the_post();
			// get event image
			$event_image = get_post_meta($post->ID, 'shift8_event_image')[0];
			$event_image_id = shift8_get_image_id($event_image);
			$event_image_display = null;
			if (!empty($event_image)) {
				$event_image_display = '<div class="tgh-eventhome-image-cropped" style="background-image: url(\'' . $event_image . '\');"></div>';
			}
			// convert event time
			$event_date = date('D M d Y', strtotime(get_post_meta($post->ID, 'shift8_event_date')));
			$event_date_trigger = strtolower(date('M-Y', strtotime(get_post_meta($post->ID, 'shift8_event_date'))));
			$event_time = date('h:i a', strtotime(get_post_meta($post->ID, 'shift8_event_time')));
			// price
			$event_price = get_post_meta($post->ID, "shift8_event_price")[0];

			$out .= '<div class="tgh-feat-eventhome-container featured-event-'.get_the_ID().' row">
				<div class="tgh-feathome-left col-lg-6">'.$event_image_display.'</div>
				<div class="tgh-feathome-right col-lg-6"><div class="tgh-feat-eventhome-info"><ul><span class="tgh-feat-calendar-eventhome"><li>'.$event_date.'</li><li>'.$event_time.'</li><li>'.get_the_title().'</li><li>'.($event_price > 0 ? "$" . $event_price : "$FREE") . '</li><li><div class="tgh-eventhome-button"><a href="'.get_permalink().'"><button>Info</button></a></div></li></ul></div></div>
				</div>';
		}
	} else {
		return;
	}
	wp_reset_query();
	return html_entity_decode($out);

}

add_shortcode('shift8_event_featured', 'shift8_featured_events_shortcode');

// Shortcode for main events page
function shift8_events_shortcode($atts){


}

add_shortcode('shift8_event', 'shift8_events_shortcode');
