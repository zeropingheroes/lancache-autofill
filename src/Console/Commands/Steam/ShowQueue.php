<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;

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
        if( $this->argument('status') && ! in_array($this->argument('status'), $this::STATUSES))
        {
            $this->error('Invalid status specified. Available statuses are: '. implode(' ', $this::STATUSES));
            die();
        }

        // If a status is specified, display only apps of that status
        if( $this->argument('status') )
        {
            $this->displayAppsWithStatus( $this->argument('status') );
            die();
        }

        foreach($this::STATUSES as $status)
        {
            $this->displayAppsWithStatus($status);
        }
    }

    private function displayAppsWithStatus( $status )
    {
        switch($status)
        {
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

        $apps = Capsule::table('steam_queue')
                        ->where('status', $status)
                        ->orderBy('message')
                        ->get();

        if ( ! count($apps) )
            return;


        $this->{$messageStyle}(ucfirst($status) . ':');

        foreach( $apps as $app )
        {
            $this->{$messageStyle}( $app->appid ."\t". $app->name . "\t" . $app->platform . "\t" . $app->account . "\t" . $app->message );
        }
    }

}