<div class="input-form eligibility-fail">
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('Thank You For Your Time');?></label></h2>
    	</div>
        <div class="content">
            <?php _e("<p>Thank you for taking an interest in the PROCAS – Lifestyle research study. The information that you have entered on this website has been used to see if you are eligible to take part.</p>
            
            <p> Unfortunately because you have answered yes to one or more of the questions, you are not able to progress onto the study. You can access lifestyle support or advice for existing health conditions via the links provided below.</p>
            
            <p>If you are interested in losing weight you can choose to follow our evidence-based 2 day diet by following the appropriate diet plan (insert link to below) based on your current weight.");?></p>
        </div>
    </div>
    
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('Advice');?></label></h2>
    	</div>
        <div class="content">
            <p><?php _e("We've put together an advice document which you may find useful.");?>
            <a href="<?php echo $ineligibleDownloadPdfUrl;?>" target="_blank" class="bright"><?php _e('Download it here')?></a></p>
        </div>
    </div>
    
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('The 2 Day Diet');?></label></h2>
    	</div>
        <div class="content">
            <p><?php _e("If you would still like to try the 2 day diet, we've put together some useful advice.")?>
                <a href="<?php echo $twoDayDietDownloadPdfUrl;?>" target="_blank" class="bright"><?php _e("Download it here");?></a>
            </p>
        </div>
    </div>
    
    <a href="<?php echo home_url()?>" class="button large blue"><?php _e("Go to the PROCAS Lifestyle Study Homepage")?></a>
</div>