<div class="progress-graph-switcher">
	<div class="button-container">
        <div class="button-group measurement">
            <h2>Measurements</h2>
            <div class="button-row">
                <button class="pink button large" data-mode="weight">Weight</button>
                <button class="orange button large" data-mode="exercise_minutes">Exercise</button>
    	        <div class="extended-button">
                    <button class="green button large" data-mode="weight_loss">Weight Progress</button>
                    <div class="averages">                        
                        <input type="checkbox" name="averages" id="averages" />
                        <label for="averages"><?php _e('Show all user averages'); ?></label>
                    </div>
                </div>
            </div>
        	
        </div>
    
        <div class="button-group food">
            <h2>Food Group Portions</h2>
            <button class="red button small" data-mode="fat">Fat</button>
            <button class="darkgray button small" data-mode="carbs">Carbohydrates</button>
            <button class="blue button small" data-mode="protein">Protein</button>
            
            <button class="pink button small" data-mode="fruit">Fruit</button>
            <button class="orange button small" data-mode="vegetables">Vegetables</button>
            <button class="green button small" data-mode="dairy">Dairy</button>
            
            <button class="red button small" data-mode="alcohol">Alcohol</button>
            <button class="darkgray button small" data-mode="treat">Treats</button>
        </div>
    </div>
    
	
</div>

<div class="genesis-graph-container">
    <div class="graph-top">
    	<select class="mode-switcher weight-unit">
    		<option value="1">Stone / Pounds</option>
    		<option value="2">Kilograms</option>
    	</select>
        <!-- if displays need to add a legend to the graph -->
        <div class="graph-legend"><h3>This is your weight change since you started the study</h3></div>
    </div>
	<div class="alert-warning fusion-alert no-results alert notice">
		<div class="msg">
			<h2>There are no results available for your selection</h2>
			<a href="<?php echo $userInputPage;?>">Record a measurement now</a>
		</div>
	</div>
	<div class="genesis-progress-graph">
	</div>
</div>

<div class="zoomer">
	<button class="button blue in">Zoom In</button>
	<button class="button blue out">Zoom Out</button>
</div>


<?php if($weightChangeInButter != 0) : ?>
<div class="butter weight-loss-example">
	<p>You have lost the equivalent <em><?php echo $weightChangeInButter; ?> packs of butter!</em></p>
</div>
<?php endif; ?>