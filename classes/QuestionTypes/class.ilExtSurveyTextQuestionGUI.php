<?php
require_once('./Modules/SurveyQuestionPool/classes/class.SurveyTextQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/interface.asrQuestion.php');
/**
 * Class ilExtSurveyTextQuestionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilExtSurveyTextQuestionGUI extends SurveyTextQuestionGUI implements  asrQuestion{

	/**
	 * @param int $a_id
	 * @param     $own_results
	 */
	public function __construct($a_id = - 1, $own_results) {
		parent::__construct($a_id);
		$this->own_results = $own_results['text'][0];
	}

	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL) {
		return $this->own_results ;
	}


	/**
	 * @param int  $question_title
	 * @param int  $show_questiontext
	 * @param null $survey_id
	 *
	 * @return string
	 */
	public function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = NULL) {
		$ilAdvancedSvyResultsPlugin = ilAdvancedSvyResultsPlugin::getInstance();
		$this->tpl->addCss($ilAdvancedSvyResultsPlugin->getDirectory() . '/templates/css/input_types.css');

		$template = $ilAdvancedSvyResultsPlugin->getTemplate('default/tpl.il_svy_qpl_text_printview.html');

		if ($show_questiontext) {
			$this->outQuestionText($template);
		}
		if ($question_title) {
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("TEXTBOX_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("textbox.png")));
		$template->setVariable("TEXTBOX", $this->lng->txt("textbox"));
		$template->setVariable("ANSWER", $this->own_results);

		$template->setVariable("TEXTBOX_WIDTH", $this->object->getTextWidth() * 16);
		$template->setVariable("TEXTBOX_HEIGHT", $this->object->getTextHeight() * 16);
		$template->setVariable("QUESTION_ID", $this->object->getId());
		if ($this->object->getMaxChars()) {
			$template->setVariable("TEXT_MAXCHARS", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxChars()));
		}

		return $template->get();
	}
}

?>
