<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUFreibCourseTableGUI extends ilTable2GUI
{
	/**
	 * ilUFreibCourseTableGUI constructor.
	 * @param $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_plugin, $a_courses)
	{
		global $DIC;

		$this->ui = $DIC->ui();
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tree = $DIC->repositoryTree();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$this->tree = $DIC->repositoryTree();

		$this->plugin = $a_plugin;

		$this->setId("ufreibcrs");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setLimit(9999);

		$this->setTitle($lng->txt("courses"));
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("path"));
		$this->addColumn($lng->txt("actions"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));

		$this->setRowTemplate($this->plugin->getDirectory()."/templates/tpl.courses_row.html");

		$data = [];
		foreach ($a_courses->getAll() as $ref_id)
		{
			$obj_id = ilObject::_lookupObjectId($ref_id);
			$title = ilObject::_lookupTitle($obj_id);
			if ($obj_id > 0)
			{
				$data[] = [
					"ref_id" => $ref_id,
					"title" => $title,
					"obj_id" => $obj_id
				];
			}
		}

		$this->setData($data);
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$lng = $this->lng;

		$tree = $this->tree;
		$ui = $this->ui;
		$ctrl = $this->ctrl;

		$path = $tree->getPathFull($a_set["ref_id"]);
		array_shift($path);
		$this->tpl->setVariable("TITLE", ilObject::_lookupTitle($a_set["obj_id"]));
		$this->tpl->setVariable("PATH", implode (" > ", array_column($path, 'title')));

		// actions
		$ctrl->setParameter($this->parent_obj, "crs_ref_id", $a_set["ref_id"]);
		$link = [];
		$link[] = $ui->factory()->link()->standard($lng->txt("remove"),
			$ctrl->getLinkTarget($this->parent_obj, "confirmRemoveCourse"));
		$link[] = $ui->factory()->link()->standard($this->plugin->txt("edit_items"),
			$ctrl->getLinkTarget($this->parent_obj, "listItems"));
		$dd = $ui->factory()->dropdown()->standard($link);
		$this->tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));

	}

}


?>