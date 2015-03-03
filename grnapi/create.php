<?php
class Create{
	function createXML($xml, $uname){
		$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);		//:を削除

		$exParams = array(
			'/xmlns=\".*\"/',				//xmlns=***
			'/\<\/member\>|\<member\>/',	//memberタグ
			'/\<returns\>|\<\/returns\>/',	//returnタグ
			'/^\s+/m',						//先頭の空白
			'/\<scheduleScheduleGetEventsByTargetResponse\>|\<\/scheduleScheduleGetEventsByTargetResponse\>/',
		);

		//必要の無いタグを削除
		foreach($exParams as $exparam){
			$xml = preg_replace($exparam, '', $xml);
		}

		$xml = new SimpleXMLElement($xml);
		$schedule_event = $xml->soapBody->schedule_event;

		for($i=0; $i<$schedule_event->count(); $i++){
			//時刻をschedule_eventの属性にセット
			if(preg_match('/([0-9]{4})[-]([0-9]{1,2})[-]([0-9]{1,2})[T]/',$schedule_event[$i]->when->datetime['start'])){
				//通常のスケジュールの場合
				$jpStartTime = date("Y-m-d H:i",	strtotime($schedule_event[$i]->when->datetime['start']) + 8 * 60 * 60);
				$jpEndTime = date("Y-m-d H:i",	strtotime($schedule_event[$i]->when->datetime['end']) + 8 * 60 * 60);
				$schedule_event[$i]->addAttribute('start', $jpStartTime);
				$schedule_event[$i]->addAttribute('end', $jpEndTime);
			}else{
				//終日の場合
				$schedule_event[$i]->addAttribute('start', date("Y-m-d H:i", strtotime($schedule_event[$i]->when->date['start'])));
				$schedule_event[$i]->addAttribute('end', date("Y-m-d H:i", strtotime($schedule_event[$i]->when->date['end'])));
			}

			//user.iniファイルのuidと一致する人にaccountをセット
			$userFile = parse_ini_file("user.ini");
			foreach($userFile as $uf_uname => $uf_uid){
				$members = $schedule_event[$i]->members;
				foreach($members->user as $m_user){
					if($uf_uid == $m_user['id']){
						$m_user->addAttribute('account', $uf_uname);
					}
				}
			}
		}

		//新たにSimpleXMLElementオブジェクトをインスタンス化
		$newXML = new SimpleXMLElement('<schedule></schedule>');

		//newXMLに属性と要素を追加
		for($i=0; $i<$schedule_event->count(); $i++){
			//public_typeがprivateとqualifiedのものは取り除く
			if(!preg_match('/private|qualified/', $schedule_event[$i]['public_type'])){
				$members = $schedule_event[$i]->members;
				for($j=0; $j<$members->user->count(); $j++){
					$event = $newXML->addChild('event');
					//event_idを指定の形式で生成 5 + event_id + 00
					$eId = sprintf("%02d",$j);
					$event->addAttribute('eid', '5' . $schedule_event[$i]['id'] . $eId);
					$event->addAttribute('plan', $schedule_event[$i]['plan']);
					$event->addAttribute('detail', $schedule_event[$i]['detail']);
					$event->addAttribute('start', $schedule_event[$i]['start']);
					$event->addAttribute('end', $schedule_event[$i]['end']);
					$event->addChild('user');
					$event->user->addAttribute('order', $members->user[$j]['order']);
					$event->user->addAttribute('uid', $members->user[$j]['id']);
					$event->user->addAttribute('name', $members->user[$j]['name']);
					if(isset($members->user[$j]['account'])){
						$event->user->addAttribute('account', $members->user[$j]['account']);
					}
				}
			}
		}

		//SimpleXMLElementオブジェクトをDOMElementオブジェクトに変換して整形
		$dom = new DOMDocument('1.0', 'utf-8');
		//DOMNodeをコピー
		$node = $dom->importNode(dom_import_simplexml($newXML), true);
		//コピーしたDOMNodeを追加
		$dom->appendChild($node);
		//余白を取り除く
		$dom->preserveWhiteSpace = false;
		//整形
		$dom->formatOutput = true;
		//XMLツリーを文字列として出力
		$xml = $dom->saveXML();

		//デバッグ表示用
		//echo '<pre>' . htmlspecialchars($xml, ENT_QUOTES) . '</pre>';

		//ユーザ名.xmlを出力
		$fp = fopen("xml/" . $uname . ".xml", "w");

		if($fp){
			if(flock($fp,LOCK_EX)){
				if(!fwrite($fp, $xml)){
					error_log("Error : " . date("Y-m-d H:i:s", time()+8*60*60)." ".$uname.".xml write failed.\n", 3, "log/exec_log");
					echo getcwd() . "にerror_logを出力しました。";
				}
				flock($fp,LOCK_UN);
			}else{
				error_log("Error : " . date("Y-m-d H:i:s", time()+8*60*60)." ".$uname.".xml lock failed.\n", 3, "log/exec_log");
				echo getcwd() . "にerror_logを出力しました。";
			}
		}
		if(!fclose($fp)){
			error_log("Error : " . date("Y-m-d H:i:s", time()+8*60*60)." ".$uname.".xml close failed.\n", 3, "log/exec_log");
			echo getcwd() . "にerror_logを出力しました。";
		}else{
			error_log("Exec : " . date("Y-m-d H:i:s", time()+8*60*60)." ".$uname.".xml file closed.\n", 3, "log/exec_log");
		}
		//300KBでlog削除
		if(filesize('log/exec_log')>300000){
			unlink('log/exec_log');
		}
	}
}
?>
