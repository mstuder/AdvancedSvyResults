<?php
require_once('./Modules/Survey/classes/class.ilSurveyEditorGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/class.asrQuestionTypeMapper.php');
/**
 * Class ilExtSurveyEditorGUI
 *
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @version      1.0.0
 *
 * @ilCtrl_Calls ilExtSurveyEditorGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
 * @ilCtrl_Calls ilExtSurveyEditorGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
 * @ilCtrl_Calls ilExtSurveyEditorGUI: SurveyMatrixQuestionGUI, ilSurveyPageGUI
 *
 * @ingroup      ModulesSurvey
 */
class ilExtSurveyEditorGUI extends ilSurveyEditorGUI {

	/**
	 * @param $a_cmd
	 *
	 * @return bool|void
	 */
	protected function questionsSubtabs($a_cmd) {
		return false;
	}




	/**
	 * Creates a print view of the survey questions
	 *
	 * @access public
	 */
	public  function printViewObject() {
		$this->questionsSubtabs("print");
		$template = new ilTemplate("tpl.il_svy_svy_printview.html", true, true, "Modules/Survey");

		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if (ilRPCServerSettings::getInstance()->isEnabled()) {
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "printView"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");

		$pages =& $this->object->getSurveyPages();


		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/Presentation/class.asrPresentation.php');
		$my_results = asrPresentation::getMyResultForRefId($_GET['ref_id']);
//		echo '<pre>' . print_r($my_results, 1) . '</pre>';

		foreach ($pages as $page) {
			if (count($page) > 0) {
				foreach ($page as $question) {
					$questionGUI = $this->object->getQuestionGUI($question["type_tag"], $question["question_id"]);

					$questionGUI = asrQuestionTypeMapper::getInstanceByOriginalClassName($questionGUI, $question["question_id"], $my_results[$question["question_id"]]);

					if (is_object($questionGUI)) {
						if (strlen($question["heading"])) {
							$template->setCurrentBlock("textblock");
							$template->setVariable("TEXTBLOCK", $question["heading"]);
							$template->parseCurrentBlock();
						}
						/***
						 * @var $questionGUI SurveySingleChoiceQuestionGUI
						 */
						$template->setCurrentBlock("question");
						$template->setVariable("QUESTION_DATA", $questionGUI->getPrintView($this->object->getShowQuestionTitles(), $question["questionblock_show_questiontext"], $this->object->getSurveyId()));
						$template->parseCurrentBlock();
					}
				}
				if (count($page) > 1 && $page[0]["questionblock_show_blocktitle"]) {
					$template->setCurrentBlock("page");
					$template->setVariable("BLOCKTITLE", $page[0]["questionblock_title"]);
					$template->parseCurrentBlock();
				} else {
					$template->setCurrentBlock("page");
					$template->parseCurrentBlock();
				}
			}
		}
		$this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1)) {
			$printbody = new ilTemplate("tpl.il_as_tst_print_body.html", true, true, "Modules/Test");
			$printbody->setVariable("TITLE", sprintf($this->lng->txt("tst_result_user_name"), $uname));
			$printbody->setVariable("ADM_CONTENT", $template->get());
			$printoutput = $printbody->get();
			$printoutput = preg_replace("/href=\".*?\"/", "", $printoutput);
			$fo = $this->object->processPrintoutput2FO($printoutput);
			// #11436
			if (!$fo || !$this->object->deliverPDFfromFO($fo)) {
				ilUtil::sendFailure($this->lng->txt("msg_failed"), true);
				$this->ctrl->redirect($this, "printView");
			}
		} else {
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
		}
	}
}

?>
