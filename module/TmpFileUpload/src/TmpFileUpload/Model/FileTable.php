<?php
/*
 * Copyright (c) 2014 Joachim Basmaison This program is free software; you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version. This program is
 * distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */
namespace TmpFileUpload\Model;

use Zend\Db\TableGateway\TableGateway;
use TmpFileUpload\Exception;

class FileTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getFile($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array(
            'id' => $id
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getHash($hash)
    {
        $rowset = $this->tableGateway->select(array(
            'hash' => $hash
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new Exception\HashDoesntExistsException($hash);
        }
        return $row;
    }

    public function getPubkey($pubkey)
    {
        $rowset = $this->tableGateway->select(array(
            'pubkey' => $pubkey
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new Exception\PubkeyDoesntExistsException($pubkey);
        }
        error_log('getPubkey: ' . print_r($row, true));
        return $row;
    }

    public function saveFile(File $file)
    {
        error_log($file->toString());
        $id = (int) $file->id;
        if ($id == 0) {
            $this->tableGateway->insert($file->asArray());
        } else {
            if ($this->getFile($id)) {
                $this->tableGateway->update($data, array(
                    'id' => $id
                ));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteFile($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}