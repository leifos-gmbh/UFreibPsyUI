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
	 * @var ilTabsGUI
	 */
	protected $tabs;

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
		$this->rbacreview = $DIC->rbac()->review();

		$this->settings = new ilSetting("ufreibpsy");

		$this->tabs = $DIC->tabs();

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
	 * Add tabs
	 *
	 * @param
	 * @return
	 */
	protected function addTabs($a_active)
	{
		$tabs = $this->tabs;
		$pl = $this->getPluginObject();
		$ctrl = $this->ctrl;

		$tabs->addTab("general", $pl->txt("general"), $ctrl->getLinkTarget($this, "configure"), "");
		$tabs->addTab("container", $pl->txt("crs_container"), $ctrl->getLinkTarget($this, "listContainer"));
		$tabs->activateTab($a_active);
	}

	//
	// General
	//

	/**
	 *
	 */
	function configure()
	{
		$this->addTabs("general");
		$form = $this->initGeneralForm();
		$this->main_tpl->setContent($form->getHTML());
	}

	/**
	 * Init general form.
	 */
	public function initGeneralForm()
	{
		$pl = $this->getPluginObject();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// role selector
		$options = array(
			"" => $this->lng->txt("none"),
		);
		$all_gl_roles = $this->rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
		foreach ($all_gl_roles as $obj_data)
		{
			$options[$obj_data["obj_id"]] = $obj_data["title"];
		}
		$si = new ilSelectInputGUI($this->lng->txt("role"), "role");
		$si->setInfo($pl->txt("role_info"));
		$si->setValue($this->settings->get("role"));
		$si->setOptions($options);
		$form->addItem($si);

		$form->addCommandButton("saveGeneral", $this->lng->txt("save"));

		$form->setTitle($pl->txt("general"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save general form
	 */
	public function saveGeneral()
	{
		$form = $this->initGeneralForm();
		if ($form->checkInput())
		{
			$this->settings->set("role", (int) $_POST["role"]);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "configure");
	}

	//
	// Container
	//

	/**
	 * Configure (list container)
	 */
	function listContainer()
	{
		$this->addTabs("container");

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
		$exp->setTypeWhiteList(array("root", "cat", "crs", "grp", "fold"));
		//$exp->setClickableTypes(array("root", "cat", "crs", "grp", "fold"));
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
		$this->ctrl->redirect($this, "listContainer");
	}

	/**
	 * Confirm
	 */
	function confirmRemoveCourse()
	{
		$main_tpl = $this->main_tpl;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ctrl->getFormAction($this));
		$cgui->setHeaderText($this->getPluginObject()->txt("remove_course_config"));
		$cgui->setCancel($lng->txt("cancel"), "listContainer");
		$cgui->setConfirm($lng->txt("remove"), "removeCourse");

		$cgui->addItem("crs_ref_id", $_GET["crs_ref_id"],
			ilObject::_lookupTitle(ilObject::_lookupObjectId($_GET["crs_ref_id"])));

		$main_tpl->setContent($cgui->getHTML());
	}


	/**
	 * Remove course
	 */
	protected function removeCourse()
	{
		$this->courses->remove((int) $_POST["crs_ref_id"]);
		$this->ctrl->redirect($this, "listContainer");
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

		$this->tabs->setBackTarget($this->getPluginObject()->txt("crs_container"), $this->ctrl->getLinkTarget($this, "listContainer"));

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
		$ti->setImage($this->images->getImageName($this->crs_item_ref_id));
		$ti->setALlowDeletion(true);
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
		if ($_POST["image_delete"])
		{
			$this->images->delete($this->crs_item_ref_id);
		}
		$this->images->upload($this->crs_item_ref_id);
		$this->ctrl->redirect($this, "listItems");
	}
	

}