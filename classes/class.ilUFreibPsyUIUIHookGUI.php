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

		if (!isset($DIC["lng"]) || !isset($DIC["ilDB"]) || !isset($DIC["ilUser"]))
		{
			return false;
		}

		$settings = new ilSetting("ufreibpsy");

		if (($r = $settings->get("role")) > 0)
		{
			$roles_of_user = $DIC->rbac()->review()->assignedRoles($DIC->user()->getId());
			if (in_array((string) $r, $roles_of_user))
			{
				return true;
			}
		}

		return false;
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


		$view = false;

		if (isset($DIC["ilHelp"]))
		{
			$help = $DIC["ilHelp"];
			if (in_array($help->getScreenId(), array("crs/view_content/", "crs/view_content/view_content")))
			{
				$view = true;
			}
		}

		if (isset($DIC["ilCtrl"]))
		{
			$ilCtrl = $DIC["ilCtrl"];
			if (strtolower($ilCtrl->getCmdClass()) == "ilobjcoursegui" && in_array($ilCtrl->getCmd(), array("view", "")))
			{
				$view = true;
			}
		}

		if ($view)
		{
			$plugin = $this->getPluginObject();
			$plugin->includeClass("class.ilUFreibPsyUICourses.php");
			$courses = new ilUFreibPsyUICourses();
			if (in_array((int) $_GET["ref_id"], $courses->getAll()))
			{
				return true;
			}
		}

		return false;
	}

    /**
     * Checks if current view is the one given as parameter
     *
     * @param string $cmd_class
     * @return bool
     */
    protected function isView($cmd_class)
    {
        global $DIC;


        $view = false;

        if (isset($DIC["ilCtrl"]))
        {
            $ilCtrl = $DIC["ilCtrl"];
            if (strtolower($ilCtrl->getCmdClass()) == $cmd_class)
            {
                $view = true;
            }
        }

        return $view;
    }



	/**
	 * Get html for ui area
	 *
	 * @param
	 * @return
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		global $DIC;

		if ($a_comp == "Services/MainMenu" && in_array($a_part, array("main_menu_list_entries")))
		{
			$DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getStyleSheetLocation("freibpsy_general.css"));
		}

		if ($this->isStudyParticipant())
		{
            if ($a_comp == "" && $a_part == "template_get")
            {
                if ($a_par["tpl_id"] == "src/UI/templates/default/MainControls/tpl.metabar.html")
                {
                    $DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getStyleSheetLocation("freibpsy_restricted.css"));
                    $DIC->ui()->mainTemplate()->addJavaScript(
                        $this->getPluginObject()->getDirectory()."/js/UFreibPsyUI.js");
                }

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

                if($this->isView("ilmailfoldergui")) {

                    if (in_array($a_par["tpl_id"], array("Services/UIComponent/Tabs/tpl.tabs.html")))
                    {
                        $DIC->logger()->usr()->info("Is mailfoldergui");
                        $DIC->logger()->usr()->dump($a_par["tpl_id"]);

                        $tpl = new ilTemplate(
                            "tpl.tabs.html",
                            true,
                            true,
                            "Services/UIComponent/Tabs"
                        );

                        $tpl->setVariable("{TAB_TEXT}", "Blub");


                        return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
                    }

                }

                if($this->isView("ilmailformgui")) {
                    $DIC->logger()->usr()->info("This is the mailformgui");
                    $DIC->logger()->usr()->dump($a_par["tpl_id"]);
                }
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