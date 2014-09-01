<section class="reading-box main-accent butted clearfix">
	<h2><?php _e('Sorry, you are currently not eligible for the Genesis Procas Lifestyle Study') ?></h2>
	<p>Please read the advice below</p>
	<div class="tagline-shadow"></div>
</section>

<div class="input-form eligibility-fail">
    <div class="question-outer-container">
    	<div class="title">
    		<h3><label class="general-label"><?php _e('Weight loss advice');?></label></h3>
    	</div>
        <div class="content">
            <p>Weight loss advice</p>
        </div>
    </div>


    <?php if($form->getRawValue('question_one') !== "1") : ?>
        <div class="question-outer-container">
        	<div class="title">
        		<h3><label class="general-label"><?php _e('Question One advice');?></label></h3>
        	</div>
            <div class="content">
                <p>Question one advice</p>
            </div>
        </div>
    
    <?php endif ?>
    
    <?php if($form->getRawValue('question_two') !== "2") : ?>
        <div class="question-outer-container">
        	<div class="title">
        		<h3><label class="general-label"><?php _e('Question Two advice');?></label></h3>
        	</div>
            <div class="content">
                <p>Question two advice</p>
            </div>
        </div>
    
    <?php endif ?>
    
    <?php if($form->getRawValue('question_three') !== "2") : ?>
        <div class="question-outer-container">
        	<div class="title">
        		<h3><label class="general-label"><?php _e('Question Three advice');?></label></h3>
        	</div>
            <div class="content">
                <p>Question three advice</p>
            </div>
        </div>
    
    <?php endif ?>
    
    <a href="/" class="button large blue">Go to the Genesis Procas Lifestyle Study Homepage</a>
</div>