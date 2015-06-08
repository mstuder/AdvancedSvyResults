<?php
require_once('./Modules/SurveyQuestionPool/classes/class.SurveyMetricQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/interface.asrQuestion.php');

/**
 * Class ilExtSurveyMetricQuestionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilExtSurveyMetricQuestionGUI extends SurveyMetricQuestionGUI implements asrQuestion {

	/**
	 * @var array
	 */
	protected $own_results = array();


	/**
	 * @param int $a_id
	 * @param     $own_results
	 */
	public function __construct($a_id = - 1, $own_results) {
		parent::__construct($a_id);
		$this->own_results = $own_results['num'];
	}


	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL) {
		return $this->own_results[0][0];
	}


	/**
	 * @param $a_id
	 * @param $a_values
	 *
	 * @return string
	 */
	protected function renderChart($a_id, $a_values) {
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart($a_id, 700, 400);
		$legend = new ilChartLegend();
		$chart->setLegend($legend);

		$data = new ilChartData("bars");
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.1, "center");

		$data_own = new ilChartData("bars");
		$data_own->setLabel(ilAdvancedSvyResultsPlugin::getInstance()->txt("chart_my_answer"));
		$data_own->setBarOptions(0.1, "center");

		$data_own->addPoint($this->own_results[0][0], 1);

		if ($a_values) {
			$labels = array();
			foreach ($a_values as $idx => $answer) {
				$data->addPoint($answer["value"], $answer["selected"]);
				$labels[$answer["value"]] = $answer["value"];
			}
			$chart->addData($data);
			$chart->addData($data_own);

			$chart->setTicks($labels, false, true);
		}

		return "<div style=\"margin:10px\">" . $chart->getHTML() . "</div>";
	}


	/**
	 * Creates a HTML representation of the question
	 *
	 * Creates a HTML representation of the question
	 *
	 * @access private
	 */
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = NULL) {
		$template = new ilTemplate("tpl.il_svy_qpl_metric_printview.html", true, true, "Modules/SurveyQuestionPool");
		$template->setVariable("MIN_MAX", $this->object->getMinMaxText());

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
		if ($show_questiontext) {
			$this->outQuestionText($template);
		}
		if ($question_title) {
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
		$template->setVariable("QUESTION_ID", $this->object->getId());

		$solution_text = $this->getTextRepresenation();
		$len = 10;
		for ($i = 0; $i < 10; $i ++) {
			$solution_text .= "&#160;";
		}
		$template->setVariable("TEXT_SOLUTION", $solution_text);

		$template->parseCurrentBlock();

		return $template->get();
	}
}

?>
