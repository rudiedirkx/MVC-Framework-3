<?php

class mod_version_2 extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'index',
		'/1/'					=> 'test1',
		'/2/'					=> 'test2',
		'/3/'					=> 'test3',
	);

	protected function __start() {
		require_once(PROJECT_MODELS.'/db/new/inc.cls.activerecordobject.php');
		require_once(PROJECT_MODELS.'/db/new/inc.cls.person.php');
		$this->db = $GLOBALS['db'];
		ActiveRecordObject::setDbObject( $this->db );
	}


	/**
	 * 
	 */
	protected function test1()
	{
		$id = 2;
		try {
			$p = Person::finder()->get($id);
			var_dump($p);
		} catch ( Exception $ex ) {
			echo 'Exception caught: No person ['.$id.'] found.';
		}

	} // END test1() */


	/**
	 * 
	 */
	protected function test2()
	{
		

	} // END test2() */


	/**
	 * 
	 */
	protected function test3()
	{
		

	} // END test3() */


	/**
	 * 
	 */
	protected function index()
	{
		echo '<ul>';
		foreach ( $this->m_arrHooks AS $addr => $hook ) {
			if ( $hook != __FUNCTION__ ) {
				echo '<li><a href="/'.trim($GLOBALS['g_szRequestUri'], '/').$addr.'">'.$hook.'</a></li>';
			}
		}
		echo '</ul>';

	} // END index() */



} // END Class mod_version_2


