<?php
/**
 * IPMessanger レシーバークラス
 * 
 * 通信プロトコル仕様(ドラフト10版）に準拠して、ソケット通信をします。
 * 
 * @link http://ipmsg.org/protocol.txt 通信プロトコル仕様
 * 
 * @since 2011.12
 * @version 0.1β
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * 
 * @author bonknow108 <info@bornknow.com>
 * @copyright Copyright (c) 2011 bornknow108.com
 */	
class IPMessanger_Receiver {	
	const VERSION				= '1';
	const PROGRAM_ENCODING		= 'UTF-8';
	const MESSANGER_ENCODING	= 'SJIS-win';
	const BOT_VERSION			= 'IPMessangerBot for PHP Version 0.1β';
	const MESSAGE_ZERO			= "\0";
	
	const COMMAND_VERSION		= 0;
	const COMMAND_PACKET_NUMBER	= 1;
	const COMMAND_USER			= 2;
	const COMMAND_HOST			= 3;
	const COMMAND_COMMAND		= 4;
	const COMMAND_APPEND		= 5;
	
	protected $host			= null;
	protected $port			= null;
	protected $user_name	= null;
	protected $host_name	= null;
	protected $handle_name	= null;
	protected $network_name	= null;
	protected $group_name	= null;
	protected $socket		= null;
	
	protected $is_private	= array();
	protected $accept_hosts	= array();
	
	protected $host_list	= array();
		
	/**
	 * コンストラクタ
	 * 
	 * @param string $user_name
	 * @param string $host_name
	 * @param string $network_name
	 * @param string $group_name 
	 * @param string $host
	 * @param string $port
	 * 
	 */
	public function __construct($user_name = 'username', $host_name = 'hostname', $network_name = '', $group_name = '', $host = '0.0.0.0', $port = '2425') {
		$this->user_name	= $user_name;
		$this->host_name	= $host_name;
		$this->network_name = $network_name;
		$this->group_name	= $group_name;
		
		$this->host			= $host;
		$this->port			= $port;
	}
	
	/**
	 * 受信処理を開始する
	 */
	public function start() {
		// ソケットを生成する(IPMessangerはUDPソケットを利用するので STREAM_SERVER_BIND を指定しています。)
		$this->socket = stream_socket_server("udp://{$this->host}:{$this->port}", $no, $message, STREAM_SERVER_BIND);
		if (!$this->socket) {
			// ソケットの生成に失敗した場合は、例外を発生させる
			throw new Exception("[{$no}]{$message}");
		}
		
		// パケットの受信処理
		do {
			// パケットを受信する
			$address_port = null;
			$packet = stream_socket_recvfrom($this->socket, 7.5 * 1024, 0, $address_port);
			list($ip_address, $port) = explode(':', $address_port);
			
			if (!$this->isAllowHost($ip_address)) {
				self::logging("Access deny {$ip_address}.");
			} else {
				// パケットを区切り文字で分割する
				$commands		= explode(IPMessanger_Command::COMMAND_SEPARATOR, $packet);
				$version		= $commands[self::COMMAND_VERSION];
				$packet_num		= $commands[self::COMMAND_PACKET_NUMBER];
				$user_name		= $commands[self::COMMAND_USER];
				$host_name		= $commands[self::COMMAND_HOST];
				$command		= $commands[self::COMMAND_COMMAND];
				$append			= $this->decodeMessage($commands[self::COMMAND_APPEND]);
				if (!array_key_exists($user_name . $host_name, $this->host_list)) {
					$this->host_list[$user_name . $host_name] = array();
				}

				// エントリーのメッセージを受信した場合
				if (IPMessanger_Command::hasCommand($command, IPMessanger_Command::IPMSG_BR_ENTRY)) {
					self::logging('Entry');
					
					// BR系パケット用の拡張エントリのチェック
					$matches = array();
					if (preg_match_all('/(?P<label>UN|HN|NN|GN):(?P<value>.*)(\n|$)/', $packet, $matches)) {
						$extension_name	= array();
						foreach ($matches['label'] as $index => $label) {
							$value = $matches['value'][$index];
							$extension_name[$label] = $value;
						}

						// ホスト一覧に追記する
						$this->host_list[$user_name . $host_name] = $extension_name;
					}

					// エントリーメッセージを受信したら、エントリーを送信したクライアントに返答をする
					$append		= $this->encodeMessage($this->user_name . self::MESSAGE_ZERO . $this->group_name . self::MESSAGE_ZERO . "\n") 
								. $this->getBrPacketExtendEntry();
					$send_packet= $this->getTransmitPacket(IPMessanger_Command::IPMSG_ANSENTRY, $append);					
					stream_socket_sendto($this->socket, $send_packet, 0, $address_port);	
					
				// バージョン確認
				} else if (IPMessanger_Command::hasCommand($command, IPMessanger_Command::IPMSG_GETINFO)) {
					self::logging('Info');
					
					$send_packet = $this->getTransmitPacket(IPMessanger_Command::IPMSG_SENDINFO, self::BOT_VERSION);
					stream_socket_sendto($this->socket, $send_packet, 0, $address_port);

				// メッセージの受信
				} else if (IPMessanger_Command::hasCommand($command, IPMessanger_Command::IPMSG_SENDMSG)) {
					self::logging('Send');
					
					// オプションの確認
					// 開封チェックが指定されていた場合
					if (IPMessanger_Command::hasOption($command, IPMessanger_Command::IPMSG_SENDCHECKOPT)) {
						self::logging('----Check');
						
						// 開封したことを通知する
						$send_packet = $this->getTransmitPacket(IPMessanger_Command::IPMSG_RECVMSG, $packet_num);
						stream_socket_sendto($this->socket, $send_packet, 0, $address_port);
					}
					
					// プラグインに合わせて処理をする
					$lines	= explode("\n", $append);
					$class	= 'IPMessanger_Plugin_Bot';
					if (count($lines) > 0) {
						$tag	= $lines[0];
						$class	= 'IPMessanger_Plugin_' . $tag;
						// 対象のクラスファイルが存在しない場合は、ボットクラスに呼び出す
						if (!file_exists(dirname(__FILE__) . '/Plugin/' . $tag . '.php')) {
							$class = 'IPMessanger_Plugin_Bot';
						}
					}
					$plugin = new $class;
					$plugin->send($this->socket
							, array($version, $packet_num, $user_name, $host_name, $command, $append, $address_port)
							, array(self::VERSION, $this->user_name, $this->host_name, $this->network_name, $this->group_name, $this->host, $this->port));
				}
			}
		} while ($packet !== false);
	}
	
