<?php
namespace TmpFileUpload\Filter;
use Zend\InputFilter\InputFilter;

class UploadFilter extends InputFilter {
    public function init()
    {
        $this->add(array(
            'name' => 'glassname',
            'required' => true,
            'filters' => array(
                array('name' => 'StringToUpper'),
            ),
            'validators' => array(
                array( 'name' => 'StringLength', 'options' => array('min' => 3),
                ),
            ))
        );
    }
}