<?php

class ApiController extends BaseController {
    
    public function actionIndex($uri) {
        $resourcePath = "api/" . $uri . EXT;
        
        if (file_exists($resourcePath)) {
        
            require_once("api/lib/init.php");
             
            require_once($resourcePath);     
        } else {
            Response::json(array("success" => "no", "message" => "Invalid resource"));
        }
        
    }
    
}