<?php

	/**
	* ZModels Extension
	*
	* @package Scabbia
	* @subpackage zmodels
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class zmodels {
		/**
		* @ignore
		*/
		public static $zmodels = null;

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$zmodels = array();

			foreach(config::get('/zmodelList', array()) as $tZmodel) {
				self::$zmodels[$tZmodel['name']] = $tZmodel;
			}
		}
	}

?>