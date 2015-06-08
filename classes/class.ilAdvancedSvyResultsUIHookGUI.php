<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');

/**
 * Class ilAdvancedSvyResultsUIHookGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 0.0.1
 *
 * @ingroup ServicesUIComponent
 */
class ilAdvancedSvyResultsUIHookGUI extends ilUIHookPluginGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ilCtrl;
	/**
	 * @var HTML_Template_ITX|ilTemplate
	 */
	protected $tpl;
	/**
	 * @var array
	 */
	protected static $loaded = array();


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected static function isLoaded($key) {
		return self::$loaded[$key] == 1;
	}


	/**
	 * @param $key
	 */
	protected static function setLoaded($key) {
		self::$loaded[$key] = 1;
	}


	public function __construct() {
		global $ilCtrl, $tpl;
		$this->plugin = ilAdvancedSvyResultsPlugin::getInstance();
		$this->ilCtrl = $ilCtrl;
		$this->tpl = $tpl;
	}


	/**
	 * @param       $a_comp
	 * @param       $a_part
	 * @param array $a_par
	 *
	 * @return array
	 */
	public function getHTML($a_comp, $a_part, $a_par = array()) {
		/**
		 * @var $ilCtrl       ilCtrl
		 * @var $tpl          ilTemplate
		 * @var $ilToolbar    ilToolbarGUI
		 */
		global $ilCtrl, $ilUser;

		return array( 'mode' => ilUIHookPluginGUI::KEEP, 'html' => 'lorem' );
	}


	public function modifyGUI($a_comp, $a_part, $a_par = array()) {
		if ($a_part == 'tabs') {
			if ($this->isInSurvey()) {
				/**
				 * @var $ilTabsGUI ilTabsGUI
				 */
				$ilTabsGUI = $a_par['tabs'];
				$this->ilCtrl->setParameterByClass('ilsurveyextevaluationgui', 'ref_id', $_GET['ref_id']);
				$ilTabsGUI->addTab('svy_results', $this->plugin->txt('tabs_results'), $this->ilCtrl->getLinkTargetByClass(array(
					'ilRouterGUI',
					'asrPresentationGUI',
					'ilSurveyExtEvaluationGUI'
				)));
			}

			if ($this->isOnResultTab()) {
			}
		}
	}


	protected function isOnResultTab() {
		global $ilTabs;
		/**
		 * @var $ilTabs ilTabsGUI
		 */

		//echo $ilTabs->getActiveTab();

		return true;
	}


	/**
	 * @return bool
	 */
	protected function isInSurvey() {
		if ($_GET['cmdClass'] == 'asrpresentationgui' OR $_GET['cmdClass'] == 'ilsurveyevaluationgui') {
			return true;
		}
		foreach ($this->ilCtrl->getCallHistory() as $hist) {
			if ($hist['class'] == 'ilObjSurveyGUI') {
				return true;
			}
		}

		return false;
	}
}

?>
