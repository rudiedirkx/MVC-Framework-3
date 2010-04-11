<?php

class AROReservationPlayer extends ActiveRecordObject {

	protected static $_table = 'members_in_reservations';
	protected static $_columns = array(
		'member_id',
		'reservation_id',
		'checked_in_by',
		'costs',
		'paid_to',
		'paid_when',
		'cancellation_costs',
		'strips',
		'external_linked',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'reservation' => array( self::HAS_ONE, 'AROReservation', 'reservation_id' ),
		'cancellations' => array( self::HAS_MANY, 'AROCancellation', 'member_id', 'member_id', 'cancellation_costs > 0 AND paid_to IS NULL' ),
	);

	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);
	}

	public function getQuery( $clause ) {
		$szQuery = 'SELECT m.*, members_in_reservations.* FROM members_in_reservations LEFT JOIN members m ON (members_in_reservations.member_id = m.id) WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}


} // END Class AROReservationPlayer

?>