<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Zeropingheroes\LancacheAutofill\Models\SteamAccount;
use Zeropingheroes\LancacheAutofill\Services\SteamCmd\SteamCmd;

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

        if (!$account) {
            $account = $this->ask('Please enter your Steam username');
        }

        $this->info('Authorising account '.$account);
        $password = $this->secret('Please enter your password');
        $guard = $this->ask('Please enter your Steam Guard code (optional)', false);

        $steamCmd = (new SteamCmd(getenv('STEAMCMD_PATH')))
            ->login($account, $password, $guard)
            ->run();

        // Show SteamCMD output line by line
        $steamCmd->run(function ($type, $buffer) {
            $this->line(str_replace(["\r", "\n"], '', $buffer));
        });

        if (!$steamCmd->isSuccessful()) {
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