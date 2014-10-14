<div class="input-form eligibility-fail">
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('Thank You For Your Time');?></label></h2>
    	</div>
        <div class="content">
            <p><?php _e("Unfortunately because of one of the following, you are not able to progress onto the study;");?></p>
                <ul>
                    <li>Your age is not between 47 to 74 years</li>
                    <li>Your BMI is lower than 25</li>
                    <li>You do not have access to a telephone or high/moderate speed internet</li>
                    <li>You have answered yes to one or more of the health eligibility questions</li>
                </ul>        
            <p><?php _e("People who have previously been diagnosed with cancer or those with any of the health conditions listed on the previous page are unable to take part in the study as we are interested in the effect of diet and exercise in the <strong>prevention of cancer and other health conditions</strong>, that can be modified by diet and exercise. In addition, the use of certain medications may mean that you are unable to take part in the study as they can affect your risk of developing certain health problems. You can access further information or support services in relation to your current health or medications by <a href='" . $ineligibleDownloadPdfUrl . "' target='_blank'>downloading the supporting information document</a>");?></p>
            
            <p><?php _e("If you are still interested in losing weight you can choose to follow our evidence-based 2 day diet by following the appropriate diet plan. The diet plans are part of a healthy lifestyle that includes exercise and they are designed to be:");?></p>
                
            <ul>
                <li>Low enough in calories to enable you to lose weight, but without leaving you feeling hungry.</li>
                <li>Nutritionally balanced so that all your vitamin, mineral and protein requirements are met.</li>
                <li>Easy to fit into a normal, busy lifestyle.</li>
            </ul>
            
            <p>For further information about the diet and to access a diet plan, <a href="<?php echo $twoDayDietDownloadPdfUrl;?>" target='_blank'>click here</a></p>
        </div>
    </div>
    
    <a href="<?php echo home_url()?>" class="button large blue"><?php _e("Go to the PROCAS Lifestyle Study Homepage")?></a>
</div>