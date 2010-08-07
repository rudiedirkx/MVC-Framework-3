<?php

class Time {

	public static function toMathTime( $f_szReadableTime ) {
		$x = explode(":", $f_szReadableTime);
		return (string)round($x[0]+$x[1]/60, 4);
	}

	public static function toReadableTime( $f_fMathTime ) {
		$fTime = max(0, $f_fMathTime);
		if ( 24 < $fTime ) {
			$fTime -= 24;
		}
		$iHours = (int)$fTime;
		$iMinutes = round(($fTime-(int)$fTime)*60, 4);
		$szMinutes = str_pad((string)round($iMinutes,0), 2, '0', STR_PAD_LEFT);
		if ( '60' == $szMinutes ) {
			$szMinutes = '00';
			$iHours++;
		}
		$szHours = str_pad((string)$iHours, 2, '0', STR_PAD_LEFT);
		return $szHours.':'.$szMinutes;
	}

	public static function minutesDiff( $f_szStartTime, $f_szEndTime ) {
		$fTimeA = math_time($f_szStartTime);
		$fTimeB = math_time($f_szEndTime);
		$fHoursDiff = abs(round($fTimeB - $fTimeA, 4));
		return round(60*$fHoursDiff, 0);
	}

	public $m_iHours	= 0;
	public $m_iMinutes	= 0;

	public function __construct($f_szTime=null) {
		if ( $f_szTime ) {
			$this->setTime($f_szTime);
		}
	}

	public function setTime($f_szTime) {
		$x = explode(':', $f_szTime);
		$this->m_iHours = (int)$x[0];
		$this->m_iMinutes = isset($x[1]) ? (int)$x[1] : 0;
	}

	public function addMinutes($f_iMinutes, $f_bClone=false) {
		$obj = $f_bClone ? clone $this : $this;
		$obj->m_iMinutes += ((int)$f_iMinutes)%60;
		$obj->m_iHours += floor((int)$f_iMinutes/60);
		if ( 60 <= $obj->m_iMinutes ) {
			$obj->m_iMinutes -= 60;
			$obj->m_iHours += 1;
		}
		else if ( 0 > $obj->m_iMinutes ) {
			$obj->m_iMinutes += 60;
			$obj->m_iHours -= 1;
		}
		if ( $f_bClone ) {
			return $obj;
		}
	}

	public function addHours($f_iHours) {
		$this->m_iHours += (int)$f_iHours;
	}

	public function getTimeAsInt() {
		return math_time($this->getTimeAsString());
	}

	public function getTimeAsString($f_bRealtime = false) {
		$iHours = $f_bRealtime ? $this->m_iHours%24 : $this->m_iHours;
		return str_pad((string)$iHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$this->m_iMinutes, 2, '0', STR_PAD_LEFT);
	}

	public function __tostring() {
		return $this->getTimeAsString();
	}


	public static function getAsMinutes($f_szTime) {
		$x = explode(':', $f_szTime);
		$iMinutes = 60 * (int)$x[0];
		if ( isset($x[1]) ) {
			$iMinutes += (int)$x[1];
		}
		return $iMinutes;
	}
}

?>