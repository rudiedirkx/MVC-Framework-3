<?php

if ( $_SERVER['argc'] < 5 ) {
	exit('More parameters required.'."\n");
}
array_shift($_SERVER['argv']);

list($classname, $tablename, $pkname, $columns, ) = $_SERVER['argv'];
$columns = max(1, (int)$columns);

$szFilename = 'inc.cls.'.strtolower($classname).'.php';
if ( file_exists($szFilename) ) {
	exit('File <'.$szFilename.'> already exists.'."\n");
}

$szClassFileContents = '<?php

class '.$classname.' extends ActiveRecordObject {

	protected static $_table = \''.$tablename.'\';
	protected static $_columns = array(
'.str_repeat("\t\t'',\n", $columns).'
	);
	protected static $_pk = \''.$pkname.'\';
	protected static $_relations = array(
		
	);


	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);

	} // END finder() */


	public function getQuery( $clause ) {
		$szQuery = \'SELECT * FROM '.$tablename.' WHERE 1\';
		if ( $clause ) {
			$szQuery .= \' AND \'.$clause;
		}
		return $szQuery.\';\';

	} // END getQuery() */


} // END Class '.$classname.'

?'.'>';

if ( $fp = @fopen($szFilename, 'w') ) {
	fwrite($fp, $szClassFileContents);
	fclose($fp);
	exit('File and class have been created: '.$szFilename.'.'."\n");
}

exit('File could not be created [unknown].'."\n");

?>