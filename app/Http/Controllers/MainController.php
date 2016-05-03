<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sunra\PhpSimple\HtmlDomParser;
use JonnyW\PhantomJs\Client;
use App\FootballLeague;
use App\FootballMatch;
use App\FootballProfit;

class MainController extends Controller
{
    public function testProfit()
    {
        $id = 1053;
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
            'bet-city',
            'bet365',
            'betfair',
            'betfair-sp',
            'marathon',
            'matchbook',
            'pinnaclesports',
            'sbobet',
            'william-hill',
        );
        foreach ($avaliable_bks as &$avaliable_bk) {
            $avaliable_bk = '/bookmakers/'.$avaliable_bk.'/';
        }
        unset($avaliable_bk);
        $client = Client::getInstance();
        $client->getEngine()->addOption('--disk-cache=true');
        $request = $client->getMessageFactory()->createRequest($url, 'GET');
        $request->setTimeout(30000);
        $response = $client->getMessageFactory()->createResponse();
        $client->send($request, $response);


        $finalArray = array();
        $bests = array();
        $finalUserResponse = array();

        if (($response->getStatus() === 200) || ($response->getStatus() === 408)) {

            $dom = HtmlDomParser::str_get_html($response->getContent());

            $tabActive = $dom->find('#tabBetType ul .active', 0);
            if ($tabActive->plaintext === '1x2') {
                $rows = $dom->find('.odds-panel .odds-table tbody tr');
                foreach ($rows as $key => $row) {
                    if ($row->parent()->tag != 'tbody') continue;

                    $bkname = $row->children(0)->find('.bm-info a')[0]->href;
                    if (!in_array($bkname, $avaliable_bks)) continue;

                    if (substr_count($row->children(1)->class, 'odd-lock') === 0) {
                        $finalArray['1'][$bkname] = $row->children(1)->plaintext;
                    }
                    if (substr_count($row->children(2)->class, 'odd-lock') === 0) {
                        $finalArray['X'][$bkname] = $row->children(2)->plaintext;
                    }
                    if (substr_count($row->children(3)->class, 'odd-lock') === 0) {
                        $finalArray['2'][$bkname] = $row->children(3)->plaintext;
                    }
                }
            }
        } else {
            dd($response);
        }
        $data = array(
            '__EVENTTARGET' => 'BET_TYPE',
            '__EVENTARGUMENT' => '6'
        );
        $request->setMethod('POST');
        $request->setUrl($url);
        $request->setRequestData($data);

        $client->send($request, $response);

