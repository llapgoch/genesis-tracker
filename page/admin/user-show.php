<div class="wrap">
    <h2>
        Family History Lifestyle Study User Details
        <a class="add-new-h2" href="<?php echo $userEditLink;?>">Edit User</a>
    </h2>
    
    <dl class="admin-list">
        <dt>Email Address</dt>
        <dd><a href="mailto:<?php echo $user->user_email;?>"><?php echo $user->user_email;?></a></dd>
        <dt>Name</dt>
        <dd><?php echo trim($userDetails['user_name']) ? (string)$userDetails['user_name'] : "- -"; ?></dd>
        <dt>Telephone Number</dt>
        <dd><?php echo $userTelephone; ?></dd>
        <dt>Passcode Group</dt>
        <dd><?php echo $userDetails[GenesisTracker::passcodeGroupCol] ? $userDetails[GenesisTracker::passcodeGroupCol] : "- -"; ?></dd>
        <dt>Study Number</dt>
        <dd><?php echo $userDetails[GenesisTracker::studyGroupCol] ? $userDetails[GenesisTracker::studyGroupCol] : "- -"; ?></dd>
        <dt>Register Date</dt>
        <dd><?php echo gmdate('d M Y', strtotime($userDetails['user_registered']));?></dd>
        <dt>Start Date</dt>
        <dd><?php echo $userDetails[GenesisTracker::userStartDateCol] ? gmdate('d M Y', strtotime($userDetails[GenesisTracker::userStartDateCol])) : "<strong>Not Set</strong>";?></dd>
        <dt>User Has Failed Exercise Eligibility Questions</dt>
        <dd><?php echo $userDetails[GenesisTracker::failedExerciseEligibilityCol] ? "<span style='color:red'>Yes</span>" : "No";?></dd>
        <dt>User Contacted</dt>
        <dd><?php echo (int) $userDetails['user_contacted'] == 0 ? 'No' : 'Yes'?></dd>
        <dt>Account Active</dt>
        <dd><?php echo (int) $userDetails['account_active'] == 0  ? 'No' : 'Yes';?></dd>
        <dt>User Withdrawn</dt>
        <dd><?php echo (int) $userDetails['withdrawn'] == 0 ? 'No' : 'Yes'?></dd>
        <dt>Comments</dt>
        <dd><?php echo $userDetails['notes']?></dd>
        <dt>Last Measurement Date</dt>
        <dd><?php 
            if(isset($userDetails['measure_date']) && $userDetails['measure_date']) :
                echo GenesisTracker::prettyDBDate($userDetails['measure_date']);
            else :
                echo "- -";
            endif;
            ?>
        </dd>
        <dt>Initial Weight (Kg)</dt>
        <dd><?php 
            if(isset($userDetails['start_weight']) && $userDetails['start_weight']) :
                echo round($userDetails['start_weight'], 4);
            else :
                echo "- -";
            endif;
        ?></dd>
        <dt>Lowest Recorded Weight of All Time (Kg)</dt>
        <dd>
            <?php if(isset($userDetails['least_weight']) && $userDetails['least_weight']) :
                echo round($userDetails['least_weight'], 4);
            else :
                echo "- -";
            endif;
            ?>
        </dd>
      
        <dt>Current Weight (Kg)</dt>
        <dd><?php 
            if(isset($userDetails['weight']) && $userDetails['weight']) :
                echo round($userDetails['weight'], 4);
            else :
                echo "- -";
            endif;
            ?>  
        </dd>
        <dt>Weight Change (Kg)</dt>
        <dd><?php 
             if(isset($userDetails['weight_change']) && $userDetails['weight_change']) :
                 echo round($userDetails['weight_change'], 4);
             else :
                 echo "- -";
             endif;
            ?>     
        </dt>
        <dt>Current Weight Minus Benchmark Weight (Kg) <br /><small>(positive value indicates weight gain; negative value indicates weight loss)</small></dt>
        <dd><?php echo is_numeric($userDetails['six_month_benchmark_change']) ? round($userDetails['six_month_benchmark_change'], 4) : "- -";?>
        </dd>
        
        <dt>User Flagged (Registered for six months and gained 1kg from benchmark weight)</dt>
        <dd><?php echo (int) $userDetails['six_month_benchmark_change'] >= 1 ? '<span style="color:red">Yes</span>' : "No" ?>
            <form action="<?php echo GenesisTracker::getAdminUrl(array('sub' => 'genesis_admin_send_red_flag_email')); ?>" method="post">
            

                <input type="hidden" name="user" value="<?php echo $user->ID;?>" />
                <button <?php echo $userDetails['six_month_benchmark_change_email_check'] < 1 ? 'disabled="disabled"' : '';?> type="submit">Send Red Flag Email</button>
                <?php if ($userDetails['red_flag_message']): ?>
                    <span style="font-style:italic;margin-left:5px"><?php echo $userDetails['red_flag_message']?></span>
                <?php endif;?>
                <?php if($dateSent = $userDetails['red_flag_email_date']): ?>
                    <span style="font-style:italic;margin-left:5px">Sent at: <strong><?php echo GenesisTracker::convertDBDatetime($userDetails['red_flag_email_date']); ?></strong></span>
                <?php endif; ?>
            </form>
        <dt>Week Number</dt>
        <dd><?php echo $userDetails['weeks_registered'] ? $userDetails['weeks_registered'] : "- -";?></dd>
        <dt>Four Weekly Emails</dt>
        <dd>
            <?php if($userDetails['four_weekly_date']): ?>
                    <em><strong>This user was last sent a four weekly email on the <?php echo GenesisTracker::convertDBDatetime($userDetails['four_weekly_date'])?></strong></em>
            <?php endif; ?>
            
            <?php if($userDetails['four_week_required_to_send']):?>
            <form action="<?php echo GenesisTracker::getAdminUrl(array('sub' => 'genesis_admin_send_four_weekly_email'))?>" class="confirm-submit" method="post">
                <input type="hidden" name="user" value="<?php echo $userDetails['user_id']; ?>">
                <table class="four-weekly">
                    <?php foreach(GenesisAdmin::getFourWeekEmailTypes() as $key => $label): ?>
                        <?php $suggested = $key == $userDetails['four_week_outcome'] ?>
                        <tr class="<?php echo $suggested ? 'suggested' : '' ?>">
                            <td><?php echo $label ?></td>
                            <td><button type="submit" name="action" value="<?php echo $key;?>">Send</td>
                            <td>
                                <?php if($suggested): ?>
                                    <em>The system suggests this email. Please check against their log data before sending</em>
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
        <?php endif; ?>
        <?php if(!$userDetails['four_week_required_to_send']): ?>
            <em style="display:block"><strong>This user is not eligible for a four weekly email. Either:</strong></em>
            <ul style="list-style:circle">
                <li>They have withdrawn</li>
                <li>They haven't been subscribed for long enough</li>
                <li>They haven't been given a six month weight</li>
                <li>They have opted out of 6 - 12 month reminder emails</li>
                <li>They have been subscribed for a year or more (the Monday after their activation date + 52 weeks)</li>
                <li>The weeks they've been registered is not one of the four week email points</li>
                <li>It is less than 28 days since they were last sent a 4-weekly email</li>
                <li>A red flag email was send in the last 7 days <em><strong>or the user is flagged for a red flag email</strong></em></li>
            </ul>
        <?php endif; ?>
        </dd>
    </dl>

