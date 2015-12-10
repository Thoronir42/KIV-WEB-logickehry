<?php
namespace model;

/**
 * Description of ImageManager
 *
 * @author Stepan
 */
class ImageManager{
	const IMG_FOLDER = __DIR__."/../www/images/";
	
	const IMAGE_NOT_FOUND = "nf.png";
	
	public static function get($fileName){
		if($fileName == null){
			return self::IMAGE_NOT_FOUND;
		}
		if(!file_exists(self::IMG_FOLDER.$fileName)){
			return self::IMAGE_NOT_FOUND;
		}
		return $fileName;
	}
}
