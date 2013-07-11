<form class="input-form" action="" method="post" name="input-form">
	<div class="question-container">
		<div class="title">
			<label>Date of Measurement</label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation">The date when you took this measurement.  Click the calendar button below to select the date on a calendar</p>
		<?php
		echo $form->input('measure_data', 'text', array(
			'class' => 'date-measure'
		));
		?>
	</div>

	<div class="question-container">
		<div class="title">
			<label for="weight_unit">Weight Units</label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation">Whether you would like your weight to be saved as metric or imperial units</p>
		<?php
		echo $form->dropdown('weight_unit', array(
				'1' => 'Stone and Pounds',
				'2' => 'Kilograms'
			));
		?>
		
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="weight">Weight</label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation">Enter your weight for the day you are recording</p>
		<?php
		echo $form->input('weight', 'text', array(
			'class' => 'general-input',
			'id' => 'weight'
		));
		?>
		<p class="input-suffix">kg / stone</p>
		<div class="form-input-error-container">
			<span class="form-input-error">This field is required</span>
		</div>
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="calories">Calories consumed</label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation">Enter the amount of calories you consumed on the day you are recording</p>
		<?php
		echo $form->input('calories', 'text', array(
			'id' => 'calories',
			'class' => 'general-input'
		));
		?>
		<p class="input-suffix">kcals</p>
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="exercise_minutes">Minutes of Exercise</label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation">Enter the minutes of exercise you completed on the day you are recording</p>
		<?php
		echo $form->input('exercise_minutes', 'text', array(
			'id' => 'calories',
			'class' => 'general-input'
		));
			
		
		?>
		<p class="input-suffix">minutes</p>
	</div>
	<div class='button-c-container'>
		<input type="hidden" name="action" value="savemeasurement" />
		<button type="submit" class="button large green">Save your measurement</button>
	</div>
</form>