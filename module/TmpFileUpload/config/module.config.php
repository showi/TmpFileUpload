<?php
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
                            'route' => '/serve[/:hash]',
                            //'route'    => '/album[/:action][/:id]',
                            'constraints' => array(
                              //  'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'hash'     => '[a-zA-Z0-9]+',
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