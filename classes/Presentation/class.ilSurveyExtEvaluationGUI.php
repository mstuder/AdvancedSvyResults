<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Modules/Survey/classes/class.ilSurveyEvaluationGUI.php');
require_once('./Services/Skill/classes/class.ilSkillManagementSettings.php');
require_once('class.ilSurveyExtResultsCumulatedTableGUI.php');
require_once('./Services/Link/classes/class.ilLink.php');
require_once('./Modules/Survey/classes/class.ilSurveyExecutionGUI.php');

/**
 * Survey evaluation graphical output
 *
 * The ilSurveyExtEvaluationGUI class creates the evaluation output for the ilObjSurveyGUI
 * class. This saves some heap space because the ilObjSurveyGUI class will be
 * smaller.
 *
 * @author            Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id: class.ilSurveyExtEvaluationGUI.php 51392 2014-07-14 08:27:40Z jluetzen $
 *
 * @ingroup           ModulesSurvey
 *
 * @ilCtrl_Calls      ilSurveyExtEvaluationGUI : ilSurveyExecutionGUI
 * @ilCtrl_IsCalledBy ilSurveyExtEvaluationGUI : asrPresentationGUI
 */
class ilSurveyExtEvaluationGUI extends ilSurveyEvaluationGUI {

	/**
	 * @var ilObjSurvey
	 */
	public $object;
	/**
	 * @var ilLanguage
	 */
	public $lng;
	/**
	 * @var HTML_Template_ITX|ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilCtrl
	 */
	public $ctrl;


	/**
	 * @param ilObjSurvey $a_object
	 */
	public function __construct(ilObjSurvey $a_object) {
		parent::ilSurveyEvaluationGUI($a_object);
		$this->lng->loadLanguageModule('survey');
		$this->pl = ilAdvancedSvyResultsPlugin::getInstance();
		$this->ctrl->saveParameter($this, 'ref_id');
		$this->ctrl->setParameterByClass('ilExtSurveyEditorGUI', 'ref_id', $_GET['ref_id']);
		$this->ctrl->setParameterByClass('ilsurveyeditorgui', 'ref_id', $_GET['ref_id']);
	}


	public function initHeader() {
		return;
	}


	/**
	 * @param $name
	 * @param $arguments
	 */
	public function __call($name, $arguments) {
		if ($name == 'exitSurvey') {
			return;
		}

		$ilSurveyExecutionGUI = new ilSurveyExecutionGUI(new ilObjSurvey($_GET['ref_id']));
		$ilSurveyExecutionGUI->preview = true;
		$ilSurveyExecutionGUI->{$name}();
	}


	public function setEvalSubtabs() {
		return;
	}


	/**
	 * @param int $details
	 */
	public function evaluation($details = 0) {
		global $rbacsystem;
		global $ilToolbar;

		// auth
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"])) {
			if (!$rbacsystem->checkAccess("read", $_GET["ref_id"])) {
				ilUtil::sendFailure($this->lng->txt("permission_denied"));

				return;
			}

			switch ($this->object->getEvaluationAccess()) {
				case ilObjSurvey::EVALUATION_ACCESS_OFF:
					ilUtil::sendFailure($this->lng->txt("permission_denied"));

					return;

				case ilObjSurvey::EVALUATION_ACCESS_ALL:
				case ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS:
					if (!$this->checkAnonymizedEvaluationAccess()) {
						ilUtil::sendFailure($this->lng->txt("permission_denied"));

						return;
					}
					break;
			}
		}

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", "Modules/Survey");

		$data = null;

		if ($this->object->get360Mode()) {
			$appr_id = $this->getAppraiseeId();
			$this->addApprSelectionToToolbar();
		}

