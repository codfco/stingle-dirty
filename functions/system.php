<?
function default_exception_handler(Exception $e){
	HookManager::callHook('NoDebugExceptionHandler', array('e' => $e));
	
	if(Debug::getMode()){
		echo format_exception($e, true);
	}
	else{
		HookManager::callHook('ExceptionHandler', array('e' => $e));
	}
	
	exit;
}

function default_error_handler($severity, $message, $file, $line){
	if ( $severity === E_RECOVERABLE_ERROR or $severity === E_WARNING ) {
		throw new ErrorException($message, $severity, $severity, $file, $line);
	}
}

function shutdown(){
	global $gi;
	if($gi){
		geoip_close($gi);
	}
}

function stingleOutputHandler($buffer){
	$hookArgs = array( 'buffer' => &$buffer );
	
	HookManager::callHook("onOutputHandler", $hookArgs);
	return $buffer;
}
?>