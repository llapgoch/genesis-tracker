<section class="reading-box main-accent clearfix">
	<h2>Track your weight, calorie intake and minutes exercised</h2>
	<p>Select the date you would like to track from the calendar field, enter your weight in imperial or metric, your calories consumed, and the minutes of exercise you have done. If you would like to overwrite a previously saved entry, you will be asked if you would like to overwrite it.</p>
	<div class="tagline-shadow"></div></section>
	
	<?php
	if(isset(GenesisTracker::$pageData['errors'])){
		?>
		<div class="alert error spaced"><div class="msg">
			<?php echo implode("<br />", GenesisTracker::$pageData['errors']);?>
		</div></div>
		<?php
	}
	?>
	
<form class="input-form user-tracking-input" action="" method="post" name="input-form">
	<div class="question-container">
		<div class="title">
			<label><?php _e('Date of Measurement');?></label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation"><span class='js-show'><?php _e('The date when you took this measurement.  Click the field below to select the date on a calendar');?></span><span class="js-hide"><?php echo _e('Enter the date you took this measurement in the format DD-MM-YYYY');?></span></p>
		<?php
		echo $form->input('measure_date', 'text', array(
			'class' => 'general-input date-input'
		));
		?>
	</div>

	<div class="question-container">
		<div class="title">
			<label for="weight_unit"><?php _e('Weight Units');?></label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation"><?php _e('Whether you would like your weight to be saved as metric or imperial units');?></p>
		<?php
		echo $form->dropdown('weight_unit', array(
				'1' => 'Stone and Pounds',
				'2' => 'Kilograms'
			), array(
				'class' => 'weight-unit'
			));
		?> 
		<button type="submit" name="action" value="changeunits" class="changeunits">Set</button>
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="weight"><?php _e('Weight');?></label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation"><?php _e('Enter your weight for the day you are recording');?></p>
		
		<div class="input-wrapper">
			<?php
			echo $form->input('weight_main', 'text', array(
				'class' => 'general-input weight-input',
				'id' => 'weight-main'
				));
			?>
			<p class="input-suffix metric <?php echo (!$metricUnits ? 'hidden' : '');?>"><?php _e('kilograms');?></p>
			<p class="input-suffix imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('stone');?></p>
		</div>
		<div class="input-wrapper">
			<?php
			echo $form->input('weight_pounds', 'text', array(
				'class' => 'general-input weight-input imperial ' . ($metricUnits ? "hidden" : ""),
				'id' => 'weight-pounds'
				));
			?>
		
			<p class="input-suffix imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('pounds');?></p>
		</div>
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="calories"><?php _e('Calories consumed');?></label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation"><?php _e('Enter the amount of calories you consumed on the day you are recording');?></p>
		<?php
		echo $form->input('calories', 'text', array(
			'id' => 'calories',
			'class' => 'general-input'
		));
		?>
		<p class="input-suffix"><?php _e('kcals');?></p>
	</div>
	
	<div class="question-container">
		<div class="title">
			<label for="exercise_minutes"><?php _e('Minutes of Exercise');?></label>
			<div class="title-sep-container"><div class="title-sep"></div></div>
		</div>
		<p class="form-explanation"><?php _e('Enter the minutes of exercise you completed on the day you are recording');?></p>
		<?php
		echo $form->input('exercise_minutes', 'text', array(
			'id' => 'calories',
			'class' => 'general-input'
		));
			
		
		?>
		<p class="input-suffix"><?php _e('minutes');?></p>
	</div>
	<div class='button-c-container'>
		<button type="submit" name="action" value="savemeasurement" class="button large green saveform"><?php _e('Save your measurement');?></button>
	</div>
</form>