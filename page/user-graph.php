<div class="progress-graph-switcher">
	<div class="button-container">
        <div class="button-group measurement">
            <h2>My Progress</h2>
            <div class="button-row">
                <button class="pink button large" data-mode="weight">Weight</button>
                <button class="orange button large" data-mode="exercise_minutes">Aerobic Exercise</button>
                <button class="green button large" data-mode="exercise_minutes_resistance">Resistance Exercise</button>
    	        <div class="extended-button">
                    <button class="purple button large" data-mode="weight_loss">Weight Progress</button>
                    <!-- <div class="averages">
                        <input type="checkbox" name="averages" id="averages" />
                        <label for="averages"><?php _e('Average weight loss for other women on the study'); ?></label>
                    </div> -->
                </div>
            </div>
        	
        </div>
    
        <div class="button-group food">
            <h2>Diet Tracker</h2>
            <button class="blue button large" data-mode="unrestricted-days">View Your Most Recent Logs</button>
        </div>
    </div>
    
	
</div>

<div class="data-container user-data-container">
    <div class="genesis-graph-container">
        <div class="graph-top">
        	<select class="mode-switcher weight-unit">
        		<option value="1">Stone / Pounds</option>
        		<option value="2">Kilograms</option>
        	</select>
            <!-- if displays need to add a legend to the graph -->
            <div class="graph-legend"><h3>This is your weight change since you started the study</h3></div>
        </div>
    	
    	<div class="genesis-progress-graph">
    	</div>
        
        <!-- no results for the graph -->
    	<div class="alert-warning fusion-alert no-results alert notice graph-warning">
    		<div class="msg">
    			<h2>There are no results available for your selection</h2>
    			<a href="<?php echo $userInputPage;?>">Record a measurement now</a>
    		</div>
    	</div>
        
        <div class="zoomer">
        	<button class="button blue in">Zoom In</button>
        	<button class="button blue out">Zoom Out</button>
        </div>
    </div>
    
    <div class="genesis-food-table-container">
        <?php if($foodLogData && count($foodLogData)): ?>
        <h3>Your Last <?php echo count($foodLogData) > 1 ? count($foodLogData) : "";?> Diet Tracker Entr<?php echo count($foodLogData) == 1 ? "y" : "ies";?></h3>
        <table class="progress-food-log responsive">
            <thead>
                <th>&nbsp;</th>
                <?php foreach($foodTypes as $foodType => $food) : ?>
                    <th><span><?php echo $food['name'];?><span></th>
                <?php endforeach; ?>
            </thead>
            <tbody>
                
                <?php foreach($foodLogData as $data):?>
                    <tr>
                    <td><?php echo date('d F Y', strtotime($data->measure_date)); ?>
                        <?php foreach($foodTypes as $foodType => $food) : ?>
                            <td><?php echo $data->$foodType ?  (float) $data->$foodType : 0;?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <?php else : ?>
        	<div class="alert-warning fusion-alert alert notice">
        		<div class="msg">
        			<h2>You haven't saved any unrestricted diet tracker entries yet</h2>
        			<a href="<?php echo $userInputPage;?>">Record one now</a>
        		</div>
        	</div>
        <?php endif;?>
    </div>
    
   
</div>


<?php if($weightChangeInButter != 0) : ?>
<div class="butter weight-loss-example">
	<p>You have <?php echo $weightChangeInButter > 0 ? "gained" : "lost";?> the equivalent of <em><?php echo abs($weightChangeInButter); ?> packs of butter</em> since starting the diet and exercise plan</p>
</div>
<?php endif; ?>