<<<<<<< HEAD
    <?php if($userDetails[GenesisTracker::failedExerciseEligibilityCol] && $exerciseEligibilityAnswers): ?>
        <hr />
        <h2>This user failed their exercise eligibility questions:</h2>

        <div class="table-scroller">
            <table class="wp-list-table widefat">
                <thead>
                    <th>Question</th>
                    <th>Answer</th>
                </thead>
                <tbody>
                <?php foreach($exerciseEligibilityAnswers as $answer): ?>
                    <tr>
                        <td>
                            <?php echo $answer->question; ?>
                            <?php if($answer->question_id == 25 && $eligibilityResult->no_physical_activity_reason): ?>
                                <p><strong>User Answer: </strong><?php echo _e($eligibilityResult->no_physical_activity_reason); ?></p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="<?php echo $answer->answer !== $answer->correct ? 'color:red' : '';?>">
                            <?php echo $answer->answer == 2 ? "No" : "Yes"; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if($fourWeekLogs && count($fourWeekLogs)) : ?>
        <hr />
        <h2>Four Week Email Logs (<?php echo count($fourWeekLogs);?>)</h2>
        <p>If there are more than ten logs, the table will scroll</p>

        <div class="table-scroller">
            <table class="wp-list-table widefat">
                <thead>
                    <th>Date</th>
                    <th>Week</th>
                    <th>Type</th>
                    <th>Send Type</th>
                </thead>
                <tbody>
                <?php foreach($fourWeekLogs as $log): ?>
                    <tr>
                        <td><?php echo date( 'j M Y', strtotime($log->log_date) ); ?></td>
                        <td><?php echo $log->week;?></td>
                        <td><?php echo isset($fourWeekTypes[$log->type]) ? $fourWeekTypes[$log->type] : '- -';?></td>
                        <td><?php echo $log->send_type; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
