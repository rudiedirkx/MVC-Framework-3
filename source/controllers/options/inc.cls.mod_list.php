<?php

class mod_list extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'overview',
		'/overview/'			=> 'overview',

		'/reservations/'		=> 'reservations',

		'/members/'				=> 'members',
		'/users/'				=> 'members',
	);


	/**
	 * O v e r v i e w
	 */
	protected function overview()
	{
		echo __METHOD__;

	} // END overview() */


	/**
	 * 
	 */
	protected function reservations()
	{
		echo __METHOD__;

	} // END reservations() */


	/**
	 * 
	 */
	protected function members()
	{
		echo __METHOD__;

	} // END members() */



} // END Class mod_list

?>