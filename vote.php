<?php
require_once "common.php";

!isset($voter) && $voter = 'adsystem';// voter from cookie

if(isset($_POST['method']) && trim($_POST['method']) == 'vote' ) {
    $score = doubleval($_POST['score']);
    $lector= trim($_POST['lector']);
    $date  = trim($_POST['date']);
    updateVoteScore($date, $lector, $voter, $score);
}

$scores = getVoteScore();
krsort($scores); // 时间desc
$lists = $date2Lectors = array();
$today = date('Y-m-d');
foreach($scores as $date=> $value) {
    foreach($value as $lector => $score) {
        if($today <= $date && !in_array($voter, $score['voters'])) {
            if(!isset($date2Lectors[$date])) $date2Lectors[$date] = array();
            $date2Lectors[$date][] = $lector;
        }
        $list['date'] = $date;
        $list['title'] = $score['title'];
        $list['lector'] = $lector;
        $list['voters'] = count($score['voters']);
        if($list['voters'] >2) {
            sort($score['scores']);
            array_shift($score['scores']);
            array_pop($score['scores']);
            $list['voters'] = $list['voters'] - 2;
        }
        $list['AvgScores'] = 0;
        if($list['voters'] != 0) {
            $list['AvgScores'] = array_sum($score['scores']) / $list['voters'];
        }
        $list['AvgScores'] = round($list['AvgScores'], 2);
        // $list['voters'] = implode(', ', ($score['voters']));
        $list['voters'] = $list['voters'] >2 ? implode(', ', ($score['voters'])) : $list['voters'] . ' partners';
        $lists[] = $list;
    }
}

$date2LectorsJson = json_encode($date2Lectors);

require_once "views/vote.html";


