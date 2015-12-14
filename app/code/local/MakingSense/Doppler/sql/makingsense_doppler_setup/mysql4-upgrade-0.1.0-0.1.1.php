<?php

$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('makingsense_doppler/doppler_leadmap'))) {
	$installer->run("
		CREATE TABLE `{$installer->getTable('makingsense_doppler/doppler_leadmap')}` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
}
	
$installer->endSetup();