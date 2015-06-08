<?php

/**
 * Class asrQuestion
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
interface asrQuestion {

	/**
	 * @param int $row
	 *
	 * @return string
	 */
	public function getTextRepresenation($row = NULL);
}

?>
