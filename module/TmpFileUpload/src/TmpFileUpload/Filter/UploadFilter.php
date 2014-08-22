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
        $this->options['delete_meta'] = false;
        parent::__construct($targetOrOptions);
    }

    public function setDeleteMeta($bool) {
        $this->options['delete_meta'] = (bool) $bool;
    }
    public function getDeleteMeta() {
        return $this->options['delete_meta'];
    }
    public function setMaxSize($size)
    {
        $this->options['max_size'] = MyHelper::convertPHPSizeToBytes($size);
        return $this;
    }

    public function getMaxSize($size)
    {
        return $this->options['max_size'];
    }

    protected function checkFileExists($targetFile)
    {
        $dir = dirname($targetFile);
        if (! is_writable($dir)) {
            throw new MyException\DirectoryNotWritableException($dir);
        }
        if (file_exists($targetFile)) {
            if ($this->getOverwrite()) {
                unlink($targetFile);
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        "File '%s' could not be renamed. It already exists.",
                        $targetFile));
            }
        }
    }

    public function setParent($parent)
    {
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

    public function filter($value)
    {
        if (key_exists('max_size', $this->getOptions())) {
            $max_size = $this->getOptions()['max_size'];
            if ($max_size > 0) {
                if ($max_size <= (int) $value['size']) {
                    throw new MyException\FileSizeMaxException($value['size']);
                }
            }
        }
        $value['mime_id'] = $this->getMimeByName($value['type'])->id;
        $value['mime'] = $value['type'];
        if (MyHelper::startsWith($value['mime'], 'image/')) {
            if($this->getDeleteMeta()) {
                if (!$this->removeMeta($value['tmp_name'])) {
                    error_log('Cannot remove meta from image: ' .
                        $value['tmp_name']);
                    return false;
                }
            }
        }
        if (isset($value['tmp_name']) &&
             ! isset($this->alreadyFiltered[$value['tmp_name']])) {
            $value['hash'] = $this->getHash($value['tmp_name']);
            $value['pubkey'] = MyHelper::randomString(64);
            $expire_in = $this->parent->getConfig()['file_expire_in'];
            $value['valid_until'] = MyHelper::validUntil($expire_in);
        }
        $filter = parent::filter($value);
        if ($filter === false) {
            return false;
        }
        $value = $filter;
        if (is_array($value)) {
            $value['path'] = $value['tmp_name'];
        }
        return $value;
    }

    protected function removeMeta($path) {
        $binary = $this->parent->getConfig()['bin_exiv2'];
        return MyHelper::removeMeta($binary, $path);
    }

    protected function getMimeByName($mime)
    {
        $row = $this->mimeTable->getValue($mime);
        if (! $row) {
            throw MyException\InvalidMimeException($mime);
        }
        return $row;
    }

    protected function getHash($path)
    {
        $hash = hash_file('sha256', $path, false);
        $table = $this->getParent()->getFileTable();
        try {
            $table->getHash($hash);
        } catch (MyException\HashDoesntExistsException $e) {
            return\hash_file('sha256', $path, false);
        }
        throw new MyException\HashExistsException($hash);
    }
}