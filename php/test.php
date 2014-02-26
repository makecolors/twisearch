<?php
//error_reporting(E_ALL & ~E_NOTICE); // NOTICEの警告文を消去する
	
/*プロキシ設定*/
/*
require_once('HTTP/Request.php');
$http_request = new HTTP_Request();
$http_request->setProxy('scproxy.aoyama.ac.jp', 3128);
*/

require_once('codebird.php');
require_once('config.php');
require_once('method.php');
require_once('database.php');

Codebird::setConsumerKey(CONSUMEKEY, CONSUMESECRET);
$cb = Codebird::getInstance();
$cb->setToken(ACCESSTOKEN, ACCESSTOKENSECRET);

/*
printf("%d %s\n", $http_request->getResponseCode(),
		$http_request->getResponseReason());
var_dump($http_request->getResponseBody());
*/
/*
$param = array(
	'count' => '20',
	'include_rts' => true,
	'screen_name' => 'GloStaRosk',
	//'user_id' => 152082316
);

$tweets = (array) $cb->statuses_userTimeline($param);
foreach($tweets as $tweet){
	var_dump($tweet);
}
*/
/*
$i = 0;
//var_dump($tweets);
echo '<ul>';
foreach($tweets as $tweet){
	if($tweet->text == NULL)
		break;
	else{
		echo '<li>' . $tweet->text . '</li>';
		$twInfo['text'][$i] = $tweet->text;
		//$twInfo['created'][$i] = $tweet->created_at;
		$twInfo['created'][$i] = date('Y-m-d H:i:s', strtotime((string) $tweet->created_at));

		//$temp = textAnalyze($twInfo['text'][$i]);
		//$twInfo['summary'][$i] = $temp['summary'];
		//$twInfo['stationId'][$i] = $temp['stationId'];
		$i++;
	}
}
echo '</ul>';

$friendsInfo = (array) $cb->friends_ids(); // Twitterからフォローしている人の情報を取得

var_dump($twInfo['created']);
//var_dump($friendsInfo['ids']);
*/

/*
echo '<ol>';
foreach($friendsInfo['ids'] as $friendInfo){
	echo '<li>' . $friendInfo . '</li>';
}
echo '</ol>';
*/
/*
$query = 'SELECT created FROM DelayInfo ORDER BY created DESC LIMIT 1'; // createdで並び替えて一番上にあるデータを取り出す
$stmt = $db->prepare($query); // queryを準備して実行する
$stmt->execute();

$row = $stmt->fetch();
if($row == false){
	echo "型名はboolです。\n";
}
var_dump($row);
echo strtotime((string)$row['created']);
*/
/*
$input = "あいうえお";
$results = "";

exec("echo $input | mecab", $results);
//var_dump($results);

foreach($results as $result){
	$temps = explode(",", $result);
	//var_dump($temps[0]);
	$temp = explode("\t", $temps[0]);
	var_dump($temp[0]);
}
*/
$text = '07:40頃、相模大野～東林間駅間で発生した踏切内立入の影響で、一部列車に遅れが出ています。[13/06...';
$reg = '/^.+?、(.+?)～(.+?)駅間.*/';
if (preg_match($reg, $text, $match)) {
  print_r($match);
}