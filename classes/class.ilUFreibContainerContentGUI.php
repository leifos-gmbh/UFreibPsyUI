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

		//Define the some responsive image
		$image = $f->image()->responsive(
			"./templates/default/images/HeaderIcon.svg", "Thumbnail Example")
			->withAction("http://www.ilias.de");

		//Define the card by using the image and add a new section with a button
		$card = $f->card(
			$a_item_data["title"],
			$image
		)->withTitleAction("http://www.ilias.de");

		return $card;
	}
}