<?php

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

class MyFilter extends \Zend\InputFilter\InputFilter {
    public function toString() {
        return "";
    }

}

class TmpUploadFilter extends RenameUpload {

    protected $parent = Null;
    protected $fileTable = Null;

    public function __construct($parent, $targetOrOptions)
    {
        parent::__construct($targetOrOptions);
        $this->setParent($parent);
        $this->options['hash'] = Null;
        $this->fileTable = $this->parent->getFileTable();
    }
    public function setParent($parent)
    {
        error_log('Setting parent: ' . \spl_object_hash($parent));
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function filter($value) {
        error_log('Filtering...' . print_r($value, true));
        if (isset($value['tmp_name']) && !isset($this->alreadyFiltered[$value['tmp_name']])) {
            $hash = $this->hashFile($value['tmp_name']);
            $value['hash'] = $hash;
            //$this->setOptions(array('hash' => $hash));
        }
        $filter = parent::filter($value);
        if (false == $filter) {
        	return false;
        }
        $value = $filter;
        error_log('Filtering...' . print_r($value, true));
        return $value;
    }


    protected function hashFile($path) {
        error_log("Hashing file: $path");
//         function my_hash_file($filename, $algo="sha256", $raw_output=false) {
//             return hash_file($algo, $filename, $raw_output);
//         }
        $hash = hash_file('sha256', $path, false);
        $table = $this->getParent()->getFileTable();
        try {
            $table->hashExists($hash);
        } catch (MyException\HashDoesntExistsException $e) {
            throw new MyException\HashExistsException($hash);
            return \hash_file('sha256', $path, false);
        }
        error_log('File exists? ' . (file_exists($path) ? 'Yes' : 'No'));
        throw new MyException\HashExistsException($hash);
    }

//     protected function applyRandomToFilename($source, $filename)
//     {
//         $info = pathinfo($filename);
//         $filename = $info['filename'] . uniqid('_');
//         $sourceinfo = pathinfo($source);
//         $extension = '';
//         if ($this->getUseUploadExtension() === true && isset($sourceinfo['extension'])) {
//             $extension .= '.' . $sourceinfo['extension'];
//         } elseif (isset($info['extension'])) {
//             $extension .= '.' . $info['extension'];
//         }
//         return $filename . $extension;
//     }
}

class UploadForm extends Form //implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $fileTable;

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
        $tmpUploadFilter = new TmpUploadFilter($this, array(
					'target'          => './data/tmpuploads/',
					'overwrite'       => false,
					'use_upload_name' => false,
			        'randomize'         => true
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