<?php

$installer = $this;

$connection = $installer->getConnection();

$installer->startSetup();

$connection->modifyColumn(
  $installer->getTable('jobqueue/job'),
  'handler',
  'BLOB NOT NULL'
);

$installer->endSetup();