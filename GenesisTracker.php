<?php
class GenesisTracker{
	const version = "0.1";
	const prefixId = "genesis___tracker___";
	const userPageId = "user_page";
	
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
	 }
	 
	 public static function getTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_tracker";
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
	 
	 public static function addHeaderElements(){
		 wp_register_script('jquery-1.10.1', '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js');
		 
		 if(self::isOnUserPage()){	
			    wp_register_script( "progress", plugins_url('js/user-page.js', __FILE__), array( 
		 			'jquery-1.10.1'  
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
		 
		 if(self::isOnUserPage()){
			 if($page = get_page(GenesisTracker::getOption(GenesisTracker::userPageId))){
				auth_redirect();
			 }
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
