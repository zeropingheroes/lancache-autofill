<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InitialiseDownloadsDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initialise-downloads-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise the downloads directory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {       
        if ($this->confirm('Are you sure you wish to remove all files and folders in "'.getenv('DOWNLOADS_DIRECTORY').'"?')) {
            $remove = new Process('rm -r '.getenv('DOWNLOADS_DIRECTORY'));
            $remove->run(function ($type, $buffer) {

                if (Process::ERR === $type) {
                    $this->error(str_replace(["\r", "\n"], '', $buffer));
                } else {
                    $this->line(str_replace(["\r", "\n"], '', $buffer));
                }

            });

            $create = new Process('mkdir -p '.getenv('DOWNLOADS_DIRECTORY'));
            $create->run();

            if( $remove->isSuccessful() && $create->isSuccessful() ) {
                $this->info('Successfully removed all files and folders in "'.getenv('DOWNLOADS_DIRECTORY').'"');
                die();
            }
            $this->error('Unable to remove all files and folders in "'.getenv('DOWNLOADS_DIRECTORY').'"');
        }
    }
}