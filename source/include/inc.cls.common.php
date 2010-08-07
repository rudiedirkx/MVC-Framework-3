<?php

class Common
{

	/**
	 * P r e p a r e   a   r e s e r v a t i o n   f o r   i n s e r t   /   u p d a t e
	 */
	public static function createInitialPlayersArray( $f_arrPlayers ) {
		$arrPlayers = array();
		foreach ( $f_arrPlayers AS $iUserId ) {
			$arrPlayers[] = array('user_id' => $iUserId, 'user' => null, 'mt' => null, 'costs' => 0, 'cancellation' => 0, 'strips' => 0, 'actual_costs' => 0, 'actual_cancellation' => 0, 'actual_strips' => 0);
		}
		return $arrPlayers;

	} // END createInitialPlayersArray() */


	/**
	 * P r e p a r e   a   r e s e r v a t i o n   f o r   i n s e r t   /   u p d a t e
	 */
	public static function prepareReservationPlayers( $f_arrPlayers, $f_arrResource, $f_iUtcTime, $f_iGames, $f_bDoRoundPrices = true, &$f_parrIsPeakTime = array() ) {
		$arrResource = $f_arrResource;

		$iUtcStartTime = $f_iUtcTime;
		$szDate = date('Y-m-d', $f_iUtcTime);
		$szStartTime = date('H:i:00', $f_iUtcTime);

		$iToday = (int)date('w', $f_iUtcTime);
		$bIsWeekend = (0 == $iToday || 6 == $iToday);

		$arrPlayers = $f_arrPlayers;

		$objTime = new Time($szStartTime);
		$arrIsPeakTime = array();
		$p1 = array();
		for ( $i=0; $i<$f_iGames; $i++ ) {
			if ( 0 < $i ) { $objTime->addMinutes($f_arrResource['slotsize'] * $f_arrResource['game_length']); }
			$szTime = $objTime->getTimeAsString();
			$arrIsPeakTime[] = $bIsPeakTime = common::isPeakTime($f_arrResource['id'], $szDate, $szTime);
			foreach ( $arrPlayers AS $k => &$arrPlayer ) {
				if ( null === $arrPlayer['user_id'] || ( !empty($p1) && '1' === $p1['mt']['strips'] && '1' === $p1['pays_strips_for_coplayers'] && 0 < (int)$p1['balance_strips'] ) ) {
					// GUEST || P1 pays for Px
					if ( !empty($p1) && '1' === $p1['mt']['strips'] && 0 < (int)$p1['balance_strips'] ) {
						// P1 pays in strips
						$arrPlayers[0]['strips']++;
						$arrPlayer['actual_strips']++;
#						$p1['balance_strips']--;
					}
					else {
						// P1 pays in money
						$c = round(1 * user::getPrice($f_arrResource['id'], $bIsWeekend, $bIsPeakTime, $szDate, $arrPlayer['user_id']), 2);
						$cc = round(1 * user::getCancelPrice($f_arrResource['id'], $bIsWeekend, $bIsPeakTime, $szDate, $arrPlayer['user_id']), 2);
						$arrPlayer['actual_costs'] += $c;
						$arrPlayers[0]['costs'] += $c;
						$arrPlayer['actual_cancellation'] += $cc;
						$arrPlayers[0]['cancellation'] += $cc;
					}
				}
				else {
					// MEMBER || Px pays for himself
					// Assign member to buffer
					$arrPlayer['user'] = user::get($arrPlayer['user_id']);
					$arrPlayer['mt'] = user::getMembership($arrPlayer['user_id'], $szDate);
					if ( 0 === $k && array() === $p1 && null !== $arrPlayer['user_id'] ) { $p1 = $arrPlayer['user']; $p1['mt'] = $arrPlayer['mt']; }
					// Payment method
					if ( '1' === $arrPlayer['mt']['strips'] && 0 < (int)$arrPlayer['user']['balance_strips'] ) {
						// Player has strips
						$arrPlayer['actual_strips']++;
						$arrPlayer['strips']++;
#						if ( 0 === $k ) {
#							$p1['balance_strips']--;
#						}
					}
					else {
						// Player pays with money
						$c = round(1 * user::getPrice($f_arrResource['id'], $bIsWeekend, $bIsPeakTime, $szDate, $arrPlayer['user_id']), 2);
						$cc = round(1 * user::getCancelPrice($f_arrResource['id'], $bIsWeekend, $bIsPeakTime, $szDate, $arrPlayer['user_id']), 2);
						$arrPlayer['actual_costs'] += $c;
						$arrPlayer['costs'] += $c;
						$arrPlayer['actual_cancellation'] += $cc;
						$arrPlayer['cancellation'] += $cc;
					}
				}
				unset($arrPlayer);
			}
		}

		if ( empty($f_parrIsPeakTime) ) {
			$f_parrIsPeakTime = $arrIsPeakTime;
		}

		$fTotalCancellationCosts = 0;
		foreach ( $arrPlayers AS $p ) {
			$fTotalCancellationCosts += (float)$p['cancellation'];
		}

		foreach ( $arrPlayers AS &$arrPlayer ) {
			if ( 0 < $arrPlayer['strips'] ) {
				$arrPlayer['strips'] /= $f_iGames;
			}
			if ( $f_bDoRoundPrices ) {
				$arrPlayer['costs'] = 0.05 * round( $arrPlayer['costs'] / 0.05 );
				$arrPlayer['cancellation'] = 0.05 * round( $arrPlayer['cancellation'] / 0.05 );
			}
			$arrPlayer['cancellation'] = null !== $arrPlayer['user_id'] ? $fTotalCancellationCosts : 0.0;
			unset($arrPlayer);
		}

		// R U L E S //
		global $g_arrClub;
		$bImmune = defined('USER_ID') ? user::access('IMMUNE_TO_RULES') : false;
		$bFFA = common::freeForAll($szDate);
		$bDoAllRules = !$bImmune && !$bFFA;
		$arrRules = db_select_fields( 'rules_for_clubs c, rules r', 'r.id,r.rule', 'r.id = c.rule_id AND c.club_id = '.(int)$g_arrClub['id'].' AND ( type = \'costs\''.( $bDoAllRules ? ' OR type = \'reservation\'' : '' ).' )' );
		foreach ( $arrRules AS $szRule ) {
			if ( is_string($r=@eval($szRule)) ) {
				return $r;
			}
		}
		// R U L E S //

		/*foreach ( $arrPlayers AS &$p ) {
			unset($p['user'], $p['mt']);
			unset($p);
		}*/

		return $arrPlayers;

	} // prepareReservationPlayers() */


