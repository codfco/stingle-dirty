<?
class TextsManager extends DbAccessor{
	
	const TBL_TEXTS = "texts";
	
	public  function __construct($dbInstanceKey = null){
		parent::__construct($dbInstanceKey);
<<<<<<< HEAD

		$this->host = $host;
		$this->language = $language;
	}


	/**
	 * Get value of the text. If not set, takes default|current host and lang.
	 *
	 * @param string $textName
	 * @param string[optional] $host_ext
	 * @param string[optional] $lang
	 * @return string
	 */
	public function getTextValue($textName, Host $host=NULL, Language $lang=NULL){
		if($this->textNameExists($textName)){
			if($host === null){
				$host = $this->host;
			}
			if($lang === null){
				$lang = $this->language;
			}
			$text_id = Text::getTextId($textName);

			$textValue = $this->getTextVal($text_id, $lang->id, $host->id); //Given host Language text value
			if(!empty($textValue)){
				return $textValue;
			}
			else{
				$host_lang_id = HostLanguageManager::getHostLanguageId($host, $lang);
				if($this->isAliased($text_id, $host_lang_id)){
					return $this->getAlisedTextVal($text_id,$host_lang_id);
				}
			}
			//return $this->getDefaultText($text_id); //Given text default value
			return "";
		}
		throw new InvalidArgumentException("No text with name '".$textName."'");
	}

	public function getDisplayProperty($textName, Host $host=NULL, Language $lang=NULL){
		if($this->textNameExists($textName)){
			if($host === null){
				$host = $this->host;
			}
			if($lang === null){
				$lang = $this->language;
			}
			$text_id = Text::getTextId($textName);

			if($this->getDisplay($text_id,$lang->id, $host->id)==1){
				return "yes";
			}
			return "no";
		}
		throw new InvalidArgumentException("No text with name '".$textName."'");
	}

