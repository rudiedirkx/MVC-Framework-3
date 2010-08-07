<?php

class Person extends ActiveRecordObject {

	const _TABLE = 'people';
	const _PK = 'id';
	public $_GETTERS = array(
		'fav_movie_obj' => 'getFavMovie',
		'friends' => 'getFriends',
		'admin' => 'getIsAdmin'
	);


	public function getFavMovie() {
		return Movie::finder()->byPK( $this->fav_movie_id );
	}

	public function getFriends() {
		return People::finder()->findMany( 'people.id IN ( SELECT id FROM friends WHERE status = \'accepted\' AND ( person_id = people.id AND friend_id = '.$this->id.' OR person_id = '.$this->id.' AND friend_id = people.id ) )' );
	}

	public function getIsAdmin() {
		return in_array((int)$this->status, array(0, 7));
	}


	public function getQuery( $conditions = '' ) {
		return 'SELECT people.*, m.title AS fav_movie FROM people, movies m WHERE m.id = people.fav_movie_id'.( $conditions ? ' AND ( '.$conditions.' )' : '' );
	}

	static public function finder( $class = __CLASS__ ) {
		return parent::finder( $class );
	}

}


