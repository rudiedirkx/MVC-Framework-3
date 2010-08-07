<?php

class AROException extends Exception { }

abstract class ActiveRecordObject {

	// Finder
	static public function finder( $class ) {
		$class = strtolower($class);
		if ( !isset(self::$_finders[$class]) ) {
			self::$_finders[$class] = new $class;
		}
		return self::$_finders[$class];
	}

	final static public function setDbObject( $db ) {
		self::$_db = $db;
	}


	// Static properties
	static public $_finders = array(); // for all extensions
	static public $_db; // for all extensions

#	const _TABLE; // per extension 'type'		# cannot be predefined because can only be assigned once
#	const _PK; // per extension 'type'			# cannot be predefined because can only be assigned once



	// Semi-static
	public function insert( $data ) {
		$this->getDbObject()->insett( $this->getTableName(), $data );
	}

	public function replace( $data ) {
		$this->getDbObject()->replace( $this->getTableName(), $data );
	}

	public function updateMany( $data, $conditions ) {
		$this->getDbObject()->update( $this->getTableName(), $data, $conditions );
	}

	public function deleteMany( $conditions ) { // there might be a LIMIT and ORDER BY in $conditions (as in: that's fine)
		$this->getDbObject()->delete( $this->getTableName(), $conditions );
	}


	protected function __construct( $data = null ) {
		if ( null !== $data ) {
			$this->fill( $data );
		}
	}

	public function getQuery( $conditions = '' ) {
		return 'SELECT * FROM '.$this->getTableName().( $conditions ? ' WHERE ( '.$conditions.' )' : '' ).';';
	}

	public function findOne( $conditions ) {
		$r = $this->getDbObject()->fetch( $this->getQuery($conditions).' LIMIT 2' );
		if ( !$r || 1 != count($r) ) {
			throw new AROException('ARO: Not exactly one record found');
		}
		return $r[0];
	}

	public function findMany( $conditions = '' ) {
		return $this->getDbObject()->fetch( $this->getQuery($conditions) );
	}

	public function byPK( $pk, $more = '' ) {
		return $this->findOne( $this->getTableName().'.'.$this->getPKName().' = '.$this->getDbObject()->escapeAndQuote($pk).( $more ? ' AND ( '.$more.' )' : '' ) );
	}

	public function get( $pk, $more = '' ) { // alias for ->byPK
		return $this->byPK( $pk, $more );
	}

	public function findFirst( $conditions = '' ) {
		$r = $this->getDbObject()->fetch( $this->getQuery(( $conditions ? $conditions : '1' )).' LIMIT 1' );
		if ( 0 == count($r) ) {
			return false;
		}
		return $r[0];
	}


	// Object methods
	final public function __get( $key ) {
		return property_exists( $this, $key ) ? $this->$key : $this->_getter( $key );
	}

	final public function _getter( $key ) {
		return isset($this->_GETTERS[$key]) && is_callable(array($this, $this->_GETTERS[$key])) ? call_user_func(array($this, $this->_GETTERS[$key])) : null;
	}

	protected function delete() {
		return $this->getDbObject()->delete( $this->getTableName(), $this->getPKName().' = '.$this->getPKValue().' LIMIT 1' );
	}

	protected function fill( $data, $init = true ) {
		foreach ( $data AS $k => $v ) {
			$this->$k = $v;
		}
		if ( $init ) {
			$this->init();
		}
	}

	protected function init() { } // dummy untill used by ancestor

	public function getDbObject() {
		return self::$_db;
	}

	public function getTableName() {
		$class = get_class($this);
		return constant( $class.'::_TABLE' );
	}

	public function getPKName() {
		$class = get_class($this);
		return constant( $class.'::_PK' );
	}

	public function getPKValue() {
		$pkName = $this->getPKName();
		return $this->getDbObject()->escapeAndQuote( $this->$pkName );
	}

}


