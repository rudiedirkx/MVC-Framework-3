<?php # 0.1

class TemplateHelpers {

	static public $instance;
	static public function instance() {
		return empty(self::$instance) ? self::$instance=new self : self::$instance;
	}

	public function html_options($options, $name = '', $extra = '') {
		$szOptions = '';
		foreach ( $options AS $k => $v ) {
			$szOptions .= '<option value="'.(string)$k.'">'.(string)$v.'</option>';
		}
		return ( $name ? '<select name="'.(string)$name.'"'.( $extra ? ' '.$extra : '' ).'>' : '' ) . $szOptions . ( $name ? '</select>' : '' );
	}

}

class TemplateEngine {

	protected $tpl_dir;
	protected $vars = array();
	public $helpers;

	public function __construct( $tpl_dir ) {
		if ( !is_readable($tpl_dir) || !is_dir($tpl_dir) || !realpath($tpl_dir) ) {
			throw new Exception('Template dir \''.$tpl_dir.'\' isn\'t a readable folder');
		}
		$this->tpl_dir = realpath($tpl_dir);
	}

	public function assign( $key, $value ) {
		$this->vars[$key] = $value;
	}

	public function display( $tpl, $exit = false ) {
		$this->fetch($tpl, true);
		if ( $exit ) {
			exit;
		}
	}

	public function fetch( $tpl, $display = false ) {
		$__inc_path = ini_set('include_path', $this->tpl_dir);
		$__err_rep = ini_set('error_reporting', 0);
//		$__sot = ini_set('short_open_tag', '1');
		$this->helpers = TemplateHelpers::instance();
		$template = $this;
		extract($this->vars);
		if ( $display ) {
			include($tpl);
			$contents = true;
		}
		else {
			ob_start();
			include($tpl);
			$contents = ob_get_contents();
			ob_end_clean();
		}
		ini_set('include_path', $__inc_path);
		ini_set('error_reporting', $__err_rep);
//		ini_set('short_open_tag', $__sot);
		return $contents;
	}

	public function tpl_dir() {
		return $this->tpl_dir;
	}

	public function exists($var) {
		return isset($this->vars[$var]);
	}

}


