<?php

class AROClub extends ActiveRecordObject {

	protected static $_table = 'clubs';
	protected static $_columns = array(
		'name',
		'subdomain',
		'domain',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'language' => array( self::HAS_ONE, 'AROLanguage', 'language_id' ),
	);


	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);

	} // END finder() */


	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM clubs WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';

	} // END getQuery() */


} // END Class AROClub

?>