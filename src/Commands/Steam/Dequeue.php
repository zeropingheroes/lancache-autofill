<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class Dequeue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:dequeue
                            {--app_id=}
                            {--platform=}
                            {--status=}
                            {--message=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove item(s) from the download queue';

    /**
     * The permissible platforms.
     *
     * @var array
     */
    const PLATFORMS = ['windows', 'osx', 'linux'];

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
        if ($this->option('platform') && !in_array($this->option('platform'), $this::PLATFORMS)) {
            $this->error('Invalid platform specified. Available platforms are: '.implode(' ', $this::PLATFORMS));
            die();
        }

        if ($this->option('status') && !in_array($this->option('status'), $this::STATUSES)) {
            $this->error('Invalid status specified. Available statuses are: '.implode(' ', $this::STATUSES));
            die();
        }

        $query = Capsule::table('steam_queue');

        if ($this->option('app_id')) {
            $query->where('app_id', $this->option('app_id'));
        }

        if ($this->option('platform')) {
            $query->where('platform', $this->option('platform'));
        }

        if ($this->option('status')) {
            $query->where('status', $this->option('status'));
        }

        if ($this->option('message')) {
            $query->where('message', 'like', '%'.$this->option('message').'%');
        }

        // If no options were specified, ask for confirmation
        if (!array_filter($this->options()) && !$this->confirm('Are you sure you want to clear the download queue?')) {
            die();
        }

        $affected = $query->delete();

        if (!$affected) {
            $this->error('No items in the queue match the provided criteria');
            die();
        }

        $this->info('Removed '.$affected.' item(s) from the download queue');
    }
}