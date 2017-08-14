<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class UpdateAppList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:update-app-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the latest list of apps from Steam';

    /**
     * The URL to get the list of Steam apps from.
     *
     * @var string
     */
    const STEAM_APP_LIST_URL = 'https://api.steampowered.com/ISteamApps/GetAppList/v2';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Clearing apps from database');
        Capsule::table('steam_apps')->truncate();
        
        $this->info('Downloading app list from Steam Web API');
        $client = new Client();
        $result = $client->request('GET', self::STEAM_APP_LIST_URL);

        if ( $result->getStatusCode() != 200 )
        {
            $this->error('Steam Web API unreachable');
            die();
        }

        $response = json_decode($result->getBody(), TRUE);

        $apps = $response['applist']['apps'];
        
        $appsChunked = array_chunk($apps, 500);

        $bar = $this->output->createProgressBar(count($appsChunked));
        $bar->setFormat("%bar% %percent%%");

        $this->info('Inserting records into database');

        foreach($appsChunked as $appChunk)
        {
            Capsule::table('steam_apps')->insert($appChunk);
            $bar->advance();
        }

        $bar->finish();

        $this->info(PHP_EOL . 'Done');

    }
}