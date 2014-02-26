<?php
$db = new PDO('mysql:host=localhost;dbname=TwiSearch', 'root', '7rmwmhs6tr',
		array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')); // データベースを操作するためのハンドラを手に入れる

/*
$twInfo['created'] = "2013-04-05 14:33:26";
$twInfo['stationListID'] = "5";
$twInfo['text'] = "2013-04-05 14:33:26";
$twInfo['summary'] = "事故";
insertDatas2DelayInfo($twInfo, $db);
*/
/*
$twInfo['stationListID'] = 1;
$twInfo['stationNames'][0] = "渋谷";
$twInfo['stationNames'][1] = "赤坂";
$stationNames = $twInfo['stationNames'];
foreach($stationNames as $stationName){
	insertDatas2StationList($twInfo, $stationName, $db);
}
*/

function insertDatas2DelayInfo($twInfo, $db){
	// データベースにデータを挿入する
	$query = 'INSERT INTO DelayInfo(created, stationListID, text, summary) VALUES(?, ?, ?, ?)';
	$stmt = $db->prepare($query);
	// 準備したqueryを実行する
	$stmt->execute(array($twInfo['created'], $twInfo['stationListID'], $twInfo['text'], $twInfo['summary']));
}
function insertDatas2StationList($twInfo, $stationName, $db){
	$query = 'INSERT INTO stationList(stationListID, stationName) VALUES(?, ?)';
	// データベースにデータを挿入する
	$stmt = $db->prepare($query);
	// 準備したqueryを実行する
	$stmt->execute(array($twInfo['stationListID'], $stationName));
}
?>
