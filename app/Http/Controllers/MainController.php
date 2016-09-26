<?php

namespace App\Http\Controllers;

use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Http\Request;
use Sunra\PhpSimple\HtmlDomParser;
use App\League;
use App\Match;
use App\Profit;
use App\History;
use App\SportType;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    private function wrapIt($id, $bests, $firstKey, $secondKey, $type)
    {
        if (isset($bests[$firstKey]) && isset($bests[$secondKey])) {
            return array(
                'match_id' => $id,
                'type' => $type,
                'profit' => ((2 - (1/$bests[$firstKey]['bet'] + 1/$bests[$secondKey]['bet'])) * 100),
                'text' => $firstKey.' - '.$bests[$firstKey]['bk'].' => '.$bests[$firstKey]['bet']."; \n".
                    $secondKey.' - '.$bests[$secondKey]['bk'].' => '.$bests[$secondKey]['bet']
            );
        } else return null;
    }

    private function wrapIt3($id, $bests, $firstKey, $secondKey, $thirdKey, $type)
    {
        if (isset($bests[$firstKey]) && isset($bests[$secondKey]) && isset($bests[$thirdKey])) {
            return array(
                'match_id' => $id,
                'type' => $type,
                'profit' => ((2 - (1/$bests[$firstKey]['bet'] + 1/$bests[$secondKey]['bet'] + 1/$bests[$thirdKey]['bet'])) * 100),
                'text' => $firstKey.' - '.$bests[$firstKey]['bk'].' => '.$bests[$firstKey]['bet']."; \n".
                    $secondKey.' - '.$bests[$secondKey]['bk'].' => '.$bests[$secondKey]['bet']."; \n".
                    $thirdKey.' - '.$bests[$thirdKey]['bk'].' => '.$bests[$thirdKey]['bet']
            );
        } else return null;
    }
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
        //var_dump($a);
        $j = implode( '"', $a );
        //var_dump( $j );
        return $j;
    }

    private function singleSearch(&$finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');

        $switcher = DB::table('ParcerSwitcher')->first();
        if ($switcher && ($switcher->ip)) {
            curl_setopt($ch, CURLOPT_INTERFACE, $switcher->ip);
        }

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
            if (($odds) && (property_exists($odds, 'odds'))) {
                if ($odds->odds[0]->t === $eventArg) {
                    foreach($odds->odds[0]->r as $odd){
                        if (in_array($odd->i,$avaliable_bks_keys)) {
                            foreach ($odd->c as $oddc){
                                if ($oddc->b == 0) {
                                    if ($stringType) {
                                        $finalArray[$stringType.'('.$oddc->k.')'][$flipped[$odd->i]] = $oddc->v;
                                    } else {
                                        $finalArray[$oddc->k][$flipped[$odd->i]] = $oddc->v;
                                    }
                                }

                            }
                        }
                    }
                }
            }
        }
    }

    private function multiSearch(&$finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, &$subTypes) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://dev.bmbets.com/oddsdata');

        $switcher = DB::table('ParcerSwitcher')->first();
        if ($switcher && ($switcher->ip)) {
            curl_setopt($ch, CURLOPT_INTERFACE, $switcher->ip);
        }

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
            if (($odds) && (property_exists($odds, 'odds'))) {
                foreach ($odds->odds as $partOdd) {
                    if ($partOdd->t === $eventArg) {
                        foreach($partOdd->r as $odd){
                            if (in_array($odd->i,$avaliable_bks_keys)) {
                                foreach ($odd->c as $oddc){
                                    if ($oddc->b == 0) {
                                        $finalArray[$stringType.$partOdd->h.'('.$oddc->k.')'][$flipped[$odd->i]] = $oddc->v;
                                        $subTypes[] = $stringType.$partOdd->h;
                                    }
                                }
                            }
                        }
                    }

                }
            }
            $subTypes = array_unique($subTypes);
        }
    }
    public function testProfit()
    {
        $id = 1;
        $match = Match::find($id);
        $matchId = $match->link_id;
        $matchTypeId = $match->league->sport_type_id;

        $avaliable_bks = array(
            '1xbet',
            'bet365',
            'betfair',
            'marathon',
            'matchbook',
            'pinnaclesports',
            'sbobet',
            'william-hill'
        );
        $avaliable_bks_keys = array();
        $json = '{"1":{"id":1,"name":"Betfair","url":"betfair","isAff":true},
        "2":{"id":2,"name":"PinnacleSports","url":"pinnaclesports","isAff":true},
        "4":{"id":4,"name":"PariMatch","url":"parimatch","isAff":true},
        "5":{"id":5,"name":"Bet City","url":"bet-city","isAff":true},
        "6":{"id":6,"name":"Toto","url":"toto","isAff":true},
        "7":{"id":7,"name":"BWin","url":"bwin","isAff":true},
        "8":{"id":8,"name":"BetAtHome","url":"betathome","isAff":true},
        "9":{"id":9,"name":"Vivaro","url":"vivaro","isAff":true},
        "13":{"id":13,"name":"Betclic","url":"betclic","isAff":true},
        "14":{"id":14,"name":"William Hill","url":"william-hill","isAff":true},
        "16":{"id":16,"name":"Unibet","url":"unibet","isAff":true},
        "17":{"id":17,"name":"Marathon","url":"marathon","isAff":true},
        "18":{"id":18,"name":"Intertops","url":"intertops","isAff":true},
        "21":{"id":21,"name":"Betgun","url":"betgun","isAff":true},
        "22":{"id":22,"name":"Interwetten","url":"interwetten","isAff":true},
        "24":{"id":24,"name":"Noxwin","url":"noxwin","isAff":true},
        "25":{"id":25,"name":"Mybet","url":"mybet","isAff":true},
        "27":{"id":27,"name":"Betsafe","url":"betsafe","isAff":true},
        "29":{"id":29,"name":"5Dimes","url":"5dimes","isAff":true},
        "30":{"id":30,"name":"10Bet","url":"10bet","isAff":true},
        "31":{"id":31,"name":"Ladbrokes","url":"ladbrokes","isAff":true},
        "32":{"id":32,"name":"Betfred","url":"betfred","isAff":true},
        "35":{"id":35,"name":"Sportbet","url":"sportbet","isAff":true},
        "38":{"id":38,"name":"BetDSI","url":"betdsi","isAff":true},
        "39":{"id":39,"name":"LeonBets","url":"leonbets","isAff":true},
        "42":{"id":42,"name":"188Bet","url":"188bet","isAff":true},
        "43":{"id":43,"name":"bet365","url":"bet365","isAff":true},
        "44":{"id":44,"name":"888sport","url":"888sport","isAff":true},
        "46":{"id":46,"name":"Bet US","url":"bet-us","isAff":true},
        "47":{"id":47,"name":"Betdaq","url":"betdaq","isAff":true},
        "48":{"id":48,"name":"Oddmaker","url":"oddmaker","isAff":true},
        "50":{"id":50,"name":"BetRedKings","url":"betredkings","isAff":true},
        "51":{"id":51,"name":"Betsson","url":"betsson","isAff":true},
        "52":{"id":52,"name":"GoldBet","url":"goldbet","isAff":true},
        "53":{"id":53,"name":"The Greek","url":"the-greek","isAff":true},
        "54":{"id":54,"name":"DOXXbet","url":"doxxbet","isAff":true},
        "55":{"id":55,"name":"youwin","url":"youwin","isAff":true},
        "57":{"id":57,"name":"Jetbull","url":"jetbull","isAff":true},
        "60":{"id":60,"name":"BetOnline","url":"betonline","isAff":true},
        "63":{"id":63,"name":"Bet3000","url":"bet3000","isAff":true},
        "64":{"id":64,"name":"Tipico","url":"tipico","isAff":true},
        "65":{"id":65,"name":"BetVictor","url":"betvictor","isAff":true},
        "71":{"id":71,"name":"Fonbet","url":"fonbet","isAff":true},
        "73":{"id":73,"name":"Favbet","url":"favbet","isAff":true},
        "77":{"id":77,"name":"Dafabet","url":"dafabet","isAff":true},
        "80":{"id":80,"name":"Topbet","url":"topbet","isAff":true},
        "81":{"id":81,"name":"Titanbet","url":"titanbet","isAff":true},
        "82":{"id":82,"name":"1Bet","url":"1bet","isAff":true},
        "83":{"id":83,"name":"Fortuna.cz","url":"fortunacz","isAff":true},
        "85":{"id":85,"name":"M88","url":"m88","isAff":true},
        "86":{"id":86,"name":"Smarkets","url":"smarkets","isAff":true},
        "88":{"id":88,"name":"Sbobet","url":"sbobet","isAff":true},
        "91":{"id":91,"name":"Coral","url":"coral","isAff":true},
        "93":{"id":93,"name":"Matchbook","url":"matchbook","isAff":true},
        "95":{"id":95,"name":"Betrally","url":"betrally","isAff":true},
        "96":{"id":96,"name":"Winner","url":"winner","isAff":true},
        "97":{"id":97,"name":"Netbet","url":"netbet","isAff":true},
        "98":{"id":98,"name":"Smart Live","url":"smart-live","isAff":true},
        "100":{"id":100,"name":"SkyBet","url":"skybet","isAff":true},
        "101":{"id":101,"name":"RoyalSports","url":"royalsports","isAff":true},
        "103":{"id":103,"name":"18Bet","url":"18bet","isAff":true},
        "104":{"id":104,"name":"bet88sports","url":"bet88sports","isAff":true},
        "105":{"id":105,"name":"1xBet","url":"1xbet","isAff":true},
        "106":{"id":106,"name":"Betkurus","url":"betkurus","isAff":true},
        "107":{"id":107,"name":"Winlinebet","url":"winlinebet","isAff":true},
        "108":{"id":108,"name":"VitalBet","url":"vitalbet","isAff":true},
        "109":{"id":109,"name":"Betfair SP","url":"betfair-sp","isAff":true},
        "110":{"id":110,"name":"TonyBet","url":"tonybet","isAff":true},
        "111":{"id":111,"name":"Supermatch","url":"supermatch","isAff":true},
        "112":{"id":112,"name":"Bets10","url":"bets10","isAff":true},
        "113":{"id":113,"name":"SportingBet","url":"sportingbet","isAff":true},
        "114":{"id":114,"name":"138.com","url":"138com","isAff":true},
        "116":{"id":116,"name":"Roisbet","url":"roisbet","isAff":true}}';
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

        if ($matchTypeId == 1) {
            //==================================1x2========================================

            $eventArg = 3;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped);


            //==================================DC========================================

            $eventArg = 6;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped);

            //==================================Asian Handicap========================================

            $stringType = 'AH';
            $ahTypes = array();
            $eventArg = 1;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahTypes);

            //==================================Totals========================================

            $stringType = 'OU';
            $ouTypes = array();
            $eventArg = 4;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ouTypes);

            //==================================DNB========================================

            $stringType = 'DNB';
            $eventArg = 35;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            //======================================Both teams to score============================================

            $stringType = 'BTTS';
            $eventArg = 304;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            //==================================Highest scoring half==================================

            $stringType = 'HSH';
            $eventArg = 383;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            //================================Home team total goals================================

            $stringType = 'HTTG';
            $eventArg = 101;
            $httgTypes = array();
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $httgTypes);

            //================================Away team total goals==================================

            $stringType = 'ATTG';
            $eventArg = 102;
            $attgTypes = array();
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $attgTypes);

            //================================Home team score a goal================================

            $stringType = 'HTSG';
            $eventArg = 305;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            //================================Away team score a goal==================================

            $stringType = 'ATSG';
            $eventArg = 306;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            foreach ($finalArray as $key => $value) {
                arsort($value);
                $bests[$key] = array(
                    'bk' => key($value),
                    'bet' => current($value),
                );
            }

            $finalUserResponse[] = $this->wrapIt3($id, $bests, '1', 'X', '2', '1_X_2');
            $finalUserResponse[] = $this->wrapIt($id, $bests, '1X', '2', '1X_2');
            $finalUserResponse[] = $this->wrapIt($id, $bests, '1', 'X2', '1_X2');
            $finalUserResponse[] = $this->wrapIt($id, $bests, '12', 'X', '12_X');
            foreach ($ahTypes as $ahType) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $ahType.'(1)', $ahType.'(2)', $ahType);
            }
            foreach ($ouTypes as $ouType) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $ouType.'(Under)', $ouType.'(Over)', $ouType);
            }
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'DNB(1)', 'DNB(2)', 'DNB1-DNB2');
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'DNB(1)', 'AH0(2)', 'DNB1-AH2(0)');
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'AH0(1)', 'DNB(2)', 'AH1(0)-DNB2');
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'BTTS(Yes)', 'BTTS(No)', 'BTTS');
            $finalUserResponse[] = $this->wrapIt3($id, $bests, 'HSH(1st Half)', 'HSH(X)', 'HSH(2nd Half)', 'HSH');
            foreach ($httgTypes as $httgType) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $httgType . '(Under)', $httgType . '(Over)', $httgType);
            }
            foreach ($attgTypes as $attgType) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $attgType.'(Under)', $attgType.'(Over)', $attgType);
            }
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'HTSG(Yes)', 'HTSG(No)', 'HTSG');
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'ATSG(Yes)', 'ATSG(No)', 'ATSG');


        } elseif ($matchTypeId == 2) {

            $stringType = 'FullTime';
            $eventArg = 5;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            $stringType = '1stSet';
            $eventArg = 7;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            $stringType = '2ndSet';
            $eventArg = 8;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);

            $stringType = 'AH(Set)';
            $ahsTypes = array();
            $eventArg = 21;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahsTypes);

            $stringType = 'AH(Game)';
            $ahgTypes = array();
            $eventArg = 22;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahgTypes);

            $stringType = 'AH(1st Set)';
            $ahs1Types = array();
            $eventArg = 17;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahs1Types);

            $stringType = 'AH(2nd Set)';
            $ahs2Types = array();
            $eventArg = 18;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahs2Types);

            $stringType = 'AH(3rd Set)';
            $ahs3Types = array();
            $eventArg = 19;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahs3Types);

            $stringType = 'AH(4th Set)';
            $ahs4Types = array();
            $eventArg = 20;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ahs4Types);

            $stringType = 'OU(Total Sets)';
            $outsTypes = array();
            $eventArg = 23;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $outsTypes);

            $stringType = 'OU(Total Games)';
            $outgTypes = array();
            $eventArg = 26;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $outgTypes);

            $stringType = 'OU(1st Set Games)';
            $ou1sTypes = array();
            $eventArg = 24;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ou1sTypes);

            $stringType = 'OU(2nd Set Games)';
            $ou2sTypes = array();
            $eventArg = 25;
            $this->multiSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType, $ou2sTypes);

            $stringType = 'TB';
            $eventArg = 11;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);


            $stringType = 'TB(1st Set)';
            $eventArg = 99;
            $this->singleSearch($finalArray, $matchId, $eventArg, $avaliable_bks_keys, $flipped, $stringType);


            foreach ($finalArray as $key => $value) {
                arsort($value);
                $bests[$key] = array(
                    'bk' => key($value),
                    'bet' => current($value),
                );
            }

            $finalUserResponse[] = $this->wrapIt($id, $bests, 'FullTime(1)', 'FullTime(2)', 'FullTime');
            $finalUserResponse[] = $this->wrapIt($id, $bests, '1stSet(1)', '1stSet(2)', '1stSet');
            $finalUserResponse[] = $this->wrapIt($id, $bests, '2ndSet(1)', '2ndSet(2)', '2ndSet');
            foreach ($ahsTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($ahgTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($ahs1Types as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($ahs2Types as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($ahs3Types as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($ahs4Types as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(1)', $type.'(2)', $type);
            }
            foreach ($outsTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(Over)', $type.'(Under)', $type);
            }
            foreach ($outgTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(Over)', $type.'(Under)', $type);
            }
            foreach ($ou1sTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(Over)', $type.'(Under)', $type);
            }
            foreach ($ou2sTypes as $type) {
                $finalUserResponse[] = $this->wrapIt($id, $bests, $type.'(Over)', $type.'(Under)', $type);
            }
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'TB(Yes)', 'TB(No)', 'TB');
            $finalUserResponse[] = $this->wrapIt($id, $bests, 'TB(1st Set)(Yes)', 'TB(1st Set)(No)', 'TB(1st Set)');
        }
        //=======================================================================================

        $types = array();
        foreach($finalUserResponse as $profitData) {
            if ($profitData) {
                $types[] = $profitData['type'];
                if ($profit = Profit::where('type', $profitData['type'])
                    ->where('match_id', $profitData['match_id'])
                    ->first()
                ) {
                    if ($profitData['profit'] > 101) {
                        $profit->profit = $profitData['profit'];
                        $profit->text = $profitData['text'];
                        $profit->save();
                    } else {
                        $profit->delete();
                    }
                } else {
                    if ($profitData['profit'] > 101) {
                        Profit::create($profitData);
                    }
                }
                if ($profitData['profit'] >= 90) {
                    History::create(array(
                        'sport_type' => $match->league->sportType->name,
                        'league' => $match->league->title,
                        'match' => $match->title,
                        'type' => $profitData['type'],
                        'profit' => $profitData['profit'],
                        'text' => $profitData['text'],
                        'full_link' => $match->full_link,
                        'match_date' => $match->match_date,
                    ));
                }
            }
        }
        \DB::table('Profits')
            ->where('match_id', $id)
            ->whereNotIn('type', $types)
            ->delete()
        ;
    }

    public function testLeague()
    {
        foreach (SportType::all() as $type) {
            $url = 'http://www.bmbets.com/'.$type->url.'/';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            $switcher = DB::table('ParcerSwitcher')->first();
            if ($switcher && ($switcher->ip)) {
                curl_setopt($ch, CURLOPT_INTERFACE, $switcher->ip);
            }

            curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpcode === 200) || ($httpcode === 408)) {
                $dom = HtmlDomParser::str_get_html($output);
                if (is_object($dom)) {
                    $elements = $dom->find('.country-table .m-count');
                    $links = array();
                    foreach ($elements as $key => $elem) {
                        $count = preg_replace("/[^0-9]/", '', $elem->plaintext);
                        $link = $elem->parent()->find('a', 0);
                        $links[] = $link->href;
                        if ($league = League::where('link', $link->href)->first()) {
                            $league->count = $count;
                            $league->save();
                            continue;
                        }
                        $leagueData = array(
                            'title' => $link->plaintext,
                            'link' => $link->href,
                            'count' => $count,
                            'sport_type_id' => $type->id,
                        );
                        League::create($leagueData);
                    }
                    \DB::table('Leagues')
                        ->where('sport_type_id', $type->id)
                        ->whereNotIn('link', $links)
                        ->delete();
                }
            } else {
                dd($output);
            }
        }
    }

    public function testMatch()
    {
        $id = 26;
        $leagueLink = League::find($id)->link;
        $url = 'http://www.bmbets.com'.$leagueLink;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $switcher = DB::table('ParcerSwitcher')->first();
        if ($switcher && ($switcher->ip)) {
            curl_setopt($ch, CURLOPT_INTERFACE, $switcher->ip);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (($httpcode === 200) || ($httpcode === 408)) {
            $dom = HtmlDomParser::str_get_html($output);
            if (is_object($dom)) {
                $elements = $dom->find('.odds-table tr');
                $links = array();
                $currDate = '';
                foreach ($elements as $key => $row) {
                    if ($row->children[0]->tag == "th") {
                        $currDate = $row->children[0]->plaintext;
                    } else {
                        $elem = $row->find('a');
                        if ($elem && (isset($elem[0]))) {
                            $elem = $elem[0];
                            preg_match('/^.*(?<link_id>\d+)\/?$/isU', $elem->href, $matches);
                            $link_id = $matches['link_id'];
                            $links[] = $elem->href;
                            if ($match = Match::where('link', $elem->href)->first()) {
                                continue;
                            }
                            $matchDate = \DateTime::createFromFormat('l, F j, Y H:i', $currDate.' '.$row->children[0]->plaintext);
                            $matchData = array(
                                'league_id' => $id,
                                'title' => $elem->plaintext,
                                'link' => $elem->href,
                                'full_link' => 'http://www.bmbets.com'.$elem->href,
                                'link_id' => $link_id,
                                'match_date' => $matchDate
                            );
                            Match::create($matchData);
                        }
                    }
                }
                \DB::table('Matches')
                    ->where('league_id', $id)
                    ->whereNotIn('link', $links)
                    ->delete();
            }
        } else {
            dd($output);
        }

    }

    public function client()
    {
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

    }
}