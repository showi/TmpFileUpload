<?php
/*
* Copyright (c) 2014 Joachim Basmaison
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License,
* or (at your option) any later version. This program is distributed in the
* hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
* implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
* See the GNU General Public License for more details.
*/
return array(
    'bin_exiv2' => '/usr/bin/exiv2',
    'bin_crontab' => '/usr/bin/crontab',
    'bin_php' => '/usr/bin/php',
    'file_expire_in' => '+5 min',
    'db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=tmpfileupload;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
        )
    ),
    'factories' => array(
        'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
    ),
    'controllers' => array(
        'invokables' => array(
            'TmpFileUpload\Controller\Upload' => 'TmpFileUpload\Controller\UploadController',
            'TmpFileUpload\Controller\Cron' => 'TmpFileUpload\Controller\CronController'
        )
    ),
    'router' => array(
        'routes' => array(
            'tfu' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/tfu',
                    'defaults' => array(
                        'controller' => 'TmpFileUpload\Controller\Upload',
                        'action' => 'upload'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'success' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/success',
                            'defaults' => array(
                                'controller' => 'TmpFileUpload\Controller\Upload',
                                'action' => 'success'
                            )
                        )
                    ),
                    'about' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/about',
                            'defaults' => array(
                                'controller' => 'TmpFileUpload\Controller\Upload',
                                'action' => 'about'
                            )
                        )
                    ),
                    'serve' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/serve[/:pubkey]',
                            'constraints' => array(
                                'pubkey'     => '[a-zA-Z0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'TmpFileUpload\Controller\Upload',
                                'action' => 'serve'
                            )
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'tmpfileupload' => __DIR__ . '/../view'
        )
    ),
    'filters' => array(
	   'invokables' => array(
    	   'uploadfilter' => '\TmpFileUpload\Filter\UploadFilter',
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'cron-executable' => array(
                    'options' => array(
                         'route'    => 'cron [install] [remove]',
                         'defaults' => array(
                                'controller' => 'TmpFileUpload\Controller\Cron',
                                'action'     => 'entry'
                            )
                    )
                )
            )
        )
    )
);