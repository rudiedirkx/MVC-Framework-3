<?php

class AROLanguage extends ActiveRecordObject {

	protected static $_table = 'languages';
	protected static $_columns = array(
		'name',
	);
	protected static $_pk = 'id';
	protected static $_relations = array();

	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);
	}

	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM languages WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}


} // END Class AROMember

?>