<?php

class MigrationController
{   
    public function setServers($migrationId)
    {
		print MigrationResource::load()->setServers($migrationId);	
    }

    public function saveEntities($migrationId)
    {
    	print MigrationResource::load()->saveEntities($migrationId);
    } 

    public function getTables($migrationId)
    {
    	print MigrationResource::load()->getTables($migrationId);
    }

    public function saveRelationships($migrationId)
    {
    	print MigrationResource::load()->saveRelationships($migrationId);
    } 

    public function executeMigration($migrationId)
    {
    	print MigrationResource::load()->executeMigration($migrationId);
    }

    public function deleteMigration($migrationId)
    {
    	print MigrationResource::load()->deleteMigration($migrationId);
    } 
}