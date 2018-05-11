<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUFreibCourseItemImage
{
	static $instance = null;

	/**
	 */
	protected function __construct()
	{
	}

	/**
	 * Get instance
	 *
	 * @return ilUFreibCourseItemImage
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Get directory
	 *
	 * @param
	 * @return
	 */
	protected function getDir($a_ref_id)
	{
		return "plugin/ufreibpsy/item_".$a_ref_id;
	}

	/**
	 * Upload
	 *
	 * @param int $a_item_ref_id
	 */
	public function upload($a_item_ref_id)
	{
		global $DIC;

		$dir = $this->getDir($a_item_ref_id);

		$storage = $DIC->filesystem()->storage();
		try
		{
			$storage->deleteDir($dir);
		} catch (Exception $e) {};

		$upload = $DIC->upload();
		$upload->process();
		$res = $upload->getResults();
		if ($first = current($res))
		{
			if ($first->getName() != "")
			{
				$ext = pathinfo($first->getName(), PATHINFO_EXTENSION);
				$new_name = $a_item_ref_id . "." . strtolower($ext);
				$upload->moveOneFileTo($first, $dir,
					ILIAS\FileUpload\Location::STORAGE, $new_name, true);

				$fullpath = CLIENT_DATA_DIR . "/" . $dir . "/" . $new_name;
				list($width, $height, $type, $attr) = getimagesize($fullpath);
				$min = min($width, $height);
				ilUtil::execConvert($fullpath . "[0] -geometry " . $min . "x" . $min . "^ -gravity center -extent " . $min . "x" . $min . " " . $fullpath);
			}
		}

		//$upload->moveFilesTo($this->getDir($a_item_ref_id), ILIAS\FileUpload\Location::STORAGE);

	}

	/**
	 * Delete
	 *
	 * @param int $a_item_ref_id
	 */
	public function delete($a_item_ref_id)
	{
		global $DIC;

		$dir = $this->getDir($a_item_ref_id);
		$storage = $DIC->filesystem()->storage();
		try
		{
			$storage->deleteDir($dir);
		} catch (Exception $e) {};
	}

	/**
	 * Get image name
	 *
	 * @param int $a_item_ref_id
	 * @return string
	 */
	public function getImageName($a_item_ref_id)
	{
		global $DIC;

		$dir = $this->getDir($a_item_ref_id);
		$storage = $DIC->filesystem()->storage();

		if ($storage->hasDir($dir))
		{
			$files = $storage->listContents($dir, false);
			$f = current($files);
			return pathinfo($f->getPath(), PATHINFO_BASENAME);
		}
		return "";
	}


	/**
	 * Does image exist?
	 *
	 * @param int $a_item_ref_id
	 * @return boolean
	 */
	public function exists($a_item_ref_id)
	{
		if ($this->getImageName($a_item_ref_id) != "")
		{
			return true;
		}
		return false;
	}

	/**
	 * Send
	 *
	 * @param int $a_item_ref_id
	 */
	public function send($a_item_ref_id)
	{
		global $DIC;

		$dir = $this->getDir($a_item_ref_id);
		$storage = $DIC->filesystem()->storage();

		if ($storage->hasDir($dir))
		{
			$files = $storage->listContents($dir, false);
			if ($f = current($files))
			{
				$fileStream = $storage->readStream($f->getPath());
				echo $fileStream->getContents();
			}
		}
		exit;
	}



}

?>