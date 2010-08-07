<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'baanreserveren');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';


$reservation = AROReservation::finder()->byPk(983, 'resource_id = 32');
echo '<!--'."\n";
print_r($reservation);
echo '-->'."\n\n";

echo (string)$reservation->start_time.' - '.(string)$reservation->getEndTime()."\n\n";

echo 'resource ID : '.$reservation->resource_id.' ('.$reservation->resource->name.")\n\n";
$reservation->resource->sport->club;

print_r( $reservation );


echo "\n\n<hr />\n\n";

$resource = AROResource::finder()->byPk(34);
$child2 = $resource->sport->club;
print_r( $resource );

?>