<?php

class __Home extends __TopModule
{

	protected $m_arrHooks = array(
		'/'					=> 'index',
	);



	/**
	 * I n d e x
	 */
	protected function index()
	{
		function pd( $fs ) {
			echo '<ul>';
			foreach ( $fs AS $f ) {
				if ( is_dir($f) || 0 < preg_match('/^inc\.cls\.mod_([0-9a-z_]+)\.php$/', basename($f), $parrMatches) ) {
					$c = explode('/', substr($f, strlen(PROJECT_CONTROLLERS)+1));
					if ( !is_dir($f) ) {
						$c[count($c)-1] = $parrMatches[1];
					}
					echo '<li><a title="'.$f.'" href="'.str_replace('-N', '-'.rand(1, 3545), implode('-', $c)).'">'.basename($f).'</a>';
					if ( is_dir($f) ) {
						echo '<ul>';
						pd(glob($f.'/*'));
						echo '</ul>';
					}
					echo '</li>';
				}
			}
			echo '</ul>';
		}

		pd(glob(PROJECT_CONTROLLERS.'/*'));

	} // END index() */


} // END Class __Home

?>