<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DeleteDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-downloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete downloads from the disk';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {       
        if ($this->confirm('Are you sure you wish to remove all files and folders in "'.getenv('DOWNLOAD_LOCATION').'"?')) {
            $remove = new Process('rm -r '.getenv('DOWNLOAD_LOCATION'));
            $remove->run(function ($type, $buffer) {

                if (Process::ERR === $type) {
                    $this->error(str_replace(["\r", "\n"], '', $buffer));
                } else {
                    $this->line(str_replace(["\r", "\n"], '', $buffer));
                }

            });

            $create = new Process('mkdir -p '.getenv('DOWNLOAD_LOCATION'));
            $create->run();

            if( $remove->isSuccessful() && $create->isSuccessful() ) {
                $this->info('Successfully removed all files and folders in "'.getenv('DOWNLOAD_LOCATION').'"');
                die();
            }
            $this->error('Unable to remove all files and folders in "'.getenv('DOWNLOAD_LOCATION').'"');
        }
    }
}