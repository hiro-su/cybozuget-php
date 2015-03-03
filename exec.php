<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>exec.php</title>
</head>
<body>

<?php
require_once('grnapi/request.php');
require_once('grnapi/params.php');
require_once('grnapi/create.php');

//ガルーンログイン
//$login_id   = base64_decode('aC1zdWdpbW90bw==');
//$login_pass = base64_decode('QXNkZjE1OTM1NyE=');

$login_id = 'sato';
$login_pass = 'sato';

//ガルーンのurl
//$url = 'https://cybozu.iiji.jp/cgi-bin/cbgrn/grn.cgi';
$url = 'http://onlinedemo.cybozu.co.jp/scripts/garoon3/grn.exe';

//apilocation は $url?WSDL を参照
$scheduleLocation	= $url . '/cbpapi/schedule/api';

//apiname
$apiName = 'ScheduleGetEventsByTarget';
$setApiParam = 'set' . $apiName;
$getApiParam = 'get' . $apiName;

//スケジュールを取得したいユーザを取得
$users = parse_ini_file("user.ini");

/* スケジュール取得範囲設定
 * 日本時間を指定する場合はグリニッジ標準時より9時間を足して下さい。
 * (例)日本時間2011年11月17日09:00:00 -> 2011-11-17T00:00:00Z */
$start = date("Y-n-j", strtotime("yesterday")) . "T15:00:00Z";		//今日の00:00から
$end = date("Y-n-j", strtotime("+2 week")) . "T14:59:00Z";	//2週間後の23:59まで

//インスタンス化
$apiParams = new Params();
$apiRequest = new Request();
$create = new Create();

$totalTime = 0;

//各ユーザーのスケジュール情報をxmlで取得
foreach ($users as $uname => $uid){
	//実行時間測定開始
	$st =microtime(TRUE);

	//APIのリクエストパラメータを設定。詳しくはdocを参照。
	$apiParams->$setApiParam($uid, $start, $end);
	$params = $apiParams->$getApiParam();
	//Requestを生成します。
	$result = $apiRequest->doRequest($login_id, $login_pass, $apiName, $params, $scheduleLocation);
	//XMLファイルを生成します。
	$create->createXML($result, $uname);

	//実行時間測定終了
	$et = microtime(TRUE);
	$execTime = $et - $st;
	echo '<p><b>' . $uname .'の実行時間</b><br />' . $execTime . '</p>';
	$totalTime += $execTime;
}
echo '<p style="color:#c06"><b>全ての実行時間</b><br />' . $totalTime . '</p>';
?>

<hr><p><small>Developed by h-sugimoto.&nbsp;(h-sugimoto@iij.ad.jp)</small></p>
</body>
</html>
