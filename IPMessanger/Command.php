<?php
/**
 * IP Messenger コマンドクラス
 * 
 * 通信プロトコル仕様(ドラフト10版）に準拠したコマンドを管理してます。
 * 各コマンドの定数は、IPMessangerのソースから抜き出しております。
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
class IPMessanger_Command {
	const COMMAND_SEPARATOR			= ':';
	
	// コマンド (command番号(32bit)のうち、下位8bit)
	const IPMSG_NOOPERATION			= 0x00000000;

	const IPMSG_BR_ENTRY			= 0x00000001;
	const IPMSG_BR_EXIT				= 0x00000002;
	const IPMSG_ANSENTRY			= 0x00000003;
	const IPMSG_BR_ABSENCE			= 0x00000004;

	const IPMSG_BR_ISGETLIST		= 0x00000010;
	const IPMSG_OKGETLIST			= 0x00000011;
	const IPMSG_GETLIST				= 0x00000012;
	const IPMSG_ANSLIST				= 0x00000013;
	const IPMSG_BR_ISGETLIST2		= 0x00000018;

	const IPMSG_SENDMSG				= 0x00000020;
	const IPMSG_RECVMSG				= 0x00000021;
	const IPMSG_READMSG				= 0x00000030;
	const IPMSG_DELMSG				= 0x00000031;
	const IPMSG_ANSREADMSG			= 0x00000032;

	const IPMSG_GETINFO				= 0x00000040;
	const IPMSG_SENDINFO			= 0x00000041;

	const IPMSG_GETABSENCEINFO		= 0x00000050;
	const IPMSG_SENDABSENCEINFO		= 0x00000051;

	const IPMSG_GETFILEDATA			= 0x00000060;
	const IPMSG_RELEASEFILES		= 0x00000061;
	const IPMSG_GETDIRFILES			= 0x00000062;

	const IPMSG_GETPUBKEY			= 0x00000072;
	const IPMSG_ANSPUBKEY			= 0x00000073;

	// オプション (command番号(32bit)のうち、上位24bit)
	// option for all command
	const IPMSG_ABSENCEOPT			= 0x00000100;
	const IPMSG_SERVEROPT			= 0x00000200;
	const IPMSG_DIALUPOPT			= 0x00010000;
	const IPMSG_FILEATTACHOPT		= 0x00200000;
	const IPMSG_ENCRYPTOPT			= 0x00400000;
	const IPMSG_UTF8OPT				= 0x00800000;
	const IPMSG_CAPUTF8OPT			= 0x01000000;
	const IPMSG_ENCEXTMSGOPT		= 0x04000000;
	const IPMSG_CLIPBOARDOPT		= 0x08000000;

	// option for send command
	const IPMSG_SENDCHECKOPT		= 0x00000100;
	const IPMSG_SECRETOPT			= 0x00000200;
	const IPMSG_BROADCASTOPT		= 0x00000400;
	const IPMSG_MULTICASTOPT		= 0x00000800;
	const IPMSG_AUTORETOPT			= 0x00002000;
	const IPMSG_RETRYOPT			= 0x00004000;
	const IPMSG_PASSWORDOPT			= 0x00008000;
	const IPMSG_NOLOGOPT			= 0x00020000;
	const IPMSG_NOADDLISTOPT		= 0x00080000;
	const IPMSG_READCHECKOPT		= 0x00100000;
	const IPMSG_SECRETEXOPT			= 0x00100200;

	// obsolete option for send command
	const IPMSG_NOPOPUPOPTOBSOLT	= 0x00001000;
	const IPMSG_NEWMULTIOPTOBSOLT	= 0x00040000;
	
	/* encryption/capability flags for encrypt command */
	const IPMSG_RSA_512				= 0x00000001;
	const IPMSG_RSA_1024			= 0x00000002;
	const IPMSG_RSA_2048			= 0x00000004;
	const IPMSG_RC2_40				= 0x00001000;
	const IPMSG_BLOWFISH_128		= 0x00020000;
	const IPMSG_AES_256				= 0x00100000;
	const IPMSG_PACKETNO_IV			= 0x00800000;
	const IPMSG_ENCODE_BASE64		= 0x01000000;
	const IPMSG_SIGN_SHA1			= 0x20000000;

	/* compatibilty for Win beta version */
	const IPMSG_RC2_40OLD			= 0x00000010;	// for beta1-4 only
	const IPMSG_RC2_128OLD			= 0x00000040;	// for beta1-4 only
	const IPMSG_BLOWFISH_128OLD		= 0x00000400;	// for beta1-4 only
	const IPMSG_RC2_128OBSOLETE		= 0x00004000;
	const IPMSG_RC2_256OBSOLETE		= 0x00008000;
	const IPMSG_BLOWFISH_256OBSOL	= 0x00040000;
	const IPMSG_AES_128OBSOLETE		= 0x00080000;
	const IPMSG_SIGN_MD5OBSOLETE	= 0x10000000;
	const IPMSG_UNAMEEXTOPTOBSOLT	= 0x02000000;

	/* file types for fileattach command */
	const IPMSG_FILE_REGULAR		= 0x00000001;
	const IPMSG_FILE_DIR			= 0x00000002;
	const IPMSG_FILE_RETPARENT		= 0x00000003;	// return parent directory
	const IPMSG_FILE_SYMLINK		= 0x00000004;
	const IPMSG_FILE_CDEV			= 0x00000005;	// for UNIX
	const IPMSG_FILE_BDEV			= 0x00000006;	// for UNIX
	const IPMSG_FILE_FIFO			= 0x00000007;	// for UNIX
	const IPMSG_FILE_RESFORK		= 0x00000010;	// for Mac
	const IPMSG_FILE_CLIPBOARD		= 0x00000020;	// for Windows Clipboard

	/* file attribute options for fileattach command */
	const IPMSG_FILE_RONLYOPT		= 0x00000100;
	const IPMSG_FILE_HIDDENOPT		= 0x00001000;
	const IPMSG_FILE_EXHIDDENOPT	= 0x00002000;	// for MacOS X
	const IPMSG_FILE_ARCHIVEOPT		= 0x00004000;
	const IPMSG_FILE_SYSTEMOPT		= 0x00008000;

	/* extend attribute types for fileattach command */
	const IPMSG_FILE_UID			= 0x00000001;
	const IPMSG_FILE_USERNAME		= 0x00000002;	// uid by string
	const IPMSG_FILE_GID			= 0x00000003;
	const IPMSG_FILE_GROUPNAME		= 0x00000004;	// gid by string
	const IPMSG_FILE_CLIPBOARDPOS	= 0x00000008;	// 
	const IPMSG_FILE_PERM			= 0x00000010;	// for UNIX
	const IPMSG_FILE_MAJORNO		= 0x00000011;	// for UNIX devfile
	const IPMSG_FILE_MINORNO		= 0x00000012;	// for UNIX devfile
	const IPMSG_FILE_CTIME			= 0x00000013;	// for UNIX
	const IPMSG_FILE_MTIME			= 0x00000014;
	const IPMSG_FILE_ATIME			= 0x00000015;
	const IPMSG_FILE_CREATETIME		= 0x00000016;
	const IPMSG_FILE_CREATOR		= 0x00000020;	// for Mac
	const IPMSG_FILE_FILETYPE		= 0x00000021;	// for Mac
	const IPMSG_FILE_FINDERINFO		= 0x00000022;	// for Mac
	const IPMSG_FILE_ACL			= 0x00000030;
	const IPMSG_FILE_ALIASFNAME		= 0x00000040;	// alias fname
	
	/**
	 * コマンドのチェック
	 * 
	 * 対象のコマンドが存在する場合は、１を返します。
	 * 対象のコマンドが存在しない場合は、０を返します。
	 * 
	 * @param int $param
	 * @param int $command
	 * @return int 
	 */
	public static function hasCommand($param, $command) {
		return $param & 0x000000ff & $command;
	}
	
	/**
	 * オプションのチェック
	 * 
	 * 対象のオプションが存在する場合は、１を返します。
	 * 対象のオプションが存在しない場合は、０を返します。
	 * 
	 * @param int $param
	 * @param int $command
	 * @return int 
	 */
	public static function hasOption($param, $command) {
		return $param & 0xffffff00 & $command;
	}
	
	/**
	 * 他のクライアントに送信するコマンドをフォーマットバージョンに
	 * 合わせて生成する
	 * 
	 * コマンドフォーマット(Ver.1)にあわせてコマンドを作成する
	 * Ver(1) : Packet番号 : 自User名 : 自Host名 : Command番号 : 追加部
	 * 
	 * @param string $version
	 * @param string $user_name
	 * @param string $host_name
	 * @param string $command
	 * @param string $append
	 * @return string 
	 */
	public static function makeTransmitPacket($version, $user_name, $host_name, $command, $append) {
		// 追加部のテキストがない場合は、¥0 を当て込む
		$append_text = is_null($append) || $append == '' ? self::MESSAGE_ZERO : $append;

		$params = array(
			$version
		,	time() . mt_rand(1, 1000)
		,	$user_name
		,	$host_name
		,	$command
		,	$append_text
		);

		// コマンドの区切り文字「:」でパラメータを連結する
		return implode(self::COMMAND_SEPARATOR, $params);
	}
}