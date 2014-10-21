<div class="wrap">
    <h2>
        Procas User Details
        <a class="add-new-h2" href="<?php echo $userEditLink;?>">Edit User</a>
    </h2>
    
    <dl class="admin-list">
        <dt>Email Address</dt>
        <dd><?php echo $user->user_email;?></dd>
        <dt>Telephone Number</dt>
        <dd><?php echo $userTelephone; ?></dd>
        <dt>Account Active</dt>
        <dd><?php echo (int) $userDetails['account_active'] == 0  ? 'No' : 'Yes';?></dd>
        <dt>Last Measurement Date</dt>
        <dd><?php 
            if(isset($userDetails['measure_date']) && $userDetails['measure_date']) :
                echo date('d F Y', strtotime($userDetails['measure_date']));
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
            </thead>
            <tfoot>
                <tr>
                    <td>Total</td>
                    
                        <?php foreach($foodTimes as $timeKey => $foodTime):?>
                            <td><?php echo $log->foodLog[$timeKey]['total'];?></td>
                        <?php endforeach; ?>
                    
                </tr>
            </tfoot>
            <tbody>
                <tr>
                <?php foreach($foodTypes as $foodKey => $foodType): ?>
                    <td><?php echo $foodType['name']?></td>
                
                <?php foreach($foodTimes as $timeKey => $foodTime):?>
                    <td><?php echo $log->foodLog[$timeKey][$foodKey]->value;?></td>
                <?php endforeach; ?>
                
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