<?php
echo GenesisThemeShortCodes::readingBox(
    "Welcome to the Genesis Procas Lifestyle Study",
    "First, let's check that the clinical trial is for you.  Please read <a href='$eligibilityPdfUrl' target='_blank'>this document</a> before answering the eligibility questions below."
);

echo GenesisThemeShortCodes::generateErrorBox(GenesisTracker::$pageData);

?>


<form class="input-form eligibility-form" action="" method="post" name="eligibility">
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Firstly, Please Enter Your details');?></h3>
		</div>
	
		
		<div class="inner-question-container weight-container">
			
            	
			<div class="question-container clearfix">
				<div class="title">
					<label for="weight" class="general-label">
                        <?php _e('Your Weight');?>
                    </label>
				</div>
                
				<p class="form-explanation">
                    <a href="javascript:;" class="fa fa-question-circle help-icon weight-help" title="<strong>Simple tips for accurate weight measurements</strong><ul><li>Use the same set of <strong>reliable</strong> scales</li><li>Stand bathroom scales on an on a <strong>hard, level floor</strong> –it is best not to place them on carpet</li><li><strong>Remove your clothes and shoes</strong> before weighing yourself or wear light clothes only</li></ul>"></a>
                    <?php _e('Please enter your current weight.  You can switch between imperial and metric values.');?></p>
                <div class="unit-switcher">
                    <?php
    				echo $form->dropdown('weight_unit', array(
    						'1' => 'Stone and Pounds',
    						'2' => 'Kilograms'
    					), array(
    						'class' => 'weight-unit'
    					));
                    ?>
                </div>
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
            
            
			<div class="question-container clearfix">
				<div class="title">
					<label for="weight" class="general-label"><?php _e('Your Height');?></label>
				</div>
				<p class="form-explanation"><?php _e('Please enter your current height.  You can switch between imperial and metric values.');?></p>
	            <div class="unit-switcher">
                    <?php
    				echo $form->dropdown('height_unit', array(
    						'1' => 'Feet and Inches',
    						'2' => 'Metres'
    					), array(
    						'class' => 'height-unit'
    					));
                    ?>
                </div>
				<div class="input-wrapper">
					<?php
					echo $form->input('height_main', 'text', array(
						'class' => 'general-input height-input height',
						'id' => 'height-main'
						));
					?>
					<p class="input-suffix height metric <?php echo (!$metricUnits ? 'hidden' : '');?>"><?php _e('metres');?></p>
					<p class="input-suffix height imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('feet');?></p>
				</div>
				<div class="input-wrapper">
					<?php
					echo $form->input('height_inches', 'text', array(
						'class' => 'general-input height-input height imperial ' . ($metricUnits ? "hidden" : ""),
						'id' => 'height-inches'
						));
					?>
	
					<p class="input-suffix height imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('inches');?></p>
				</div>				
			</div>
		</div>
	</div>
    
    
	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label" for="question-one"><?php _e('Question One');?></label></h3>
		</div>
    
         <p class="form-explanation"><?php _e('Question one description');?></p>
        <?php
        echo $form->dropdown('question_one', array(
            '' => '--- Please Select ---',
            "1" => 'Yes',
            "2" => 'No'
        ), array(
            'default' => ''
        ));
        ?>
    </div>
    
    
	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label" for="question-one"><?php _e('Question Two');?></label></h3>
		</div>
    
     <p class="form-explanation"><?php _e('Question two description');?></p>
        <?php
        echo $form->dropdown('question_two', array(
            '' => '--- Please Select ---',
            "1" => 'Yes',
            "2" => 'No'
        ), array(
            'default' => ''
        ));
        ?>
    </div>
    
    
	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label" for="question-one"><?php _e('Question Three');?></label></h3>
		</div>
    
     <p class="form-explanation"><?php _e('Question three description');?></p>
        <?php
        echo $form->dropdown('question_three', array(
            '' => '--- Please Select ---',
            "1" => 'Yes',
            "2" => 'No'
        ), array(
            'default' => ''
        ));
        ?>
    </div>
    
	<div class="question-outer-container">
		<div class="title">
			<h3><label class="general-label" for="password"><?php _e('Passcode');?></label></h3>
		</div>
    
     <p class="form-explanation"><?php _e('You will have been sent a passcode with your welcome letter. Please enter it here.');?></p>
        <?php
        echo $form->input('passcode', 'password', array(
			'class' => 'general-input'
        ));
        ?>
    </div>
    
    <input type="hidden" name="action" value="checkeligibility" />
    
	<div class='button-c-container'>
		<button type="submit" class="button large green saveform"><?php _e('Check Your Eligibility');?></button>
	</div>
</form>