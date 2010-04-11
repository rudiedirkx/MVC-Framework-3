<?php

require_once('inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'tests');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

echo '<pre>';

$arrFriends = $db->select('friends');
var_dump($arrFriends);

$arrUsers = $db->select_fields('users', 'id, concat(username,\':\',password)');
var_dump($arrUsers);

$arrUsers = $db->select_by_field('users', 'id');
var_dump($arrUsers);

?>