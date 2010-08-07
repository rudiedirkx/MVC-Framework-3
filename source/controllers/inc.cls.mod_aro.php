<?php

class mod_aro extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'test1',
		'/test/1/'				=> 'test1',
		'/test/2/'				=> 'test2',
		'/test/3/'				=> 'test2',
		'/test/4/'				=> 'test2',
		'/test/5/'				=> 'test2',
		'/test/6/'				=> 'test2',
		'/test/7/'				=> 'test2',
		'/test/8/'				=> 'test2',
	);


	/**
	 * 
	 */
	protected function test1()
	{
echo '<pre>';
		$res = aroreservation::finder()->bypk(62265);
		$res->resource_id = 'a';
		$res->slots = 3;
		var_dump($res->save());
echo $res->getDBObject()->error;

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
	protected function test4()
	{
		

	} // END test4() */


	/**
	 * 
	 */
	protected function test5()
	{
		

	} // END test5() */


	/**
	 * 
	 */
	protected function test6()
	{
		

	} // END test6() */


	/**
	 * 
	 */
	protected function test7()
	{
		

	} // END test7() */


	/**
	 * 
	 */
	protected function test8()
	{
		

	} // END test8() */



} // END Class mod_aro

?>