<?php
echo GenesisThemeShortCodes::readingBox(
    "Choose a weight target to aim for",
    "Enter a target and the date you would like to obtain your target by"
);

echo GenesisThemeShortCodes::generateErrorBox(GenesisTracker::$pageData);
?>


	<form class="input-form user-tracking-input" action="" method="post" name="input-form">
		
		<div class="question-container">
			<div class="title">
				<label><?php _e('Date of Target');?></label>
				
			</div>
			<p class="form-explanation"><?php _e('The date when you would like to acheive your target weight by');?></p>
			<?php
			echo $form->input('target_date', 'text', array(
				'class' => 'general-input date-input'
			));
			?>
		</div>

		<div class="question-container">
			<div class="title">
				<label for="weight_unit"><?php _e('Weight Units');?></label>
				
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
				
			</div>
			<p class="form-explanation"><?php _e('Enter the target weight you\'d like to be on the date you\'ve chosen');?>
				<?php
				if(isset($weight) && $weight){
					?>
					<em>
						<?php
					echo _e('The last weight you recorded for yourself was') . ' <span class="metric">' . $weight['metric'] . "</span><span class='js-hide'> / </span><span class='imperial'>" . $weight['imperial'] . "</span>";
					?>
					</em> 
					<?php
				}
				?>
			</p>
			
		
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
		<div class="button-c-container">
				<button type="submit" name="action" value="savetarget" class="button large green saveform">Save your target</button>
			</div>
	</form>
</div>