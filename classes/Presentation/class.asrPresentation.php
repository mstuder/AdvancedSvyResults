<?php

/**
 * Class asrPresentation
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class asrPresentation {

	/**
	 * @param $ref_id
	 * @param $usr_id
	 *
	 * @return array
	 */
	public static function getMyResultForRefId($ref_id, $usr_id = NULL) {
		if (!$usr_id) {
			global $ilUser;
			$usr_id = $ilUser->getId();
		}
		$return = array();
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$q = "SELECT ans.value, ans.textanswer, ans.rowvalue, cat.title, ans.question_fi, var.value1
					FROM svy_svy svy
				JOIN object_reference ref
					ON svy.obj_fi = ref.obj_id
				JOIN svy_finished finished
					ON finished.survey_fi = svy.survey_id
				JOIN svy_answer ans
					ON ans.active_fi = finished.finished_id
				LEFT JOIN svy_variable var
					ON var.question_fi = ans.question_fi AND ans.value + 1  = var.value1
				LEFT JOIN svy_category cat ON cat.category_id = var.category_fi
				WHERE ref.ref_id =  " . $ilDB->quote($ref_id, 'integer') . ".
					AND finished.user_fi = " . $ilDB->quote($usr_id, 'integer');

		$res = $ilDB->query($q);
		while ($rec = $ilDB->fetchObject($res)) {
			if ($rec->textanswer) {
				$return[$rec->question_fi]['text'][$rec->rowvalue] = $rec->textanswer;
				continue;
			}
			$return[$rec->question_fi]['num'][$rec->rowvalue][] = $rec->value;
			$return[$rec->question_fi]['num2'][$rec->rowvalue][] = $rec->value1;
			$return[$rec->question_fi]['title'][$rec->rowvalue][] = $rec->title;
		}

		return $return;
	}


	public static function getResultStringForQuestionId($q_id, $usr_id = NULL) {
	}
}

?>
