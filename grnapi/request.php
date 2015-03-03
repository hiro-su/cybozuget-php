<?php
/*
 * Cybozu Garoon APIはSOAP1.2準拠ですが、
 * 使用したライブラリNuSOAPはSOAP1.1対応のため
 * NuSOAPライブラリに変更を加えています。
 *
 * PHP5.3.8 Apache2.2.21
 * NuSOAP 0.9.5(1.123) LGPLライセンス
 *
 * nusoapx.php 713,7245-7247 setGrnEnv()を設置
 * nusoapx.php 7665 Soap1.2対応(エラー回避)
 */

require_once('grnapi/nusoapx.php');

class Request{
	//request生成
	function doRequest($login_user, $login_pass, $apiName, $params, $apiLocation){

		//locale
		$locale = 'jp';
	
		/* Original Envelop elements for Cybozu Garoon API */
		$grn_env = '
			xmlns:SOAP-ENV="http://www.w3.org/2003/05/soap-envelope"
			xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"';
	
		/* Header params */
		$header = '
		    <Action SOAP-ENV:mustUnderstand="1" xmlns="http://schemas.xmlsoap.org/ws/2003/03/addressing">' . $apiName . '</Action>
		    <Security xmlns:wsu="http://schemas.xmlsoap.org/ws/2002/07/utility" SOAP-ENV:mustUnderstand="1" xmlns="http://schemas.xmlsoap.org/ws/2002/12/secext">
		        <UsernameToken wsu:Id="id">
		            <Username>' . $login_user . '</Username>
		            <Password>' . $login_pass . '</Password>
		        </UsernameToken>
		    </Security>
		    <Timestamp SOAP-ENV:mustUnderstand="1" Id="id" xmlns="http://schemas.xmlsoap.org/ws/2002/07/utility">
		        <Created>2037-08-12T14:45:00Z</Created>
		        <Expires>2037-08-12T14:45:00Z</Expires>
		    </Timestamp>
			<Locale>' . $locale . '</Locale>';
	
		$client = new nusoap_client($apiLocation);					//create soap client
		$client->decodeUTF8(false);									//UTF8デコードFalse
		$client->setGrnEnv($grn_env);								//ガルーン用の envelope要素をセット
		$client->call($apiName, $params, null, $apiName, $header);	//リクエスト生成

		// Display the request and response
		if($client->getError()){
			error_log(date("Y-m-d H:i:s", time()+8*60*60)." ".$client->getError()."\n", 3, error_log);
			echo getcwd() . "にerror_logを出力しました。";
		}else{
			return $client->responseData;

			// Request/Response確認用
			//echo '<h2>Request</h2>';
			//echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
			//echo '<h2>Response</h2>';
			//echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
		}
	}
}
?>
