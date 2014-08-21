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

	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label"><?php _e('Date of Measurement');?></label></h3>
			
		</div>
		<p class="form-explanation"><span class='js-show'><?php _e('The date when you took this measurement.  Click the field below to select the date on a calendar');?></span><span class="js-hide"><?php echo _e('Enter the date you took this measurement in the format DD-MM-YYYY');?></span></p>
		<?php
		echo $form->input('measure_date', 'text', array(
			'class' => 'general-input date-input'
		));
		?>
	</div>

	
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Weight');?></h3>
			
		</div>
		<?php echo $form->checkbox('record-weight', 1, array(
			'class' => 'question-chooser',
			'id' => 'record-weight'
		));?>
		<label for="record-weight">I would like to record my weight on this occasion</label>
		<div class="inner-question-container weight-container js-hide">
			<div class="question-container clearfix">
				<div class="title">
					<label for="weight_unit" class="general-label"><?php _e('Units');?></label>
					
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
	
			<div class="question-container clearfix">
				<div class="title">
					<label for="weight" class="general-label"><?php _e('Your Weight');?></label>
					
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
			
		</div>
	</div>
	
	
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Restricted Days');?></h3>
		</div>
		<?php echo $form->checkbox('diet-days', 1, array(
			'class' => 'question-chooser',
			'id' => 'diet-days'
		));?>
		<label for="diet-days"><?php _e('I would like to record the number of restricted days I\'ve completed in the last week');?></label>
		<div class="inner-question-container diet-days-container js-hide clearfix">
			<p class="form-explanation"><?php _e('Please mark any restricted days you have done in the last week');?></p>
			<div class="diet-days">
				<?php 
				if($dateListPicker) :
					echo $dateListPicker;
					else :
					?>
				<p class='diet-warn'><?php echo _e('Please select your date of measurement before setting your restricted days');?>
				<?php
				endif;
				?>
			</div>
		</div>
	</div>
	
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Calories');?></h3>
			
		</div>
		<?php echo $form->checkbox('record-calories', 1, array(
			'class' => 'question-chooser',
			'id' => 'record-calories'
		));?>
		<label for="record-calories"><?php _e('I would like to record my calories on this occasion');?></label>
		<div class="inner-question-container calories-container js-hide">
			<div class="question-container clearfix">
				<div class="title">
					<label for="calories" class="general-label"><?php _e('Calories consumed');?></label>
					
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
		</div>
	</div>
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Exercise');?></h3>
			
		</div>
		<?php echo $form->checkbox('record-exercise', 1, array(
			'class' => 'question-chooser',
			'id' => 'record-exercise'
		));?>
			<label for="record-exercise">I would like to record my minutes of exercise on this occasion</label>
		<div class="inner-question-container calories-container js-hide">
			<div class="question-container clearfix">
				<div class="title">
					<label for="exercise_minutes" class="general-label"><?php _e('Minutes of Exercise');?></label>
					
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
		</div>
	</div>
	<div class='button-c-container'>
		<button type="submit" name="action" value="savemeasurement" class="button large green saveform"><?php _e('Save your measurement');?></button>
	</div>
</form>