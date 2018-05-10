<?php
class LoaderMail extends Loader{
	protected function includes(){
		require_once ('MailException.class.php');
		require_once ('MailSender.class.php');
		require_once ('Mail.class.php');
	}
	
	protected function loadMail(){
		Reg::register($this->config->Objects->Mail, new MailSender($this->config->AuxConfig));
	}
}
