<?php

class mod_access extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'overview',
		'/overview/'			=> 'overview',
		'/oele/#/boele/' => 'OeleXBoele',
	);



	/**
	 * T e s t
	 */
	protected function OeleXBoele( $x )
	{
		echo 'Params: ';
		print_r(func_get_args());

	} // END OeleXBoele() */



	/**
	 * O v e r v i e w
	 */
	protected function overview()
	{
		echo __METHOD__;
		echo '<br/>params: ';
		print_r(func_get_args());

	} // END overview() */



} // END Class mod_access

?>