<?
$defaultConfig = array(	
						'Objects' => array(	'LanguageManager' => 'lm'  ),
						'ObjectsIgnored' => array(  'Language' => 'language'  ),
						'Hooks' => array(	'AfterThisPluginTreeInit' => 'GetLanguageObj',
											'AfterPackagesLoad' => 'DefineAllConstants'  ),
						'Memcache' => array(  'LanguageManager' => -1, 'Language' => -1  )
					);
?>