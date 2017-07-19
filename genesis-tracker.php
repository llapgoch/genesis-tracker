<?php
/*
Plugin Name: Genesis Tracker
Plugin URI: http://carbolowdrates.com
Description: Tracks user's weight, calories, and exercise
Version: 1.42
Author: Dave Baker
Author URI: http://www.scumonline.co.uk
License: GPL

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : dangerous@scumonline.co.uk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
session_start();
require_once('includes.php');

DEFINE(DS, DIRECTORY_SEPARATOR);

register_activation_hook( __FILE__, array('GenesisTracker', 'install'));

add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::userPageId), 'genesis_user_graph');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::inputProgressPageId), 'genesis_user_input_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::targetPageId), 'genesis_tracker_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::initialWeightPageId), 'genesis_initial_weight_page');
//add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::eligibilityPageId), 'genesis_eligibility_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::ineligiblePageId), 'genesis_ineligible_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::prescriptionPageId), 'genesis_prescription_page');
add_shortcode(GenesisTracker::getOptionKey(GenesisTracker::physiotecLoginPageId), 'genesis_physiotec_login');

add_filter('cron_schedules', 'new_interval');
add_filter('body_class', array('GenesisTracker', 'addBodyClasses'));
add_filter('retrieve_password_message', array('GenesisTracker', 'forgottenPassword'));
add_filter('survey_success', array('GenesisTracker', 'doSurveySuccessMessage'));
add_filter('show_admin_bar', '__return_false');

add_filter('login_headertitle', function(){
    return get_option('blogname');
});

add_filter( 'login_body_class', 'adjust_body_classes');

// Checks whether the install function needs to be called again for DB changes
add_action( 'init', array('GenesisTracker', 'checkVersionUpgrade') );
add_action( 'init', array('GenesisTracker', 'initActions') );
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

add_filter('user_contactmethods','remove_profile_contact_methods',10,1);
function remove_profile_contact_methods( $contactmethods ) {
  unset($contactmethods['aim']);
  unset($contactmethods['jabber']);
  unset($contactmethods['yim']);
  return $contactmethods;
}

/* remove the wp-core-ui class on the login page */
/* remove the wp-core-ui class on the login page */
function adjust_body_classes($classes){
    if(GenesisTracker::isOnLoginPage() && isset($_GET['checkemail'])) {
        $index = array_search('wp-core-ui', $classes);

        if ($index !== false) {
            array_splice($classes, $index, 1);
        }

    }
    return $classes;
}

//wp_unschedule_event(1457199974, 'genesis_send_reminder_email');

if(!wp_next_scheduled('genesis_send_reminder_email')){
    wp_schedule_event(1456833600, 'daily', 'genesis_send_reminder_email');
}

//wp_unschedule_event(1412121600, 'genesis_generate_average_user_data');


// Regenerates all cache data for graph averages
if(!wp_next_scheduled('genesis_generate_average_user_data')){
    wp_schedule_event(mktime(0,0,0,9,1,2014), 'daily', 'genesis_generate_average_user_data');
}

wp_unschedule_event(mktime(13,0,0,1,24,2017), 'send_automatic_four_week_emails');


wp_unschedule_event(1497358800, 'genesis_send_automatic_four_week_emails');


add_action('genesis_send_reminder_email', 'send_reminder_email');
add_action('genesis_generate_average_user_data', array('GenesisTracker', 'generateAverageUsersGraphData'));


// Caters for if reauth=1 is on the URL.  wp_redirect_admin_locations didn't do this
add_action('template_redirect', function(){
    $urlParts = parse_url($_SERVER['REQUEST_URI']);
    $path = $urlParts['path'];

    $logins = array(
        home_url( 'wp-login.php', 'relative' ),
        home_url( 'login', 'relative' ),
        site_url( 'login', 'relative' ),
    );
    if ( in_array( untrailingslashit($path), $logins ) ) {
        wp_redirect( site_url( 'wp-login.php', 'login' ) );
        exit;
    }
}, 900 );

//GenesisTracker::populate();

// For testing Email Reminders ---- CAREFUL!
//add_action('wp', array('GenesisTracker', 'sendReminderEmail'));



 if( $timestamp = wp_next_scheduled( 'genesis_send_reminder_email' )){

//     wp_unschedule_event($timestamp, 'genesis_send_reminder_email');
 }

// add once 10 minute interval to wp schedules
function new_interval($interval) {
    $interval['minutes_10'] = array('interval' => 10*60, 'display' => 'Once 10 minutes');
    $interval['second_1'] = array('interval' => 1, 'display' => '1 second');
    $interval['minute_1'] = array('interval' => 60, 'display' => '1 minute');
    $interval['weekly'] = array('interval' => 604800, 'display' => __('Once Weekly'));

    return $interval;
}


