<?php

class AROResource extends ActiveRecordObject {

	protected static $_table = 'resources';
	protected static $_columns = array(
		'club_sport_id',
		'name',
		'game_length',
		'multiple_games',
		'minimum_games',
		'is_enabled',
		'is_class_resource',
		'max_players',
		'min_players',
		'uses_resource_id',
		'zindex',
		'resource_opening_hours_id',
		'open_for_guests',
		'reason_for_inactivity',
		'color_free_slot',
		'color_free_peak_slot',
		'color_closed_slot',
	);
	protected static $_pk = 'id';
	protected static $_relations = array(
		'sport' => array( self::HAS_ONE, 'AROClubSport', 'club_sport_id' ),
	);


	static public function finder( $class = __CLASS__ ) {
		return parent::finder($class);

	} // END finder() */


	public function getQuery( $clause ) {
		$szQuery = 'SELECT s.*, resources.* FROM club_sports s, resources WHERE s.id = resources.club_sport_id';
		if ( $clause ) {
			$szQuery .= ' AND '.$clause;
		}
		return $szQuery.';';

	} // END getQuery() */


} // END Class AROResource

?>