<?php
namespace TmpFileUpload\Filter;
use Zend\Filter\File\RenameUpload;
use TmpFileUpload\Helper\CommonHelper as MyHelper;
use TmpFileUpload\Exception as MyException;

class UploadFilter extends RenameUpload {

    protected $parent = Null;
    protected $fileTable = Null;
    protected $mimeTable = Null;

    public function __construct($parent, $targetOrOptions)
    {
        $this->setParent($parent);
//         $this->options['hash'] = Null;
//         $this->options['max_size'] = Null;
        parent::__construct($targetOrOptions);
    }

    public function setMaxSize($size) {
        error_log("Setting max_size: $size");
        $this->options['max_size'] = MyHelper::convertPHPSizeToBytes($size);
        return $this;
    }
    public function getMaxSize($size) {
        return $this->options['max_size'];
    }

    protected function checkFileExists($targetFile)
    {
        $dir = dirname($targetFile);
        if (!is_writable($dir)) {
            throw new MyException\DirectoryNotWritableException($dir);
        }
        if (file_exists($targetFile)) {
            if ($this->getOverwrite()) {
                unlink($targetFile);
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf("File '%s' could not be renamed. It already exists.", $targetFile)
                );
            }
        }
    }
    public function setParent($parent)
    {
        error_log('Setting parent: ' . \spl_object_hash($parent));
        $this->parent = $parent;
        if (is_null($parent)) {
        	return;
        }
        $this->fileTable = $this->parent->getFileTable();
        $this->mimeTable = $this->parent->getMimeTable();
        return;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function filter($value) {
        error_log('Filtering...' . print_r($value, true));
        if (key_exists('max_size', $this->getOptions())) {
            $max_size = $this->getOptions()['max_size'];
            error_log("max_size: $max_size");
            if  ($max_size > 0) {
                error_log('Checking size');
                if ($max_size <= (int) $value['size']) {
                    throw new MyException\FileSizeMaxException($value['size']);
                }
            }
        }
        $value['mime_id'] = $this->getMimeByName($value['type'])->id;
        if (isset($value['tmp_name']) && !isset($this->alreadyFiltered[$value['tmp_name']])) {
            $value['hash'] = $this->getHash($value['tmp_name']);
            $value['pubkey'] = MyHelper::randomString(64);
            $value['valid_until'] = MyHelper::validUntil("+5M");
        }
        error_log('parent::Filter');
        $filter = parent::filter($value);
        if ($filter === false) {
        	return false;
        }
        $value = $filter;
        if (is_array($value)) {
            $value['path'] = $value['tmp_name'];
        }
        error_log("File moved to finale destination: " . $value['path']);
        error_log(print_r($value, true));
        return $value;
    }

    protected function getMimeByName($mime) {
        $row = $this->mimeTable->getValue($mime);
        if (!$row) {
            throw MyException\InvalidMimeException($mime);
        }
        return $row;
    }

    protected function getHash($path) {
        error_log("Hashing file: $path");
//         function my_hash_file($filename, $algo="sha256", $raw_output=false) {
//             return hash_file($algo, $filename, $raw_output);
//         }
        $hash = hash_file('sha256', $path, false);
        $table = $this->getParent()->getFileTable();
        try {
            $table->getHash($hash);
        } catch (MyException\HashDoesntExistsException $e) {
            //throw new MyException\HashExistsException($hash);
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