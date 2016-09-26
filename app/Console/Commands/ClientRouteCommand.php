<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\League;
use App\Match;
use App\Profit;
use Illuminate\Support\Facades\DB;

class ClientRouteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:clientcommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while (true) {
            $switcher = DB::table('ParcerSwitcher')->first();
            if ($switcher && ($switcher->enabled)) {
                $client = new \GearmanClient();
                $client->addServer();
                $start = microtime(true);

                $client->addTask('cli_leagues', 'lol');

                $client->runTasks();

                $totaltime = number_format(microtime(true) - $start, 2);
                dump("Got leagues in: $totaltime seconds");

                foreach (League::all() as $league) {
                    $client->addTask('cli_matches', (string)$league->id);
                }
                $start = microtime(true);
                $client->runTasks();

                $totaltime = number_format(microtime(true) - $start, 2);
                dump("Got matches in: $totaltime seconds");

                foreach (Match::all() as $match) {
                    $client->addTask('cli_profits', (string)$match->id);
                }
                $start = microtime(true);
                $client->runTasks();

                $totaltime = number_format(microtime(true) - $start, 2);
                dump("Got profits in: $totaltime seconds");

            } else {
                dump("Parcer is disabled. Go into DB and switch variable");
            }
            sleep(2);
        }
    }
}
