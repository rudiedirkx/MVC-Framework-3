<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'baanreserveren');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';

$sport = AROClubSport::finder()->byPk(9);
print_r($sport);

print_r($sport->resources);

print_r($sport->resources[4]->sport);

?>