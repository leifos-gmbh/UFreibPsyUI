<?php

/* Copyright (c) 2018 Leifos GmbH, GPL3, see docs/LICENSE */

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * University Freiburg Psychology plugin
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilUFreibPsyUIUIHookGUI extends ilUIHookPluginGUI
{
	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;

	static protected $course_content_called = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		if (isset($DIC["ui"]))
		{
			$this->main_tpl = $DIC->ui()->mainTemplate();
		}
	}

	/**
	 * Is study participant
	 *
	 * @param
	 * @return
	 */
	protected function isStudyParticipant()
	{
		global $DIC;

		$access = $DIC->access();

		if ($access->checkAccess("write", "", ROOT_FOLDER_ID))
		{
			return false;
		}
		return true;
	}

	/**
	 * Is course content view
	 *
	 * @param
	 * @return
	 */
	protected function isCourseContentView()
	{
		global $DIC;

		if (isset($DIC["ilHelp"]))
		{
			$help = $DIC["ilHelp"];
			if (in_array($help->getScreenId(), array("crs/view_content/", "crs/view_content/view_content")))
			{
				return true;
			}
		}

		if (isset($DIC["ilCtrl"]))
		{
			$ilCtrl = $DIC["ilCtrl"];
			if (strtolower($ilCtrl->getCmdClass()) == "ilobjcoursegui" && in_array($ilCtrl->getCmd(), array("view", "")))
			{
				return true;
			}
		}


		return false;
	}



	/**
	 * Get html for ui area
	 *
	 * @param
	 * @return
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		if ($this->isStudyParticipant())
		{
			if ($a_comp == "" && $a_part == "template_get")
			{
				if ($a_par["tpl_id"] == "Services/Locator/tpl.locator.html")
				{
					return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
				}

				if ($this->isCourseContentView())
				{
					if (in_array($a_par["tpl_id"], array("Services/UIComponent/Tabs/tpl.tabs.html",
						"Services/UIComponent/Tabs/tpl.sub_tabs.html")))
					{
						return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
					}

					if (!self::$course_content_called)
					{
						if (in_array($a_par["tpl_id"], array("Services/Container/tpl.container_page.html")))
						{
							return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $this->getCourseHTML());
						}
					}
				}

				if (strpos($a_par["tpl_id"], "cont"))
				{
				/*	var_dump($a_comp);
					var_dump($a_par);
					exit;*/
				}
				//return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
			}
		}

		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}

	/**
	 * Get course HTML
	 *
	 * @param
	 * @return
	 */
	protected function getCourseHTML()
	{
		self::$course_content_called = true;
		$course_gui = new ilObjCourseGUI();
		$this->getPluginObject()->includeClass("class.ilUFreibContainerContentGUI.php");
		$container_view = new ilUFreibContainerContentGUI($course_gui);
		$container_view->setPlugin($this->getPluginObject());

		return $container_view->getMainContent();
	}


}
?>