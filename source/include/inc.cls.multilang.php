<?php

class Multilang
{

	public $m_iLanguage			= '1';
	public $m_arrTranslations	= array();

	static public $_me;


	/**
	 * C O N S T R U C T O R
	 */
	public function __construct( $f_iLanguage = '1' )
	{
		$this->m_arrTranslations = $this->getList($f_iLanguage);

	} // END __construct() */


	/**
	 * @scope	static
	 */
	static public function translate( $f_szKey )
	{
		if ( empty(self::$_me) ) {
			global $g_arrClub;
			$iLanguageId = empty($g_arrClub['language_id']) ? 1 : $g_arrClub['language_id'];
			self::$_me = new Multilang($iLanguageId);
		}
		$args = func_get_args();
		return call_user_func_array( array(self::$_me, '_translate'), $args );

	} // END translate() */


	/**
	 * used by the static translate function
	 */
	public function _translate( $f_szKey, $f_bUCFirst = true )
	{
		if ( empty($this->m_arrTranslations[strtoupper($f_szKey)]) )
		{
			return 'MISSING:'.$f_szKey;
		}
		$r = trim($this->m_arrTranslations[strtoupper($f_szKey)]);
		return $f_bUCFirst ? ucfirst($r) : $r;

	} // END translate() */


	/**
	 * getList - return the array with translations for given language
	 */
	public function getList( $f_iLanguage )
	{
		return Buffers::getLanguage($f_iLanguage);

	} // END getList() */


} // END Class Multilang

?>