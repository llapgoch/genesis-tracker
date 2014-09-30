<?php
class GenesisUserInputTable{
    public function getFoodInputTableHTML($timeIdentifier, $form){
        $foodTypes = GenesisTracker::getUserMetaTargetFields();
        
        $html = array();
        
        $html[] = GenesisTracker::getUserTargetLabel($foodIdentifier);
        $html[] = "<div class='food-input-form'>";
        
        foreach($foodTypes as $foodIdentifier => $food){
            $fullKey = $timeIdentifier . "_" . $foodIdentifier;
            
            $html[] = "<div class='input-box'>";
            $html[] = "<label for='$fullKey'>" . $food['name'] . "</label>";
                $html[] = "<div class='input-container $foodIdentifier'>";

                $html[] = $form->input($fullKey, 'text', array(
    		        'id' => $timeIdentifier . "_" . $foodIdentifier,
                    'class' => 'general-input',
                    'default' => 0
                ));
            
                $html[] = "</div>";
            $html[] = "</div>";
        }
        
        $html[] = "</div>";
        
        return implode($html);
    }
}