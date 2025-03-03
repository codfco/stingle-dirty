<?
class LoaderYubikey extends Loader{
	protected function includes(){
		require_once ('Yubikey.class.php');
		require_once ('YubikeyUserAuthorization.class.php');
		require_once ('Exceptions/YubikeyException.class.php');
	}
	
	protected function customInitBeforeObjects(){
		Tbl::registerTableNames('YubikeyUserAuthorization');
	}
	
	protected function loadYubikeyUserAuthorization(){
		$usersConfig = ConfigManager::getConfig("Users","Users");
		
		$resultingConfig = ConfigManager::mergeConfigs($usersConfig->AuxConfig, $this->config->AuxConfig);
		
		$yubikeyUserAuthorization = new YubikeyUserAuthorization(	Reg::get($usersConfig->Objects->UserManagement),
																	$resultingConfig);
		
		Reg::register($this->config->Objects->YubikeyUserAuthorization, $yubikeyUserAuthorization);
	}
}
?>