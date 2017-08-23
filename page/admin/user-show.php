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
        <dt>Week Number</dt>
        <dd><?php echo $userDetails['weeks_registered'] ? $userDetails['weeks_registered'] : "- -";?></dd>
    </dl>
    
    
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
             <th>Aerobic Exercise</th>
             <th>Resistance Exercise</th>
         </thead>
         <tbody>
             <?php foreach($exerciseLogs as $log): ?>
                 <tr>
                     <td><?php echo date( 'j M Y', strtotime($log->measure_date) ); ?></td>
                     <td>
                         <?php echo $log->exercise_minutes ? $log->exercise_minutes . " minutes" : "- -";?>
                         <?php echo isset($exerciseTypes[$log->exercise_type]) ? "<br /> <strong>Type: " . $exerciseTypes[$log->exercise_type]['name'] . "</strong>" : ""; ?>
                         <?php echo $log->exercise_description ? "<br /> <small>" . esc_html($log->exercise_description) . "</small>" : ""; ?>
                     </td>
                     <td>
                         <?php echo $log->exercise_minutes_resistance ? $log->exercise_minutes_resistance . " minutes"  : "- -";?>
                         <?php echo isset($resistanceExerciseTypes[$log->exercise_type_resistance]) ? "<br /> <strong>Type: " . $resistanceExerciseTypes[$log->exercise_type_resistance]['name'] . "</strong>" : ""; ?>
                         <?php echo $log->exercise_description_resistance ? "<br /> <small>" . esc_html($log->exercise_description_resistance) . "</small>" : ""; ?>
                     </td>
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