function send_reminder_email(){
    GenesisTracker::sendReminderEmail();
}

if(!is_admin()){
    add_action('wp', array('GenesisTracker', 'decideAuthRedirect'));
    add_action('wp', array('GenesisTracker', 'addHeaderElements'));
    add_action('wp', array('GenesisTracker', 'doActions'));
    add_action('wp_login', array('GenesisTracker', 'checkLoginWeightEntered'), 1000, 2);
    add_action('wp', array('GenesisTracker', 'checkWeightEntered'), 1000);
    add_filter('registration_errors', array('GenesisTracker', 'checkRegistrationErrors'), 10, 3);
    add_action('user_register', array('GenesisTracker', 'checkRegistrationPost'), 10, 1);
    add_filter('wp_authenticate_user', array('GenesisTracker', 'checkLoginAction'), 10, 2);

    // Stop the new user registration email from sending
    add_filter('wp_mail', array('GenesisTracker', 'disableDefaultRegistrationEmail'), 10, 1);
    add_filter( 'wp_login_errors',  array('GenesisTracker', 'modifyRegistrationMessage'), 10, 2);

    add_filter( 'login_message', function($message){
         if(GenesisTracker::isOnRegistrationPage()){
            return GenesisThemeShortCodes::readingBox(
                'Thank you for taking an interest in the 2 Day Wythenshawe Programme',
                '<ul><li>The information that you have entered on this website has been used to see if you are eligible to take part in our study.</li><li><strong>We are happy to say that you are able to take part in the study.</strong></li><li>Please fill in the registration form below and a member of our research team will contact you within 3-4 working days to get you started.</li></ul>'
            );
        }

        if(GenesisTracker::isOnLoginPage() && GenesisTracker::userHasJustRegistered()){
            return GenesisThemeShortCodes::readingBox(
                'Registration Successful - What Happens Next?',
                '<ul><li>A member of the research team will contact you within 3-4 days to book an appointment with you.</li><li> We aim to get you started in the trial within 2 weeks of signing up, so it won’t be long before you receive your diet and exercise advice from us.</li> <li>You will receive a food diary to record your normal food and drink intake in the 7 days before your appointment with us.</li>
<li>Please make sure you don’t change your normal diet and activity level, and do not make any changes before your initial appointment with us.</li><li>You will be able to log in to the website once your account has been activated by a member of our research team.</li></ul><div class="centered-button-box"><a href="' . home_url() . '" class="button large blue">Go to the 2 Day Wythenshawe Programme Homepage</a></div>'
            );
        }

        return $message;
    });
}else{
    // ADMIN HOOK - EXECUTE BEFORE HEADERS -- USE THIS TO CALL ADMIN METHODS AND THEN REDIRECT FOR EXAMPLE
    add_action('admin_init', array('GenesisAdmin', 'doAdminInitHook'));
    // add the datepicker to the admin pages
    add_action('admin_notices', array('GenesisAdmin', 'doAdminNotices'));
}


add_action( 'show_user_profile', 'extra_user_profile_fields',1 );
//add_action( 'show_user_profile', '// user_target_fields',2 );

add_action( 'edit_user_profile', 'extra_user_profile_fields' ,1);
//add_action( 'edit_user_profile', 'user_target_fields' ,2);


add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

add_action( 'personal_options_update', array('GenesisTracker', 'saveUserTargetFields'), 10, 1);
add_action( 'edit_user_profile_update', array('GenesisTracker', 'saveUserTargetFields'), 10, 1);

add_action( 'register_form', 'genesis_add_registration_fields' );

add_action('login_enqueue_scripts', function(){
    // We're hijacking the login page after registering and displaying our custom content - so hide these elements
    if(GenesisTracker::isOnLoginPage() && GenesisTracker::userHasJustRegistered()){
     ?>
     <style>
     #loginform, #backtoblog, #nav{
         display:none;
     }
     </style>
     <?php

    }
});


function genesis_add_registration_fields(){
    $tel = ( isset( $_POST['tel'] ) ) ? $_POST['tel'] : '';
      $first_name = ( isset( $_POST['first_name'] ) ) ? stripslashes(trim($_POST['first_name'])) : '';
      $last_name = ( isset( $_POST['first_name'] ) ) ? stripslashes(trim($_POST['last_name'])) : '';
      ?>
      <p>
          <label for="first_name"><?php _e('First Name') ?><br />
          <input type="text" autocomplete="off" name="first_name" id="first_name" class="input" value="<?php echo esc_attr($first_name);?>" size="25" /></label>
      </p>

      <p>
          <label for="last_name"><?php _e('Last Name') ?><br />
          <input type="text" autocomplete="off" name="last_name" id="last_name" class="input" value="<?php echo esc_attr($last_name); ?>" size="25" /></label>
      </p>
      <p>
          <label for="tel"><?php _e( 'Telephone Number') ?><br />
              <input type="text" autocomplete="off" name="tel" id="tel" class="input" value="<?php echo esc_attr( stripslashes( $tel ) ); ?>" size="25" /></label>
      </p>

      <p>
          <label for="password">Password<br/>
          <input id="password" autocomplete="off" class="input" type="password" size="25" value="" name="password" />
          </label>
      </p>
      <p>
          <label for="repeat_password">Repeat password<br/>
          <input id="repeat_password" autocomplete="off" class="input" type="password" size="25" value="" name="repeat_password" />
          </label>
      </p>

      <p class="message"><?php _e('You will receive an email containing your registration details.<br />Keep this safe, you will need them when your account has been activated.'); ?></p>
      <?php
}