		if (!$this->object->get360Mode() || $appr_id) {
			$format = new ilSelectInputGUI("", "export_format");
			$format->setOptions(array(
				self::TYPE_XLS  => $this->lng->txt('exp_type_excel'),
				self::TYPE_SPSS => $this->lng->txt('exp_type_csv'),
			));
			$ilToolbar->addInputItem($format);

			$label = new ilSelectInputGUI("", "export_label");
			$label->setOptions(array(
				'label_only'  => $this->lng->txt('export_label_only'),
				'title_only'  => $this->lng->txt('export_title_only'),
				'title_label' => $this->lng->txt('export_title_label'),
			));
			$ilToolbar->addInputItem($label);

			if ($details) {
				$ilToolbar->addFormButton($this->lng->txt("export"), 'exportDetailData');
			} else {
				$ilToolbar->addFormButton($this->lng->txt("export"), 'exportData');
			}

			$finished_ids = null;
			if ($appr_id) {
				$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
				if (!sizeof($finished_ids)) {
					$finished_ids = array( - 1 );
				}
			}

			$questions =& $this->object->getSurveyQuestions();
			$data = array();
			$counter = 1;
			$last_questionblock_id = null;
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/class.asrQuestionTypeMapper.php');
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/Presentation/class.asrPresentation.php');

			$own_results = asrPresentation::getMyResultForRefId($_GET['ref_id']);

			foreach ($questions as $qdata) {
				$question_gui = SurveyQuestion::_instanciateQuestionGUI($qdata["question_id"]);
				$own_results1 = $own_results[$qdata["question_id"]];
				$question_gui = asrQuestionTypeMapper::getInstanceByOriginalClassName($question_gui, $qdata["question_id"], $own_results1);
				$question = $question_gui->object;
				$c = $question->getCumulatedResultData($this->object->getSurveyId(), $counter, $finished_ids);

				if (is_array($c[0])) {
					// keep only "main" entry - sub-items will be handled in tablegui
					// this will enable proper sorting
					$main = array_shift($c);
					$main['own_choice'] = asrQuestionTypeMapper::getTextRepresentation($question_gui);

					foreach ($c as $k => $cc) {
						$c[$k]['own_choice'] = asrQuestionTypeMapper::getTextRepresentation($question_gui, $k);
					}

					$main["subitems"] = $c;
					array_push($data, $main);
				} else {
					$c['own_choice'] = asrQuestionTypeMapper::getTextRepresentation($question_gui);
					array_push($data, $c);
				}

				$counter ++;
				if ($details) {
					// questionblock title handling
					if ($qdata["questionblock_id"] && $qdata["questionblock_id"] != $last_questionblock_id) {
						$qblock = $this->object->getQuestionblock($qdata["questionblock_id"]);
						if ($qblock["show_blocktitle"]) {
							$this->tpl->setCurrentBlock("detail_qblock");
							$this->tpl->setVariable("BLOCKTITLE", $qdata["questionblock_title"]);
							$this->tpl->parseCurrentBlock();
						}

						$last_questionblock_id = $qdata["questionblock_id"];
					}

					$detail = $question_gui->getCumulatedResultsDetails($this->object->getSurveyId(), $counter - 1, $finished_ids);
					$this->tpl->setCurrentBlock("detail");
					$this->tpl->setVariable("DETAIL", $detail);
					$this->tpl->parseCurrentBlock();
				}
			}
		}

