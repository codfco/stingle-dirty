<?
/**
 * Smarty Internal Plugin Compile Block Plugin
 * 
 * Compiles code for the execution of block plugin
 * 
 * @package Smarty
 * @subpackage Compiler
 * @author Uwe Tews 
 */

/**
 * Smarty Internal Plugin Compile Block Plugin Class
 */
class Smarty_Internal_Compile_Private_Block_Plugin extends Smarty_Internal_CompileBase {
	// attribute definitions
    public $optional_attributes = array('_any'); 

    /**
     * Compiles code for the execution of block plugin
     * 
     * @param array $args array with attributes from parser
     * @param object $compiler compiler object
     * @param array $parameter array with compilation parameter
     * @param string $tag name of block plugin
     * @param string $function PHP function name
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter, $tag, $function)
    {
        $this->compiler = $compiler;
        if (strlen($tag) < 6 || substr($tag, -5) != 'close') {
            // opening tag of block plugin
        	// check and get attributes
        	$_attr = $this->_get_attributes($args); 
        	if ($_attr['nocache'] === true) {
            	$this->compiler->tag_nocache = true;
        	}
       		unset($_attr['nocache']);
            // convert attributes into parameter array string
            $_paramsArray = array();
            foreach ($_attr as $_key => $_value) {
                if (is_int($_key)) {
                    $_paramsArray[] = "$_key=>$_value";
                } else {
                    $_paramsArray[] = "'$_key'=>$_value";
                } 
            } 
            $_params = 'array(' . implode(",", $_paramsArray) . ')';

            $this->_open_tag($tag, array($_params, $this->compiler->nocache)); 
            // maybe nocache because of nocache variables or nocache plugin
            $this->compiler->nocache = $this->compiler->nocache | $this->compiler->tag_nocache; 
            // compile code
            $output = "<? \$_smarty_tpl->smarty->_tag_stack[] = array('{$tag}', {$_params}); \$_block_repeat=true; {$function}({$_params}, null, \$_smarty_tpl, \$_block_repeat);while (\$_block_repeat) { ob_start();?>";
        } else {
            // must endblock be nocache?
            if ($this->compiler->nocache) {
                $this->compiler->tag_nocache = true;
            } 
            // closing tag of block plugin, restore nocache
            list($_params, $this->compiler->nocache) = $this->_close_tag(substr($tag, 0, -5)); 
            // This tag does create output
            $this->compiler->has_output = true; 
            // compile code
            if (!isset($parameter['modifier_list'])) {
            	$mod_pre = $mod_post ='';
            } else {
            	$mod_pre = ' ob_start(); ';
            	$mod_post = 'echo '.$this->compiler->compileTag('private_modifier',array(),array('modifierlist'=>$parameter['modifier_list'],'value'=>'ob_get_clean()')).';';
            }
            $output = "<? \$_block_content = ob_get_clean(); \$_block_repeat=false;".$mod_pre." echo {$function}({$_params}, \$_block_content, \$_smarty_tpl, \$_block_repeat); ".$mod_post." } array_pop(\$_smarty_tpl->smarty->_tag_stack);?>";
        } 
        return $output . "\n";
    } 
} 

?>