<?php

class IndexController extends BaseController {

	public function actionIndex() 
	{
		Index::load()->index();
		View::render("index");
	}

}