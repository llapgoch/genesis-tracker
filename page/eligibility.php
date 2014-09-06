<?php
echo GenesisThemeShortCodes::readingBox(
    "Thank you for participating in the Procas Lifestyle Study",
    "Please answer a few questions to check your eligibility in the clinical trial."
);

echo GenesisThemeShortCodes::generateErrorBox(GenesisTracker::$pageData);

?>


<form class="input-form eligibility-form" action="" method="post" name="eligibility">

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