<?php
class GenesisUserInputTable{
    public function getFoodInputTableHTML($foodIdentifier, $form){
        $userTargetTimes = GenesisTracker::getUserTargetTimes();
        
        $html = array();
        
        $html[] = GenesisTracker::getUserTargetLabel($foodIdentifier);
        $html[] = "<table>";
            $html[] = "<thead>";
                $html[] = "<tr>";
        
        foreach($userTargetTimes as $key => $time){
            $fullKey = $key . "_" . $foodIdentifier;
            $html[] = "<th>" . "<label for='$fullKey'>" . $time['name'] . "</label>" . "</th>";
        }
        $html[] = "</tr>";
        $html[] = "</thead>";
        
        $html[] = "<tbody>";
        
        foreach($userTargetTimes as $key => $time){
            $fullKey = $key . "_" . $foodIdentifier;
            
            $html[] = "<td>";

            $html[] = $form->input($fullKey, 'text', array(
		        'id' => $key . "_" . $foodIdentifier,
                'class' => 'general-input',
                'default' => 0
            ));

            $html[] = "<p class='input-suffix'>" .  'portions' . "</p>";
            $html[] = "</td>";
        }
        $html[] = "</tbody>";
        $html[] = "</table>";
        
        return implode($html);
    }
}