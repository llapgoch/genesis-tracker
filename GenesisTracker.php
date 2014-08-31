<?php
class GenesisTracker{
	const UNIT_IMPERIAL = 1;
	const UNIT_METRIC = 2;
    // Unfortunately, we can't get the comments plugin version from anywhere but the admin area - so we have to store
    // it twice.  Go Wordpress!
	const version = "0.5";
	const prefixId = "genesis___tracker___";
	const userPageId = "user_page";
	const inputProgressPageId = "progress_page";
	const initialWeightPageId = "initial_weight_page";
	const weightEnterSessionKey = "___WEIGHT_ENTER___";
	const targetPageId = "tracker_page";
	const userStartWeightKey = "start_weight";
    const userStartDateKey = "start_date";
    const targetPrependKey = "target_";
    const averageDataKey = "average_data";
    const versionKey = "version";
    const userInitialUnitSelectionKey = "initial_unit_selection";
    
	const omitUserReminderEmailKey = "omit_reminder_email";
	const defaultFieldError = '<div class="form-input-error-container error-[FIELDFOR]">
								<span class="form-input-error">[ERROR]</span></div>';
	const editCapability = "edit_genesis";
	
	// 7 Stone
	const MIN_VALID_WEIGHT = 44.4;
	// 25 Stone
	const MAX_VALID_WEIGHT = 158.8;

	
	public static $pageData = array();
	public static $dietDaysToDisplay = 7;
    
    protected static $_initialUserUnit;

    
    protected static $_userMetaTargetFields = array(
        "fat" => array("name" => 'Fat', "unit" => "portion"),
        "protein" => array("name" => "Protein", "unit" => "portion"),
        "carbs" => array("name" => "Carbohydrate", "unit" => "portion"),
        "fruit" => array("name" => "Fruit", "unit" => "portion"),
        "vegetables" => array("name" => "Vegetables", "unit" => "portion"),
        "dairy" => array("name" => "Dairy", "unit" => "portion"),
        "alcohol" => array("name" => "Alcohol", "unit" => "unit"),
        "treat" => array("name" => "Treat", "unit" => "portion")
    );
    
    protected static $_userTargetTimes = array(
        "breakfast" => array("name" => "Breakfast"),
        "lunch" => array("name" => "Lunch"),
        "evening" => array("name" => "Evening"),
        "snacks" => array("name" => "Snacks"),
        "drinks" => array("name" => "Drinks")
    );
	
    public function populate(){
        global $wpdb;
        
        // for($i = 37541; $i < 72001; $i++){
 //            foreach(self::$_userMetaTargetFields as $targetKey => $target){
 //                foreach(self::$_userTargetTimes as $timeKey => $time){
 //                    $data = array(
 //                       'tracker_id' => $i,
 //                       'food_type' => $targetKey,
 //                       'time' => $timeKey,
 //                       'value' => rand(0, 200)
 //                    );
 //
 //
 //                    $wpdb->insert(self::getFoodLogTableName(), $data);
 //                }
 //            }
 //        }
        
        // for($i = 0; $i < 36000; $i++){
        //     $data = array(
        //         "user_id" => rand(1, 200),
        //         "measure_date" => date("Y-m-d", rand(time() - (86400 * 30 * 6), time())),
        //         "weight" => rand(75, 146),
        //         "exercise_minutes" => rand(0, 1) ? rand(0, 500) : "NULL",
        //         "weight_unit" => rand(1, 2)
        //     );
        //
        //     $wpdb->insert(self::getTrackerTableName(), $data);
        // }
        
        // create users
        // for($i = 102; $i < 202; $i++){
        //     wp_create_user ( "test" . $i . "@example.com", "test" . $i,  "test" . $i . "@example.com" );
        // }
    }
    
	public static function install(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		// Because the makers of this are dicks, you have to have two spaces after "PRIMARY KEY"
		dbDelta($sql = "CREATE TABLE " . self::getTrackerTableName() . " (
		  tracker_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) DEFAULT NULL,
		  measure_date datetime DEFAULT NULL,
		  weight decimal(10,6) unsigned DEFAULT NULL,
		  exercise_minutes int(11) DEFAULT NULL,
          weight_unit tinyint(1) unsigned DEFAULT 1,
		  PRIMARY KEY  (tracker_id),
		  KEY user_id (user_id)
		)");
        
        $wpdb->query($sql =  "ALTER TABLE " . self::getTrackerTableName() . " 
            DROP COLUMN calories" );
        
        $wpdb->query("ALTER TABLE " . self::getTrackerTableName() ." DROP COLUMN fat, DROP COLUMN carbs, DROP COLUMN protein, DROP COLUMN fruit, DROP COLUMN dairy, DROP COLUMN vegetables, DROP COLUMN alcohol, DROP COLUMN treat");
        
		// Create the target table
		dbDelta($sql = "CREATE TABLE " . self::getTargetTableName() . " (
		  target_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) unsigned NOT NULL,
		  target decimal(10,6) unsigned DEFAULT NULL,
		  target_date datetime DEFAULT NULL,
		  PRIMARY KEY  (target_id)
		)");
		
		
		dbDelta($sql = "CREATE TABLE " . self::getDietDayTableName() . " (
		  diet_day_id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  user_id int(11) DEFAULT NULL,
		  day date DEFAULT NULL,
		  PRIMARY KEY  (diet_day_id)
		)");
        
