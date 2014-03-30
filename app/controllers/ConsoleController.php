<?php

class ConsoleController extends BaseController {

	public function actionIndex() {
		if (!array_key_exists("user_id", $_SESSION)) Response::to_route("SignIn");
		View::render("console.index");
	}
}