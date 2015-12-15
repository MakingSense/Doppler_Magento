<?php

$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('makingsense_doppler/doppler_lists'))) {
	$installer->run("
		CREATE TABLE `{$installer->getTable('makingsense_doppler/doppler_lists')}` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  `last_usage` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
}
	
$installer->endSetup();