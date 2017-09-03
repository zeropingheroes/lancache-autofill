<?php

namespace Zeropingheroes\LancacheAutofill\Commands\Steam;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Helper\Table;


class SearchApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:search-apps {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search Steam apps by name';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = Capsule::table('steam_apps')
            ->where('name', 'like', '%'.$this->argument('name').'%')
            ->get();

        $table = new Table($this->output);
        $table->setHeaders(['App ID', 'Name']);

        foreach ($apps as $app) {
            $table->addRow([$app->id, $app->name]);
        }

        $table->render();
    }
}