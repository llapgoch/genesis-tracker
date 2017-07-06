<?php 

echo GenesisThemeShortCodes::readingBox("Input your weight, food intake, and minutes of exercise",
	"<p>Click the box below to select the date that you want to enter your details for.</p>");

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
		<p class="form-explanation"><span class='js-show'><?php _e('Click the field below to select the date on a calendar.');?></span></p>
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
    			<h3 class="general-label"><?php _e('Diet Days');?></h3>
    		</div>
    		<?php  echo $form->checkbox('diet-days', 1, array(
    			'class' => 'question-chooser',
    			'id' => 'diet-days'
    		));?>
    	    <label for="diet-days"><?php _e('I would like to record the number of diet days I\'ve completed in the last week');?></label>
    		<div class="inner-question-container diet-days-container js-hide clearfix">
    			<p class="form-explanation"><?php _e('Please mark any diet days you have done in the last week.');?><br /><?php _e('Previously saved diet days for the last week will automatically be shown here.');?></p>
    			<div class="diet-days">
    				<?php 
    				if($dateListPicker) :
    					echo $dateListPicker;
    					else :
    					?>
    				<p class='diet-warn'><?php echo _e('Please select your date of measurement before setting your diet days');?>
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
    		<label for="record-weight">I would like to record my weight for this date</label>
    		<div class="inner-question-container weight-container js-hide">
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="weight_unit" class="general-label"><?php _e('Units');?></label>
    				</div>
		
    				<p class="form-explanation"><?php _e('Would you like your weight to be saved as metric or imperial?');?></p>
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
    			<h3 class="general-label"><?php _e('Diet Tracker');?></h3>
    		</div>
          
    		<?php echo $form->checkbox('record-food', 1, array(
    			'class' => 'question-chooser',
    			'id' => 'record-food'
    		));?>
    		<label for="record-food"><?php _e('I would like to record my food and drink portions for this date');?></label>
    		<div class="inner-question-container food-container js-hide">
                
    			<div class="question-container clearfix">
                      <div class="form-explanation">
                          <p><?php _e('You may choose to keep a log of the different foods and drinks you consume on a daily, weekly or monthly basis. This log can be used to see how closely you match your food group targets for a healthy balanced diet. We know that people who monitor their intake are more likely to choose healthy foods and to be successful at weight loss.');?></p>
                      
                          <ul>
                              <li><strong>Step 1:</strong> Make a list of the meals, snacks and drinks you consumed today</li>
                              <li><strong>Step 2:</strong> Match the foods with the food groups and record how many portions you had using the portion guide (as shown in the example breakfast below).</li>
                          </ul>
                          
                          <div class="example">
                              <div class="title">
                                  <label for="example" class="general-label">Breakfast Example:</label>
                              </div>
                               <p class="example-explanation">2 Weetabix, 100 mls of semi-skimmed milk, 150mls of orange juice</p>
                              <div class="food-input-form">
                                <div class="input-box">
                                    <label for="example_fat">Carbohydrate</label>
                                    <div class="input-container fat">
                                        <input type="text" readonly="readonly" id="example_fat" class="general-input food-input" name="example_fat" value="2">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_protein">Protein</label>
                                    <div class="input-container protein">
                                        <input type="text" readonly="readonly" id="example_protein" class="general-input food-input" name="example_protein" value="0">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_carbs">Dairy</label>
                                    <div class="input-container carbs">
                                        <input type="text" readonly="readonly" id="example_carbs" class="general-input food-input" name="example_carbs" value="0.5">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_fruit">Vegetables</label>
                                    <div class="input-container fruit">
                                        <input type="text" readonly="readonly" id="example_fruit" class="general-input food-input" name="example_fruit" value="0">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_vegetables">Fruit</label>
                                    <div class="input-container vegetables">
                                        <input type="text" readonly="readonly" id="example_vegetables" class="general-input food-input" name="example_vegetables" value="1">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_dairy">Fat</label>
                                    <div class="input-container dairy">
                                        <input type="text" readonly="readonly" id="example_dairy" class="general-input food-input" name="example_dairy" value="0">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_alcohol">Treat</label>
                                    <div class="input-container alcohol">
                                        <input type="text" readonly="readonly" id="example_alcohol" class="general-input food-input" name="example_alcohol" value="0">
                                    </div>
                                </div>
                                <div class="input-box">
                                    <label for="example_treat">Alcohol</label>
                                    <div class="input-container treat">
                                        <input type="text" readonly="readonly" id="example_treat" class="general-input food-input" name="example_treat" value="0">
                                    </div>
                                </div>
                            </div>

                          </div>
                      </div>
    				<div class="title">
    					<label for="breakfast" class="general-label"><?php _e('Breakfast');?></label>
    				</div>
    	
    	            <?php echo GenesisUserInputTable::getFoodInputTableHTML('breakfast', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="lunch" class="general-label"><?php _e('Lunch');?></label>
    				</div>
    				
                      <?php echo GenesisUserInputTable::getFoodInputTableHTML('lunch', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="evening" class="general-label"><?php _e('Evening');?></label>
    				</div>
    				
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('evening', $form); ?>
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="snacks" class="general-label"><?php _e('Snacks');?></label>
    				</div>
                     
    				<?php
                    echo GenesisUserInputTable::getFoodInputTableHTML('snacks', $form); 
                    ?>
                
    			</div>
            
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="drinks" class="general-label"><?php _e('Drinks');?></label>
    				</div>
    
                    <?php echo GenesisUserInputTable::getFoodInputTableHTML('drinks', $form); ?>
    			</div>
            
    			<div class="question-container clearfix totals">
    				<div class="title">
    					<label for="total" class="general-label"><?php _e('Your total portions for this day');?></label>
    				</div>
                    <div class="food-input-form">
                        <?php $_foods = GenesisTracker::getUserMetaTargetFields(); ?>
                    
                        <?php foreach($_foods as $_foodIdentifier => $_food): ?>  
                            <?php $fullKey = "total_" . $_foodIdentifier; ?>  
                            <div class='input-box total-box' data-total-type="<?php echo $_foodIdentifier;?>">
                                <label for='<?php echo $fullKey; ?>'><?php echo $_food['name'];?></label>
                                
                                <span class="value">0</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
    			</div>
    			
                <?php $_foods = GenesisTracker::getUserMetaTargetFields(); ?>
    			<div class="question-container clearfix targets">
    				<div class="title">
    					<label for="targets" class="general-label"><?php _e('Your 2 Diet Days Targets');?></label>
    				</div>
                    
                    <div class="food-input-form">
                    <?php foreach($_foods as $_foodIdentifier => $_food): ?>  

                        <div class='input-box'>
                            <label><?php echo $_food['name'];?></label>
                            <span class="value">
                                <?php
                                echo $_food['med'];
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    
    				<div class="title">
    					<label for="targets" class="general-label"><?php _e('Your Mediterranean Days Targets');?></label>
    				</div>
                    <div class="food-input-form">
                        
                    
                        <?php foreach($_foods as $_foodIdentifier => $_food): ?>  
                            <?php $fullKey = "total_" . $_foodIdentifier; ?>  
                            <div class='input-box <?php echo $_foodIdentifier;?>'>
                                <label for='<?php echo $fullKey; ?>'><?php echo $_food['name'];?></label>
                                <span class="value">
                                    <?php
                                    if($_target = GenesisTracker::getUserTargetLabel($_foodIdentifier)) :
                                        echo $_target;
                                    else :
                                        echo "--";
                                    endif;
                                    ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
    			<label for="record-exercise">I would like to record my minutes of exercise for this date</label>
    		<div class="inner-question-container exercise-container js-hide">
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="exercise_minutes" class="general-label"><?php _e('Minutes of Aerobic Exercise');?></label>
					
    				</div>

					<div class="question-container-small">
						<p class="form-explanation"><?php _e('Enter the minutes of aerobic exercise you completed and the type of exercise it was');?></p>

						<?php
						echo $form->input('exercise_minutes', 'text', array(
							'id' => 'exercise_minutes',
							'class' => 'general-input'
						));
						?>
						<p class="input-suffix"><?php _e('minutes');?></p>

						<?php
						echo $form->dropdown('exercise_type',
							$exerciseTypes,
							array()
						);
						?>
					</div>
					<div class="question-container-small">
						<p class="form-explanation"><?php _e('Enter a description for this exercise');?></p>
						<?php
						echo $form->textarea('exercise_description', array(
							'default' => "",
							'cols' => 30,
							'rows' => 5,
							'class' => 'general-input large-input'
						));
						?>
					</div>
    			</div>
                
    			<div class="question-container clearfix">
    				<div class="title">
    					<label for="exercise_minutes_resistance" class="general-label"><?php _e('Minutes of Resistance Exercise');?></label>
					
    				</div>
					<div class="question-container-small">
						<p class="form-explanation"><?php _e('Enter the minutes of resistance exercise you completed and the type of exercise it was');?></p>
						<?php
						echo $form->input('exercise_minutes_resistance', 'text', array(
							'id' => 'exercise_minutes_resistance',
							'class' => 'general-input'
						));
						?>
						<p class="input-suffix"><?php _e('minutes');?></p>

						<?php
						echo $form->dropdown('exercise_type_resistance',
							$exerciseTypes,
							array()
						);
						?>
					</div>

					<div class="question-container-small">
						<p class="form-explanation"><?php _e('Enter a description for this exercise');?></p>
						<?php
						echo $form->textarea('exercise_description_resistance', array(
							'default' => "",
							'cols' => 30,
							'rows' => 5,
							'class' => 'general-input large-input'
						));
						?>
					</div>
    			</div>
    		</div>
    	</div>
    
    	<div class='button-c-container'>
    		<button type="submit" name="action" value="savemeasurement" class="button large green saveform"><?php _e('Save your measurement');?></button>
    	</div>
    </div>
</form>