<?php

class mailsniffer {

	private $___conn = NULL;

	private $mailbox = NULL;
	private $user = NULL;
	private $password = NULL;

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
				$port = isset($opts['port']) ? $opts['port'] : ('' != $flags['encryption'] ? 993 : 143);
			break;

			case 'pop3':
				$port = isset($opts['port']) ? $opts['port'] : ('' != $flags['encryption'] ? 995 : 110);
			break;
		}

		$this->mailbox = '{'.gethostbyname($opts['server']).':'.$port.'/'.implode('/', $flags).'}';
		$this->user = $opts['user'];
		$this->password = $opts['password'];

	}

	/**
	 * Class destructor.
	 *
	 * Closes the current connection (if any), and nullifies all attributes.
	 *
	 */
	public function __destruct(){

		if (NULL != $this->___conn) $this->close();
		$this->password = $this->user = $this->mailbox = NULL;

	}

	/**
	 * Opens the connection to the mail server and stores the connection reference
	 * in the ___conn attribute.
	 *
	 */
	public function open(){
		if (!$this->___conn) $this->___conn = imap_open($this->mailbox, $this->user, $this->password);
	}

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
		if ($this->___conn) $mailboxes = imap_getmailboxes($this->___conn, $this->mailbox, $pattern);
		return $mailboxes;

	}

	public function sniff($mailbox){
	}

	public function sniff_all(){
	}

}
