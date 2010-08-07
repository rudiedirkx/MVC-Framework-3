<?php #1.10

// Todo
// - DataCenter to counteract doubles

class AROException extends Exception { }
class DBException extends Exception { }

abstract class ActiveRecordObject {

	const HAS_ONE = 1; // FK in $this, Unique Key in foreign table
	const HAS_MANY = 2; // PK in $this, FK in foreign table
	const MANY_TO_MANY = 3; // 
	const FROM_FUNCTION = 4; // Uses a user defined function to fetch the records and saves it as normal

	/**
	 * The global, default database object
	 */
	protected static $__db;

	/**
	 * Save the database abstraction layer object (just once)
	 * Todo:
	 * - Save one db object per table/ARO child and fetch the default one if none is set
	 */
	final public static function setDbObject( db_generic $db ) {
		self::$__db = $db;

	} // END setDbObject() */


	/**
	 * Whether to track changed columns
	 */
	protected $_trackChanges = false;

	/**
	 * The user-changed columns
	 */
	protected $_changedColumns = array();

	/**
	 * All record columns
	 */
	protected $_values = array();

	/**
	 * The table's name
	 */
	protected static $_table = '_';

	/**
	 * All columns except for the PK
	 */
	protected static $_columns = array(
		'_'
	);

	/**
	 * The name of the PK field
	 */
	protected static $_pk = '_';

	/**
	 * Format:
	 * A => array( B, C, D, E, F )
	 * See method __get for details
	 */
	protected static $_relations = array(
		
	);


	/**
	 * 
	 */
	static public function finder( $class = __CLASS__ ) {
		if ( !class_exists($class, true) ) {
			throw new AROException('Class "'.$class.'" doesn\'t exist');
		}
		$class = strtolower($class);
		static $finders = array();
		if ( empty($finders[$class]) ) {
			$finders[$class] = new $class;
		}
		return $finders[$class];

	} // END finder() */


	/**
	 * Keeps an empty object, or fills it with prefetched data
	 */
	public function __construct( $data = null ) {
		if ( null !== $data && ( is_array($data) || is_object($data) ) ) {
			$this->fill( (array)$data );
		}
		$this->_trackChanges = true;

	} // END __construct() */


	/**
	 * 
	 */
	public function __set( $k, $v ) {
		if ( true === $this->_trackChanges && 0 !== strpos($k, '_') ) {
			$this->_values[$k] = $v;
			$this->_changedColumns[$k] = true;
		}
		else {
			$this->$k = $v;
		}

	} // END __set() */


	/**
	 * If a relation member is requested and the member is unset:
	 * - HAS_ONE
	 *  : array( HAS_ONE, Type, Local FK column name, External PK column name )
	 * - HAS_MANY
	 *  : array( HAS_MANY, Type, External FK column name, Local PK column name )
	 * - MANY_TO_MANY
	 *  : array( MANY_TO_MANY, Type, Local PK column name, array( Relation table name, Relation FK to local, Relation FK to external ), External PK column name )
	 * - FROM_FUNCTION
	 *  : array( FROM_FUNCTION, Type, External FK column name, Local PK column name )
	 */
	public function __get( $k ) {
		if ( 0 === strpos($k, '_') ) {
			return isset($this->$k) ? $this->$k : null;
		}
		if ( !isset($this->_values[$k]) ) {
			$relations = $this->getStaticChildValue('relations');
			if ( !isset($relations[$k]) ) {
				return null;
			}
			$rel = $relations[$k];
			if ( self::FROM_FUNCTION !== $rel[0] ) {
				$finder = self::finder($rel[1]);
				$key1 = $rel[2];
			}
			$key2 = empty($rel[3]) ? 'id' : $rel[3];
			if ( self::HAS_ONE === $rel[0] ) {
				$field = $finder->getStaticChildValue('table').'.'.$key2;
				$this->$k = null === $this->$key1 ? new $rel[1] : $finder->findOne( $field.' = ?', $this->$key1 );
			}
			else if ( self::HAS_MANY === $rel[0] ) {
				$field = $finder->getStaticChildValue('table').'.'.$key1;
				$this->$k = $finder->findMany( $field.' = ?'.( !empty($rel[4]) ? ' AND '.$rel[4] : '' ), $this->$key2 );
			}
			else if ( self::MANY_TO_MANY === $rel[0] ) {
				$field = $finder->getStaticChildValue('table').'.'.$rel[4];
				$this->$k = $finder->findMany( $field.' IN ( SELECT '.$rel[3][2].' FROM '.$rel[3][0].' WHERE '.$rel[3][1].' = '.$this->$key1.' )' );
			}
			else if ( self::FROM_FUNCTION === $rel[0] && is_callable(array($this, $rel[1])) ) {
				$this->$k = call_user_func(array($this, $rel[1]));
			}
		}
		return $this->_values[$k];

	} // END __get() */


	/**
	 * Returns the query for this ARO, including the variable conditions
	 */
	public function getQuery( $conditions ) {
		return '';

	} // END getQuery() */


	/**
	 * Retrieve the table's db object
	 */
	public function getDbObject() {
		return self::$__db;

	} // END getDbObject() */


