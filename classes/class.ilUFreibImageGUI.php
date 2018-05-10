<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_IsCalledBy ilUFreibImageGUI: ilUIPluginRouterGUI
 */
class ilUFreibImageGUI
{
	/**
	 */
	public function __construct()
	{
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->ui = $DIC->ui();
	}


	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("sendImage");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("sendImage")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Get image component
	 *
	 * @param
	 * @return
	 */
	public function getImage($a_item_ref_id)
	{
		$ctrl = $this->ctrl;
		$ui = $this->ui;

		include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UFreibPsyUI/classes/class.ilUFreibCourseItemImage.php");
		$images = ilUFreibCourseItemImage::getInstance();

		if ($images->exists($a_item_ref_id))
		{
			$ctrl->setParameterByClass("ilUFreibImageGUI", "item_ref_id", $a_item_ref_id);
			$im = $ui->factory()->image()->responsive($ctrl->getLinkTargetByClass(
				array("ilUIPluginRouterGUI", "ilUFreibImageGUI")),"");
		}
		else
		{
			$type = ilObject::_lookupType($a_item_ref_id,true);
			$im = $ui->factory()->image()->responsive(ilUtil::getImagePath("icon_".$type.".svg"),"");
		}
		return $im;
	}


	/**
	 * Send image
	 *
	 * @param
	 */
	protected function sendImage()
	{
		include_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UFreibPsyUI/classes/class.ilUFreibCourseItemImage.php");
		$images = ilUFreibCourseItemImage::getInstance();

		$images->send((int) $_GET["item_ref_id"]);
	}

}

?>