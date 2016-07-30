<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\FootballLeague;
use App\FootballMatch;
use App\FootballProfit;

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
        $client = new \GearmanClient();
        $client->addServer();
        $start = microtime(true);

        $client->addTask('cli_leagues_football', 'lol');

        $client->runTasks();

        $totaltime = number_format(microtime(true) - $start, 2);
        dump("Got leagues in: $totaltime seconds");

        foreach (FootballLeague::all() as $league) {
            $client->addTask('cli_matches_football', (string)$league->id);
        }
        $start = microtime(true);
        $client->runTasks();

        $totaltime = number_format(microtime(true) - $start, 2);
        dump("Got matches in: $totaltime seconds");

        foreach (FootballMatch::all() as $match) {
            $client->addTask('cli_profits_football', (string)$match->id);
        }
        $start = microtime(true);
        $client->runTasks();

        $totaltime = number_format(microtime(true) - $start, 2);
        dump("Got profits in: $totaltime seconds");
    }
}
