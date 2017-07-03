<?php 
require_once 'libraries/phpcas/CAS-1.3.1/CAS.php';
phpCAS::client(CAS_VERSION_2_0,'sso.dangdang.com',443,'authentication');
phpCAS::setNoCasServerValidation();
phpCAS::handleLogoutRequests();
phpCAS::forceAuthentication();

$voter = phpCAS::getUser();

$supers = array('cheng', 'zhang', 'ge');

$isSuper = false;
if(in_array($voter, $supers)) {
    $isSuper = true;
}

define('SCORE_FILE', 'data/scores.json');

function getVoteScore($date='', $user='') {
    $jsonstr = file_get_contents(SCORE_FILE);
    $scores  = json_decode($jsonstr,true);
    if($scores && $date != '') {
        $score = isset($scores[$date]) ? $scores[$date] : false;
        if($score && $user !='') {
            $score = isset($score[$user])? $score[$user] : false;
        }
        return $score;
    }
    return $scores;
}

// 更新某一人的评分(哪天谁给谁投票投多少分)
function updateVoteScore($date='', $user='', $voter='', $score='') {
    $fp = fopen(SCORE_FILE , 'a+');    
    if(flock($fp , LOCK_EX)){
        $scores = getVoteScore($date, $user);
        if($scores) {
            if(!in_array($voter, $scores['voters'])) {
                $scores['voters'][] = $voter;
                $scores['scores'][] = $score;
                shuffle($scores['voters']); // 将数组打乱,谁知道哪一票是谁投的
                if(count($scores['voters']) == count($scores['scores'])) {
                    $allScores = getVoteScore();
                    $allScores[$date][$user] = $scores;
                    file_put_contents(SCORE_FILE, json_encode($allScores));
                }
            }
        }
        flock($fp , LOCK_UN);
    }    
    fclose($fp);
}

function addNewShare($date, $lector, $title, $description='') {
    $fp = fopen(SCORE_FILE , 'a+');    
    if(flock($fp , LOCK_EX)){
        $allScores = getVoteScore();
        // if($allScores) {
            $dateShares = array();
            if(isset($allScores[$date])) $dateShares = $allScores[$date];
            if(!isset($dateShares[$lector])) {
                $dateShares[$lector] = array(
                    'voters' => array(),
                    'scores' => array(),
                    'title'  => $title,
                    'description'=> $description
                );
                $allScores[$date] = $dateShares;
                file_put_contents(SCORE_FILE, json_encode($allScores));
            }
        // }
        flock($fp , LOCK_UN);
    }
    fclose($fp);
}

