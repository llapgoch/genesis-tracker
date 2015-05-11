<?php
class GenesisAdmin{
    public static function getDietDaysForUser($user_id){
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . GenesisTracker::getDietDayTableName() . '
            WHERE user_id=%d
            ORDER BY day DESC
            LIMIT 10', $user_id
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
			IF(weight - LEAST(lowest_weight, initial_weight) >= 1 AND user_registered < date_sub(now(), interval 6 month) , 1, 0) as gained_more_than_one_kg FROM 
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
                user_first_name.meta_value as first_name,
                user_last_name.meta_value as last_name,
                CONCAT(user_first_name.meta_value, ' ' , user_last_name.meta_value) as user_name,
                UNIX_TIMESTAMP(u.user_registered) as user_registered_timestamp,
        		(SELECT weight 
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
                ORDER BY measure_date DESC 
                LIMIT 1) as weight

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
                $where
                GROUP BY ID
                
            ) as mainQuery
            ORDER BY $sortBy", 
            GenesisTracker::getOptionKey(GenesisTracker::userStartWeightKey),
            GenesisTracker::getOptionKey(GenesisTracker::userActiveKey),
            GenesisTracker::getOptionKey(GenesisTracker::eligibilityGroupSessionKey),
            GenesisTracker::getOptionKey(GenesisTracker::userContactedKey),
            GenesisTracker::getOptionKey(GenesisTracker::userWithdrawnKey),
            GenesisTracker::getOptionKey(GenesisTracker::userNotesKey)
        ), ARRAY_A);
       
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