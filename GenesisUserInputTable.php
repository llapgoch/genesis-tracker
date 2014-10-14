<?php
class GenesisUserInputTable{
    public function getFoodInputTableHTML($timeIdentifier, $form){
        $foodTypes = GenesisTracker::getUserMetaTargetFields();
        
        $html = array();
        
       
       
        $html[] = "<div class='food-description'>";
        $html[] = "<label for='" . $timeIdentifier . "_description'>A Brief Description of what you ate:</label>";
        
        $html[] = $form->input($timeIdentifier . '_description', 'text', array(
            'id' => $timeIdentifier . "_description",
            'class' => 'general-input food-description'
        ));
        $html[] = "</div>";
       
        $html[] = "<div class='food-input-form'>";
       
            
        foreach($foodTypes as $foodIdentifier => $food){
            $fullKey = $timeIdentifier . "_" . $foodIdentifier;
            
            $html[] = "<div class='input-box'>";
            $html[] = "<label for='$fullKey'>" . $food['name'] . "</label>";
                $html[] = "<div class='input-container $foodIdentifier'>";

                $html[] = $form->input($fullKey, 'text', array(
    		        'id' => $timeIdentifier . "_" . $foodIdentifier,
                    'class' => 'general-input food-input',
                    'default' => 0,
                    'data-input-food' => $foodIdentifier
                ));
            
                $html[] = "</div>";
            $html[] = "</div>";
        }
        
        $html[] = "</div>";
        
        return implode($html);
    }
}