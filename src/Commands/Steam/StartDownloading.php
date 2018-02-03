<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Zeropingheroes\LancacheAutofill\Services\SteamCmd\SteamCmd;
use Zeropingheroes\LancacheAutofill\Models\SteamQueueItem;
use Zeropingheroes\LancacheAutofill\Models\SteamAccount;

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

        $this->checkSteamAccountsAreAuthorised();

        // Loop through all apps in the queue
        while ($item = $this->nextApp()) {

            // Attempt download using each authorised Steam account in turn
            foreach ($this->steamAccounts() as $account) {

                if (isset($item->app->name)) {
                    $appName = $item->app->name;
                } else {
                    $appName = 'Unknown';
                }

                $this->info('Starting download of ' . $appName . ' (App ID: ' . $item->app_id . ') for ' . $item->platform . ' from Steam account "' . $account . '"');

                try {

                    $downloadPath = getenv('DOWNLOADS_DIRECTORY') . '/' . $item->platform . '/' . $item->app_id;

                    $steamCmd = (new SteamCmd(getenv('STEAMCMD_PATH')))
                        ->login($account)
                        ->platform($item->platform)
                        ->directory($downloadPath)
                        ->update($item->app_id)
                        ->run();

                    // Show SteamCMD output line by line
                    $steamCmd->run(function ($type, $buffer) {
                        $this->line(str_replace(["\r", "\n"], '', $buffer));
                    });

                    if (!$steamCmd->isSuccessful()) {
                        throw new ProcessFailedException($steamCmd);
                    }

                    $this->info('Successfully completed download of ' . $appName . ' (App ID: ' . $item->app_id . ') for ' . $item->platform . '. Deleting from disk.');
                    $this->updateQueueItemStatus($item->id, 'completed');

                    // Delete download directory
                    $remove = new Process('rm -rf ' . $downloadPath);
                    $remove->run(function ($type, $buffer) {

                        if (Process::ERR === $type) {
                            $this->error(str_replace(["\r", "\n"], '', $buffer));
                        } else {
                            $this->line(str_replace(["\r", "\n"], '', $buffer));
                        }

                    });

                    // As the download was successful, do not attempt to download using any other Steam accounts
                    break;

                } catch (ProcessFailedException $e) {

                    // Create an array of SteamCMD's output (removing excess newlines)
                    $lines = explode(PHP_EOL, trim($steamCmd->getOutput()));

                    // Remove lines that don't contain the text "error"
                    $linesContainingError = array_where($lines, function ($value, $key) {
                        return str_contains(strtolower($value), 'error');
                    });

                    // Collect all errors
                    $message = implode(PHP_EOL, $linesContainingError);

                    // Removing ANSI codes
                    $message = preg_replace('#\x1b\[[0-9;]*[a-zA-Z]#', '', $message);

                    $this->error('Failed to download ' . $appName . ' (App ID: ' . $item->app_id . ') for ' . $item->platform . ' from Steam account ' . $account);
                    $this->updateQueueItemStatus($item->id, 'failed', $message);
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
        return SteamQueueItem::where('status', 'queued')
            ->orderBy('popularity', 'desc')
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
        return SteamQueueItem::where('id', $id)
            ->update(['status' => $status, 'message' => $message]);
    }

    /**
     * Get total number of items in queue
     *
     * @return int
     */
    private function queuedItems()
    {
        return SteamQueueItem::where('status', 'queued')
            ->count();
    }

    /**
     * Get collection of accounts specified to download apps
     *
     * @return \Illuminate\Support\Collection
     */
    private function steamAccounts()
    {
        return SteamAccount::all()->pluck('username')->toArray();
    }

    /**
     * Check all Steam accounts specified in the accounts table are authorised
     */
    private function checkSteamAccountsAreAuthorised()
    {
        $accounts = $this->steamAccounts();

        if ( ! $accounts) {
            $this->error('No Steam accounts authorised');
            $this->comment('');
            $this->comment('Please run: ');
            $this->comment('    ./lancache-autofill steam:authorise-account');
            $this->comment('');

            die();
        }

        $this->info('Checking all Steam accounts are authorised');

        foreach ($accounts as $account) {
            $this->info('Checking Steam account ' . $account . '...');
            $steamCmd = (new SteamCmd(getenv('STEAMCMD_PATH')))
                ->login($account)
                ->run();

            // Show SteamCMD output line by line
            $steamCmd->run(function ($type, $buffer) {
                $this->line(str_replace(["\r", "\n"], '', $buffer));
            });

            if (!$steamCmd->isSuccessful()) {
                $this->error('Steam account ' . $account . ' is not authorised');
                $this->comment('');
                $this->comment('Please re-run:');
                $this->comment('     ./lancache-autofill steam:authorise-account ' . $account . '"');
                $this->info('');

                die();
            }
            $this->info('Steam account ' . $account . ' is authorised and will be used to download apps');
        }
    }
}