<?php
class GenesisAdmin{
	const WEIGHT_GAINING = "GAINING";
	const WEIGHT_LOSING  = "LOSING";
	const WEIGHT_MAINTAINING = "MAINTAINING";
	
	public static function getFourWeekEmailTypes(){		
		return array(
			'GAINING' 	=> 'Weight gaining',
			'LOSING' 	=> 'Weight losing',
			'MAINTAINING'	=> 'Weight maintaining',
			'NOTHING'	=> 'No weight recorded'
		);
	}
	
	public static function userIsClassedAsLosing($user_id){
		// This is for the four weekly emails.
		// A user is considered as losing if their two consecutive weights
		// prior to their newest log indicate a downward trend
		global $wpdb;
		
		$results = $wpdb->get_results( $wpdb->prepare($sql = "
			SELECT *
		 FROM
			(SELECT measure_date, weight, 
			um.`meta_value` as six_month_date
			FROM " . GenesisTracker::getTrackerTableName() . " t
			LEFT JOIN " . $wpdb->usermeta . " um
				ON um.`user_id` = t.`user_id`
				AND um.`meta_key` = %s
				WHERE measure_date >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
				AND t.user_id = %d
			ORDER BY measure_date) as weight_data
		WHERE
			measure_date >= six_month_date",
			GenesisTracker::getOptionKey(GenesisTracker::sixMonthDateKey),
			$user_id
		));
		
		// Remove the latest weight
		array_pop($results);
		
		if(count($results) < 2){
			return false;
		}
		
		$last = array_pop($results);
		$penultimate = array_pop($results);
		
		// Not sure what inducates a downward trend
		return (float) $last->weight < (float) $penultimate->weight;
	}
	
	public static function doAdminInitHook(){
		global $pagenow;
		
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
			
		// Use get as the function name to execute
		if(isset($_GET['sub']) && is_admin()){
			if(strpos($_GET['sub'], "genesis_admin_") === 0){
				if(function_exists($_GET['sub'])){
					call_user_func($_GET['sub']);
				}
			}
		}
		
		if($pagenow == 'profile.php'){
            wp_register_script('genesis-admin-profile', plugins_url('js/admin-profile.js', __FILE__), array('jquery-ui-datepicker'));
			
			wp_enqueue_script('genesis-admin-profile');
		}
	}
	
	public static function doAdminNotices(){
		$key = GenesisTracker::getOptionKey(GenesisTracker::adminNoticesSessionKey);
		if(isset($_SESSION[$key])){
			foreach($_SESSION[$key] as $notice){
				echo '<div class=' . $notice["type"] . '><p>' . $notice["message"] . '</p></div>';
			}
			unset($_SESSION[$key]);
		}
	}
	
	public static function addAdminNotice($type = 'updated', $message){
		$key = GenesisTracker::getOptionKey(GenesisTracker::adminNoticesSessionKey);
		if(!isset($_SESSION[$key])){
			$_SESSION[$key] = array();
		}
		
		$_SESSION[$key][] = array("type" => $type, "message" => $message);
	}
	