		include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsCumulatedTableGUI.php";
		$table_gui = new ilSurveyExtResultsCumulatedTableGUI($this, $details ? 'evaluationdetails' : 'evaluation', $detail);
		$table_gui->setData($data);
		$this->tpl->setVariable('CUMULATED', $table_gui->getHTML());
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'evaluation'));
	}


	/**
	 * @param int $details
	 */
	public function evaluationOld($details = 0) {
		global $rbacsystem;
		global $ilToolbar;
		global $ilUser;

		// auth
		if (!$rbacsystem->checkAccess('write', $_GET['ref_id'])) {
			if (!$rbacsystem->checkAccess('read', $_GET['ref_id'])) {
				ilUtil::sendFailure($this->lng->txt('permission_denied'));

				return;
			}

			switch ($this->object->getEvaluationAccess()) {
				case ilObjSurvey::EVALUATION_ACCESS_OFF:
					ilUtil::sendFailure($this->lng->txt('permission_denied'));

					return;

				case ilObjSurvey::EVALUATION_ACCESS_ALL:
				case ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS:
					if (!$this->checkAnonymizedEvaluationAccess()) {
						ilUtil::sendFailure($this->lng->txt('permission_denied'));

						return;
					}
					break;
			}
		}

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.il_svy_svy_evaluation.html', 'Modules/Survey');

		$data = null;

		if ($this->object->get360Mode()) {
			$appr_id = $this->getAppraiseeId();
			$this->addApprSelectionToToolbar();
		}

		if (!$this->object->get360Mode() || $appr_id) {
			$format = new ilSelectInputGUI('', 'export_format');
			$format->setOptions(array(
				self::TYPE_XLS  => $this->lng->txt('exp_type_excel'),
				self::TYPE_SPSS => $this->lng->txt('exp_type_csv'),
			));
			$ilToolbar->addInputItem($format);

			$label = new ilSelectInputGUI('', 'export_label');
			$label->setOptions(array(
				'label_only'  => $this->lng->txt('export_label_only'),
				'title_only'  => $this->lng->txt('export_title_only'),
				'title_label' => $this->lng->txt('export_title_label'),
			));
			$ilToolbar->addInputItem($label);

			if ($details) {
				$ilToolbar->addFormButton($this->lng->txt('export'), 'exportDetailData');
			} else {
				$ilToolbar->addFormButton($this->lng->txt('export'), 'exportData');
			}

			$finished_ids = null;
			if ($appr_id) {
				$finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
				if (!sizeof($finished_ids)) {
					$finished_ids = array( - 1 );
				}
			}
			$userResults = $this->object->getUserSpecificResults(null);
			$activeID = $this->object->getActiveID($ilUser->getId(), null, 0);

			$questions =& $this->object->getSurveyQuestions();
			$data = array();
			$counter = 1;
			$last_questionblock_id = null;
			foreach ($questions as $qdata) {
				include_once './Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php';
				$question_gui = SurveyQuestion::_instanciateQuestionGUI($qdata['question_id']);
				$question = $question_gui->object;
				$c = $question->getCumulatedResultData($this->object->getSurveyId(), $counter, $finished_ids);

				if (is_array($c[0])) {
					// keep only 'main' entry - sub-items will be handled in tablegui
					// this will enable proper sorting
					$main = array_shift($c);
					$main['subitems'] = $c;
					array_push($data, $main);
				} else {
					$found = $userResults[$qdata['question_id']][$activeID];

					if (is_array($found)) {
						$text = implode('<br />', $found);
					} else {
						$text = $found;
					}
					if (!$text) {
						$text = '&nbsp;';
					}

					$c['own_choice'] = $text;
					array_push($data, $c);
				}
				$counter ++;
				if ($details) {
					// questionblock title handling
					if ($qdata['questionblock_id'] && $qdata['questionblock_id'] != $last_questionblock_id) {
						$qblock = $this->object->getQuestionblock($qdata['questionblock_id']);
						if ($qblock['show_blocktitle']) {
							$this->tpl->setCurrentBlock('detail_qblock');
							$this->tpl->setVariable('BLOCKTITLE', $qdata['questionblock_title']);
							$this->tpl->parseCurrentBlock();
						}

						$last_questionblock_id = $qdata['questionblock_id'];
					}
					require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/Presentation/class.asrChartGUI.php');
					/**
					 * @var  $question_gui SurveyMultipleChoiceQuestionGUI
					 */
					$detail = $question_gui->getCumulatedResultsDetails($this->object->getSurveyId(), $counter - 1, $finished_ids);

					$doc = new DOMDocument();
					$doc->formatOutput = true;
					$doc->loadHTML($detail);
					$doc->createDocumentFragment();
					$xp = new DOMXPath($doc);
					$old_node = $xp->query('//div[@id="ilChartsvy_ch_34"]')->item(0);

					if ($old_node instanceof DOMElement) {
						$asrChartGUI = new asrChartGUI($question_gui);
						//						echo '<pre>' . print_r($userResults, 1) . '</pre>';
						$question_id = $qdata['question_id'];

						//						var_dump($question_id); // FSX
						//						var_dump($activeID); // FSX
						//						var_dump($userResults); // FSX

						$new_chart = $asrChartGUI->renderChart($this->object->getSurveyId(), $counter
						                                                                     - 1, $finished_ids, $userResults[$question_id][$activeID]);
						$replacement = $doc->createDocumentFragment();
						$replacement->appendXML($new_chart);
						$old_node->parentNode->replaceChild($replacement, $old_node);
						$detail = $doc->saveXml($doc->documentElement);
					}

					$this->tpl->setCurrentBlock('detail');
					$this->tpl->setVariable('DETAIL', $detail);
					$this->tpl->parseCurrentBlock();
				}
			}
		}

		include_once './Modules/Survey/classes/tables/class.ilSurveyResultsCumulatedTableGUI.php';
		$table_gui = new ilSurveyExtResultsCumulatedTableGUI($this, $details ? 'evaluationdetails' : 'evaluation', $detail);
		$table_gui->setData($data);
		$this->tpl->setVariable('CUMULATED', $table_gui->getHTML());
		$this->tpl->addCss('./Modules/Survey/templates/default/survey_print.css', 'print');
		$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'evaluation'));
	}
}

?>