<?php

class mailsniffer {

	private $___conn = NULL;

	private $main_mailbox = NULL;
	private $user = NULL;
	private $password = NULL;

	const IMAP_DEFAULT_PORT = 143;
	const POP3_DEFAULT_PORT = 110;
	const IMAP_SSL_DEFAULT_PORT = 993;
	const POP3_SSL_DEFAULT_PORT = 995;

	/**
	 * Class constructor
	 *
	 * @param $opts Indexed Array with the params for the connection to the mail server.
	 * Supported keys/params are:
	 * 	- server: string server IP or name. MANDATORY.
	 * 	- port: integer port to connect. If no one is given, it's assigned the default
	 * 	value for the protocol and encryption method used.
	 * 	- protocol: string mail server protocol. Supported values: 'imap', 'pop3'; other values
	 * 	defaults to 'imap'. MANDATORY.
	 * 	- user: string username for login in the mail server. MANDATORY.
	 * 	- password: string password for the used user. MANDATORY.
	 * 	- encryption: mixed FALSE for use no encryption method in the connection,
	 * 	string with the encryption method to use. Supported values: 'ssl', 'tls', 'notls',
	 * 	with the same meaning that the ones used in imap_open. Any other values are not
	 * 	supported, and defaults to FALSE.
	 * 	- validate_cert: if encryption method was given, boolean TRUE to validate certificates
	 * 	from TLS/SSL server, FALSE otherwise. If not given, it defaults to FALSE.
	 *
	 */
	public function __construct($opts){

		if (!(isset($opts['server']) && isset($opts['protocol']) && isset($opts['user']) && isset($opts['password']))) trigger_error('Mandatory param missing', E_ERROR);

		$flags['protocol'] = strtolower($opts['protocol']);
		if ('imap' != $opts['protocol'] && 'pop3' != $opts['protocol']) $flags['protocol'] = 'imap';

		if (isset($opts['encryption']) && FALSE != $opts['encryption']){
			switch ($opts['encryption']){
				case 'ssl':
				case 'tls':
				case 'notls':
					$flags['encryption'] = $opts['encryption'];
				break;
			}
		}

		$flags['validate_cert'] = (isset($opts['validate_cert']) && $opts['validate_cert']) ? 'validate-cert' : 'novalidate-cert';

		switch ($flags['protocol']){
			case 'imap':
				$port = isset($opts['port']) ? $opts['port'] : ('' != $flags['encryption'] ? self::IMAP_SSL_DEFAULT_PORT : self::IMAP_DEFAULT_PORT);
			break;

			case 'pop3':
				$port = isset($opts['port']) ? $opts['port'] : ('' != $flags['encryption'] ? self::POP3_SSL_DEFAULT_PORT : self::POP3_DEFAULT_PORT);
			break;
		}

		$this->main_mailbox = '{'.gethostbyname($opts['server']).':'.$port.'/'.implode('/', $flags).'}';
		$this->user = $opts['user'];
		$this->password = $opts['password'];

	}

	/**
	 * Class cloner.
	 *
	 */
	public function __clone(){ $this->___conn = imap_open($this->main_mailbox, $this->user, $this->password, OP_READONLY); }

	/**
	 * Class destructor.
	 *
	 * Closes the current connection (if any), and nullifies all attributes.
	 *
	 */
	public function __destruct(){

		if (NULL != $this->___conn) $this->close();
		$this->password = $this->user = $this->main_mailbox = NULL;

	}

	/**
	 * Opens the connection to the mail server and stores the connection reference
	 * in the ___conn attribute.
	 *
	 */
	public function open(){ if (!$this->___conn) $this->___conn = imap_open($this->main_mailbox, $this->user, $this->password, OP_READONLY); }

	/**
	 * Closes the connection to the mail server in the ___conn attribute
	 * and nullifies said attribute.
	 *
	 */
	public function close(){

		$status = TRUE;
		if ($this->___conn && $status = imap_close($this->___conn)) $this->___conn = NULL;
		return $status;

	}

	public function getmailboxes($pattern='*'){

		$mailboxes = array();
		if ($this->___conn) $mailboxes = imap_getmailboxes($this->___conn, $this->main_mailbox, $pattern);
		return $mailboxes;

	}

	public function sniff($mailbox){

		if (imap_reopen($this->___conn, $mailbox, OP_READONLY)){
			$mailbox_info = imap_mailboxmsginfo($this->___conn);
			var_dump($mailbox_info);
		}

		imap_reopen($this->___conn, $this->main_mailbox, OP_READONLY);

	}

	public function sniff_all(){

		$messages = array();

		$mailboxes = $this->getmailboxes();
		foreach ($mailboxes as $mailbox){
			$messages[(string)$mailbox->name] = TRUE;
		}

	}

}
