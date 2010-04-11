<?php

class AROClubSport extends ActiveRecordObject {

	protected static $_table = 'club_sports';
	protected static $_columns = array(
		'club_id',
		'name',
		'max_matrix_height',
		'slotsize',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'club' => array( self::HAS_ONE, 'AROClub', 'club_id' ),
		'resources' => array( self::HAS_MANY, 'AROResource', 'club_sport_id' ),
	);


	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);

	} // END finder() */


	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM club_sports WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';

	} // END getQuery() */


} // END Class AROClubSport

?>