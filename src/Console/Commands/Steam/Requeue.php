<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

class Requeue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:requeue
                            {status=failed}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Requeue failed and/or completed item(s) in the download queue';

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

        $query = Capsule::table('steam_queue')
            ->where('status', '<>', 'queued');

        if ($this->argument('status')) {
            $query->where('status', $this->argument('status'));
        }

        $affected = $query->update([
            'status' => 'queued',
            'message' => null,
        ]);

        if (!$affected) {
            $this->error('No items in the queue match the provided criteria');
            die();
        }

        $this->info('Requeued '.$affected.' item(s) in the download queue');
    }
}