<?php #2.2

abstract class db_generic {

	public $qmark = '???';
	public function replaceQMarks( $str, $args ) {
		if ( !$args ) return $str;
		$offset = 0;
		foreach ( $args AS $arg ) {
			$pos = strpos($str, $this->qmark, $offset);
			if ( false === $pos ) break;
			$arg = $this->escapeAndQuote($arg);
			$str = substr_replace($str, $arg, $pos, strlen($this->qmark));
			$offset = $pos + strlen($arg);
		}
		return $str;
	}
	function prepAndReplaceQMarks( $str, $args, $offset ) {
		if ( $offset < count($args) ) {
			$args = array_slice($args, $offset);
			$str = $this->replaceQMarks( $str, $args );
		}
		return $str;
	}

	protected $dbCon;
	public $error = '';
	public $errno = 0;
	public $num_queries = 0;

	public function __construct( $f_szHost, $f_szUser = null, $f_szPass = null, $f_szDb = null ) {}
	public function saveError() {}
	public function connected() {
		return false;
	}
	public function insert_id() {}
	public function affected_rows() {}
	public function query( $f_szSqlQuery ) {}
	public function fetch($f_szSqlQuery) {}
	public function fetch_fields($f_szSqlQuery) {}
	public function select_one($tbl, $field, $where = '') {}
	public function count_rows($f_szSqlQuery) {}
	public function select_by_field($tbl, $field, $where = '') {}

	public function escape( $v ) {
		return addslashes($v);
	}

	public function escapeAndQuote($v) {
		if ( is_bool($v) ) {
			$v = (int)$v;
		}
		else if ( $v === null ) {
			return 'NULL';
		}
		return "'".$this->escape((string)$v)."'";
	}


	public function select( $table, $where = '1' ) {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 2 );
		$sql = 'SELECT * FROM '.$table.' WHERE '.$where.';';
		return $this->fetch($sql);
	}

	public function select_first( $table, $where = '1' ) {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 2 );
		$sql = 'SELECT * FROM '.$table.' WHERE '.$where.';';
		return $this->fetch($sql, null, true); // sql, ?class, ?first
	}

	public function max($tbl, $field, $where = '1') {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 3 );
		return $this->select_one($tbl, 'MAX('.$field.')', $where);
	}

	public function min($tbl, $field, $where = '1') {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 3 );
		return $this->select_one($tbl, 'MIN('.$field.')', $where);
	}

	public function count($tbl, $where = '1') {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 2 );
		return $this->select_one($tbl, 'COUNT(1)', $where);
	}

	public function select_fields($tbl, $fields, $where = '1') {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 3 );
		$sql = 'SELECT '.$fields.' FROM '.$tbl.' WHERE '.$where.';';
		return $this->fetch_fields($sql);
	}

	public function replace_into($tbl, $values) {
		foreach ( $values AS $k => $v ) {
			$values[$k] = $this->escapeAndQuote($v);
		}
		$sql = 'REPLACE INTO '.$tbl.' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
		return $this->query($sql);
	}

	public function insert($tbl, $values) {
		foreach ( $values AS $k => $v ) {
			$values[$k] = $this->escapeAndQuote($v);
		}
		$sql = 'INSERT INTO '.$tbl.' ('.implode(', ', array_keys($values)).') VALUES ('.implode(", ", $values).');';
		return $this->query($sql);
	}

	public function update($tbl, $update, $where = '1') {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 3 );
		if ( !is_string($update) ) {
			$u = '';
			foreach ( (array)$update AS $k => $v ) {
				$u .= ',' . $k . '=' . $this->escapeAndQuote($v);
			}
			$update = substr($u, 1);
		}
		$sql = 'UPDATE '.$tbl.' SET '.$update.( $where ? ' WHERE '.$where : '' ).';';
		return $this->query($sql);
	}

	public function delete($tbl, $where) {
		$where = $this->prepAndReplaceQMarks( $where, func_get_args(), 2 );
		$sql = 'DELETE FROM '.$tbl.' WHERE '.$where.';';
		return $this->query($sql);
	}


} // END Class db_generic


