<?php # 0.1

require_once(dirname(__FILE__).'/inc.cls.templateengine.php');

class Template {

	static public $class = '';
	static public $instance;
	static public function instance() {
		if ( empty(self::$instance) ) {
			self::$class = class_exists(self::$class, false) ? self::$class : __CLASS__;
			self::$instance = new self::$class;
		}
		return self::$instance;
	}

	public $tpl;

	public function __construct() {
		$this->tpl = new TemplateEngine('./templates');
	}

	public function assign() {
		$args = func_get_args();
		return call_user_func_array(array($this->tpl, 'assign'), $args);
	}

	public function fetch() {
		$args = func_get_args();
		return call_user_func_array(array($this->tpl, 'fetch'), $args);
	}

	public function display( $tpl, $frame = 'framework.tpl.html' ) {
		if ( !$this->tpl->exists('_szHtmlTitle') ) {
			$this->tpl->assign('_szHtmlTitle', '');
		}
		$this->tpl->assign( '_szHtmlContents', $this->tpl->fetch($tpl) );
		return $this->tpl->display( $frame );
	}

}


