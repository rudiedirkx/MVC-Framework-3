<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'baanreserveren');

require_once('../../../../websites/baanreserveren/source/include/inc.cls.common.php');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';

$fStart = microtime(true);

$reservation = AROReservation::finder()->byPk(1078);
$reservation->players;
//$reservation->players[0]->member_id = 58;
//var_dump($reservation->players[0]->saveAsNew());
print_r($reservation);


echo "\n".(microtime(true)-$fStart);

?>