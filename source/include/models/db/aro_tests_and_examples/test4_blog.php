<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'tests');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';


$kat = AROUser::finder()->findOne('username = ?', 'kat');
$kat->bff; // jaap
echo 'User katrien: ';
print_r($kat);

$jaap = AROUser::finder()->byPk(1, 'id <> ? AND username <> ?', '8', 'larel');
$jaap->bff; // no record found, so empty record stored
echo "\n".'User jaap: ';
print_r($jaap);

$posts = $jaap->posts;
echo "\n".'Jaap\'s posts: ';
print_r($posts);
// OR
$posts = AROPost::finder()->findMany('user_id = ?', $jaap->id);
echo "\n".'Nogmaals: ';
print_r($posts);

echo "\n";

echo "\n".'Post [3] met childs: ';
$post3 = AROPost::finder()->byPK(3);
$post3->creator->posts;
print_r($post3);

//var_dump($post3->saveAsNew());

?>