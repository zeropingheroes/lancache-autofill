<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Creating database');

        Capsule::schema()->create('steam_apps', function ($table) {
            $table->integer('appid')->unique();
            $table->string('name');
        });

        Capsule::schema()->create('steam_queue', function ($table) {
            $table->increments('id')->unique();
            $table->integer('appid')->unique();
            $table->string('name');
            $table->string('status');
            $table->string('message')->nullable();

        });
    }
}