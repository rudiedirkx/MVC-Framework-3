<?php

class Person extends ActiveRecordObject {

	const _TABLE = 'people';
	const _PK = 'id';
	public static $_GETTERS = array(
		'fav_movie_obj' => array( self::GETTER_FIRST, false, 'Movie', 'fav_movie_id', 'id' ),
		'friends' => array( self::GETTER_FUNCTION, false, 'getFriends' ),
		'underlings' => array( self::GETTER_MANY, false, 'Person', 'supervisor_person_id', 'id' ),
		'admin' => array( self::GETTER_FUNCTION, false, 'getIsAdmin' ),
	);


	public function getFavMovie() {
		return Movie::finder()->byPK( $this->fav_movie_id );
	}

	public function getFriends() {
		return Person::finder()->findMany( 'people.id IN ( SELECT id FROM friends WHERE status = \'accepted\' AND ( person_id = people.id AND friend_id = '.$this->id.' OR person_id = '.$this->id.' AND friend_id = people.id ) )' );
	}

	public function getIsAdmin() {
		return in_array((int)$this->status, array(0, 7));
	}


	public function getQuery( $conditions = '' ) {
		return 'SELECT people.*, m.title AS fav_movie_name FROM people LEFT JOIN movies m ON m.id = people.fav_movie_id'.( $conditions ? ' WHERE '.$conditions : '' );
	}

	static public function finder( $class = __CLASS__ ) {
		return parent::finder( $class );
	}

}


