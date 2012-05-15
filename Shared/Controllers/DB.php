<?php 
class DB extends PDO {
	
	static private $instance = null;
	
	static public function Factory(){
		if(self::$instance == null){
			self::$instance = new DukeService();
		}
		return self::$instance;
	}
	
    public function __construct(
    	$host = "localhost",
    	$port = 3306,
    	$dbname = "jukebox",
    	$username="root", 
    	$password="",
    	$platform = "mysql", 
    	$driver_options=array()) {
    	$dsn = "{$platform}:host={$host};dbname={$dbname}";	
    	
        parent::__construct($dsn,$username,$password, $driver_options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBStatement', array($this)));
    }
}
class DBStatement extends PDOStatement {
    public $dbh;
    protected function __construct($dbh) {
        $this->dbh = $dbh;
    }
}