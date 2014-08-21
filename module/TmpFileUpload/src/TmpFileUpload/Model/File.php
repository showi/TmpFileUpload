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

namespace TmpFileUpload\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class File implements InputFilterAwareInterface
{
    public $id;
    public $pubkey;
    public $valid_until;
    public $hash;
    public $mime_id;
    public $mime;
    public $path;

    protected $_fields_name = array('pubkey', 'valid_until', 'hash', 'mime_id',
            'path');
    protected $inputFilter;

    public function exchangeArray($data)
    {
        if (key_exists('file-upload', $data)) {
            $data = $data['file-upload'];
        }
        error_log('ExchangeArray: ' . print_r($data, true));
        $this->id     = (isset($data['id']))     ? $data['id']     : null;
        foreach($this->_fields_name as $idx => $key) {
            error_log("Setting key: $key");
            $this->$key = (isset($data[$key]))? $data[$key] : null;
        }
//         $this->pubkey = (isset($data['pubkey'])) ? $data['pubkey'] : null;
//         $this->valid_until  = (isset($data['valid_until']))  ? $data['valid_until']  : null;
//         $this->hash  = (isset($data['hash']))  ? $data['hash']  : null;
//         $this->mime_id  = (isset($data['mime_id']))  ? $data['mime_id']  : null;
//         $this->path
    }

     // Add the following method:
    public function getArrayCopy()
    {
        $a = get_object_vars($this);
        unset($a['_fields_name'], $a['inputFilter'], $a['mime']);
        return $a;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
            		'name'     => 'mime_id',
            		'required' => true,
            		'filters'  => array(
            				array('name' => 'Int'),
            		),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'pubkey',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'ascii',
                            'min'      => 1,
                            'max'      => 64,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
            		'name'     => 'hash',
            		'required' => true,
            		'filters'  => array(
            				array('name' => 'StripTags'),
            				array('name' => 'StringTrim'),
            		),
            		'validators' => array(
            				array(
            						'name'    => 'StringLength',
            						'options' => array(
            								'encoding' => 'ascii',
            								'min'      => 1,
            								'max'      => 100,
            						),
            				),
            		),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'path',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'ascii',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'valid_until',
                'required' => true,
//                 'filters'  => array(
//                     array('name' => 'StripTags'),
//                     array('name' => 'StringTrim'),
//                 ),
					'validators'=>array(
					    array(
					        'name'=>'Date',
					        'break_chain_on_failure'=>true,
					        'options'=>array(
					            'format'=>'m-d-Y',
					            'messages'=>array(
					                'dateFalseFormat'=>'Invalid date format, must be mm-dd-yyy',
					                'dateInvalidDate'=>'Invalid date, must be mm-dd-yyy'
					            ),
					        ),
					    ),
					    array(
					        'name'=>'Regex',
					        'options'=>array(
					            'messages'=>array('regexNotMatch'=>'Invalid date format, must be mm-dd-yyy'),
					            'pattern'=>'/^\d{1,2}-\d{1,2}-\d{4}$/',
					        ),
					    ),
					),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function asArray()
    {
        return $this->getArrayCopy();
//         $data = array();
//         foreach($this->_fields_name as $key)
//         {
//             $data[$key] = $this->$key;
//         }
//         error_log('asArray: ' . print_r($data, true));
//         return $data;
    }
    public function toString() {
        return __CLASS__;
    }
}