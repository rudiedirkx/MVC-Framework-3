<?php

class AROUser extends ActiveRecordObject {

	protected static $_table = 'users';
	protected static $_columns = array(
		'username',
	//	'password', # there really is no use to update this, so don't list it. It will be fetched though (see * in getQuery())
		'name',
		'email',
		'bff_user_id',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'bff' => array( self::HAS_ONE, 'AROUser', 'bff_user_id', 'id' ),
		'posts' => array( self::HAS_MANY, 'AROPost', 'user_id', 'id' ),
		'friends' => array( self::MANY_TO_MANY, 'AROUser', 'id', array('friends', 'user_id', 'friend_user_id'), 'id' ),
		'friends2' => array( self::FROM_FUNCTION, 'getFriends' ),
	);

	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);
	}

	public function getFriends() {
		return AROUser::finder()->findMany('id IN (SELECT friend_user_id FROM friends WHERE user_id = ?)', $this->id);
	}

	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM users WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}

	public function updatePassword( $pwd ) {
		return $this->getDbObject()->update(
			$this->getStaticChildValue('table'), # table
			array('password' => md5($this->id.':'.$pwd)), # updates
			$this->getStaticChildValue('pk').' = '.$this->id # WHERE clause
		);
	}


} // END Class AROUser

?>