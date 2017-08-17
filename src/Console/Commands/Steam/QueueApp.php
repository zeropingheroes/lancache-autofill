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
    protected $signature = 'steam:queue-app {app_id} {platform=windows}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a Steam app for donwloading';

    /**
     * The permissible platforms.
     *
     * @var array
     */
    const PLATFORMS = ['windows', 'osx', 'linux'];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if( $this->argument('platform') && ! in_array($this->argument('platform'), $this::PLATFORMS))
        {
            $this->error('Invalid platform specified. Available platforms are: '. implode(' ', $this::PLATFORMS));
            die();
        }

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
                        ->where('platform', $this->argument('platform'))
                        ->count();

        if( $alreadyQueued )
        {
            $this->error('Steam app "' . $app->name .'" on platform "'.$this->argument('platform').'" already in download queue');
            die(); 
        }

        Capsule::table('steam_queue')->insert([
            'appid' => $app->appid,
            'name'  => $app->name,
            'platform'  => $this->argument('platform'),
            'status'=> 'queued'
        ]);

        $this->info('Added Steam app "' . $app->name .'" on platform "'.$this->argument('platform').'" to download queue');

    }
}