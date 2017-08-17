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
    protected $signature = 'steam:queue-app
                            {app_id : The ID of the app}
                            {platforms=windows : Comma separated list of platforms to download the app for [windows, osx, linux]}
                            {--account= : The Steam account to use when downloading the app (the DEFAULT_STEAM_USER environment variable will be used if this option is omitted)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a Steam app for downloading';

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
        // If no platforms are specified, default to windows
        $platforms = explode(',', $this->argument('platforms')) ?? ['windows'];
        
        // If no account is specified, default to the account set in the .env file
        $account = $this->option('account') ?? getenv('DEFAULT_STEAM_USER');
        
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

        // Queue each platform separately
        foreach( $platforms as $platform )
        {
            $alreadyQueued = Capsule::table('steam_queue')
                            ->where('appid', $app->appid)
                            ->where('platform', $platform)
                            ->count();
            if( $alreadyQueued )
            {
                $this->error('Steam app "' . $app->name .'" on platform "'.$platform.'" already in download queue');
                continue; 
            }

            // Add the app to the download queue, specifying the platform and account
            Capsule::table('steam_queue')->insert([
                'appid' => $app->appid,
                'name'  => $app->name,
                'platform'  => $platform,
                'account'  => $account,
                'status'=> 'queued'
            ]);

            $this->info('Added Steam app "' . $app->name .'" on platform "'.$platform.'" from Steam account "'.$account.'" to download queue');
            
        }



    }
}