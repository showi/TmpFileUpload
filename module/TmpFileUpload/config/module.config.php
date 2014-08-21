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
    'controllers' => array(
        'invokables' => array(
            'TmpFileUpload\Controller\Upload' => 'TmpFileUpload\Controller\UploadController'
        )
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'upload' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/upload',
                    'defaults' => array(
                        'controller' => 'TmpFileUpload\Controller\Upload',
                        'action' => 'index'
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
                    'serve' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/serve[/:pubkey]',
                            //'route'    => '/album[/:action][/:id]',
                            'constraints' => array(
                              //  'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
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
);