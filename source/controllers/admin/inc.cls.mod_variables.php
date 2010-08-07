<?php

class mod_variables extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'overview',
		'/overview/'			=> 'overview',
	);


	/**
	 * O v e r v i e w
	 */
	protected function overview()
	{
		echo __METHOD__;

	} // END overview() */



} // END Class mod_variables

?>