function extra_user_profile_fields($user){
    $userData = GenesisTracker::getUserData($user->ID);

    $reminderKey = GenesisTracker::getOptionKey(GenesisTracker::omitUserReminderEmailKey);
    $storedVal = get_the_author_meta($reminderKey, $user->ID );

    $activeKey = GenesisTracker::userActiveCol;
    $activeVal = $userData[$activeKey];
    $activeVal = (int)$activeVal;

    $startWeightKey = GenesisTracker::userStartWeightCol;
    $startWeight = GenesisTracker::getUserData($user->ID, $startWeightKey);

    $contactedKey = GenesisTracker::userContactedCol;
    $contactedVal = GenesisTracker::getUserData($user->ID, $contactedKey);

    $withdrawnKey = GenesisTracker::userWithdrawnCol;
    $withdrawnVal = GenesisTracker::getUserData($user->ID, $withdrawnKey);

    $notesKey = GenesisTracker::userNotesCol;
    $notesVal = GenesisTracker::getUserData($user->ID, $notesKey);

    $startDateKey = GenesisTracker::userStartDateCol;
    $startDateVal = GenesisTracker::convertDBDate(GenesisTracker::getUserData($user->ID, $startDateKey));

    $twelveMonthTargetKey = GenesisTracker::getOptionKey(GenesisTracker::twelveMonthWeightTargetKey);
    $twelveMonthTargetVal = get_the_author_meta($twelveMonthTargetKey, $user->ID);

    $minHealthyWeightKey = GenesisTracker::getOptionKey(GenesisTracker::minHealthyWeightKey);
    $maxHealthyWeightKey = GenesisTracker::getOptionKey(GenesisTracker::maxHealthyWeightKey);
    $weightTargetKey     = GenesisTracker::getOptionKey(GenesisTracker::weightTargetKey);
    $weightTargetLongTermKey = GenesisTracker::getOptionKey(GenesisTracker::weightTargetLongTermKey);
    $sixMonthTargetKey   = GenesisTracker::getOptionKey(GenesisTracker::sixMonthWeightTargetKey);
    $sixMonthWeightKey   = GenesisTracker::sixMonthWeightCol;
    $sixMonthDateKey     = GenesisTracker::sixMonthDateCol;
    $omitSixMonthEmailKey = GenesisTracker::sixMonthEmailOptOutCol;
    $studyGroupKey       = GenesisTracker::studyGroupCol;

    $isActive = GenesisTracker::getUserData($user->ID, GenesisTracker::userActiveCol);

    $minHealthyWeightVal = get_the_author_meta($minHealthyWeightKey, $user->ID );
    $maxHealthyWeightVal = get_the_author_meta($maxHealthyWeightKey, $user->ID );
    $weightTargetLongTermVal = get_the_author_meta($weightTargetLongTermKey, $user->ID );
    $weightTargetVal     = get_the_author_meta($weightTargetKey, $user->ID );
    $sixMonthTargetVal   = get_the_author_meta($sixMonthTargetKey, $user->ID );
    $sixMonthWeightVal   = GenesisTracker::getUserSixMonthWeight( $user->ID );
    $sixMonthDateValue     = GenesisTracker::getUserData($user->ID, $sixMonthDateKey);
    $omitSixMonthEmailValue = GenesisTracker::getUserData($user->ID, $omitSixMonthEmailKey);
    $studyGroupVal       = GenesisTracker::getUserStudyGroup($user->ID);

    $isMetric = GenesisTracker::getInitialUserUnit($user->ID) == GenesisTracker::UNIT_METRIC;

    $tel = get_the_author_meta('tel', $user->ID );
    $form = DP_HelperForm::createForm('userRegister');

    $initalUnit  = GenesisTracker::getInitialUserUnit($user->ID);

    if($lastSavedUnit = get_the_author_meta(GenesisTracker::getOptionKey('last_profile_unit'), $user->ID)){
        $isMetric = $lastSavedUnit == GenesisTracker::UNIT_METRIC;
    }

    if(is_admin()){
        $isMetric = true;
    }

    $sixMonthWeightMain = "";
    $sixMonthWeightPounds = "";

    if($sixMonthWeightVal){
        if($isMetric){
            $sixMonthWeightMain = $sixMonthWeightVal;
        }else{
            $weight = GenesisTracker::kgToStone($sixMonthWeightVal);

            $sixMonthWeightMain = $weight['stone'];
            $sixMonthWeightPounds = $weight['pounds'];
        }
    }

    ?>
    <table class="form-table input-form">
        <tr>
        <th><label for="tel"><?php _e("Telephone"); ?></label></th>
            <td>
            <?php
            echo $form->input('tel', 'text', array(
              'autocomplete' => 'off',
              'id' => 'tel',
              'class' => 'input regular-text',
              'value' => $tel,
              'size' => 25
            ));
            ?>
           </label>
            </td>
        </tr>


        <?php if(is_admin()): ?>
            <tr>
                <th>
                    <label for="user_id"><?php _e("Web ID")?></label>
                </th>
                <td>

                    <?php $settings = array(
                        'default' => $user->ID,
                        'id' => 'user_id',
                        'readonly' => 'readonly'
                    );

                    echo $form->input('user_id', 'text', $settings);
                    ?>
                </td>
            </tr>


            <tr>
                <th>
                    <label for="<?php echo $startDateKey?>"><?php _e("Activation Date")?></label>
                </th>
                <td>
                    <?php $settings = array(
                        'default' => $startDateVal ? $startDateVal : '',
                        'id' => $startDateKey,
                        'readonly' => 'readonly',
                        'class' => 'datepicker'
                    );

                    echo $form->input($startDateKey, 'text', $settings);
                    ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if(is_admin()){ ?>
        <hr />
    <table class="form-table">
        <tr>
            <th>
                <label for="<?php echo $startWeightKey?>"><?php _e("Initial Weight (Kg)")?></label>
            </th>
            <td>
                <?php $settings = array(
                    'default' => $startWeight,
                    'id' => $startWeightKey
                );

                if(!$startWeight){
                    $settings['disabled'] = 'disabled';
                }
                echo $form->input($startWeightKey, 'text', $settings);
                ?>
                <div><span class="description"><?php _e('This should not be set before the user has logged in and set it themselves')?></span></div>
            </td>
        </tr>

        <tr>
        <th><label for="<?php echo $activeKey?>"><?php _e("Genesis Activate User"); ?></label></th>
        <td>
        <?php

         echo $form->dropdown($activeKey, array(
         '0' => 'Disabled',
         '1' => 'Active'
         ), array(
             'default' => $activeVal,
             'id' => $activeKey
         ));
        ?>
        </td>
        </tr>
        <tr>
            <th><label for="<?php echo $contactedKey; ?>"><?php _e("User has been contacted"); ?></label></th>
            <td>
            <?php
             echo $form->dropdown($contactedKey, array(
             '0' => 'No',
             '1' => 'Yes'
             ), array(
                 'default' => $contactedVal,
                 'id' => $contactedKey
             ));
            ?>
            </td>
        </tr>

        <tr>
            <th><label for="<?php echo $withdrawnKey; ?>"><?php _e("User has withdrawn"); ?></label></th>
            <td>
            <?php
             echo $form->dropdown($withdrawnKey, array(
             '0' => 'No',
             '1' => 'Yes'
             ), array(
                 'default' => $withdrawnVal,
                 'id' => $withdrawnKey
             ));
            ?>
            </td>
        </tr>

        <tr>
            <th><label for="<?php echo $notesKey; ?>"><?php _e("Comments"); ?></label></th>
            <td>
            <?php
             echo $form->textarea($notesKey, array(
                 'default' => $notesVal,
                 'id' => $notesKey,
                 'cols' => 30,
                 'rows' => 5
             ));
            ?>
            </td>
        </tr>


    </table>



    <?php } ?>


    <?php if(is_admin()):?>
        <hr />
    <?php endif;?>

    <table class="form-table">
    <tr>
    <th><label for="<?php echo $reminderKey ?>"><?php _e("Opt out of weekly reminder emails"); ?></label></th>
    <td>
    <?php
     echo $form->createInput($reminderKey, 'checkbox', array(
     'id' => $reminderKey,
     'value' => 1
     ), $storedVal);
    ?>
    </td>
    </tr>

    <?php
    if(is_admin()):?>
    <tr>
    <th><label for="<?php echo $omitSixMonthEmailKey ?>"><?php _e("Opt out of 6 - 12 month emails"); ?></label></th>
    <td>
    <?php
     echo $form->createInput($omitSixMonthEmailKey, 'checkbox', array(
     'id' => $omitSixMonthEmailKey,
     'value' => 1
     ), $omitSixMonthEmailValue);
    ?>
    </td>
    </tr>
    <?php endif; ?>

    </table>

        <?php if(is_admin()):?>
            <hr />
        <?php endif;?>
        <div class="stats">
            <h4><?php if(is_admin() == false): echo "Your"; endif;?> Target Information</h4>
            <table class="form-table">
                <?php if(is_admin() == false):?>
                <tr>
                    <th><label><?php _e('Your Start Weight')?></label></th>
                    <td>
                        <span class="stat">
                            <?php if($isMetric):?>
                                <span class="weight">
                                    <?php echo GenesisTracker::niceFormatWeight(GenesisTracker::getInitialUserWeight($user->ID), "metric");?>
                                </span>
                            <?php else:?>
                                <span class="weight">
                                    <?php echo GenesisTracker::niceFormatWeight(GenesisTracker::getInitialUserWeight($user->ID), "imperial");?>
                                </span>
                            <?php endif;?>
                        </span>
                    </td>
                </tr>
                <?php endif;?>
            <tr>
                <th>
                    <?php if(!is_admin()):?>
                        <a href="javascript:;" class="fa fa-question-circle help-icon weight-help" title="<strong>Healthy weight range</strong><p>This weight range will give you a healthy amount body fat</p>"></a>
                    <?php endif;?>
                    <label for="<?php echo $minHealthyWeightKey;?>"><?php _e('Healthy Weight Range (Min)'); ?></label></th>
                <td>
                    <?php
                    if(is_admin()):
                        echo $form->input($minHealthyWeightKey, 'text', array(
                              'autocomplete' => 'off',
                              'id' => $minHealthyWeightKey,
                              'class' => '',
                              'value' => $minHealthyWeightVal
                          ));
                    else :
                        ?>
                        <span class="stat">
                            <?php if($isMetric):?>
                                <span class="weight">
                                    <?php echo GenesisTracker::niceFormatWeight($minHealthyWeightVal, "metric");?>
                                </span>
                            <?php else:?>
                                <span class="weight">
                                    <?php echo GenesisTracker::niceFormatWeight($minHealthyWeightVal, "imperial");?>
                                </span>
                            <?php endif;?>
                        </span>
                    <?php
                    endif;
                    ?>
                </td>
            </tr>
            <tr>
                <th class="max-weight"><label for="<?php echo $maxHealthyWeightKey;?>"><?php _e('Healthy Weight Range (Max)'); ?></label></th>
                <td>
                    <?php
                     if(is_admin()):
                        echo $form->input($maxHealthyWeightKey, 'text', array(
                              'autocomplete' => 'off',
                              'id' => $maxHealthyWeightKey,
                              'class' => '',
                              'value' => $maxHealthyWeightVal
                          ));
                      else:
                          ?>
                         <span class="stat">
                              <?php if($isMetric):?>
                                 <span class="weight-check">
                                     <?php echo GenesisTracker::niceFormatWeight($maxHealthyWeightVal, "metric");?>
                                 </span>
                             <?php else:?>
                                 <span class="weight-check">
                                     <?php echo GenesisTracker::niceFormatWeight($maxHealthyWeightVal, "imperial");?>
                                 </span>
                             <?php endif; ?>
                         </span>
                     <?php
                     endif;
                    ?>
                </td>
            </tr>
            <?php if(is_admin()):?>

                <tr>
                    <th><label for="<?php echo $weightTargetKey;?>"><?php _e('End of Programme Target'); ?></label></th>
                    <td>
                        <?php
                        if(is_admin()):
                            echo $form->input($weightTargetKey, 'text', array(
                                'autocomplete' => 'off',
                                'id' => $weightTargetKey,
                                'class' => '',
                                'value' => $weightTargetVal
                            ));
                        else:
                            ?>
                            <span class="stat">
                               <?php if($isMetric):?>
                                   <span class="weight">
                                      <?php echo GenesisTracker::niceFormatWeight($weightTargetVal, "metric");?>
                                  </span>
                               <?php else:?>
                                   <span class="weight">
                                      <?php echo GenesisTracker::niceFormatWeight($weightTargetVal, "imperial");?>
                                  </span>
                               <?php endif;?>
                          </span>
                            <?php
                        endif;
                        ?>
                    </td>
                </tr>

                <tr>
                    <th><label for="<?php echo $weightTargetLongTermKey;?>"><?php _e('Long Term Target'); ?></label></th>
                    <td>
                        <?php
                        if(is_admin()):
                            echo $form->input($weightTargetLongTermKey, 'text', array(
                                'autocomplete' => 'off',
                                'id' => $weightTargetLongTermKey,
                                'class' => '',
                                'value' => $weightTargetLongTermVal
                            ));
                        else:
                            ?>
                            <span class="stat">
                               <?php if($isMetric):?>
                                   <span class="weight">
                                      <?php echo GenesisTracker::niceFormatWeight($weightTargetLongTermVal, "metric");?>
                                  </span>
                               <?php else:?>
                                   <span class="weight">
                                      <?php echo GenesisTracker::niceFormatWeight($weightTargetLongTermVal, "imperial");?>
                                  </span>
                               <?php endif;?>
                          </span>
                            <?php
                        endif;
                        ?>
                    </td>
                </tr>

            <?php endif; ?>
        </table>
        <?php if(is_admin()):?>
            <hr />
            <h4>Nutritional Targets</h4>
        <?php endif;?>
        <?php user_target_fields($user); ?>
    </div>


    <?php
}

