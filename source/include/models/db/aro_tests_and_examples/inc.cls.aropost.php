<?php

class MyDateTime {
	private $utc;
	function __construct($utc) {
		$this->utc = $utc;
	}
	function __tostring() {
		return (string)$this->utc; # __tostring must always return database format
	}
	function text() {
		return $this->date().' '.$this->time();
	}
	function date() {
		return date('Y-m-d', $this->utc);
	}
	function time() {
		return date('H:i:s', $this->utc);
	}
}

class AROPost extends ActiveRecordObject {

	protected static $_table = 'posts';
	protected static $_columns = array(
		'parent_post_id', # FK (NULL)
		'user_id', # FK
		'title',
		'content',
		'created', # int
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'creator' => array( self::HAS_ONE, 'AROUser', 'user_id'/*, 'id'*/ ), # `id` is default
		'replies' => array( self::HAS_MANY, 'AROPost', 'parent_post_id', 'id' ),
	);

	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);
	}

	public function fill($data) {
		parent::fill($data);
		$this->created = new MyDateTime($this->created);
	}

	public function getQuery( $clause ) {
		$szQuery = 'SELECT * FROM posts WHERE 1';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';
	}


} // END Class AROPost

?>