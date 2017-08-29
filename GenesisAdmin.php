<?php
class GenesisAdmin{
    const WEIGHT_GAINING = "GAINING";
    const WEIGHT_LOSING  = "LOSING";
    const WEIGHT_MAINTAINING = "MAINTAINING";
    
    public static function getFourWeekEmailTypes(){        
        return array(
            'GAINING'     => 'Weight gaining',
            'LOSING'     => 'Weight losing',
            'MAINTAINING'    => 'Weight maintaining',
            'NOTHING'    => 'No weight recorded'
        );
    }
    
    public static function doAdminInitHook(){
        global $pagenow;
        
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            
        // Use get as the function name to execute -- this is before the page rendering
        if(isset($_GET['sub']) && is_admin()){
            if(strpos($_GET['sub'], "genesis_admin_") === 0){
                if(function_exists($_GET['sub'])){
                    call_user_func($_GET['sub']);
                }
            }
        }
        
        if($pagenow == 'profile.php' || $pagenow == 'user-edit.php'){
            wp_register_script('genesis-admin-profile', plugins_url('js/admin-profile.js', __FILE__), array('jquery-ui-datepicker'));
            
            wp_enqueue_script('genesis-admin-profile');
        }
        
        if(isset($_GET['page']) && $_GET['page'] == 'genesis-tracker'){
            wp_register_script('genesis-admin-global', plugins_url('js/admin-global.js', __FILE__), array('jquery'));
            
            wp_enqueue_script('genesis-admin-global');
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

    public static function getFourWeekLogsForUser($user_id){
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            'SELECT * FROM ' . GenesisTracker::getFourWeekEmailLogTableName() . '
            WHERE user_id=%d 
            ORDER BY log_date DESC'
        , $user_id));

        return $results;
    }



    public static function getUserLogDetails($sortBy = 'measure_date', $user = null, $manualMode = false, $useCache = false){
        global $wpdb;
        
        if(!$sortBy){
            $sortBy = 'measure_date';
        }
        
        if($user){
            $where = " WHERE u.ID = $user";
        }
        
        if($useCache && $cache = GenesisTracker::getCacheData(GenesisTracker::userDataCacheKey . ($user ? '-' . $user : '-sb-' . $sortBy))){
            return $cache;
        }
        

        // Use this when executing the SQL in the GUI:
        //set global sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
        // set session sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
        
        $results = $wpdb->get_results($sql =  
            "SELECT *, IFNULL(weight - start_weight, 0) weight_change,
            /* IF(weight - LEAST(lowest_weight, start_weight) >= 1 AND user_registered < date_sub(now(), interval 6 month), 1, 0) as gained_more_than_one_kg, */
            /* This first one is without the red flag email check */
            IF(six_month_date IS NOT NULL 
                AND registered_for_year = 0 
                AND withdrawn <> 1
                AND six_month_weight IS NOT NULL, 
                    IF(last_six_month_weight IS NULL, six_month_weight, last_six_month_weight) - LEAST(IFNULL(min_weight_after_six_months, 10000), six_month_weight), 0) 
                as six_month_benchmark_change,
      
              
            
            IF(min_weight_after_six_months IS NULL, six_month_weight, LEAST(min_weight_after_six_months, six_month_weight)) as benchmark_weight
            
             FROM 
                (SELECT u.user_registered, u.user_email, u.ID user_id,  
                MAX(measure_date) as measure_date, 
                MIN(weight) as lowest_weight,
                UNIX_TIMESTAMP(MAX(measure_date)) unix_timestamp,
                IFNULL(account_active, 1) as account_active,
                IFNULL(user_contacted, 0) as user_contacted,
                IFNULL(withdrawn, 0) as withdrawn,
                notes,
                six_month_weight,
                start_weight,
                
                
                six_month_email_opt_out,
                passcode_group,
                
                user_first_name.meta_value as first_name,
                user_last_name.meta_value as last_name,
                six_month_date,
                start_date,
                study_group,
                CONCAT(user_first_name.meta_value, ' ' , user_last_name.meta_value) as user_name,
                UNIX_TIMESTAMP(u.user_registered) as user_registered_timestamp,
                IF(DATE_ADD(DATE_ADD(start_date, INTERVAL (7 - WEEKDAY(start_date)) DAY), INTERVAL 52 WEEK) < NOW(), 1, 0) as registered_for_year,
                /* The weeks registered goes from the monday after the start date, not registration date */
                FLOOR(DATEDIFF(NOW(), DATE_ADD(start_date, INTERVAL (7 - WEEKDAY(start_date)) DAY))/7) + 1 as weeks_registered,
                
                DATE_ADD(start_date, INTERVAL (7 - WEEKDAY(start_date)) DAY) as actual_start_date,
                (SELECT weight 
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
                ORDER BY measure_date DESC 
                LIMIT 1) as weight,
                (SELECT weight 
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
                    AND measure_date > six_month_date
                ORDER BY measure_date DESC 
                LIMIT 1) as last_six_month_weight,
                (SELECT min(weight)
                    FROM " . GenesisTracker::getTrackerTableName() . " 
                WHERE NOT ISNULL(weight) 
                    AND user_id=u.ID
                    AND measure_date >= six_month_date
                    AND measure_date < (
                        SELECT MAX(measure_date) 
                            FROM " . GenesisTracker::getTrackerTableName() . " 
                            WHERE user_id = u.ID
                            AND NOT ISNULL(weight)
                    ) 
                ) as min_weight_after_six_months
                
            FROM " . $wpdb->users . " u
                LEFT JOIN " . GenesisTracker::getTrackerTableName() . " t
                    ON u.ID = t.user_id
                LEFT JOIN " . $wpdb->usermeta . " as user_first_name
                    ON user_first_name.user_id = u.ID
                    AND user_first_name.meta_key = 'first_name'
                LEFT JOIN " . $wpdb->usermeta . " as user_last_name
                    ON user_last_name.user_id = u.ID
                    AND user_last_name.meta_key = 'last_name'
                LEFT JOIN " . GenesisTracker::getUserDataTableName() . " ud
                    ON u.ID = ud.user_id
                $where
                GROUP BY u.ID
                
            ) as mainQuery
            ORDER BY $sortBy",
            ARRAY_A);

     
        
     
        // Return results for a single user
        if($user && $results){
            GenesisTracker::setCacheData(GenesisTracker::userDataCacheKey . '-' . $user, $results[0], 3600);
            return $results[0];
        }
        
        GenesisTracker::setCacheData(GenesisTracker::userDataCacheKey . '-sb-' . $sortBy, $results);
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