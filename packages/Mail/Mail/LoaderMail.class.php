<?php
class LoaderMail extends Loader{
	protected function includes(){
		require_once ('Exceptions/MailException.class.php');
		require_once ('Exceptions/DKIMConfigException.class.php');
		require_once ('Managers/MailSender.class.php');
		require_once ('Objects/Mail.class.php');
		require_once ('Objects/DKIMConfig.class.php');
	}	
}