	/**
	 * Nasty function to handle static child members
	 */
	public function getStaticChildValue( $key ) {
		static $values = array();
		if ( empty($values[$key]) ) {
			$class = get_class($this);
			eval('$v = '.$class.'::$_'.$key.';');
			$values[$key] = $v;
		}
		return $values[$key];

	} // END getStaticChildValue() */


	/**
	 * Returns all records it finds
	 */
	public function fetch( $clause, $class = 'SimpleArrayObject' ) {
		$szSqlQuery = $this->getQuery($clause);
		return $this->getDbObject()->fetch($szSqlQuery, $class);

	} // END fetch() */


	/**
	 * Replaces question marks by given values
	 */
	public function replaceMarks( $args ) {
		$str = array_shift($args);
		$p = 0;
		while ( is_int($q=strpos($str, '?', $p)) && 0 < count($args) ) {
			$str = substr_replace($str, $this->getDbObject()->escapeAndQuote(array_shift($args)), $q, 1);
		}
		return $str;

	} // END replaceMarks() */


	/**
	 * Counts records by using count_rows(), not count()
	 */
	public function count( $clause ) {
		if ( 1 < func_num_args() ) {
			$clause = $this->replaceMarks(func_get_args());
		}
		return $this->getDbObject()->count_rows($this->getQuery($clause));

	} // END count() */


	/**
	 * Must return one record
	 */
	public function byPk( $value, $extra = null ) {
		$args = func_get_args();
		$value = array_shift($args);
		if ( null !== $extra && 1 < func_num_args() ) {
			$extra = $this->replaceMarks($args);
		}
		$field = $this->getStaticChildValue('table').'.'.$this->getStaticChildValue('pk');
		$query = $field.' = ?' . ( $extra ? ' AND '.$extra : '' );
		return $this->findOne( $query, $value );

	} // END byPk() */


	/**
	 * Returns 0 or 1 record
	 */
	public function findFirst( $query ) {
		$args = func_get_args();
		$args[0] .= ' LIMIT 1';
		$r = call_user_func_array( array($this, 'findMany'), $args );
		if ( 0 < count($r) ) {
			return $r[0];
		}
		return false;

	} // END findFirst() */


	/**
	 * Must return one record
	 */
	public function findOne( $query ) {
		$args = func_get_args();
		$args[0] .= ' LIMIT 2';
		$r = call_user_func_array( array($this, 'findMany'), $args );
		if ( 1 !== count($r) ) {
			throw new AroException( 'Not exactly one record found', count($r) );
		}
		return $r[0];

	} // END findOne() */


	/**
	 * Returns 0 or more records
	 */
	public function findMany( $query ) {
		if ( 1 < func_num_args() ) {
			$query = $this->replaceMarks(func_get_args());
		}
		$class = get_class($this);
		$r = $this->fetch($query, $class);
		if ( false === $r ) {
			throw new DbException( $this->getDbObject()->error, $this->getDbObject()->errno );
		}
		return $r;

	} // END findMany() */


	/**
	 * 
	 */
	public function save() {
		$pk = $this->getStaticChildValue('pk');
		if ( null === $this->$pk ) {
			return $this->saveAsNew();
		}
		$arrUpdate = array();
		foreach ( $this->getStaticChildValue('columns') AS $k ) {
			if ( isset($this->_changedColumns[$k]) ) {
				$arrUpdate[$k] = $this->$k;
			}
		}
		unset($arrUpdate[$pk]);
		return 0 < $arrUpdate ? $this->getDbObject()->update( $this->getStaticChildValue('table'), $arrUpdate, $pk.' = '.$this->getDbObject()->escapeAndQuote($this->_values[$pk]) ) : false;

	} // END save() */


	/**
	 * 
	 */
	public function saveAsNew() {
		$pk = $this->getStaticChildValue('pk');
		$arrInsert = array();
		foreach ( $this->getStaticChildValue('columns') AS $k ) {
			$arrInsert[$k] = $this->_values[$k];
		}
		unset($arrInsert[$pk]);
		if ( !$this->getDbObject()->insert($this->getStaticChildValue('table'), $arrInsert) ) {
			return false;
		}
		return $this->getDbObject()->insert_id();

	} // END saveAsNew() */


	/**
	 * 
	 */
	public function delete() {
		$pk = $this->getStaticChildValue('pk');
		$r = $this->getDbObject()->delete( $this->getStaticChildValue('table'), "".$pk." = '".$this->getDbObject()->escape($this->_values[$pk])."'" );
		unset($this);
		return $r;

	} // END delete() */


	/**
	 * Copies and/or replaces data from $data into $this
	 */
	public function fill( $data ) {
		foreach ( $data AS $k => $v ) {
			$this->_values[$k] = $v;
		}
		$this->_trackChanges = true;

	} // END fill() */


	/**
	 * Returns this object in PHP Array format (this[x]), instead of PHP Object (this->x)
	 */
	public function asArray() {
		return (array)$this->_values;

	} // END asArray() */


} // END Class ActiveRecordObject


