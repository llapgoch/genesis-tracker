<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class GenesisTracker{
    const UNIT_IMPERIAL = 1;
    const UNIT_METRIC = 2;
    // Unfortunately, we can't get the comments plugin version from anywhere but the admin area - so we have to store
    // it twice.  Go Wordpress!


    const version = "1.47.2";
    
    const userIdForAutoCreatedPages = 1;
    const prefixId = "genesis___tracker___";
    const userPageId = "user_page";
    const inputProgressPageId = "progress_page";
    const initialWeightPageId = "initial_weight_page";
    const eligibilityPageId = "eligibility_page";
    const eligibilityExercisePageId = "eligibility_exercise_page";
    const ineligiblePageId = "ineligibile_page";
    const eligibilityDoctorDownloadPageId = "eligibility_doctor_download_page";
    const prescriptionPageId = "prescription_page";
    const physiotecLoginPageId = "physiotec_login_page";
    const eligibilityDoctorPageId = "eligibility_doctor_page";
    const ineligibleSurveyPageId = "ineligible_survey_page";
    const weightEnterSessionKey = "___WEIGHT_ENTER___";
    const eligibilitySessionKey = "___USER_ELIGIBLE___";
    const eligibilityGroupSessionKey = "___ELIGIBILITY_GROUP___";
    const adminNoticesSessionKey      = "___ADMIN_NOTICES";
    const registrationPasswordEmailSessionKey = "___REGISTRATION_PASSWORD_EMAIL__";
    const targetPageId = "tracker_page";
    const alternateContactEmail = "hello@fhlstudy.co.uk";
    
    const minHealthyWeightKey = "min_healthy_weight";
    const maxHealthyWeightKey = "max_healthy_weight";
    const weightTargetKey     = "weight_target";
    const sixMonthWeightTargetKey = "weight_target_six_months";
    const twelveMonthWeightTargetKey = "weight_target_twelve_months";
    const lastReminderDateKey = "last_reminder_date";

    
    // Migrate relevant keys to cols here
    const userActiveCol = "account_active";
    const passcodeGroupCol = "passcode_group";
    const eligibilityCol = "eligibility_id";
    const userStartWeightCol = "start_weight";
    const userContactedCol = "user_contacted";
    const userWithdrawnCol = "withdrawn";
    const userNotesCol     = "notes";
    const sixMonthWeightCol = "six_month_weight";
    const redFlagEmailDateCol = "red_flag_email_date";
    const fourWeekleyEmailDateCol = "four_weekly_date";
    const sixMonthDateCol = "six_month_date";
    const userStartDateCol = "start_date";
    const studyGroupCol = "study_group";
    const sixMonthEmailOptOutCol = "six_month_email_opt_out"; // previously omitSixMonthEmailKey
    const showMedCol = "show_med";
    const genderCol = "gender";

    const userActiveEmailSentKey = "active_email_sent";
    const targetPrependKey = "target_";
    const averageDataKey = "average_data";
    const versionKey = "version";
    const userInitialUnitSelectionKey = "initial_unit_selection";
    
    const omitUserReminderEmailKey = "omit_reminder_email";
    const defaultFieldError = '<div class="form-input-error-container error-[FIELDFOR] field-[TYPE]">
                                <span class="form-input-error">[ERROR]</span></div>';
    const editCapability = "edit_genesis";
    const userDataCacheKey = "genesis_admin_user_data";

    
    // 7 Stone
    const MIN_VALID_WEIGHT = 44.4;
    // 25 Stone
    const MAX_VALID_WEIGHT = 158.8;
    
    // 0.5 - 3m
    const MIN_VALID_HEIGHT = 0.5;
    const MAX_VALID_HEIGHT = 2.5;

    const AEROBIC_EXERCISE_ACHIEVEMENT = 150;
    const RESISTANCE_AMOUNT_ACHIEVEMENT = 3;

    const FOUR_WEEK_SEND_TYPE_MANUAL = 'MANUAL';
    const FOUR_WEEK_SEND_TYPE_AUTOMATIC = 'AUTOMATIC';
    
    const CACHE_DIR = "genesis-tracker";
    const REGISTER_URL = "wp-login.php?action=register";
    const DOCTOR_ELIGIBILITY_GET_PARAM = "eligibility_check_success";

    // TODO: MAKE SURE THIS IS ENABLED
    const CACHE_ENABLED = false;
    const INCLUDE_ADMIN_USERS_IN_AVERAGES = true;
    
    protected static $eligibilityPasswords = array(
        "FHLUHS",
        "FHLTGH",
        "FHLWSM"
    );
    

    public static $pageData = array();
    public static $dietDaysToDisplay = 7;
    
    protected static $_initialUserUnit;

    // NOTE: It appears these have switched - med values are actually personal portions!
    protected static $_userMetaTargetFields = array(
        "carbs" => array("name" => "Carbohydrate", "unit" => "portions", "male" => "0", "female" => "0"),
        "protein" => array("name" => "Protein", "unit" => "portions", "male" => "Between 6 and 11", "female" => "Between 5 and 9"),
        "dairy" => array("name" => "Dairy", "unit" => "portions", "male" => "Aim for 3", "female" => "Aim for 3"),
        "vegetables" => array("name" => "Vegetables", "unit" => "portions", "male" => "Aim for 5", "female" => "Aim for 5"),
        "fruit" => array("name" => "Fruit", "unit" => "portions", "male" => "Aim for 1", "female" => "Aim for 1"),
        "fat" => array("name" => 'Fat', "unit" => "portions", "male" => "Maximum of 4", "female" => "Maximum of 3"),
        "treat" => array("name" => "Treat", "unit" => "portions", "male" => "0", "female" => "0"),
        "alcohol" => array("name" => "Alcohol", "unit" => "units", "male" => "0", "female" => "0")
    );

    
    protected static $_userTargetTimes = array(
        "breakfast" => array("name" => "Breakfast"),
        "lunch" => array("name" => "Lunch"),
        "evening" => array("name" => "Evening"),
        "snacks" => array("name" => "Snacks"),
        "drinks" => array("name" => "Drinks")
    );

    protected static $_exerciseTypes = array(
        "light" => array("name" => "Light", "color" => '#f49ac1'),
        "moderate" => array("name" => "Moderate", "color" => '#fbaf5d'),
        "vigorous" => array("name" => "Vigorous", "color" => '#ed1c24')
    );

    protected static $_exerciseTypesResistance = array(
        "arms" => array("name" => "Arms", "color" => "#f49ac1"),
        "legs" => array("name" => "Legs", "color" => "#77f6ed"),
        "trunk" => array("name" => "Trunk", "color" => "#76dfaa"),
        "combination" => array("name" => "Combination", "color" => "#8560a8"),
        "whole" => array("name" => "Whole Body", "color" => "#f84451")
    );

    public static $genders = array(
        "male" => array("name" => "Male"),
        "female" => array("name" => "Female")
    );
    
    protected static $_fourWeekPoints = array(
        30, 34, 38, 42, 46, 50
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
          exercise_minutes_resistance int(11) DEFAULT NULL,
          exercise_type varchar(255) DEFAULT NULL,
          exercise_type_resistance varchar(255) DEFAULT NULL,
          exercise_description text DEFAULT NULL,
          exercise_description_resistance text DEFAULT NULL,
          weight_unit tinyint(1) unsigned DEFAULT 1,
          food_log_explanation text DEFAULT NULL,
          PRIMARY KEY  (tracker_id),
          KEY user_id (user_id)
        )");

        dbDelta($sql = "CREATE TABLE " . self::getLogTableName() . " (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          type varchar(255) DEFAULT NULL,
          message text,
          date_created datetime DEFAULT NULL,
          PRIMARY KEY  (id)
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
          `value` decimal(5,2) DEFAULT NULL,
          PRIMARY KEY  (`food_log_id`),
          KEY `tracker_id` (`tracker_id`),
          KEY `time` (`time`),
          KEY `food_type_time` (`food_type`,`time`),
          KEY `tracker_id_time_food_type` (`tracker_id`,`time`,`food_type`)
          KEY `tracker_id` (`tracker_id`)
        )");
        
        dbDelta($sql = "CREATE TABLE " . self::getFoodDescriptionTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `tracker_id` int(10) unsigned NOT NULL,
          `time` varchar(255) DEFAULT NULL,
          `description` text,
          PRIMARY KEY  (`id`),
          KEY `time` (`time`),
          KEY `tracker_id` (`tracker_id`)
        )");
        
        $userDataTableExists = self::checkTableExists(self::getUserDataTableName()); 
        
        dbDelta($sql = "CREATE TABLE " . self::getUserDataTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `start_weight` decimal(10,6) DEFAULT NULL,
          `account_active` tinyint(1) DEFAULT NULL,
          `passcode_group` varchar(255) DEFAULT NULL,
          `user_contacted` tinyint(1) DEFAULT NULL,
          `withdrawn` tinyint(1) DEFAULT NULL,
          `notes` longtext,
          `six_month_weight` decimal(10,6) DEFAULT NULL,
          `red_flag_email_date` datetime DEFAULT NULL,
          `four_weekly_date` datetime DEFAULT NULL,
          `six_month_date` datetime DEFAULT NULL,
          `start_date` datetime DEFAULT NULL,
          `six_month_email_opt_out` tinyint(1) DEFAULT NULL,
          `study_group` varchar(255) DEFAULT NULL,
          `show_med` varchar(255) DEFAULT NULL,
          `gender` varchar(255) DEFAULT 'female',
          `eligibility_id` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`)
        )");


        // Reinstall eligibility questions for this version
        if(self::version == '1.47'){
            $wpdb->query($sql = "DROP TABLE `" . self::getEligibilityQuestionsTableName() . '`');
        }
        
        $eligibilityQuestionsTableExists = self::checkTableExists(self::getEligibilityQuestionsTableName());

        
        dbDelta($sql = "CREATE TABLE " . self::getEligibilityQuestionsTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `question` text,
          `correct` tinyint(1) DEFAULT NULL,
          `set_number` int(11) DEFAULT NULL,
          `position` int(11) NOT NULL DEFAULT 0,
          PRIMARY KEY  (`id`)
        )");


        // unique_id is what will be on the PDF for doctors
        dbDelta($sql = "CREATE TABLE " . self::getEligibilityResultTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `unique_id` varchar(255) DEFAULT NULL, 
          `hash_id` varchar(255) DEFAULT NULL,
          `ip_address` varchar(255) DEFAULT NULL,
          `weight` decimal(10,6) DEFAULT NULL,
          `height` decimal(10,6) DEFAULT NULL,
          `age` int(11) unsigned DEFAULT NULL,
          `high_speed_internet` tinyint(1) unsigned DEFAULT NULL,
          `can_understand_english` tinyint(1) unsigned DEFAULT NULL,
          `bmi` decimal(10,6) DEFAULT NULL,
          `date_created` datetime DEFAULT NULL,
          `is_eligible` tinyint(1) DEFAULT NULL,
          `passcode` VARCHAR(255) DEFAULT NULL,
          `no_physical_activity_reason` TEXT DEFAULT NULL,
          PRIMARY KEY  (`id`),
          KEY `hash_id` (`hash_id`)
        )");
        
        dbDelta($sql = "CREATE TABLE ". self::getEligibilityResultAnswersTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `result_id` int(11) unsigned DEFAULT NULL,
          `question_id` int(11) unsigned DEFAULT NULL,
          `answer` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`)
        )");

        $wpdb->query($sql =  "ALTER TABLE " . self::getEligibilityResultTableName() . " 
            DROP COLUMN happy_to_follow" );


        
        dbDelta($sql = "CREATE TABLE ". self::getFourWeekEmailLogTableName() . " (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(11) unsigned DEFAULT NULL,
          `type` VARCHAR(255) DEFAULT NULL,
          `log_date` datetime DEFAULT NULL,
          `week` TINYINT(4) DEFAULT NULL,
          `send_type` varchar(255) DEFAULT 'MANUAL',
          PRIMARY KEY  (`id`)
        )");
        

        if(!$userDataTableExists){
            // Migrate the data to the new table!
            GenesisMigration::migrateUsers();
        }
        
        // Don't install the questions again after the first time
        if($eligibilityQuestionsTableExists == false){
            // Initial questions to install
            $eligibilityQuestions = array(
                array(
                  'question' => 'Are you receiving <strong>annual or 18 monthly mammograms because you have an increased risk of breast cancer</strong>?',
                  'correct' => 1,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Are you or have you been on the <strong>PROCAS (<span class="u-underline">P</span>redicting <span class="u-underline">R</span>isk <span class="u-underline">o</span>f <span class="u-underline">B</span>reast <span class="u-underline">C</span>ancer <span class="u-underline">a</span>t <span class="u-underline">S</span>creening) study</strong>?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Is anyone in your family already on this research study (<strong>Family History Lifestyle Study</strong>)?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Have you ever been diagnosed with <strong>kidney disease</strong>?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Are you currently prescribed medication for raised <strong>cholesterol</strong>?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Are you <strong>pregnant, breast feeding or planning pregnancy</strong> in the next 12 months?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Have you ever been diagnosed with <strong>cancer</strong>? <em>This does not include a diagnosis of non-melanoma skin cancer or precancerous cells on a cervical smear (CIN)</em>',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Have you ever been diagnosed with <strong>diabetes</strong>?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                  'question' => 'Have you ever had a <strong>stroke, Transient Ischemic Attack (TIA), angina, heart attack, heart failure, or ventricular or aortic aneurysm</strong>?',
                  'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Do you currently have a diagnosis of an <strong>eating disorder</strong> (e.g. binge eating or bulimia)?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Do you currently have an <strong>alcohol</strong> or <strong>drug dependency</strong>?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Have you ever been diagnosed with a <strong>personality disorder</strong> or <strong>bipolar disorder</strong> (formerly known as manic depression) or have you ever tried to <strong>self-harm</strong>?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Are you currently taking any of the following medication prescribed for psychosis and schizophrenia: <strong>Aripiprazole, Clozapine, Olanzapine, Quetiapine or Risperidone</strong> <em>Please note, these medicines may have a different brand name. If you’re unsure, please check the box the medicine is in</em>',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Are you currently successfully <strong>following a diet and/or physical activity plan</strong> and have <strong>lost more than 2 lb (1 kg) of weight</strong> in the last 2 weeks?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Have you ever had <strong>weight loss surgery</strong> e.g. gastric bypass or sleeve gastrectomy, or do you plan to have this type of surgery in the next 12 months?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Are you currently taking any <strong>medication to help you lose weight</strong> e.g. Orlistat, Xenical, Alli?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Are you on any <strong>specialist medical diet</strong> to treat conditions such as phenylketonuria, maple syrup urine disease, glycogen storage diseases, urea cycle disorders, advanced kidney disease or advanced liver disease?',
                    'correct' => 2,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Are you willing to follow a <strong>healthy diet and physical activity programme to lose weight</strong>?',
                    'correct' => 1,
                    'set_number' => 1
                ),
                array(
                    'question' => 'Has your doctor ever said that you have a heart condition and that you should only do physical activity recommended by a doctor?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'Do you feel pain in your chest when you do physical activity?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'In the past month, have you had chest pain when you were <strong>not</strong> doing physical activity?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'Do you ever lose your balance because of dizziness or do you ever lose consciousness?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'Do you have a bone or joint problem (for example, back, knee or hip) that could be made worse by doing physical activity?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'Is your doctor currently prescribing drugs (for example, water pills) for your blood pressure or heart condition?',
                    'correct' => 2,
                    'set_number' => 2
                ),
                array(
                    'question' => 'Do you know of <strong>any other reason</strong> why you should not do physical activity?',
                    'correct' => 2,
                    'set_number' => 2
                )

            );

            $position = 0;

            // Insert the questions into the DB
            foreach($eligibilityQuestions as $questionData){
                $questionData['position'] = $position;
                $wpdb->insert(self::getEligibilityQuestionsTableName(), $questionData);
                $position += 10;
            }
        }

        // Change surveys - add a hidden type (initially so we can log eligibility IDs alongside a survey)
        $wpdb->query($sql = "ALTER TABLE {$wpdb->prefix}surveys_question CHANGE user_answer_format user_answer_format ENUM('entry','textarea','checkbox','hidden')");


         // Create the user page if it's not already there         
         self::createUserPage();
         self::createInputPage();
         self::createTargetInputPage();
         self::createInitialWeightPage();
         self::createEligibilityPage();
         self::createEligibilityExercisePage();
         self::createIneligiblePage();
         self::createIneligibleSurveyPage();
         self::createPrescriptionPage();
         self::createPhysiotecLoginPage();
         self::createEligibilityDoctorPage();
         self::createEligibilityDoctorDownloadPage();
         
          self::updateOption("version", self::version);
         
         $role = get_role('administrator');
         
         if($role){
             $role->add_cap(self::editCapability);
         }
     }

    public static function logMessage($message, $type = ''){
        global $wpdb;

        $wpdb->insert(self::getLogTableName(), array(
            'message' => $message,
            'type' => $type,
            'date_created' => current_time('Y-m-d H:i:s')
        ));
    }
     
     public static function getFourWeeklyPoints(){
         return self::$_fourWeekPoints;
     }
     
     public static function checkVersionUpgrade(){
         $installedVersion = self::getOption(self::versionKey);
         
         if($installedVersion !== self::version){
             self::install();             
         }
     }
     
     public static function isOnLogoutPage(){
         $logoutUrl = wp_logout_url();
         
         if(site_url($_SERVER['REQUEST_URI']) == $logoutUrl){
             return true;
         }
     }
     
     public static function isOnRegistrationPage(){
         $registerUrl = self::REGISTER_URL;

         if(strpos($registerUrl, $_SERVER['REQUEST_URI']) !== false){
             return true;
         }
         
         if(self::isOnLoginPage() && isset($_GET['action']) && $_GET['action'] == 'register'){
             return true;
         }
         
         return false;
     }
     
     public static function isOnLoginPage(){
         return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
     }
     
     public static function initActions(){

         if((self::isOnRegistrationPage() && self::userIsEligible() == false)
             && self::isOnDoctorEligibilityRegistrationPage() == false){
             wp_redirect(home_url());
         }

         if(self::isOnRegistrationPage() || self::isOnLoginPage()){
             wp_enqueue_script('login', plugins_url('js/login.js', __FILE__), array('jquery'));
             wp_localize_script('login', 'wpBaseUrl', get_site_url());
         }
        
        // We set the username as the email address when registering
        if(self::isOnRegistrationPage() && count($_POST)){
            if(isset($_POST['user_email'])){
                $_POST['user_login'] = $_POST['user_email'];
            }
        }
     }
     
     public static function checkRegistrationErrors($errors, $sanitized_user_login, $user_email){
         // Remove the username error - it's the same as email in our case
         $errs = $errors->errors;
         
         if(isset($errs['username_exists'])){
             unset($errs['username_exists']);
         }
         
         $errors->errors = $errs;
         
         if(self::userIsEligible() == false && self::isOnDoctorEligibilityRegistrationPage() == false){
             $errors->errors = array();
             $errors->add( 'eligible_error', __('<strong>ERROR</strong>: Sorry, you are not eligible for this research study.','mydomain') );
             return $errors;
         }

         if(self::isOnDoctorEligibilityRegistrationPage()) {
             if (empty($_POST['unique_id'])) {
                 $errors->add('unique_id_error', __('<strong>ERROR</strong>: Please enter your Unique ID'));
             }else{
                if(self::getEligibilityResultFromUniqueID($_POST['unique_id']) == false){
                    $errors->add('eligibility_not_found', __('<strong>ERROR</strong>: We could not find a record of your eligibility results. Please make sure you have entered your Unique ID correctly'));
                }
             }
         }
         
         if(empty($_POST['first_name'])){
            $errors->add( 'first_name_error', __('<strong>ERROR</strong>: You must include a first name.') );
        }
    
         if (empty( $_POST['last_name'] )){
               $errors->add( 'last_name_error', __('<strong>ERROR</strong>: You must include a last name.') );
         }
        
         if (empty( $_POST['tel'] )){
               $errors->add( 'tel_error', __('<strong>ERROR</strong>: You must include a telephone number.') );
         }
        
        if ( $_POST['password'] !== $_POST['repeat_password'] ) {
            $errors->add( 'passwords_not_matched', "<strong>ERROR</strong>: Passwords must match" );
        }
        if ( strlen( trim($_POST['password']) ) < 8 ) {
            $errors->add( 'password_too_short', "<strong>ERROR</strong>: Passwords must be at least eight characters long" );            
        }

        
        return $errors;
     }
     
     public static function doSurveySuccessMessage($message){
         $afterMessage = "";
         
         if(self::isOnIneligibleSurveyPage()){
             $dietPlanPdfUrl = plugins_url("/downloads/2-day-diet-advice.pdf", __FILE__);
             $afterMessage = "For further information about the diet and to access a diet plan, <a href='{$dietPlanPdfUrl}'>click here</a>";
         }
         
         return GenesisThemeShortCodes::successBox(
             $message . $afterMessage . (
            is_user_logged_in() ?
          '<a href="' . self::getUserPagePermalink() . '" class="button large blue">Go to your progress graph</a>'
            : ""
             )
        );
     }

     
     public static function userHasJustRegistered(){
         return isset($_GET['checkemail']) && $_GET['checkemail'] == 'registered';
     }
     
     public static function modifyRegistrationMessage($errors, $redirect_to){
         
         if(!count($errors->errors)){
             return $errors;
         }

         if(isset($errors->errors['registered'])){
             $errs = $errors->errors;
             $errs['registered'] = array();
             
             $errors->errors = $errs;
         }
         
         return $errors;
     }
     
     public static function checkLoginAction($user, $password){
         if(!$user){
             return;
         }
         
         $isActive = self::getUserData($user->ID, GenesisTracker::userActiveCol);
         $startDate = self::getUserData($user->ID, GenesisTracker::userStartDateCol);

         if((is_numeric($isActive) && $isActive == 0) || !$startDate){
              return new WP_Error( 'user_inactive',  __( '<strong>ERROR</strong>: Sorry, your account has not been activated yet.'));
          }
          
          return $user;
     }
     
     public static function checkRegistrationPost($user_id){
         if ( isset( $_POST['first_name'] ) ){
             update_user_meta($user_id, 'first_name', trim($_POST['first_name']));
         }
   
         if ( isset( $_POST['last_name'] ) ){
             update_user_meta($user_id, 'last_name', trim($_POST['last_name']));
         }
         
         if ( isset( $_POST['tel'] ) ){
             update_user_meta($user_id, 'tel', trim($_POST['tel']));
         }
         
         if( isset( $_SESSION[self::getOptionKey(self::eligibilityGroupSessionKey)]) ){
             GenesisTracker::setUserData($user_id, self::passcodeGroupCol, $_SESSION[self::getOptionKey(self::eligibilityGroupSessionKey)]);
         }

         // Save the eligibility data and passcode data if we're coming back to the site from GP consent
         // Only log eligibility result if in this case, in other cases we know the answers to the questions will be all correct
         if(self::isOnDoctorEligibilityRegistrationPage()){
             if($eligibilityResult = self::getEligibilityResultFromUniqueID($_POST['unique_id'])) {
                 GenesisTracker::setUserData($user_id, self::eligibilityCol, $eligibilityResult->id);
                 GenesisTracker::setUserData($user_id, self::passcodeGroupCol, $eligibilityResult->passcode);
             }
         }
         
         $userdata = array();
         $userdata['ID'] = $user_id;
         $userdata['user_pass'] = trim($_POST['password']);

         $_SESSION[self::registrationPasswordEmailSessionKey] = true;

         $user_id = wp_update_user( $userdata );
         update_user_option( $user_id, 'default_password_nag', 0, true );

         GenesisTracker::setUserData($user_id, self::userActiveCol, 0);

         $plaintext_pass = trim($_POST['password']);
         
         // Send our registration email with the new email
         if ( empty($plaintext_pass) ){
             return;
        }
        
        unset($_SESSION[self::getOptionKey(self::eligibilitySessionKey)]);

         $headers = self::getEmailHeaders();
         $contents = self::getTemplateContents('register');
        
         $contents = str_replace(array(
            "%user_email%",
            "%user_pass%",
            "%site_url%",
            '%genesis_logo%',
         ),array(
            trim($_POST['user_email']),
            $plaintext_pass,
            get_site_url(),
            self::getLogoUrl()
         ), $contents);
         
        $res = wp_mail(trim($_POST['user_email']), 'Welcome to the PROCAS Lifestyle Research Study', $contents, $headers); 

         unset($_SESSION[self::registrationPasswordEmailSessionKey]);
     }
     
     public static function getEligibilityQuestions($set = null){
         global $wpdb;
         $res = $wpdb->get_results($sql = "SELECT * FROM " . self::getEligibilityQuestionsTableName() .
             ($set === null ? "" : " WHERE `set_number`={$set} ") .
             " ORDER BY `position`
        ");

         return $res;
     }
     
     public static function getEligibilityAnswersForResultHash($hashId){
         global $wpdb;
         $res = $wpdb->get_results($sql = $wpdb->prepare("
             SELECT answers.* FROM " . self::getEligibilityResultAnswersTableName() . " answers
             JOIN " . self::getEligibilityResultTableName() . " result 
                 ON result.id = answers.result_id
             WHERE result.hash_id = %s", $hashId
        ));

        return $res;
     }
     
     public static function disableDefaultRegistrationEmail($vals){
         // If we're an administrator, keep the default email alert
         // So the new user gets their password
         if(is_admin() || !is_array($vals)){
             return $vals;
         }
         
         if(strpos($vals['subject'], 'Your username and password') !== false){
             $vals['to'] = '';
             $vals['subject'] = '';
             $vals['message'] = '';
         }
         
         return $vals;
     }
     
     public static function saveUserTargetFields($user_id){
         if(!is_admin()){ return; }

         $targetFields = self::getuserMetaTargetFields();
    
         foreach($targetFields as $fieldKey => $data){
             $fullKey = self::getOptionKey(self::targetPrependKey . $fieldKey); 

             if(isset($_POST[$fullKey])){
                 update_user_meta( $user_id, $fullKey, $_POST[$fullKey] );
             }
         }
    
         // Check whether the user has been activated
         $activeKey = self::userActiveCol;
         $contactedKey = self::userContactedCol;
         $withdrawnKey = self::userWithdrawnCol;
         $notesKey     = self::userNotesCol;
         $startDateKey = self::userStartDateCol;

         if(isset($_POST[$startDateKey])) {
             $dateParts = date_parse($_POST[$startDateKey]);

             if ($dateParts['day'] && $dateParts['month'] && $dateParts['year']) {
                 GenesisTracker::setUserData($user_id, $startDateKey, GenesisTracker::convertFormDate($_POST[$startDateKey]));
             }else{
                 $_POST[$activeKey] = false;
                 GenesisTracker::setUserData($user_id, $startDateKey, '');
             }
         }
         
         
         if(isset($_POST[$activeKey])){
             $active = (int) $_POST[$activeKey];
             $emailSent = (int) get_the_author_meta(self::getOptionKey(self::userActiveEmailSentKey), $user_id );
             
             self::setUserData($user_id, $activeKey, $active);

             if(!$emailSent && $active){
                 self::sendUserActivateEmail($user_id);
             }
         }
         
         // Check whether the user has been contacted
         if(isset($_POST[$contactedKey])){
             $contacted = (int) $_POST[$contactedKey];
             self::setUserData($user_id, $contactedKey, $contacted);
         }
         
         if(isset($_POST[$withdrawnKey])){
             $withdrawn = (int) $_POST[$withdrawnKey];
             self::setUserData($user_id, $withdrawnKey, $withdrawn);
         }
         
         if(isset($_POST[$notesKey])){
             $notes = (string) $_POST[$notesKey];
             self::setUserData($user_id, $notesKey, $notes);
         }
     }
     
     public static function getAdminUrl($query = array()){
         return admin_url('admin.php?page=genesis-tracker') . "&" . build_query($query);
     }

    public static function sendUserActivateEmail($user_id){
        $user = get_userdata($user_id);

        if(!$user){
            return;
        }

        update_user_meta( $user->ID, self::getOptionKey(self::userActiveEmailSentKey), 1);

        $headers = self::getEmailHeaders();
        $body = self::getTemplateContents('activated');

        $body = str_replace(
            array('%site_url%', '%genesis_logo%'),
            array(get_site_url(),  self::getLogoUrl()),
            $body
        );

        wp_mail($user->user_email, 'Your Family History Lifestyle Study account has been activated', $body, self::getEmailHeaders());
    }
     
     public static function userIsEligible(){
         return $_SESSION[self::getOptionKey(self::eligibilitySessionKey)] == true;
     }

    public static function isOnDoctorEligibilityRegistrationPage(){
        if(!isset($_GET[self::DOCTOR_ELIGIBILITY_GET_PARAM])
            || !(bool) $_GET[self::DOCTOR_ELIGIBILITY_GET_PARAM]){
            return false;
        }
        return self::isOnRegistrationPage();
    }
     
     public static function getuserMetaTargetFields(){
         return self::$_userMetaTargetFields;
     }
     
     public static function getUserTargetTimes(){
         return self::$_userTargetTimes;
     }

    public static function getExerciseTypes(){
        return self::$_exerciseTypes;
    }

    public static function getResistanceExerciseTypes(){
        return self::$_exerciseTypesResistance;
    }
     
     public static function getUserTargetLabel($key, $user_id = null){
         $user_id = !is_null($user_id) ? $user_id : get_current_user_id();

         if(!isset(self::$_userMetaTargetFields[$key])){
             return '';
         }         

         if('' == $val = get_the_author_meta(self::getOptionKey(self::targetPrependKey . $key), $user_id)){
             return '';
         }

         $fieldData = self::$_userMetaTargetFields[$key];
         
         return $val;
     }
     
     public static function getUserTargetUnit($key){
         if(!isset(self::$_userMetaTargetFields[$key]) || !isset(self::$_userMetaTargetFields[$key]['unit'])){
             return 'portions';
         }
         
         return self::$_userMetaTargetFields[$key]['unit'];
     }
     
     public static function getFoodDescriptionTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_food_description";
     }
     
     public static function getTrackerTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_tracker";
     }

     protected function getLogTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_log";
     }
     
     public static function getUserDataTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_userdata";
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
     
     public static function getEligibilityQuestionsTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_eligibility_questions";
     }
     
     public static function getEligibilityResultTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_eligibility_result";
     }
     
     public static function getEligibilityResultAnswersTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_eligibility_result_answers";
     }
     
     public static function getFourWeekEmailLogTableName(){
         global $wpdb;
         return $wpdb->base_prefix . "genesis_eligibility_four_week_log";
     }
     
     public static function checkTableExists($tableName){
         global $wpdb;
         $tableRes = $wpdb->get_var($sql = "SHOW TABLES LIKE '$tableName'");
         
         return $tableRes === $tableName;
     }
     
     public static function stoneToKg($stone, $pounds = 0){
         return (((float) $stone * 14) + (float) $pounds) * 0.453592;
     }
     
     public static function feetToMetres($feet, $inches = 0){
         $feet = (float) $feet;
         
         if($inches = (float) $inches){
             $feet += ($inches / 12);
         }
         
         return $feet * 0.3048;
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
     
     public static function niceFormatWeight($weight, $unit = "metric", $outputZero = false){
         $weight = (float)$weight;
         
         if($weight <= 0 && $outputZero == false){
             return "- -";
         }
         
         if($unit == "metric"){
             return $weight . " Kg";
         }
         
         $imperial = self::kgToStone($weight);
         $imperialString = "";
         
         if($imperial['stone'] > 0){
             $imperialString = round($imperial['stone'], 2) . " st";
         }
         
         if($imperial['pounds'] > 0){
             $imperialString .= $imperialString ? ", " : "";
             $imperialString .= round($imperial['pounds']) . " lbs";
         }
         
         return $imperialString;
     }
     
     public static function convertFormDate($formDate){
         preg_match("/(\d+)-(\d+)-(\d+)/", $formDate, $matches);
         return $matches[3] . "-" . $matches[2] . "-" . $matches[1];
     }
     
     public static function convertDBDate($dbDate){
         $dateParts = date_parse($dbDate);

         if($dateParts['day'] && $dateParts['month'] && $dateParts['year']){
             return date("d-m-Y", strtotime($dbDate));
          }

          return false;
     }
     
     public static function convertDBDatetime($dbDate){
         return date("d/m/Y H:i:s", strtotime($dbDate));
     }
     
     public static function prettyDBDate($dbDate){
          return date('d M Y', strtotime($dbDate));
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
     
     public function eligibilityPageAction(){
         $form = DP_HelperForm::createForm('eligibility');
         $form->fieldError = self::defaultFieldError;
         
         if(!DP_HelperForm::wasPosted()){
             return;
          }
         
         $form->setData($_POST);
         $action = $form->getRawValue('action');
         
         // Actions for the user input page
         
         switch($action){
             case "checkeligibility" :
             self::checkEligibility($form);
             break;
         }
     }

    public function eligibilityExercisePageAction(){
        $form = DP_HelperForm::createForm('eligibility_exercise');
        $form->fieldError = self::defaultFieldError;

        if(!DP_HelperForm::wasPosted()){
            return;
        }

        $form->setData($_POST);
        $action = $form->getRawValue('action');

        // Actions for the user input page

        switch($action){
            case "checkeligibility" :
                self::checkEligibilityExercise($form);
                break;
        }
    }

    public static function getPluginBase(){
        return plugin_dir_path(__FILE__);
    }

    public static function loadPDFLibraries(){
        $pluginBase = self::getPluginBase();

        require_once($pluginBase . 'lib/fpdf181/fpdf.php');
        require_once($pluginBase . 'lib/fpdi2/src/autoload.php');
    }

    public static function getEligibilityResultFromUniqueID($uniqueID){
        global $wpdb;

        $res = $wpdb->get_row(
            $sql = $wpdb->prepare(
                "SELECT * FROM " . self::getEligibilityResultTableName() . " WHERE unique_id=%s",
                $uniqueID
            )
        );

        return $res;
    }

    public static function eligibilityDoctorDownloadPageAction(){

        $hashID = isset($_GET['hash_id']) ? $_GET['hash_id'] : "";
        $result = self::getEligibilityResult($hashID, true);

        if(!$result){
            exit;
        }

        self::loadPDFLibraries();

        $pdf = new Fpdi();
        $pdfLocation = self::getPluginBase() . "original-documents/doctor-consent.pdf";

        $pageCount = $pdf->setSourceFile($pdfLocation);

        for($i = 1; $i <= $pageCount; $i++){
            $pageId = $pdf->importPage($i, PdfReader\PageBoundaries::MEDIA_BOX);
            $pdf->addPage();
            $pdf->useImportedPage($pageId);

            if($i == 1) {
                $pdf->SetFont('Helvetica');
                $pdf->SetTextColor(0, 113, 185);
                $pdf->SetXY(12, 80);
                $pdf->SetFontSize(10);
                $pdf->Write(0, "Your Unique ID: {$result->unique_id}");

                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY(12, 85);
                $pdf->Write(0, current_time('d/m/Y'));
            }
        }

        // The user will receive a PDF to download

        $pdf->Output('D', 'doctor-consent-form.pdf');
        exit;
    }

    public static function getEligibilityResult($eligibilityID, $useHash = false){
        global $wpdb;

        $col = $useHash ? 'hash_id' : 'id';

        $result = $wpdb->get_row(
            $sql = $wpdb->prepare(
                "SELECT * FROM " . self::getEligibilityResultTableName() . " WHERE {$col}=%s",
                $eligibilityID
            )
        );

        return $result;
    }


     
     public static function checkEligibility($form){
         // Validate all eligibility options
         $_SESSION[self::getOptionKey(self::eligibilitySessionKey)] = false;
         
         $eligibilityQuestions = self::getEligibilityQuestions(1);
         
         $rules = array(
             "age" => array("R", "N", "VALUE-GREATER[0]", "VALUE-LESS[95]"),
             'weight_main' => array('N', 'R', "VALUE-GREATER[0]"),
             "height_main" => array('N', 'R', "VALUE-GREATER[0]"),
             "high_speed_internet" => array("N", "R"),
             "can_understand_english" => array("N", "R"),
             "passcode" => array("R")
         );
         
         $weight = $form->getRawValue("weight_main");
         $height = $form->getRawValue("height_main");
         
         if((int) $form->getRawValue("weight_unit") == 1){
             $rules["weight_pounds"] = array('N', "VALUE-GREATER-EQ[0]");
             $weight = self::stoneToKg($weight, $form->getRawValue('weight_pounds'));
         }
         
         if((int) $form->getRawValue("height_unit") == 1){
             $rules["height_inches"] = array('N', "VALUE-GREATER-EQ[0]");
             $height = self::feetToMetres($height, $form->getRawValue('height_inches'));
         }
         
         foreach($eligibilityQuestions as $question){
             $rules['question_' . $question->id] = array("R");
         }

         $form->validate($rules);
         

         if(!self::isValidWeight($weight)){
            $form->setError('weight_main', array(
                'general' => 'Please enter a valid weight',
                'main' => 'Please enter a valid weight'
            ));
         }
         
         if(!self::isValidHeight($height)){
             $form->setError('height_main', array(
                 'general' => 'Please enter a valid height',
                 'main' => 'Please enter a valid height'
             ));
         }
         
         
         // Check consent was given
         if((int)$form->getRawValue('consent') !== 1){
             $form->setError('consent', array(
                'main' => 'You must give your consent to continue',
                'general' => 'You must give your consent to continue'
             ));
         }
         
         if($form->hasErrors()){
             return;
         }
         
         // Check the values for the eligibility
         
         if(in_array(strtoupper($form->getRawValue('passcode')), self::$eligibilityPasswords) == false){
             $form->setError('passcode', array(
                 'main' => 'The passcode you have entered is not correct.',
                 'general' => 'The passcode you have entered is not correct.'
             ));
             
             self::$pageData['errors'][] = 'Please enter the passcode from your introduction letter carefully.';
             return;
         }
         
         $eligible = true;
         
         foreach($eligibilityQuestions as $question){
             if($form->getRawValue('question_' . $question->id) !== $question->correct){
                 $eligible = false;
                 break;
             }
         }
         
         if((int)$form->getRawValue("high_speed_internet") !== 1){
             $eligible = false;
         }
         
         if((int)$form->getRawValue("can_understand_english") !== 1){
             $eligible = false;
         }
         
         if((int)$form->getRawValue("age") < 30 || (int)$form->getRawValue("age") > 74){
             $eligible = false;
         }
         
         $bmi = $weight / ($height * $height);
         
         if($bmi < 25){
             $eligible = false;            
         }
         
         $form->setValue('is_eligible', $eligible);
         
         $res = self::logEligibilityData($form);
         
         if(!$eligible){
         
             if($res['hash_id']){
                 wp_redirect(add_query_arg(array(
                     'result' => $res['hash_id']
                 ),
                     self::getIneligiblePagePermalink()
                 ));
                 exit;
             }else{
                 wp_redirect(home_url());
                 exit;
             }
             return;
         }
        
         $_SESSION[self::getOptionKey(self::eligibilityGroupSessionKey)] = $form->getRawValue('passcode');

         wp_redirect(add_query_arg(array(
             'result' => $res['hash_id']
         ),
             self::getEligibilityExercisePagePermailink()
         ));
         exit;
     }

    public function checkEligibilityExercise($form){
        $eligibilityQuestions = self::getEligibilityQuestions(2);

        foreach($eligibilityQuestions as $question){
            $rules['question_' . $question->id] = array("R");
        }

        // Not pretty, but validates the extra data box if yes is selected for "any other reason"
        if((int)$form->getRawValue('question_25') == 1){
            $rules['question-no_physical_activity_reason'] = array("R");
        }

        $form->validate($rules);

        if($form->hasErrors()){
            return;
        }

        $answers = self::getPageData('eligibilityAnswers');

        if(!count($answers)){
            return;
        }

        $eligibilityID = $answers[0]->result_id;
        $eligibilityResult = self::getEligibilityResult($eligibilityID);


        $res = self::logEligibilityExerciseData($eligibilityID, $form);

        $eligible = true;

        foreach($eligibilityQuestions as $question){
            if($form->getRawValue('question_' . $question->id) !== $question->correct){
                $eligible = false;
                break;
            }
        }

        if(!$eligible){
            wp_redirect(add_query_arg(array(
                'result' => $eligibilityResult->hash_id
            ),
                self::getEligibilityDoctorPagePermailink()
            ));

            return;
        }

        $_SESSION[self::getOptionKey(self::eligibilitySessionKey)] = true;
        self::$pageData['eligible'] = true;

        wp_redirect(wp_registration_url());
    }

    public static function logEligibilityExerciseData($eligibilityID, $form){
        global $wpdb;

        $eligibilityQuestions = self::getEligibilityQuestions(2);

        $wpdb->update(
            self::getEligibilityResultTableName(),
            array(
                'no_physical_activity_reason' => $form->getRawValue('question-no_physical_activity_reason')
            ),
            array(
                'id' => $eligibilityID
            )
        );

        foreach($eligibilityQuestions as $question){
            $wpdb->insert(self::getEligibilityResultAnswersTableName(), array(
                'result_id' => $eligibilityID,
                'question_id' => $question->id,
                'answer' => $form->getRawValue("question_" . $question->id)
            ));
        }
    }


    public static function getLastMondayDate(){
        return date('Y-m-d',strtotime('last monday'));
    }

    public static function getExerciseAchievementMessages($user_id){
        $resistanceAchievements = self::getNumberOfResistanceAchievementsForLastWeek($user_id);
        $aerobicMinutes = self::getMinutesOfAerobicAchievementsForLastWeek($user_id);
        $messages = array();

        if($aerobicMinutes >= self::AEROBIC_EXERCISE_ACHIEVEMENT){
            $messages[] = "Completed a combination of <strong>" . self::AEROBIC_EXERCISE_ACHIEVEMENT . " minutes moderate</strong> or <strong>" . (self::AEROBIC_EXERCISE_ACHIEVEMENT/2) . " minutes vigorous</strong> aerobic exercise!";
        }

        if($resistanceAchievements >= self::RESISTANCE_AMOUNT_ACHIEVEMENT){
            $messages[] = "Completed three or more resistance exercises of <strong>arms, legs, or trunk</strong>!";
        }

        return $messages;
    }


     public static function getNumberOfResistanceAchievementsForLastWeek($user_id){
         global $wpdb;
         
         $dateFrom = self::getLastMondayDate();

         $result = $wpdb->get_row(
             $sql = $wpdb->prepare(
                 "SELECT count(*) as resistance_count
                    FROM genwp_genesis_tracker 
	              WHERE exercise_type_resistance IN ('arms','legs','trunk')
                    AND user_id=%d
                    AND measure_date >= {$dateFrom}", $user_id
             )
         );


         return $result->resistance_count;
     }


     public static function getMinutesOfAerobicAchievementsForLastWeek($user_id){
         global $wpdb;

         $dateFrom = self::getLastMondayDate();

         $result = $wpdb->get_row(
            $sql = $wpdb->prepare("
                SELECT IF(ISNULL(type_moderate.total), 0, type_moderate.total) + (IF(ISNULL(type_vigorous.total), 0, type_vigorous.total) * 2) as total  FROM
                         (SELECT SUM(`exercise_minutes`) as total
                    FROM genwp_genesis_tracker
                    WHERE genwp_genesis_tracker.`exercise_type`='moderate' 
                      AND measure_date >= {$dateFrom}
                      AND user_id=%d
                ) as type_moderate,
                (SELECT SUM(`exercise_minutes`) as total
                    FROM genwp_genesis_tracker
                    WHERE genwp_genesis_tracker.`exercise_type`='vigorous'
                      AND measure_date >= {$dateFrom}
                      AND user_id=%d
                ) as type_vigorous", $user_id, $user_id
            )
         );

        return $result->total;
     }
     
     public function getClientIp(){
         $ip = '';
         
         if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
             $ip = $_SERVER['HTTP_CLIENT_IP'];
         } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
             $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
         } else {
             $ip = $_SERVER['REMOTE_ADDR'];
         }
         
         return $ip;
     }
     
     // Insert all the user's data, and return the unique hash key
     public function logEligibilityData(DP_HelperForm $form){
         global $wpdb;
         
         // get the questions to store against
         $ip = self::getClientIp();
         $hash = md5(self::getClientIp() . time() . rand(1, 100000));
         
         $weight = (float)$form->getRawValue('weight_main');
         
         if($form->getRawValue('weight_unit') == 1){
             $weight = self::stoneToKg($weight, $form->getValue('weight_pounds'));
         }  
         
         $height = (float)$form->getRawValue('height_main');
         
         if($form->getRawValue('height_unit') == 1){
             $height = self::feetToMetres($height, $form->getValue('height_inches'));
         }  
         
         $bmi = $weight / ($height * $height);
         $resultId = false;

         
        if($wpdb->insert(self::getEligibilityResultTableName(), array(
            'ip_address' =>  $ip,
            'hash_id' => $hash,
            'unique_id' => self::getEligibilityUniqueId(),
            'is_eligible' => $form->getRawValue('is_eligible'),
            'weight' => $weight,
            'height' => $height,
            'age' => $form->getRawValue('age'),
            'passcode' => strtoupper($form->getRawValue('passcode')),
            'bmi' => $bmi,
            'high_speed_internet' => $form->getRawValue('high_speed_internet'),
            'can_understand_english' => $form->getRawValue('can_understand_english'),
            'date_created' => current_time('Y-m-d H:i:s'),
        ))){
            $resultId = $wpdb->insert_id;
            // Insert the question answers
            $eligibilityQuestions = self::getEligibilityQuestions(1);
            
            foreach($eligibilityQuestions as $question){
                $wpdb->insert(self::getEligibilityResultAnswersTableName(), array(
                    'result_id' => $resultId,
                    'question_id' => $question->id,
                    'answer' => $form->getRawValue("question_" . $question->id)
                ));
            }
        }
        
        
        return array(
            'hash_id' => $hash,
            'result_id' => $resultId
        );
         
     }

    public function getEligibilityUniqueId(){
        global $wpdb;

        do {
            $uniqueId = substr(rand(1000, 99999), 0, 5);
            $res = $wpdb->get_results(
                $sql = $wpdb->prepare(
                    "SELECT * FROM " . self::getEligibilityResultTableName() . " WHERE unique_id=%s",
                    $uniqueId
                )
            );

        } while (count($res) >= 1);

        return $uniqueId;
    }
     
     public function checkLoginWeightEntered($userLogin, $user){
         if(!GenesisTracker::getInitialUserWeight($user->ID)){
             $_SESSION[GenesisTracker::weightEnterSessionKey] = true;
         }else{
             unset($_SESSION[GenesisTracker::weightEnterSessionKey]);
         }
     }

     public static function checkWeightEntered(){
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
                  wp_redirect(self::getUserPagePermalink());
                  exit;
              }
             
             return;
         }
        
         
         if($post && $pageID == $post->ID || self::isOnLogoutPage()){
             return;
         }
        
         wp_redirect(get_permalink($pageID));
         exit;
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

         if(!$user_id){
             return false;
         }
         
         $rules = array(
             'weight_main' => array('N', 'R', "VALUE-GREATER[0]"),
         );


         
         $unit     = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL ? self::UNIT_IMPERIAL : self::UNIT_METRIC;
         $imperial = $form->getRawValue('weight_unit') == self::UNIT_IMPERIAL;
         
         // If we're doing imperial, validate pounds too.
         if($imperial){
             $rules['weight_pounds'] = array("N", "VALUE-GREATER-EQ[0]");
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
             
             GenesisTracker::setUserData($user_id, self::userStartWeightCol, $weight);

             add_user_meta($user_id, self::getOptionKey(self::userInitialUnitSelectionKey), $unit, true);
             
             self::$pageData['weight-save'] = true;
             unset($_SESSION[GenesisTracker::weightEnterSessionKey]);
         }
     }
     
     public static function makeValidWeight($weight){
         $weight = (float)$weight;
         return min(max(self::MIN_VALID_WEIGHT, $weight), self::MAX_VALID_WEIGHT);
     }
     
     public static function isValidWeight($weight){
         $weight = (float)$weight;
         return $weight >= self::MIN_VALID_WEIGHT && $weight <= self::MAX_VALID_WEIGHT;
     }
     
     public static function isValidHeight($height){
         $height = (float)$height;
         return $height >= self::MIN_VALID_HEIGHT && $height <= self::MAX_VALID_HEIGHT;
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
         
         if(self::isOnEligibilityPage()){
             $formName = 'eligibility';
             self::eligibilityPageAction();
         }

         if(self::isOnEligibilityExercisePage()){
             $formName = 'eligibility_exercise';
             self::eligibilityExercisePageAction();
         }
         
         if(self::isOnTargetPage()){
             $formName = 'tracker';
             self::targetPageAction();
         }
         
         if(self::isOnEnterWeightPage()){
             $formName = 'initial-weight';
             self::enterWeightPageAction();
         }

         if(self::isOnEligibilityDoctorDownloadPage()){
             self::eligibilityDoctorDownloadPageAction();
         }
         
         if($formName && count(self::$pageData['errors']) == 0 &&  $form = DP_HelperForm::getForm($formName)){
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
             $rules['exercise_minutes'] = array('N', 'R', 'VALUE-GREATER-EQ[0]', 'VALUE-LESS-EQ[960]');
             $rules['exercise_minutes_resistance'] = array('N', 'R', 'VALUE-GREATER-EQ[0]', 'VALUE-LESS-EQ[960]');
             $rules['exercise_type'] = array('R');
             $rules['exercise_type_resistance'] = array('R');
         }
         
         if($form->getRawValue('record-food')){
             foreach(self::$_userMetaTargetFields as $targetKey => $target){
                 foreach(self::$_userTargetTimes as $timeKey => $time){
                     $rules[$timeKey . "_" . $targetKey] = array('N', 'R', 'VALUE-GREATER-EQ[0]', 'VALUE-LESS-EQ[200]');
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
         
         if($logDate < strtotime(self::getInitialUserStartDate(get_current_user_id()))){
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
             if((float)$form->getRawValue('exercise_minutes') > 0){
                 $data['exercise_minutes'] = (float)$form->getRawValue('exercise_minutes');
                 $data['exercise_type'] = $form->getRawValue('exercise_type');
                 $data['exercise_description'] = $form->getRawValue('exercise_description');
             }
             
             if((float)$form->getRawValue('exercise_minutes_resistance') > 0){
                 $data['exercise_minutes_resistance'] = (float)$form->getRawValue('exercise_minutes_resistance');
                 $data['exercise_type_resistance'] = $form->getRawValue('exercise_type_resistance');
                 $data['exercise_description_resistance'] = $form->getRawValue('exercise_description_resistance');
             }

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
             
             // Remove food descriptions
             $wpdb->query(
                 $sql = $wpdb->prepare("DELETE FROM " . self::getFoodDescriptionTableName() . "
                     WHERE tracker_id = %d", $prevResult->tracker_id
                 )
             );
         
         }  
         
         
         
         
         // Remove current entry
         $wpdb->query(
             $wpdb->prepare('DELETE FROM ' . self::getTrackerTableName() . ' WHERE user_id=%d AND measure_date=%s', get_current_user_id(), $date)
         );

         if($form->hasValue('record-food')){
             $data['food_log_explanation'] = $form->getRawValue('food_log_explanation');
         }
        
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
            
            // Save the food descriptions
            foreach(self::$_userTargetTimes as $timeKey => $time){
                if(!$form->hasValue($timeKey . "_description")){
                    continue;
                }
                
                $wpdb->insert(self::getFoodDescriptionTableName(), 
                    array(
                        'tracker_id' => $trackerId,
                        'time' => $timeKey,
                        'description' => trim($form->getRawValue($timeKey . "_description"))
                    )
                );
            }
        }      
         
         self::clearCachedUserData(get_current_user_id());
         self::$pageData['user-input-save'] = true;
     }
     
     public static function getUserFoodDescriptionsForTracker($tracker_id){
          global $wpdb;
          
          $res = $wpdb->get_results($sql = $wpdb->prepare("SELECT fd.*
              FROM " . self::getFoodDescriptionTableName() . " fd
              JOIN " . self::getTrackerTableName() . " t 
                ON fd.tracker_id = t.tracker_id
                AND t.tracker_id = %d", $tracker_id)    
          );
          
          return $res;
     }
     
     public static function getUserFoodLogsForTracker($tracker_id){
         global $wpdb;
         
         $res = $wpdb->get_results($sql = $wpdb->prepare("SELECT fl.* 
             FROM " . self::getFoodLogTableName() . " fl
             JOIN " . self::getTrackerTableName() . " t 
                 ON fl.tracker_id = t.tracker_id
                 AND t.tracker_id = %d", $tracker_id)
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

     public static function getIneligibleSurveyPagePermalink(){
         return get_permalink(self::getOption(self::ineligibleSurveyPageId));
     }
     
     public static function getUserPagePermalink(){
         return get_permalink(self::getOption(self::userPageId));
     }
     public static function getUserInputPagePermalink(){
         return get_permalink(self::getOption(self::inputProgressPageId));
     }
     
     public static function getEligibilityPagePermailink(){
         return get_permalink(self::getOption(self::eligibilityPageId));
     }

    public static function getEligibilityExercisePagePermailink(){
        return get_permalink(self::getOption(self::eligibilityExercisePageId));
    }

    public static function getEligibilityDoctorPagePermailink(){
        return get_permalink(self::getOption(self::eligibilityDoctorPageId));
    }

    public static function getEligibilityDoctorDownloadPagePermailink(){
        return get_permalink(self::getOption(self::eligibilityDoctorDownloadPageId));
    }
     
     public static function getIneligiblePagePermalink(){
         return get_permalink(self::getOption(self::ineligiblePageId));
     }
     
     public static function getPrescriptionPagePermalink(){
         return get_permalink(self::getOption(self::prescriptionPageId));
     }
     
     public static function getPhysiotecPagePermalink(){
         return get_permalink(self::getOption(self::physiotecLoginPageId));
     }
     
     public static function isOnEnterWeightPage(){
        return self::isOnPage(self::initialWeightPageId); 
     }
     
     public static function isOnUserPage(){
         return self::isOnPage(self::userPageId);
     }
     
     public static function isOnTargetPage(){
         return self::isOnPage(self::targetPageId);
     }
     
     public static function isOnEligibilityPage(){
          return self::isOnPage(self::eligibilityPageId);
     }

    public static function isOnEligibilityExercisePage(){
        return self::isOnPage(self::eligibilityExercisePageId);
    }

    public static function isOnEligibilityDoctorPage(){
        return self::isOnPage(self::eligibilityDoctorPageId);
    }

    public static function isOnEligibilityDoctorDownloadPage(){
        return self::isOnPage(self::eligibilityDoctorDownloadPageId);
    }
     
     public static function isOnInEligiblePage(){
          return self::isOnPage(self::ineligiblePageId);
     }

     public static function isOnIneligibleSurveyPage(){
         return self::isOnPage(self::ineligibleSurveyPageId);
     }
     
     public static function isOnPrescriptionPage(){
         return self::isOnPage(self::prescriptionPageId);
     }
     
     public static function isOnPhysiotecLoginPage(){
         return self::isOnPage(self::physiotecLoginPageId);
     }
     
     public static function isOnUserInputPage(){
         return self::isOnPage(self::inputProgressPageId);
     }
     
     public static function isOnPage($pageCode){
          global $post;

          if(!$post){
              return false;
          }
        
          if(self::getOption($pageCode) == $post->ID){
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
         return GenesisTracker::getUserData($user_id, self::userStartWeightCol);
     }
     
     public static function getInitialUserStartDate($user_id){
         return GenesisTracker::getUserData($user_id, self::userStartDateCol);
     }

    public static function getUserStudyGroup($user_id){
        return GenesisTracker::getUserData($user_id, self::studyGroupCol);
    }

    public static function getShowMed($user_id){
        return GenesisTracker::getUserData($user_id, self::showMedCol);
    }

    public static function getUserGender($user_id){
        return GenesisTracker::getUserData($user_id, self::genderCol);
    }
     
     public static function isUserSixMonths($user_id){        
        return (bool) get_user_meta($user_id, self::getOptionKey(self::sixMonthDateKey), true);
     }
     
     public function getUserSixMonthWeight($user_id){
         return self::getUserData($user_id, self::sixMonthWeightCol);
     }
     
     public function getUserFourWeekleyEmailDate($user_id){
         return self::getUserData($user_id, self::fourWeekleyEmailDateCol);
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
    
    public static function getTotalFoodLogs($user_id, $limit = 7){
        global $wpdb;
        // Build the aggregates for each value we want to pull out
        $aggregates = array();
        $nonZeros = array();

        foreach(self::$_userMetaTargetFields as $targetKey => $targetVal){
            $aggregates[] = sprintf("SUM(CASE WHEN fl.`food_type` = '%s' THEN fl.`value` ELSE NULL END) as %s", $targetKey, $targetKey);
            $nonZeros[] = sprintf("%s > 0 ", $targetKey);
        }
        
         $results = $wpdb->get_results($sql = $wpdb->prepare(
             $select = "SELECT t.* " .
               ($aggregates ? "," . implode(",\n", $aggregates) . " " : "") . 
               "FROM " . self::getTrackerTableName() . " t " . 
               "JOIN " . self::getFoodLogTableName() . " fl USING(tracker_id)
             WHERE user_id=%d 
               GROUP BY t.tracker_id
               HAVING (" . (implode(" OR ", $nonZeros)) . ") 
               ORDER BY measure_date DESC 
               LIMIT %d", $user_id, $limit
         ));
         
         return $results;
    }
     
     public static function getAllUserLogs($user_id, $startDate ='', $endDate = ''){
         global $wpdb;
         
         $weightQ = '';
         $dateConstraint = '';
        
         
         if($startWeight = self::getInitialUserWeight($user_id)){
             $weightQ = ", ($startWeight - weight) as weight_loss ";
         }

         // Always provide a from date, whichever's greater - the user start date or the passed in date
         $userStartDate = self::getInitialUserStartDate($user_id);

         if($startDate){
             $startDateTimeStamp = strtotime($startDate);
             $userStartDateTimeStamp = strtoTime($userStartDate);

             if($userStartDateTimeStamp > $startDateTimeStamp){
                 $startDate = $userStartDate;
             }
         }else{
             $startDate = $userStartDate;
         }

         $dateConstraint = "AND measure_date >= '$startDate'";
        
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


         $date = new DateTime(self::getInitialUserStartDate($user_id));
         $date->modify("- 1 day");
         
         $start = new stdClass();
         $start->user_id = $user_id;
         // Set the initial weight as the day before the start date
         $start->measure_date = $date->format('Y-m-d H:i:s');
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
         $userStartDate = self::getInitialUserStartDate($user_id);
         $userStartDateTimestamp = strtotime($userStartDate);

         // Day Length *= 1000
         $dayLength = 86400000;

         $weightInitial = array();
         // Get the user's start weight in imperial and metric
         $weightInitial['initial_weight'] = self::getInitialUserWeight($user_id);
         $weightInitial['initial_weight_imperial'] = self::kgToPounds($weightInitial['initial_weight']);
         
         $valsToCollate = array(
             'weight',
             'exercise_minutes',
             'exercise_minutes_resistance',
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
                     if(!property_exists($log, $valToCollate) || $log->$valToCollate === null){
                         continue;
                     }
                 
                 
                     if(!isset($collated[$valToCollate]['yMin']) || $collated[$valToCollate]['yMin'] > $log->$valToCollate){
                         $collated[$valToCollate]['yMin'] = $log->$valToCollate;
                     }
         
                     if(!isset($collated[$valToCollate]['yMax']) || $collated[$valToCollate]['yMax'] < $log->$valToCollate){
                         $collated[$valToCollate]['yMax'] = $log->$valToCollate;
                     }
                 
                     $collated[$valToCollate]['timestamps'][] = $timestamp;

                     $additionalData = array();

                     if($valToCollate == 'exercise_minutes'){
                         $additionalData['type'] = $log->exercise_type;
                         $additionalData['description'] = $log->exercise_description;

                         if(isset(self::$_exerciseTypes[$log->exercise_type])){
                             $additionalData['label'] = self::$_exerciseTypes[$log->exercise_type]['name'];
                             $additionalData['color'] = self::$_exerciseTypes[$log->exercise_type]['color'];
                         }
                     }

                     if($valToCollate == 'exercise_minutes_resistance'){
                         $additionalData['type'] = $log->exercise_type_resistance;

                         if(isset(self::$_exerciseTypesResistance[$log->exercise_type_resistance])){
                             $additionalData['label'] = self::$_exerciseTypesResistance[$log->exercise_type_resistance]['name'];
                             $additionalData['color'] = self::$_exerciseTypesResistance[$log->exercise_type_resistance]['color'];
                         }
                         
                         $additionalData['description'] = $log->exercise_description_resistance;
                     }
                
                     $collated[$valToCollate]['data'][] = array(
                         $timestamp, $log->$valToCollate, $additionalData
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
     
     public static function getCachePath(){
         return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . self::CACHE_DIR . DIRECTORY_SEPARATOR;
     }
     
     public static function setCacheData($key, $data, $expire = 0){
         if(!file_exists(self::getCachePath())){
             mkdir(self::getCachePath(), 0775, true);
         }
         
         $encKey = base64_encode($key);
         
         // Save the cache
         file_put_contents(self::getCachePath() . $encKey, serialize($data));
         
         if($expire){
             file_put_contents(self::getCachePath() . $encKey . "_expire", time() + $expire);
         }elseif(file_exists(self::getCachePath() . $encKey . "_expire")){
             unlink(self::getCachePath() . $encKey . "_expire");
         }
     }
     
     public static function clearCacheData($key){
         $encKey = base64_encode($key);
         
         if(file_exists(self::getCachePath() . $encKey . "_expire")){
             unlink(self::getCachePath() . $encKey . "_expire");
         }
         
         if(file_exists(self::getCachePath() . $encKey)){
             unlink(self::getCachePath() . $encKey);
         }
     }
     
     public static function getCacheData($key){
         if(!self::CACHE_ENABLED){
             return null;
         }

         if(!file_exists(self::getCachePath() . base64_encode($key))){
             return null;
         }
         
         if(file_exists(self::getCachePath() . base64_encode($key) . "_expire")){
             $expire = (int) file_get_contents(self::getCachePath() . base64_encode($key) . "_expire");

             if($expire <= time()){
                 return null;
             }
         }
         
         return unserialize(file_get_contents(self::getCachePath() . base64_encode($key)));
     }
     
     /*
         This needs testing on large datasets
         First, we get all user data and fill in the averages for each day inbetween, this could be expensive on its own.
         Then we key that data by date, then merge it into an array of all values using the date as key.  
         Then we average each value for the date.

        $endDateRanges contain maximum dates which to get values for each type for, the points array will be capped using
        this.
     */
     public static function getAverageUsersGraphData($alignToStartDate = null, $endDateRanges = null){
         // Update to include admins
        $averages = self::getCacheData(self::getOptionKey(self::averageDataKey));

        if($averages === null){
            $averages = self::generateAverageUsersGraphData(self::INCLUDE_ADMIN_USERS_IN_AVERAGES == false);
        }
        
        if(!$averages){
            return;
        }

         $dayLength = 86400;



         // Change the indexed data points to date keys for the user ID
         if($alignToStartDate && ($alignToStartDateTimestamp = strtotime($alignToStartDate))){


             foreach($averages as $type => &$points){
                 $endTime = null;

                 if($type == 'weight_loss_imperial'){
                     $maxKey = 'weight_loss_max';
                 }else{
                     $maxKey = $type . "_max";
                 }

                 if($endDateRanges->$maxKey){
                     $endTime = strtotime($endDateRanges->$maxKey);
                 }elseif($endDateRanges->maxDate){
                     $endTime = strtotime($endDateRanges->maxDate);
                 }

                 $pointCount = 0;

                 foreach($points['data'] as $index => &$point){
                     $timestamp = ($alignToStartDateTimestamp + ($index * $dayLength));

                     // Cap this type at the endTime if we have one
                     if($endTime !== null && $timestamp > $endTime){
                         array_splice($points['data'], $pointCount);
                         break;
                     }

                     $point[0] = $timestamp * 1000;
                     $pointCount++;
                 }
             }
         }

         // Trim the data so we only have between the dates we need
         // This used to be done in the method which now caches all user data
         foreach($averages as $averageKey => &$averageData){
             unset($averageData['yMin']);
             unset($averageData['yMax']);

             foreach($averageData['data'] as $dataKey => &$dataPoint){
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


                 // Average using an index as the key, rather than using the dates.
                 // This way, we align each user's start date
                 $index = 0;

                 foreach($measurementSet['data'] as $date => $measurement){
                     if(!isset($structure[$key][$index])){
                         $structure[$key][$index] = array();
                     }
                     $structure[$key][$index][] = $measurement;

                     $index++;
                 }
             }
         }

         
         // Now average them!
         $averages = array();
         
         foreach($structure as $key => $items){
              
             foreach($items as $item => $measurements){
                 
                 $avg = array_sum($measurements) / count($measurements);
                 
                 // if(isset($averages[$key]['yMin'])){
 //                      $averages[$key]['yMin'] = min($averages[$key]['yMin'], $avg);
 //                      $averages[$key]['yMax'] = max($averages[$key]['yMax'], $avg);
 //                  }else{
 //                      $averages[$key]['yMin'] = $avg;
 //                      $averages[$key]['yMax'] = $avg;
 //                  }
                 
                 $averages[$key]['data'][] = array($item, $avg);
             }


         }
         
         self::setCacheData(self::getOptionKey(self::averageDataKey), $averages, 86400);
         
         // mail("dave_preece@mac.com", "Regenerated Cache", "Regenerated");
         
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
         $foodDescription = array();
         $autofillFoods = self::getAutofillFoods($user_id);
         
         if($measureDetails->tracker_id){
             $foodData = self::getUserFoodLogsForTracker($measureDetails->tracker_id);
             $foodDescription = self::getUserFoodDescriptionsForTracker($measureDetails->tracker_id);
         }

         // Get previously entered foods for
         
         return array(
             "date_picker" =>self::getDateListPicker($day, $month, $year),
             "measure_details" => $measureDetails,
             "food_log" => $foodData,
             "food_descriptions" => $foodDescription,
             "autofill_foods" => $autofillFoods
        );
     }


    public static function getAutofillFoods($user_id){
        global $wpdb;

        $foodLogTableName = self::getFoodLogTableName();
        $foodColumns = "";
        $foodJoins = "";
        $outerColumns = "value, time, " . implode(",", array_keys(self::$_userMetaTargetFields));
        $collated = array();

        foreach(self::$_userMetaTargetFields as $target => $value){
            $foodColumns .= ($foodColumns ? "," : "") . " food_log_{$target}.value as {$target}";
            $foodJoins .= " LEFT JOIN {$foodLogTableName} food_log_{$target}
            ON description.tracker_id = food_log_{$target}.tracker_id
                AND food_log_{$target}.time = description.time
                AND food_log_{$target}.food_type = '{$target}'";
        }

        $results = $wpdb->get_results(
            $sql = $wpdb->prepare( "SELECT DISTINCT {$outerColumns} FROM
              (SELECT DISTINCT description.`time`, description.description as value, {$foodColumns}
                  FROM " . self::getTrackerTableName() . " tracker
                JOIN  " . self::getFoodDescriptionTableName() . " description
                    ON description.tracker_id = tracker.tracker_id 
                	AND description <> ''
                {$foodJoins}
                WHERE tracker.user_id=%d
                ORDER BY time, tracker.`tracker_id` DESC) as orderer", $user_id
            )
        );

        // Only use the latest entry from any description so that we don't get duplicates with different values
        $usedEntries = array();

        foreach($results as $res){
            if(!isset($collated[$res->time])){
                $collated[$res->time] = array();
                $usedEntries[$res->time] = array();
            }

            if(!in_array($res->value, $usedEntries[$res->time])) {
                $collated[$res->time][] = $res;
                $usedEntries[$res->time][] = $res->value;
            }
        }

        return $collated;
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
         || self::isOnEligibilityPage()
         || self::isOnPhysiotecLoginPage()
         || self::isOnPrescriptionPage()
         || apply_filters('hide-header-notice', false)){
             $classes[] = 'hide-header-notice';
         }
         
         if(self::isOnEnterWeightPage()){
             $classes[] = 'enter-weight-page';
         }
         
         return $classes;
     }
     
     public static function addHeaderElements(){
         $user_id = get_current_user_id();

         // Do any redirects first - require a hash for exercise questions or ineligible
         if(self::isOnInEligiblePage() || self::isOnEligibilityExercisePage() ||
             self::isOnEligibilityDoctorPage() || self::isOnIneligibleSurveyPage()){
             // Check we've got a hash
             if(isset($_GET['result'])){
                 // Get the result answers data based on the hash
                 $answers = self::getEligibilityAnswersForResultHash($_GET['result']);

                 if(!$answers){
                     wp_redirect(home_url());
                     exit;
                 }

                 self::getEligibilityResult($answers[0]->result_id);
                 
                 self::$pageData['eligibilityAnswers'] = $answers;
                 self::$pageData['eligibilityResult'] = self::getEligibilityResult($answers[0]->result_id);
             }else{
                 wp_redirect(home_url());
                 exit;
             }
         }
         
         if(self::isOnUserPage()){
             $userId = get_current_user_id();

             add_action( 'wp_head', function() {
                echo '<!--[if lt IE 9]><script src="' . plugins_url("js/excanvas.min.js", __FILE__) . '"></script><![endif]-->';
             });

              wp_enqueue_script('flot', plugins_url('js/jquery.flot.min.js', __FILE__), array('jquery'));
              wp_enqueue_script('flot-time', plugins_url('js/jquery.flot.time.min.js', __FILE__), array('flot'));
              wp_enqueue_script('flot-navigate', plugins_url('js/jquery.flot.navigate.min.js', __FILE__), array('flot'));
              wp_enqueue_script('user-graph', plugins_url('js/UserGraph.js', __FILE__), array('flot-navigate'));
             
              wp_localize_script('flot', 'userGraphData', self::getUserGraphData(get_current_user_id()));

              $startDate = self::getInitialUserStartDate($userId);
              $dateRanges = self::getUserDateRange($userId);

              wp_localize_script('flot', 'averageUserGraphData', self::getAverageUsersGraphData($startDate, $dateRanges));
              
               wp_enqueue_script('responsive-tables', plugins_url('js/responsive-tables.js', __FILE__));
               wp_enqueue_style('responsive-tables-css', plugins_url('css/responsive-tables.css', __FILE__));
         }
              
        wp_register_script( "progress", plugins_url('js/script.js', __FILE__), array( 
             'jquery'  
        ));



         if(self::isOnUserPage() || self::isOnUserInputPage() || self::isOnTargetPage() || self::isOnEnterWeightPage() || self::isOnEligibilityPage()){    
           
            
            wp_localize_script( 'progress', 'myAjax', array( 
                'ajaxurl' => admin_url('admin-ajax.php')
            ));
            
            
            // Don't set the initial user unit in the case of a posted form - allow the form to use what was posted
            if(!DP_HelperForm::wasPosted() && (int) self::getInitialUserUnit($user_id)){
                wp_localize_script('progress', 'initialUserUnit', self::getInitialUserUnit($user_id));     
            }
            
            if(self::isOnEligibilityPage()){
                wp_register_script("eligibility", plugins_url('js/eligibility.js', __FILE__), array(
                   'progress' 
                ));
                
                wp_enqueue_script('eligibility');
            }
            
            
           
        }
        wp_enqueue_script('progress');
        
        if(self::isOnUserInputPage()){
            $minDate = strtotime(self::getInitialUserStartDate($user_id));

            wp_localize_script('progress', 'datePickerMin', array(
                "day" => date("j", $minDate),
                "month" => date("n", $minDate),
                "year" => date("Y", $minDate)
            ));
        }
     }
     
     public static function decideAuthRedirect(){
         if(is_user_logged_in()){
             // If on the eligibility pages, redirect to the homepage
             if(self::isOnEligibilityPage() || self::isOnInEligiblePage()){
                 wp_redirect(home_url());
             }
             
             return false;
         }

         if(self::isOnUserPage() || self::isOnUserInputPage() 
         || self::isOnTargetPage() || self::isOnPrescriptionPage()){
            auth_redirect();    
         }
     }
     
     public static function ajaxRequest($data){
         var_dump($data);
         exit;
     }
     
     // An alternative to using WP's user meta tables as when joining, they're sloooow
     public static function getUserData($user_id, $key = null){
         global $wpdb;
         
         $result = $wpdb->get_row(
             $wpdb->prepare("SELECT * FROM ". self::getUserDataTableName() . 
                 " WHERE user_id=%d", $user_id
             ), ARRAY_A
         );
         
         if(!$key){
             return $result;
         }
         
         if(!isset($result[$key])){
             return null;
         }

         return $result[$key];
     }
    
    public static function doSiteUrlChanges( $url, $path, $scheme, $blog_id ){
        // Adapt the URL for the registration form
        if(self::isOnDoctorEligibilityRegistrationPage()){
            if(strpos($url, self::REGISTER_URL) !== false){
                return $url . "&" . self::DOCTOR_ELIGIBILITY_GET_PARAM . "=1";
            }

        }

        return $url;
    }
     
     // Use this instead of WP's meta fields for the following values:
     // start_weight, account_active, passcode_group, user_contacted, withdrawn, notes,
     // six_month_weight, red_flag_email_date, four_weekly_date, 
     // six_month_date, start_date, six_month_email_opt_out
     public static function setUserData($user_id, $key, $value){
         global $wpdb;
         // Check the user has an entry
         $result = $wpdb->get_row(
             $wpdb->prepare(
                 "SELECT * FROM " . self::getUserDataTableName() . " WHERE
                     user_id=%d", $user_id
             )
         );
             
         
         if($result){
             $wpdb->update(
                 self::getUserDataTableName(),
                 array($key => $value),
                 array('user_id' => $user_id)
             );
             
         }else{
             $wpdb->insert(
                 self::getUserDataTableName(),
                 array(
                     'user_id' => $user_id,
                     $key => $value
                 )
             );
         }
         
         self::clearCachedUserData($user_id);
     }
     
     public static function clearCachedUserData($user_id){
         self::clearCacheData(self::userDataCacheKey);
         self::clearCacheData(self::userDataCacheKey . '-' . $user_id);
         
         foreach(GenesisUserTable::get_sortable_columns() as $k => $arr){
             self::clearCacheData(self::userDataCacheKey . '-sb-' . $arr[0]);
             self::clearCacheData(self::userDataCacheKey . '-sb-' . $arr[0] . ' DESC');
             self::clearCacheData(self::userDataCacheKey . '-sb-' . $arr[0] . ' ASC');

             // var_dump(self::userDataCacheKey . '-sb-' . $arr[0] . ' DESC');
             // if('genesis_admin_user_data-sb-measure_date DESC' == self::userDataCacheKey . '-sb-' . $arr[0] . ' DESC'){
   //
   //           }
         }
     }
     
     // Part of the set up.  Adds the user page into the database
     public static function createUserPage($overwrite = false){
         $pageID = self::getOption(self::userPageId);
         $post = get_post($pageID);
             
         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }
         
         // Creates the page which displays the graph information

         $pageData = array(
            'post_title' => 'Progress',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::userPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
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

         $pageData = array(
            'post_title' => 'Input Your Progress',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::inputProgressPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
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
         

         $pageData = array(
            'post_title' => 'Set a weight target',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::targetPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );
         

         if($pageID){
             wp_delete_post($pageID, true);
         }
         
          $post_id = wp_insert_post($pageData);
          self::updateOption(self::targetPageId, $post_id);
     } 
     
     public static function createEligibilityPage($overwrite = false){
         // Create the page which allows users to enter a target weight and date
         $pageID = self::getOption(self::eligibilityPageId);
          $post = get_post($pageID);

         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }

         $pageData = array(
            'post_title' => 'Check Your Eligibility',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::eligibilityPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );

         if($pageID){
             wp_delete_post($pageID, true);
         }

          $post_id = wp_insert_post($pageData);
          self::updateOption(self::eligibilityPageId, $post_id);
     }

    public static function createEligibilityDoctorDownloadPage($overwrite = false){
        /* Exercise Eligibility */

        $pageId = self::getOption(self::eligibilityDoctorDownloadPageId);
        $post = get_post($pageId);

        if($post && $post->post_status == 'publish'  &! $overwrite){
            return;
        }

        // This page is only created for routing, the content will never get that far
        $pageData = array(
            'post_title' => 'Check Your Eligibility - Doctor Consent Form',
            'comment_status' => 'closed',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => self::userIdForAutoCreatedPages
        );

        if($pageId){
            wp_delete_post($pageId, true);
        }

        $post_id = wp_insert_post($pageData);
        self::updateOption(self::eligibilityDoctorDownloadPageId, $post_id);
    }

    public static function createEligibilityExercisePage($overwrite = false){
    /* Exercise Eligibility */

    $exercisePageID = self::getOption(self::eligibilityExercisePageId);
    $post = get_post($exercisePageID);

    if($post && $post->post_status == 'publish'  &! $overwrite){
        return;
    }

    $pageData = array(
        'post_title' => 'Check Your Eligibility - Exercise',
        'comment_status' => 'closed',
        'post_content' => '[' . self::getOptionKey(self::eligibilityExercisePageId) . ']',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => self::userIdForAutoCreatedPages
    );

    if($exercisePageID){
        wp_delete_post($exercisePageID, true);
    }

    $post_id = wp_insert_post($pageData);
    self::updateOption(self::eligibilityExercisePageId, $post_id);
}

    public static function createIneligibleSurveyPage($overwrite = false){
        /* Exercise Eligibility */

        $pageId = self::getOption(self::ineligibleSurveyPageId);
        $post = get_post($pageId);

        if($post && $post->post_status == 'publish'  &! $overwrite){
            return;
        }

        $pageData = array(
            'post_title' => 'Check Your Eligibility - Survey',
            'comment_status' => 'closed',
            'post_content' => '[' . self::getOptionKey(self::ineligibleSurveyPageId) . ']',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => self::userIdForAutoCreatedPages
        );

        if($pageId){
            wp_delete_post($pageId, true);
        }

        $post_id = wp_insert_post($pageData);
        self::updateOption(self::ineligibleSurveyPageId, $post_id);
    }

    public static function createEligibilityDoctorPage($overwrite = false){
        /* Exercise Eligibility */

        $pageID = self::getOption(self::eligibilityDoctorPageId);
        $post = get_post($pageID);

        if($post && $post->post_status == 'publish'  &! $overwrite){
            return;
        }

        $pageData = array(
            'post_title' => 'Check Your Eligibility - Doctor\'s Consent',
            'comment_status' => 'closed',
            'post_content' => '[' . self::getOptionKey(self::eligibilityDoctorPageId) . ']',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => self::userIdForAutoCreatedPages
        );

        if($pageID){
            wp_delete_post($pageID, true);
        }

        $post_id = wp_insert_post($pageData);
        self::updateOption(self::eligibilityDoctorPageId, $post_id);
    }


     
     public static function createPhysiotecLoginPage($overwrite = false){
    
         // Create the page which allows users to enter a target weight and date
         $pageID = self::getOption(self::physiotecLoginPageId);
          $post = get_post($pageID);
    
         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }
         
         
         $pageData = array(
            'post_title' => 'Physiotec Login',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::physiotecLoginPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );
 
         if($pageID){
             wp_delete_post($pageID, true);
         }
         
          $post_id = wp_insert_post($pageData);
          self::updateOption(self::physiotecLoginPageId, $post_id);
     }
     
     public static function createPrescriptionPage($overwrite = false){
         // Create the page which allows users to enter a target weight and date
         $pageID = self::getOption(self::prescriptionPageId);
          $post = get_post($pageID);
    
         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }
         
         $pageData = array(
            'post_title' => 'Prescription Exercises',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::prescriptionPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );

         if($pageID){
             wp_delete_post($pageID, true);
         }
         
          $post_id = wp_insert_post($pageData);
          self::updateOption(self::prescriptionPageId, $post_id);
     }
     
     public static function createIneligiblePage($overwrite = false){
         // Create the page which allows users to enter a target weight and date
         $pageID = self::getOption(self::ineligiblePageId);
          $post = get_post($pageID);
    
         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }
         
         $pageData = array(
            'post_title' => 'Thank You',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::ineligiblePageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );

         if($pageID){
             wp_delete_post($pageID, true);
         }
         
          $post_id = wp_insert_post($pageData);
          self::updateOption(self::ineligiblePageId, $post_id);
     }
     
     public static function createInitialWeightPage($overwrite = false){
         // Create the page which allows users to enter a target weight and date
         $pageID = self::getOption(self::initialWeightPageId);
          $post = get_post($pageID);
        
         if($post && $post->post_status == 'publish'  &! $overwrite){
             return;
         }

         $pageData = array(
            'post_title' => 'Enter Your Initial Weight',
             'comment_status' => 'closed',
              'post_content' => '[' . self::getOptionKey(self::initialWeightPageId) . ']',
              'post_status' => 'publish',
              'post_type' => 'page',
              'post_author' => self::userIdForAutoCreatedPages
          );

         if($pageID){
             wp_delete_post($pageID, true);
         }
         
          $post_id = wp_insert_post($pageData);
          self::updateOption(self::initialWeightPageId, $post_id);
     }
     
     public static function getTemplateContents($name){
         $templatePath = plugin_dir_path( __FILE__ ) . "template" . DIRECTORY_SEPARATOR . $name . ".html";
         return file_get_contents($templatePath);
     }
     
     public static function getEmailHeaders(){
          $headers = array();
          $headers[] = 'From: Family History Lifestyle Study <'. get_option('admin_email') .'>';
          $headers[] = 'MIME-Version: 1.0';
          $headers[] = 'Content-type: text/html; charset=utf-8';
        
        return $headers;
     }
     
     public static function getLogoUrl(){
         return plugins_url('images/genesis-logo@2x.png', __FILE__);
     }
     
     public static function sendFourWeeklyEmail($userId, $type, $manualMode = false){
         global $wpdb;
         
         if(!in_array($type, array_keys(GenesisAdmin::getFourWeekEmailTypes()))){
             return array(
                 'message' => 'Invalid type'
             );
         }
         
         if(!$user = get_userdata($userId)){
             return array(
                 'message' => 'Invalid user'
             );
         }
         
        $userDetails = GenesisAdmin::getUserLogDetails(null, $userId, $manualMode);
        
        if(!in_array($userDetails['weeks_registered'], self::$_fourWeekPoints)){
            return array(
                'message' => 'This user has not been registered for a correct four weekly point'
            );
        }
         
        $uploadsDir = wp_upload_dir();
        $body = self::getTemplateContents('four-weekly-' . strtolower($type));
        
        $body = str_replace(array(
            '%genesis_logo%',
            '%user_nicename%',
            '%contact_email%',
            '%healthy_weight_range_link%',
            '%keeping_the_weight_off_link%',
            '%hints_and_tips_link%',
            '%diet_day_link%',
            '%med_day_link%',
            '%newsletters_link%',
            
        ),
        array(
            self::getLogoUrl(),
            $user->user_firstname,
            self::alternateContactEmail,
            site_url('your-profile'),
            $uploadsDir['url'] . '2015/06/PROCAS-Lifestyle-The-2-Day-Diet-Keeping-weight-off-V1-27.5.15.pdf',
            site_url('faq'),
            site_url('2-day-recipes'),
            site_url('mediterranean-recipes'),
            site_url('newsletters')
        ), $body);
        
         if(wp_mail($user->user_email, 'Procas Lifestyle Week ' . $userDetails['weeks_registered'] . ' feedback', $body, self::getEmailHeaders())){
            // Mark user's account
            self::setUserData($user->ID, self::fourWeekleyEmailDateCol, current_time('Y-m-d H:i:s'));
            
            $wpdb->insert(self::getFourWeekEmailLogTableName(), array(
                'user_id' => $userId,
                'type' => $type,
                'log_date' => current_time('Y-m-d H:i:s'),
                'week' => $userDetails['weeks_registered'],
                'send_type' => $manualMode ? self::FOUR_WEEK_SEND_TYPE_MANUAL : self::FOUR_WEEK_SEND_TYPE_AUTOMATIC
            ));

             self::clearCachedUserData($userId);

            return true;
        }else{
        
            return array(
                'message' => 'The email failed to send'
            );
        }
        
     }
     
     public static function sendRedFlagEmail($userId, $manualMode = false){
         
         if($userDetails = GenesisAdmin::getUserLogDetails(null, $userId, $manualMode)){
             if($userDetails['six_month_benchmark_change_email_check'] >= 1){
                 $uploadsDir = wp_upload_dir();
                $user = get_userdata($userId);
                $body = self::getTemplateContents('red-flag');
                
                $body = str_replace(array(
                    '%genesis_logo%',
                    '%user_nicename%',
                    '%two_day_diet_link%',
                    '%contact_email%',
                    '%hints_and_tips_link%',
                    '%diet_day_link%',
                    '%mediterranean_day_link%',
                    '%newsletters_link%',
                ),
                array(
                    self::getLogoUrl(),
                    $user->user_firstname,
                    $uploadsDir['url'] . '2015/06/PROCAS-Lifestyle-The-2-Day-Diet-Keeping-weight-off-V1-27.5.15.pdf',
                    self::alternateContactEmail,
                    site_url('faq'),
                    site_url('2-day-recipes'),
                    site_url('mediterranean-recipes'),
                    site_url('newsletters')
                ), $body);
                
                 if(wp_mail($user->user_email, 'Your recent weight', $body, self::getEmailHeaders())){
                     // Mark user's account
                     GenesisTracker::setUserData($user->ID, self::redFlagEmailDateCol, current_time('Y-m-d H:i:s'));
                     GenesisTracker::logMessage('Sent Red Flag Email ' . $user->ID);
                     return true;
                    
                }else{
                    return array(
                        'message' => 'The email failed to send'
                    );
                }
             }else{
                 return array(
                    'message' => 'The user is not eligible for a red flag email'
                 );
             }
         }else{
             return array(
                 'message' => 'No logs for this user could be found'
             );
         }
     }
     
     public static function sendReminderEmail(){
         global $wpdb;
         // Sends a reminder email to all users
        $body = self::getTemplateContents('reminder');
        
        $body = str_replace(
            array(
                '%site_url%', 
                '%login_url%',
                '%forgot_url%',
                '%genesis_logo%'
            ),
            array(
                get_site_url(),
                wp_login_url(),
                wp_lostpassword_url(),
                self::getLogoUrl()
            ),
            $body
        );
         

        // Send email reminders in batches for spam prevention
          $users = $wpdb->get_results($sql =
              $wpdb->prepare("SELECT u.ID, u.user_email, last_date.`meta_value` 
                  FROM " . $wpdb->users . " u
                LEFT JOIN `" . $wpdb->usermeta . "` last_date
                    ON u.ID = last_date.user_id
                    AND last_date.`meta_key` = '%s'
                LEFT JOIN `" . $wpdb->usermeta . "` opt_out
                    ON u.ID = opt_out.`user_id`
                    AND opt_out.`meta_key` = '%s'
                LEFT JOIN `" . self::getUserDataTableName() . "` ud
                    ON u.ID = ud.`user_id`
                WHERE 
                    (last_date.`meta_value` IS NULL OR last_date.`meta_value` <= DATE_SUB(NOW(), INTERVAL 1 WEEK))
                    AND (ud.`withdrawn` IS NULL OR ud.`withdrawn` <> 1)
                    AND ud.`account_active` = 1
                    AND (opt_out.meta_value IS NULL OR opt_out.meta_value = 0)
                LIMIT 90",
                    self::getOptionKey(self::lastReminderDateKey),
                    self::getOptionKey(self::omitUserReminderEmailKey)
            )
          );

          foreach($users as $user){   
              wp_mail($user->user_email, 'A reminder from The Family History Lifestyle Study', $body, self::getEmailHeaders());
              update_user_meta($user->ID,  self::getOptionKey(self::lastReminderDateKey), current_time('Y-m-d H:i:s')); 
          }
        
     }
}
