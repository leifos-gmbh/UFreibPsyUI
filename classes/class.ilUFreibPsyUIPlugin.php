<?php

/* Copyright (c) 2018 Leifos GmbH, GPL3, see docs/LICENSE */


include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

/**
 * University Freiburg Psychology plugin
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 */
class ilUFreibPsyUIPlugin extends ilUserInterfaceHookPlugin
{
	function getPluginName()
	{
		return "UFreibPsyUI";
	}

}

?>
