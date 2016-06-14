<?
class Config{
	
	public function __construct(array $CONFIG = null){
		if($CONFIG !== null){
			$this->parseConfig($CONFIG);
		}
	}
	
	public function __get($name){
		if(!isset($this->$name)){
			throw new RuntimeException("There is no such config element with name $name");
		}
		return $this->$name;
	}
	
	public function __set($name, $value){
		$this->$name = $value;
	}
	
	public function __isset($name){
		if(isset($this->$name)){
			return true;
		}
		return false;
	}
	
	public function toArray($recursive = false){
		$returnArray = array();
		foreach(get_object_vars($this) as $key=>$value){
			if($key !== 'config'){
				if($recursive === true and is_a($value,"Config")){
					$returnArray[$key] = $value->toArray(true);
				}
				else{
					$returnArray[$key] = $value;
				}
			}
		}
		
		return $returnArray;
	}
	
	private function parseConfig(array $configArray){
		foreach($configArray as $key=>$value){
			if(is_array($value)){
				$this->$key = new Config($configArray[$key]);
			}
			else{
				$this->$key = $value;
			}
		}
	}
}
?>