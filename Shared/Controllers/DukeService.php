<?php

class DukeService{
	static private $ip = '0.0.0.0';
	static private self::$port = 3783;
	
	static private $instance = null;
	
	static public function Factory(){
		if(self::$instance == null){
			self::$instance = new DukeService();
		}
		return self::$instance;
	} 
	
	public function __construct(){
		$this->create_instance();
		$this->create_socket();
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
	}
	
	private function run(){
		do {
		    if (($msgsock = socket_accept($this->sock)) === false) {
		        echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($this->sock)) . "\n";
		        break;
		    }
		    /* Send instructions. */
		    $msg = "\nCOMMENT: Welcome to the PHP Test Server. \n" .
		        "COMMENT: To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
		    socket_write($msgsock, $msg, strlen($msg));
		
		    do {
		        if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
		            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
		            break 2;
		        }
		        if (!$buf = trim($buf)) {
		            continue;
		        }
		        if ($buf == 'quit') {
		            break;
		        }
		        if ($buf == 'shutdown') {
		            socket_close($msgsock);
		            break 2;
		        }
		        $talkback = "PHP: You said '$buf'.\n";
		        socket_write($msgsock, $talkback, strlen($talkback));
		        echo "$buf\n";
		    } while (true);
		    socket_close($msgsock);
		} while (true);
		
		socket_close($this->sock);
	}
}