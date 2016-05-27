<?php
require_once('./Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/interface.asrQuestion.php');

/**
 * Class ilExtSurveyTextQuestionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilExtSurveySingleChoiceQuestionGUI extends SurveySingleChoiceQuestionGUI implements asrQuestion {

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
	}


	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL) {

		$num = $this->own_results_raw['num2'][0][0];
		$title = $this->own_results_raw['title'][0][0];

		return $num . ' - ' . $title;
	}


	/**
	 * Creates a HTML representation of the question
	 *
	 * @access private
	 */
	function getPrintView($question_title = 1, $show_questiontext = 1, $survey_id = NULL) {
		$template = new ilTemplate("tpl.il_svy_qpl_sc_printview.html", true, true, "Modules/SurveyQuestionPool");

		switch ($this->object->orientation) {
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other) {
						$template->setCurrentBlock("other_row");
						$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
						$template->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("OTHER_ANSWER", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
						$template->parseCurrentBlock();
					} else {
						$template->setCurrentBlock("row");
						if (in_array($i, $this->own_results)) {
							$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_checked.png")));
						} else {
							$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
						}

						$template->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
						$template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($cat->title));
						$template->parseCurrentBlock();
					}
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$template->setCurrentBlock("radio_col");
					$template->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
					$template->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
					$template->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
					$template->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$cat = $this->object->categories->getCategory($i);
					if ($cat->other) {
						$template->setCurrentBlock("other_text_col");
						$template->setVariable("OTHER_LABEL", ilUtil::prepareFormOutput($cat->title));
						$template->setVariable("OTHER_ANSWER", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
						$template->parseCurrentBlock();
					} else {
						$template->setCurrentBlock("text_col");
						$template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($cat->title));
						$template->parseCurrentBlock();
					}
				}
				break;
			case 2:
				// combobox output
				for ($i = 0; $i < $this->object->categories->getCategoryCount(); $i ++) {
					$cat = $this->object->categories->getCategory($i);
					$template->setCurrentBlock("comborow");
					$template->setVariable("TEXT_SC", ilUtil::prepareFormOutput($cat->title));
					$template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);
					if (is_array($working_data)) {
						if (strcmp($working_data[0]["value"], "") != 0) {
							if ($working_data[0]["value"] == $i) {
								$template->setVariable("SELECTED_SC", " selected=\"selected\"");
							}
						}
					}
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock("combooutput");
				$template->setVariable("QUESTION_ID", $this->object->getId());
				$template->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
				$template->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
				$template->parseCurrentBlock();
				break;
		}
		if ($question_title) {
			$template->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		if ($show_questiontext) {
			$this->outQuestionText($template);
		}
		$template->parseCurrentBlock();

		return $template->get();
	}



	/**
	 * @param $a_id
	 * @param $a_variables
	 *
	 * @return string
	 */
	protected function renderChart($a_id, $a_variables) {
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
		$chart->setSize('700px', '400px');

		$legend = new ilChartLegend();
		$chart->setLegend($legend);

		$data = new ilChartDataBars();
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.5, "center");

		$data_own = new ilChartDataBars();
		$data_own->setLabel(ilAdvancedSvyResultsPlugin::getInstance()->txt("chart_my_answer"));
		$data_own->setBarOptions(0.5, "center");

		$data_own->addPoint($this->own_results[0][0], 1);

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