	/**
	 * freeForAll()
	 */
	public static function freeForAll( $f_szDate ) {
		global $g_arrClub;
		if ( empty($g_arrClub['id']) || !($szDate=common::checkDate($f_szDate)) ) { return false; }
		$iUtc = common::mktime($szDate);
		$iToday = date('w', $iUtc);
		if ( date('Y-m-d', ctime()) !== $szDate ) { return false; }
		return ( $szDate === $g_arrClub['free_for_all'] || strstr($g_arrClub['ignore_rules_for_days'], (string)$iToday) || ( null !== $g_arrClub['ignore_rules_after_hours'] && math_time(date('H:i', ctime())) > math_time($g_arrClub['ignore_rules_after_hours']) ) );

	} // END freeForAll() */


	/**
	 * checkTime()
	 * Valid times are: 7:20, 18:0, 12:51, 0:0, 24:0, 4:30pm (->16:30), 12am (24:00), 19pm (19:00), 19:4am (19:40), 03:0:1 (03:00), etc
	 */
	public static function checkTime( $f_szTime, $f_bUpper = true )
	{
		if ( !preg_match('/^[0-9]{1,2}(?:\:[0-9]{1,2})*(?: ?(?:am|pm))?$/i', $f_szTime) ) {
			return false;
		}
		$x = explode(':', $f_szTime.':0');
		if ( ('pm' == strtolower(substr($f_szTime, -2)) && 12 > (int)$x[0]) || ('am' == strtolower(substr($f_szTime, -2)) && 12 == (int)$x[0]) ) {
			$x[0] = (int)$x[0] + 12;
		}
		if ( !$f_bUpper && 24 <= (int)$x[0] ) {
			$x[0] -= 24 * floor((int)$x[0]/24);
		}
		return str_pad((int)$x[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad((int)$x[1], 2, '0', STR_PAD_LEFT);

	} // END checkTime() */


	/**
	 * checkDate()
	 */
	public static function checkDate( $f_szDate )
	{
		if ( !preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $f_szDate) ) {
			return false;
		}
		$x = explode('-', $f_szDate);
		if ( 10 != strlen($f_szDate) ) {
			$f_szDate = $x[0] . '-' . str_pad((int)$x[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad((int)$x[2], 2, '0', STR_PAD_LEFT);
		}
		return $f_szDate;

	} // END checkDate() */


	/**
	 * getPeriod()
	 */
	public static function getPeriod( $f_bWeekend, $f_bPeak )
	{
		return 'week' . ( $f_bWeekend ? 'end' : '' ) . ( $f_bPeak ? '_peak' : '' );

	} // END getPeriod() */



	/**
	 * getWeekday()
	 */
	public static function getWeekday( $f_iWeekday, $f_iSubstrAt = 0 ) {
		// 0 = SUNDAY
		$szWeekday = multilang::translate('WEEKDAY_'.((int)$f_iWeekday%7));
		if ( 2 <= $f_iSubstrAt ) {
			$szWeekday = substr($szWeekday, 0, $f_iSubstrAt);
		}
		return $szWeekday;

	} // END getWeekday() */


	/**
	 * getWeekdays()
	 */
	public static function getWeekdays( $f_iSubstrAt = 0 ) {
		// 0 = SUNDAY
		$arrWeekdays = array(
			1 => multilang::translate('WEEKDAY_MONDAY'),
			2 => multilang::translate('WEEKDAY_TUESDAY'),
			3 => multilang::translate('WEEKDAY_WEDNESDAY'),
			4 => multilang::translate('WEEKDAY_THURSDAY'),
			5 => multilang::translate('WEEKDAY_FRIDAY'),
			6 => multilang::translate('WEEKDAY_SATURDAY'),
			0 => multilang::translate('WEEKDAY_SUNDAY'),
		);
		if ( 2 <= $f_iSubstrAt ) {
			foreach ( $arrWeekdays AS $k => $szDay ) {
				$arrWeekdays[$k] = substr($szDay, 0, $f_iSubstrAt);
			}
		}
		return $arrWeekdays;

	} // END getWeekdays() */


	/**
	 * getMonths()
	 */
	public static function getMonths( $f_iSubstrAt = 0 )
	{
		// 0 = EMPTY, 1 = JANUARY
		$arrMonths = array(
			1 => multilang::translate('MONTH_JANUARY'),
			multilang::translate('MONTH_FEBRUARY'),
			multilang::translate('MONTH_MARCH'),
			multilang::translate('MONTH_APRIL'),
			multilang::translate('MONTH_MAY'),
			multilang::translate('MONTH_JUNE'),
			multilang::translate('MONTH_JULY'),
			multilang::translate('MONTH_AUGUST'),
			multilang::translate('MONTH_SEPTEMBER'),
			multilang::translate('MONTH_OCTOBER'),
			multilang::translate('MONTH_NOVEMBER'),
			multilang::translate('MONTH_DECEMBER'),
		);

		if ( 2 <= $f_iSubstrAt ) {
			foreach ( $arrMonths AS $k => $v ) {
				$arrMonths[$k] = substr($v, 0, $f_iSubstrAt);
			}
		}

		return $arrMonths;

	} // END getMonths() */


	/**
	 * isReservable()
	 */
	public static function isReservable( $f_iResource, $f_szDate, $f_szStartTime, $f_szEndTime, $f_arrRuleVars = array(), $f_arrIgnoreReservations = array() )
	{
		$iUtcStartTime = common::mktime($f_szDate);
		$iToday = (int)date('w', $iUtcStartTime);

		$f_szStartTime = substr($f_szStartTime, 0, 5).':00';
		$f_szEndTime = substr($f_szEndTime, 0, 5).':00';

		// Is it even open?
		$arrTimeset = resource::getTimeset( $f_iResource, $f_szDate );
		if ( $arrTimeset['open_time'].':00' > $f_szStartTime || $arrTimeset['close_time'].':00' < $f_szEndTime ) {
			return 'closed:'.__LINE__;
		}

		// Blocked resource set?
		$szWhereClause = 'CONCAT(\',\',resource_ids,\',\') LIKE \'%,'.(int)$f_iResource.",%' AND is_enabled = '1' AND start_date <= '".$f_szDate."' AND ( end_date >= '".$f_szDate."' OR end_date IS NULL ) AND on_days LIKE '%".$iToday."%' AND ('".$f_szEndTime."' > start_time AND '".$f_szStartTime."' < end_time)";
		$iBlocked = db_count( 'blocked_resource_sets', $szWhereClause );
		if ( $iBlocked ) {
			return 'blocked';
		}

		// Existing reservation in the way?
		$szSqlQuery = "SELECT r.id, r.slots, r.start_time AS StartA, ADDTIME(r.start_time, SEC_TO_TIME(60*r.slots*s.slotsize)) AS EindA, '".$f_szStartTime."' AS StartB, '".$f_szEndTime."' AS EindB FROM club_sports s, resources c, reservations r WHERE ".( $f_arrIgnoreReservations ? "r.id NOT IN (".implode(',', (array)$f_arrIgnoreReservations).") AND " : '' )."r.resource_id = c.id AND c.club_sport_id = s.id AND r.not_cancelled = '1' AND r.resource_id = ".(int)$f_iResource." AND r.date = '".$f_szDate."' HAVING
( (StartA >= StartB AND StartA < EindB) OR
  (EindA >= StartB AND EindA < StartB) OR
  (StartB >= StartA AND StartB < EindA) OR
  (EindB >= StartA AND EindB < StartA) );";
		$arrReservations = db_fetch($szSqlQuery);
		if ( 0 < count($arrReservations) ) {
			return 'reservation:'.$arrReservations[0]['id'].' ('.substr($arrReservations[0]['StartA'], 0, 5).' - '.substr($arrReservations[0]['EindA'], 0, 5).')';
		}

		$szSqlQuery = '
		SELECT
			a.start_time,
			ADDTIME(
				a.start_time,
				SEC_TO_TIME( s.slotsize*60 * (a.length_in_slots+a.pause_slots_in_between) * a.repeat_times - (s.slotsize*60*a.pause_slots_in_between) )
			) AS end_time
		FROM
			club_sports s,
			resources r,
			class_activities a
		WHERE
			a.resource_id = r.id AND
			r.club_sport_id = s.id AND
			a.attach_to_resource_id IS NOT NULL AND
			CONCAT(\',\',a.attach_to_resource_id,\',\') LIKE \'%,'.$f_iResource.",%' AND
			a.start_date <= '".$f_szDate."' AND
			( a.end_date >= '".$f_szDate."' OR a.end_date IS NULL ) AND
			a.is_enabled = '1' AND
			a.on_days LIKE '%".$iToday."%'
		HAVING
			(start_time < '".$f_szEndTime."' AND end_time > '".$f_szStartTime."');";
		$arrClasses = db_fetch($szSqlQuery);
		if ( 0 < count($arrClasses) ) {
			return 'class';
		}

		return true;

	} // END isReservable() */


	/**
	 * isPeakTime()
	 */
	public static function isPeakTime( $f_iResource, $f_szDate, $f_szTime )
	{
		$iUtcStartTime = common::mktime($f_szDate);
		$iToday = (int)date('w', $iUtcStartTime);

		if ( ($iSpecialSet=db_select_one('special_opening_hours_sets', 'id', 'resource_id = '.(int)$f_iResource." AND ('".$f_szDate."' BETWEEN start_date AND end_date) ORDER BY id DESC")) )
		{
			return (0 < db_count( 'resource_opening_hours t, peak_times_in_special_opening_hours_sets p', 't.id = p.resource_opening_hours_id AND p.special_opening_hours_set_id = '.(int)$iSpecialSet.' AND t.open_'.$iToday." <= '".$f_szTime."' AND t.closed_".$iToday." > '".$f_szTime."' AND t.open_".$iToday.' != t.closed_'.$iToday ));
		}

		return (0 < db_count( 'resource_opening_hours t, peak_hours_in_resources p', 't.id = p.resource_opening_hours_id AND p.resource_id = '.(int)$f_iResource.' AND t.open_'.$iToday." <= '".$f_szTime."' AND t.closed_".$iToday." > '".$f_szTime."' AND t.open_".$iToday.' != t.closed_'.$iToday ));

	} // END isPeakTime() */


	/**
	 * isWeekend()
	 */
	public static function isWeekend( $f_szDate )
	{
		$iToday = (int)date('w', common::mktime($f_szDate));
		return ( 0 == $iToday || 6 == $iToday );

	} // END isWeekend() */


	/**
	 * mktime()
	 */
	public static function mktime( $f_szDate, $f_szTime = null )
	{
		if ( !$f_szTime ) {
			$f_szTime = '0:0';
		}
		$d = explode('-', $f_szDate);
		$t = explode(':', $f_szTime);
		if ( 3 > count($d) || 2 > count($t) ) {
			return 0;
		}
		return mktime((int)$t[0], (int)$t[1], 0, (int)$d[1], (int)$d[2], (int)$d[0]);

	} // END mktime() */


}

?>
