<?
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */
 
/**
 * Smarty escape modifier plugin
 * 
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  escape string for output
 * 
 * @link http://smarty.php.net/manual/en/language.modifier.count.characters.php count_characters (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com> 
 * @param string $string input string
 * @param string $esc_type escape type
 * @param string $char_set character set
 * @return string escaped input string
 */
function smarty_modifier_escape($string, $esc_type = 'html', $char_set = SMARTY_RESOURCE_CHAR_SET)
{
    if (!function_exists('mb_str_replace')) {
        // simulate the missing PHP mb_str_replace function
        function mb_str_replace($needles, $replacements, $haystack)
        {
            $rep = (array)$replacements;
            foreach ((array)$needles as $key => $needle) {
                $replacement = $rep[$key];
                $needle_len = mb_strlen($needle);
                $replacement_len = mb_strlen($replacement);
                $pos = mb_strpos($haystack, $needle, 0);
                while ($pos !== false) {
                    $haystack = mb_substr($haystack, 0, $pos) . $replacement
                     . mb_substr($haystack, $pos + $needle_len);
                    $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
                } 
            } 
            return $haystack;
        } 
    } 
    switch ($esc_type) {
        case 'html':
            return htmlspecialchars($string, ENT_QUOTES, $char_set);

        case 'htmlall':
            return htmlentities($string, ENT_QUOTES, $char_set);

        case 'url':
            return rawurlencode($string);

        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));

        case 'quotes': 
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

        case 'hex': 
            // escape every character into hex
            $return = '';
            for ($x = 0; $x < strlen($string); $x++) {
                $return .= '%' . bin2hex($string[$x]);
            } 
            return $return;

        case 'hexentity':
            $return = '';
            for ($x = 0; $x < strlen($string); $x++) {
                $return .= '&#x' . bin2hex($string[$x]) . ';';
            } 
            return $return;

        case 'decentity':
            $return = '';
            for ($x = 0; $x < strlen($string); $x++) {
                $return .= '&#' . ord($string[$x]) . ';';
            } 
            return $return;

        case 'javascript': 
            // escape quotes and backslashes, newlines, etc.
            return strtr($string, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));

        case 'mail': 
            // safe way to display e-mail address on a web page
            if (function_exists('mb_substr')) {
                return mb_str_replace(array('@', '.'), array(' [AT] ', ' [DOT] '), $string);
            } else {
                return str_replace(array('@', '.'), array(' [AT] ', ' [DOT] '), $string);
            } 

        case 'nonstd': 
            // escape non-standard chars, such as ms document quotes
            $_res = '';
            for($_i = 0, $_len = strlen($string); $_i < $_len; $_i++) {
                $_ord = ord(substr($string, $_i, 1)); 
                // non-standard char, escape it
                if ($_ord >= 126) {
                    $_res .= '&#' . $_ord . ';';
                } else {
                    $_res .= substr($string, $_i, 1);
                } 
            } 
            return $_res;

        default:
            return $string;
    } 
} 

?>