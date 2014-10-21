<?php
class GenesisAdmin{
    public static function getUserLogDetails($sortBy = 'measure_date', $user = null){
        global $wpdb;
        
        if(!$sortBy){
            $sortBy = 'measure_date';
        }

        if($user){
            $where = " WHERE u.ID = $user";
        }
        
        $results = $wpdb->get_results($wpdb->prepare($sql = 
            "SELECT *, IFNULL(weight - initial_weight, 0) weight_change FROM 
                (SELECT u.user_email, u.ID user_id,  
            	MAX(measure_date) as measure_date, 
                UNIX_TIMESTAMP(MAX(measure_date)) unix_timestamp,
            	initial_weight.`meta_value` as initial_weight,
            	IFNULL(account_active.`meta_value`, 1) as account_active,
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
                $where
                GROUP BY ID
                
            ) as mainQuery
            ORDER BY $sortBy", 
            GenesisTracker::getOptionKey(GenesisTracker::userStartWeightKey),
            GenesisTracker::getOptionKey(GenesisTracker::userActiveKey)
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
                    
                    foreach($foodLogs as $log){
                        if($log->food_type == $foodKey && $log->time == $timeKey){
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