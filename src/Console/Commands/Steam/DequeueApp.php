<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class DequeueApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:dequeue-app
                            {app_id : The ID of the app}
                            {platforms=windows,osx,linux : (Optional) Which platform(s) to dequeue. Defaults to all platforms}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a Steam app from the download queue';

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
        // If no platforms are specified, default to all platforms
        $platforms = explode(',', $this->argument('platforms'));
      
        if( array_diff($platforms, $this::PLATFORMS))
        {
            $this->error('Invalid platform(s) specified. Available platforms are: '. implode(' ', $this::PLATFORMS));
            die();
        }

        // Check if app with specified ID exists
        $app = Capsule::table('steam_apps')
                        ->where('appid', $this->argument('app_id'))
                        ->first();
        
        if( ! $app ) {
            $this->error('Steam app with ID '.$this->argument('app_id').' not found');
            die();
        }

        Capsule::table('steam_queue')
                ->where('appid', $this->argument('app_id'))
                ->whereIn('platform', $platforms)
                ->delete();

        $this->info('Removed Steam app "' . $app->name .'" on platforms "'.implode(' ', $platforms).'" from download queue');
    }
}