<?php

namespace Hypersistence;

use Hypersistence\Console\CreateModelsHypersistence;
use Hypersistence\Console\AuthMakeCommand;
use Hypersistence\Console\CreateHistoryTable;
use Illuminate\Support\ServiceProvider;

class HypersistenceServiceProvider extends ServiceProvider {
	/**
	* Bootstrap the application services.
	*
	* @return void
	*/
	public function boot()
	{
		if ($this->app->runningInConsole()) {
		    $this->commands([
		        CreateModelsHypersistence::class,
		        AuthMakeCommand::class,
                        CreateHistoryTable::class
		    ]);
		}
	}
}
