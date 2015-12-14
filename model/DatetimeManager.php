<?php
namespace model;
/**
 * Description of DatetimeManager
 *
 * @author Stepan
 */
class DatetimeManager {
	
	const DB_FORMAT = "y-m-d H:i:s";
	
	public static function getWeeksBounds($weekOffset = 0, $format = false){
		if($weekOffset == 0){
			$looseWeek = time();
		} else {
			if ($weekOffset < 0){
				$looseWeek = strtotime("$weekOffset week");
			} else {
				$looseWeek = strtotime("+$weekOffset week");
			}
		}
		$isMonday = date('w', $looseWeek) == 1;
		
		$start = strtotime((($isMonday) ? 'this' : 'last').' Monday', $looseWeek);

		$end = strtotime(($isMonday ? 'next' : 'this').' Monday', $looseWeek);
		if($format){
			return ['time_from' => date($format, $start),
					'time_to' => date($format, $end)];
		} else { return ['time_from' => $start, 'time_to' => $end]; }
	}
	
}
