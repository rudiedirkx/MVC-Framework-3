<?php

# multiple unset
function munset( &$f_arrContainer, $f_arrKeys ) {
	if ( is_scalar($f_arrKeys) ) {
		$f_arrKeys = func_get_args();
		array_shift($f_arrKeys);
	}
	foreach ( $f_arrKeys AS $k ) {
		unset($f_arrContainer[$k]);
	}
}


# numbers #
function isint( $f_mixed ) {
	return is_numeric($f_mixed) && (string)$f_mixed === (string)(int)$f_mixed;
}

function str2int( $f_szNum ) {
	return (int)str_replace(',', '', str_replace('.', '', str_replace(' ', '', str_replace('-', '', (string)$f_szNum ) ) ) );
}

function int2str( $f_iNum, $f_bToMonetary = false ) {
	$str = number_format( (float)$f_iNum, 0, '.', ',' );
	if ( $f_bToMonetary ) {
		return monetary($str);
	}
	return $str;
}

function monetary( $f_fAmount, $f_bForcePlus = false ) {
	return multilang::translate('MONEY_SYMBOL') . ' ' . ( $f_bForcePlus && 0 <= (float)$f_fAmount ? '+' : '' ) . number_format((float)$f_fAmount, 2, multilang::translate('DECIMALS_SEPARATOR'), multilang::translate('THOUSANDS_SEPARATOR'));
}


function escapehtml($string) {
	static $r;
	if ( empty($r) ) {
		$r = array_combine(range(chr(0), chr(9)), array_fill(0, 10, ''));
	}
	return htmlspecialchars(strtr((string)$string, $r));
}


function timetostr() {
	$args = func_get_args();
	return strtolower(call_user_func_array('date', $args));
}

function stime() {
	return time();
}
function ctime() {
	global $g_arrClub;
	return isset($g_arrClub['timezone_offset_seconds']) ? stime()+$g_arrClub['timezone_offset_seconds'] : stime();
}



# password generation #
function rand_string( $f_iLength = 8 ) {
	$arrTokens = array_merge( range("a","z"), range("A","Z"), range("0","9") );
	$szRandString = "";
	for ( $i=0; $i<max(1, (int)$f_iLength); $i++ ) {
		$szRandString .= $arrTokens[array_rand($arrTokens)];
	}
	return $szRandString;
}



# times #
function sqlmktime( $f_szSqlDate ) {
	if ( (int)str_replace('-', '', $f_szSqlDate) == 0 ) return 0;
	$x = explode("-", $f_szSqlDate);
	return max(0, (int)mktime( 0, 0, 1, $x[1], $x[2], $x[0] ));
}
function sqlyear( $f_szSqlDate ) {
	$x = explode("-", $f_szSqlDate);
	return (int)$x[0];
}
function sqlmonth( $f_szSqlDate ) {
	$x = explode("-", $f_szSqlDate);
	return (int)$x[1];
}
function sqldayofmonth( $f_szSqlDate ) {
	$x = explode("-", $f_szSqlDate);
	return (int)$x[2];
}
function sqlreverseformat( $f_szSqlDate ) {
	return implode("-", array_reverse(explode("-", $f_szSqlDate)));
}
function reverse_2d_array( $f_array ) {
	$a = array();
	foreach ( $f_array AS $k1 => $v1 ) {
		foreach ( $v1 AS $k2 => $v2 ) {
			$a[$k2][$k1] = $v2;
		}
	}
	return $a;
}
function readable_time( $f_fMathTime ) {
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
	$szHours = /*24 == $iHours ? '00' : */str_pad((string)$iHours, 2, '0', STR_PAD_LEFT);
	return $szHours.':'.$szMinutes;
}
function math_time( $f_szReadableTime ) {
	$x = explode(":", $f_szReadableTime);
	return (string)round($x[0]+$x[1]/60, 4);
}




# misc #
function ifsetor( & $var, $second = NULL ) {
	return isset($var) ? $var : $second;
}

?>