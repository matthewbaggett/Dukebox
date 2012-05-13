<?php

class DukeService{
	static private $address = '0.0.0.0';
	static private $port = 3783;
	static public $config;
	
	private $msgsock = null;
	
	static private $instance = null;
	
	static public function Factory(){
		if(self::$instance == null){
			self::$instance = new DukeService();
		}
		return self::$instance;
	}
	
		
	public function set_config($config){
		self::$config = $config;
	}
	public function __construct(){
		
	}
	public function initialise(){
		$this->create_instance();
		$this->create_socket();
		AudioPlayer::Factory()->index();
	}
	
	private function create_instance(){
	 	error_reporting(E_ALL);

		/* Allow the script to hang around waiting for connections. */
		set_time_limit(0);
		
		/* Turn on implicit output flushing so we see what we're getting
		 * as it comes in. */
		ob_implicit_flush();
	}
	
	private function create_socket(){
		if (($this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
		}
		
		if (socket_bind($this->sock, self::$address, self::$port) === false) {
		    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($this->sock)) . "\n";
		}
		
		if (socket_listen($this->sock, 5) === false) {
		    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->sock)) . "\n";
		}
		
		echo "Created Dukebox service on ".self::$address.":".self::$port."\n\n";
	}
	protected function welcome(){
		$msg = "\nCOMMENT: Welcome to the Dukebox Server. \n" .
		        "COMMENT: To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
		return $this->write($msg);
	}
	protected function write($msg){
		$msg = trim($msg)."\n";
		return socket_write($this->msgsock, $msg, strlen($msg));	
	}
	protected function parse($buf){
		$particles = explode(":",$buf,2);
		$payload = $particles[1];
		$commandstring = $particles[0];
		$commands = explode(" ", $commandstring);
		switch($commands[0]){
			case 'PLAY':
				AudioPlayer::Factory()->play();
				break;
			case 'STOP':
				AudioPlayer::Factory()->stop();
				break;
			case 'PAUSE':
				AudioPlayer::Factory()->stop();
				break;
		}
	}
	public function run(){
		do {
		    if (($this->msgsock = socket_accept($this->sock)) === false) {
		        echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($this->sock)) . "\n";
		        break;
		    }
		    /* Send instructions. */
		    $this->welcome();
		    
		
		    do {
		        if (false === ($buf = socket_read($this->msgsock, 2048, PHP_NORMAL_READ))) {
		            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($this->msgsock)) . "\n";
		            break 2;
		        }
		        if (!$buf = trim($buf)) {
		            continue;
		        }
		        if ($buf == 'quit') {
		            break;
		        }
		        if ($buf == 'shutdown') {
		            socket_close($this->msgsock);
		            break 2;
		        }
		        $this->parse($buf);
		        
		        echo "$buf\n";
		    } while (true);
		    socket_close($this->msgsock);
		} while (true);
		
		socket_close($this->sock);
	}
}