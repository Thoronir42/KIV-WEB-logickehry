<?php

namespace model;

/**
 * Description of ImageManager
 *
 * @author Stepan
 */
class ImageManager {

	const IMG_FOLDER = "../www/images/";
	const ALLOWED_FILE_TYPES = ["jpg", "jpeg", "png"];
	const MIN_IMG_SIZE = 150;
	const MAX_IMG_SIZE = 2000;
	const MAX_IMG_FILE_SIZE = 12582912; // 12 * 1024 * 1024
	const IMAGE_NOT_FOUND = "nf.png";

	public static function get($fileName, $extension = null) {
		if (file_exists(self::IMG_FOLDER . $fileName)) {
			echo "<br>";
			return $fileName;
		}
		if ($extension != null) {
			$fn = "$fileName.$extension";
			if (file_exists(self::IMG_FOLDER . $fn)) {
				return $fn;
			}
		} else {
			foreach (self::ALLOWED_FILE_TYPES as $ext) {
				$fn = "$fileName.$ext";
				if (file_exists(self::IMG_FOLDER . $fn)) {
					return $fn;
				}
			}
		}

		return self::IMAGE_NOT_FOUND;
	}

	public static function put($sourceKey, $destFile) {
		// Allow certain file formats
		$fileType = self::checkFileType(basename($_FILES[$sourceKey]["name"]));
		if (!$fileType) {
			return ['result' => false, 'message' => "Nahraný soubor " . $_FILES[$sourceKey]["name"] . " není jedním z povolených typů: " . implode(", ", self::ALLOWED_FILE_TYPES)];
		}

		// Check if image file is a actual image or fake image
		$check = self::checkImageSize(getimagesize($_FILES["picture"]["tmp_name"]));
		if ($check) {
			return $check;
		}

		// Check file size
		if (($file_size = $_FILES["picture"]["size"]) > self::MAX_IMG_FILE_SIZE) {
			return ['result' => false, 'message' => "Nahraný obrázek je příliš velký: " . ($file_size / 1024) . "kb"];
		}

		// if everything is ok, try to upload file
		$finalFileName = self::IMG_FOLDER . "$destFile.$fileType";
		self::deleteIfexists(self::IMG_FOLDER . $destFile);
		if (move_uploaded_file($_FILES[$sourceKey]["tmp_name"], $finalFileName)) {
			return ['result' => true, 'message' => "Obrázek se podařilo nahrát jako $destFile.$fileType"];
		} else {
			return ['result' => false, 'message' => "Nahraný obrázek se nepodařilo přesunout do správné složky"];
		}

		return ['result' => false, 'message' => "Při nahrávání souboru nastala neočekávaná chyba"];
	}

	private static function checkFileType($file) {
		$imageFileType = pathinfo($file, PATHINFO_EXTENSION);
		foreach (self::ALLOWED_FILE_TYPES as $ft) {
			if ($imageFileType == $ft) {
				return $ft;
			}
		}
		echo $imageFileType;
		return false;
	}

	private static function checkImageSize($check) {
		if ($check === false) {
			return ['result' => false, 'message' => "Nahraný soubor není obrázek"];
		}
		if ($check[0] != $check[1]) {
			return ['result' => false, 'message' => "Nahraný obrázek není čtvercový"];
		}
		if ($check[0] < self::MIN_IMG_SIZE) {
			return ['result' => false, 'message' => "Nahraný obrázek je příliš malý($check[0])"];
		}
		if ($check[1] > self::MAX_IMG_SIZE) {
			return ['result' => false, 'message' => "Nahraný obrázek je příliš malý($check[0])"];
		}

		return false;
	}

	private static function deleteIfexists($fileName) {
		foreach (self::ALLOWED_FILE_TYPES as $ext) {
			$n = "$fileName.$ext";
			if (file_exists($n)) {
				unlink($n);
			}
		}
	}

}
