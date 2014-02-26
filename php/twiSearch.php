<?php
//error_reporting(E_ALL & ~E_NOTICE); // NOTICEの警告文を消去する

/*プロキシ設定*/
/*
require_once('HTTP/Request.php');
$http_request = new HTTP_Request();
$http_request->setProxy('scproxy.aoyama.ac.jp, 3128');
*/

require_once('codebird.php');
require_once('config.php');
require_once('method.php');
require_once('database.php');


Codebird::setConsumerKey(CONSUMEKEY, CONSUMESECRET);
$cb = Codebird::getInstance();
$cb->setToken(ACCESSTOKEN, ACCESSTOKENSECRET);

$friendsInfo = (array) $cb->friends_ids(); // Twitterからフォローしている人の情報を取得
$friendsInfoIDs = $friendsInfo['ids']; // Twitterからフォローしている人のuser_idをすべて代入

$query2 = 'SELECT id FROM DelayInfo ORDER BY id DESC LIMIT 1';
$stmt2 = $db->prepare($query2); // queryを準備して実行する
$stmt2->execute();
$row2 = $stmt2->fetch();
$id = $row2['id'];
//var_dump($id);

// フォローしている人全てに対して繰り返す
foreach($friendsInfoIDs as $friendInfoID){
	// このデータベースはユーザーによるアクセスによって不定期に更新される(デメリット)
	$param = array(
		'count' => '10', // 最新の情報とそうでない情報の境目をとれる
		'user_id' => $friendInfoID // フォローしているうちの一人のIDを取り出す
	);
	$tweets = (array) $cb->statuses_userTimeline($param); // パラメータによって指定されたツイートすべてを取り出す


	$query = 'SELECT created FROM DelayInfo ORDER BY created DESC LIMIT 1'; // createdで並び替えて一番上にあるデータを取り出す
	$stmt = $db->prepare($query); // queryを準備して実行する
	$stmt->execute();
	
	$row = $stmt->fetch();
	
	$twInfo = null;
	// それぞれのツイートに対して繰り返す
	foreach($tweets as $tweet){
		if($tweet->text != NULL && strtotime($tweet->created_at) > strtotime((string)$row['created'])){ // 前回の更新日時より新しかったら
			//id重複しないように予めインクリメント
			$id++;
			// それぞれの情報を更新
			//$twInfo['text'] = $tweet->text;
			//$twInfo['accountName'] = $tweet->name;
			
			$twInfo['created'] = date('Y-m-d H:i:s', strtotime((string)$tweet->created_at));
			$temp = textAnalyze2($tweet->text, $db);
			$twInfo['text'] = $temp['text'];
			$twInfo['summary'] = $temp['summary'];
			$twInfo['stationId'] = $twInfo['stationListID'] = $id;
			$twInfo['stationNames'] = $temp['stationName'];
			
			// デバッグ用
			//$twInfo['stationNames'][0] = "渋谷";
			//$twInfo['stationNames'][1] = "赤坂";
			
			// テーブルDelayInfoに書き込む
			insertDatas2DelayInfo($twInfo, $db);
			// テーブルstationListに書き込む
			//var_dump($twInfo['stationNames']);
			if($twInfo['stationNames'] != NULL){
				foreach($twInfo['stationNames'] as $stationName){
					//echo "print<br>";
					//var_dump($twInfo);
					//var_dump($stationName);
					if($stationName != NULL)
						insertDatas2StationList($twInfo, $stationName, $db);
				}
			}
		}
		else{
			break;
		}
	}
}

		
?>
