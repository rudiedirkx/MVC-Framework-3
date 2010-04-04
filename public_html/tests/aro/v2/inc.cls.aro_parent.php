<?php

abstract class ARO_Parent {

	// Finder
	static public $_finders = array();
	static public function finder( $class, db_generic $db = null ) {
		$class = strtolower($class);
		if ( !isset(self::$_finders[$class]) ) {
			self::$_finders[$class] = new $class(array());
		}
		return self::$_finders[$class];
	}

	static public setDbObject( db_generic $db ) {
		self::$_db = $db;
		return $db;
	}


	// Static properties
	static public $_db;
	static public $_table;
	static public $_pk;

	// Object properties
	public $_db;


	// Semi-static
	public function insert( $data );

	public function replace( $data );

	public function update( $data, $conditions );

	public function delete( $conditions );


	public function __construct( $data ) {
		if ( is_object($data) && is_a($data, 'db_generic') ) {
			$this->_db = $data;
		}
		else if ( is_array($data) || is_object($data) ) {
			$this->fill($data);
		}
		else {
			$this->fill($this->getDbObject()->select_first());
		}
	}

	public function findOne( $conditions );

	public function findMany( $conditions );

	public function byPK( $pk, $more_conditions );

	public function findFirst( $conditions ); 


	// Object methods
	public function fill( $data ) {
		foreach ( $data AS $k => $v ) {
			$this->$k = $v;
		}
	}

	public function init() { // executed after fill() (internally)
		return $this;
	}

	public function getDbObject() {
		return $this->db ? $this->db : self::$_db;
	}


}


