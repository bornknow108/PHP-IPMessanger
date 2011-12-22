<?php
/**
 * IPMessanger Plugin クラス
 * 
 * ボットです
 * 
 * @since 2011.12
 * @version 0.1β
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * 
 * @author bonknow108 <info@bornknow.com>
 * @copyright Copyright (c) 2011 bornknow108.com
 */	
class IPMessanger_Plugin_Bot implements IPMessanger_Plugin_Abstract {
	/**
	 * とりあえずのボット
	 * 
	 * @param resource $receiver
	 * @param array $sender
	 * @param array $receiver
	 * @return boolean
	 */
	public function send($socket, $sender, $receiver) {
		$append = 'hogehoge';
		
		$send_packet = IPMessanger_Command::makeTransmitPacket($receiver[0], $receiver[1], $receiver[2], IPMessanger_Command::IPMSG_SENDMSG, $append);
		stream_socket_sendto($socket, $send_packet, 0, $sender[6]);
	}
}
