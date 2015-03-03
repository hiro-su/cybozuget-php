<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php
$urls = array(
	"sato" => "../xml/sato.xml"
);

foreach ($urls as $name => $xml){
	echo '<div id="xml"><h1>'.$name.'.xml</h1><div id="soapbody"><h2>schedule</h2>';
	$xml = new SimpleXMLElement(file_get_contents($xml));
	$event = $xml->event;

	for($i=0; $i<$event->count(); $i++){
		$eid = $event[$i]['eid'];
		$plan = $event[$i]['plan'];
		$detail = $event[$i]['detail'];
		$start = $event[$i]['start'];
		$end = $event[$i]['end'];

		echo '<div id="wrap"><h3><a href="http://onlinedemo.cybozu.co.jp/scripts/garoon3/grn.exe/schedule/view?event=' . $eid . '&bdate=' . date("Y-m-d", strtotime($start)) . '">event</a></h3>';
		echo '<div id="eid">eid : ' . $eid . "</div>";
		echo '<div id="plan">plan : ' . $plan . "</div>";
		echo '<div id="detail">detail : ' . $detail . "</div>";
		echo '<div id="start">start : ' . $start . "</div>";
		echo '<div id="end">end : ' . $end . "</div>";

		echo '<div id="name_wrap"><h3>user</h3>';
		foreach($event[$i]->user as $user){
			$order = $user['order'];
			$uid = $user['uid'];
			$name = $user['name'];
			$account = $user['account'];
			echo 'order : '. $order . '<br>';
			echo 'uid : '. $uid;
			if(isset($user['account'])){
				echo '<div id="name">user : <a href="http://onlinedemo.cybozu.co.jp/scripts/garoon3/grn.exe/schedule/user_view?uid='. $user['uid'] . '">' . $account . "</a></div>";
			}else{
				echo '<div id="name">user : <a href="http://onlinedemo.cybozu.co.jp/scripts/garoon3/grn.exe/schedule/user_view?uid='. $user['uid'] . '">' . $name . "</a></div>";
			}
		}
		echo "</div></div><br>";
	}
	echo "</div></div>";
}
?>

<hr><p style="text-align:center"><small>Developed by h-sugimoto.&nbsp;(h-sugimoto@iij.ad.jp)</small></p>
</body>
</html>