        if (($response->getStatus() === 200) || ($response->getStatus() === 408)) {

            $dom = HtmlDomParser::str_get_html($response->getContent());

            $tabActive = $dom->find('#tabBetType ul .active', 0);
            if ($tabActive->plaintext === 'Double Chance') {
                $rows = $dom->find('.odds-panel .odds-table tbody tr');
                foreach ($rows as $key => $row) {
                    if ($row->parent()->tag != 'tbody') continue;
                    $bkname = $row->children(0)->find('.bm-info a')[0]->href;
                    if (!in_array($bkname, $avaliable_bks)) continue;

                    if (substr_count($row->children(1)->class, 'odd-lock') === 0) {
                        $finalArray['1X'][$bkname] = $row->children(1)->plaintext;
                    }
                    if (substr_count($row->children(2)->class, 'odd-lock') === 0) {
                        $finalArray['12'][$bkname] = $row->children(2)->plaintext;
                    }
                    if (substr_count($row->children(3)->class, 'odd-lock') === 0) {
                        $finalArray['X2'][$bkname] = $row->children(3)->plaintext;
                    }
                }
            }
        } else {
            dd($response);
        }
        foreach ($finalArray as $key => $value) {
            arsort($value);
            $bests[$key] = array(
                'bk' => key($value),
                'bet' => current($value),
            );
        }
        if (isset($bests['1']) && isset($bests['X']) && isset($bests['2']))
        {
            $finalUserResponse[] = array(
                'football_match_id' => $id,
                'type' => '1_X_2',
                'profit' => ((2 - (1/$bests['1']['bet'] + 1/$bests['X']['bet'] + 1/$bests['2']['bet'])) * 100),
                'text' => '1 - '.$bests['1']['bk'].' => '.$bests['1']['bet']."; \n".
                    'X - '.$bests['X']['bk'].' => '.$bests['X']['bet']."; \n".
                    '2 - '.$bests['2']['bk'].' => '.$bests['2']['bet']
            );
        }
        if (isset($bests['1X']) && isset($bests['2']))
        {
            $finalUserResponse[] = array(
                'football_match_id' => $id,
                'type' => '1X_2',
                'profit' => ((2 - (1/$bests['1X']['bet'] + 1/$bests['2']['bet'])) * 100),
                'text' => '1X - '.$bests['1X']['bk'].' => '.$bests['1X']['bet']."; \n".
                    '2 - '.$bests['2']['bk'].' => '.$bests['2']['bet']
            );
        }
        if (isset($bests['1']) && isset($bests['X2']))
        {
            $finalUserResponse[] = array(
                'football_match_id' => $id,
                'type' => '1_X2',
                'profit' => ((2 - (1/$bests['1']['bet'] + 1/$bests['X2']['bet'])) * 100),
                'text' => '1 - '.$bests['1']['bk'].' => '.$bests['1']['bet']."; \n".
                    'X2 - '.$bests['X2']['bk'].' => '.$bests['X2']['bet']
            );
        }
        if (isset($bests['1']) && isset($bests['X2']))
        {
            $finalUserResponse[] = array(
                'football_match_id' => $id,
                'type' => '12_X',
                'profit' => ((2 - (1/$bests['12']['bet'] + 1/$bests['X']['bet'])) * 100),
                'text' => '12 - '.$bests['12']['bk'].' => '.$bests['12']['bet']."; \n".
                    'X - '.$bests['X']['bk'].' => '.$bests['X']['bet']
            );
        }
        $types = array();
        foreach($finalUserResponse as $profitData) {
            $types[] = $profitData['type'];
            if ($profit = FootballProfit::where('type', $profitData['type'])
                                        ->where('football_match_id', $profitData['football_match_id'])
                                        ->first()
            ) {
                $profit->profit = $profitData['profit'];
                $profit->text = $profitData['text'];
                $profit->save();
                continue;
            }
            FootballProfit::create($profitData);
        }
        \DB::table('FootballProfits')
            ->where('football_match_id', $id)
            ->whereNotIn('type', $types)
            ->delete();

    }

    public function testLeague()
    {
        $url = 'http://www.bmbets.com/football/';
        $client = Client::getInstance();
        $client->getEngine()->addOption('--disk-cache=true');
        $request = $client->getMessageFactory()->createRequest($url, 'GET');
        $request->setTimeout(30000);
        $response = $client->getMessageFactory()->createResponse();
        $client->send($request, $response);

        if (($response->getStatus() === 200) || ($response->getStatus() === 408)) {

            $dom = HtmlDomParser::str_get_html($response->getContent());

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
        } else {
            dd($response);
        }

    }

    public function testMatch()
    {
        $id = 151;
        $leagueLink = FootballLeague::find($id)->link;
        $url = 'http://www.bmbets.com'.$leagueLink;
        $client = Client::getInstance();
        $client->getEngine()->addOption('--disk-cache=true');
        $request = $client->getMessageFactory()->createRequest($url, 'GET');
        $request->setTimeout(30000);
        $response = $client->getMessageFactory()->createResponse();
        $client->send($request, $response);

        if (($response->getStatus() === 200) || ($response->getStatus() === 408)) {

            $dom = HtmlDomParser::str_get_html($response->getContent());

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

        } else {
            dd($response);
        }

    }

    public function client()
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