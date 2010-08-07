<?php

require_once(dirname(__FILE__).'/inc.cls.activerecordobject.php');

class AROCancellation extends ActiveRecordObject {

	protected static $_table = 'cancellations';
	protected static $_columns = array();
	protected static $_pk = '';
	protected static $_relations = array(
		'reservation' => array( self::HAS_ONE, 'AROReservation', 'reservation_id' ),
	);

	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM cancellations WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}


} // END Class AROCancellation

?>