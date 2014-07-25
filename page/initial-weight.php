<section class="reading-box main-accent clearfix">
	<h2>Thank you for participating in Genesis' clinical trial</h2>
	<p>Before we can get you up and running we just need to know what your current weight is.</p>
	<div class="tagline-shadow"></div>
</section>

<?php if(isset(GenesisTracker::$pageData['errors'])) : ?>
	<div class="alert error spaced"><div class="msg">
		<?php echo implode("<br />", GenesisTracker::$pageData['errors']);?>
	</div></div>
<?php
endif;
?>

<form class="input-form user-tracking-input" action="" method="post" name="input-form">
	<div class="question-outer-container clearfix">
		<div class="title">
			<h3 class="general-label"><?php _e('Weight Units');?></h3>
			
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
	
	<div class="question-outer-container clearfix">
		<div class="title">
			<h3 class="general-label"><?php _e('Your Starting Weight');?></h3>
			
		</div>
		<p class="form-explanation"><?php _e('Enter what your weight is at the start of the trial');?></p>
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
	<div class='button-c-container'>
		<button type="submit" name="action" value="saveinitialweight" class="button large green saveform"><?php _e('Save your starting weight');?></button>
	</div>
</form>
