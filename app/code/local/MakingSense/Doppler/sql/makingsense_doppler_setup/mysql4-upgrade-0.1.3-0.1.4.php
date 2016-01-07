<?php
/**
 * 0.1.3 - 0.1.4 upgrade installer
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('makingsense_doppler/doppler_defaultlist'))) {
	$installer->run("
		CREATE TABLE `{$installer->getTable('makingsense_doppler/doppler_defaultlist')}` (
		  `listId` int(11) unsigned NOT NULL,
		  `name` varchar(255) DEFAULT NULL,
		  `last_import` datetime DEFAULT NULL,
		  PRIMARY KEY (`listId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
}

$installer->endSetup();