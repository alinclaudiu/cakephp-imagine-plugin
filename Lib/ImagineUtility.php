<?php
/**
 * Copyright 2011-2014, Florian Krämer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * Copyright 2011-2014, Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class ImagineUtility {

/**
 * Turns the operations and their params into a string that can be used in a file name to cache an image.
 *
 * Suffix your image with the string generated by this method to be able to batch delete a file that has versions of it cached.
 * The intended usage of this is to store the files as my_horse.thumbnail+width-100-height+100.jpg for example.
 *
 * So after upload store your image meta data in a db, give the filename the id of the record and suffix it
 * with this string and store the string also in the db. In the views, if no further control over the image access is needd,
 * you can simply direct linke the image like $this->Html->image('/images/05/04/61/my_horse.thumbnail+width-100-height+100.jpg');
 *
 * @param array $operations
 * @param array $separators
 * @param mixed $hash
 * @throws BadFunctionCallException
 * @return string Filename compatible String representation of the operations
 * @link http://support.microsoft.com/kb/177506
 */
	public static function operationsToString($operations, $separators = array(), $hash = false) {
		ksort($operations);

		$defaultSeparators = array(
			'operations' => '.',
			'params' => '+',
			'value' => '-'
		);
		$separators = array_merge($defaultSeparators, $separators);

		$result = '';
		foreach ($operations as $operation => $data) {
			$tmp = array();
			foreach ($data as $key => $value) {
				if (is_string($value) || is_numeric($value)) {
					$tmp[] = $key . $separators['value'] . $value;
				}
			}
			$result = $separators['operations'] . $operation . $separators['params'] . join($separators['params'], $tmp);
		}

		if ($hash && $result !== '') {
			if (function_exists($hash)) {
				return $hash($result);
			}
			throw new BadFunctionCallException();
		}

		return $result;
	}

/**
 * This method expects an array of Model.configName => operationsArray
 *
 * @param array $imageSizes
 * @param int $hashLenght
 * @return array Model.configName => hashValue
 */
	public static function hashImageOperations($imageSizes, $hashLenght = 8) {
		foreach ($imageSizes as $model => $operations) {
			foreach ($operations as $name => $operation) {
				$imageSizes[$model][$name] = substr(self::operationsToString($operation, array(), 'md5'), 0, $hashLenght);
			}
		}
		return $imageSizes;
	}

/**
 * Loader for the Imagine Namespace
 *
 * @param string
 * @return void
 */
	public static function load($name) {
		$name = str_replace("\\", DS, $name);
		$imagineBase = \Configure::read('Imagine.base');
		if (empty($imagineBase)) {
			$imagineBase = \CakePlugin::path('Imagine') . 'Vendor' . DS . 'Imagine' . DS . 'lib' . DS;
		}

		$filePath = $imagineBase . $name . '.php';
		if (file_exists($filePath)) {
			require_once ($filePath);
			return;
		}

		$imagineBase = $imagineBase . 'Image' . DS;
		if (file_exists($imagineBase . $name . '.php')) {
			require_once ($imagineBase . $name . '.php');
		}
	}

}
