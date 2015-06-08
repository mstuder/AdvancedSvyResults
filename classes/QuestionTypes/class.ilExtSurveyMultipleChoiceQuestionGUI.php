<?php
require_once('./Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/interface.asrQuestion.php');

/**
 * Class ilExtSurveyMultipleChoiceQuestionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilExtSurveyMultipleChoiceQuestionGUI extends SurveyMultipleChoiceQuestionGUI implements asrQuestion {

	/**
	 * @var array
	 */
	protected $own_results = array();
	/**
	 * @var array
	 */
	protected $own_results_raw = array();


	/**
	 * @param int $a_id
	 * @param     $own_results
	 */
	public function __construct($a_id = - 1, $own_results) {
		parent::__construct($a_id);
		$this->own_results = $own_results['num'][0];
		$this->own_results_raw = $own_results;
		//		echo '<pre>' . print_r($this->own_results_raw, 1) . '</pre>';
	}


	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL) {
		$string = '';

		foreach ($this->own_results_raw['num2'][0] as $k => $own) {
			$num = $this->own_results_raw['num2'][0][$k];
			$title = $this->own_results_raw['title'][0][$k];
			$string .= $num . ' - ' . $title . '<br>';
		}

		return $string;
	}


	/**
	 * Creates a HTML representation of the question
	 *
	 * @access private
	 */
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = NULL) {
		$template = new ilTemplate("tpl.il_svy_qpl_mc_printview.html", true, true, "Modules/SurveyQuestionPool");
		switch ($this->object->getOrientation()) {
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					/**
					 * @var $cat ilSurveyCategory
					 */

					$cat = $this->object->categories->getCategory($i);
					if ($cat->other) {
						$template->setCurrentBlock("other_row");
						$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("OTHER_ANSWER", "&nbsp;");
						$template->parseCurrentBlock();
					} else {
						$template->setCurrentBlock("mc_row");
						if (in_array($i, $this->own_results)) {
							$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_checked.png")));
						} else {
							$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
						}
						$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($cat->title));
						$template->parseCurrentBlock();
					}
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$template->setCurrentBlock("checkbox_col");
					$template->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
					$template->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
					$template->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other) {
						$template->setCurrentBlock("other_text_col");
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("OTHER_ANSWER", "&nbsp;");
						$template->parseCurrentBlock();
					} else {
						$template->setCurrentBlock("text_col");
						$template->setVariable("TEXT_MC", ilUtil::prepareFormOutput($cat->title));
						$template->parseCurrentBlock();
					}
				}
				break;
		}

		if ($this->object->use_min_answers) {
			$template->setCurrentBlock('min_max_msg');
			if ($this->object->nr_min_answers > 0 && $this->object->nr_max_answers > 0) {
				$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_max_nr_answers'), $this->object->nr_min_answers, $this->object->nr_max_answers));
			} else {
				if ($this->object->nr_min_answers > 0) {
					$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_min_nr_answers'), $this->object->nr_min_answers));
				} else {
					if ($this->object->nr_max_answers > 0) {
						$template->setVariable('MIN_MAX_MSG', sprintf($this->lng->txt('msg_max_nr_answers'), $this->object->nr_max_answers));
					}
				}
			}
			$template->parseCurrentBlock();
		}
		if ($show_questiontext) {
			$this->outQuestionText($template);
		}
		if ($question_title) {
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$template->parseCurrentBlock();

		return $template->get();
	}

	protected function renderChart($a_id, $a_variables) {
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart($a_id, 700, 400);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);
		$chart->setYAxisToInteger(true);

		$data = new ilChartData("bars");
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.5, "center");

		$data_own = new ilChartData("bars");
		$data_own->setLabel(ilAdvancedSvyResultsPlugin::getInstance()->txt("chart_my_answer"));
		$data_own->setBarOptions(0.5, "center");

		foreach ($this->own_results_raw['num'][0] as $k =>$v) {
			$data_own->addPoint($this->own_results_raw['num'][0][$k], 1);
		}

		$max = 5;

		if (sizeof($a_variables) <= $max) {
			if ($a_variables) {
				$labels = array();
				foreach ($a_variables as $idx => $points) {
					$data->addPoint($idx, $points["selected"]);
					$labels[$idx] = ($idx + 1) . ". " . ilUtil::prepareFormOutput($points["title"]);
				}
				$chart->addData($data);
				$chart->addData($data_own);

				$chart->setTicks($labels, false, true);
			}

			return "<div style=\"margin:10px\">" . $chart->getHTML() . "</div>";
		} else {
			$chart_legend = array();
			$labels = array();
			foreach ($a_variables as $idx => $points) {
				$data->addPoint($idx, $points["selected"]);
				$labels[$idx] = ($idx + 1) . ".";
				$chart_legend[($idx + 1)] = ilUtil::prepareFormOutput($points["title"]);
			}
			$chart->addData($data);

			$chart->setTicks($labels, false, true);

			$legend = "<table>";
			foreach ($chart_legend as $number => $caption) {
				$legend .= "<tr valign=\"top\"><td>" . $number . ".</td><td>" . $caption . "</td></tr>";
			}
			$legend .= "</table>";

			return "<div style=\"margin:10px\"><table><tr valign=\"bottom\"><td>" . $chart->getHTML()
			. "</td><td class=\"small\" style=\"padding-left:15px\">" . $legend . "</td></tr></table></div>";
		}
	}
}

?>
