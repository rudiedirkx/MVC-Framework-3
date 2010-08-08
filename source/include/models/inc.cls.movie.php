<?php

class Movie extends ActiveRecordObject {

	const _TABLE = 'movies';
	const _PK = 'id';

	static public function finder( $class = __CLASS__ ) {
		return parent::finder( $class );
	}

}


