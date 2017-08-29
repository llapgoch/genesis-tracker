<?php
class GenesisThemeShortCodes{
    public static function readingBox($title, $content, $attribs = array()){
        $defaults = array(
            "shadow" => "yes",
            "shadowopacity" => 0.4,
            "border" => "0px",
            "bordercolor" => "#e67fb9",
            "highlightposition" => "top",
            "content_alignment" => "left",
            "button_size" => "small",
            "button_shape" => "square",
            "button" => "Test",
            "button_color" => "",
            "button_type" => "flat",
            "animation_type" => 0
         );
         
         array_merge($defaults, $attribs);
         $taglineObj = new FusionSC_Tagline();
         
         $content =  ($title ? "<h2>" . $title . "</h2>" : "") . $content;
         return $taglineObj->render($defaults, $content);
    }
    
    public static function errorBox($content){
        return "<div class='fusion-alert alert error alert-danger alert-shadow'>
				    <div class='msg'>" . $content . "</div>
                </div>";
    }
    
    public static function successBox($content){
        return "<div class='fusion-alert alert success alert-success alert-shadow'>
				    <div class='msg'>" . $content . "</div>
                </div>";
    }

    public static function achievementBox($title, $content){
        return self::readingBox($title, $content);
    }
    
    public static function generateErrorBox($pageData){

        if(!is_array($pageData) || !isset($pageData['errors'])){
            return '';
        }        

        return self::errorBox(implode("<br />", $pageData['errors']));
    }
}