function user_target_fields($user){
    $targetFields = GenesisTracker::getuserMetaTargetFields();

    if(!is_admin()):
        ?>
        <h4>Nutritional Targets</h4>
    <?php
    endif;
    ?>
    <table class="form-table food-table">

        <?php if(!is_admin()):?>
        <tr>
            <th>&nbsp;</th>
            <th><h4>Diet day portions</h4></th>
            <th><h4>Mediterranean day portions</h4></th>

        </tr>
        <?php endif;?>

        <?php foreach($targetFields as $fieldKey => $data) : ?>
        <?php $fullKey = GenesisTracker::getOptionKey(GenesisTracker::targetPrependKey . $fieldKey); ?>
        <?php $val = get_the_author_meta( $fullKey, $user->ID );?>

        <tr>

            <th><label for="<?php echo $fullKey;?>"><?php _e((is_admin() ? "Target " : "") . $data['name']); ?></label></th>
            <?php if(!is_admin()):?>
                <td><span class="stat">
                        <?php
                        echo isset($data['med']) ? $data['med'] : "- -";
                        ?>
                    </span>
                </td>
            <?php endif; ?>
            <td>
                <?php


                if(is_admin()):
                    echo DP_HelperForm::createInput($fullKey, 'text', array(
                        'id' => $fullKey,
                         'value' => $val
                    ));
                else:
                    ?>
                    <span class="stat"><?php echo $val ? $val : "- -";?></span>
                    <?php
                endif;
                ?>
            </td>


        </tr>

        <?php endforeach; ?>
    </table>
    <?php
}

