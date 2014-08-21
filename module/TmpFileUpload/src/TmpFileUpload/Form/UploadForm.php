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

namespace TmpFileUpload\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter;
use Zend\Filter\File\RenameUpload;
use Zend\Form\Annotation\Input;
// use Zend\Validator\AbstractValidator;
// use Zend\Http\Header\AbstractAccept;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Ldap\Filter\AbstractFilter;
use Zend\Filter\Exception;
// class FilterUpload extends InputFilter\Input {

//     public function isValid($context = null) {
//         $rawValue  = $this->getRawValue();
//         error_log($rawValue);
//         return False;
//     }
// }
use TmpFileUpload\Exception as MyException;
use TmpFileUpload\Helper\CommonHelper as MyHelper;
use TmpFileUpload\Filter as MyFilter;

// class MyFilter extends \Zend\InputFilter\InputFilter {
//     public function toString() {
//         return "";
//     }
// }



class UploadForm extends Form //implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $fileTable;
    protected $mimeTable;

	public function __construct($serviceLocator, $name = null, $options = array())
	{
	    $this->setServiceLocator($serviceLocator);
		parent::__construct($name, $options);
		$this->addElements();
		$this->setInputFilter($this->createInputFilter());
	}

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
	    $this->serviceLocator = $serviceLocator;
	}

	public function getServiceLocator()
	{
	    return $this->serviceLocator;
	}

	public function getFileTable()
	{
	    if (!$this->fileTable) {
	        $sm = $this->getServiceLocator();
	        $this->fileTable = $sm->get('TmpFileUpload\Model\FileTable');
	    }
	    return $this->fileTable;
	}

	public function getMimeTable()
	{
	    if (!$this->mimeTable) {
	        $sm = $this->getServiceLocator();
	        $this->mimeTable = $sm->get('TmpFileUpload\Model\MimeTable');
	    }
	    return $this->mimeTable;
	}

	public function addElements()
	{
        // File Input
        $file = new Element\File('file-upload');
        $file
            ->setLabel('File Input')
            ->setAttributes(array(
                'id' => 'file-upload',
            ));
        $this->add($file);
        // Text Input
//         $text = new Element\Text('text');
//         $text->setLabel('Text Entry');
//         $this->add($text);
	}

	public function createInputFilter()
	{
		$inputFilter = new InputFilter\InputFilter();
		//$t = new RenameUpload();
//         error_log('createInputFilter');
//         $serviceLocator = $this->getServiceLocator();
//         error_log("ServiceLocator: $serviceLocator");
//         $serviceLocator->get('uploadfilter');
		// File Input
// 		$upload = new FilterUpload();
//         $inputFilter->add($upload);
		$file = new InputFilter\FileInput('file-upload');
		$file->setRequired(true);
// 		$file->getFilterChain()->attachByName(
// 			'TmpFileUpload\Form\MyRenameUpload',
// 			array(
// 					'target'          => './data/tmpuploads/',
// 					'overwrite'       => false,
// 					'use_upload_name' => false,
// 			        'randomize'         => true
// 		  )
// 		);
        $tmpUploadFilter = new MyFilter\UploadFilter($this, array(
					'target'          => './data/tmpuploads/',
					'overwrite'       => false,
					'use_upload_name' => false,
			        'randomize'         => true,
                    //'max_size' => ini_get('post_max_size'), useless
		  ));
        $file->getFilterChain()->attach($tmpUploadFilter, 1);
		$inputFilter->add($file);
//         $inputFilter->add(new MyFilter());
// 		// Text Input
// 		$text = new InputFilter\Input('text');
// 		$text->setRequired(false);
// 		$inputFilter->add($text);

		return $inputFilter;
	}
}