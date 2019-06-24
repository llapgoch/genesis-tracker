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
    
    public static function userIsLosingOrMaintaining($user_id, $weeksBetweenEmail = 4){
        // This is for the four weekly emails.
        // A user is considered as losing if their two consecutive weights
        // prior to their newest log indicate a downward trend
        global $wpdb;
        
        $results = $wpdb->get_results( $sql = $wpdb->prepare("
        SELECT measure_date, weight, 
            ud.six_month_weight,
            ud.six_month_date
            FROM " . GenesisTracker::getTrackerTableName() . " t
            LEFT JOIN " . GenesisTracker::getUserDataTableName() . " ud
                ON ud.`user_id` = t.`user_id`
            WHERE /* measure_date >= DATE_SUB(NOW(), INTERVAL " . $weeksBetweenEmail . " WEEK) */
               /* AND  */ measure_date >= ud.six_month_date 
                AND t.user_id = %d
                AND weight IS NOT NULL
            ORDER BY measure_date DESC
            LIMIT 2",
            $user_id
        ));
        
        if($_GET['debug'] == 1){
            echo $sql;
        }
        
        // Remove the latest weight
        if(count($results) < 2){
            return false;
        }
        
        $secondWeight = (float) (array_pop($results)->weight);
        $lastWeight = (float) (array_pop($results)->weight);
        
        // To be losing, the last weight needs to be 1kg or under the previous weight
        if($lastWeight + 1 <= $secondWeight){
            return self::WEIGHT_LOSING;
        }
        
        
        
        // To be maintaining, a user must have their two previous weights within 1kg of the most recent weight
        if($secondWeight >= ($lastWeight - 0.5) && $secondWeight <= ($lastWeight + 0.5)){
            return self::WEIGHT_MAINTAINING;
        }
        
        return false;
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
        
        $results = $wpdb->get_results($sql = $wpdb->prepare(
        'SELECT exercise.*, tracker.measure_date FROM ' . GenesisTracker::getExerciseLogTableName() . ' exercise
        JOIN . ' . GenesisTracker::getTrackerTableName() . ' as tracker
            USING(tracker_id)
         WHERE tracker.user_id = %d
         ORDER BY tracker.measure_date DESC
         LIMIT 40'
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

    // Needs testing - for automatically sending four week and red flag emails
    public static function sendAllWeightEmails(){
        $logs = self::getUserLogDetails();
        $headers = GenesisTracker::getEmailHeaders();

        // Emails have been disabled for 2DW
        return;

        GenesisTracker::logMessage("Attempting send of four week emails");

        foreach($logs as $log){
            // Red Flag
            if($log['six_month_benchmark_change_email_check'] >= 1){

                $contents = "A red flag email has been sent to user: " . $log['user_email'];

                wp_mail(GenesisTracker::alternateContactEmail, 'A Red Flag Email Has Been Sent', $contents, $headers);

                $result = GenesisTracker::sendRedFlagEmail($log['user_id']);

                if(is_array($result)){
                    GenesisTracker::logMessage($result['message']);
                }
            }

            if($log['four_week_required_to_send']){

                $result = GenesisTracker::sendFourWeeklyEmail($log['user_id'], $log['four_week_outcome']);

                if(is_array($result)){
                    GenesisTracker::logMessage($result['message']);
                }
            }

        }
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
        
        $fourWeekArray = GenesisTracker::getFourWeeklyPoints();
        
        $newFourWeekZones = array();
        $weeksBetweenEmail = $manualMode ? 3 : 4;
        
        // Make the sending more flexible
        if($manualMode){
            foreach($fourWeekArray as $zone){
                $newFourWeekZones[] = $zone;
                $newFourWeekZones[] = $zone - 1;
            }
            
            $fourWeekArray = $newFourWeekZones;
        }
        
         $fourWeekZones = implode($fourWeekArray, ", ");

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
            /* This, for some reason wouldn't work with six_month_email_opt_out <> 1, hence the IS NULL OR = 0 */
            IF(registered_for_year = 0 AND withdrawn <> 1, 
                GREATEST(IF(red_flag_email_date IS NULL AND (six_month_email_opt_out IS NULL OR six_month_email_opt_out = 0) AND six_month_date IS NOT NULL
                    AND six_month_weight IS NOT NULL, 
                        IF(last_six_month_weight IS NULL, 
                            six_month_weight, last_six_month_weight
                        ) - LEAST(IFNULL(
                                min_weight_after_six_months
                            , 10000), 
                        six_month_weight), 
                    0), 
                  0),
                0) as six_month_benchmark_change_email_check,
                
            IF( registered_for_year = 0 AND withdrawn <> 1
                AND six_month_weight IS NOT NULL 
                AND six_month_date IS NOT NULL 
                /* Don't send a four week email if the red flag email was sent within the last week */
                AND (
                    red_flag_email_date IS NULL 
                    OR red_flag_email_date < DATE_SUB(NOW(), INTERVAL 1 WEEK)
                )
                /* This, for some reason wouldn't work with six_month_email_opt_out <> 1, hence the IS NULL OR = 0 */
                AND (six_month_email_opt_out IS NULL 
                    OR six_month_email_opt_out = 0
                ) 
                AND in_four_week_zone = 1, 
                    IF(four_weekly_date <= DATE_SUB(NOW(), INTERVAL " . $weeksBetweenEmail . " WEEK) 
                        OR four_weekly_date IS NULL, 
                1, 0), 
            NULL) as four_week_required_to_send,
            
            /* Use least_weight instead of lowest_weight in result sets as it takes into account the initial weight */
            LEAST(lowest_weight, start_weight, IFNULL(six_month_weight, 10000)) as least_weight,
            
            IF(four_weekly_weight IS NULL, 'NOTHING', 
                IF(IF(min_weight_after_six_months IS NULL, six_month_weight, LEAST(min_weight_after_six_months, six_month_weight)) - four_weekly_weight >= 1, 'LOSING',
                    IF(IF(min_weight_after_six_months IS NULL, six_month_weight, LEAST(min_weight_after_six_months, six_month_weight)) - four_weekly_weight <= -1, 'GAINING',
                        'MAINTAINING'
                    )
                )
            ) as four_week_outcome,
            
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
                red_flag_email_date,
                four_weekly_date,
                six_month_email_opt_out,
                passcode_group,
                failed_exercise_eligibility,
                UNIX_TIMESTAMP(four_weekly_date) as four_weekly_date_timestamp,
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
                IF((FLOOR(DATEDIFF(NOW(), DATE_ADD(start_date, INTERVAL (7 - WEEKDAY(start_date)) DAY))/7) + 1) IN ($fourWeekZones), 1, 0) as in_four_week_zone,
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
                ) as min_weight_after_six_months,
                (SELECT weight 
                    FROM " . GenesisTracker::getTrackerTableName() . "
                    /* Change this back to four weeks when getting the user's weight */
                    WHERE measure_date >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
                        AND measure_date > six_month_date
                        AND weight IS NOT NULL
                        AND user_id=u.ID
                    ORDER BY measure_date DESC
                    LIMIT 1
                ) as four_weekly_weight
                
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

        $fourWeekPoints = GenesisTracker::getFourWeeklyPoints();
        
        foreach($results as &$result){
            $result['red_flag_message'] = '';
            // Change the output if the user's week doesn't fit with a four week point
            if(!in_array($result['weeks_registered'], $fourWeekPoints)){
                $result['four_week_required_to_send'] = 0;
            }
            
            // Do the four weekly logic
            if($result['four_week_outcome'] == self::WEIGHT_MAINTAINING 
                || $result['four_week_outcome'] == self::WEIGHT_GAINING){
                
                // Put this back to four weeks as we still want to get the weight for the last month
                if($losingOrMaintaining = self::userIsLosingOrMaintaining($result['user_id'], 4)){
                    $result['four_week_outcome'] = $losingOrMaintaining;
                }
            }

            // Don't send a red flag email if the user is flagged as losing weight
            if($result['four_week_outcome'] == self::WEIGHT_LOSING){
               $result['six_month_benchmark_change_email_check'] = 0;
               $result['red_flag_message'] = 'This user has recently lost weight so a red flag is not available to send.';
            }else{
                // If the user is not losing and a red flag has been flagged, don't mark the four week email.
                if($result['six_month_benchmark_change_email_check'] >= 1){
                    $result['four_week_required_to_send'] = 0;
                }
            }
            
        }

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