<?php namespace Graphist\Controller;

use Graphist\Controller as Controller;
use Graphist\Response as Response;

class BaseController extends Controller {

	public function __call($name, $args) 
	{ 
		Response::error("404");
	}

}