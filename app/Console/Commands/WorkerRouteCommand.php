<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sunra\PhpSimple\HtmlDomParser;
use JonnyW\PhantomJs\Client;
use App\FootballLeague;
use App\FootballMatch;
use App\FootballProfit;

class WorkerRouteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:workercommand';

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

    private function fix_json( $j )
    {
        $j = trim( $j );
        $j = ltrim( $j, '(' );
        $j = rtrim( $j, ')' );
        $a = preg_split('#(?<!\\\\)\"#', $j );
        for( $i=0; $i < count( $a ); $i+=2 ){
            $s = $a[$i];
            $s = preg_replace('#([^\s\[\]\{\}\:\,]+):#', '"\1":', $s );
            $a[$i] = $s;
        }
        $j = implode( '"', $a );
        return $j;
    }

    public function handle()
    {
        $worker = new \GearmanWorker();
        $worker->addServer();

        $worker->addFunction('cli_profits_football', function(\GearmanJob $job){

            $id = $job->workload();
            echo 'Started job "Match #'.$id.'"'.PHP_EOL;
            $matchLink = FootballMatch::find($id)->link;
            $url = 'http://www.bmbets.com'.$matchLink;
            $url = preg_replace_callback(
                '/[^0-9a-zA-Z\:\/\.\-_]/i',
                function ($matches) {
                    return urlencode($matches[0]);
                },
                $url
            );
            $avaliable_bks = array(
                '1xbet',
                'bet365',
                'betfair',
                'marathon',
                'matchbook',
                'pinnaclesports',
                'sbobet',
                'william-hill',
                'favbet'
            );

            $finalArray = array();
            $bests = array();
            $finalUserResponse = array();

            //==================================1x2========================================

            $eventArg = 3;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                preg_match('/(<script language="JavaScript">BMBets.Bookmakers =)(?<bookmakers>.*)(<\/script><script language="JavaScript">var dataObject = )(?<odds>.*)(;<\/script><script language="JavaScript">BMBets.EventId)/', $output, $matches);
                $avaliable_bks_keys = array();
                $json = $this->fix_json($matches['bookmakers']);
                $json = str_replace("'", '"', $json);
                $bookmakers = json_decode($json);
                foreach ($bookmakers as $key => $bookmaker) {
                    if (in_array($bookmaker->url, $avaliable_bks)) {
                        $avaliable_bks_keys[$bookmaker->url]=$key;
                    }
                }
                $flipped = array_flip($avaliable_bks_keys);
                $json = $this->fix_json($matches['odds']);
                $json = str_replace("'", '"', $json);
                $odds = json_decode($json);
                if ($odds->odds[0]->t === $eventArg) {
                    foreach($odds->odds[0]->r as $odd){
                        if (in_array($odd->i,$avaliable_bks_keys)) {
                            foreach ($odd->c as $oddc){
                                if (!(property_exists($oddc, 'b')))
                                    $finalArray[$oddc->k][$flipped[$odd->i]] = $oddc->v;
                            }
                        }
                    }
                }
            }

            //==================================DC========================================
            $eventArg = 6;
            $data = array(
                '__EVENTTARGET' => 'BET_TYPE',
                '__EVENTARGUMENT' => $eventArg
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                preg_match('/(<script language="JavaScript">BMBets.Bookmakers =)(?<bookmakers>.*)(<\/script><script language="JavaScript">var dataObject = )(?<odds>.*)(;<\/script><script language="JavaScript">BMBets.EventId)/', $output, $matches);
                $avaliable_bks_keys = array();
                $json = $this->fix_json($matches['bookmakers']);
                $json = str_replace("'", '"', $json);
                $bookmakers = json_decode($json);
                foreach ($bookmakers as $key => $bookmaker) {
                    if (in_array($bookmaker->url, $avaliable_bks)) {
                        $avaliable_bks_keys[$bookmaker->url]=$key;
                    }
                }
                $flipped = array_flip($avaliable_bks_keys);
                $json = $this->fix_json($matches['odds']);
                $json = str_replace("'", '"', $json);
                $odds = json_decode($json);
                if ($odds->odds[0]->t === $eventArg) {
                    foreach($odds->odds[0]->r as $odd){
                        if (in_array($odd->i,$avaliable_bks_keys)) {
                            foreach ($odd->c as $oddc){
                                if (!(property_exists($oddc, 'b')))
                                    $finalArray[$oddc->k][$flipped[$odd->i]] = $oddc->v;
                            }
                        }
                    }
                }
            }

            //==================================Asian Handicap========================================
            $stringType = 'AH';
            $ahTypes = array();
            $eventArg = 1;
            $data = array(
                '__EVENTTARGET' => 'BET_TYPE',
                '__EVENTARGUMENT' => $eventArg
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                preg_match('/(<script language="JavaScript">BMBets.Bookmakers =)(?<bookmakers>.*)(<\/script><script language="JavaScript">var dataObject = )(?<odds>.*)(;<\/script><script language="JavaScript">BMBets.EventId)/', $output, $matches);
                $avaliable_bks_keys = array();
                $json = $this->fix_json($matches['bookmakers']);
                $json = str_replace("'", '"', $json);
                $bookmakers = json_decode($json);
                foreach ($bookmakers as $key => $bookmaker) {
                    if (in_array($bookmaker->url, $avaliable_bks)) {
                        $avaliable_bks_keys[$bookmaker->url]=$key;
                    }
                }
                $flipped = array_flip($avaliable_bks_keys);
                $json = $this->fix_json($matches['odds']);
                $json = str_replace("'", '"', $json);
                $odds = json_decode($json);
                foreach ($odds->odds as $partOdd) {
                    if ($partOdd->t === $eventArg) {
                        foreach($partOdd->r as $odd){
                            if (in_array($odd->i,$avaliable_bks_keys)) {
                                foreach ($odd->c as $oddc){
                                    if (!(property_exists($oddc, 'b'))) {
                                        $finalArray[$stringType.$partOdd->h.'('.$oddc->k.')'][$flipped[$odd->i]] = $oddc->v;
                                        $ahTypes[] = $stringType.$partOdd->h;
                                    }
                                }
                            }
                        }
                    }

                }
                $ahTypes = array_unique($ahTypes);
            }
            foreach ($finalArray as $key => $value) {
                arsort($value);
                $bests[$key] = array(
                    'bk' => key($value),
                    'bet' => current($value),
                );
            }

            if (isset($bests['1']) && isset($bests['X']) && isset($bests['2'])) {
                $finalUserResponse[] = array(
                    'football_match_id' => $id,
                    'type' => '1_X_2',
                    'profit' => ((2 - (1/$bests['1']['bet'] + 1/$bests['X']['bet'] + 1/$bests['2']['bet'])) * 100),
                    'text' => '1 - '.$bests['1']['bk'].' => '.$bests['1']['bet']."; \n".
                        'X - '.$bests['X']['bk'].' => '.$bests['X']['bet']."; \n".
                        '2 - '.$bests['2']['bk'].' => '.$bests['2']['bet']
                );
            }
            if (isset($bests['1X']) && isset($bests['2'])) {
                $finalUserResponse[] = array(
                    'football_match_id' => $id,
                    'type' => '1X_2',
                    'profit' => ((2 - (1/$bests['1X']['bet'] + 1/$bests['2']['bet'])) * 100),
                    'text' => '1X - '.$bests['1X']['bk'].' => '.$bests['1X']['bet']."; \n".
                        '2 - '.$bests['2']['bk'].' => '.$bests['2']['bet']
                );
            }
            if (isset($bests['1']) && isset($bests['X2'])) {
                $finalUserResponse[] = array(
                    'football_match_id' => $id,
                    'type' => '1_X2',
                    'profit' => ((2 - (1/$bests['1']['bet'] + 1/$bests['X2']['bet'])) * 100),
                    'text' => '1 - '.$bests['1']['bk'].' => '.$bests['1']['bet']."; \n".
                        'X2 - '.$bests['X2']['bk'].' => '.$bests['X2']['bet']
                );
            }
            if (isset($bests['12']) && isset($bests['X'])) {
                $finalUserResponse[] = array(
                    'football_match_id' => $id,
                    'type' => '12_X',
                    'profit' => ((2 - (1/$bests['12']['bet'] + 1/$bests['X']['bet'])) * 100),
                    'text' => '12 - '.$bests['12']['bk'].' => '.$bests['12']['bet']."; \n".
                        'X - '.$bests['X']['bk'].' => '.$bests['X']['bet']
                );
            }
            foreach ($ahTypes as $ahType) {
                if (isset($bests[$ahType.'(1)']) && isset($bests[$ahType.'(2)'])) {
                    $finalUserResponse[] = array(
                        'football_match_id' => $id,
                        'type' => $ahType,
                        'profit' => ((2 - (1/$bests[$ahType.'(1)']['bet'] + 1/$bests[$ahType.'(2)']['bet'])) * 100),
                        'text' => $ahType.'(1) - '.$bests[$ahType.'(1)']['bk'].' => '.$bests[$ahType.'(1)']['bet']."; \n".
                            $ahType.'(2) - '.$bests[$ahType.'(2)']['bk'].' => '.$bests[$ahType.'(2)']['bet']
                    );
                }
            }
            $types = array();
            foreach($finalUserResponse as $profitData) {
                $types[] = $profitData['type'];
                if ($profit = FootballProfit::where('type', $profitData['type'])
                    ->where('football_match_id', $profitData['football_match_id'])
                    ->first()
                ) {
                    if ($profitData['profit'] > 100) {
                        $profit->profit = $profitData['profit'];
                        $profit->text = $profitData['text'];
                        $profit->save();
                    } else {
                        $profit->delete();
                    }
                } else {
                    if ($profitData['profit'] > 100) {
                        FootballProfit::create($profitData);
                    }
                }
            }
            \DB::table('FootballProfits')
                ->where('football_match_id', $id)
                ->whereNotIn('type', $types)
                ->delete()
            ;
            echo 'Finished job "Match #'.$id.'"'.PHP_EOL.PHP_EOL;
        });

        $worker->addFunction('cli_leagues_football', function(\GearmanJob $job){
            echo 'Started job "Leagues (Football)"'.PHP_EOL;
            $url = 'http://www.bmbets.com/football/';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {

                $dom = HtmlDomParser::str_get_html($output);

                $elements = $dom->find('.country-table .m-count');
                $links = array();
                foreach ($elements as $key => $elem) {
                    $count = preg_replace("/[^0-9]/", '', $elem->plaintext);
                    $link = $elem->parent()->find('a', 0);
                    $links[] = $link->href;
                    if ($league = FootballLeague::where('link', $link->href)->first()) {
                        $league->count = $count;
                        $league->save();
                        continue;
                    }
                    $leagueData = array(
                        'title' => $link->plaintext,
                        'link' => $link->href,
                        'count' => $count,
                    );
                    FootballLeague::create($leagueData);
                }
                //FootballLeague::whereNotIn('link', $links)->delete();
                \DB::table('FootballLeagues')
                    ->whereNotIn('link', $links)
                    ->delete();
            }
            echo 'Finished job "Leagues"'.PHP_EOL.PHP_EOL;
        });

        $worker->addFunction('cli_matches_football', function(\GearmanJob $job){
            $id = $job->workload();
            echo 'Started job "League #'.$id.'"'.PHP_EOL;
            $leagueLink = FootballLeague::find($id)->link;
            $url = 'http://www.bmbets.com'.$leagueLink;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {

                $dom = HtmlDomParser::str_get_html($output);

                $elements = $dom->find('.odds-table tr a');
                $links = array();
                foreach ($elements as $key => $elem) {
                    $links[] = $elem->href;
                    if ($match = FootballMatch::where('link', $elem->href)->first()) {
                        continue;
                    }
                    $matchData = array(
                        'football_league_id' => $id,
                        'title' => $elem->plaintext,
                        'link' => $elem->href,
                    );
                    FootballMatch::create($matchData);
                }
                \DB::table('FootballMatches')
                    ->where('football_league_id', $id)
                    ->whereNotIn('link', $links)
                    ->delete();

            }
            echo 'Finished job "League #'.$id.'"'.PHP_EOL.PHP_EOL;
        });

        while ($worker->work());
    }
}
