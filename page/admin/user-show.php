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
        <dd><?php echo $userDetails[GenesisTracker::passcodeGroupCol] ? $userDetails[GenesisTracker::passcodeGroupCol] : "- -"; ?></dd>
        <dt>Register Date</dt>
        <dd><?php echo gmdate('d M Y', strtotime($userDetails['user_registered']));?></dd>
        <dt>Activation Date</dt>
        <dd><?php echo gmdate('d M Y', strtotime($userDetails[GenesisTracker::userStartDateCol]));?></dd>
        <dt>Actual Start Date</dt>
        <dd><?php echo gmdate('d M Y', strtotime($userDetails['actual_start_date']));?></dd>
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
            if(isset($userDetails['initial_weight']) && $userDetails['initial_weight']) :
                echo round($userDetails['initial_weight'], 4);
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
		<dt>Six Month Date</dt>
		<dd><?php echo ($userDetails[GenesisTracker::sixMonthDateCol] ? GenesisTracker::prettyDBDate($userDetails[GenesisTracker::sixMonthDateCol]) : "- -");?></dd>
		<dt>Six Month Weight (Kg)</dt>
		<dd><?php echo ($userDetails['six_month_weight'] ? $userDetails['six_month_weight'] : "- -");?></dd>
		<dt>Benchmark Weight (Kg)</dt>
		<dd><?php echo ($userDetails['benchmark_weight'] ? round($userDetails['benchmark_weight'], 4) : "- -"); ?></dd>
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
				<?php if($dateSent = $userDetails['red_flag_email_date']): ?>
					<span style="font-style:italic;margin-left:5px">Sent at: <strong><?php echo GenesisTracker::convertDBDatetime($userDetails['red_flag_email_date']); ?></strong></span>
				<?php endif; ?>
			</form>
		<dt>Week Number</dt>
		<dd><?php echo $userDetails['weeks_registered'] ? $userDetails['weeks_registered'] : "- -";?></dd>
		<dt>Four Weekly Emails</dt>
		<dd>
			<?php if($userDetails['four_weekly_date']): ?>
					<em><strong>This user was last sent a four weekly email on the <?php echo $userDetails['four_weekly_date']?></strong></em>
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
		<?php if(!$userDetails['four_weekly_date'] && !$userDetails['four_week_required_to_send']): ?>
			<em><strong>This user is not eligible for a four weekly email. Either:</strong></em>
            <ul style="list-style:circle">
                <li>They have withdrawn</li>
                <li>They haven't been subscribed for long enough</li>
                <li>They haven't been given a six month weight</li>
                <li>They have opted out of 6 - 12 month reminder emails</li>
                <li>They have been subscribed for a year or more (the Monday after their activation date + 52 weeks)</li>
                <li>The weeks they've been registered is not one of the four week email points</li>
            </ul>
		<?php endif; ?>
		</dd>
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