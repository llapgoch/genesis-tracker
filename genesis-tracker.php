<?php
/*
Plugin Name: Genesis Tracker
Plugin URI: http://carbolowdrates.com
Description: Tracks user's weight, calories, and exercise
Version: 0.1
Author: Dave Preece
Author URI: http://www.scumonline.co.uk
License: GPL

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : dangerous@scumonline.co.uk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('includes.php');


register_activation_hook( __FILE__, array('GenesisTracker', 'install'));

add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::userPageId), 'genesis_user_graph');

add_action('wp', array('GenesisTracker', 'decideAuthRedirect'));
add_action('wp', array('GenesisTracker', 'addHeaderElements'));



/* TODO: Change this so that it uses an optionified key */
add_action('wp_ajax_moose', 'test');




function test() {
	var_dump('moooo');
}

function genesis_user_graph(){
	ob_start();

	include('user-graph.php');
	
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}