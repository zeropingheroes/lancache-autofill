<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Zeropingheroes\LancacheAutofill\Models\SteamQueueItem;

class QueuePopularApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:queue-popular-apps
                            {top=100 : Limit how many apps are queued}
                            {--windows=true : Queue the Windows version of the apps}
                            {--osx : Queue the OS X version of the apps}
                            {--linux : Queue the Linux version of the apps}
                            {--free : Only queue free apps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue popular Steam apps for downloading';

    /**
     * The URL to get the list of popular Steam apps from.
     *
     * @var string
     */
    const POPULAR_STEAM_APP_LIST_URL = 'http://steamspy.com/api.php?request=top100in2weeks';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $top = $this->argument('top');
        $free = $this->option('free');

        // Add platforms depending on options
        if ($this->option('windows')) {
            $platforms[] = 'windows';
        }

        if ($this->option('osx')) {
            $platforms[] = 'osx';
        }

        if ($this->option('linux')) {
            $platforms[] = 'linux';
        }

        $client = new Client();
        $result = $client->request('GET', self::POPULAR_STEAM_APP_LIST_URL);

        if ($result->getStatusCode() != 200) {
            $this->error('Web API unreachable');
            die();
        }

        $apps = json_decode($result->getBody(), true);

        if ( ! count($apps) ) {
            $this->error('SteamSpy API did not return any apps:');
            $this->error(self::POPULAR_STEAM_APP_LIST_URL . ' returned:');
            $this->error($result->getBody());
            die();
        }

        $i = 0;

        foreach ($apps as $appId => $app) {
            if ($free && $app['price'] != 0) {
                continue;
            }

            // Queue each platform separately
            foreach ($platforms as $platform) {

                $alreadyQueued = SteamQueueItem::where('app_id', $appId)
                    ->where('platform', $platform)
                    ->first();

                if ($alreadyQueued) {
                    $this->error('Steam app "'.$app['name'].'" on platform "'.ucfirst($platform).'" already in download queue');
                    continue;
                }

                // Add the app to the download queue, specifying the platform and account
                $steamQueueItem = new SteamQueueItem;
                $steamQueueItem->app_id = $appId;
                $steamQueueItem->platform = $platform;
                $steamQueueItem->status = 'queued';

                if ($steamQueueItem->save()) {
                    $this->info('Added Steam app "'.$app['name'].'" on platform "'.ucfirst($platform).'" to download queue');
                }
            }
            if (++$i == $top) {
                break;
            }
        }
    }
}