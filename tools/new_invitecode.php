<?php
ini_set('display_errors', 1);

require('../config.php');
require(DIR_SYSTEM . 'library/db.php');
// Database 
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($argc < 2) {
	echo "parameter error, count = 0\n";
	return;
}

$count = (int)$argv[1];
if (isset($argv[2]))
	$usertype = $argv[2];

if ($count == null || $count <= 0) {
	echo "parameter error, count = $count\n";
	return;
}

function getRandomString($len, $chars=null)
{
	if (is_null($chars)){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	}  
	mt_srand(10000000*(double)microtime());
	for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
		$str .= $chars[mt_rand(0, $lc)];  
	}
	return $str;
}

function createCode($number, $type) {
	global $db;

	$db->begin();
	for($i = 0; $i < $number; ) {
		$code = getRandomString(6);
		$ret = $db->query("insert into oc_invitecode set code='$code', usertype=$type");
		if ($ret == false) continue;
		$i++;
	}
	$db->commit();
}

if (isset($usertype)) {
	createCode($count, $usertype);
}
else {
	createCode($count, 0); // usertype 0: customer 客户
	createCode($count, 1); // usertype 1: customer & user 配送商
	createCode($count, 2); // usertype 2: seller 销售
	createCode($count, 3); // usertype 3: user 分拣中心 总部
	createCode($count, 4); // usertype 4: user 供应商
}

echo "Done.\n";
?>