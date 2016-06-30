<?
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


function smarty_function_rand($params, &$smarty){
	return generateRandomString($params['length'], $params['type']);
}
?>