<?php
class LoaderFacebookSDK extends Loader{
	
	protected function includes(){
		require_once ('Facebook/autoload.php');
		require_once ('Managers/FacebookSDK.class.php');
		require_once ('Objects/FacebookPhotoAlbum.class.php');
		require_once ('Objects/FacebookPhoto.class.php');
	}
	
	protected function customInitBeforeObjects(){
		Tbl::registerTableNames('FacebookSDK');
	}
}
