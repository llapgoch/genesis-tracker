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
        <dd><?php echo $userDetails['account_active'] ? 'Yes' : 'No';?></dd>
        <dt>Last Measurement Date</dt>
        <dd><?php echo date('d F Y', strtotime($userDetails['measure_date']));?></dd>
        <dt>Initial Weight (Kg)</dt>
        <dd><?php echo $userDetails['initial_weight'];?></dd>
        <dt>Current Weight (Kg)</dt>
        <dd><?php echo $userDetails['weight'];?></dd>
        <dt>Weight Change</dt>
        <dd><?php echo $userDetails['weight_change'];?></dt>
    </dl>
    
    <?php if($foodLogs && count($foodLogs)): ?>
    <h2>Last <?php echo count($foodLogs);?> Food Logs</h2>
    
    <?php endif;?>

</div>