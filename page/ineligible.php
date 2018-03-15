<?php if($hasOnlyAnsweredSelfHarm): ?>
<div class="input-form eligibility-fail">
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('You may be eligible to take part in the study');?></label></h2>
    	</div>
    	<div class="content"><?php echo _e("Please contact the study team on 0161 291 4412");?></div>
    </div>
</div>
<?php else : ?>
<div class="input-form eligibility-fail">
    <div class="question-outer-container">
    	<div class="title">
    		<h2><label class="general-label"><?php _e('Thank you for taking an interest in The Family History Lifestyle Study');?></label></h2>
    	</div>
        <div class="content">
            <p><?php _e("The information that you provided on the previous web page has been used to see if you are eligible to take part.");?></p>
                  
            <p><?php _e("Unfortunately some of the information you have provided indicates that you are not able to progress onto the study. A summary of the reasons which may prevent you taking part are listed below (some reasons may not apply to you).");?></p>

            <p>Once you have had chance to read the information below we would be very grateful if you could complete a few additional questions about yourself.</p>

            <p>Knowing more about the people who are not able to take part can help our research and help us to plan future studies. If you are happy to give this information pleases click the link below to complete three short questions. Any information you give will be held anonymously and cannot be traced back to you.</p>

            <div class="button-c-container" style="margin: 20px 0">
                <a href="<?php echo $surveyPageUrl?>" class="button green large"><?php _e("Take additional questions");?></a>
            </div>

            <div class="title">
                <h2><label class="general-label">Reasons you may not be able to take part</label></h2>
            </div>

            <h2>BMI of less than 25</h2>
           <p>As the research study is testing a diet and physical activity weight loss plan, women who have a body mass index (BMI) below 25 are unable to take part. We would advise these women to maintain a healthy weight by following a healthy diet and getting plenty of exercise to minimise their future risk of conditions such as cancer, heart disease, diabetes, stroke and high cholesterol.</p>
           
           <h2>You are not receiving annual or 18 monthly mammograms because you have an increased risk of breast cancer</h2>
           <p>This study is aimed specifically at women who have an increased risk of breast cancer and are receiving additional screening. If this does not apply to you unfortunately you are not able to take part.</p>
           
           <h2>You are unable to understand written and spoken English</h2>
           <p>All our support is delivered over the phone and via email, and we need to be sure that participants are able to understand the information provided to them during the study.</p>

            <h2>No access to a telephone and/or high/moderate speed Internet</h2>
            <p>The study requires women to use the interactive study website to monitor their weight loss and other lifestyle changes and have regular telephone and online support from the study team.</p>

            <h2>Pre-existing health conditions</h2>
            <p>Women who have previously been diagnosed with cancer or other health conditions (heart disease, angina, diabetes, stroke or high cholesterol) are unable to take part in the study as we are testing a diet and physical activity programme for the prevention of these conditions. Further information or support services in relation to cancer and these other health conditions can be found below:</p>
            <ul>
                <li><a href="https://bhf.org.uk" target="_blank">The British heart foundation</a></li>
                <li><a href="https://heartuk.org.uk/what-we-do" target="_blank">Heart UK (The cholesterol charity)</a></li>
                <li><a href="https://www.stroke.org.uk" target="_blank">Stroke association</a></li>
                <li><a href="https://www.diabetes.org.uk" target="_blank">Diabetes UK</a></li>
                <li><a href="https://www.cancerresearchuk.org/about-cancer" target="_blank">Cancer research UK</a></li>
                <li><a href="https://preventbreastcancer.org.uk" target="_blank">Prevent breast cancer</a></li>
            </ul>

            <h2>Alcohol or drug dependency</h2>
            <p>Alcohol and drug dependency require specialist support which we are not able to provide. You can find local alcohol and drug services by following the links below;</p>
            <ul>
                <li><a href="https://www.nhs.uk/Service-Search/Support-services-for-alcohol-addiction/LocationSearch/295" target="_blank">Alcohol Services</a></li>
                <li><a href="https://www.nhs.uk/Service-Search/Information-and-support-for-drug-misuse/LocationSearch/339" target="_blank">Drug Services</a></li>
            </ul>

            <h2>You are taking a medication which can affect you weight</h2>
            <p>Aripiprazole, Clozapine, Olanzapine, Quetiapine and Risperidone are all medications whose side effects include weight gain. This could impact the success of a diet and physical activity regime.Orlistat, Xenical and Alli, are all medications for weight loss and could impact the success of a diet and physical activity regime.</p>

            <h2>You have had weight loss surgery</h2>
            <p>People who have had or may be having weight loss surgery are unable to join the study as this will affect the accuracy of our results.</p>

            <h2>Already successfully dieting and losing weight</h2>
            <p>The study is trying to help motivate women to join and keep to a weight loss programme to see if we can reduce risk of disease. If you are already successfully dieting it is best to keep with your current weight loss plan.</p>

            <h2>You are following a specialist medical diet</h2>
            <p>These diets cannot be adjusted to fit with our recommended diet plan.</p>

            <h2>Can I Still Follow The Diet?</h2>
            <p>If you are overweight and are still interested in losing weight you may be able to follow our evidence-based 2 day diet. The diet is designed to be part of a healthy lifestyle that includes exercise and a diet that is:</p>

            <ul>
                <li>Low enough in calories to enable you to lose weight, but without leaving you feeling hungry.</li>
                <li>Nutritionally balanced so that all your vitamin, mineral and protein requirements are met.</li>
                <li>Easy to fit into a normal, busy lifestyle.</li>
            </ul>

            <p>For further information about the diet, <a href="<?php echo $dietPlanPdfUrl;?>" target="_blank">click here</a></p>
        </div>
    </div>
</div>
<?php endif; ?>