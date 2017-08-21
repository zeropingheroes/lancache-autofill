<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class InitialiseDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initialise-database';

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
        if (Capsule::schema()->hasTable('steam_apps') OR Capsule::schema()->hasTable('steam_queue')) {
            if ($this->confirm('Are you sure you wish to clear all data in the database?')) {
                $this->info('Removing existing database tables');
                Capsule::schema()->dropIfExists('steam_apps');
                Capsule::schema()->dropIfExists('steam_queue');
            }
            else {
                die();
            }

        }

        $this->info('Creating empty database tables');
        Capsule::schema()->create('steam_apps', function ($table) {
            $table->integer('appid')->unique();
            $table->string('name');
        });

        Capsule::schema()->create('steam_queue', function ($table) {
            $table->increments('id')->unique();
            $table->integer('appid');
            $table->string('name');
            $table->string('account');
            $table->string('status');
            $table->string('message')->nullable();
            $table->string('platform');

        });
    }
}