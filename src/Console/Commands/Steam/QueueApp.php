<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class QueueApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:queue-app {app_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a Steam app for donwloading';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {       
        $app = Capsule::table('steam_apps')
                        ->where('appid', $this->argument('app_id'))
                        ->first();
        
        if( ! $app )
        {
            $this->error('Steam app with ID '.$this->argument('app_id').' not found');
            die();
        }

        $alreadyQueued = Capsule::table('steam_queue')
                        ->where('appid', $app->appid)
                        ->count();

        if( $alreadyQueued )
        {
            $this->error('Steam app "' . $app->name .'" already in download queue');
            die(); 
        }

        Capsule::table('steam_queue')->insert([
            'appid' => $app->appid,
            'name'  => $app->name,
            'status'=> 'queued'
        ]);

        $this->info('Steam app "' . $app->name .'" added to download queue');

    }
}