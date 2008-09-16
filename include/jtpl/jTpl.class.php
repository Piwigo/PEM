<?php
/**
* @package     jelix
* @subpackage  jtpl
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * template engine
 * @package     jelix
 * @subpackage  jtpl
 */
class jTpl {

    /**
     * all assigned template variables. Public because Internal use. Don't touch it :-)
     * See methods of jTpl to manage template variables
     * @var array
     */
    public $_vars = array ();

    /**
     * internal use
     * @var array
     */
    public $_meta = array();

    public function __construct(){ }

    /**
     * assign a value in a template variable
     * @param string|array $name the variable name, or an associative array 'name'=>'value'
     * @param mixed  $value the value (or null if $name is an array)
     */
    public function assign ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               $this->_vars[$key] = $val;
           }
        }else{
            $this->_vars[$name] = $value;
        }
    }

    /**
     * concat a value in with a value of an existing template variable
     * @param string|array $name the variable name, or an associative array 'name'=>'value'
     * @param mixed  $value the value (or null if $name is an array)
     */
    public function append ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               if(isset($this->_vars[$key]))
                  $this->_vars[$key] .= $val;
               else
                  $this->_vars[$key] = $val;
           }
        }else{
            if(isset($this->_vars[$name]))
               $this->_vars[$name] .= $value;
            else
               $this->_vars[$name] = $value;
        }
    }

    /**
     * assign a value in a template variable, only if the template variable doesn't exist
     * @param string|array $name the variable name, or an associative array 'name'=>'value'
     * @param mixed  $value the value (or null if $name is an array)
     */
    public function assignIfNone ($name, $value = null){
        if(is_array($name)){
           foreach ($name as $key => $val) {
               if(!isset($this->_vars[$key]))
                  $this->_vars[$key] = $val;
           }
        }else{
            if(!isset($this->_vars[$name]))
               $this->_vars[$name] = $value;
        }
    }



    /**
     * says if a template variable exists
     * @param string $name the variable template name
     * @return boolean true if the variable exists
     */
    public function isAssigned ($name){
        return isset ($this->_vars[$name]);
    }

    /**
     * return the value of a template variable
     * @param string $name the variable template name
     * @return mixed the value (or null if it isn't exist)
     */
    public function get ($name){
        if (isset ($this->_vars[$name])){
            return $this->_vars[$name];
        }else{
            $return = null;
            return $return;
        }
    }

    /**
     * Return all template variables
     * @return array
     */
    public function getTemplateVars (){
        return $this->_vars;
    }

    /**
     * process all meta instruction of a template
     * @param string $tpl template selector
     */
    public function meta($tpl){
        $this->getTemplate($tpl,'template_meta_');
    }

    /**
     * display the generated content from the given template
     * @param string $tpl template selector
     */
    public function display ($tpl){
        $this->getTemplate($tpl,'template_');
    }

    /**
     * include the compiled template file and call one of the generated function
     * @param string $tpl template selector
     * @param string $fctname the internal function name (meta or content)
     */
    protected function  getTemplate($file,$fctname){
        $cachefile = JTPL_CACHE_PATH . $file;
        $tpl = JTPL_TEMPLATES_PATH . $file;

        $mustCompile = $GLOBALS['jTplConfig']['compilation_force']['force'] || !file_exists($cachefile);
        if (!$mustCompile) {
            if (filemtime($tpl) > filemtime($cachefile)) {
            $mustCompile = true;
            }
        }

        if ($mustCompile) {
            include_once(JTPL_PATH . 'jTplCompiler.class.php');

            $compiler = new jTplCompiler();
            $compiler->compile($file);
        }
        require_once($cachefile);
        $fct = $fctname.md5($file);
        $fct($this);
    }

    /**
     * return the generated content from the given template
     * @param string $tpl template selector
     * @return string the generated content
     */
    public function fetch ($tpl){
        ob_start ();
        try{
           $this->getTemplate($tpl,'template_');
           $content = ob_get_clean();
        }catch(Exception $e){
           ob_end_clean();
           throw $e;
        }
        return $content;
    }

    /**
     * optimized version of meta() + fetch()
     * @param string $tpl template selector
     * @return string the generated content
     * @since 1.0b1
     */
    public function metaFetch ($file){
        ob_start ();
        try{
            $tpl = JTPL_TEMPLATES_PATH . $file;
            $cachefile = JTPL_CACHE_PATH . $file;

            $mustCompile = $GLOBALS['jTplConfig']['compilation_force']['force'] || !file_exists($cachefile);
            if (!$mustCompile) {
                if (filemtime($tpl) > filemtime($cachefile)) {
                $mustCompile = true;
                }
            }

            if ($mustCompile) {
                include_once(JTPL_PATH . 'jTplCompiler.class.php');
                $compiler = new jTplCompiler();
                $compiler->compile($file);
            }
            require_once($cachefile);
            $md = md5($file);
            $fct = 'template_meta_'.$md;
            $fct($this);
            $fct = 'template_'.$md;
            $fct($this);
            $content = ob_get_clean();
        }catch(Exception $e){
            ob_end_clean();
            throw $e;
        }
        return $content;
    }
}
?>
