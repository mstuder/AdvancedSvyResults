<?php
require_once('./Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AdvancedSvyResults/classes/QuestionTypes/interface.asrQuestion.php');

/**
 * Class ilExtSurveyMatrixQuestionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilExtSurveyMatrixQuestionGUI extends SurveyMatrixQuestionGUI implements asrQuestion {

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

		$this->own_results = $own_results['num'];
		$this->own_results_raw = $own_results;
	}


	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL) {
		if ($row === NULL) {
			return '';
		}

		$num = $this->own_results_raw['num2'][$row][0];
		$title = $this->own_results_raw['title'][$row][0];

		return $num . ' - ' . $title;
	}


	/**
	 * Creates the detailed output of the cumulated results for the question
	 *
	 * @param integer $survey_id The database ID of the survey
	 * @param integer $counter   The counter of the question position in the survey
	 *
	 * @return string HTML text with the cumulated results
	 * @access private
	 */
	function getCumulatedResultsDetails($survey_id, $counter, $finished_ids) {
		if (count($this->cumulated) == 0) {
			if (!$finished_ids) {
				include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
				$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			} else {
				$nr_of_users = sizeof($finished_ids);
			}
			$this->cumulated =& $this->object->getCumulatedResults($survey_id, $nr_of_users, $finished_ids);
		}

		$cumulated_count = 0;
		foreach ($this->cumulated as $key => $value) {
			if (is_numeric($key)) {
				$cumulated_count ++;
			}
		}

		$output = "";

		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_detail.html", true, true, "Modules/Survey");

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question"));
		$questiontext = $this->object->getQuestiontext();
		$template->setVariable("TEXT_OPTION_VALUE", $this->object->prepareTextareaOutput($questiontext, true));
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("question_type"));
		$template->setVariable("TEXT_OPTION_VALUE",
			$this->lng->txt($this->getQuestionType()) . " (" . $cumulated_count . " " . $this->lng->txt("rows") . ")");
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_ANSWERED"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["USERS_SKIPPED"]);
		$template->parseCurrentBlock();
		/*
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE"]);
		$template->parseCurrentBlock();
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MODE_NR_OF_SELECTIONS"]);
	    $template->parseCurrentBlock();
		 */
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->cumulated["TOTAL"]["MEDIAN"]);
		$template->parseCurrentBlock();

		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
		$columns = "";
		foreach ($this->cumulated["TOTAL"]["variables"] as $key => $value) {
			$columns .= "<li>" . $value["title"] . ": n=" . $value["selected"] . " (" . sprintf("%.2f", 100 * $value["percentage"]) . "%)</li>";
		}
		$columns = "<ol>$columns</ol>";
		$template->setVariable("TEXT_OPTION_VALUE", $columns);
		$template->parseCurrentBlock();

		// total chart
		$template->setCurrentBlock("detail_row");
		$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
		$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_" . $this->object->getId()
			. "_total", $this->cumulated["TOTAL"]["variables"], true));
		$template->parseCurrentBlock();

		$template->setVariable("QUESTION_TITLE", "$counter. " . $this->object->getTitle());

		$output .= $template->get();

		foreach ($this->cumulated as $key => $value) {
			if (is_numeric($key)) {
				$template = new ilTemplate("tpl.il_svy_svy_cumulated_results_detail.html", true, true, "Modules/Survey");

				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_answered"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_ANSWERED"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("users_skipped"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["USERS_SKIPPED"]);
				$template->parseCurrentBlock();
				/*
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE"]);
				$template->parseCurrentBlock();
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("mode_nr_of_selections"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MODE_NR_OF_SELECTIONS"]);
				$template->parseCurrentBlock();
				*/
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("median"));
				$template->setVariable("TEXT_OPTION_VALUE", $value["MEDIAN"]);
				$template->parseCurrentBlock();

				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("categories"));
				$columns = "";
				foreach ($value["variables"] as $cvalue) {
					$columns .= "<li>" . $cvalue["title"] . ": n=" . $cvalue["selected"] . " (" . sprintf("%.2f", 100 * $cvalue["percentage"])
						. "%)</li>";
				}
				$columns = "<ol>" . $columns . "</ol>";
				$template->setVariable("TEXT_OPTION_VALUE", $columns);
				$template->parseCurrentBlock();

				// add text answers to detailed results
				if (is_array($value["textanswers"])) {
					$template->setCurrentBlock("detail_row");
					$template->setVariable("TEXT_OPTION", $this->lng->txt("freetext_answers"));
					$html = "";
					foreach ($value["textanswers"] as $tkey => $answers) {
						$html .= $value["variables"][$tkey]["title"] . "\n";
						$html .= "<ul>\n";
						foreach ($answers as $answer) {
							$html .= "<li>" . preg_replace("/\n/", "<br>\n", $answer) . "</li>\n";
						}
						$html .= "</ul>\n";
					}
					$template->setVariable("TEXT_OPTION_VALUE", $html);
					$template->parseCurrentBlock();
				}

				// chart
				$template->setCurrentBlock("detail_row");
				$template->setVariable("TEXT_OPTION", $this->lng->txt("chart"));
				$template->setVariable("TEXT_OPTION_VALUE", $this->renderChart("svy_ch_" . $this->object->getId() . "_"
						. $key, $value["variables"], $key));
				$template->parseCurrentBlock();

				$template->setVariable("QUESTION_SUBTITLE", $counter . "." . ($key + 1) . " "
					. $this->object->prepareTextareaOutput($value["ROW"], true));

				$output .= $template->get();
			}
		}

		return $output;
	}


	/**
	 * @param      $a_id
	 * @param      $a_variables
	 * @param null $row
	 *
	 * @return string
	 */
	protected function renderChart($a_id, $a_variables, $row = NULL) {
		include_once "Services/Chart/classes/class.ilChart.php";
		require_once('./Services/Chart/classes/class.ilChartDataBars.php');
		$chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_id);
		$chart->setSize('700px', '400px');

		$legend = new ilChartLegend();
		$chart->setLegend($legend);
