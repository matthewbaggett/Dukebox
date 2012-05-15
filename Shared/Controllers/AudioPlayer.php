<?php
class AudioPlayer {

	static private $instance = null;
	
	static public function Factory(){
		if(self::$instance == null){
			self::$instance = new AudioPlayer();
		}
		return self::$instance;
	}
	
	public function index(){
		$this->index_check_for_deletions();
		$this->index_check_for_additions();
	}
	
	private function index_check_for_additions(){
		var_dump(DukeService::$config);
		$media_location = DukeService::$config['media_location'];
		echo "Checking for Additions...\n";
		echo " > Location: {$media_location}\n";
		$list_of_files = $this->scan_directory_recursively($media_location);
		$list_of_files = $this->filter_by_extention($list_of_files);
		echo " > Found " . count($list_of_files) . " files\n";
		foreach($list_of_files as $file){
			$cmp3 = new CMP3File($file->path);
			$title = $cmp3->title;
			$artist = $cmp3->artist;
			$album = $cmp3->album;
			$year = $cmp3->year;
			$playtime = 0;
			$filename = $file->path;
			$mimetype = $file->mime;
			DB::Factory()->query("
				INSERT INTO Tracks (
					Title, 
					Artist, 
					Playtime, 
					Filename, 
					Mimetype, 
					Album, 
					Year
				) VALUES (
					'{$title}',
					'{$artist}',
					$playtime,
					'{$filename}',
					'{$mimetype}',
					'{$album}',
					'{$year}'
				) 
			");
		}
		//var_dump($list_of_files);exit;
	}
	
	private function index_check_for_deletions(){}

	private function filter_by_extention($list_of_files){
		foreach($list_of_files as &$file){
			$path = $file;
			$mime = mime_content_type($file);
			
			switch($mime){
				case 'audio/mpeg':
				case 'audio/x-flac':
					echo "found " .basename($path)."\n";
					$file = new stdClass();
					$file->path = $path;
					$file->mime = $mime;
					$file->size = filesize($path);
					break;
				default:
					unset($file);
			}
		}
	}
	
	private function scan_directory_recursively($path = '', &$name = array() ){
		  $path = $path == ''? dirname(__FILE__) : $path;
		  $lists = @scandir($path);
		  
		  if(!empty($lists)){
		      foreach($lists as $f){ 
			      if(is_dir($path.DIRECTORY_SEPARATOR.$f) && $f != ".." && $f != "."){
			          $this->scan_directory_recursively($path.DIRECTORY_SEPARATOR.$f, &$name); 
			      }else{
			          $name[] = $path.DIRECTORY_SEPARATOR.$f;
			      }
		      }
		  }
		  return $name;
	}
	public function play($track_id){
		
	}
}