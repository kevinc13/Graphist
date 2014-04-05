<?php

class IndexController
{
	public function index() 
	{
		Index::load()->index();
		View::render("index");
	}
}