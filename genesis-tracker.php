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
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::inputProgressPageId), 'genesis_user_input_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::targetPageId), 'genesis_tracker_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::initialWeightPageId), 'genesis_initial_weight_page');

if(!is_admin()){
	add_action('wp', array('GenesisTracker', 'decideAuthRedirect'));
	add_action('wp', array('GenesisTracker', 'addHeaderElements'));
	add_action('wp', array('GenesisTracker', 'doActions'));
	add_action('wp_login', array('GenesisTracker', 'checkLoginWeightEntered'), 1000, 2);
	add_action('wp', array('GenesisTracker', 'checkWeightEntered'), 1000);
}

/* TODO: Change this so that it uses an optionified key */
add_action('wp_ajax_genesis_getdatepicker', 'genesis_post_date_picker');

// Because the ajax functionality doesn't pass parameters, we get them here
function genesis_post_date_picker(){
	$day = $_POST['day'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	
	die(GenesisTracker::getDateListPicker($day, $month, $year));
}

function genesis_user_graph(){
	ob_start();
	GenesisTracker::getAverageUsersGraphData(false);
	
	include('page/user-graph.php');
	$output = ob_get_contents();
	
	ob_end_clean();
	return $output;
}

// PAGES
function genesis_user_input_page(){
	ob_start();
	$form = DP_HelperForm::getForm('user-input');
	$outputBody = false;
	$userGraphPage = GenesisTracker::getUserPagePermalink();
	$userInputPage = GenesisTracker::getUserInputPagePermalink();
	
	$dateListPicker = '';
	
	if($form->wasPosted()){
		// Get the date list picker html with selected post values
		$date = GenesisTracker::convertFormDate($form->getRawValue('measure_date'));

		$dateParts = date_parse($date);
		$selectedDates = is_array($form->getRawValue('diet_days')) ? $form->getRawValue('diet_days') : array();
		$dateListPicker = GenesisTracker::getDateListPicker($dateParts['day'], $dateParts['month'] - 1, $dateParts['year'], false, $selectedDates);
	}
	
	if(GenesisTracker::getPageData('user-input-save') == true){
		require('page/user-input-success.php');
		$outputBody = true;
	}
	
	if(GenesisTracker::getPageData('user-input-duplicate') == true){
		require('page/user-input-duplicate.php');
		$outputBody = true;
	}
	
	// Default form output
	if(!$outputBody){
		$metricUnits = $form->getRawValue('weight_unit') == GenesisTracker::UNIT_METRIC;
		require('page/user-input.php');
	}
	
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function genesis_tracker_page(){
	ob_start();
	$form = DP_HelperForm::getForm('tracker');
	$outputBody = false;
	$userGraphPage = GenesisTracker::getUserPagePermalink();
	$userInputPage = GenesisTracker::getUserInputPagePermalink();
	
	if(GenesisTracker::getPageData('target-save') == true){
		require('page/target-save-success.php');
		$outputBody = true;
	}
	
	
	// Default form output
	if(!$outputBody){
		$metricUnits = $form->getRawValue('weight_unit') == GenesisTracker::UNIT_METRIC;
		if($currentWeight = GenesisTracker::getUserLastEnteredWeight(get_current_user_id())){
			$imperial = GenesisTracker::kgToStone($currentWeight);
			$weight = array(
				'metric' => round($currentWeight, 2) . ' kilograms',
				'imperial' => round($imperial['stone'], 2) . " stone" . ($imperial['pounds'] ? ", " . round($imperial['pounds'], 2) . " pounds" : "")
			);
		}
		
		
		require('page/tracker-input.php');
	}
	
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function genesis_initial_weight_page(){
	ob_start();
	$form = DP_HelperForm::getForm('initial-weight');
	$outputBody = false;
	
	if(GenesisTracker::getPageData('weight-save') == true){
		require('page/initial-weight-success.php');
		$outputBody = true;
	}
	
	if(!$outputBody){
		require('page/initial-weight.php');
	}
	
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}