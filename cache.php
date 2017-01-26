<?php
class cache {
	private $path = '';
	private $extension = 'cache';
	// private $file_permissions = 0755;
	private $folder_permissions = 0755;
	
	public function __construct () {
		$this->path = $_SERVER['DOCUMENT_ROOT'].'cache/';
	}
	
	public function __destruct () {
		
	}
	
	/**
	 * _mkdir
	 * 
	 * create folder
	 * 
	 * @param   string  $pwd    folder full path
	 */
	private function _mkdir ($pwd) {
		$path = $result = '';
		$data = array();
		
		$result = TRUE;
		$path = realpath($_SERVER['DOCUMENT_ROOT']);
		$pwd = str_replace($path,'',$pwd);
		$data = explode('/',$pwd);
		
		foreach ($data as $folder) {
			if (empty($folder)) {
				continue;
			}
			
			$path .= '/'.$folder;
			
			if (!is_dir($path)) {
				if (!mkdir($path,$this->folder_permissions,TRUE)) {
					$result = FALSE;
					break;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * _search_file
	 * 
	 * file search
	 * 
	 * @param   string  $path       full folder path
	 * @param	string	$extension  file extension
	 */
	private function _search_file ($path,$extension = '') {
		$list = $pathinfo = array();
		
		$path = realpath($path);
		foreach (scandir($path) as $value) {
			if ($value == '.' || $value == '..') {
				// path
			} else if (is_dir($path.'/'.$value)) {
				$list = array_merge($list,$this->_search_file($path.'/'.$value,$extension));
			} else if (is_file($path.'/'.$value)) {
				$pathinfo = pathinfo($path.'/'.$value);
				
				if ($pathinfo['extension'] == $extension) {
					$list[] = $path.'/'.$value;
				}
			}
		}
		
		return $list;
	}
	
	/**
	 * set
	 * 
	 * save cache data
	 * 
	 * @param   string          $id     cache id or {cache folder}/{cache id}
	 * @param	string|array    $data	cache value
	 * @param	int             $ttl    second
	 */
	public function set ($id,$data,$ttl = 60) {
		$file_path = $result = '';
		$pathinfo = array();
		
		$file_path = $this->path.$id.'.'.$this->extension;
		$pathinfo = pathinfo($file_path);
		
		// create folder
		$result = $this->_mkdir($pathinfo['dirname']);
		
		if ($result) {
			file_put_contents($file_path,serialize(array('time'=>time(),'ttl'=>$ttl,'data'=>$data)));
			chmod($file_path,$this->file_permissions);
			
			$result = FALSE;
			if (is_file($file_path)) {
				$result = TRUE;
			}
		}
		
		return $result;
	}
	
	/**
	 * get
	 * 
	 * get cache data
	 * 
	 * @param   string  $id     cache id or {cache folder}/{cache id}
	 */
	public function get ($id) {
		$file_path = '';
		$cache = $data = array();
		
		$file_path = $this->path.$id.'.'.$this->extension;
		
		if (is_file($file_path)) {
			$cache = unserialize(file_get_contents($file_path));
			
			if ($cache['time'] + $cache['ttl'] > time()) {
				$data = $cache['data'];
			} else {
				$data = NULL;
			}
		} else {
			$data = NULL;
		}
		
		return $data;
	}
	
	/**
	 * delete
	 * 
	 * delete cache data
	 * 
	 * @param   string  $id     cache id or {cache folder}/{cache id}
	 */
	public function delete ($id) {
		$file_path = $result = '';
		
		$result = TRUE;
		$file_path = $this->path.$id.'.'.$this->extension;
		
		if (is_file($file_path)) {
			$result = unlink($file_path);
		}
		
		return $result;
	}
	
	/**
	 * clean
	 * 
	 * delete cache data
	 * 
	 * @param   string      $folder     cache folder
	 * @param   string      $flag       true (all delete) / false (time over delete)
	 */
	public function clean ($folder = '',$flag = FALSE) {
		$folder_path = $id = '';
		$list = array();
		
		$folder_path = $this->path.$folder;
		
		$list = $this->_search_file($folder_path,$this->extension);
		foreach ($list as $value) {
			$id = str_replace(array($this->path,'.'.$this->extension),'',$value);
			
			if ($flag) {
				$this->delete($id);
			} else if (!$this->get($id)) {
				$this->delete($id);
			}
		}
	}
}
