<?php
require_once('class.ilExtSurveyMultipleChoiceQuestionGUI.php');
require_once('class.ilExtSurveyTextQuestionGUI.php');
require_once('class.ilExtSurveySingleChoiceQuestionGUI.php');
require_once('class.ilExtSurveyMetricQuestionGUI.php');
require_once('class.ilExtSurveyMatrixQuestionGUI.php');

/**
 * Class asrQuestionTypeMapper
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class asrQuestionTypeMapper {

	/**
	 * @param SurveyQuestionGUI $original_class
	 * @param null              $row
	 *
	 * @return string
	 */
	public static function getTextRepresentation(SurveyQuestionGUI $original_class, $row = NULL) {
		if ($original_class instanceof asrQuestion) {
			return $original_class->getTextRepresenation($row);
		}

		return '';
	}


	const MULTIPLE_CHOICE = 'SurveyMultipleChoiceQuestionGUI';
	const SINGLE_CHOICE = 'SurveySingleChoiceQuestionGUI';
	const METRIC = 'SurveyMetricQuestionGUI';
	const TEXT = 'SurveyTextQuestionGUI';
	const MATRIX = 'SurveyMatrixQuestionGUI';
	/**
	 * @var array
	 */
	protected static $original_types = array(
		1 => self::MULTIPLE_CHOICE,
		2 => self::SINGLE_CHOICE,
		3 => self::METRIC,
		4 => self::TEXT,
		5 => self::MATRIX,
	);
	/**
	 * @var array
	 */
	protected static $is_mapped = array(
		self::MULTIPLE_CHOICE,
		self::TEXT,
		self::SINGLE_CHOICE,
		self::METRIC,
		self::MATRIX,
	);


	/**
	 * @param SurveyQuestionGUI $original_class
	 * @param                   $id
	 *
	 * @return mixed
	 */
	public static function getInstanceByOriginalClassName(SurveyQuestionGUI $original_class, $id, $own_results) {
		$original_class_name = get_class($original_class);
		if (in_array($original_class_name, self::$is_mapped)) {
			$class = 'ilExt' . $original_class_name;

			return new $class($id, $own_results);
		} else {
			return $original_class;
		}
	}
}

?>
