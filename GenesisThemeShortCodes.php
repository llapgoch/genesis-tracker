<?php
class GenesisThemeShortCodes{
    public static function readingBox($title, $content, $attribs = array()){
        $extra = '';
        foreach($attribs as $key => $attrib){
            $extra .= $key . '="' . $attrib . '" ';
        }
        
        return do_shortcode('[tagline_box backgroundcolor="" shadow="yes" shadowopacity="0.4" border="0px" bordercolor="#e67fb9" highlightposition="top" content_alignment="left" link="" linktarget="_self" button_size="small" button_shape="square" button_type="flat" buttoncolor="" button="" title="' . $title . '" description="' . $content . '" animation_type="0" animation_direction="down" animation_speed="0.1" ' . $extra . '][/tagline_box]');
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
    
    public static function generateErrorBox($pageData){

        if(!is_array($pageData) || !isset($pageData['errors'])){
            return '';
        }        

        return self::errorBox(implode("<br />", $pageData['errors']));
    }
}
