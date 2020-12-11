<?php

/* Copyright (c) 2018 Leifos GmbH, GPL3, see docs/LICENSE */

use ILIAS\UI\Component\Symbol\Icon\Standard;

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

	static protected $tabs_replaced = false;

	static protected $coaches_called = false;

	static protected $mail_notification_called = false;

	const COACH_FIELD_NAME = "E-Coaches";

	protected $inbox_id = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{

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
     * Checks if current command is the one given as parameter
     *
     * @param string $cmd
     * @return bool
     */
    protected function isCommand($cmd)
    {
        global $DIC;


        $command = false;

        if (isset($DIC["ilCtrl"]))
        {
            $ilCtrl = $DIC["ilCtrl"];
            if (strtolower($ilCtrl->getCmd()) == strtolower($cmd))
            {
                $command = true;
            }
        }

        return $command;
    }



	/**
	 * Get html for ui area
	 *
	 * @param
	 * @return
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
        global $DIC, $tpl;

        if ($tpl)
        {
            $main_tpl = $tpl;
        }

        $lng = $DIC->language();

		if ($a_comp == "Services/MainMenu" && in_array($a_part, array("main_menu_list_entries")))
		{
			$DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getStyleSheetLocation("freibpsy_general.css"));
		}

		if ($this->isStudyParticipant())
		{
            if ($a_comp == "" && $a_part == "template_get")
            {
                $this->triggerMailReadEvent($a_par);
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

                    if(strpos(strtolower($a_par["tpl_id"]), "notification") !== false)
                    {
                        $DIC->logger()->usr()->dump($a_par["tpl_id"]);
                    }

                    if(!self::$coaches_called)
                    {
                        if (in_array($a_par["tpl_id"], array("Services/UIComponent/Tabs/tpl.tabs.html")))
                        {
                            $coach_cards = $this->getCoachCards();

                            self::$coaches_called = true;
                            //return array("mode" => ilUIHookPluginGUI::PREPEND, "html" => $coach_cards);


                            $stpl = new ilTemplate("src/UI/templates/default/Panel/tpl.secondary.html", true, true);
                            $ui_factory = $DIC->ui()->factory();
                            $notification = new \ILIAS\Mail\Provider\MailNotificationProvider($DIC);
                            $ui_renderer = $DIC->ui()->renderer();

                            $description = "";
                            foreach ($notification->getNotifications() as $isItem) {
                                $item_renderer = $isItem->getRenderer($ui_factory);
                                $comp = $item_renderer->getNotificationComponentForItem($isItem);
                                $contents = $comp->getContents();
                                foreach ($contents as $content) {
                                    $description = $content->getDescription();
                                }
                            }
                            if ($description == "") {
                                $description = $this->getPluginObject()->txt("no_feebdack");
                            }


                            $icon = $ui_factory->symbol()->icon()->standard(Standard::MAIL, 'mail')
                                              ->withIsOutlined(false)->withSize("large");

                            $icon = $ui_factory->image()->standard(ilUtil::getImagePath("icon_mail.svg"), "Mail");
                            $icon = $ui_renderer->render($icon);

                            //if(!empty($content->getDescription()))
                            $stpl->setVariable("BODY_LEGACY",
                                "<h3>".$this->getPluginObject()->txt("ecoach_feedbacks")."</h3>".
                                "<div class='row'><div class='col-xs-2 col-sm-5'>".$icon."</div>".
                                "<div class='col-xs-10 col-sm-7 small'><p>"." " . $description."</p></div></div>");

                            $main_tpl->setRightContent($coach_cards.$stpl->get());
                        }
                    }

                    if (in_array($a_par["tpl_id"], array("Services/UIComponent/Tabs/tpl.tabs.html",
						"Services/UIComponent/Tabs/tpl.sub_tabs.html")))
					{
						return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
					}

					if (!self::$course_content_called)
					{
						if (in_array($a_par["tpl_id"], array("Services/Container/tpl.container_page.html")))
						{
//							return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $this->getCourseHTML());
						}
					}

				}

                if($this->isView("ilmailfoldergui") || $this->isView("ilmailformgui"))
                {

                    $DIC->ui()->mainTemplate()->addJavaScript(
                        $this->getPluginObject()->getDirectory()."/js/UFreibPsyUI_Mail.js");

                    if (in_array($a_par["tpl_id"], array("Services/UIComponent/Tabs/tpl.tabs.html")))
                    {


                        if (!self::$tabs_replaced) {


                            $tabs = new ilTabsGUI();
                            $links = $this->getMailLinks();
                            $tabs->addTab("fold", $lng->txt('inbox'), $links["inbox"]);
                            $tabs->addTab("sent", $lng->txt('sent'), $links["sent"]);
                            $tabs->addTab("compose", $lng->txt('compose'), $links["compose"]);

                            if($this->isView("ilmailformgui"))
                            {
                                $tabs->activateTab("compose");
                            } else if ($this->isView("ilmailfoldergui") && $_GET["mobj_id"] == $this->inbox_id)
                            {
                                $tabs->activateTab("fold");
                            } else {
                                $tabs->activateTab("sent");
                            }

                            self::$tabs_replaced = true;
                            return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $tabs->getHTML());
                        }
                    }

                    if (in_array($a_par["tpl_id"], array("Services/UIComponent/Toolbar/tpl.toolbar.html")) && !$this->isCommand("showmail"))
                    {
                        return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
                    }

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

    /**
     *
     * @param
     * @return array
     */
	protected function getCoaches()
    {
        global $DIC;

        $udf_userdata = $DIC->user()->getUserDefinedData();

        $userDefinedFields = ilUserDefinedFields::_getInstance();
        $udf_definitions = $userDefinedFields->getDefinitions();

        $coaches = array();

        if(!empty($udf_definitions)) {

            foreach ($udf_definitions as $udf_key => $udf_definition) {
                if ($udf_definition["field_name"] === self::COACH_FIELD_NAME) {
                    $udf_userdata = $udf_userdata["f_" . $udf_key];
                }
            }

            $e_coaches = [];

            if ($udf_userdata) {
                $e_coaches = explode(",", $udf_userdata);
            }

            foreach ($e_coaches as $coach_name) {
                $coach_id = ilObjUser::_lookupId($coach_name);

                if (!empty($coach_id)) {
                    $coaches[] = new ilObjUser($coach_id);
                }
            }
        }

        return $coaches;
    }

    /**
     *
     * @param
     * @return
     */
    protected function getMailLinks()
    {
        global $DIC;
        $ctrl = $DIC->ctrl();

        $mail_tree = new ilTree($DIC->user()->getId());
        $mail_tree->setTableNames('mail_tree', 'mail_obj_data');
        $childs = $mail_tree->getChilds($mail_tree->readRootId());

        $links = [];
        foreach ($childs as $c) {
            if ($c["m_type"] === "inbox" || $c["m_type"] === "sent") {
                $ctrl->setParameterByClass("ilmailfoldergui", "mobj_id", $c["obj_id"]);
                $links[$c["m_type"]] = $ctrl->getLinkTargetByClass("ilmailfoldergui", "");
            }
            if ($c["m_type"] === "inbox") {
                $this->inbox_id = $c["obj_id"];
            }
        }

        $coaches = $this->getCoaches();

        $to_coaches= "";
        $last_coach = end($coaches);
        foreach ($coaches as $coach) {
            if($coach->getLogin() != $last_coach->getLogin()) {
                $to_coaches .= $coach->getLogin() . ",";
            } else {
                $to_coaches .= $coach->getLogin();
            }

        }

        $ctrl->setParameterByClass("ilmailformgui", "rcp_to", $to_coaches);
        $links['compose'] = $ctrl->getLinkTargetByClass("ilmailformgui", "mailUser");

        return $links;
    }

    /**
     * Creates Cards for all assigned E-Coaches of signed in user
     *
     *
     * @return string
     */
    protected function getCoachCards()
    {
        global $DIC;

        $factory     = $DIC->ui()->factory();
        $ctrl        = $DIC->ctrl();
        $ui_renderer = $DIC->ui()->renderer();

        $coaches = $this->getCoaches();
        $cards = "";
        foreach ($coaches as $coach)
        {
            $dep = ($coach->getDepartment() != "")
                ? "<p>".$coach->getDepartment()."</p>"
                : "";
            $avatar = $factory->image()->standard($coach->getPersonalPicturePath('big'), $coach->getPublicName());
            $cards.= "<div class='row'><div class='col-xs-8 col-sm-6 small'><p>".
                $coach->getUTitle()." ".$coach->getFirstname()." ".$coach->getLastname()."</p>".
                $dep.
                "<p>".$coach->getInstitution()."</p>".
                "</div>".
                "<div class='col-xs-4 col-sm-6 il-ecoaches'>".$ui_renderer->render($avatar)."</div></div>";
        }

        if(!empty($cards))
        {
            $html = '<div class="panel panel-secondary panel-flex"><div class="panel-body">'.
                "<h3>".$this->getPluginObject()->txt("e_coaches")."</h3>".$cards."</div></div>";
            return $html;
        }

        return "";
    }

    protected function triggerMailReadEvent($tpl) {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $user_id = $DIC->user()->getId();
        $mail_id = (int) $_GET["mail_id"];
        $app_event_handler = $DIC['ilAppEventHandler'];
        if ($tpl["tpl_id"] == "Services/Form/tpl.property_form.html" &&
            strtolower($ctrl->getCmdClass()) == "ilmailfoldergui" &&
            $ctrl->getCmd() == "showMail") {
            $app_event_handler->raise('Services/Mail', 'mailRead', [
                'mail_id' => $mail_id,
                'user_id' => $user_id
            ]);
        }
    }
}