=======
    <?php if($surveyResults && count($surveyResults)): ?>
        <hr />
        <h2>Questionnaires Completed</h2>
        <p>
            This user has completed <?php echo count($surveyResults);?> questionnaire<?php echo count($surveyResults) !== 1 ? "s" : "";?>
        </p>
    <div class="table-scroller">
        <table class="wp-list-table widefat">
            <thead>
            <th>Name</th>
            <th>Date</th>
            </thead>
            <tbody>
            <?php foreach($surveyResults as $result): ?>
                <tr>
                    <td>
                        <a href="<?php echo $result->admin_uri; ?>"><?php echo $result->name ? $result->name : "- -";?></a>
                    </td>
                    <td><?php echo date( 'j M Y', strtotime($result->added_on) ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        </div>

>>>>>>> 5b82e59... output surveys
    <?php endif; ?>
    
    <?php if($weightLogs && count($weightLogs)) : ?>
        <hr />
        <h2>All Weight Logs (<?php echo count($weightLogs) ?>)</h2>
        <p>If there are more than ten logs, the table will scroll</p>
        <div class="table-scroller">
        <table class="wp-list-table widefat">
            <thead>
                <th>Date</th>
                <th>Weight</th>
            </thead>
            <tbody>
                <?php foreach($weightLogs as $log): ?>
                    <tr>
                        <td><?php echo date( 'j M Y', strtotime($log->measure_date) ); ?></td>
                        <td><?php echo $log->weight ? $log->weight : "- -";?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
        </table>
    </div>
    <?php endif;  ?>
    
    <?php if($exerciseLogs && count($exerciseLogs)) : ?>
        <hr />
    <h2>Last <?php echo count($exerciseLogs); ?> Exercise Logs</h2>
     <table class="wp-list-table widefat">
         <thead>
             <th>Date</th>
             <th>Exercise Type</th>
             <th>Workout Details</th>
             <th>Duration</th>
         </thead>
         <tbody>
             <?php foreach($exerciseLogs as $log): ?>
                 <?php $type = $log->type == 'aerobic' ? $exerciseTypes : $resistanceExerciseTypes; ?>
                 <tr>
                     <td><?php echo date( 'j M Y', strtotime($log->measure_date) ); ?></td>
                     <td>
                         <?php echo isset($mainExerciseTypes[$log->type]) ? $mainExerciseTypes[$log->type]['name'] : ""; ?>
                     </td>
                     <td>
                         <?php echo isset($type[$log->sub_type]) ? "<strong>" . $type[$log->sub_type]['name'] . "</strong>" : ""; ?>
                         <?php echo $log->description ? "<br /> <small>" . esc_html($log->description) . "</small>" : ""; ?>
                     </td>
                     <td><?php echo $log->minutes; ?> minutes</td>
                 </tr>
             <?php endforeach; ?>
             </tbody>
     </table>
        
    <?php endif; ?>
    
    <?php if($dietDays && count($dietDays)) : ?>
        <hr />
        <h2>All Diet Tracker Entries</h2>
        <div class="table-scroller">
            <table class="wp-list-table widefat ">
                <thead>
                    <th>Date</th>
                </thead>
                <tbody>
                    <?php foreach($dietDays as $dietDay) : ?>
                        <tr>
                            <td><?php echo date('j M Y', strtotime($dietDay->day)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <hr />
    <?php if($foodLogs && count($foodLogs)): ?>
    <h2>Last <?php echo count($foodLogs);?> Food Logs</h2>
    <?php
    foreach($foodLogs as $log) :
        ?>
        <h3><?php echo date('F d Y', strtotime($log->measure_date));?></h3>
        <?php if($log->food_log_explanation):?>
            <p><strong>Comments:</strong> <?php echo esc_html($log->food_log_explanation); ?></p>
        <?php endif; ?>
        <table class="wp-list-table widefat fixed food">
            <thead>
                <th>&nbsp;</th>
                <?php
                foreach($foodTimes as $foodTime):
                    ?>
                    <th><?php echo $foodTime['name']?></th>
                <?php
                endforeach;
                ?>
                <th style="font-weight:bold">Total</th>
                <th style="font-weight:bold">Target</th>
            </thead>
           
            <tbody>
                <tr>
                <?php foreach($foodTypes as $foodKey => $foodType): ?>
                    <td><?php echo $foodType['name']?></td>
                
                <?php foreach($foodTimes as $timeKey => $foodTime):?>
                    <td><?php echo $log->foodLog[$timeKey][$foodKey]->value;?></td>
                <?php endforeach; ?>
                    <td style="font-weight:bold"><?php echo $log->foodLog[$foodKey . "_total"]; ?></td>
                    <td style="font-weight:bold"><?php echo $log->foodLog[$foodKey . "_target"]; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Descriptions -->
        <?php if($log->foodDescriptions && count($log->foodDescriptions)) :?>
        <h4>Descriptions</h4>

        <table class="wp-list-table widefat food-description">
            <?php foreach($log->foodDescriptions as $description):?>
                <tr>
                    <td class="name"><?php echo $description->time; ?></td>
                    <td class="description"><?php echo $description->description ? esc_html($description->description) : "- -";?>
                </tr>
            <?php endforeach; ?>
        </table>
      <?php endif; ?>
      <hr />
      <?php endforeach;?>
      
     
      
<?php endif; ?>

</div>