<?php

class mod_aro extends __topmodule {

	protected $m_arrHooks = array(
		'/'						=> 'people',
		'/people/'				=> 'people',
		'/person/#/'			=> 'person',

		'/-source'				=> 'phpSource',
		'/-model/*'				=> 'modelSource',
	);



	function __start() {
		echo '<style>code { display:block; white-space:nowrap; }</style>';
		echo '<p><a href="/aro/-source">You can view the source of this controller here.</a></p>'."\n";
		echo '<p>View the model sources of: <a href="/aro/-model/person">Person</a>, <a href="/aro/-model/movie">Movie</a></p>'."\n";
		echo '<pre>';
	}
	function phpSource() {
		echo '</pre>'."\n";
		highlight_file(__FILE__);
	}
	function modelSource( $model ) {
		echo '</pre>'."\n";
		highlight_file(PROJECT_MODELS.'/inc.cls.'.$model.'.php');
	}



	/**
	 * 
	 */
	protected function people()
	{
		$people = person::finder()->findMany('1 ORDER BY id ASC');
		$this->peoples = $people;
		$self = $this;

		// Anonymous functions are introduced in PHP 5.3, so the following might produce a Parse error:
		array_map(function(&$p) {
			$p->name = '<a href="/aro/person/'.$p->id.'/">'.$p->name.'</a>';
		}, $people, array($this));

		print_r($people);

	} // END people() */


	/**
	 * 
	 */
	protected function person( $person )
	{
		$person = person::finder()->bypk($person);

		echo 'This is a (standard) person:'."\n";
		print_r($person);

		echo "\n";

		echo 'This is its favorite movie:'."\n";
		print_r($person->fav_movie_obj);

		echo "\n";

		echo 'The person itself hasn\'t changed:'."\n";
		print_r($person);

		echo "\n";

		echo 'But now I set &cache to true (I can but should never do that inline) and request '.$person->name.'\'s fav movie again:'."\n";
		$person::$_GETTERS['fav_movie_obj'][1] = true; // This is uncool and you shouldn't do it :)
		print_r($person->fav_movie_obj);

		echo "\n";

		echo 'And now... '.$person->name.' has changed:'."\n";
		print_r($person);

	} // END person() */


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


