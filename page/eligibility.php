<?php
echo GenesisThemeShortCodes::readingBox(
    "Welcome to The Family History Lifestyle Study",
    "<p>The Family History Lifestyle Study is a diet and exercise programme designed to support people with a BMI greater than 25 to lose weight.</p>
<p>Before you decide to take part, let's check that the clinical trial is for you. Please read <a href='$eligibilityPdfUrl' target='_blank'>this document</a> before completing the form below.</p>"
);

echo GenesisThemeShortCodes::generateErrorBox(GenesisTracker::$pageData);

?>


<form class="input-form eligibility-form" action="" method="post" name="eligibility" autocomplete="off">
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Firstly, Please Enter Your details');?></h3>
		</div>
	           
        
		<div class="inner-question-container">
			<div class="question-container clearfix">
				<div class="title">
					<label for="age" class="general-label">
                        <?php _e('1. Your Age');?>
                    </label>
				</div>
                <p class="form-explanation"><?php _e('Please tell us your age')?></p>
				<div class="input-wrapper">
					<?php
					echo $form->input('age', 'text', array(
						'class' => 'general-input smaller',
						'id' => 'age',
						'default' => $autoAnswer ? 50 : ""
						));
					?>
                    <p class="input-suffix"><?php _e('years');?></p>
                </div>
            </div>
        </div>

		<div class="inner-question-container">
			<div class="question-container clearfix">
				<div class="title">
					<label for="height-main" class="general-label"><?php _e('2. Your Height');?></label>
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
						'id' => 'height-main',
						'default' => $autoAnswer ? 4 : ""
					));
					?>
					<p class="input-suffix height metric <?php echo (!$metricUnits ? 'hidden' : '');?>"><?php _e('metres');?></p>
					<p class="input-suffix height imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('feet');?></p>
				</div>
				<div class="input-wrapper">
					<?php
					echo $form->input('height_inches', 'text', array(
						'class' => 'general-input height-input height imperial ' . ($metricUnits ? "hidden" : ""),
						'id' => 'height-inches',
						'default' => $autoAnswer ? 0 : ""
					));
					?>

					<p class="input-suffix height imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('inches');?></p>
				</div>
			</div>
		</div>
        
        <div class="inner-question-container">
			<div class="question-container clearfix">
				<div class="title">
					<label for="weight-main" class="general-label">
                        <?php _e('3. Your Weight');?>
                    </label>
				</div>
                
				<p class="form-explanation">
                    <a href="javascript:;" class="fa fa-question-circle help-icon weight-help" title="<strong>Simple tips for accurate weight measurements</strong><ul><li>Use the same set of <strong>reliable</strong> scales</li><li>Stand bathroom scales on a <strong>hard, level floor</strong> –it is best not to place them on carpet</li><li><strong>Remove your clothes and shoes</strong> before weighing yourself or wear light clothes only</li></ul>"></a>
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
						'id' => 'weight-main',
						'default' => $autoAnswer ? 13 : ""
						));
					?>
					<p class="input-suffix weight metric <?php echo (!$metricUnits ? 'hidden' : '');?>"><?php _e('kilograms');?></p>
					<p class="input-suffix weight imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('stone');?></p>
				</div>
				<div class="input-wrapper">
					<?php
					echo $form->input('weight_pounds', 'text', array(
						'class' => 'general-input weight-input weight imperial  ' . ($metricUnits ? "hidden" : ""),
						'id' => 'weight-pounds',
						'default' => $autoAnswer ? 0 : ""
						));
					?>
	
					<p class="input-suffix weight imperial <?php echo ($metricUnits ? 'hidden' : '');?>"><?php _e('pounds');?></p>
				</div>				
			</div> 
        </div>

        <div class="inner-question-container">
			<div class="question-container container-bmi clearfix">
				<div class="title">
					<span class="general-label"><?php _e('4. Your BMI');?></span>
				</div>
				<p class="form-explanation"><?php _e('When you\'ve entered your weight and height, your BMI will be calculated below');?></p>
                <div class="bmi-inner">
                    *
                </div>
            </div>
        </div>
        
        <div class="inner-question-container">
			<div class="question-container clearfix">
				<div class="title">
					<label class="general-label"><?php _e('5. Do you have access to a telephone and high/moderate-speed internet?');?></label>
				</div>
				<p class="form-explanation"><?php _e('As part of the study you will receive feedback and support by phone and email, you will also be asked to log your progress using the website');?></p>
                <?php
                echo $form->dropdown('high_speed_internet', array(
                    '' => '--- Please Select ---',
                    "1" => 'Yes',
                    "2" => 'No'
                ), array(
                    'default' => $autoAnswer ? 1 : ""
                ));
                ?>
            </div>
        </div>
        
        <div class="inner-question-container">
			<div class="question-container clearfix">
				<div class="title">
					<label class="general-label"><?php _e('6. Can you understand written and spoken English');?></label>
				</div>
                <?php
                echo $form->dropdown('can_understand_english', array(
                    '' => '--- Please Select ---',
                    "1" => 'Yes',
                    "2" => 'No'
                ), array(
                    'default' => $autoAnswer ? 1 : ""
                ));
                ?>
            </div>
        </div>
    </div>
	<div class="question-outer-container">
		<div class="title">
			<h3 class="general-label"><?php _e('Please answer the following questions');?></h3>
		</div>

        <div class="inner-question-container">
			<?php $a = 1; ?>
			<?php foreach($eligibilityQuestions1 as $question) : ?>
				<div class="question-container clearfix">
					<div class="title">
						<label class="general-label" for="question-<?php echo $a ?>"><?php  _e($a . ". " . $question->question);?></label>
					</div>

					<?php
					echo $form->dropdown('question_' . $question->id, array(
						'' => '--- Please Select ---',
						"1" => 'Yes',
						"2" => 'No'
					), array(
						'default' => $autoAnswer ? $question->correct : "",
						'id' => 'question-' . $a
					));
					?>
				</div>
				<?php $a++ ?>
			<?php endforeach ?>
		</div>
    </div>


    
    <div class="question-outer-container">
         <div class="inner-question-container">
             <div class="question-container">
        		<div class="title">
        			<label class="general-label" for="passcode"><?php _e('Passcode');?></label>
        		</div>
    
             <p class="form-explanation"><?php _e('You will have been sent a passcode with your welcome letter. Please enter it here.');?></p>
                <?php
                echo $form->input('passcode', 'password', array(
        			'class' => 'general-input',
                    'id' => 'passcode',
					'default' => $autoAnswer ? "FHLUHS" : "",
					'autocomplete' => 'off'
                ));
                ?>
            </div>
            
            <div class="question-container">
        		<div class="title">
        			<span class="general-label"><?php _e('Your Consent');?></span>
        		</div>
                <div class="auto-width-form">
                <div class="col">
                    <?php
                    echo $form->checkbox('consent', 1, array(
            			'class' => 'general-checkbox',
                        'id' => 'consent',
						'default' => $autoAnswer ? 1 : ""
                    ));
                    ?>
                </div>
                <div class="col">
                    <label for="consent"><?php _e('I give my consent that Family History Lifestyle Study to use the information I have entered for research purposes. <br /><em>We will never share your data with any third parties</em>')?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <input type="hidden" name="action" value="checkeligibility" />
    
	<div class='button-c-container'>
		<button type="submit" class="button large green saveform"><?php _e('Next Stage');?></button>
	</div>
</form>