function save_extra_user_profile_fields($user_id){
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }


    $reminderKey = GenesisTracker::getOptionKey(GenesisTracker::omitUserReminderEmailKey);
    $minHealthyWeightKey = GenesisTracker::getOptionKey(GenesisTracker::minHealthyWeightKey);
    $maxHealthyWeightKey = GenesisTracker::getOptionKey(GenesisTracker::maxHealthyWeightKey);
    $weightTargetKey     = GenesisTracker::getOptionKey(GenesisTracker::weightTargetKey);
    $weightTargetLongTermKey = GenesisTracker::getOptionKey(GenesisTracker::weightTargetLongTermKey);
    $sixMonthTargetKey   = GenesisTracker::getOptionKey(GenesisTracker::sixMonthWeightTargetKey);
    $sixMonthDateKey     = GenesisTracker::sixMonthDateCol;
    $omitSixMonthEmailKey = GenesisTracker::sixMonthEmailOptOutCol;
    $startDateKey         = GenesisTracker::userStartDateCol;
    $studyGroupKey        = GenesisTracker::studyGroupCol;
    $isActive             = (bool) GenesisTracker::getUserData($user_id, GenesisTracker::userActiveCol);

    $startWeightKey = GenesisTracker::userStartWeightCol;
    $sixMonthWeightKey   = GenesisTracker::sixMonthWeightCol;

    $twelveMonthTargetKey = GenesisTracker::getOptionKey(GenesisTracker::twelveMonthWeightTargetKey);

    $val = isset($_POST[$reminderKey]) ? $_POST[$reminderKey] : 0;
    $tel = isset($_POST['tel']) ? $_POST['tel'] : '';

    update_user_meta( $user_id, $reminderKey, $val );
    update_user_meta( $user_id, 'tel', $tel );


    $sixMonthWeight = $_POST['weight_main'];

    if(is_admin() == false){
        if($_POST['weight_unit'] == GenesisTracker::UNIT_METRIC){
            $sixMonthWeight = $_POST['weight_main'];
        }else{
            $sixMonthWeight = GenesisTracker::stoneToKg($_POST['weight_main'], $_POST['weight_pounds']);
        }
    }

    if(GenesisTracker::isValidWeight($sixMonthWeight)){
        GenesisTracker::setUserData($user_id, $sixMonthWeightKey, $sixMonthWeight);

        if(is_admin() == false){
            update_user_meta( $user_id, GenesisTracker::getOptionKey('last_profile_unit'), $_POST['weight_unit']);
        }
    }


    if(is_admin()){
        $minHealthyWeight = isset($_POST[$minHealthyWeightKey]) ? (float) $_POST[$minHealthyWeightKey] : '';
        $maxHealthyWeight = isset($_POST[$maxHealthyWeightKey]) ? (float)$_POST[$maxHealthyWeightKey] : '';
        $targetWeight = isset($_POST[$weightTargetKey]) ? (float)$_POST[$weightTargetKey] : '';
        $weightTargetLongTermVal = isset($_POST[$weightTargetLongTermKey]) ? (float)$_POST[$weightTargetLongTermKey] : '';
        $sixMonthTargetWeight = isset($_POST[$sixMonthTargetKey]) ? (float)$_POST[$sixMonthTargetKey] : '';
        $omitSixMonthEmailValue = isset($_POST[$omitSixMonthEmailKey]) ? (int)$_POST[$omitSixMonthEmailKey] : 0;
        $twelveMonthTargetValue = isset($_POST[$twelveMonthTargetKey]) ? (int)$_POST[$twelveMonthTargetKey] : '';
        $studyGroupValue = isset($_POST[$studyGroupKey]) ? $_POST[$studyGroupKey] : '';

        if(isset($_POST[$startWeightKey]) && ((float) $_POST[$startWeightKey])){
            GenesisTracker::setUserData($user_id, $startWeightKey, GenesisTracker::makeValidWeight($_POST[$startWeightKey]));
        }

       if(isset($_POST[$twelveMonthTargetKey]) && ((float) $_POST[$twelveMonthTargetKey])){
            update_user_meta( $user_id, $twelveMonthTargetKey, GenesisTracker::makeValidWeight($_POST[$twelveMonthTargetKey]) );
        }

        if(isset($_POST[$sixMonthDateKey]) && $_POST[$sixMonthDateKey]){
            GenesisTracker::setUserData($user_id, $sixMonthDateKey, GenesisTracker::convertFormDate($_POST[$sixMonthDateKey]));
        }


        update_user_meta( $user_id, $minHealthyWeightKey, $minHealthyWeight );
        update_user_meta( $user_id, $maxHealthyWeightKey, $maxHealthyWeight );
        update_user_meta( $user_id, $weightTargetKey, $targetWeight );
        update_user_meta( $user_id, $weightTargetLongTermKey, $weightTargetLongTermVal );
        update_user_meta( $user_id, $sixMonthTargetKey, $sixMonthTargetWeight );
        GenesisTracker::setUserData($user_id, $omitSixMonthEmailKey, $omitSixMonthEmailValue);
        GenesisTracker::setUserData($user_id, $studyGroupKey, $studyGroupValue);
    }

    GenesisTracker::clearCachedUserData($user_id);
}




