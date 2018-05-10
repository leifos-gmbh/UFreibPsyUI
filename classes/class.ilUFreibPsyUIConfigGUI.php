<?php

/* Copyright (c) 2018 Leifos GmbH, GPL3, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * University Freiburg Psychology plugin
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilUFreibPsyUIConfigGUI extends ilPluginConfigGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilUFreibPsyUICourses
	 */
	protected $courses;

	/**
	 * @var ilUFreibCourseItemImage
	 */
	protected $images;

	/**
	 * course item ref id (first level children)
	 *
	 * @var int
	 */
	protected $crs_item_ref_id;

	/**
	 * course ref id
	 *
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * Handles all commmands, default is "configure"
	 */
	function performCommand($cmd)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->toolbar = $DIC->toolbar();
		$this->lng = $DIC->language();

		$plugin = $this->getPluginObject();
		$plugin->includeClass("class.ilUFreibPsyUICourses.php");
		$this->courses = new ilUFreibPsyUICourses();

		$plugin->includeClass("class.ilUFreibCourseItemImage.php");
		$this->images = ilUFreibCourseItemImage::getInstance();

		$this->ctrl->saveParameter($this, array("crs_ref_id", "crs_item_ref_id"));

		$this->crs_ref_id = (int) $_GET["crs_ref_id"];
		$this->crs_item_ref_id = (int) $_GET["crs_item_ref_id"];

		switch ($cmd)
		{
			default:
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure (list courses)
	 */
	function configure()
	{
		$plugin = $this->getPluginObject();
		$main_tpl = $this->main_tpl;

		//$this->toolbar->setFormAction($this->ctrl->getFormAction($this));
		$this->toolbar->addButton($plugin->txt("add_course"), $this->ctrl->getLinkTarget($this, "selectCourse"));

		$plugin->includeClass("class.ilUFreibCourseTableGUI.php");
		$table = new ilUFreibCourseTableGUI($this, "configure", $this->getPluginObject(), $this->courses);

		$main_tpl->setContent($table->getHTML());
	}

	/**
	 * Select a course
	 */
	protected function selectCourse()
	{
		$main_tpl = $this->main_tpl;

		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$exp = new ilRepositorySelectorExplorerGUI($this, "selectCourse",
			$this, "addCourse", "crs_ref_id");
		$exp->setTypeWhiteList(array("root", "cat", "crs"));
		$exp->setClickableTypes(array("crs"));
		if (!$exp->handleCommand())
		{
			$main_tpl->setContent($exp->getHTML());
		}
	}

	/**
	 * Add course
	 */
	protected function addCourse()
	{
		$this->courses->add((int) $_GET["crs_ref_id"]);
		$this->ctrl->redirect($this, "configure");
	}

	/**
	 * Remove course
	 */
	protected function removeCourse()
	{
		$this->courses->remove((int) $_GET["crs_ref_id"]);
		$this->ctrl->redirect($this, "configure");
	}


	//
	// Items
	//

	/**
	 * Configure (list courses)
	 */
	function listItems()
	{
		$plugin = $this->getPluginObject();
		$main_tpl = $this->main_tpl;


		$plugin->includeClass("class.ilUFreibCourseItemTableGUI.php");
		$table = new ilUFreibCourseItemTableGUI($this, "listItems",
			$this->getPluginObject(), $this->images);

		$main_tpl->setContent($table->getHTML());
	}
	
	/**
	 * Edit image
	 */
	protected function editImage()
	{
		$form = $this->initForm();
		$this->main_tpl->setContent($form->getHTML());
	}

	/**
	 * Init image form.
	 */
	public function initForm()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		//
		$ti = new ilImageFileInputGUI($this->lng->txt("image"), "image");
		$form->addItem($ti);

		$form->addCommandButton("saveImage", $this->lng->txt("save"));
		$form->addCommandButton("listItems", $this->lng->txt("cancel"));

		$form->setTitle($this->lng->txt("image"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save image
	 */
	protected function saveImage()
	{
		$this->images->upload($this->crs_item_ref_id);
		$this->ctrl->redirect($this, "listItems");
	}
	

}