    public static function getDietDaysForUser($user_id){
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . GenesisTracker::getDietDayTableName() . '
            WHERE user_id=%d
            ORDER BY day DESC', $user_id
        ));

        return $results;
    }
	
	public static function getWeightLogsForUser($user_id){
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . GenesisTracker::getTrackerTableName() . '
         WHERE weight IS NOT NULL
			AND user_id=%d
         ORDER BY measure_date DESC'
            , $user_id));
        
        return $results;
	}
    
    public static function getExerciseLogsForUser($user_id){
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . GenesisTracker::getTrackerTableName() . '
         WHERE (exercise_minutes IS NOT NULL
            OR exercise_minutes_resistance IS NOT NULL)
            AND user_id=%d
         ORDER BY measure_date DESC
         LIMIT 10'
            , $user_id));
        
        return $results;
    }
    
    public static function getUserLogDetails($sortBy = 'measure_date', $user = null){
        global $wpdb;
        
        if(!$sortBy){
            $sortBy = 'measure_date';
        }

        if($user){
            $where = " WHERE u.ID = $user";
        }
        
        $results = $wpdb->get_results($sql = $wpdb->prepare( 
            "SELECT *, IFNULL(weight - initial_weight, 0) weight_change,
			/* IF(weight - LEAST(lowest_weight, initial_weight) >= 1 AND user_registered < date_sub(now(), interval 6 month), 1, 0) as gained_more_than_one_kg, */
			/* This first one is without the red flag email check */
			IF(six_month_date IS NOT NULL 
				AND six_month_weight IS NOT NULL, IF(weight IS NULL, six_month_weight, weight) - LEAST(IFNULL(min_weight_after_six_months, 10000), six_month_weight), 0) as six_month_benchmark_change,
			/* This one is with the red flag email check */
			IF(red_flag_email_date IS NULL AND six_month_date IS NOT NULL
					AND six_month_weight IS NOT NULL, IF(weight IS NULL, six_month_weight, weight) - LEAST(IFNULL(min_weight_after_six_months, 10000), six_month_weight), 0) as six_month_benchmark_change_email_check,
			IF(six_month_weight IS NOT NULL AND six_month_date IS NOT NULL AND six_month_date + INTERVAL 4 WEEK < NOW(), 
				IF(four_weekly_date < DATE_SUB(NOW(), INTERVAL 4 WEEK) OR four_weekly_date IS NULL, 1, 0), 
			NULL) as four_week_required_to_send,
			/* Use least_weight instead of lowest_weight in result sets as it takes into account the initial weight */
			LEAST(lowest_weight, initial_weight) as least_weight,
			IF(four_weekly_weight IS NULL, 'NOTHING', 
				IF(six_month_weight - four_weekly_weight >= 1, 'LOSING',
					IF(six_month_weight - four_weekly_weight <= -1, 'GAINING',
						'MAINTAINING'
					)
				)
			) as four_week_outcome
			
			 FROM 
                (SELECT u.user_registered, u.user_email, u.ID user_id,  
            	MAX(measure_date) as measure_date, 
				MIN(weight) as lowest_weight,
                UNIX_TIMESTAMP(MAX(measure_date)) unix_timestamp,
            	initial_weight.`meta_value` as initial_weight,
                passcode_group.`meta_value` as passcode_group,
            	IFNULL(account_active.`meta_value`, 1) as account_active,
                IFNULL(user_contacted.`meta_value`, 0) as user_contacted,
                IFNULL(withdrawn.`meta_value`, 0) as withdrawn,
                notes.`meta_value` as notes,
				six_month_weight.`meta_value` as six_month_weight,
				red_flag_email_date.`meta_value` as red_flag_email_date,
				four_weekly_date.`meta_value` as four_weekly_date,
				UNIX_TIMESTAMP(four_weekly_date.`meta_value`) as four_weekly_date_timestamp,
                user_first_name.meta_value as first_name,
                user_last_name.meta_value as last_name,
				six_month_date.meta_value as six_month_date,
                CONCAT(user_first_name.meta_value, ' ' , user_last_name.meta_value) as user_name,
                UNIX_TIMESTAMP(u.user_registered) as user_registered_timestamp,
        		(SELECT weight 
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
                ORDER BY measure_date DESC 
                LIMIT 1) as weight,
        		(SELECT min(weight)
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
					AND measure_date >= DATE_ADD(user_registered, INTERVAL 6 MONTH)
				) as min_weight_after_six_months,
				(SELECT weight 
					FROM " . GenesisTracker::getTrackerTableName() . "
					WHERE measure_date >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
						AND measure_date > six_month_date
						AND weight IS NOT NULL
					ORDER BY measure_date DESC
					LIMIT 1
				) as four_weekly_weight
				
            FROM " . $wpdb->users . " u
                LEFT JOIN " . GenesisTracker::getTrackerTableName() . " t
                	ON u.ID = t.user_id
                LEFT JOIN " . $wpdb->usermeta . " as initial_weight 
                	ON initial_weight.user_id = u.ID
                	AND initial_weight.meta_key = %s
                LEFT JOIN " . $wpdb->usermeta . " as account_active 
                	ON account_active.user_id = u.ID
                	AND account_active.meta_key = %s
                LEFT JOIN " . $wpdb->usermeta . " as user_first_name
                    ON user_first_name.user_id = u.ID
                    AND user_first_name.meta_key = 'first_name'
                LEFT JOIN " . $wpdb->usermeta . " as user_last_name
                    ON user_last_name.user_id = u.ID
                    AND user_last_name.meta_key = 'last_name'
                LEFT JOIN " . $wpdb->usermeta . " as passcode_group
                    ON passcode_group.user_id = u.ID
                    AND passcode_group.meta_key = %s
                LEFT JOIN " . $wpdb->usermeta . " as user_contacted 
                    ON user_contacted.user_id = u.ID
                    AND user_contacted.meta_key = %s
                LEFT JOIN " . $wpdb->usermeta . " as withdrawn 
                    ON withdrawn.user_id = u.ID
                    AND withdrawn.meta_key = %s
                LEFT JOIN " . $wpdb->usermeta . " as notes 
                    ON notes.user_id = u.ID
                    AND notes.meta_key = %s
				LEFT JOIN " . $wpdb->usermeta . " as six_month_weight
					ON six_month_weight.user_id = u.ID
					AND six_month_weight.meta_key = %s
				LEFT JOIN " . $wpdb->usermeta . " as red_flag_email_date
					ON red_flag_email_date.user_id = u.ID
					AND red_flag_email_date.meta_key = %s
				LEFT JOIN " . $wpdb->usermeta . " as four_weekly_date
					ON four_weekly_date.user_id = u.ID
					AND four_weekly_date.meta_key = %s
				LEFT JOIN " . $wpdb->usermeta . " as six_month_date
					ON six_month_date.user_id = u.ID
					AND six_month_date.meta_key = %s
                $where
                GROUP BY ID
                
            ) as mainQuery
            ORDER BY $sortBy", 
            GenesisTracker::getOptionKey(GenesisTracker::userStartWeightKey),
            GenesisTracker::getOptionKey(GenesisTracker::userActiveKey),
            GenesisTracker::getOptionKey(GenesisTracker::eligibilityGroupSessionKey),
            GenesisTracker::getOptionKey(GenesisTracker::userContactedKey),
            GenesisTracker::getOptionKey(GenesisTracker::userWithdrawnKey),
            GenesisTracker::getOptionKey(GenesisTracker::userNotesKey),
			GenesisTracker::getOptionKey(GenesisTracker::sixMonthWeightKey),
			GenesisTracker::getOptionKey(GenesisTracker::redFlagEmailDateKey),
			GenesisTracker::getOptionKey(GenesisTracker::fourWeekleyEmailDateKey),
			GenesisTracker::getOptionKey(GenesisTracker::sixMonthDateKey)
        ), ARRAY_A);
		
		foreach($results as &$result){
			// Do the four weekly logic
			
			if($result['four_week_outcome'] !== self::WEIGHT_MAINTAINING){
				continue;
			}
			
			if($isLosingResult = self::userIsClassedAsLosing($result['user_id'])){
				$result['four_week_outcome'] = self::WEIGHT_LOSING;
			}
			
		}

        // Return results for a single user
        if($user && $results){
            return $results[0];
        }
        
        return $results;
    }

    public static function getFoodLogs($user_id, $limit = 10){
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT t.* FROM " . GenesisTracker::getTrackerTableName() . " t
            JOIN " . GenesisTracker::getFoodLogTableName() . " f
            	ON t.`tracker_id` = f.`tracker_id`
            WHERE user_id = %d
            ORDER BY measure_date DESC
            LIMIT %d
            
        ", $user_id, $limit));
        
        // Get all of the logs
        
        
        foreach($results as $result){
            $result->foodLog = array();
            $foodLogs = GenesisTracker::getUserFoodLogsForTracker($result->tracker_id);

            foreach(GenesisTracker::getUserTargetTimes() as $timeKey => $time){
                $result->foodLog[$timeKey] = array();
                $result->foodLog[$timeKey]['total'] = 0;
                foreach(GenesisTracker::getuserMetaTargetFields() as $foodKey => $food){
                    $result->foodLog[$timeKey][$foodKey] = array();
                    
                    if(!isset($result->foodLog[$foodKey . "_total"])){
                        $result->foodLog[$foodKey . "_total"] = 0;
                        $target = get_user_meta( $user_id, GenesisTracker::getOptionKey(GenesisTracker::targetPrependKey . $foodKey ), true);
                        
                            
                        $result->foodLog[$foodKey . "_target"] = $target ? $target : "- -";
                    }
                    
                    
                    foreach($foodLogs as $log){
                        if($log->food_type == $foodKey && $log->time == $timeKey){
                            $result->foodLog[$foodKey . "_total"] += $log->value;
                            $result->foodLog[$timeKey][$foodKey] = $log;
                            $result->foodLog[$timeKey]['total'] += $log->value;
                        }
                    }
                    
                }
            }
            
            $result->foodDescriptions = GenesisTracker::getUserFoodDescriptionsForTracker($result->tracker_id);
        }
        
        return $results;
        
    }
}