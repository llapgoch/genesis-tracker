<?php
class GenesisTracker{
	const UNIT_IMPERIAL = 1;
	const UNIT_METRIC = 2;
	const version = "0.1";
	const prefixId = "genesis___tracker___";
	const userPageId = "user_page";
	const inputProgressPageId = "progress_page";
	const targetPageId = "tracker_page";
	const defaultFieldError = '<div class="form-input-error-container error-[FIELDFOR]">
								<span class="form-input-error">[ERROR]</span></div>';
	public static $pageData = array();
	
	public static function install(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		
		// Because the makers of this are dicks, you have to have two spaces after "PRIMARY KEY"
		dbDelta($sql = "CREATE TABLE " . self::getTrackerTableName() . " (
		  tracker_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) DEFAULT NULL,
		  date_tracked datetime DEFAULT NULL,
		  weight decimal(10,6) unsigned DEFAULT NULL,
		  calories int(11) unsigned DEFAULT NULL,
		  exercise_minutes int(11) DEFAULT NULL,
		  PRIMARY KEY  (tracker_id),
		  KEY user_id (user_id)
		)");
		
		// Create the target table
		dbDelta($sql = "CREATE TABLE " . self::getTargetTableName() . " (
		  target_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) unsigned NOT NULL,
		  target decimal(10,6) unsigned DEFAULT NULL,
		  target_date datetime DEFAULT NULL,
		  PRIMARY KEY  (target_id)
		)");
		
		self::updateOption("version", self::version);
		 
		 // Create the user page if it's not already there		 
 		 self::createUserPage();
		 self::createInputPage();
		 self::createTargetInputPage();
	 }
	 
	 public static function getTrackerTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_tracker";
	 }
	 
	 public static function getTargetTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_user_target";
	 }
	 
	 public static function stoneToKg($stone, $pounds = 0){
		 return (($stone * 14) + $pounds) * 0.453592;
	 }
	 
	 public static function kgToStone($kg){
		 $pounds = (float) $kg / 0.453592;
		 
		 $stone = floor($pounds / 14);
		 $pounds = $pounds - ($stone * 14);
		 
		 return array(
			 'stone' => $stone,
			 'pounds' => $pounds
		 );
	 }
	 
	 public static function convertFormDate($formDate){
		 preg_match("/(\d+)-(\d+)-(\d+)/", $formDate, $matches);
		 return $matches[3] . "-" . $matches[2] . "-" . $matches[1];
	 }
	 
	 public static function convertDBDate($dbDate){
		 return date("d-m-Y", strtotime($dbDate));
	 }
	 
	 public static function userInputPageAction(){
		 $form = DP_HelperForm::createForm('user-input');
		 $form->fieldError = self::defaultFieldError;

		 if(!DP_HelperForm::wasPosted()){
			 return;
	 	 }
		 
		 $form->setData($_POST);
		 $action = $form->getRawValue('action');
		 
		 // Actions for the user input page
		 
		 switch($action){
			 case "duplicate-overwrite" :
			 case "savemeasurement" :
				 self::saveMeasurement($form);
			break;
		 }
	 }
	 
	 public static function getUserLastEnteredWeight($user_id){
		 global $wpdb;
		 $result = $wpdb->get_row($wpdb->prepare($sql = "SELECT * FROM  ". self::getTrackerTableName() . "
		 WHERE user_id=%d" . " ORDER BY measure_date DESC", $user_id));
		 
		 if(!$result){
			 return null;
		 }
		 
		 return $result->weight;
	 }
	 
	 public static function targetPageAction(){
		global $wpdb;
		
 	    $form = DP_HelperForm::createForm('tracker');
		$form->fieldError = self::defaultFieldError;
		
		// Get the previously saved target
		$result = $wpdb->get_row($wpdb->prepare($sql = 'SELECT * FROM ' . self::getTargetTableName() . '
				WHERE user_id=%s', get_current_user_id())); 
		
		if($result){
			$savedData = array(
				'target_date' => self::convertDBDate($result->target_date),
				'weight_main' => (float)$result->target,
				'weight_unit' => $result->unit
			);
		
			if((int)$result->unit == self::UNIT_IMPERIAL){
				$imperialWeight = self::kgToStone($result->target);
				$savedData['weight_main'] = $imperialWeight['stone'];
				$savedData['weight_pounds'] = $imperialWeight['pounds'];
			}
			
			$form->setData($savedData);
		}
		
		 if(!DP_HelperForm::wasPosted()){
		 	return;
		 }
		 
		 $form->setData($_POST);
		 $action = $form->getRawValue('action');
		 
		 switch($action){
			 case "savetarget" :
			 	self::saveTarget($form);
				break;
		 }
	 }
	 
	 // For saving, updating etc
	 public static function doActions(){
		 global $wpdb;
		 
		 $formName = null;
		 
		 if(self::isOnUserInputPage()){
			 $formName = 'user-input';
			 self::userInputPageAction();
		 }
		 
		 
		 if(self::isOnTargetPage()){
			 $formName = 'tracker';
			 self::targetPageAction();
		 }
		 
		 if($formName &&  $form = DP_HelperForm::getForm($formName)){
			 if($form->hasErrors()){
				 self::$pageData['errors'][] = 'Please fix the errors on the form and try again.';
		 	}
		 }
	 }
	
	 
	 public static function saveTarget(DP_HelperForm $form){
		 global $wpdb;
		 // TO DO;
		 $rules = array(
			 'weight_main' => array('N', 'R'),
			 'target_date' => array("R", "DATE")
		 );
		 
		 $imperial = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL;
		 
		 // If we're doing imperial, validate pounds too.
		 if($imperial){
			 $rules['weight_pounds'] = array("N");
		 }
		 
		 $form->validate($rules);
		 
		 if(!$form->hasErrors()){
		 	 $date = self::convertFormDate($form->getRawValue('target_date'));
			
			 // Extra validation
			 // Validate the date is greater than now
			 if(strtotime($date) <= time()){
				$form->setError('target_date', array(
					'general' => 'Your target date must be in the future',
					'main' => 'Target Date must be in the future'
				));
				return;
			 }
			 
			 $weight = (float)$form->getRawValue('weight_main');
			 
 			 if($imperial){
 				 $weight = self::stoneToKg($weight, (float)$form->getRawValue('weight_pounds'));
 			 }
			
			 if(($lastWeight = (float)self::getUserLastEnteredWeight(get_current_user_id())) > 0){
				 // Check the weight entered is lower than the last weight the user entered on their tracker

				 if($lastWeight <= $weight){
					 $form->setError('weight_main', array(
						 'general' => 'Your target weight must be lower than the last weight you tracked',
						 'main' => 'Please make sure your target weight is lower than the last weight you tracked'
					 ));
					 return;
				 }
			 }
			
			
			  
 			 
			 
			 $data = array(
				 'target' => (float)$weight,
				 'target_date' => $date,
				 'unit' => $imperial ? self::UNIT_IMPERIAL : self::UNIT_METRIC,
				 'user_id' => get_current_user_id()
			 );
			 
			 $wpdb->query($wpdb->prepare(
				 "DELETE FROM " . self::getTargetTableName() . " WHERE user_id=%d ", get_current_user_id()
			 ));
			 
			 // Remove any current targets
			 $wpdb->query($wpdb->prepare("DELETE FROM " . self::getTargetTableName() . " WHERE user_id=%d", get_current_user_id()));
			
			 if(!($wpdb->insert(self::getTargetTableName(), $data))){
				 self::$pageData['errors'] = array(
					 'An error occurred in saving your target'
				 );
			 }else{
				 self::$pageData['target-save'] = true;
			 }
		 }
	 }
	 
	 public static function saveMeasurement(DP_HelperForm $form){
		 global $wpdb;
		 
		 $rules = array(
			 'weight_main' => array('N', 'R'),
			 'calories' => array('N', 'R', 'VALUE-GREATER-EQ[0]'),
		 	 'exercise_minutes' => array('N', 'R'),
			 'measure_date' => array("R", "DATE")
		 );
		 
		 $imperial = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL;
		 
		 // If we're doing imperial, validate pounds too.
		 if($imperial){
			 $rules['weight_pounds'] = array("N");
		 }
		 
		 $form->validate($rules);
		 
		 if(!$form->hasErrors()){
			 // Prepare the data
			 $date = self::convertFormDate($form->getRawValue('measure_date'));
			 
			 
			 // Validate the date is in the past or today
			 if(strtotime($date) >= mktime(0, 0, 0, date("m"), date("d")+1, date("Y"))){
				 $form->setError('measure_date', array(
					 'general' => 'You can only add measurements for today\'s date or past days',
					 'main' => 'Your measurement date needs to be in the past or for today'
				 ));
				 return;
			 }
			 $weight = (float)$form->getRawValue('weight_main');
			 
			 if($form->getRawValue('action') !== 'duplicate-overwrite'){
				 if(self::getUserDataForDate(get_current_user_id(), $date)){
				 	 self::$pageData['user-input-duplicate'] = true;
					 return;
				 }
		 	}
			 
			 if($imperial){
				 $weight = self::stoneToKg($weight, (float)$form->getRawValue('weight_pounds'));
			 }
			 
			 $data = array(
				 'weight' => $weight,
				 'calories' => (float)$form->getRawValue('calories'),
				 'exercise_minutes' => (float)$form->getRawValue('exercise_minutes'),
				 'measure_date' => $date,
				 'user_id' => get_current_user_id()
			 );
			
			 if(!($wpdb->insert(self::getTrackerTableName(), $data))){
				 $this->pageData['errors'] = array(
					 'An error occurred in saving your measurement'
				 );
			 }else{
				 self::$pageData['user-input-save'] = true;
			 }
		 }
		 
	 }
	 
	 public static function getUserDataForDate($user_id, $date){
		 global $wpdb;
		 
		 return $wpdb->get_row($sql = $wpdb->prepare(
		 	"SELECT * FROM " . self::getTrackerTableName() . "
		 	 WHERE user_id=%d
			 	AND measure_date=%s",
			$user_id,
			$date));
	 }
	 
	 public static function getOptionKey($option){
		 return self::prefixId . $option;
	 }
	 
	 public static function getOption($option, $default = null){
		 return get_option(self::prefixId . $option, $default);
	 }
	 
	 public static function updateOption($option, $value){
		 update_option(self::prefixId . $option, $value);
	 }
	 
	 public static function getUserPagePermalink(){
		 return get_permalink(self::getOption(self::userPageId));
	 }
	 public static function getUserInputPagePermalink(){
	 	return get_permalink(self::getOption(self::inputProgressPageId));
	 }
	 
	 public static function isOnUserPage(){
		global $post;

		if(!$post){
			return false;
		}
		
		if(self::getOption(self::userPageId) == $post->ID){
			return true;
		}
		
		return false;
	 }
	 
	 public static function isOnTargetPage(){
  		global $post;

  		if(!$post){
  			return false;
  		}
		
  		if(self::getOption(self::targetPageId) == $post->ID){
  			return true;
  		}
		
  		return false;
	 }
	 
	 public static function isOnUserInputPage(){
 		global $post;

 		if(!$post){
 			return false;
 		}
		
 		if(self::getOption(self::inputProgressPageId) == $post->ID){
 			return true;
 		}
		
 		return false;
	 }
	 
	 public static function getPageData($key){
		 if(isset(self::$pageData[$key])){
			 return self::$pageData[$key];
		 }
		 
		 return null;
	 }
	 
	 public static function addHeaderElements(){		 
		 if(self::isOnUserPage() || self::isOnUserInputPage() || self::isOnTargetPage()){	
		    wp_register_script( "progress", plugins_url('js/script.js', __FILE__), array( 
	 			'jquery'  
			));
		    
			wp_localize_script( 'progress', 'myAjax', array( 
				'ajaxurl' => admin_url('admin-ajax.php')
			));        

		    wp_enqueue_script('progress');
		}
	 }
	 
	 public static function decideAuthRedirect(){
		 if(is_user_logged_in()){
			 return false;
		 }
		 
		 if(self::isOnUserPage() || self::isOnUserInputPage() || self::isOnTargetPage()){
			auth_redirect();	
		 }
	 }
	 
	 public static function ajaxRequest($data){
		 var_dump($data);
		 exit;
	 }
	 
	 // Part of the set up.  Adds the user page into the database
	 public static function createUserPage(){
		 // Creates the page which displays the graph information
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Progress',
 			'comment_status' => 'closed',
 		 	'post_content' => '[' . self::getOptionKey(self::userPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );
		 
		 $pageID = self::getOption(self::userPageId);

		 if($pageID){
			 wp_delete_post( $pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::userPageId, $post_id);
	 }
	 
	 public static function createInputPage(){
		 // Creates the page which displays the graph information
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Input Your Progress',
 			'comment_status' => 'closed',
 		 	'post_content' => '[' . self::getOptionKey(self::inputProgressPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );
		 
		 $pageID = self::getOption(self::inputProgressPageId);

		 if($pageID){
			 wp_delete_post($pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::inputProgressPageId, $post_id);
	 }
	 
	 public static function createTargetInputPage(){
		 // Create the page which allows users to enter a target weight and date
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Set a weight target',
 			'comment_status' => 'closed',
 		 	'post_content' => '[' . self::getOptionKey(self::targetPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );
		 
		 $pageID = self::getOption(self::targetPageId);

		 if($pageID){
			 wp_delete_post($pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::targetPageId, $post_id);
	 } 
}
