<?php

date_default_timezone_set("Etc/GMT-8");

// ��ע��������Ƿ�ͨfopen����
function  log_result($word) {
	$fp = fopen(LOG_FILE,"a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,strftime("%Y%m%d%H%M%S",time()).":".$word.PHP_EOL);
    flock($fp, LOCK_UN);
    fclose($fp);
}

?>