	/**
	 * 一部のクライアントに対してのみ返答をする場合の対象ホストを設定する
	 * 
	 * @param array $hosts 
	 */
	public function setPrivateHosts($hosts) {
		$this->is_private	= true;
		$this->accept_hosts	= $hosts;
	}
	
	/**
	 * 許可されたホストかどうかを返す
	 * 
	 * @param string $ip_address
	 * @return boolean 
	 */
	protected function isAllowHost($ip_address) {
		$result = false;
		if (!$this->is_private) {
			$result = true;
		} else if ($this->is_private && in_array($ip_address, $this->accept_hosts)) {
			$result = true;
		}
		
		return $result;
	}
	
	
	/**
	 * BR系パケット用の拡張エントリを生成する
	 * 
	 * @return string 
	 */
	protected function getBrPacketExtendEntry() {
		return "UN:{$this->user_name}\nHN:{$this->host_name}\nNN:{$this->network_name}\nGN:{$this->group_name}\n";
	}
	
	/**
	 * 他のクライアントに送信するコマンドをフォーマットバージョンに
	 * 合わせて生成する
	 * 
	 * コマンドフォーマット(Ver.1)にあわせてコマンドを作成する
	 * Ver(1) : Packet番号 : 自User名 : 自Host名 : Command番号 : 追加部
	 * 
	 * @param string $command
	 * @param string $append 
	 * @return string
	 */
	protected function getTransmitPacket($command, $append) {
		return IPMessanger_Command::makeTransmitPacket(self::VERSION, $this->user_name, $this->host_name, $command, $append);
	}
	
	/**
	 * IPMessanger用にメッセージをエンコードする
	 * 
	 * @param string $message
	 * @return string 
	 */
	public static function encodeMessage($message) {
		return mb_convert_encoding($message, self::MESSANGER_ENCODING, self::PROGRAM_ENCODING);
	}
	
	/**
	 * IPMessanger用のメッセージをデコードする
	 * 
	 * @param string $message
	 * @return string 
	 */
	public static function decodeMessage($message) {
		return str_replace(self::MESSAGE_ZERO, '', mb_convert_encoding($message, self::PROGRAM_ENCODING, self::MESSANGER_ENCODING));
	}
	
	/**
	 * ログを出力する
	 * 
	 * @param string $message
	 */
	public static function logging($message) {
		echo date('Y-m-d H:i:s') . ':' . $message . "\n";
	}
}