        dbDelta($sql = "CREATE TABLE " . self::getFoodLogTableName() . " (
          `food_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `tracker_id` int(11) unsigned NOT NULL,
          `food_type` varchar(255) DEFAULT NULL,
          `time` varchar(255) DEFAULT NULL,
          `value` int(11) DEFAULT NULL,
          PRIMARY KEY  (`food_log_id`),
          KEY `tracker_id` (`tracker_id`)
        )");
		
		self::updateOption("version", self::version);
		 
		 // Create the user page if it's not already there		 
 		 self::createUserPage();
		 self::createInputPage();
		 self::createTargetInputPage();
		 self::createInitialWeightPage();
		 
		 $role = get_role('administrator');
		 
		 if($role){
			 $role->add_cap(self::editCapability);
		 }
	 }
     
     public static function checkVersionUpgrade(){
         $installedVersion = self::getOption(self::versionKey);

         if($installedVersion !== self::version){
             self::install();             
         }
     }
     
     public static function getuserMetaTargetFields(){
         return self::$_userMetaTargetFields;
     }
     
     public static function getUserTargetTimes(){
         return self::$_userTargetTimes;
     }
     
     public static function getUserTargetLabel($key, $user_id = null){
         $user_id = !is_null($user_id) ? $user_id : get_current_user_id();

         if(!isset(self::$_userMetaTargetFields[$key])){
             return '';
         }         

         if('' == $val = get_the_author_meta(self::getOptionKey(self::targetPrependKey . $key, $user_id))){
             return '';
         }

         $fieldData = self::$_userMetaTargetFields[$key];
         
         return "<p class='target-label'>" . sprintf("<strong>Your Personal %s Target:</strong> %s", $fieldData['name'], $val)  . "</p>";
     }
	 
	 public static function getTrackerTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_tracker";
	 }
	 
	 public static function getDietDayTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_diet_day";
	 }
     
	 public static function getFoodLogTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_food_log";
	 }
	 
	 public static function getTargetTableName(){
		 global $wpdb;
		 return $wpdb->base_prefix . "genesis_user_target";
	 }
	 
	 public static function stoneToKg($stone, $pounds = 0){
		 return (($stone * 14) + $pounds) * 0.453592;
	 }
	 
	 public static function kgToPounds($kg){
		 return  (float) $kg / 0.453592;
	 }
	 
	 public static function kgToStone($kg){		 
		 $pounds = self::kgtoPounds($kg);
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
	 
	 public function checkLoginWeightEntered($userLogin, $user){
		
	 	if(!GenesisTracker::getInitialUserWeight($user->ID)){
	 		$_SESSION[GenesisTracker::weightEnterSessionKey] = true;
	 	}
	 }

	 public function checkWeightEntered(){
		 global $post;
		 
		 if(!is_user_logged_in()){
			 unset($_SESSION[GenesisTracker::weightEnterSessionKey]);
			 return;
		 }
		 
		 $pageID = self::getOption(self::initialWeightPageId);
		 $weightPost = get_post($pageID);
		 
		 if(!$weightPost || $weightPost->post_status !== 'publish'){
			 return;
		 }
		 
		 if(!isset($_SESSION[self::weightEnterSessionKey]) && self::getPageData('weight-save') !== true){
			 // If we're on the enter initial weight page, redirect the user
			  if($post && $pageID == $post->ID){
				  wp_redirect(GenesisTracker::getUserPagePermalink());
			  }
			 
			 return;
		 }
		
		 
		 if($post && $pageID == $post->ID){
			 return;
		 }
		
		 wp_redirect(get_permalink($pageID));
	 }
	 
	 public static function getUserWeightChange($user_id){
		 $cWeight = (float) self::getUserLastEnteredWeight($user_id);
		 $sWeight = (float) self::getInitialUserWeight($user_id);
		 
		 if(!$cWeight){
			 return 0;
		 }
		 
		 return $cWeight - $sWeight;
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
	
	 public static function enterWeightPageAction(){
		 global $wpdb;
		 
   	     $form = DP_HelperForm::createForm('initial-weight');
		 $form->fieldError = self::defaultFieldError;

		 if(!DP_HelperForm::wasPosted()){
			 return;
	 	 }
		 
		 $form->setData($_POST);
		 $action = $form->getRawValue('action');
		 
		 // Actions for the user input page
		 
		 switch($action){
			 case "saveinitialweight" :
				 self::saveInitialWeight($form, get_current_user_id());
			break;
		 }
	 }
	 
	 public static function saveInitialWeight($form, $user_id){
         global $wpdb;
         
		 $rules = array(
			 'weight_main' => array('N', 'R', "VALUE-GREATER[0]"),
		 );
		 
         $unit     = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL ? self::UNIT_IMPERIAL : self::UNIT_METRIC;
		 $imperial = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL;
		 
		 // If we're doing imperial, validate pounds too.
		 if($imperial){
			 $rules['weight_pounds'] = array("N");
		 }
		 
		 $form->validate($rules);
		 
		 if(!$form->hasErrors()){
			 $weight = (float)$form->getRawValue('weight_main');
			 
			 if($imperial){
				 $weight = self::stoneToKg($weight, (float)$form->getRawValue('weight_pounds'));
			 }
			 
			 if(!self::isValidWeight($weight)){
 				$form->setError('weight_main', array(
 					'general' => 'Please enter a valid weight',
 					'main' => 'Please enter a valid weight'
 				));
				return;
			 }
             
             // Store the initial weight date for yesterday, so the user can make an entry if they like on log in.
             $date = date('Y-m-d', time() - 86400);
			 
		 	 add_user_meta($user_id, self::getOptionKey(self::userStartWeightKey), $weight, true);
             add_user_meta($user_id, self::getOptionKey(self::userStartDateKey), $date, true);
             add_user_meta($user_id, self::getOptionKey(self::userInitialUnitSelectionKey), $unit, true);
			 
             $data = array(
                 'user_id' => $user_id,
                 'measure_date' => $date,
                 'weight' => $weight
             );
                
             
			 self::$pageData['weight-save'] = true;
 	 		 unset($_SESSION[GenesisTracker::weightEnterSessionKey]);
		 }
	 }
	 
	 public static function isValidWeight($weight){
		 $weight = (float)$weight;
		 return $weight >= self::MIN_VALID_WEIGHT && $weight <= self::MAX_VALID_WEIGHT;
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
		 global $current_user;
		 
		 $formName = null;

		 if(self::isOnUserInputPage()){
			 $formName = 'user-input';
			 self::userInputPageAction();
		 }
		 
		 
		 if(self::isOnTargetPage()){
			 $formName = 'tracker';
			 self::targetPageAction();
		 }
		 
		 if(self::isOnEnterWeightPage()){
			 $formName = 'initial-weight';
			 self::enterWeightPageAction();
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
			 'weight_main' => array('N', 'R', "VALUE-GREATER[0]"),
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
						 'general' => 'Your target weight must be lower than the last weight you recorded',
						 'main' => 'Please make sure your target weight is lower than the last weight you recorded'
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
			 'measure_date' => array("R", "DATE")
		 );
		 
		 if($form->getRawValue('record-weight')){
			 $rules['weight_main'] = array('N', 'R');
		 }
		 
		 if($form->getRawValue('record-calories')){
			 $rules['calories'] = array('N', 'R', 'VALUE-GREATER-EQ[0]');
		 }
		 
		 if($form->getRawValue('record-exercise')){
			 $rules['exercise_minutes'] = array('N', 'R', 'VALUE-GREATER-EQ[0]');
		 }
		 
         if($form->getRawValue('record-food')){
             foreach(self::$_userMetaTargetFields as $targetKey => $target){
                 foreach(self::$_userTargetTimes as $timeKey => $time){
                     $rules[$timeKey . "_" . $targetKey] = array('N', 'R', 'VALUE-GREATER-EQ[0]');
                 }
             }
         }
         
		 $imperial = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL;
         $weightUnit = $imperial ? self::UNIT_IMPERIAL : self::UNIT_METRIC;
		 
		 // If we're doing imperial, validate pounds too.
		 if($imperial){
			 $rules['weight_pounds'] = array("N");
		 }
		 
		 $form->validate($rules);
		 
		 if($form->hasErrors()){
             return false;
         }
		 // Prepare the data
		 $date = self::convertFormDate($form->getRawValue('measure_date'));
		 $logDate = strtotime($date);
		 $dateParsed = date_parse($date);
		 
		 // Validate the date is in the past or today
		 if($logDate >= mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))){
			 $form->setError('measure_date', array(
				 'general' => 'You can only add measurements for today\'s date or past days',
				 'main' => 'Your measurement date needs to be in the past or for today'
			 ));
			 return;
		 }
         
         if($logDate <= strtotime(self::getInitialUserStartDate(get_current_user_id()))){
             $form->setError('measure_date', array(
                 'general' => 'Your measurement date must be after your start day',
                 'main' => 'Your measurement date must be after your start day'
             ));
             return;
         }
		 
		 // Check at least one entry type has been checked
		 if(!$form->hasValue('record-weight') &! $form->hasValue('record-food') &! $form->hasValue('record-exercise') &! $form->hasValue('diet-days')){
			 self::$pageData['errors'][] = 'Please select at least one measurement type to take';
			 return;
		 }
		 
         if($form->hasValue('record-weight')){ 
			 $weight = (float)$form->getRawValue('weight_main');
		 	 
			 if($imperial){
				 $weight = self::stoneToKg($weight, (float)$form->getRawValue('weight_pounds'));
			 }
		 
			 if(!self::isValidWeight($weight)){
 				$form->setError('weight_main', array(
 					'general' => 'Please enter a valid weight',
 					'main' => 'Please enter a valid weight'
 				));
				return;
			 }
         }
		 
		 if($form->getRawValue('action') !== 'duplicate-overwrite'){
			 if(self::getUserDataForDate(get_current_user_id(), $date)){
			 	 self::$pageData['user-input-duplicate'] = true;
				 return;
			 }
	 	}
		 
		 $data = array(
			 'measure_date' => $date,
			 'user_id' => get_current_user_id(),
             'weight_unit' => $weightUnit
		 );
		 
		 if($form->hasValue('record-weight')){
			 $data['weight'] = $weight;
		 }
		 
		 if($form->hasValue('record-exercise')){
			 $data['exercise_minutes'] = (float)$form->getRawValue('exercise_minutes');
		 }
		 
         // Remove Food Logs
		// Get the ID of any previously saved data against this date
         if($prevResult = self::getUserDataForDate(get_current_user_id(), $date)){
             // Remove dates
 			 $wpdb->query(
 			 	$sql = $wpdb->prepare('DELETE FROM ' . self::getFoodLogTableName() . ' 
 					WHERE tracker_id = %d', $prevResult->tracker_id
 				)
 		 	 );
         }  
		 
         // Remove current entry
		 $wpdb->query(
		 	$wpdb->prepare('DELETE FROM ' . self::getTrackerTableName() . ' WHERE user_id=%d AND measure_date=%s', get_current_user_id(), $date)
		 );
		
		 if(!($wpdb->insert(self::getTrackerTableName(), $data))){
			 self::$pageData['errors'] = array(
				 'An error occurred in saving your measurement'
			 );
			 return;
		 }
	     
		 $trackerId = $wpdb->insert_id;
		 $removeDates = array();
		 
		 // Remove the diet days
		 for($i = 0; $i < self::$dietDaysToDisplay; $i++){
			 // Create as timestamp to overflow
			 $time = mktime(0,0,0, $dateParsed['month'] , $dateParsed['day'] - $i, $dateParsed['year']);
			 
			 $removeDates[] = "'" . date('Y-m-d', $time) . "'";
		 }
		 
		 if($removeDates){
			 $wpdb->query(
			 	$sql = $wpdb->prepare($sql = 'DELETE FROM ' . self::getDietDayTableName() . ' 
					WHERE day IN (' . implode(',', $removeDates) . ')
					AND user_id=%d', get_current_user_id()
				)
		 	 );
             
		}
		
		// Add diet days
		
		if($dietDays = $form->getRawValue('diet_days')){
			foreach($dietDays as $dietDay){
				if(!mktime($dietDay)){
					continue;
				}
				
				$wpdb->insert(self::getDietDayTableName(),
					array(
						'user_id' => get_current_user_id(),
						'day' => $dietDay
					)
				);
			}
		}
        
        // Save food logs
        if($form->hasValue('record-food')){
            // Save the new values
            foreach(self::$_userMetaTargetFields as $targetKey => $target){
                foreach(self::$_userTargetTimes as $timeKey => $time){
                    $fieldKey = $timeKey . "_" . $targetKey;
                    
                    if(!$form->hasValue($fieldKey) || (float) $form->getRawValue($fieldKey) < 0){
                        continue;
                    }
                    
                    
                    if(!$wpdb->insert(self::getFoodLogTableName(),
                        array(
                            'tracker_id' => $trackerId,
                            'food_type' => $targetKey,
                            'time' => $timeKey,
                            'value' => (float) $form->getRawValue($fieldKey) 
                        )
                    )){
                         self::$pageData['errors'] = array(
                        	 'An error occurred in saving your measurement'
                         );
                         return;
                    }
                }
            }
        }      
		 
         self::$pageData['user-input-save'] = true;
	 }
     
     public static function getUserFoodLogsForTracker($user_id, $tracker_id){
         global $wpdb;
         
         $res = $wpdb->get_results($sql = $wpdb->prepare("SELECT fl.* 
             FROM " . self::getFoodLogTableName() . " fl
             JOIN " . self::getTrackerTableName() . " t 
                 ON fl.tracker_id = t.tracker_id
                 AND t.user_id = %d
                 AND t.tracker_id = %d", $user_id, $tracker_id)
        );
        
        return $res;
     }
	 
	 public static function getUserDataForDate($user_id, $date){
		 global $wpdb;
		 
		 $res = $wpdb->get_row($sql = $wpdb->prepare(
		 	"SELECT * FROM " . self::getTrackerTableName() . "
		 	 WHERE user_id=%d
			 	AND measure_date=%s",
			$user_id,
			$date));
        
        return $res;
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
	 
	 public static function isOnEnterWeightPage(){
		global $post;

		if(!$post){
			return false;
		}
		
		if(self::getOption(self::initialWeightPageId) == $post->ID){
			return true;
		}
		
		return false;
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
	 
	 public static function getInitialUserWeight($user_id){
		 return get_user_meta($user_id, self::getOptionKey(self::userStartWeightKey), true);
	 }
     
	 public static function getInitialUserStartDate($user_id){
		 return get_user_meta($user_id, self::getOptionKey(self::userStartDateKey), true);
	 }
     
	 public static function getInitialUserUnit($user_id){
         if(!self::$_initialUserUnit){
             self::$_initialUserUnit = get_user_meta($user_id, self::getOptionKey(self::userInitialUnitSelectionKey), true);
         }
		 
         return self::$_initialUserUnit;
	 }
	 
	 public static function getUserDateRange($user_id){
		 global $wpdb;
         
         
		 
		 $res = $wpdb->get_row(
			 $sql = $wpdb->prepare($sql = "SELECT *  
                FROM (
                	SELECT MIN(measure_date) weight_min, MAX(measure_date) weight_max 
                	FROM " . self::getTrackerTableName() . "
                	WHERE weight IS NOT NULL
                	AND user_id = %d
                ) weight_dates,
                (
                	SELECT MIN(measure_date) exercise_minutes_min, MAX(measure_date) exercise_minutes_max 
                	FROM " . self::getTrackerTableName() . "
                	WHERE exercise_minutes IS NOT NULL
                	AND user_id = %d
                ) measure_dates,
                (
                	SELECT MIN(measure_date) mindate, MAX(measure_date) maxdate 
                	FROM " . self::getTrackerTableName() . "
                	WHERE user_id = %d
                ) total_dates
                ", $user_id, $user_id, $user_id)
	 	);
        
		if(!$res){
            return new stdClass();
		}
		
        $initialUserStartDate = self::getInitialUserStartDate($user_id);
        
        // This should be lower than any value in the measurements table
        $res->weight_min = $initialUserStartDate;
        
        $res->weight_loss_min = $res->weight_min;
        $res->weight_loss_max = $res->weight_max;

        return $res;
	}
	 
	 public static function getAllUserLogs($user_id, $startDate ='', $endDate = ''){
		 global $wpdb;
		 
		 $weightQ = '';
		 $dateConstraint = '';
		 
		 if($startWeight = self::getInitialUserWeight($user_id)){
			 $weightQ = ", ($startWeight - weight) as weight_loss ";
		 }
		 
		 if($startDate){
			 $dateConstraint = "AND measure_date >= '$startDate'";
		 }
		 
		 if($endDate){
			 $dateConstraint .= " AND measure_Date <= '$endDate'";
		 }
         
         $aggregates = array();
         
         // Build the aggregates for each value we want to pull out
         foreach(self::$_userMetaTargetFields as $targetKey => $targetVal){
             $aggregates[] = sprintf("SUM(CASE WHEN fl.`food_type` = '%s' THEN fl.`value` ELSE NULL END) as %s", $targetKey, $targetKey);
         }
         
		 $results = $wpdb->get_results($sql = $wpdb->prepare(
		 	$select = "SELECT t.* $weightQ " .
            ($aggregates ? "," . implode(",\n", $aggregates) . " " : "") . 
            "FROM " . self::getTrackerTableName() . " t " . 
            "LEFT JOIN " . self::getFoodLogTableName() . " fl USING(tracker_id)
		 	WHERE user_id=%d $dateConstraint 
            GROUP BY t.tracker_id
            ORDER BY measure_date", $user_id
		 ));

         
         $start = new stdClass();
         $start->user_id = $user_id;
         $start->measure_date = self::getInitialUserStartDate($user_id);
         $start->weight = self::getInitialUserWeight($user_id);
         $start->weight_loss = 0;
         
         array_unshift($results, $start);
         
         // sort the array by date entered
         usort($results, function($a, $b){
             return strtotime($a->measure_date) - strtotime($b->measure_date);
         });
		 
		 return $results;
	 }
	 
	 // Pass in an array of keys to average in $avgVals
	 public static function getUserGraphData($user_id, $fillAverages = false, $avgVals = array(), $keyAsDate = false, $startDate = '', $endDate = ''){
         

         $userData = self::getAllUserLogs($user_id, $startDate, $endDate);		 
		 $weightInitial = array();
		 // Get the user's start weight in imperial and metric
		 $weightInitial['initial_weight'] = self::getInitialUserWeight($user_id);
		 $weightInitial['initial_weight_imperial'] = self::kgToPounds($weightInitial['initial_weight']);
		 
		 $valsToCollate = array(
			 'weight',
			 'exercise_minutes',
			 'weight_loss',
             'fat',
             'carbs',
             'protein',
             'dairy',
             'fruit',
             'vegetables',
             'treat',
             'alcohol'
		 );
		 
		 $collated['weight_imperial'] = array();
		 $collated['weight_loss_imperial'] = array();
		 $collated['weight_imperial']['data'] = array();
		 $collated['weight_loss_imperial']['data'] = array();
		 
		 $collated['weight_imperial']['timestamps'] = array();
		 $collated['weight_loss_imperial']['timestamps'] = array();		 
		 
		 if($userData){
			 foreach($userData as $log){             
				 $timestamp = strtotime($log->measure_date . " UTC ") * 1000;
			 
				 foreach($valsToCollate as $valToCollate){
					 if(!isset($collated[$valToCollate])){
						 $collated[$valToCollate] = array();
						 $collated[$valToCollate]['data'] = array();
						 $collated[$valToCollate]['timestamps'] = array();
					 }
				 
				 
					 $isWeight = $valToCollate == 'weight';
					 $isWeightLoss = $valToCollate == 'weight_loss';
				 
					 // Only collate weight if it's been entered this time
					 if($log->$valToCollate === null){
						 continue;
					 }
				 
				 
					 if(!isset($collated[$valToCollate]['yMin']) || $collated[$valToCollate]['yMin'] > $log->$valToCollate){
						 $collated[$valToCollate]['yMin'] = $log->$valToCollate;
					 }
		 
					 if(!isset($collated[$valToCollate]['yMax']) || $collated[$valToCollate]['yMax'] < $log->$valToCollate){
					 	$collated[$valToCollate]['yMax'] = $log->$valToCollate;
					 }
		 		
					 $collated[$valToCollate]['timestamps'][] = $timestamp;
				
					 $collated[$valToCollate]['data'][] = array(
						 $timestamp, $log->$valToCollate
					 );
			 	
				 
					  if($isWeight){  
						  $collated['weight_imperial']['data'][] = array(
							 $timestamp, self::kgToPounds($log->$valToCollate)
						 );
					 
						 $collated['weight_imperial']['timestamps'][] = $timestamp;
					 }
				 
					 if($isWeightLoss){
						 $collated['weight_loss_imperial']['data'][] = array(
							 $timestamp, self::kgToPounds($log->$valToCollate)
						 );
					 
						 $collated['weight_loss_imperial']['timestamps'][] = $timestamp;
					 }	 
				 }
			 }
		 }
		 
		
		 
		 if(isset($collated['weight'])){
			 // Update the min and max vals using the initial entered user weight
			 $collated['weight']['yMin'] = min($collated['weight']['yMin'], $weightInitial['initial_weight']);
			 $collated['weight']['yMax'] = max($collated['weight']['yMax'], $weightInitial['initial_weight']);
		 }
		 
		 
		 $collated['weight_imperial']['yMin'] = self::kgToPounds($collated['weight']['yMin']);
		 $collated['weight_imperial']['yMax'] = self::kgToPounds($collated['weight']['yMax']);
		 
		 $collated['weight_loss_imperial']['yMin'] = self::kgToPounds($collated['weight_loss']['yMin']);
		 $collated['weight_loss_imperial']['yMax'] = self::kgToPounds($collated['weight_loss']['yMax']);
		 
		 
		 if($fillAverages){
			 $newCollated = array();
			 
			 foreach($avgVals as $avgVal){
				 $newCollated = array();


				if(!isset($collated[$avgVal]) || !isset($collated[$avgVal]['data'])){
					continue;
				}

				 foreach($collated[$avgVal]['data'] as $data){
					 // First loop
					 if(!$newCollated){
						 $newCollated[] = $data;
						 continue;
				 	}
					
					// Subsequent loops, calculate the differences
					$last =  end($newCollated);
					// Day Length *= 1000 
					$dayLength = 86400000;
					// One day less to fill the gaps in
					$daysBetween = max(1, floor(($data[0] - $last[0]) / $dayLength));
					
					if($daysBetween > 1){
						$valDivisor = ($data[1] - $last[1]) / ($daysBetween);
					
						// Calculate the averages
						for($i = 1; $i < $daysBetween; $i++){
							$date = $last[0] + ($i * $dayLength);

							$newCollated[] = array(
								$date,  $last[1] + ($valDivisor * $i)
							);
						}
					}
					
					// Push the actual value in
					$newCollated[] = $data;
					
				 }
				 
				 $collated[$avgVal]['data'] = $newCollated;
		 	}
		 }
		 
		 if($keyAsDate){
			 foreach($collated as $key => &$collate){
				 $newData = array();
				 
				 if(!isset($collate['data'])){
					 continue;
				 }
				 
				 foreach($collate['data'] as $data){
					 $newData[$data[0]] = $data[1];
				 }
				 
				 $collate['data'] = $newData;
			 }
		 }
		 
		 if(!isset($collated['weight']['data'])){
			 $collated['weight']['data'] = array();
			 $collated['weight']['yMin'] = $weightInitial['initial_weight'];
			 $collated['weight']['yMax'] = $weightInitial['initial_weight'];
			 
			 $collated['weight_imperial']['yMin'] = $weightInitial['initial_weight_imperial'];
			 $collated['weight_imperial']['yMax'] = $weightInitial['initial_weight_imperial'];
			
		 }
		 
		 $collated['initial_weights'] = $weightInitial;
		 return $collated;
	 }
	 
	 /*
	 	This needs testing on large datasets
	 	First, we get all user data and fill in the averages for each day inbetween, this could be expensive on its own.
	 	Then we key that data by date, then merge it into an array of all values using the date as key.  
	 	Then we average each value for the date.
	 */
     public static function getAverageUsersGraphData($rangeDates){ 
        if(!$averages = wp_cache_get( self::getOptionKey(self::averageDataKey) )){
            $averages = self::generateAverageUsersGraphData();
        };
        
        if(!$averages){
            return;
        }
        

        
        
        // Trim the data so we only have between the dates we need
        // This used to be done in the method which now caches all user data
        foreach($averages as $averageKey => &$averageData){
            unset($averageData['yMin']);
            unset($averageData['yMax']);
            
            $startTime = null;
            $endTime = null;
            
            $minKey = $averageKey . "_min";
            $maxKey = $averageKey . "_max";
            
            if($averageKey == 'weight_loss_imperial'){
                $minKey = 'weight_loss_min';
                $maxKey = 'weight_loss_max';
            }
            
            if($rangeDates->$minKey){
                $startTime = strtotime($rangeDates->$minKey) * 1000;
            }elseif($rangeDates->minDate){
                $startTime = strtotime($rangeDates->minDate) * 1000;
            }
            
            if($rangeDates->$maxKey){
                $endTime = strtotime($rangeDates->$maxKey) * 1000;
            }elseif($rangeDates->maxDate){
                $endTime = strtotime($rangeDates->maxDate) * 1000;
            }
            
            
            foreach($averageData['data'] as $dataKey => &$dataPoint){
                
                if($startTime !== null && $dataPoint[0] < $startTime){
                    unset($averageData['data'][$dataKey]);
                    continue;
                }
                
                if($endTime !== null && $dataPoint[0] > $endTime){
                     unset($averageData['data'][$dataKey]);
                     continue;
                }
                

                if(!isset($averageData['yMin']) || $dataPoint[1] < $averageData['yMin']){
                    $averageData['yMin'] = $dataPoint[1];
                }
                
                if(!isset($averageData['yMax']) || $dataPoint[1] > $averageData['yMax']){
                    $averageData['yMax'] = $dataPoint[1];
                }
            }
            
            // Because the array has had items removed from it, the index is no longer sequential
            // So it becomes an assoc array (and object when json_encoded). Make it indexed here.
            $averageData['data'] = array_values($averageData['data']);
        }
        
        return $averages;
     }
     
     
     // Generate the cached version of the average dataset
	 public static function generateAverageUsersGraphData($onlySubscribers = true){
         $limit = $onlySubscribers ? 'role=subscriber' : '';
		 $users = get_users($limit);
        
		 $results = array();
		 $structure = array();
		 
		 // No need to average weight or weight-imperial
		 $averageValues = array(
			'weight_loss', 
	        // 'exercise_minutes',
        //     'calories',
			'weight_loss_imperial'
		 );

		 
		 // Get all of the values in an array with the timestamp as key so se can easily loop over them
		 foreach($users as $user){
			 $graphData = self::getUserGraphData($user->ID, true, $averageValues, true);
			  
			 if(!$graphData){
				 continue;
			 }
			 
			 foreach($graphData as $key => &$measurementSet){ 
				 if(!isset($measurementSet['data']) || !in_array($key, $averageValues)){
					 continue;
				 }
				 if(!isset($structure[$key])){
					 $structure[$key] = array();
				 }				 
				 
				 foreach($measurementSet['data'] as $date => $measurement){
					 if(!isset($structure[$key][$date])){
						 $structure[$key][$date] = array();
					 }
					 $structure[$key][$date][] = $measurement;
				 }
				 
			 }
		 }
		 
		 // Now average them!
		 $averages = array();
		 
		 foreach($structure as $key => $dates){
			  
			 foreach($dates as $date => $measurements){
				 
				 $avg = array_sum($measurements) / count($measurements);
				 
				 // if(isset($averages[$key]['yMin'])){
 //                      $averages[$key]['yMin'] = min($averages[$key]['yMin'], $avg);
 //                      $averages[$key]['yMax'] = max($averages[$key]['yMax'], $avg);
 //                  }else{
 //                      $averages[$key]['yMin'] = $avg;
 //                      $averages[$key]['yMax'] = $avg;
 //                  }
				 
				 $averages[$key]['data'][] = array($date, $avg);
			 }

			 // Sort by date
			 if(isset($averages[$key]['data'])){
 				 usort($averages[$key]['data'], function($a, $b){
 					
 					
 					 if($a[0] == $b[0]){
 						 return 0;
 					 }
 				 
 					 if($a[0] < $b[0]){
 						 return -1;
 					 }
 				 
 					 return 1;
 				 });
 			 }
		 }
         
         wp_cache_set(self::getOptionKey(self::averageDataKey), $averages, null, 86400);
         
         mail("dave_preece@mac.com", "Regenerated Cache", "Regenerated");
         
		 return $averages;
	 }
     

     
     public static function getUserFormValues($day, $month, $year){
         // Add rest of form details here
         $user_id = get_current_user_id();
         $date = $year . "-" . $month . "-" . $day;

         if(!$measureDetails = self::getUserDataForDate($user_id, $date)){
             $measureDetails = new stdClass();
         }
         
         if($measureDetails->weight !== null){
             $measureDetails->weight_imperial = self::kgToStone($measureDetails->weight);
             $measureDetails->weight = (float) $measureDetails->weight;
             
             $measureDetails->weight_imperial['stone'] = round($measureDetails->weight_imperial['stone'], 2);
             $measureDetails->weight_imperial['pounds'] = round($measureDetails->weight_imperial['pounds'], 2); 
         }
         
         // Look up food targets using the tracker_id if we have one
         $foodData = array();
         
         if($measureDetails->tracker_id){
             $foodData = self::getUserFoodLogsForTracker($user_id, $measureDetails->tracker_id);
         }
         
         return array(
             "date_picker" =>self::getDateListPicker($day, $month, $year),
             "measure_details" => $measureDetails,
             "food_log" => $foodData 
        );
     }
	 
	 public static function getDateListPicker($day, $month, $year, $forUser = true, $selected = array()){
		 global $wpdb;
		 // return html for the last five days
		 $list = "";
		 
		 for($i = self::$dietDaysToDisplay - 1; $i >= 0; $i--){
			 $time = mktime(0, 0, 0, $month, $day - $i, $year);
			 $dateKey = date("Y-m-d", $time);
			 $cl = $i == 0 ? 'last' : '';
			 
			 if($forUser){
				 $res = $wpdb->query(
					 $sql = $wpdb->prepare('SELECT * FROM ' . self::getDietDayTableName() . ' 
					 	WHERE user_id=%d AND day=%s ', get_current_user_id(), $dateKey
					)   
				 );
			 
				 if($res){
					 $selected[] = $dateKey;
				 }
		 	}
			 
			 $inputHTML = DP_HelperForm::createInput('diet_days[]', 'checkbox', array(
				 'id' => $dateKey,
				 'value' => $dateKey
			 ), $selected);
			 
			 $list .= "<li class='$cl'>" . $inputHTML . "
				 <label for='$dateKey'><span class='line-1'>" . date('l', $time) . "</span><span class='line-2'>" . date("jS F", $time). "</span></label></li>";				 
		 }
		 
		 return "<ul>" . $list . "</ul>";
	 }
	 
	 // Get rid of the < and > which break on Android browsers
	 public function forgottenPassword($message, $key){
		 return str_replace(array("<", ">"), "", $message);
	 }
	 
	 
	 public static function addBodyClasses($classes){
		 // Add classes not to show header alerts on specific pages
		 if(self::getPageData('user-input-duplicate') 
		 || self::getPageData('user-input-save')
		 || self::isOnEnterWeightPage()
         || apply_filters('hide-header-notice', false)){
			 $classes[] = 'hide-header-notice';
		 }
		 
		 return $classes;
	 }
	 
	 public static function addHeaderElements(){
         $user_id = get_current_user_id();
         
		 if(self::isOnUserPage()){
			  wp_enqueue_script('flot', plugins_url('js/jquery.flot.min.js', __FILE__), array('jquery'));
			  wp_enqueue_script('flot-time', plugins_url('js/jquery.flot.time.min.js', __FILE__), array('flot'));
			  wp_enqueue_script('flot-navigate', plugins_url('js/jquery.flot.navigate.min.js', __FILE__), array('flot'));
			  wp_enqueue_script('user-graph', plugins_url('js/UserGraph.js', __FILE__), array('flot-navigate'));
			  
			  
			  $dateRange = self::getUserDateRange(get_current_user_id());
			  
			  wp_localize_script('flot', 'userGraphData', self::getUserGraphData(get_current_user_id()));
			  wp_localize_script('flot', 'averageUserGraphData', self::getAverageUsersGraphData($dateRange));
		 }
         

		 	 
		 if(self::isOnUserPage() || self::isOnUserInputPage() || self::isOnTargetPage() || self::isOnEnterWeightPage()){	
		    wp_register_script( "progress", plugins_url('js/script.js', __FILE__), array( 
	 			'jquery'  
			));
		    
			wp_localize_script( 'progress', 'myAjax', array( 
				'ajaxurl' => admin_url('admin-ajax.php')
			));
            
            // Don't set the initial user unit in the case of a posted form - allow the form to use what was posted
            if(!DP_HelperForm::wasPosted()){
                wp_localize_script('progress', 'initialUserUnit', self::getInitialUserUnit($user_id));      
            }
		    wp_enqueue_script('progress');
		}
        
        if(self::isOnUserInputPage()){
            $minDate = strtotime(self::getInitialUserStartDate($user_id)) + 86400;

            wp_localize_script('progress', 'datePickerMin', array(
                "day" => date("j", $minDate),
                "month" => date("n", $minDate),
                "year" => date("Y", $minDate)
            ));
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
	 public static function createUserPage($overwrite = false){
		 $pageID = self::getOption(self::userPageId);
		 $post = get_post($pageID);
			 
		 if($post && $post->post_status == 'publish'  &! $overwrite){
			 return;
		 }
		 
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
		

		 if($pageID){
			 wp_delete_post( $pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::userPageId, $post_id);
	 }
	 
	 public static function createInputPage($overwrite = false){
		 // Creates the page which displays the graph information
 		 $pageID = self::getOption(self::inputProgressPageId);
		 $post = get_post($pageID);
		 
 		 if($post && $post->post_status == 'publish'  &! $overwrite){
			 return;
		 }
		 
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
	 
	 public static function createTargetInputPage($overwrite = false){
		 // Create the page which allows users to enter a target weight and date
		 $pageID = self::getOption(self::targetPageId);
		 $post = get_post($pageID);
		 
		 if($post && $post->post_status == 'publish'  &! $overwrite){
			 return;
		 }
		 
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Set a weight target',
 			'comment_status' => 'closed',
 		 	'post_content' => '[' . self::getOptionKey(self::targetPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );
		 

		 if($pageID){
			 wp_delete_post($pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::targetPageId, $post_id);
	 } 
	 
	 public static function createInitialWeightPage($overwrite = false){
		 // Create the page which allows users to enter a target weight and date
		 $pageID = self::getOption(self::initialWeightPageId);
	 	 $post = get_post($pageID);
		
		 if($post && $post->post_status == 'publish'  &! $overwrite){
			 return;
		 }
		 
		 $current_user = wp_get_current_user();

		 $pageData = array(
			'post_title' => 'Enter Your Initial Weight',
 			'comment_status' => 'closed',
 		 	'post_content' => '[' . self::getOptionKey(self::initialWeightPageId) . ']',
 		 	'post_status' => 'publish',
 		 	'post_type' => 'page',
 		 	'post_author' => $current_user->ID
 		 );

		 if($pageID){
			 wp_delete_post($pageID, true);
		 }
		 
		  $post_id = wp_insert_post($pageData);
		  self::updateOption(self::initialWeightPageId, $post_id);
	 }
	 
	 public static function sendReminderEmail(){
		 // Sends a reminder email to all users
		 $templatePath = plugin_dir_path( __FILE__ ) . "template" . DIRECTORY_SEPARATOR . "reminder.html";
		 $body = file_get_contents($templatePath);
		 
 		$headers = array();
 		$headers[] = 'From: Genesis Clinical Trial<'. get_option('admin_email') .'>';
 		$headers[] = 'MIME-Version: 1.0';
 		$headers[] = 'Content-type: text/html; charset=utf-8';
		 
		 // get all subscribers
		  $users = get_users( array("user_login" => 'admin') );

		  foreach($users as $user){
			 $optOut = (bool)get_user_meta( $user->ID, 'genesis___tracker___omit_reminder_email', true);

			  // Don't send reminders to users who have opted out of emails
			   if( $optOut ){
				   continue;
			   }
			 
			  mail($user->user_email, 'A reminder from Genesis', $body, implode("\r\n", $headers));
		  }
		
	 }
}
