<?php
/*
Copyright (c) 2014 Joachim Basmaison

This file is part of TmpFileUpload <https://github.com/showi/TmpFileUpload>

TmpFileUpload is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

See the GNU General Public License for more details.
*/


namespace TmpFileUpload;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use TmpFileUpload\Model\File;
use TmpFileUpload\Model\FileTable;
use TmpFileUpload\Model\Mime;
use TmpFileUpload\Model\MimeTable;

class Module
{
	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php',
				),
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
		);
	}

	public function getServiceConfig()
	{
		return array(
				'factories' => array(
						'TmpFileUpload\Model\FileTable' =>  function($sm) {
							$tableGateway = $sm->get('FileTableGateway');
							$table = new FileTable($tableGateway);
							return $table;
						},
						'FileTableGateway' => function ($sm) {
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							$resultSetPrototype = new ResultSet();
							$resultSetPrototype->setArrayObjectPrototype(new File());
							return new TableGateway('file', $dbAdapter, null, $resultSetPrototype);
						},
						'TmpFileUpload\Model\MimeTable' =>  function($sm) {
							$tableGateway = $sm->get('MimeTableGateway');
							$table = new MimeTable($tableGateway);
							return $table;
						},
						'MimeTableGateway' => function ($sm) {
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							$resultSetPrototype = new ResultSet();
							$resultSetPrototype->setArrayObjectPrototype(new Mime());
							return new TableGateway('mime', $dbAdapter, null, $resultSetPrototype);
						},
				),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
}