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

function shift8_get_events($atts) {
	extract(shortcode_atts(array(
		'number' => '5'
	), $atts));

	return $out;
}

add_shortcode('shift8_event', 'shift8_get_events');
