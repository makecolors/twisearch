<?php
require_once('twiSearch.php'); // main処理を呼び出す
require_once('database.php'); // データベース接続関数を取得

// データベースから表示するために必要なデータを取り出す
$query = 'SELECT DelayInfo.text, DelayInfo.created, DelayInfo.summary, DelayInfo.stationListID, 
stationList.stationName, StationMap.LineName, 
StationMap.Latitude, StationMap.Longitude 
FROM DelayInfo, stationList, StationMap 
WHERE DelayInfo.stationListID = stationList.stationListID 
AND stationList.stationName = StationMap.stationName 
ORDER BY created DESC 
LIMIT 200;';
$stmt = $db->prepare($query);
$stmt->execute();

// 一行一行格納する
$i = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$viewInfos[$i] = $row;
	$i++;
}

//var_dump($viewInfos);

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset=UTF-8 />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link rel="stylesheet" type="text/css" href="../css/twiSearch.css"/>
	<link rel="stylesheet" type="text/css" href="../css/reset.css"/>
	
	<script type="text/javascript"
		src="http://maps.google.com/maps/api/js?key=AIzaSyBXXmdEdnwX2eP9Bzg43SzOdQoDmOOU6EA&sensor=false">
	</script>
	
	<!--ここから 今後拡張予定だったもの-->
	<script type="text/javascript" src="../js/addressmaps.js"></script>
	<script type="text/javascript">
		google.load("jquery", "1.5.0");
	</script>
	<script type="text/javascript">
      $(document).ready(function() {
        $("#button").click(function() {
	      var address = $("#address").val(); // input要素の値を取得
          drawMap(address); // 地図を生成する
        });
      });
    </script>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<!--ここまで 今後拡張予定だったもの-->

	<script type="text/javascript">
	// GoogleMaps初期化関数
	function initialize(){	
		var myMap = new google.maps.Map(document.getElementById("map_canvas"), {
			zoom: 14,// ズームレベル
			center: new google.maps.LatLng(35.701306, 139.700044), // 中心点緯度経度
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		
	<?php
	// ここからマーカーの設定
	$count =0;
	foreach($viewInfos as $viewInfo){
	echo'
	var myMarker'.$count.' = new google.maps.Marker({
		position: new google.maps.LatLng('.$viewInfo['Latitude'].','.$viewInfo['Longitude'].'),// マーカーを置く緯度経度
		map: myMap
	});
	var myInfoWindow'.$count.' = new google.maps.InfoWindow({
		content:
			"<div id=\'infowindowclass\'>" +  
			"<h2>'.$viewInfo['LineName'].$viewInfo['stationName'].'駅</h2>" +  
			"<p>'.$viewInfo['created']."  <span class='accent'>".$viewInfo['summary'].'</span><br>" +  
			"'.$viewInfo['text'].'</p>" +  
			"</div>"
	});

	myInfoWindow'.$count.'.open(myMap, myMarker'.$count.');// 吹き出しを開く
	// 吹き出しが閉じられたら、マーカークリックで再び開くようにしておく
	google.maps.event.addListener(myInfoWindow'.$count.', "closeclick", function() {
		google.maps.event.addListenerOnce(myMarker'.$count.', "click", function(event) {
			myInfoWindow'.$count.'.open(myMap, myMarker'.$count.');
		});
	});
	';
	$count++;
	}
	// ここまでマーカーの設定
	?>


	}
	</script>
</head>
<body onload="initialize();">
	<header>
		<div>
			<h1>TwiSearch: 遅延情報検索</h1>
			<p>twitter上には各路線においての公式アカウントが存在している。<br/>
				その公式アカウントを利用して、路線遅延情報を取得する。<br/>
				アカウントのつぶやきはある決まった規則にしたがって情報を発信している。<br/>
				そのつぶやきからテキストとして情報を取得し、分析したものを表示する。
			</p>
			<!--<p>
				<input id="address" type="text" value="東京都世田谷区新町3丁目" size="50" />
				<button id="button">地図を作る</button>
			</p>-->
		</div>
	</header>
	<div class="float" id="sidemenu">
		<?php
		// 画面左側に表示される情報
			$loop = 0;
			foreach($viewInfos as $viewInfo){
				echo '<div class="sidebox">
					<h3>時刻:</h3>'.$viewInfo['created'].'<br>
					<h3>線名:</h3>'.$viewInfo['LineName'].'<br>
					<h3>駅名:</h3>'.$viewInfo['stationName'].'<br>
					<h3>内容:</h3>'.$viewInfo['text'].'</div>';
				$loop++;
				if($loop > 5)
					break;
			}
		?>
	</div>
	<!--GoogleMapsの表示-->
	<div id="map_canvas"></div>
	<footer>
		<div>
			<p></p>
		</div>
	</footer>
</body>
</html>