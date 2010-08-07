<?php

require_once(dirname(__FILE__).'/inc.cls.activerecordobject.php');

class AROReservation extends ActiveRecordObject {

	protected static $_table = 'reservations';
	protected static $_columns = array(
		'date',
		'start_time',
		'resource_id',
		'slots',
		'block_reservation_id',
		'created_by',
		'created_on',
		'not_cancelled',
		'cancelled_by',
		'cancelled_on',
		'notes',
		'extra',
		'last_change',
		'discarded'
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'resource' => array( self::HAS_ONE, 'AROResource', 'resource_id' ),
		'players' => array( self::HAS_MANY, 'AROReservationPlayer', 'reservation_id' ),
	);

	public $error = '';


	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);

	} // END finder() */


	public function getQuery( $clause ) {
		$szQuery = 'SELECT s.*, c.*, reservations.* FROM club_sports s, resources c, reservations WHERE s.id = c.club_sport_id AND c.id = reservations.resource_id';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';

	} // END getQuery() */


	public function fill( $data ) {
		parent::fill($data);
		$this->start_time = new Time($this->start_time);

	} // END fill() */


	/**
	 * Required:
	 * - date
	 * - resource_id
	 * - start_time
	 * - end_time
	 */
	public function isReservable( $f_arrIgnoreReservationIds = array() ) {
		$iUtcStartTime = common::mktime($this->date);
		$iToday = (int)date('w', $iUtcStartTime);

		$szStartTime = substr((string)$this->start_time, 0, 5).':00';
		$szEndTime = substr((string)$this->getEndTime(), 0, 5).':00';

//		$f_arrIgnoreReservationIds = (array)$f_arrIgnoreReservationIds;
//		if ( isset($this->id) ) {
//			array_push($f_arrIgnoreReservationIds, $this->id);
//		}

		// Is it even open?
		if ( ($iSpecialOpeningHoursId=$this->getDbObject()->select_one('special_opening_hours_sets', 'resource_opening_hours_id', 'resource_id = '.(int)$this->resource_id." AND ('".$this->date."' BETWEEN start_date AND end_date) ORDER BY id DESC")) )
		{
			// Special opening hours
			if ( !$this->getDbObject()->count( 'resource_opening_hours', 'id = '.(int)$iSpecialOpeningHoursId.' AND open_'.$iToday." != closed_".$iToday." AND open_".$iToday." <= '".$szStartTime."' AND closed_".$iToday." >= '".$szEndTime."'" ) ) {
				return 'closed:'.__LINE__;
			}
		}
		else if ( !$this->getDbObject()->count( 'resources r, resource_opening_hours h', "r.id = ".(int)$this->resource_id." AND r.resource_opening_hours_id = h.id AND h.open_".$iToday." != h.closed_".$iToday." AND h.open_".$iToday." <= '".$szStartTime."' AND h.closed_".$iToday." >= '".$szEndTime."'" ) ) {
			// Normal hours
			return 'closed:'.__LINE__;
		}

		// Blocked resource set?
		$szWhereClause = 'CONCAT(\',\',resource_ids,\',\') LIKE \'%,'.(int)$this->resource_id.",%' AND is_enabled = '1' AND start_date <= '".$this->date."' AND ( end_date >= '".$this->date."' OR end_date IS NULL ) AND on_days LIKE '%".$iToday."%' AND ('".$szEndTime."' > start_time AND '".$szStartTime."' < end_time)";
		$iBlocked = $this->getDbObject()->count( 'blocked_resource_sets', $szWhereClause );
		if ( $iBlocked ) {
			return 'blocked';
		}

		// Existing reservation in the way?
		$szSqlQuery = "SELECT r.id, r.slots, r.start_time AS StartA, ADDTIME(r.start_time, SEC_TO_TIME(60*r.slots*s.slotsize)) AS EindA, '".$szStartTime."' AS StartB, '".$szEndTime."' AS EindB FROM club_sports s, resources c, reservations r WHERE ".( $f_arrIgnoreReservationIds ? 'r.id NOT IN ('.implode(',', $f_arrIgnoreReservationIds).') AND ' : '' )."r.resource_id = c.id AND c.club_sport_id = s.id AND r.not_cancelled = '1' AND r.resource_id = ".(int)$this->resource_id." AND r.date = '".$this->date."' HAVING
		( (StartA >= StartB AND StartA < EindB) OR
		  (EindA >= StartB AND EindA < StartB) OR
		  (StartB >= StartA AND StartB < EindA) OR
		  (EindB >= StartA AND EindB < StartA) ) LIMIT 1;";
		if ( 0 < count($arrReservations=$this->getDbObject()->fetch($szSqlQuery)) ) {
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
			CONCAT(\',\',a.attach_to_resource_id,\',\') LIKE \'%,'.$this->resource_id.",%' AND
			a.start_date <= '".$this->date."' AND
			( a.end_date >= '".$this->date."' OR a.end_date IS NULL ) AND
			a.is_enabled = '1' AND
			a.on_days LIKE '%".$iToday."%'
		HAVING
			(start_time < '".$szEndTime."' AND end_time > '".$szStartTime."');";
		if ( 0 < $this->getDbObject()->count_rows($szSqlQuery) ) {
			return 'class';
		}

		return true;

	} // END isReservable() */


	/**
	 * Saves the reservation only if it's reservable
	 */
	public function save() {
		if ( true === ($s=$this->isReservable(array($this->id))) ) {
			return parent::save();
		}
		$this->error = $s;
		return false;

	} // END save() */


	/**
	 * Saves the reservation only if it's reservable
	 */
	public function saveAsNew() {
		if ( true === ($s=$this->isReservable()) ) {
			if ( $n = parent::saveAsNew() ) {
				// copy players
				foreach ( $this->players AS $p ) {
					$p->reservation_id = $n;
					$p->saveAsNew();
				}
				return $n;
			}
		}
		$this->error = $s;
		return false;

	} // END save() */


	/**
	 * Copies the exact same reservation to another date
	 */
	public function copy($date) {
		$od = $this->date;
		$this->date = $date;
		$n = $this->saveAsNew();
		$this->date = $od;
		return $n;

	} // END copy() */


	/**
	 * Sets the `not_cancelled` option to null
	 */
	public function cancel() {
		return false;
		$this->cancelled_by = USER_ID;
		$this->cancelled_on = time();
		$this->not_cancelled = null;
		return $this->save();

	} // END cancel() */


	/**
	 * Sets the `discarded` option to true
	 */
	public function discard() {
		return false;
		$this->cancelled_by = USER_ID;
		$this->cancelled_on = time();
		$this->discarded = true;
		return $this->save();

	} // END discard() */


	/**
	 * Calculates and saves the end_time, using `start_time`, `slotsize` and `slots`
	 */
	public function getEndTime( $f_bRecalculate = false ) {
		if ( empty($this->end_time) || $f_bRecalculate ) {
			if ( isset($this->slotsize, $this->slots) ) {
				$this->end_time = new Time((string)$this->start_time);
				$this->end_time->addMinutes($this->slotsize * $this->slots);
			}
			else {
				$this->end_time = new Time('00:00');
			}
		}
		return $this->end_time;

	} // END getEndTime() */


} // END Class AROReservation

?>