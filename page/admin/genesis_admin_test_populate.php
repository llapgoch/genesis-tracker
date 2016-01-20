MIGRATION FOR USERS TO THE NEW SYSTEM

<?php
function migrate_users(){
    global $wpdb;
    
    $q = "(SELECT u.id, initial_weight.`meta_value`
                   AS initial_weight,
                   account_active.`meta_value`
                   AS account_active,
                   passcode_group.`meta_value`
                   AS passcode_group,
                   user_contacted.`meta_value`
                   AS contacted,
                   withdrawn.`meta_value`
                   AS withdrawn,
                   notes.`meta_value`
                   AS notes,
                   red_flag_email_date.`meta_value`
                   AS red_flag_email_date,
                   four_weekly_date.`meta_value`
                   AS four_weekly_date,
                   six_month_date.`meta_value`
                   AS six_month_date,
                   start_date.`meta_value`
                   AS start_date

            FROM   genwp_users u
                   LEFT JOIN genwp_genesis_tracker t
                          ON u.id = t.user_id
                   LEFT JOIN genwp_usermeta AS initial_weight
                          ON initial_weight.user_id = u.id
                             AND initial_weight.meta_key =
                                 'genesis___tracker___start_weight'
                   LEFT JOIN genwp_usermeta AS account_active
                          ON account_active.user_id = u.id
                             AND account_active.meta_key =
                                 'genesis___tracker___active'
                   LEFT JOIN genwp_usermeta AS user_first_name
                          ON user_first_name.user_id = u.id
                             AND user_first_name.meta_key = 'first_name'
                   LEFT JOIN genwp_usermeta AS user_last_name
                          ON user_last_name.user_id = u.id
                             AND user_last_name.meta_key = 'last_name'
                   LEFT JOIN genwp_usermeta AS passcode_group
                          ON passcode_group.user_id = u.id
                             AND passcode_group.meta_key =
                                 'genesis___tracker______ELIGIBILITY_GROUP___'
                   LEFT JOIN genwp_usermeta AS user_contacted
                          ON user_contacted.user_id = u.id
                             AND user_contacted.meta_key =
                                 'genesis___tracker___contacted'
                   LEFT JOIN genwp_usermeta AS withdrawn
                          ON withdrawn.user_id = u.id
                             AND withdrawn.meta_key =
                                 'genesis___tracker___withdrawn'
                   LEFT JOIN genwp_usermeta AS notes
                          ON notes.user_id = u.id
                             AND notes.meta_key = 'genesis___tracker___notes'
                   LEFT JOIN genwp_usermeta AS six_month_weight
                          ON six_month_weight.user_id = u.id
                             AND six_month_weight.meta_key =
                                 'genesis___tracker___weight_six_months'
                   LEFT JOIN genwp_usermeta AS red_flag_email_date
                          ON red_flag_email_date.user_id = u.id
                             AND red_flag_email_date.meta_key =
                                 'genesis___tracker___red_flag_email_date'
                   LEFT JOIN genwp_usermeta AS four_weekly_date
                          ON four_weekly_date.user_id = u.id
                             AND four_weekly_date.meta_key =
                                 'genesis___tracker___four_weekly_email_date'
                   LEFT JOIN genwp_usermeta AS six_month_date
                          ON six_month_date.user_id = u.id
                             AND six_month_date.meta_key =
                                 'genesis___tracker___six_month_date'
                   LEFT JOIN genwp_usermeta AS start_date
                          ON start_date.user_id = u.id
                             AND start_date.meta_key =
                                 'genesis___tracker___start_date'
                   LEFT JOIN genwp_usermeta AS six_month_email_opt_out
                          ON six_month_email_opt_out.user_id = u.id
                             AND six_month_email_opt_out.meta_key =
                                 'genesis___tracker___omit_six_month_email_key'
            GROUP  BY id)";
    echo "<pre>";
    
    
    // VAULES TO BRING ACROSS FROM WP'S user meta table to our user details table.
    $valsToMigrate = array(
        'initial_weight', 
        'account_active',
        'passcode_group',
        'contacted',
        'withdrawn',
        'notes',
        'red_flag_email_date',
        'four_weekly_date',
        'six_month_date',
        'start_date',
        'omit_six_month_email_key'
    );
    
    $results = $wpdb->get_results($q);
    
    foreach($results as $result){
        // Get the result set from the DB, update if it's there and insert if it's not.
        var_dump($result->id);
    }
    
}

migrate_users();
