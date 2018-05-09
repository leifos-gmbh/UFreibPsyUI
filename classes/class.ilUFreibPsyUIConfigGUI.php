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
	protected $ctrl;
	protected $main_tpl;

	/**
	 * Handles all commmands, default is "configure"
	 */
	function performCommand($cmd)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->main_tpl = $DIC->ui()->mainTemplate();

		//$this->getPluginObject()->includeClass("class.lfCustomMenu.php");

		switch ($cmd)
		{
			default:
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure
	 *
	 * @param
	 * @return
	 */
	function configure()
	{
		$main_tpl = $this->main_tpl;

		$main_tpl->setContent("Test");
	}

}
?>
