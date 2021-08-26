<?php

namespace Zeropingheroes\LancacheAutofill\Commands\App;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class InitialiseDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initialise-database
                                {--yes : Suppress confirmations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Capsule::schema()->hasTable('steam_apps') OR Capsule::schema()->hasTable('steam_queue') OR Capsule::schema()->hasTable('steam_accounts')) {
            if ($this->option('yes') OR $this->confirm('Are you sure you wish to clear all data in the database?')) {
                $this->info('Removing existing database tables');
                Capsule::schema()->dropIfExists('steam_apps');
                Capsule::schema()->dropIfExists('steam_queue');
                Capsule::schema()->dropIfExists('steam_accounts');
            }
            else {
                die();
            }

        }

        $this->info('Creating empty database tables');
        Capsule::schema()->create('steam_apps', function ($table) {
            $table->bigInteger('id')->unique();
            $table->string('name');
        });

        Capsule::schema()->create('steam_queue', function ($table) {
            $table->increments('id')->unique();
            $table->bigInteger('app_id');
            $table->string('status');
            $table->string('platform');
            $table->integer('popularity')->nullable()->default(1);
            $table->string('message')->nullable();
        });

        Capsule::schema()->create('steam_accounts', function ($table) {
            $table->string('username');
        });

    }
}