//		$chart->setYAxisToInteger(true);

		$data = new ilChartDataBars();
		$data->setLabel($this->lng->txt("users_answered"));
		$data->setBarOptions(0.5, "center");

		$data_own = new ilChartDataBars();
		$data_own->setLabel(ilAdvancedSvyResultsPlugin::getInstance()->txt("chart_my_answer"));
		$data_own->setBarOptions(0.5, "center");
		if ($row === NULL) {
			$temp = array();
			foreach ($this->own_results_raw['num'] as $row => $v) {
				$next = $v[0];
				$temp[$next] ++;
			}
			foreach ($temp as $k => $v) {
				$data_own->addPoint($k, $v);
			}
		} else {
			$a_x = $this->own_results_raw['num'][$row][0];
			$data_own->addPoint($a_x, 1);
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


	/**
	 * Creates a HTML representation of the question
	 *
	 * @access private
	 */
	function getPrintView($question_title = 1, $show_questiontext = 1) {
		$layout = $this->object->getLayout();
		$neutralstyle = "3px solid #808080";
		$bordercolor = "#808080";
		$template = new ilTemplate("tpl.il_svy_qpl_matrix_printview.html", true, true, "Modules/SurveyQuestionPool");

		if ($this->show_layout_row) {
			$layout_row = $this->getLayoutRow();
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $layout_row);
			$template->parseCurrentBlock();
		}

		$tplheaders = new ilTemplate("tpl.il_svy_out_matrix_columnheaders.html", true, true, "Modules/SurveyQuestionPool");
		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1)))) {
			$tplheaders->setCurrentBlock("bipolar_start");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective1"], "%"));
			if (count($style) > 0) {
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}
		// column headers
		for ($i = 0; $i < $this->object->getColumnCount(); $i ++) {
			$cat = $this->object->getColumn($i);
			if ($cat->neutral) {
				$tplheaders->setCurrentBlock("neutral_column_header");
				$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($cat->title));
				$tplheaders->setVariable("CLASS", "rsep");
				$style = array();
				array_push($style, sprintf("width: %.2f%s!important", $layout["percent_neutral"], "%"));
				if ($this->object->getNeutralColumnSeparator()) {
					array_push($style, "border-left: $neutralstyle!important;");
				}
				if (count($style) > 0) {
					$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplheaders->parseCurrentBlock();
			} else {
				$style = array();
				if ($this->object->getColumnSeparators() == 1) {
					if (($i < $this->object->getColumnCount() - 1)) {
						array_push($style, "border-right: 1px solid $bordercolor!important");
					}
				}
				array_push($style, sprintf("width: %.2f%s!important", $layout["percent_columns"] / $this->object->getColumnCount(), "%"));
				$tplheaders->setCurrentBlock("column_header");
				$tplheaders->setVariable("TEXT", ilUtil::prepareFormOutput($cat->title));
				$tplheaders->setVariable("CLASS", "center");
				if (count($style) > 0) {
					$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
				}
				$tplheaders->parseCurrentBlock();
			}
		}

		if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1)))) {
			$tplheaders->setCurrentBlock("bipolar_end");
			$style = array();
			array_push($style, sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective2"], "%"));
			if (count($style) > 0) {
				$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
			}
			$tplheaders->parseCurrentBlock();
		}

		$style = array();
		array_push($style, sprintf("width: %.2f%s!important", $layout["percent_row"], "%"));
		if (count($style) > 0) {
			$tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
		}

		$template->setCurrentBlock("matrix_row");
		$template->setVariable("ROW", $tplheaders->get());
		$template->parseCurrentBlock();

		$rowclass = array( "tblrow1", "tblrow2" );

		for ($i = 0; $i < $this->object->getRowCount(); $i ++) {
			$rowobj = $this->object->getRow($i);
			$tplrow = new ilTemplate("tpl.il_svy_qpl_matrix_printview_row.html", true, true, "Modules/SurveyQuestionPool");
			for ($j = 0; $j < $this->object->getColumnCount(); $j ++) {
				$cat = $this->object->getColumn($j);
				if (($i == 0) && ($j == 0)) {
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1)))) {
						$tplrow->setCurrentBlock("bipolar_start");
						$tplrow->setVariable("TEXT_BIPOLAR_START", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(0)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				if (($i == 0) && ($j == $this->object->getColumnCount() - 1)) {
					if ((strlen($this->object->getBipolarAdjective(0))) && (strlen($this->object->getBipolarAdjective(1)))) {
						$tplrow->setCurrentBlock("bipolar_end");
						$tplrow->setVariable("TEXT_BIPOLAR_END", ilUtil::prepareFormOutput($this->object->getBipolarAdjective(1)));
						$tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
						$tplrow->parseCurrentBlock();
					}
				}
				switch ($this->object->getSubtype()) {
					case 0:
						if ($cat->neutral) {
							$tplrow->setCurrentBlock("neutral_radiobutton");
							$tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
							$tplrow->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
							$tplrow->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
							$tplrow->parseCurrentBlock();
						} else {
							$tplrow->setCurrentBlock("radiobutton");
							$tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
							// FSX
							if($this->own_results_raw['num'][$i][0] === $j) {
								$tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_checked.png")));
							}

							$tplrow->setVariable("ALT_RADIO", $this->lng->txt("unchecked"));
							$tplrow->setVariable("TITLE_RADIO", $this->lng->txt("unchecked"));
							$tplrow->parseCurrentBlock();
						}
						break;
					case 1:
						if ($cat->neutral) {
							$tplrow->setCurrentBlock("neutral_checkbox");
							$tplrow->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
							$tplrow->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
							$tplrow->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
							$tplrow->parseCurrentBlock();
						} else {
							$tplrow->setCurrentBlock("checkbox");
							$tplrow->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
							$tplrow->setVariable("ALT_CHECKBOX", $this->lng->txt("unchecked"));
							$tplrow->setVariable("TITLE_CHECKBOX", $this->lng->txt("unchecked"));
							$tplrow->parseCurrentBlock();
						}
						break;
				}
				if ($cat->neutral) {
					$tplrow->setCurrentBlock("neutral_answer");
					$style = array();
					if ($this->object->getNeutralColumnSeparator()) {
						array_push($style, "border-left: $neutralstyle!important");
					}
					if ($this->object->getColumnSeparators() == 1) {
						if ($j < $this->object->getColumnCount() - 1) {
							array_push($style, "border-right: 1px solid $bordercolor!important");
						}
					}

					if ($this->object->getRowSeparators() == 1) {
						if ($i < $this->object->getRowCount() - 1) {
							array_push($style, "border-bottom: 1px solid $bordercolor!important");
						}
					}
					if (count($style)) {
						$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
					}
					$tplrow->parseCurrentBlock();
				} else {
					$tplrow->setCurrentBlock("answer");
					$style = array();

					if ($this->object->getColumnSeparators() == 1) {
						if ($j < $this->object->getColumnCount() - 1) {
							array_push($style, "border-right: 1px solid $bordercolor!important");
						}
					}

					if ($this->object->getRowSeparators() == 1) {
						if ($i < $this->object->getRowCount() - 1) {
							array_push($style, "border-bottom: 1px solid $bordercolor!important");
						}
					}
					if (count($style)) {
						$tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
					}
					$tplrow->parseCurrentBlock();
				}
			}

			if ($rowobj->other) {
				$tplrow->setCurrentBlock("text_other");
				$tplrow->setVariable("TEXT_OTHER", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
				$tplrow->parseCurrentBlock();
			}

			$tplrow->setVariable("TEXT_ROW", ilUtil::prepareFormOutput($rowobj->title));
			$tplrow->setVariable("ROWCLASS", $rowclass[$i % 2]);
			if ($this->object->getRowSeparators() == 1) {
				if ($i < $this->object->getRowCount() - 1) {
					$tplrow->setVariable("STYLE", " style=\"border-bottom: 1px solid $bordercolor!important\"");
				}
			}
			$template->setCurrentBlock("matrix_row");
			$template->setVariable("ROW", $tplrow->get());
			$template->parseCurrentBlock();
		}

		if ($question_title) {
			$template->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		}
		$template->setCurrentBlock();
		if ($show_questiontext) {
			$this->outQuestionText($template);
		}
		$template->parseCurrentBlock();

		return $template->get();
	}
}

?>
