<?php

namespace Zeropingheroes\LancacheAutofill\Console\Commands\Steam;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Initialise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:initialise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise Steam';

    /**
     * The URL to get the SteamCMD binary from.
     *
     * @var string
     */
    const STEAMCMD_URL = 'http://media.steampowered.com/client/steamcmd_linux.tar.gz';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $steamCmdDirectory = dirname(getenv('STEAMCMD_PATH'));

        if ($this->confirm('Are you sure you wish to initialise SteamCMD"? This will remove the directory "'.$steamCmdDirectory.'"')) {

            $process['remove'] = new Process('rm -rf '.$steamCmdDirectory);
            $process['create'] = new Process('mkdir -p '.$steamCmdDirectory.' && cd '.$steamCmdDirectory.' && curl -sqL "'.self::STEAMCMD_URL.'" | tar zxvf -');
            $process['run'] = new Process('unbuffer '.getenv('STEAMCMD_PATH').' +login anonymous +quit');

            foreach ($process as $process) {
                $process->run(function ($type, $buffer) {

                    if (Process::ERR === $type) {
                        $this->error(str_replace(["\r", "\n"], '', $buffer));
                    }
                    else {
                        $this->line(str_replace(["\r", "\n"], '', $buffer));
                    }

                });
                if (!$process->isSuccessful()) {
                    $this->error('Error initialising SteamCMD');
                    die();
                }
            }
            $this->info('Successfully initialised SteamCMD');
        }
    }
}