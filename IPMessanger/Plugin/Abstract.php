<?php

/**
 * IPMessanger Plugin インターフェース
 * 
 * IPMessengerで送信されてきたメッセージに対する自動応答の処理を
 * プラグイン化しています。
 * 
 * @since 2011.12
 * @version 0.1β
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * 
 * @author bonknow108 <info@bornknow.com>
 * @copyright Copyright (c) 2011 bornknow108.com
 */	
interface IPMessanger_Plugin_Abstract {

	/**
	 * 何かを返す
	 * 
	 * @param resource $receiver
	 * @param array $sender
	 * @param array $receiver
	 * @return boolean
	 */
	public function send($socket, $sender, $receiver);
}
