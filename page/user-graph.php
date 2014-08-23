<div class="progress-graph-switcher">
	<button class="pink button large" data-mode="weight">Weight</button>
	<button class="blue button large" data-mode="calories">Calories</button>
	<button class="orange button large" data-mode="exercise_minutes">Exercise</button>
	<button class="green button large" data-mode="weight_loss">Weight Progress</button>
	<select class="mode-switcher weight-unit">
		<option value="1">Stone / Pounds</option>
		<option value="2">Kilograms</option>
	</select>
</div>
<div class="genesis-graph-container">
	<div class="no-results alert notice">
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