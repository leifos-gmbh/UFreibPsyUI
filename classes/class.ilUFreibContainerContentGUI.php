<?php

/* Copyright (c) 2018 Leifos GmbH, GPL3, see docs/LICENSE */

include_once("./Services/Container/classes/class.ilContainerByTypeContentGUI.php");

/**
 * Shows all items grouped by type.
 *
 * @author Alex Killing <killing@leifos.de>
 *
 */
class ilUFreibContainerContentGUI extends ilContainerByTypeContentGUI
{
	protected $plugin;

	/**
	 * Set plugin
	 *
	 * @param ilPlugin $a_val plugin
	 */
	function setPlugin($a_val)
	{
		$this->plugin = $a_val;
	}

	/**
	 * Get plugin
	 *
	 * @return ilPlugin plugin
	 */
	function getPlugin()
	{
		return $this->plugin;
	}

	/**
	 * Init container renderer
	 */
	protected function initRenderer()
	{
		include_once('./Services/Container/classes/class.ilContainerSorting.php');
		$sorting = ilContainerSorting::_getInstance($this->getContainerObject()->getId());

		$this->getPlugin()->includeClass("class.ilUFreibImageGUI.php");
		$this->image_gui = new ilUFreibImageGUI();

		$this->getPlugin()->includeClass("class.ilUFreibContainerRenderer.php");
		$this->renderer = new ilUFreibContainerRenderer(
			($this->getContainerGUI()->isActiveAdministrationPanel() && !$_SESSION["clipboard"])
			, $this->getContainerGUI()->isMultiDownloadEnabled()
			, $this->getContainerGUI()->isActiveOrdering() && (get_class($this) != "ilContainerObjectiveGUI") // no block sorting in objective view
			, $sorting->getBlockPositions()
		);
		$this->renderer->setPlugin($this->getPlugin());
	}

	/**
	 * Render an item, usually this should return html, here it returns a card
	 *
	 * @param    array        item data
	 *
	 * @return    string        item HTML
	 */
	function renderItem($a_item_data, $a_position = 0, $a_force_icon = false, $a_pos_prefix = "")
	{
		global $DIC;
		$f = $DIC->ui()->factory();


		$ilSetting = $this->settings;
		$ilAccess = $this->access;
		$ilCtrl = $this->ctrl;

		// Pass type, obj_id and tree to checkAccess method to improve performance
		if(!$ilAccess->checkAccess('visible','',$a_item_data['ref_id'],$a_item_data['type'],$a_item_data['obj_id'],$a_item_data['tree']))
		{
			return null;
		}
		$item_list_gui = $this->getItemGUI($a_item_data);


		$html = $item_list_gui->getListItemHTML($a_item_data['ref_id'],
			$a_item_data['obj_id'], $a_item_data['title'], $a_item_data['description'],
			false, false, "");

		if ($html == "")
		{
			return null;
		}

		$def_command = "#";

		// super hacky, but we cannot set the frame currently
		if (isset($item_list_gui->default_command["link"]))
		{
			$def_command = $item_list_gui->default_command["link"];
			if ($item_list_gui->default_command["frame"] != "")
			{
				$def_command.= "###linkframe###".$item_list_gui->default_command["frame"]."###";
			}
		}

		//Define the some responsive image
		$image = $this->image_gui->getImage($a_item_data['ref_id']);
		$image = $image->withAction($def_command);

		//Define the card by using the image and add a new section with a button
		$card = $f->card(
			$a_item_data["title"],
			$image
		)->withTitleAction($def_command);

		return $card;
	}
}