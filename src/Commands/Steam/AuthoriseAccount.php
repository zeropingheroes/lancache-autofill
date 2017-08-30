<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Zeropingheroes\LancacheAutofill\Models\SteamAccount;

class AuthoriseAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:authorise-account {account?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Authorise a Steam account to allow download of apps in their library';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account = $this->argument('account');

        if( ! $account ){
            $account = $this->ask('Please enter your Steam username');
        }

        $this->info('Authorising account '.$account);
        $password = $this->secret('Please enter your password');
        $steamGuardCode = $this->ask('Please enter your Steam Guard code', false);

        // Start SteamCMD with the arguments, using "unbuffer"
        // as SteamCMD buffers output when it is not run in a
        // tty, which prevents us showing output line by line
        $process = new Process('unbuffer '.getenv('STEAMCMD_PATH').' +login '.$account.' '.$password.' '.$steamGuardCode.' +quit');

        // Set a short timeout for this interactive login prompt
        $process->setTimeout(120);

        // Show SteamCMD output line by line
        $process->run(function ($type, $buffer) {
            $this->line(str_replace(["\r", "\n"], '', $buffer));
        });

        if (!$process->isSuccessful()) {
            $this->error('Failed to authorise Steam account '.$account);
            die();
        }

        if (SteamAccount::firstOrCreate(['username' => $account])) {
            $this->info('Successfully authorised Steam account '.$account);
        }
        else {
            $this->error('Failed to add account '.$account.' to database');
        }
    }
}