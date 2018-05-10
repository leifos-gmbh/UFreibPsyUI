<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de
 * @ingroup
 */
class ilUFreibPsyUICourses
{
	protected $course_ref_ids = array();

	/**
	 *
	 *
	 * @param
	 */
	function __construct()
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->read();
	}

	/**
	 * Read
	 */
	protected function read()
	{
		$ilDB = $this->db;

		$this->course_ref_ids = array();
		$set = $ilDB->query("SELECT * FROM ufreibpsy_courses");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->course_ref_ids[] = $rec["crs_ref_id"];
		}
	}

	/**
	 * Get all
	 *
	 * @return int[]
	 */
	public function getAll()
	{
		return $this->course_ref_ids;
	}


	/**
	 * Add course
	 *
	 * @param int $a_crs_ref_id
	 */
	public function add($a_crs_ref_id)
	{
		$ilDB = $this->db;

		$ilDB->replace("ufreibpsy_courses",
			array("crs_ref_id" => array("integer", $a_crs_ref_id)),
			array()
			);	
	}

	/**
	 * Remove course
	 *
	 * @param int $a_crs_ref_id
	 */
	public function remove($a_crs_ref_id)
	{
		$ilDB = $this->db;
		$ilDB->manipulate("DELETE FROM ufreibpsy_courses WHERE ".
			" crs_ref_id = ".$ilDB->quote($a_crs_ref_id, "integer")
			);
	}

	

}
?>