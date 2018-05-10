<#1>
<?php
$fields = array(
	'crs_ref_id' => array (
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
$ilDB->createTable("ufreibpsy_courses", $fields);
$ilDB->addPrimaryKey("ufreibpsy_courses", array("crs_ref_id"));

?>