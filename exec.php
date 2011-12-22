<?php

$receiver = new IPMessanger_Receiver('ユーザー名', 'ホスト名', 'グループ名', 'ロボット軍団');
$receiver->setPrivateHosts(array('192.168.11.10'));
$receiver->start();

/**
 * オートローダー
 * 
 * @param string $class_name 
 */
function __autoload($class_name) {
	if (!class_exists($class_name)) {
		$path = dirname(__FILE__) . '/' . str_replace('_', '/', $class_name) . '.php';
		include $path;
	}
}
