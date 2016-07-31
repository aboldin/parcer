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
            $matchId = FootballMatch::find($id)->link_id;
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
            $avaliable_bks_keys = array();
            $json = '{"1":{"id":1,"name":"Betfair","url":"betfair","isAff":true},"2":{"id":2,"name":"PinnacleSports","url":"pinnaclesports","isAff":true},"4":{"id":4,"name":"PariMatch","url":"parimatch","isAff":true},"5":{"id":5,"name":"Bet City","url":"bet-city","isAff":true},"6":{"id":6,"name":"Toto","url":"toto","isAff":true},"7":{"id":7,"name":"BWin","url":"bwin","isAff":true},"8":{"id":8,"name":"BetAtHome","url":"betathome","isAff":true},"9":{"id":9,"name":"Vivaro","url":"vivaro","isAff":true},"13":{"id":13,"name":"Betclic","url":"betclic","isAff":true},"14":{"id":14,"name":"William Hill","url":"william-hill","isAff":true},"16":{"id":16,"name":"Unibet","url":"unibet","isAff":true},"17":{"id":17,"name":"Marathon","url":"marathon","isAff":true},"18":{"id":18,"name":"Intertops","url":"intertops","isAff":true},"21":{"id":21,"name":"Betgun","url":"betgun","isAff":true},"22":{"id":22,"name":"Interwetten","url":"interwetten","isAff":true},"24":{"id":24,"name":"Noxwin","url":"noxwin","isAff":true},"25":{"id":25,"name":"Mybet","url":"mybet","isAff":true},"27":{"id":27,"name":"Betsafe","url":"betsafe","isAff":true},"29":{"id":29,"name":"5Dimes","url":"5dimes","isAff":true},"30":{"id":30,"name":"10Bet","url":"10bet","isAff":true},"31":{"id":31,"name":"Ladbrokes","url":"ladbrokes","isAff":true},"32":{"id":32,"name":"Betfred","url":"betfred","isAff":true},"35":{"id":35,"name":"Sportbet","url":"sportbet","isAff":true},"38":{"id":38,"name":"BetDSI","url":"betdsi","isAff":true},"39":{"id":39,"name":"LeonBets","url":"leonbets","isAff":true},"42":{"id":42,"name":"188Bet","url":"188bet","isAff":true},"43":{"id":43,"name":"bet365","url":"bet365","isAff":true},"44":{"id":44,"name":"888sport","url":"888sport","isAff":true},"46":{"id":46,"name":"Bet US","url":"bet-us","isAff":true},"47":{"id":47,"name":"Betdaq","url":"betdaq","isAff":true},"48":{"id":48,"name":"Oddmaker","url":"oddmaker","isAff":true},"50":{"id":50,"name":"BetRedKings","url":"betredkings","isAff":true},"51":{"id":51,"name":"Betsson","url":"betsson","isAff":true},"52":{"id":52,"name":"GoldBet","url":"goldbet","isAff":true},"53":{"id":53,"name":"The Greek","url":"the-greek","isAff":true},"54":{"id":54,"name":"DOXXbet","url":"doxxbet","isAff":true},"55":{"id":55,"name":"youwin","url":"youwin","isAff":true},"57":{"id":57,"name":"Jetbull","url":"jetbull","isAff":true},"60":{"id":60,"name":"BetOnline","url":"betonline","isAff":true},"63":{"id":63,"name":"Bet3000","url":"bet3000","isAff":true},"64":{"id":64,"name":"Tipico","url":"tipico","isAff":true},"65":{"id":65,"name":"BetVictor","url":"betvictor","isAff":true},"71":{"id":71,"name":"Fonbet","url":"fonbet","isAff":true},"73":{"id":73,"name":"Favbet","url":"favbet","isAff":true},"77":{"id":77,"name":"Dafabet","url":"dafabet","isAff":true},"80":{"id":80,"name":"Topbet","url":"topbet","isAff":true},"81":{"id":81,"name":"Titanbet","url":"titanbet","isAff":true},"82":{"id":82,"name":"1Bet","url":"1bet","isAff":true},"83":{"id":83,"name":"Fortuna.cz","url":"fortunacz","isAff":true},"85":{"id":85,"name":"M88","url":"m88","isAff":true},"86":{"id":86,"name":"Smarkets","url":"smarkets","isAff":true},"88":{"id":88,"name":"Sbobet","url":"sbobet","isAff":true},"91":{"id":91,"name":"Coral","url":"coral","isAff":true},"93":{"id":93,"name":"Matchbook","url":"matchbook","isAff":true},"95":{"id":95,"name":"Betrally","url":"betrally","isAff":true},"96":{"id":96,"name":"Winner","url":"winner","isAff":true},"97":{"id":97,"name":"Netbet","url":"netbet","isAff":true},"98":{"id":98,"name":"Smart Live","url":"smart-live","isAff":true},"100":{"id":100,"name":"SkyBet","url":"skybet","isAff":true},"101":{"id":101,"name":"RoyalSports","url":"royalsports","isAff":true},"103":{"id":103,"name":"18Bet","url":"18bet","isAff":true},"104":{"id":104,"name":"bet88sports","url":"bet88sports","isAff":true},"105":{"id":105,"name":"1xBet","url":"1xbet","isAff":true},"106":{"id":106,"name":"Betkurus","url":"betkurus","isAff":true},"107":{"id":107,"name":"Winlinebet","url":"winlinebet","isAff":true},"108":{"id":108,"name":"VitalBet","url":"vitalbet","isAff":true},"109":{"id":109,"name":"Betfair SP","url":"betfair-sp","isAff":true},"110":{"id":110,"name":"TonyBet","url":"tonybet","isAff":true},"111":{"id":111,"name":"Supermatch","url":"supermatch","isAff":true},"112":{"id":112,"name":"Bets10","url":"bets10","isAff":true},"113":{"id":113,"name":"SportingBet","url":"sportingbet","isAff":true},"114":{"id":114,"name":"138.com","url":"138com","isAff":true},"116":{"id":116,"name":"Roisbet","url":"roisbet","isAff":true}}';
            $bookmakers = json_decode($json);
            foreach ($bookmakers as $key => $bookmaker) {
                if (in_array($bookmaker->url, $avaliable_bks)) {
                    $avaliable_bks_keys[$bookmaker->url]=$key;
                }
            }
            $flipped = array_flip($avaliable_bks_keys);
            $finalArray = array();
            $bests = array();
            $finalUserResponse = array();

            //==================================1x2========================================

            $eventArg = 3;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'eId' => $matchId,
                'bId' => $eventArg
            ));
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                //$json = $this->fix_json($matches['odds']);
                //$json = str_replace("'", '"', $json);
                $odds = json_decode($output);
                if ((property_exists($odds, 'odds'))) {
                    if ($odds->odds[0]->t === $eventArg) {
                        foreach($odds->odds[0]->r as $odd){
                            if (in_array($odd->i,$avaliable_bks_keys)) {
                                foreach ($odd->c as $oddc){
                                    if ($oddc->b == 0)
                                        $finalArray[$oddc->k][$flipped[$odd->i]] = $oddc->v;
                                }
                            }
                        }
                    }
                }
            }
            //==================================DC========================================
            $eventArg = 6;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'eId' => $matchId,
                'bId' => $eventArg
            ));
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                $odds = json_decode($output);
                if ((property_exists($odds, 'odds'))) {
                    if ($odds->odds[0]->t === $eventArg) {
                        foreach($odds->odds[0]->r as $odd){
                            if (in_array($odd->i,$avaliable_bks_keys)) {
                                foreach ($odd->c as $oddc){
                                    if ($oddc->b == 0)
                                        $finalArray[$oddc->k][$flipped[$odd->i]] = $oddc->v;
                                }
                            }
                        }
                    }
                }
            }

            //==================================Asian Handicap========================================
            $stringType = 'AH';
            $ahTypes = array();
            $eventArg = 1;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'eId' => $matchId,
                'bId' => $eventArg
            ));
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (($httpcode === 200) || ($httpcode === 408)) {
                $odds = json_decode($output);
                if ((property_exists($odds, 'odds'))) {
                    foreach ($odds->odds as $partOdd) {
                        if ($partOdd->t === $eventArg) {
                            foreach($partOdd->r as $odd){
                                if (in_array($odd->i,$avaliable_bks_keys)) {
                                    foreach ($odd->c as $oddc){
                                        if ($oddc->b == 0) {
                                            $finalArray[$stringType.$partOdd->h.'('.$oddc->k.')'][$flipped[$odd->i]] = $oddc->v;
                                            $ahTypes[] = $stringType.$partOdd->h;
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                $ahTypes = array_unique($ahTypes);
            }

            //==================================Totals========================================
            $stringType = 'OU';
            $ouTypes = array();
            $eventArg = 4;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');
            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'eId' => $matchId,
                'bId' => $eventArg
            ));
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {

                $odds = json_decode($output);
                if ((property_exists($odds, 'odds'))) {
                    foreach ($odds->odds as $partOdd) {
                        if ($partOdd->t === $eventArg) {
                            foreach($partOdd->r as $odd){
                                if (in_array($odd->i,$avaliable_bks_keys)) {
                                    foreach ($odd->c as $oddc){
                                        if ($oddc->b == 0) {

                                            $finalArray[$stringType.$partOdd->h.'('.$oddc->k.')'][$flipped[$odd->i]] = $oddc->v;
                                            $ouTypes[] = $stringType.$partOdd->h;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $ouTypes = array_unique($ouTypes);
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
            foreach ($ouTypes as $ouType) {
                if (isset($bests[$ouType.'(Under)']) && isset($bests[$ouType.'(Under)'])) {
                    $finalUserResponse[] = array(
                        'football_match_id' => $id,
                        'type' => $ouType,
                        'profit' => ((2 - (1/$bests[$ouType.'(Over)']['bet'] + 1/$bests[$ouType.'(Under)']['bet'])) * 100),
                        'text' => $ouType.'(Over) - '.$bests[$ouType.'(Over)']['bk'].' => '.$bests[$ouType.'(Over)']['bet']."; \n".
                            $ouType.'(Under) - '.$bests[$ouType.'(Under)']['bk'].' => '.$bests[$ouType.'(Under)']['bet']
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
                    preg_match('/^.*(?<link_id>\d+)\/?$/isU', $elem->href, $matches);
                    $link_id = $matches['link_id'];
                    $links[] = $elem->href;
                    if ($match = FootballMatch::where('link', $elem->href)->first()) {
                        continue;
                    }
                    $matchData = array(
                        'football_league_id' => $id,
                        'title' => $elem->plaintext,
                        'link' => $elem->href,
                        'link_id' => $link_id,
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
