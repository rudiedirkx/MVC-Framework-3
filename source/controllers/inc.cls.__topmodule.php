<?php

define( 'MESSAGE_LEVEL_INFO',		101 );
define( 'MESSAGE_LEVEL_WARNING',	102 );
define( 'MESSAGE_LEVEL_ERROR',		103 );

abstract class __TopModule {

	/**
	 * E v a l u a t e s   a n d   r e t u r n s   t h e   r e q u e s t   U R I
	 */
	public static function getRequestUri() {
		$szRequestUri = '/'.ltrim(str_replace('\\', '/', substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])))), '/');
		if ( 1 < count($x = explode('?', $szRequestUri, 2)) ) {
			$szRequestUri = $x[0];
			parse_str($x[1], $_GET);
		}
		return strtolower($szRequestUri);

	} // END getRequestUri() */


	/**
	 * 
	 */
	public static function loadModule( $f_arrModule, $f_szPath ) {
		array_push($f_arrModule, 'inc.cls.mod_'.($szModule=array_pop($f_arrModule)).'.php');
		$szClassFile = implode('/', $f_arrModule);
		$szClassPath = PROJECT_CONTROLLERS.'/'.$szClassFile;
		$szClass = 'mod_' . $szModule;

		if ( file_exists($szClassPath) ) {
			require_once($szClassPath);
			return new $szClass($f_szPath);
		}
		return false;

	} // END loadModule() */


	/**
	 * T h e   d e f a u l t   M V C   s t a r t e r
	 */
	public static function run( $f_szFullRequestUri ) {
		if ( '/' != $f_szFullRequestUri ) {
			$arrUri = explode('/', substr($f_szFullRequestUri, 1), 2);
			$szModule = $arrUri[0];
			$szPath = '/'.( 1 < count($arrUri) ? $arrUri[1] : '' );

			if ( is_object($mod = self::loadModule(explode('-', $szModule), $szPath)) ) {
				return $mod;
			}
		}

		require_once(PROJECT_CONTROLLERS.'/inc.cls.__home.php');
		return new __Home( $f_szFullRequestUri );

	} // END run() */


	protected $m_szRequestUri		= '';
	protected $m_arrHooks			= array();

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
	 * C h e c k   f o r   P O S T   v a r s
	 */
	protected function mf_RequirePostVars( $f_arrVars, $f_bExit = false )
	{
		if ( 0 < count($arrMissing=array_diff_key($f_arrVars, $_POST)) ) {
			$szMessage = multilang::translate('MISSING_PARAMETERS').': '.implode(', ', array_map(array('multilang','translate'), $arrMissing));
			if ( $f_bExit ) {
				exit($szMessage);
			}
			return $szMessage;
		}
		return true;

	} // END mf_RequirePostVars() */



	/**
	 * 
	 */
	final public function __construct( $f_szUri )
	{
		$this->m_szRequestUri = $f_szUri;

		$this->__preload();

		if ( '' == $f_szUri ) { $f_szUri = '/'; }
		else { $f_szUri = strtolower($f_szUri); }

		foreach ( $this->m_arrHooks AS $szPath => $szHook )
		{
			$szMatch = str_replace('#', '([0-9]+)', $szPath);
			$szMatch = str_replace('*', '([0-9a-zA-Z_\.\@\-]+)', $szMatch);
			if ( 0 < preg_match('#^'.$szMatch.'$#', $f_szUri, $parrMatches) )
			{
				if ( is_callable(array($this, $szHook)) )
				{
					array_shift($parrMatches);
					$this->__start();
					$r = call_user_func_array( array($this, $szHook), $parrMatches );
					if ( is_string($r) ) {
						exit($r);
					}
					exit;
				}
			}
		}

		global $g_szRequestUri;
		exit(date('[Y-m-d H:i:s').'] Invalid request URI: '.$g_szRequestUri);

	} // END __construct() */


	protected function __start() { }

	protected function __preload() { }

	final public function __toString() { return ''; }


	/**
	 * A d d   a   l o g   t o   t h e   S Q L   ` l o g s `   t a b l e
	 */
	protected function mf_AddLog($f_szAction, $f_szExtra, $f_iUserId = null)
	{
		$iUserId = null !== $f_iUserId ? (int)$f_iUserId : null;
		global $g_arrClub;
		$iClubId = !empty($g_arrClub['id']) ? (int)$g_arrClub['id'] : null;
		$arrInsert = array(
			'user_id'		=> $iUserId,
			'club_id'		=> $iClubId,
			'action'		=> $f_szAction,
			'extra'			=> $f_szExtra,
			'utc_time'		=> time(),
			'ip'			=> $_SERVER['REMOTE_ADDR'],
		);
		$i = db_insert('logs', $arrInsert);
		return $i;

	} // END mf_AddLog() */


	/**
	 * 
	 */
	protected function mf_ResetMessages()
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


	protected function mf_AssignMenuFrame()
	{
		$this->tpl = template::instance();
		global $g_arrClub;
		if ( empty($g_arrClub['id']) ) {
			return;
		}

//		$iMenuLifeTime = 10;
		if ( empty($_SESSION[SESSION_NAME]['menu']) /*|| ifsetor($_SESSION[SESSION_NAME]['menu_time'],0) < time()-$iMenuLifeTime*/ ) {
			$arrMenuItems = db_fetch('
			SELECT
				i.*
			FROM
				menu_items_in_clubs c,
				menu_items i
			WHERE
				c.menu_item_id = i.id AND
				c.club_id = '.(int)$g_arrClub['id'].'
			ORDER BY
				c.zindex ASC;
			');
			$arrMenu = array();
			foreach ( $arrMenuItems AS $arrMenuItem ) {
				if ( !$arrMenuItem['needed_access_zone'] || user::access($arrMenuItem['needed_access_zone']) ) {
					$arrMenu[] = array($arrMenuItem['url'], multilang::translate($arrMenuItem['language_key']));
				}
			}
//			$_SESSION[SESSION_NAME]['menu_time'] = time();
			$_SESSION[SESSION_NAME]['menu'] = $arrMenu;
		}
		$this->tpl->assign( 'arrMenuItems', $_SESSION[SESSION_NAME]['menu'] );
		$this->tpl->assign( 'szLeftFrame', $this->tpl->fetch('user/menu_frame.tpl.html') );

	} // END mf_AssignMenuFrame() */


} // END Class __TopModule

?>
