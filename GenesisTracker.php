<?php
class GenesisTracker{
	const version = "0.1";
	const prefixId = "genesis___tracker___";
	const userPageId = "user_page";
	
	public static function install(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		
		// Because the makers of this are dicks, you have to have two spaces after "PRIMARY KEY"
		$sql = "CREATE TABLE genesis_tracker (
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
	 
	 public static function createUserPage(){
		 // Creates the page which displays the graph information
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Progress',
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
	 

}
