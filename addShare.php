<?php
require_once "common.php";

!isset($voter) && $voter = 'adsystem';// voter from cookie

$lectors = array(
    '陈','王','石','盖','高', '张','刘'
);

if(!$isSuper) {echo 'bye'; die;}

$today=date('Y-m-d');

if(isset($_POST['method']) && trim($_POST['method']) == 'addShare' ) {
    $title = trim($_POST['title']);
    $lector= trim($_POST['lector']);
    $date  = date('Y-m-d', strtotime($_POST['date']));
    $desc  = trim($_POST['description']);
    addNewShare($date, $lector, $title, $desc);
}

require_once "views/addShare.html";


