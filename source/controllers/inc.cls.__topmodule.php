<?php

define( 'MESSAGE_LEVEL_INFO',		101 );
define( 'MESSAGE_LEVEL_WARNING',	102 );
define( 'MESSAGE_LEVEL_ERROR',		103 );

class InvalidURIException extends Exception { }

abstract class __TopModule {

	/**
	 * E v a l u a t e s   a n d   r e t u r n s   t h e   r e q u e s t   U R I
	 */
	public static function getRequestUri( $f_bCaseInsensitive = false ) {
		$szRequestUri = '/'.ltrim(str_replace('\\', '/', substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])))), '/');
		if ( 1 < count($x = explode('?', $szRequestUri, 2)) ) {
			$szRequestUri = $x[0];
			parse_str($x[1], $_GET);
		}
		if ( $f_bCaseInsensitive ) {
			$szRequestUri = strtolower($szRequestUri);
		}
		return $szRequestUri;

	} // END getRequestUri() */


	/**
	 * T h e   M V C   s t a r t e r
	 */
	public static function run( $f_szFullRequestUri ) {
		if ( '/' == $f_szFullRequestUri ) {
			// Index
			return new __Home('/');
		}

		$arrUri = explode("/", substr($f_szFullRequestUri, 1));
		$szModule = strtolower(array_shift($arrUri));
		$szModuleClassFile = PROJECT_CONTROLLERS.'/inc.cls.mod_'.$szModule.'.php';

		$inArgs = array();
		if ( 1 < count($arrModule=explode('-', $szModule)) ) {
			foreach ( $arrModule AS $k => $dir ) {
				if ( (string)(int)$dir == $dir ) {
					// is numerical
					$inArgs[] = (int)$dir;
					$arrModule[$k-1] .= '-N';
					unset($arrModule[$k]);
				}
			}
			$szModule = str_replace('-', '_', array_pop($arrModule));
			$szModuleClassFile = PROJECT_CONTROLLERS.'/'.implode('/', $arrModule).'/inc.cls.mod_'.$szModule.'.php';
		}

		$szUri = '/'.implode('/', $arrUri);
		if ( file_exists($szModuleClassFile) ) {
			require_once($szModuleClassFile);
			$szClass = 'mod_' . $szModule;
			return new $szClass( $szUri, $inArgs );
		}

		return new __Home( $f_szFullRequestUri );

	} // END run() */


	protected $m_szRequestUri		= '';
	protected $m_arrHooks			= array();
	protected $m_arrInArgs			= array();

	protected $m_arrMessages		= array(
		'level'							=> array(),
		'msg'							=> array(),
	);
	protected $m_arrMessageLevels	= array(
		MESSAGE_LEVEL_INFO				=> 'Info',
		MESSAGE_LEVEL_WARNING			=> 'Warning',
		MESSAGE_LEVEL_ERROR				=> 'Error',
	);

	public $tpl	= null;
	public $db	= null;



	/**
	 * 
	 */
	final public function __construct( $f_szUri, $f_arrInArgs = array() )
	{
		$this->__preload();

		$this->m_arrInArgs = $f_arrInArgs;

		if ( '' == $f_szUri ) { $f_szUri = '/'; }
		$this->m_szRequestUri = $f_szUri;

	} // END __construct() */


	/**
	 * 
	 */
	final public function exec( $f_bAutoExec = true )
	{
		foreach ( $this->m_arrHooks AS $szPath => $szHook )
		{
			$szMatch = str_replace('#', '([0-9]+)', $szPath);
			$szMatch = str_replace('*', '([0-9a-zA-Z,_\.\@\-]+)', $szMatch);
			if ( 0 < preg_match('#^'.$szMatch.'$#', $this->m_szRequestUri, $parrMatches) )
			{
				if ( is_callable(array($this, $szHook)) )
				{
					array_shift($parrMatches);
					call_user_func_array(array($this, '__start'), $this->m_arrInArgs);
					$r = call_user_func_array( array($this, $szHook), $parrMatches );
					if ( $f_bAutoExec ) {
						if ( is_string($r) ) {
							exit($r);
						}
						exit;
					}
					return $r;
				}
			}
		}

		throw new InvalidURIException;
//		exit(date('[Y-m-d H:i:s').'] Invalid request URI: '.$GLOBALS['g_szRequestUri']);

	} // END exec() */


	protected function __start() { }

	protected function __preload() { }

	final public function __toString() { return ''; }


	/**
	 * 
	 */
	public function mf_ResetMessages()
	{
		$_SESSION[SESSION_NAME]['messages'] = $this->m_arrMessages;

	} // END mf_ResetMessages() */

	/**
	 * 
	 */
	protected function mf_GetMessages( $f_iLevel = null )
	{
		$arrMsgs = !empty($_SESSION[SESSION_NAME]['messages']) ? $_SESSION[SESSION_NAME]['messages'] : $this->m_arrMessages;
		$this->mf_ResetMessages();
		return $arrMsgs;

	} // END mf_GetMessages() */

	/**
	 * 
	 */
	protected function mf_AddMessage( $f_szMessage, $f_iLevel = MESSAGE_LEVEL_INFO )
	{
		$_SESSION[SESSION_NAME]['messages']['level'][]	= $f_iLevel;
		$_SESSION[SESSION_NAME]['messages']['msg'][]	= $f_szMessage;

	} // END mf_AddMessage() */

	/**
	 * 
	 */
	protected function mf_AssignMessages()
	{
		$this->tpl = template::instance();
		$this->tpl->assign( 'arrMessageLevels', $this->m_arrMessageLevels );
		$arrMsgs = $this->mf_GetMessages();
		arsort($arrMsgs['level'], SORT_NUMERIC);
		$arrMessages = array();
		foreach ( $arrMsgs['level'] AS $k => $iLevel ) {
			$arrMessages[] = array( 'level' => $iLevel, 'msg' => $arrMsgs['msg'][$k] );
		}
		$this->tpl->assign( 'arrMessages', $arrMessages );
		$szMessages = 0 < count($arrMessages) ? $this->tpl->fetch('messages.tpl.html') : '';
		$this->tpl->assign( 'szMessages', $szMessages );
		unset($this->tpl->smarty->_tpl_vars['arrMessages'], $this->tpl->smarty->_tpl_vars['arrMessageLevels']);

	} // END mf_AssignMessages() */



} // END Class __TopModule


