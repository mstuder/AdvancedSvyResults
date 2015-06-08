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


	public function updateLanguageFiles() {
		ini_set('auto_detect_line_endings', true);
		$path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/';
		if (file_exists($path . 'lang_custom.csv')) {
			$file = $path . 'lang_custom.csv';
		} else {
			$file = $path . 'lang.csv';
		}
		$keys = array();
		$new_lines = array();

		foreach (file($file) as $n => $row) {
			//			$row = utf8_encode($row);
			if ($n == 0) {
				$keys = str_getcsv($row, ";");
				continue;
			}
			$data = str_getcsv($row, ";");;
			foreach ($keys as $i => $k) {
				if ($k != 'var' AND $k != 'part') {
					$new_lines[$k][] = $data[0] . '_' . $data[1] . '#:#' . $data[$i];
				}
			}
		}
		$start = '<!-- language file start -->' . PHP_EOL;
		$status = true;

		foreach ($new_lines as $lng_key => $lang) {
			$status = file_put_contents($path . 'ilias_' . $lng_key . '.lang', $start . implode(PHP_EOL, $lang));
		}

		if (!$status) {
			ilUtil::sendFailure('Language-Files coul\'d not be written');
		}
		$this->updateLanguages();
	}
}

?>
