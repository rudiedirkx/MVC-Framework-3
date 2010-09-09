<?php

class DBException extends Exception { }
class AROException extends DBException { }

abstract class ActiveRecordObject {

	const GETTER_ONE		= 1;
	const GETTER_MANY		= 2;
	const GETTER_FUNCTION	= 3;
	const GETTER_FIRST		= 4;

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



	// Semi-static functions
	public function insert( $data ) {
		if ( $this->getDbObject()->insert( $this->getTableName(), $data ) ) {
			return (int)$this->getDbObject()->insert_id();
		}
		return false;
	}

	public function replace( $data ) {
		return $this->getDbObject()->replace( $this->getTableName(), $data );
	}

	public function updateMany( $data, $conditions ) {
		return $this->getDbObject()->update( $this->getTableName(), $data, $conditions );
	}

	public function deleteMany( $conditions ) { // there might be a LIMIT and ORDER BY in $conditions (and that's fine)
		return $this->getDbObject()->delete( $this->getTableName(), $conditions );
	}


	// object methods
	protected function __construct( $data = null ) {
		if ( null !== $data ) {
			$this->fill( $data );
		}
	}

	public function getQuery( $conditions = '' ) {
		return 'SELECT * FROM '.$this->getTableName().( $conditions ? ' WHERE '.$conditions : '' ).';';
	}

	/**
	 * Returns all records it finds
	 */
	public function fetch( $conditions ) {
		$szSqlQuery = $this->getQuery($conditions);
		return $this->byQuery($szSqlQuery);

	} // END fetch() */

	/**
	 * 
	 */
	public function byQuery( $f_szSqlQuery ) {
		$class = get_class($this);
		$r = $this->getDbObject()->fetch($f_szSqlQuery, $class);
		if ( false === $r ) {
			throw new DbException( $this->getDbObject()->error, $this->getDbObject()->errno );
		}
		return $r;

	} // END byQuery() */

	public function findOne( $conditions ) {
		$r = $this->fetch( $conditions.' LIMIT 2' );
		if ( !$r || 1 != count($r) ) {
			throw new AROException('ARO: Not exactly one record found');
		}
		return $r[0];
	}

	public function findMany( $conditions = '' ) {
		return $this->fetch( $conditions );
	}

	public function byPK( $pk, $more = '' ) {
		return $this->findOne( $this->getTableName().'.'.$this->getPKName().' = '.$this->getDbObject()->escapeAndQuote($pk).( $more ? ' AND ( '.$more.' )' : '' ) );
	}

	public function findFirst( $conditions ) {
		$r = $this->fetch( $conditions.' LIMIT 1' );
		if ( 1 > count($r) ) {
			return false;
		}
		return $r[0];
	}


	// Object properties
	public static $_GETTERS = array(
//		'name' => array( Integer self::GETTER_TYPE, Boolean Cache?, String ResultClass/Function [, String InternalField, String ExternalField ] )
	);


	// Object methods
	final public function __get( $key ) {
		return property_exists( $this, $key ) ? $this->$key : $this->_getter( $key );
	}

	/** 
	 * The following method has not been tested AT ALL.
	 * So far, four types are available (see top of class).
	 */
	final public function _getter( $key ) {
		$gs = $this::$_GETTERS;
		if ( isset($gs[$key]) && 3 <= count($gs[$key]) ) {
			$g = $gs[$key];
			$cache = $g[1];
			$cf = $g[2];
			switch ( $g[0] ) {
				case self::GETTER_ONE:
				case self::GETTER_MANY:
				case self::GETTER_FIRST:
					if ( 5 == count($g) ) {
						$finder = call_user_func(array($cf, 'finder'));
						$fn = $g[2] == self::GETTER_ONE ? 'findOne' : ( $g[0] == self::GETTER_MANY ? 'findMany' : 'findFirst' );
						$r = call_user_func(array($finder, $fn), $g[4].' = '.$this->getDbObject()->escapeAndQuote($this->{$g[3]}));
						if ( $cache ) {
							$this->$key = $r;
						}
						return $r;
					}
				break;
				case self::GETTER_FUNCTION:
					if ( is_callable(array($this, $cf)) ) {
						$r = call_user_func(array($this, $cf));
						if ( $cache ) {
							$this->$key = $r;
						}
						return $r;
					}
				break;
			}
		}
		return null;
//		return isset($this->_GETTERS[$key]) && is_callable(array($this, $this->_GETTERS[$key])) ? call_user_func(array($this, $this->_GETTERS[$key])) : null;
	}

	protected function delete() {
		return $this->getDbObject()->delete( $this->getTableName(), $this->getPKName().' = '.$this->getDbObject()->escapeAndQuote($this->getPKValue()).' LIMIT 1' );
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
		return $this->$pkName;
	}

}


