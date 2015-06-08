<?php
require_once('./Modules/Survey/classes/class.ilObjSurvey.php');
require_once('./Modules/Survey/classes/class.ilObjSurveyGUI.php');
require_once('./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php');
require_once('class.ilSurveyExtEvaluationGUI.php');
require_once('./Services/Object/classes/class.ilObject2.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/Presentation/class.ilExtSurveyEditorGUI.php');

/**
 * Class asrPresentationGUI
 *
 * @author             Fabian Schmid <fs@studer-raimann.ch>
 * @version            1.0.0
 *
 * @ilCtrl_IsCalledBy  asrPresentationGUI : ilRouterGUI
 * @ilCtrl_Calls       asrPresentationGUI : ilExtSurveyEditorGUI
 */
class asrPresentationGUI {

	/**
	 * @var int
	 */
	protected $ref_id;
	/**
	 * @var ilObjSurvey
	 */
	protected $ilObjSurvey;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ilCtrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $ilTabsGUI;


	public function __construct() {
		global $tpl, $ilCtrl;
		$this->ref_id = $_GET['ref_id'];
		$this->tpl = $tpl;
		$this->ilCtrl = $ilCtrl;
		$this->ilCtrl->saveParameter($this, 'ref_id');
		$this->ilCtrl->setParameterByClass('ilExtSurveyEditorGUI', 'ref_id', $_GET['ref_id']);
		$this->pl = ilAdvancedSvyResultsPlugin::getInstance();
//		$this->pl->updateLanguageFiles();
		$this->initHeaderAndTabs();
	}


	protected function initHeaderAndTabs() {
		$ref_id = $_GET['ref_id'];
		$obj_id = ilObject2::_lookupObjectId($ref_id);
		$this->tpl->setTitleIcon(ilObject2::_getIcon($obj_id));
		$this->tpl->setTitle(ilObject2::_lookupTitle($obj_id));
		$this->tpl->setDescription(ilObject2::_lookupDescription($obj_id));

		global $ilTabs, $ilLocator;
		/**
		 * @var $ilTabs    ilTabsGUI
		 * @var $ilLocator ilLocatorGUI
		 */
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->pl->txt('tabs_back'), ilLink::_getLink($ref_id));
		$ilLocator->addRepositoryItems($ref_id);
		$this->tpl->setLocator();;

		/**
		 * @var $ilTabs   ilTabsGUI
		 * @var $ilAccess ilAccessHandler
		 */

		$ilTabs->addSubTabTarget('svy_eval_cumulated', $this->ilCtrl->getLinkTargetByClass(array(
			'asrpresentationgui',
			'ilsurveyextevaluationgui'
		), 'evaluation'), array(
			'evaluation',
			'checkEvaluationAccess'
		), '');

		$ilTabs->addSubTabTarget('svy_eval_detail', $this->ilCtrl->getLinkTargetByClass(array(
			'asrpresentationgui',
			'ilsurveyextevaluationgui'
		), 'evaluationdetails'), array( 'evaluationdetails' ), '');

		$ilTabs->addSubTab('my_choice', $this->pl->txt('my_choice'), $this->ilCtrl->getLinkTargetByClass(array(
			'asrpresentationgui',
			'ilsurveyextevaluationgui'
		), 'preview'));

		$ilTabs->addSubTab('my_choice_print', $this->pl->txt('my_choice_print'), $this->ilCtrl->getLinkTargetByClass(array(
			'asrpresentationgui',
			'ilExtSurveyEditorGUI'
		), 'printView'));
	}


	public function executeCommand() {
		global $ilTabs;
		$cmd = $this->ilCtrl->getCmd();
		switch ($cmd) {
			default:
				$ilTabs->setSubTabActive('svy_eval_cumulated');
				break;
			case 'evaluationdetails':
				$ilTabs->setSubTabActive('svy_eval_detail');
				break;
			case 'preview':
				$ilTabs->setSubTabActive('my_choice');
				break;
		}

		/**
		 * @var $ilTabs ilTabsGUI
		 */
		$nextClass = $this->ilCtrl->getNextClass();

		switch ($nextClass) {
			case 'ilsurveyextevaluationgui';
				$ilSurveyExtEvaluationGUI = new ilSurveyExtEvaluationGUI(new ilObjSurvey($_GET['ref_id']));
				$this->ilCtrl->forwardCommand($ilSurveyExtEvaluationGUI);
				break;

			case 'ilextsurveyeditorgui';
				$ilTabs->setSubTabActive('my_choice_print');
				$ilSurveyExtEvaluationGUI = new ilExtSurveyEditorGUI(new ilObjSurveyGUI($_GET['ref_id']));
				$this->ilCtrl->forwardCommand($ilSurveyExtEvaluationGUI);
				break;
		}
	}
}

?>
