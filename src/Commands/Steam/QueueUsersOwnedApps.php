<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Zeropingheroes\LancacheAutofill\Models\SteamQueueItem;
use Steam;
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\BlockingConsumer;
use bandwidthThrottle\tokenBucket\storage\FileStorage;

class QueueUsersOwnedApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:queue-users-owned-apps
                            {steamIds* : One or more SteamId64(s) for the user(s) whose apps to queue, or a file containing a list}
                            {--include_free=true : Queue played free games}
                            {--windows=true : Queue the Windows version of the apps}
                            {--osx : Queue the OS X version of the apps}
                            {--linux : Queue the Linux version of the apps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all users\' Steam apps for downloading';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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

        if ($this->option('include_free')) {
            $includeFree = true;
        }
        $steamIds = $this->argument('steamIds');

        if (file_exists($steamIds[0])) {
            $steamIds = file_get_contents($steamIds[0]);
            $steamIds = explode("\n", trim($steamIds));
        }

        array_filter($steamIds, function ($steamId) {
            return trim($steamId);
        });

        // Rate limit requests to 200 requests every 5 minutes, to match Steam's rate limit
        $storage = new FileStorage(base_path('steam-api.bucket')); // store state in base directory
        $rate = new Rate(40, Rate::MINUTE); // add 40 tokens every minute (= 200 over 5 minutes)
        $bucket = new TokenBucket(200, $rate, $storage); // bucket can never have more than 200 tokens saved up
        $consumer = new BlockingConsumer($bucket); // if no tokens are available, block further execution until there are tokens
        $bucket->bootstrap(200); // fill the bucket with 200 tokens initially

        $chunkedSteamIds = array_chunk($steamIds, 10);

        $users = [];
        foreach($chunkedSteamIds as $chunkOf100SteamIds) {
            $consumer->consume(1);
            $users = array_merge($users, Steam::user($chunkOf100SteamIds[0])->GetPlayerSummaries($chunkOf100SteamIds));
        }

        foreach ($users as $user) {
            $this->info('');

            if ($user->communityVisibilityState != 3) {
                $this->warn('Skipping user with private profile: ' . $user->personaName);
                continue;
            }

            $consumer->consume(1);
            $apps = Steam::player($user->steamId)->GetOwnedGames(true, $includeFree);

            if (empty($apps)) {
                $this->warn('Skipping user who does not own any apps: ' . $user->personaName);
                continue;
            }

            $this->info('Queuing apps owned by user: ' . $user->personaName);

            foreach ($apps as $app) {

                // Queue each platform separately
                foreach ($platforms as $platform) {

                    $existingQueueItem = SteamQueueItem::where('app_id', $app->appId)
                        ->where('platform', $platform)
                        ->first();

                    if ($existingQueueItem) {
                        $existingQueueItem->popularity++;
                        $existingQueueItem->save();
                        $this->warn('Steam app "' . $app->name . '" on platform "' . ucfirst($platform) . '" already in download queue');
                        continue;
                    }

                    // Add the app to the download queue, specifying the platform and account
                    $steamQueueItem = new SteamQueueItem;
                    $steamQueueItem->app_id = $app->appId;
                    $steamQueueItem->platform = $platform;
                    $steamQueueItem->status = 'queued';

                    if ($steamQueueItem->save()) {
                        $this->info('Added Steam app "' . $app->name . '" on platform "' . ucfirst($platform) . '" to download queue');
                    }
                }
            }
        }
    }
}
