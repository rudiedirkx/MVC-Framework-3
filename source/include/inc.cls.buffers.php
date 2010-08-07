<?php

class Buffers
{

	/**
	 * R e t r i e v e   a n d   s a v e   s u n r i s e   t a b l e
	 */
	static public function setSunriseTables( $f_iTableId, $f_iYear, $f_iMonth ) {
		$arrTableInfo = db_select('sunrise_table_cities', 'id = '.(int)$f_iTableId);
		if ( !$arrTableInfo ) {
			return;
		}
		$arrTableInfo = $arrTableInfo[0];
		$szUrl = 'http://www.sunrisesunset.com/calendar.asp?comb_city_info=' . str_replace(' ', '%20', $arrTableInfo['comb_city_info']) . ';1&month='.(int)$f_iMonth.'&year='.(int)$f_iYear.'&time_type=1';
		$szContents = file_get_contents($szUrl);
		preg_match_all('/top>(\d\d?)<.+?Sunrise: (\d\d?\:\d\d).+?Sunset: (\d\d?\:\d\d)[^\d]/i', $szContents, $parrMatches);
		$arrSunrises = array();
		foreach ( $parrMatches[1] AS $k => $d ) {
			$arrSunrises[(int)$d] = array( $parrMatches[2][$k] , $parrMatches[3][$k] );
		}

		$szFile = PROJECT_RESOURCES.'/sunrises/'.$f_iTableId.'_'.$f_iYear.str_pad((string)(int)$f_iMonth, 2, '0', STR_PAD_LEFT).'.txt';
		fwrite(($h=fopen($szFile, 'w')), serialize($arrSunrises));
		fclose($h);

	} // END setSunriseTables() */


	/**
	 * G e t   s u n r i s e   t a b l e
	 */
	static public function getSunriseTable( $f_iTableId, $f_iYear, $f_iMonth ) {
		static $tables = array();
		if ( !isset($tables[(int)$f_iTableId][(int)$f_iYear][(int)$f_iMonth]) ) {
			$szFile = PROJECT_RESOURCES.'/sunrises/'.$f_iTableId.'_'.$f_iYear.str_pad((string)(int)$f_iMonth, 2, '0', STR_PAD_LEFT).'.txt';
			if ( !file_exists($szFile) ) {
				self::setSunriseTables( $f_iTableId, $f_iYear, $f_iMonth );
				if ( !file_exists($szFile) ) {
					$tables[(int)$f_iTableId][(int)$f_iYear][(int)$f_iMonth] = array();
					return array();
				}
			}
			$tables[(int)$f_iTableId][(int)$f_iYear][(int)$f_iMonth] = unserialize(file_get_contents($szFile));
		}
		return $tables[(int)$f_iTableId][(int)$f_iYear][(int)$f_iMonth];

	} // END getSunriseTable() */


	/**
	 * G e t   s u n r i s e   t i m e
	 */
	static public function getSunriseTime( $f_iTableId, $f_szDate ) {
		if ( null === $f_iTableId || !common::checkdate($f_szDate) ) {
			return '00:00';
		}
		list($year, $month, $day) = array_map('intval', explode('-', $f_szDate));
		$arrTable = self::getSunriseTable( $f_iTableId, $year, $month );
		return isset($arrTable[$day]) ? $arrTable[$day] : '00:00';

	} // END getSunriseTime() */






	/**
	 * A c c e s s   c o m b i n a t i o n s   -   G E T
	 */
	static public function getAccessZones()
	{
		$szFile = PROJECT_RESOURCES.'/access/zones.txt';
		if ( !file_exists($szFile) ) {
			self::setAccessZones();
			if ( !file_exists($szFile) ) {
				return array();
			}
		}
		return unserialize(file_get_contents($szFile));

	} // END getAccessCombinations() */


	/**
	 * A c c e s s   c o m b i n a t i o n s   -   S E T
	 */
	static public function setAccessZones()
	{
		$arrAC = array_map('intval', db_select_fields('access_zones', 'UPPER(zone),id'));
		$szFile = PROJECT_RESOURCES.'/access/zones.txt';
		fwrite(($h=fopen($szFile, 'w')), serialize($arrAC));
		fclose($h);

	} // END setAccessCombinations() */






	/**
	 * L a n g u a g e s   -   G E T
	 */
	static public function getLanguage( $f_iLanguageId )
	{
		$szFile = PROJECT_RESOURCES.'/languages/'.$f_iLanguageId.'.txt';
		if ( !file_exists($szFile) ) {
			self::setLanguages($f_iLanguageId);
		}
		if ( !file_exists($szFile) ) {
			return array();
		}
		return unserialize(file_get_contents($szFile));

	} // END getLanguage() */


	/**
	 * L a n g u a g e s   -   S E T
	 */
	static public function setLanguages( $f_iLanguageId = null )
	{
		$q = db_query('SELECT `key` FROM language_keys;');
		if ( $q ) {
			while ( $r = mysql_fetch_assoc($q) ) {
				$a[$r['key']] = '';
			}
		}

		$arrLanguages = !$f_iLanguageId ? db_select('languages') : db_select('languages', 'id = '.(int)$f_iLanguageId);
		foreach ( $arrLanguages AS $arrLanguage )
		{
			$szFile = PROJECT_RESOURCES.'/languages/'.$arrLanguage['id'].'.txt';

			$r = $a;
			if ( null !== $arrLanguage['parent_language_id'] ) {
				$szQuery = '
				SELECT
					UPPER(language_keys.key) AS k,
					language_translations.value AS v
				FROM
					language_translations,
					language_keys,
					languages
				WHERE
					languages.id = '.(int)$arrLanguage['parent_language_id'].' AND
					language_translations.language_id = languages.id AND
					language_keys.id = language_translations.language_key_id;';
				$r = array_merge($r, db_fetch_fields($szQuery));
			}

			$szQuery = '
			SELECT
				UPPER(language_keys.key) AS k,
				language_translations.value AS v
			FROM
				language_translations,
				language_keys,
				languages
			WHERE
				languages.id = '.(int)$arrLanguage['id'].' AND
				language_translations.language_id = languages.id AND
				language_keys.id = language_translations.language_key_id;';

			$r = array_merge($r, db_fetch_fields($szQuery));
			fwrite(($h=fopen($szFile, 'w')), serialize($r));
			fclose($h);
		}

	} // END setLanguages() */


	



} // END Class Buffers

?>