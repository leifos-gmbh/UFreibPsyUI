<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilUFreibCourseItemTableGUI extends ilTable2GUI
{
	/**
	 * @var ilUFreibCourseItemImage
	 */
	protected $images;

	/**
	 * ilUFreibCourseTableGUI constructor.
	 * @param $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_plugin, $a_images)
	{
		global $DIC;

		$this->crs_ref_id = (int) $_GET["crs_ref_id"];

		$this->ui = $DIC->ui();
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tree = $DIC->repositoryTree();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$this->tree = $DIC->repositoryTree();
		$this->images = $a_images;

		$this->plugin = $a_plugin;

		$this->plugin->includeClass("class.ilUFreibImageGUI.php");
		$this->image_gui = new ilUFreibImageGUI();

		$this->setId("ufreibcrsitems");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setLimit(9999);

		$this->setTitle($lng->txt("courses"));
		$this->addColumn($lng->txt("image"));
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("actions"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));

		$this->setRowTemplate($this->plugin->getDirectory()."/templates/tpl.courses_item_row.html");

		$data = [];
		foreach ($this->tree->getChilds($this->crs_ref_id) as $child)
		{
			$data[] = $child;
		}

		$this->setData($data);
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$lng = $this->lng;

		$ui = $this->ui;
		$ctrl = $this->ctrl;

		$im = $this->image_gui->getImage($a_set["child"]);
		$this->tpl->setVariable("IMAGE", $ui->renderer()->render($im));

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		// actions
		$ctrl->setParameter($this->parent_obj, "crs_item_ref_id", $a_set["child"]);
		$link = [];
		$link[] = $ui->factory()->link()->standard($lng->txt("image"),
			$ctrl->getLinkTarget($this->parent_obj, "editImage"));
		$dd = $ui->factory()->dropdown()->standard($link);
		$this->tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));

	}

}


?>