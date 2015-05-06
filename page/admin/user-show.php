<div class="wrap">
    <h2>
        Procas User Details
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
        <dd><?php echo $userDetails['passcode_group'] ? $userDetails['passcode_group'] : "- -"; ?></dd>
        <dt>Register Date</dt>
        <dd><?php echo gmdate('d M Y', strtotime($userDetails['user_registered']));?></dd>
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
                echo gmdate('d M Y', strtotime($userDetails['measure_date']));
            else :
                echo "- -";
            endif;
            ?>
        </dd>
        <dt>Initial Weight (Kg)</dt>
        <dd><?php 
            if(isset($userDetails['initial_weight']) && $userDetails['initial_weight']) :
                echo round($userDetails['initial_weight'], 2);
            else :
                echo "- -";
            endif;
        ?></dd>
        <dt>Current Weight (Kg)</dt>
        <dd><?php 
            if(isset($userDetails['weight']) && $userDetails['weight']) :
                echo round($userDetails['weight'], 2);
            else :
                echo "- -";
            endif;
            ?>  
        </dd>
        <dt>Weight Change</dt>
        <dd><?php 
             if(isset($userDetails['weight_change']) && $userDetails['weight_change']) :
                 echo round($userDetails['weight_change'], 2);
             else :
                 echo "- -";
             endif;
            ?>     
        </dt>
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
     <table class="wp-list-table widefat ">
         <thead>
             <th>Date</th>
             <th>Aerobic Exercise Minutes</th>
             <th>Resistance Exercise Minutes</th>
         </thead>
         <tbody>
             <?php foreach($exerciseLogs as $log): ?>
                 <tr>
                     <td><?php echo date( 'j M Y', strtotime($log->measure_date) ); ?></td>
                     <td><?php echo $log->exercise_minutes ? $log->exercise_minutes : "- -";?></td>
                     <td><?php echo $log->exercise_minutes_resistance ? $log->exercise_minutes_resistance : "- -";?></td>
                 </tr>
             <?php endforeach; ?>
             </tbody>
     </table>
        
    <?php endif; ?>
    
    <?php if($dietDays && count($dietDays)) : ?>
        <hr />
        <h2>Last <?php echo count($dietDays) ?> Diet Tracker Entries</h2>
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