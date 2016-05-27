<?php

require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * ilAdvancedSvyResultsPlugin
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 0.0.1
 *
 */
class ilAdvancedSvyResultsPlugin extends ilUserInterfaceHookPlugin {

	const PN = 'AdvancedSvyResults';
	/**
	 * @var ilAdvancedSvyResultsPlugin
	 */
	protected static $instance;


	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PN;
	}


	/**
	 * @return ilAdvancedSvyResultsPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

?>
