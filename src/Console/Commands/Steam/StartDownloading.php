<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class StartDownloading extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:start-downloading';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start downloading the Steam apps in the queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->queuedItems() == 0) {
            $this->error('Nothing to download');
            $this->info('Run "./lancache-autofill steam:show-queue" to see the queue');
            die();
        }

        // Check all Steam accounts specified in the accounts table are authorised
        $this->info('Checking all Steam accounts are authorised');
        foreach ($this->steamAccounts() as $account) {
            $process = new Process('unbuffer '.getenv('STEAMCMD_PATH').' +@NoPromptForPassword 1 +login '.$account.'  +quit');

            // Show SteamCMD output line by line
            $process->run(function ($type, $buffer) {
                $this->line(str_replace(["\r", "\n"], '', $buffer));
            });

            if (!$process->isSuccessful()) {
                $this->error('Steam account '.$account.' is not authorised');
                $this->comment('Please re-run "./lancache-autofill steam:authorise-account '.$account.'"');
                die();
            }
            $this->info('Steam account '.$account.' is authorised and will be used to download apps');
        }

        // Loop through all apps in the queue
        while ($item = $this->nextApp()) {

            // Attempt download using each authorised Steam account in turn
            foreach ($this->steamAccounts() as $account) {

                $this->info('Starting download of '.$item->app_id.' for '.$item->platform.' from Steam account '.$account);

                try {
                    $this->download($item->app_id, $item->platform, $account);

                    $this->info('Successfully completed download of '.$item->app_id.' for '.$item->platform.' from Steam account '.$account);
                    $this->updateQueueItemStatus($item->id, 'completed');

                    // As the download was successful, do not attempt to download using any other Steam accounts
                    break;

                } catch (ProcessFailedException $e) {

                    // Create an array of SteamCMD's output (removing excess newlines)
                    $lines = explode(PHP_EOL, trim($process->getOutput()));

                    // Get the last line (removing ANSI codes)
                    $lastLine = preg_replace('#\x1b\[[0-9;]*[a-zA-Z]#', '', end($lines));

                    $this->error('Failed to download '.$item->app_id.' for '.$item->platform.' from Steam account '.$account);
                    $this->updateQueueItemStatus($item->id, 'failed', $lastLine);
                }
            }
        }
    }

    /**
     * Return the next app in the queue
     *
     * @return mixed
     */
    private function nextApp()
    {
        return Capsule::table('steam_queue')
            ->where('status', 'queued')
            ->first();
    }

    /**
     * Update an item's status in the queue
     *
     * @param $id
     * @param $status
     * @param null $message
     * @return int
     */
    private function updateQueueItemStatus($id, $status, $message = null)
    {
        return Capsule::table('steam_queue')
            ->where('id', $id)
            ->update(['status' => $status, 'message' => $message]);
    }

    /**
     * Get total number of items in queue
     *
     * @return int
     */
    private function queuedItems()
    {
        return Capsule::table('steam_queue')
            ->where('status', 'queued')
            ->count();
    }

    /**
     * Get collection of accounts specified to download apps
     *
     * @return \Illuminate\Support\Collection
     */
    private function steamAccounts()
    {
        return Capsule::table('steam_accounts')->pluck('username');
    }

    /**
     * Start a Steam download
     *
     * @param $appId
     * @param $account
     * @param $platform
     * @throws ProcessFailedException
     */
    private function download($appId, $platform, $account)
    {
        $arguments =
            [
                'login' => $account,
                '@sSteamCmdForcePlatformType' => $platform,
                '@NoPromptForPassword' => 1,
                'force_install_dir' => getenv('DOWNLOADS_DIRECTORY').'/'.$platform.'/'.$appId,
                'app_license_request' => $appId,
                'app_update' => $appId,
                'quit' => null,
            ];

        // Build argument string
        foreach ($arguments as $argument => $value) {
            $argumentString .= "+$argument $value ";
        }

        // Start SteamCMD with the arguments, using "unbuffer"
        // as SteamCMD buffers output when it is not run in a
        // tty, which prevents us showing output line by line
        $download = new Process('unbuffer '.getenv('STEAMCMD_PATH').' '.$argumentString);

        // Set a long timeout as downloading could take a while
        $download->setTimeout(14400);
        $download->setIdleTimeout(60);

        // Show SteamCMD output line by line
        $download->run(function ($type, $buffer) {
            $this->line(str_replace(["\r", "\n"], '', $buffer));
        });

        if (!$download->isSuccessful()) {
            throw new ProcessFailedException($download);
        }
    }
}