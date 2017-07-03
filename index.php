<?php
require_once "common.php";

$date = date('Y-m-d');

$score = array(
    '2016-03-22' => array(
        'ge' => array(
            'voters' => array('shi', 'ge','gao', 'chen'),
            'scores' => array(99.8,58.6,66,88),
            'title'=> 'MySQL索引数据结构浅析'
        )
    ),
    '2016-06-22' => array(
        'shi' => array(
            'voters' => array('shi', 'ge','gao', 'chen'),
            'scores' => array(99.8, 78, 78,85),
            'title'=> 'jacascript 分享'
        )
    ),
    '2016-03-15' => array(
        'chen' => array(
            'voters' => array('shi', 'ge','gao', 'chen'),
            'scores' => array(65,85,98,36),
            'title'=> 'MySQL 入门级分享'
        )
    )
);

// file_put_contents('data/scores.json', json_encode($score));

require_once "views/index.html";
