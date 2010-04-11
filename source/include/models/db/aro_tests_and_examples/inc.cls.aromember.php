<?php

class AROMember extends ActiveRecordObject {

	protected static $_table = 'members';
	protected static $_columns = array(
		'club_id',
		'username',
		'is_superuser',
		'firstname',
		'middlename',
		'lastname',
		'email',
		'birthdate',
		'primary_club_sport_id',
		'res_sched_options',
		'misc_order_options',
		'use_captcha',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'language' => array( self::HAS_ONE, 'AROLanguage', 'language_id' ),
		'sport' => array( self::HAS_ONE, 'AROClubSport', 'primary_club_sport_id' ),
		'reservations' => array( self::HAS_MANY, 'AROReservationPlayer', 'member_id' ),
		'mt' => array( self::HAS_ONE, 'AROMemberType', 'member_type_id' ),
	);

	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);
	}

	public function fill($data) {
		parent::fill($data);
		$this->res_sched_options = new SerializedArray($this->res_sched_options);
		$this->misc_order_options = new SerializedArray($this->misc_order_options);
	}

	public function getQuery( $clause ) {
//		$szQuery = 'SELECT mt.*, members.* FROM members LEFT JOIN member_types mt ON (members.member_type_id = mt.id) WHERE 1';
		$szQuery = 'SELECT * FROM members WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}


} // END Class AROMember

?>