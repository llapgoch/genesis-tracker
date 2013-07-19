<section class="reading-box main-accent clearfix">
	<h2>Choose a weight target to aim for</h2>
	<p>Enter a target and the date you would like to obtain your target by</p>
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
				<label><?php _e('Date of Target');?></label>
				<div class="title-sep-container"><div class="title-sep"></div></div>
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
			<p class="form-explanation"><?php _e('Enter your weight for the day you are recording');?>
				<?php
				if(isset($weight) && $weight){
					?>
					<em>
						<?php
					echo _e('Your last entered weight is') . ' <span class="metric">' . $weight['metric'] . "</span><span class='js-hide'> / </span><span class='imperial'>" . $weight['imperial'] . "</span>";
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
				<p class="input-suffix imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('stones');?></p>
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