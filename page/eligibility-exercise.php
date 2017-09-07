<?php echo GenesisThemeShortCodes::readingBox(
    "Physical Activity",
    "<ul>
                            <li>This diet study involves a home based physical activity plan. Being more active is very safe for most people. However, some people may need to check with their doctor before they join.</li>
                            <li>In order to help you assess whether it is safe for you to do the physical activity plan, please read the following questions carefully and answer honestly: tick Yes or No.</li>
                            <li>If you have answered “Yes” to one or more questions, a form will be generated that you can take to your GP to sign so that he/she can review if you can join our study.</li>
                        </ul>"
);
?>
<form class="input-form eligibility-form" action="" method="post" name="eligibility" autocomplete="off">
    <div class="question-outer-container">
        <div class="title">
            <h3 class="general-label"><?php _e('Please enter the following questions');?></h3>
        </div>
        <div class="question-outer-container">
            <div class="inner-question-container">

                <?php $counter = 1; ?>
                <?php foreach($eligibilityQuestions2 as $question) : ?>
                    <div class="question-container clearfix">
                        <div class="title">
                            <label class="general-label" for="question-<?php echo $a ?>"><?php  _e($counter . ". " . $question->question);?></label>
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
                    <?php
                    $a++;
                    $counter++;
                    ?>
                <?php endforeach ?>

                <div class="question-container clearfix">
                    <div class="title">
                        <label class="general-label" for="question-no_physical_activity_reason"><?php _e("If you answered “yes” to Q7, please briefly explain:");?></label>
                    </div>
                    <?php echo $form->textarea('question-no_physical_activity_reason', array(
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
    <input type="hidden" name="action" value="checkeligibility" />

    <div class='button-c-container'>
        <button type="submit" class="button large green saveform"><?php _e('Check Your Eligibility');?></button>
    </div>
</form>