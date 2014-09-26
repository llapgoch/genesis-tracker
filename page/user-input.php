<?php 

echo GenesisThemeShortCodes::readingBox("Track your weight, food consumption, and minutes exercised",
	"<p>Select the date you would like to track from the calendar field, enter your weight in imperial or metric, your food groups, and the minutes of exercise you have done. If you would like to change a previously saved entry, you will be asked if you would like to overwrite it.</p>");

echo GenesisThemeShortCodes::generateErrorBox(GenesisTracker::$pageData);
?>
	
    
<noscript>
     <div class="alert error"><?php _e('Please enable Javascript in your browser to use the food tracker')?></div>
</noscript>

<form class="input-form user-tracking-input js-show" action="" method="post" name="input-form">

	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label"><?php _e('Date of Measurement');?></label></h3>
			
		</div>
		<p class="form-explanation"><span class='js-show'><?php _e('The date when you took this measurement.  Click the field below to select the date on a calendar.  If you have previously saved values for this date, they will be loaded below.');?></span></p>
		<?php
		echo $form->input('measure_date', 'text', array(
			'class' => 'general-input date-input',
            'readonly' => 'readonly'
		));
		?>
	</div>

	<div class="user-measurements">
    	<div class="question-outer-container">
    		<div class="title">
    			<h3 class="general-label"><?php _e('Restricted Days');?></h3>
    		</div>
    		<?php  echo $form->checkbox('diet-days', 1, array(
    			'class' => 'question-chooser',
    			'id' => 'diet-days'
    		));?>
    	    <label for="diet-days"><?php _e('I would like to record the number of restricted days I\'ve completed in the last week');?></label>
    		<div class="inner-question-container diet-days-container js-hide clearfix">
    			<p class="form-explanation"><?php _e('Please mark any restricted days you have done in the last week.');?><br /><?php _e('Previously saved restricted days for the last week will automatically be shown here.');?></p>
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
		
    				<p class="form-explanation"><?php _e('Whether you would like to enter your weight as metric or imperial units');?></p>
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
    					<p class="input-suffix weight metric <?php echo (!$metricUnits ? 'hidden' : '');?>"><?php _e('kilograms');?></p>
    					<p class="input-suffix weight imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('stone');?></p>
    				</div>
    				<div class="input-wrapper">
    					<?php
    					echo $form->input('weight_pounds', 'text', array(
    						'class' => 'general-input weight-input weight imperial  ' . ($metricUnits ? "hidden" : ""),
    						'id' => 'weight-pounds'
    						));
    					?>
		
    					<p class="input-suffix weight imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('pounds');?></p>
    				</div>				
    			</div> 
    		</div>
    	</div>
    
    
    	<div class="question-outer-container record-food-container">
    		<div class="title">
    			<h3 class="general-label"><?php _e('Food Groups');?></h3>
    		</div>
          
    		<?php echo $form->checkbox('record-food', 1, array(
    			'class' => 'question-chooser',
    			'id' => 'record-food'
    		));?>
    		<label for="record-food"><?php _e('I would like to record my food groups on this occasion');?></label>
    		<div class="inner-question-container food-container js-hide">
                <!-- <div class="alert notice"><a href="https://www.myfood24.org/login" target="_blank">Find out the portions you've consumed using Food 24's Website <em>(opens in a new window)</em></a></div> -->
                
    			<div class="question-container clearfix">
                      <p class="form-explanation"><?php _e('Enter any of the following food groups you have consumed.  You can enter as much or as little information as you like, and then come back and add more or amend it later');?></p>
    				<div class="title">
    					<label for="fat" class="general-label"><?php _e('Fat portions consumed');?></label>
    				</div>
    	
    	            <?php echo GenesisUserInputTable::getFoodInputTableHTML('fat', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="carbs" class="general-label"><?php _e('Carbohydrate portions consumed');?></label>
    				</div>
    				
                      <?php echo GenesisUserInputTable::getFoodInputTableHTML('carbs', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="protein" class="general-label"><?php _e('Protein portions consumed');?></label>
    				</div>
    				
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('protein', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="fruit" class="general-label"><?php _e('Fruit portions consumed');?></label>
    				</div>
                     
    				<?php
                    echo GenesisUserInputTable::getFoodInputTableHTML('fruit', $form); 
                    ?>
                
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="vegetables" class="general-label"><?php _e('Vegetable portions consumed');?></label>
    				</div>
    
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('vegetables', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="dairy" class="general-label"><?php _e('Dairy portions consumed');?></label>
    				</div>
  
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('dairy', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="alcohol" class="general-label"><?php _e('Alcohol units consumed');?></label>
    				</div>
    	
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('alcohol', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="treat" class="general-label"><?php _e('Treats consumed');?></label>
    				</div>
    
                   <?php echo GenesisUserInputTable::getFoodInputTableHTML('treat', $form); ?>
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
    		<div class="inner-question-container exercise-container js-hide">
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="exercise_minutes" class="general-label"><?php _e('Minutes of Exercise');?></label>
					
    				</div>
    				<p class="form-explanation"><?php _e('Enter the minutes of exercise you completed on the day you are recording');?></p>
    				<?php
    				echo $form->input('exercise_minutes', 'text', array(
    					'id' => 'exercise_minutes',
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
    </div>
</form>