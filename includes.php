<?php
// Autoloader
spl_autoload_register(function($class){
	if(file_exists(dirname(__FILE__)  . DIRECTORY_SEPARATOR . $class . ".php")){
		include(dirname(__FILE__) .  DIRECTORY_SEPARATOR . $class . ".php");
	}
});

