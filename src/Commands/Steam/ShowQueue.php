<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Helper\Table;


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

        $queue = Capsule::table('steam_queue')
            ->where('status', $status)
            ->orderBy('message')
            ->get();

        if (!count($queue)) {
            return;
        }

        $this->{$messageStyle}(ucfirst($status).':');

        // TODO: Show app name
        $table = new Table($this->output);
        $table->setHeaders(['DB ID', 'App ID', 'Platform', 'Message']);

        foreach ($queue as $item) {
            $table->addRow([$item->id, $item->app_id, $item->platform, $item->message]);
        }
        $table->render();
    }

}