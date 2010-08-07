<?php

class mod_images extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'overview',
		'/overview/'			=> 'overview',

		'/upload'				=> 'upload',

		'/banners/'				=> 'banners',

		'/userpics/'			=> 'userpics',
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
	protected function upload()
	{
		echo __METHOD__;

	} // END upload() */


	/**
	 * 
	 */
	protected function banners()
	{
		echo __METHOD__;

	} // END banners() */


	/**
	 * 
	 */
	protected function userpics()
	{
		echo __METHOD__;

	} // END userpics() */



} // END Class mod_images

?>