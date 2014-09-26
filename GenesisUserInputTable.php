<?php
class GenesisUserInputTable{
    public function getFoodInputTableHTML($foodIdentifier, $form){
        $userTargetTimes = GenesisTracker::getUserTargetTimes();
        
        $html = array();
        
        $html[] = GenesisTracker::getUserTargetLabel($foodIdentifier);
        $html[] = "<div class='food-input-form'>";
        
        foreach($userTargetTimes as $key => $time){
            $fullKey = $key . "_" . $foodIdentifier;
            
            $html[] = "<div class='input-box'>";
            
            $html[] = "<label for='$fullKey'>" . $time['name'] . "</label>";

            $html[] = $form->input($fullKey, 'text', array(
		        'id' => $key . "_" . $foodIdentifier,
                'class' => 'general-input',
                'default' => 0
            ));
            
            

//            $html[] = "<p class='input-suffix'>" .  GenesisTracker::getUserTargetUnit($foodIdentifier) . "</p>";
            $html[] = "</div>";
        }
        

        
        $html[] = "</div>";
        
        $html[] = "<p class='food-totals'><span class='total-label'>Total:</span><span class='number general-input'>0</span></p>";
        
        return implode($html);
    }
}