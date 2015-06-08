<?php

/**
 * Class asrChartGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class asrChartGUI {

	/**
	 * @var string
	 */
	protected $type = NULL;
	/**
	 * @var SurveyQuestionGUI
	 */
	protected $SurveyQuestionGUI = NULL;


	/**
	 * @param SurveyQuestionGUI $SurveyQuestionGUI
	 */
	public function __construct(SurveyQuestionGUI $SurveyQuestionGUI) {
		$this->pl = ilAdvancedSvyResultsPlugin::getInstance();
		$this->type = get_class($SurveyQuestionGUI);
		$this->SurveyQuestionGUI = $SurveyQuestionGUI;
	}


	/**
	 * @param $survey_id
	 * @param $nr_of_users
	 * @param $finished_ids
	 *
	 * @return string
	 */
	public function renderChart($survey_id, $nr_of_users, $finished_ids, $own_result) {
		global $lng;

		$a_id = $this->SurveyQuestionGUI->object->getId();
		$cumulated = $this->SurveyQuestionGUI->object->getCumulatedResults($survey_id, $nr_of_users, $finished_ids);


		$a_variables = $cumulated["variables"];
//		var_dump(); // FSX

//		echo '<pre>' . print_r($a_variables, 1) . '</pre>';
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart($a_id, 700, 400);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);
		$chart->setYAxisToInteger(true);

		$data = new ilChartData("bars");
		$data->setLabel($lng->txt("users_answered"));
		$data->setBarOptions(0.5, "center");

		$data_own = new ilChartData("bars");
		$data_own->setLabel($this->pl->txt("chart_my_answer"));
		$data_own->setBarOptions(0.5, "center");


		if (is_array($own_result)) {
			$own_result = array_keys($own_result);
			$own_result = $own_result[0];
		}

//		$data_own->addPoint($own_result, 1);
		$data_own->addPoint(1, 1);

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

				$chart->setTicks($labels, true, true);
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
