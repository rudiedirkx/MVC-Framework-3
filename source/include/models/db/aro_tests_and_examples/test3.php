<?php

require_once('../../../inc.cls.db_mysqli.php');
$db = new db_mysqli('localhost', 'usager', 'usager', 'baanreserveren');

function __autoload($class) {
	require_once(dirname(__FILE__).'/inc.cls.'.strtolower($class).'.php');
}

ActiveRecordObject::setDbObject($db);

echo '<pre>';

$user = AROMember::finder()->byPk(12);
//$user = AROMember::finder()->findOne('club_id IS NULL AND username = \'007\'');

print_r($user);

$user->use_captcha = (bool)$user->use_captcha ? false : true;
var_dump($user->save());

echo 'user sport = ['.$user->club_sport_id.':'.$user->sport->name.']'."\n";
echo 'user language = ['.$user->language_id.':'.$user->language->name.']'."\n\n";

//$user->birthdate = '2008-02-14';
//var_dump($user->save());

?>