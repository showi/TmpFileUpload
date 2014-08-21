<?php

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

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id']))     ? $data['id']     : null;
        $this->pubkey = (isset($data['pubkey'])) ? $data['pubkey'] : null;
        $this->valid_until  = (isset($data['valid_until']))  ? $data['valid_until']  : null;
        $this->hash  = (isset($data['hash']))  ? $data['hash']  : null;
        $this->mime_id  = (isset($data['mime_id']))  ? $data['mime_id']  : null;        
    }

     // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
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
                            'max'      => 100,
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
}