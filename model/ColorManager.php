<?php

namespace model;

/**
 * Description of ColorManager
 *
 * @author Stepan
 */
class ColorManager {

	const TRANSFORM_POLYNOM = [1, -5, -2, 1, +12];

	private static $colors = [];

	public static function numberToColor($n) {
		if (!isset(self::$colors[$n])) {
			$ni = self::transformNumber($n);
			self::$colors[$n] = self::makeColor($ni);
		}
		return self::$colors[$n];
	}

	private static function transformNumber($n) {
		$pol = self::TRANSFORM_POLYNOM;
		$sum = 0;
		$currentPow = 1;

		for ($i = sizeof($pol) - 1; $i >= 0; $i--) {
			$sum += $pol[$i] * $currentPow;
			$currentPow *= $n;
		}
		return $sum;
	}

	private static function makeColor($n) {
;
		return substr(md5($n), 0, 6);
	}

	public static function getColors() {
		$return = [];
		foreach (self::$colors as $k => $v) {
			$return[] = ['game_type_id' => $k, 'color' => $v];
		}
	}

}
