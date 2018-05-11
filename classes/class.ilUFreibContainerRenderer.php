<?php

require_once('./Services/Container/classes/class.ilContainerRenderer.php');

/**
 * Container renderer
 *
 * @author Alex Killing  <alex.killing@gmx.de>
 */
class ilUFreibContainerRenderer extends ilContainerRenderer
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
	 * Init template
	 *
	 * @return ilTemplate
	 */
	protected function initBlockTemplate()
	{
		return $this->getPlugin()->getTemplate("tpl.freib_container_list_block.html", true, true);
	}

	/**
	 * Add item to existing block
	 *
	 * @param mixed $a_block_id
	 * @param string $a_item_type repository object type
	 * @param mixed $a_item_id
	 * @param string $a_item_html html snippet
	 * @param bool $a_force enable multiple rendering
	 * @return boolean
	 */
	public function addItemToBlock($a_block_id, $a_item_type, $a_item_id, $a_item_html, $a_force = false)
	{
		if($this->isValidBlock($a_block_id) &&
			$a_item_type != "itgr" &&
			(!$this->hasItem($a_item_id) || $a_force))
		{
			// #16563 - item_id (== ref_id) is NOT unique, adding parent block id
			$uniq_id = $a_block_id.self::UNIQUE_SEPARATOR.$a_item_id;

			$this->items[$uniq_id] = array(
				"type" => $a_item_type
			,"html" => $a_item_html
			);

			// #18326
			$this->item_ids[$a_item_id] = true;

			$this->block_items[$a_block_id][] = $uniq_id;
			return true;
		}
		return false;
	}

	/**
	 * Render block
	 *
	 * @param ilTemplate $a_block_tpl
	 * @param mixed $a_block_id
	 * @param array $a_block block properties
	 * @param bool $a_is_single
	 * @return boolean
	 */
	protected function renderHelperGeneric(ilTemplate $a_block_tpl, $a_block_id, array $a_block, $a_is_single = false)
	{
		if(!in_array($a_block_id, $this->rendered_blocks))
		{
			$this->rendered_blocks[] = $a_block_id;

			$block_types = array();
			if(is_array($this->block_items[$a_block_id]))
			{
				foreach($this->block_items[$a_block_id] as $item_id)
				{
					if(isset($this->items[$item_id]["type"]))
					{
						$block_types[] = $this->items[$item_id]["type"];
					}
				}
			}

			// #14610 - manage empty item groups
			if(is_array($this->block_items[$a_block_id]) ||
				is_numeric($a_block_id))
			{
				$cards = [];
				$order_id = (!$a_is_single && $this->active_block_ordering)
					? $a_block_id
					: null;
				$this->addHeaderRow($a_block_tpl, $a_block["type"], $a_block["caption"], array_unique($block_types), $a_block["actions"], $order_id, $a_block["data"]);

/*
				if($a_block["prefix"])
				{
					$this->addStandardRow($a_block_tpl, $a_block["prefix"]);
				}
*/

				if(is_array($this->block_items[$a_block_id]))
				{
					foreach($this->block_items[$a_block_id] as $item_id)
					{
						$cards[] = $this->items[$item_id]["html"];
						//$this->addStandardRow($a_block_tpl, $this->items[$item_id]["html"], $item_id);
					}
				}

/*
				if($a_block["postfix"])
				{
					$this->addStandardRow($a_block_tpl, $a_block["postfix"]);
				}
*/

				global $DIC;
				$f = $DIC->ui()->factory();
				$renderer = $DIC->ui()->renderer();

				//Create a deck with large cards
				//$deck = $f->deck($cards)->withNormalCardsSize();
				$deck = $f->deck($cards)->withSmallCardsSize();


				$html = $renderer->render($deck);

				$html = preg_replace('/###linkframe###([^#]*)###/i', '" target="$1', $html);

				$a_block_tpl->setVariable("CONTAINER_ROWS", $html);

				return true;
			}
		}

		return false;
	}


}