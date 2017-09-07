<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Zeropingheroes\LancacheAutofill\Models\SteamQueueItem;


class ShowQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:show-queue {status?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the Steam app download queue';

    /**
     * The permissible statuses.
     *
     * @var array
     */
    const STATUSES = ['queued', 'completed', 'failed'];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('status') && !in_array($this->argument('status'), $this::STATUSES)) {
            $this->error('Invalid status specified. Available statuses are: '.implode(' ', $this::STATUSES));
            die();
        }

        // If a status is specified, display only apps of that status
        if ($this->argument('status')) {
            $this->displayAppsWithStatus($this->argument('status'));
            die();
        }

        foreach ($this::STATUSES as $status) {
            $this->displayAppsWithStatus($status);
        }
    }

    /**
     * Display apps in the queue for a given status
     *
     * @param $status string
     * @return void
     */
    private function displayAppsWithStatus($status)
    {
        switch ($status) {
            case 'queued':
                $messageStyle = 'comment';
                break;
            case 'completed':
                $messageStyle = 'info';
                break;
            case 'failed':
                $messageStyle = 'error';
                break;
            default:
                $messageStyle = 'info';
        }

        $steamQueueItems = SteamQueueItem::where('status', $status)
            ->orderBy('message')
            ->get();

        if (!count($steamQueueItems)) {
            return;
        }

        $this->{$messageStyle}(ucfirst($status).':');

        $table = new Table($this->output);
        $table->setHeaders(['Name', 'Platform', 'App ID', 'Message']);

        foreach ($steamQueueItems as $steamQueueItem) {
            $table->addRow([
                $steamQueueItem->app->name,
                ucfirst($steamQueueItem->platform),
                $steamQueueItem->app_id,
                $steamQueueItem->message,
            ]);
        }
        $table->render();
    }

}