	private function getDisplay($text_id, $lang_id, $host_id, $cacheMinutes = 0){
		if(!is_numeric($text_id) or !is_numeric($lang_id) or !is_numeric($host_id) ){
			throw new InvalidIntegerArgumentException("Method arguments must be integer!!!");
		}
		$this->query->exec("SELECT `display` FROM ".Tbl::get('TBL_TEXTS_VALUES', 'Text') ." tv
							LEFT JOIN `".Tbl::get('TBL_HOST_LANGUAGE', 'HostLanguageManager') ."` hl ON hl.`id`=tv.`host_language` 
							WHERE `text_id`  = $text_id AND hl.`host_id` = $host_id AND hl.`lang_id` = $lang_id",$cacheMinutes);
		if($this->query->countRecords()){
			return $this->query->fetchField("display");
		}
		else{
			return 1;
		}
	}

	public function insertPostText($post){
		$text_id = $post["text_id"];
		$host_language_id = $post["hl_id"];
		$value = stripcslashes($post["text"]);
		$this->insertTextValue($text_id,$host_language_id,$value);
	}

	public function setDisplay($text_id, $host_language_id,$display){
		$this->query->exec("INSERT INTO `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` (`text_id`,`host_language`,`display`)
							VALUES ('$text_id','$host_language_id','$display') 
							ON DUPLICATE KEY UPDATE `display`='$display'");
	}

	public function isAliased($text_id, $host_lang_id){
		if(!is_numeric($text_id) or !is_numeric($host_lang_id)){
			throw new InvalidArgumentException("No numeric arguments given");
		}
		$this->query->exec("SELECT tv.id FROM `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` tv
							RIGHT JOIN ".Tbl::get('TBL_TEXTS_ALIASES', 'Text') ." ta ON tv.id = ta.value_id
							WHERE ta.host_language = '$host_lang_id' AND tv.text_id = '$text_id'");
		if($this->query->countRecords() == 1){
=======
	}
	
	private function textNameExists($textName, $groupName, $cacheMinutes = null){
		if(empty($textName)){
			throw new InvalidArgumentException("\$textName have to be non empty");
		}
		if(empty($groupName)){
			throw new InvalidArgumentException("\$groupName have to be non empty");
		}
		
		$group = Reg::get(ConfigManager::getConfig("Texts")->Objects->TextsGroupManager)->getGroupByName($groupName, $cacheMinutes);
		
		$this->query->exec("SELECT count(*) as `count` 
								FROM `".Tbl::get('TBL_TEXTS') ."` 
								WHERE `name`='$textName' AND `group_id`='{$group->id}'", $cacheMinutes);
		
		if($this->query->fetchField("count") == 1){
>>>>>>> 00223d1... Halfly done new Texts Manager
			return true;
		}
		return false;
	}
	
<<<<<<< HEAD
	public function getTextValueId($text_id, $host_language_id){
		if(!is_numeric($text_id) or !is_numeric($host_language_id)){
			throw new InvalidIntegerArgumentException("text id and host_language_id should be an integer. text_id: ". $text_id ." and host_lang_id:".$host_language_id." given.");
		}
		$this->query->exec("SELECT id FROM `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` WHERE `text_id` = {$text_id} AND `host_language`={$host_language_id}");
		return $this->query->fetchField("id");
=======
	public function getTextById($textId, $cacheMinutes = null){
		if(empty($textId)){
			throw new InvalidArgumentException("\$textId have to be non empty");
		}
		if(!is_numeric($textId)){
			throw new InvalidArgumentException("\$textId have to be integer");
		}
		
		$this->query->exec("SELECT * FROM `".Tbl::get('TBL_TEXTS')  ."` 
								WHERE `id` = '{$textId}'", $cacheMinutes);
		
		if($this->query->countRecords() == 0){
			throw new RuntimeException("There is no text with id $textId");
		}
		
		return $this->getTextObjectFromData($this->query->fetchRecord(), $cacheMinutes);
>>>>>>> 00223d1... Halfly done new Texts Manager
	}
	
	public function getTextByName($textName, $groupName, $cacheMinutes = null){
		if(empty($textName)){
			throw new InvalidArgumentException("\$textName have to be non empty");
		}
		if(empty($groupName)){
			throw new InvalidArgumentException("\$groupName have to be non empty");
		}
		
		$group = Reg::get(ConfigManager::getConfig("Texts")->Objects->TextsGroupManager)->getGroupByName($groupName, $cacheMinutes);
		
		$this->query->exec("SELECT * FROM `".Tbl::get('TBL_TEXTS')  ."` 
								WHERE `name` = '{$textName}' AND `group_id`='{$group->id}'", $cacheMinutes);
		
		if($this->query->countRecords() == 0){
			throw new RuntimeException("There is no text with name $textName");
		}
		
		return $this->getTextObjectFromData($this->query->fetchRecord(), $cacheMinutes);
	}
	
	public function addText(Text $text, TextsGroup $group){
		if(empty($text->name)){
			throw new InvalidArgumentException("You have to specify name for new text");
		}
<<<<<<< HEAD
		$this->query->exec("DELETE FROM `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` WHERE id=".$val_id);
	}



	/**
	 * Given host all values. (for texts manager)
	 *
	 * @param unknown_type $textName
	 * @param Host $host
	 * @param unknown_type $cacheMinutes
	 * @return unknown
	 */
	public function getHostTextValues($textName, Host $host, $cacheMinutes = 0){
		if($this->textNameExists($textName)){
			$values = array();
			
			$text_id = Text::getTextId($textName);

			$this->query->exec("SELECT tv.*, tv.id value_id, hl.lang_id, hl.id unic_id  FROM `".Tbl::get('TBL_HOST_LANGUAGE', 'HostLanguageManager') ."` hl
							LEFT JOIN `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` tv ON (hl.`id`=tv.`host_language` AND tv.text_id=$text_id)
							WHERE  hl.host_id = ".$host->id,$cacheMinutes);
			while (($rec = $this->query->fetchRecord()) != false) {
				if(empty($rec["value"])){
					$rec["value"] = static::EMPTY_TEXT_FLAG ;
				}
				$values[$rec["lang_id"]] = array("hl_id"=>$rec["unic_id"], "value"=>$rec["value"],
				"value_id"=>$rec["value_id"],"default"=>$rec["default"]);
			}
			return $values;

		}
		throw new InvalidArgumentException("No text with name '".$textName."'");
	}
	
	private function getAlisedTextVal($text_id, $host_lang_id, $cacheMinutes = 0){
		if(!is_numeric($text_id) or !is_numeric($host_lang_id)){
			throw new InvalidIntegerArgumentException("Method arguments must be integer!!!");
		}
		$this->query->exec("SELECT tv.`value` FROM `".Tbl::get('TBL_TEXTS_ALIASES', 'Text')."` ta
					LEFT JOIN `".Tbl::get('TBL_TEXTS_VALUES', 'Text')."` tv ON tv.id = ta.value_id 
					 WHERE ta.`host_language` = $host_lang_id 
					AND text_id = $text_id");
		return $this->query->fetchField("value");
	}

	private  function getTextVal($text_id, $lang_id, $host_id, $cacheMinutes = 0){
		if(!is_numeric($text_id) or !is_numeric($lang_id) or !is_numeric($host_id) ){
			throw new InvalidIntegerArgumentException("Method arguments must be integer!!!");
		}
		$this->query->exec("SELECT `value` FROM ".Tbl::get('TBL_TEXTS_VALUES', 'Text') ." tv
							LEFT JOIN `".Tbl::get('TBL_HOST_LANGUAGE', 'HostLanguageManager') ."` hl ON hl.`id`=tv.`host_language` 
							WHERE `text_id`  = $text_id AND hl.`host_id` = $host_id AND hl.`lang_id` = $lang_id",$cacheMinutes);
		if($this->query->countRecords()){
			return $this->query->fetchField("value");
=======
		if(empty($group->id)){
			throw new InvalidArgumentException("Group ID have to be specified");
		}
		if(!is_numeric($group->id)){
			throw new InvalidArgumentException("Group ID have to be integer");
		}
		$this->query->exec("INSERT INTO `".Tbl::get('TBL_TEXTS') . "` (`group_id`, `name`, `description`) 
								VALUES('{$group->id}', '{$text->name}', '{$text->description}')");
		return $this->query->affected();
	}
	
	public function updateText(Text $text){
		if(empty($text->id)){
			throw new InvalidArgumentException("Text ID have to be specified");
		}
		if(!is_numeric($text->id)){
			throw new InvalidArgumentException("Text ID have to be integer");
		}
		$this->query->exec("UPDATE `".Tbl::get('TBL_TEXTS') . "` SET 
								`group_id`='{$text->group->id}', 
								`name`='{$text->name}', 
								`description`='{$text->description}', 
							WHERE `id`='{$text->id}'");
		return $this->query->affected();
	}
	
	public function deleteText(Texts $text){
		if(empty($text->id)){
			throw new InvalidArgumentException("Text ID have to be specified");
		}
		if(!is_numeric($text->id)){
			throw new InvalidArgumentException("Text ID have to be integer");
>>>>>>> 00223d1... Halfly done new Texts Manager
		}
		
		$this->query->exec("DELETE FROM `".Tbl::get('TBL_TEXTS') . "` WHERE `id`='{$text->id}'");
		
		return $this->query->affected();
	}
<<<<<<< HEAD

	/*private function getDefaultText($text_id){
		if(!is_numeric($text_id)){
			throw new InvalidIntegerArgumentException("text_id argument must be integer.");
		}
		$this->query->exec("SELECT `value` FROM `".Tbl::get('TBL_TEXTS_VALUES', 'Text') ."` WHERE `text_id` = {$text_id} AND `default`=1");
		if($this->query->countRecords()){
			return $this->query->fetchField("value");
		}
		if($debug_mode){
			throw new InvalidArgumentException("Nor value nor alias setted for text with id ".$text_id." (text_id:".$text_id.") for this host/language.");
		}
		return "_~#~_"; // return this sign if I have nothink to return.

	}*/


	private function textNameExists($textName){
		if($this->query->exec("SELECT count(*) as `count` FROM `".Tbl::get('TBL_TEXTS', 'Text') ."` WHERE `name`='$textName'")){
			if($this->query->fetchField("count") == 1){
				return true;
			}
		}
		return false;
=======
	
	
	protected function getTextObjectFromData($data, $cacheMinutes = null){
		$text = new Text();
		$text->id = $data['id'];
		$text->group = Reg::get(ConfigManager::getConfig("Texts")->Objects->TextsGroupManager)->getGroupById($data['group_id'], $cacheMinutes);
		$text->name = $data['name'];
		$text->description = $data['description'];
>>>>>>> 00223d1... Halfly done new Texts Manager
	}
}
?>