<?php

class RequestServiceProvider extends ServiceProvider {

	public function __construct() {
		
	}
	
	public function URI() 
	{
		return (!empty($_SERVER['PATH_INFO']))? rtrim($_SERVER['PATH_INFO'], "/") : "/";
	}

	public function method() 
	{
		return $_SERVER['REQUEST_METHOD'];
	}
	
}