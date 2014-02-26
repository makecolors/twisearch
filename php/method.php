<?php
error_reporting(E_ALL & ~E_NOTICE); // NOTICEの警告文を消去する
require_once('database.php'); // データベースに接続する

/* textAnalyze関数:
 * テキスト解析のための関数
 * ツイートされた内容からGoogleMapsに表示するために必要な言葉を取り出す
 */

function textAnalyze2($text, $db){
	
	// つぶやきの末尾についている時間を取り除く
	$reg = '/^(.+?)\[\d\d\/\d\d\/\d\d \d\d:\d\d\]$/';
	preg_match($reg, $text, $tempShapedText);
	$shapedText = $tempShapedText[1];
	//var_dump($shapedText);
	$result['text'] = $shapedText;
	
			
	// テーブルStationMapから必要なものを取り出す
	$query = 'SELECT stationId, stationName, LineName, LineId '
			. 'FROM StationMap';
	$stmt = $db->prepare($query);
	$stmt->execute();
	$i = 0;

	// 取り出した結果を一行ずつ処理する
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$stationMap['stationId'][$i] = $row['stationId'];
		$stationMap['stationName'][$i] = $row['stationName']; 
		$stationMap['LineName'][$i] = $row['LineName'];
		$stationMap['LineId'][$i] = $row['LineId'];
		$i++;
	}
	$loop = $i;

	/* ここからが担当部分です */
	// 正規表現で駅名を取り出す
	$reg1 = '/^.+?、現在も?(.+?)～(.+?)駅間.*/';
	$reg2 = '/^.+?、?(.+?)～(.+?)駅間.*/';
	$reg3 = '/^(.+?)～(.+?)駅間.*/';
	$reg4 = '/^.+?、?(.+?)駅.*/';
	$reg5 = '/^(.+?)駅.*/';
	
	if(preg_match($reg1, $shapedText, $match)){
		$case = 1;
		//print_r($match);
	}else if(preg_match($reg2, $shapedText, $match)) {
		$case = 2;
		//print_r(match);
	}else if(preg_match($reg3, $shapedText, $match)){
		$case = 3;
		//print_r($match);
	}else if(preg_match($reg4, $shapedText, $match)){
		$case = 4;
		//print_r($match);
	}else if(preg_match($reg5, $shapedText, $match)){
		$case = 5;
		//print_r($match);
	}
	
	//var_dump($case);
	
	$det_preStation = $match[1];
	$det_nextStation = $match[2];
	
	//var_dump($det_preStation);
	//var_dump($det_nextStation);
	
	
	for($i = 0; $i < $loop; $i++){
		if(strcmp($stationMap['stationName'][$i],$det_preStation) == 0 ){
			$det_preStationId = $i;
			break;
		}
	}
	
	//echo $firstStation;
	if($det_nextStation != NULL){
		for($j = 0; $j < $loop; $j++){
			if(strcmp($stationMap['stationName'][$j],$det_nextStation) == 0 ){
				$det_nextStationId = $j;
				break;
			}
		}
		//echo $nextStation;
		//echo $det_preStation;
		//echo $det_nextStation;

		if($det_preStationId > $det_nextStationId){
			$box = $det_preStationId;
			$det_preStationId = $det_nextStationId;
			$det_nextStationId = $box;
		}
		$l=0;
		for($k = $det_preStationId; $k <= $det_nextStationId; $k++){
			$result['stationName'][$l] = $stationMap['stationName'][$k];
			$l++;
		}
	}else{
		$result['stationName'][0] = $stationMap['stationName'][$det_preStationId];
		//var_dump($result);
	}
	
	$splitWord = Morphological_analyze($shapedText);
	$result['summary'] = summary_statistics($splitWord);
	
	//var_dump($result['stationName']);
	/* ここまでが担当部分です */
	//echo $b;
	// "stationName","summary","stationId"を連想配列"$result"に格納
	//$result['stationName'] = $stationName; 
	//$result['summary'] = $summary;
	//$result['stationId'] = $stationId;
	
	return $result; // resultを返す
}
/* Morphological_analyze関数:
 * mecabのAPIを利用するため、外部コマンドを使用して
 * コマンドライン上で処理したものを各配列に代入する(形態素解析)
 */
function Morphological_analyze($text){
	$results = "";
	$i = 0;

	exec("echo $text | mecab", $results);
	//var_dump($results);

	foreach($results as $result){
		$temps = explode(",", $result);
		$temp = explode("\t", $temps[0]);
		$spiltWord[$i] = $temp[0];
		$i++;
	}
	array_pop($spiltWord); // 配列の最後にEOSという不必要な文字を取り除くため
	return $spiltWord;
}

function summary_statistics($splitWord){
	$arrayLength = count($splitWord); //配列の数を代入
	$searchWords = array("点検","人身","情報","動物支障","立入");
	$resultsummary = array("車両点検","人身事故","平常運転","動物支障","踏切内立入");
	
	//var_dump($searchWords);
	
	for($i = 0; $i < $arrayLength; $i++){
		for($j = 0; $j < count($searchWords); $j++){
			if(strcmp($splitWord[$i], $searchWords[$j]) == 0){
				$factor = $j;
				goto stop;
			}
		}
	}
stop:
	return $resultsummary[$factor];
}


//textAnalyze('07:40頃、相模大野～東林間駅間で発生した踏切内立入の影響で、一部列車に遅れが出ています。[13/06...', $db);
//$text = '相模大野～東林間駅間で発生した踏切内立入の影響で、一部列車に遅れが出ています。[13/06...';
//$reg = '/~(.+?)〜(.+?)駅間/';
//preg_match($reg,$text,$retArr);
//var_dump($retArr);
//textAnalyze2('07:40頃、鶴間～東林間駅間で発生した踏切内立入の影響で、一部列車に遅れが出ています。[13/06...', $db);
//textAnalyze2('宮前平駅で発生した人身事故の影響で、一部列車に遅れが出ていましたが、19:00現在、ほぼ平常通り運転...', $db)
//textAnalyze2('13:58頃、宮前平駅で発生した人身事故の影響で、現在も溝の口～鷺沼駅間の運転を見合わせています。なお、振替輸送を行っています。[13/05/20 14:26]', $db)
textAnalyze2('13:58頃、宮前平駅で発生した人身事故の影響で、現在も一部列車に遅れが出ています。[13/05/20 18:02]', $db);
//
//$splitWord1 = Morphological_analyze('13:58頃、宮前平駅で発生した人身事故の影響で、現在も一部列車に遅れが出ています。[13/05/20 18:02]');
//var_dump($splitWord1);
//$summary = summary_statistics($splitWord1);
//var_dump($summary);

?>
