<?php
class GenesisTracker{
	const version = "0.1";
	const prefixId = "genesis___tracker___";
	const userPageId = "user_page";
	const inputProgresPageId = "progress_page";
	
	public static function install(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		
		// Because the makers of this are dicks, you have to have two spaces after "PRIMARY KEY"
		$sql = "CREATE TABLE " . self::getTableName() . " (
		  tracker_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) DEFAULT NULL,
		  date_tracked datetime DEFAULT NULL,
		  weight decimal(10,6) unsigned DEFAULT NULL,
		  calories int(11) unsigned DEFAULT NULL,
		  exercise_minutes int(11) DEFAULT NULL,
		  PRIMARY KEY  (tracker_id),
		  KEY user_id (user_id)
		)";
		
		dbDelta($sql);
		self::updateOption("version", self::version);
		 
		 // Create the user page if it's not already there		 
 		 self::createUserPage();
		 self::createInputPage();
	 }
	 
	 public static function getTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_tracker";
	 }
	 
	 // For saving, updating etc
	 public static function doActions(){
		
		 if(self::isOnUserInputPage()){
			 $form = DP_HelperForm::createForm('user-input');
			 
			 if(!DP_HelperForm::wasPosted()){
				 return;
		 	 }
			 
			 $form->setData($_POST);
			 $action = $form->getRawValue('action');
			 
			 // Actions for the user input page
			 switch($action){
				 case "savemeasurement" :
					 self::saveMeasurement($_POST);
				break;
			 }
		 }
		 
	 }
	 
	 public static function saveMeasurement($data){
		 $rules = array(
			 'measure_date' => array('R'),
			 'weight' => array('N', 'R'),
			 'calories' => array('N', 'R'),
		 	 'exercise_minutes' => array('N', 'R')
		 );
			 
		 var_dump(LlapgochUtils::validateData($data, $rules));
		 
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
	 
	 public static function isOnUserPage(){
		global $post;

		if(!$post){
			return false;
		}
		
		if(GenesisTracker::getOption(GenesisTracker::userPageId) == $post->ID){
			return true;
		}
		
		return false;
	 }
	 
	 public function isOnUserInputPage(){
 		global $post;

 		if(!$post){
 			return false;
 		}
		
 		if(GenesisTracker::getOption(GenesisTracker::inputProgresPageId) == $post->ID){
 			return true;
 		}
		
 		return false;
	 }
	 
	 public static function addHeaderElements(){		 
		 if(self::isOnUserPage() || self::isOnUserInputPage()){	
			    wp_register_script( "progress", plugins_url('js/script.js', __FILE__), array( 
		 			'jquery'  
				));
			    
				wp_localize_script( 'progress', 'myAjax', array( 
					'ajaxurl' => admin_url('admin-ajax.php')
				));        

			    wp_enqueue_script( 'progress' );
		}
	 }
	 
	 public static function decideAuthRedirect(){
		 if(is_user_logged_in()){
			 return false;
		 }
		 
		 if(self::isOnUserPage() || self::isOnUserInputPage()){
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
 		 	'post_content' => '[' . self::getOptionKey(self::inputProgresPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );
		 
		 $pageID = self::getOption(self::inputProgresPageId);

		 if($pageID){
			 wp_delete_post($pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::inputProgresPageId, $post_id);
	 }
	 

}
