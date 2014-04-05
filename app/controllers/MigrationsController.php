<?php

class MigrationsController
{   
    public function createMigration()
    {
		print MigrationsResource::load()->createMigration();	
    }

    public function getMigrations()
    {
    	print MigrationsResource::load()->getMigrations();
    }
}