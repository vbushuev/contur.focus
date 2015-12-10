<?php
session_start();
date_default_timezone_set('Europe/Moscow');
/**
 * client_id приложения
 */
define('CLIENT_ID', 'local.566973a3ec7410.58002193');
/**
 * client_secret приложения
 */
define('CLIENT_SECRET', '802232f8073927f1b07b812c4b9a4b3f');
/**
 * относительный путь приложения на сервере
 */
define('PATH', '/index.php');
/**
 * полный адрес к приложения
 */
define('REDIRECT_URI', 'http://contur.bitrix24.bs'.PATH);
/**
 * scope приложения
 */
define('SCOPE', 'crm');

/**
 * протокол, по которому работаем. должен быть https
 */
define('PROTOCOL', "https");

// connection using proxy
define("BS_USE_PROXY",true);
define("BS_PROXYTYPE", CURLPROXY_HTTP);
define("BS_PROXY", "192.168.11.7");
define("BS_PROXYPORT", 8080);
define("BS_PROXYAUTH", CURLAUTH_NTLM);
define("BS_PROXYUSERPWD", "v.bushuev:Vampire04");

/**
 * Производит перенаправление пользователя на заданный адрес
 *
 * @param string $url адрес
 */
function redirect($url)
{
	Header("HTTP 302 Found");
	Header("Location: ".$url);
	die();
}

/**
 * Совершает запрос с заданными данными по заданному адресу. В ответ ожидается JSON
 *
 * @param string $method GET|POST
 * @param string $url адрес
 * @param array|null $data POST-данные
 *
 * @return array
 */
function query($method, $url, $data = null)
{
	$query_data = "";

	$curlOptions = array(
		CURLOPT_RETURNTRANSFER => true
	);

	if($method == "POST")
	{
		$curlOptions[CURLOPT_POST] = true;
		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
	}
	elseif(!empty($data))
	{
		$url .= strpos($url, "?") > 0 ? "&" : "?";
		$url .= http_build_query($data);
	}
	$curl = curl_init($url);
	curl_setopt_array($curl, $curlOptions);
	if(BS_USE_PROXY){
	  curl_setopt($curl,CURLOPT_PROXYTYPE, BS_PROXYTYPE);
	  curl_setopt($curl,CURLOPT_PROXY, BS_PROXY);
	  curl_setopt($curl,CURLOPT_PROXYPORT, BS_PROXYPORT);
	  curl_setopt($curl,CURLOPT_PROXYAUTH, BS_PROXYAUTH);
	  curl_setopt($curl,CURLOPT_PROXYUSERPWD, BS_PROXYUSERPWD);
	}
	$fp=fopen('../logs/contur-focus-curl-'.date("Y-m-d").'.log', 'wa');
	curl_setopt($curl,CURLOPT_VERBOSE, 1);
	curl_setopt($curl, CURLOPT_STDERR, $fp);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	$result = curl_exec($curl);
	return json_decode($result, 1);
}

/**
 * Вызов метода REST.
 *
 * @param string $domain портал
 * @param string $method вызываемый метод
 * @param array $params параметры вызова метода
 *
 * @return array
 */
function call($domain, $method, $params)
{
	return query("POST", PROTOCOL."://".$domain."/rest/".$method, $params);
}
