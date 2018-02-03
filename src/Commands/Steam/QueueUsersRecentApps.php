<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Zeropingheroes\LancacheAutofill\Models\SteamQueueItem;
use Steam;

class QueueUsersRecentApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:queue-users-recent-apps
                            {steamIds* : One or more SteamId64(s) for the user(s) whose apps to queue, or a file containing a list}
                            {--windows=true : Queue the Windows version of the apps}
                            {--osx : Queue the OS X version of the apps}
                            {--linux : Queue the Linux version of the apps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a user\'s recently played Steam apps for downloading';

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
        $steamIds = $this->argument('steamIds');

        if (file_exists($steamIds[0])) {
            $steamIds = file_get_contents($steamIds[0]);
            $steamIds = explode("\n", trim($steamIds));
        }

        array_filter($steamIds, function ($steamId) {
            return trim($steamId);
        });

        $users = Steam::user($steamIds[0])->GetPlayerSummaries($steamIds);

        foreach ($users as $user) {

            $this->info('');

            if ($user->communityVisibilityState != 3) {
                $this->warn('Skipping user with private profile: ' . $user->personaName);
                continue;
            }

            $apps = Steam::player($user->steamId)->GetRecentlyPlayedGames();

            if (empty($apps)) {
                $this->warn('Skipping user who has not recently played any apps: ' . $user->personaName);
                continue;
            }

            $this->info('Queuing apps recently played by user: ' . $user->personaName);

            foreach ($apps as $app) {

                // Queue each platform separately
                foreach ($platforms as $platform) {

                    $alreadyQueued = SteamQueueItem::where('app_id', $app->appId)
                        ->where('platform', $platform)
                        ->first();

                    if ($alreadyQueued) {
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
