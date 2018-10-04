<?php
class GenesisUserInputTable{
    public function getFoodInputTableHTML($timeIdentifier, $form){
        $foodTypes = GenesisTracker::getUserMetaTargetFields();
        
        $html = array();

        $consumeVerb = $timeIdentifier == 'drinks' ? "drank" : "ate";
       
        $html[] = "<div class='food-input-container'>";
        $html[] = "<div class='food-description-container'>";
        $html[] = "<label for='" . $timeIdentifier . "_description'>A brief description of what you {$consumeVerb} (optional):</label>";
        
        $html[] = $form->input($timeIdentifier . '_description', 'text', array(
            'id' => $timeIdentifier . "_description",
            'class' => 'general-input food-description',
            'data-time' => $timeIdentifier
        ));
        
        $html[] = "<a href='javascript:;' class='fa fa-question-circle help-icon weight-help' title='If you record your food intake here, the dietitian will be able to provide you with individual advice that will help you lose weight and achieve a balanced diet'></a>";
        
        $html[] = "</div>";
       
        $html[] = "<div class='food-input-form'>";
       
            
        foreach($foodTypes as $foodIdentifier => $food){
            $fullKey = $timeIdentifier . "_" . $foodIdentifier;
            
            $html[] = "<div class='input-box'>";
            $html[] = "<label for='$fullKey'>{$food['name']}<br />({$food['unit']})</label>";
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
        $html[] = "</div>";
        
        return implode($html);
    }
}