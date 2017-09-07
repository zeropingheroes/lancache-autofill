<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Zeropingheroes\LancacheAutofill\Models\{
    SteamApp, SteamQueueItem
};

class QueueApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:queue-app
                            {app_ids* : One or more app IDs to queue}
                            {--windows=true : Queue the Windows version of the app}
                            {--osx : Queue the OS X version of the app}
                            {--linux : Queue the Linux version of the app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a Steam app for downloading';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appIds = $this->argument('app_ids');

        foreach($appIds as $appId)
        {
            if (!$app = SteamApp::find($appId)) {
                $this->error('Steam app with ID '.$appId.' not found');
                die();
            }

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

            // Queue each platform separately
            foreach ($platforms as $platform) {

                $alreadyQueued = SteamQueueItem::where('app_id', $app->id)
                    ->where('platform', $platform)
                    ->first();

                if ($alreadyQueued) {
                    $this->error('Steam app "'.$app->name.'" on platform "'.ucfirst($platform).'" already in download queue');
                    continue;
                }

                // Add the app to the download queue, specifying the platform and account
                $steamQueueItem = new SteamQueueItem;
                $steamQueueItem->app_id = $app->id;
                $steamQueueItem->platform = $platform;
                $steamQueueItem->status = 'queued';

                if ($steamQueueItem->save()) {
                    $this->info('Added Steam app "'.$app->name.'" on platform "'.ucfirst($platform).'" to download queue');
                }

            }
        }
    }
}