add_action('admin_menu', 'genesisAdminMenu');
add_action('wp_ajax_genesis_get_form_values', 'genesis_post_form_values');

function genesisAdminMenu(){
    add_menu_page('2DW Admin', '2DW Admin', GenesisTracker::editCapability, 'genesis-tracker', genesis_admin_page, null, 5);
}


// Because the ajax functionality doesn't pass parameters, we get them here
function genesis_post_form_values(){
    $day = $_POST['day'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    die(json_encode(GenesisTracker::getUserFormValues($day, $month, $year)));
}

function genesis_admin_page(){
    if(isset($_GET['edit_user']) && (int) $_GET['edit_user']){
        if($user = get_user_by('id', $_GET['edit_user'])){
            genesis_admin_user_show($user);
            return;
        }
    }

    // Load a sub page (this uses pagesub whereas executing a function before rendering uses sub)
    if(isset($_GET['pagesub']) && is_admin()){
        if(strpos($_GET['pagesub'], "genesis_admin_") === 0){
            if(function_exists($_GET['pagesub'])){
                return call_user_func($_GET['pagesub']);
            }else{
                include('page/admin/' . $_GET['pagesub'] . '.php');
                return;
            }
        }
    }

    // Default
    genesis_admin_user_list();
}




function genesis_admin_user_list(){
    global $wpdb;
    $tbl = new GenesisUserTable();
    include('page/admin/user-list.php');
}

function genesis_admin_user_show($user){
    global $wpdb;
    $userDetails = GenesisAdmin::getUserLogDetails(null, $user->ID, true);
    $userTelephone = get_user_meta($user->ID, 'tel', true);
    $userEditLink = get_edit_user_link($user->ID);
    $foodLogs = GenesisAdmin::getFoodLogs($user->ID);
    $foodTypes = GenesisTracker::getuserMetaTargetFields();
    $foodTimes = GenesisTracker::getUserTargetTimes();
    $exerciseLogs = GenesisAdmin::getExerciseLogsForUser($user->ID);
    $weightLogs = GenesisAdmin::getWeightLogsForUser($user->ID);
    $dietDays = GenesisAdmin::getDietDaysForUser($user->ID);
    $fourWeekTypes = GenesisAdmin::getFourWeekEmailTypes();
    $exerciseTypes = GenesisTracker::getExerciseTypes();

    include('page/admin/user-show.php');
}

function genesis_user_graph(){
    ob_start();
    $foodLogDays = 7;
    $userGraphPage = GenesisTracker::getUserPagePermalink();
    $userInputPage = GenesisTracker::getUserInputPagePermalink();

    $weightChange = GenesisTracker::getUserWeightChange(get_current_user_id());
    $foodLogData = GenesisTracker::getTotalFoodLogs(get_current_user_id(), $foodLogDays);
    $foodTypes = GenesisTracker::getuserMetaTargetFields();

    $weightChangeInButter = 0;


    if(abs($weightChange) > -0.1){
        $butterWeight = 0.25;
        $weightChangeInButter = round(($weightChange) / $butterWeight,2);
    }

    include('page/user-graph.php');
    $output = ob_get_contents();

    ob_end_clean();
    return $output;
}

// PAGES
function genesis_user_input_page(){
    ob_start();
    $form = DP_HelperForm::getForm('user-input');
    $outputBody = false;
    $userGraphPage = GenesisTracker::getUserPagePermalink();
    $userInputPage = GenesisTracker::getUserInputPagePermalink();
    $exerciseTypes = array();

    foreach(GenesisTracker::getExerciseTypes() as $key => $val){
        $exerciseTypes[$key] = $val['name'];
    }

    $dateListPicker = '';

    if($form->wasPosted()){
        // Get the date list picker html with selected post values
        $date = GenesisTracker::convertFormDate($form->getRawValue('measure_date'));

        $dateParts = date_parse($date);
        $selectedDates = is_array($form->getRawValue('diet_days')) ? $form->getRawValue('diet_days') : array();
        $dateListPicker = GenesisTracker::getDateListPicker($dateParts['day'], $dateParts['month'], $dateParts['year'], false, $selectedDates);
    }

    if(GenesisTracker::getPageData('user-input-save') == true){
        require('page/user-input-success.php');
        $outputBody = true;
    }

    if(GenesisTracker::getPageData('user-input-duplicate') == true){
        require('page/user-input-duplicate.php');
        $outputBody = true;
    }

    // Default form output
    if(!$outputBody){
        $metricUnits = $form->getRawValue('weight_unit') == GenesisTracker::UNIT_METRIC;
        require('page/user-input.php');
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function genesis_tracker_page(){
    ob_start();
    $form = DP_HelperForm::getForm('tracker');
    $outputBody = false;
    $userGraphPage = GenesisTracker::getUserPagePermalink();
    $userInputPage = GenesisTracker::getUserInputPagePermalink();

    if(GenesisTracker::getPageData('target-save') == true){
        require('page/target-save-success.php');
        $outputBody = true;
    }


    // Default form output
    if(!$outputBody){
        $metricUnits = $form->getRawValue('weight_unit') == GenesisTracker::UNIT_METRIC;
        if($currentWeight = GenesisTracker::getUserLastEnteredWeight(get_current_user_id())){
            $imperial = GenesisTracker::kgToStone($currentWeight);
            $weight = array(
                'metric' => round($currentWeight, 2) . ' kilograms',
                'imperial' => round($imperial['stone'], 2) . " stone" . ($imperial['pounds'] ? ", " . round($imperial['pounds'], 2) . " pounds" : "")
            );
        }


        require('page/tracker-input.php');
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function genesis_initial_weight_page(){
    ob_start();
    $userGraphPage = GenesisTracker::getUserPagePermalink();
    $form = DP_HelperForm::getForm('initial-weight');
    $outputBody = false;

    if(GenesisTracker::getPageData('weight-save') == true){
        require('page/initial-weight-success.php');
        $outputBody = true;
    }

    if(!$outputBody){
        require('page/initial-weight.php');
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function genesis_prescription_page(){
    return '
    <div class="physiotec">
    <script src="https://www.physiotec.ca/jscripts/iframe.js"></script>
    </div>';
}

function genesis_physiotec_login(){
    require('page/physiotec-login.php');
}

function genesis_eligibility_page(){
    ob_start();
    $form = DP_HelperForm::getForm('eligibility');
    $outputBody = false;

    $eligibilityPdfUrl = plugins_url('downloads/eligibility.pdf', __FILE__);
    $eligibilityQuestions = GenesisTracker::getEligibilityQuestions();

    require('page/eligibility.php');
    $outputBody = true;

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function genesis_ineligible_page(){
    ob_start();

    $outputBody = false;
    $ineligibleDownloadPdfUrl = plugins_url('downloads/advice.pdf', __FILE__);
    $twoDayDietDownloadPdfUrl = plugins_url('downloads/2-day-diet-advice.pdf', __FILE__);

    require('page/ineligible.php');
    $outputBody = true;

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}