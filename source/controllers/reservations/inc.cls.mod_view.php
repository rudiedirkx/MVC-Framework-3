<?php

class mod_view extends __topmodule {

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
echo '<pre>';

		$arrMembersInReservations = $GLOBALS['db']->select('members_in_reservations', '1 ORDER BY RAND() LIMIT 20');
print_r($arrMembersInReservations);

		$arrReservations = aroreservation::finder()->findMany('1 ORDER BY RAND() LIMIT 20');
		foreach ( $arrReservations AS $r ) {
//			$r->players;
		}
print_r($arrReservations);

	} // END overview() */



} // END Class mod_view

?>