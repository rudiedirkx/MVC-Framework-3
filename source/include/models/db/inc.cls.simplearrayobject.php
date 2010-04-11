<?php

class SimpleArrayObject {

	public function set($k, $v) {
		$this->$k = $v;
	}

	public function __construct($data) {
		foreach ( $data AS $k => $v ) {
			$this->$k = $v;
		}
	}

} // END Class SimpleArrayObject

?>