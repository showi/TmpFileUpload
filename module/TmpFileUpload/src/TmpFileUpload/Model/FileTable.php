<?php
/*
Copyright (c) 2014 Joachim Basmaison

This file is part of TmpFileUpload <https://github.com/showi/TmpFileUpload>

TmpFileUpload is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

See the GNU General Public License for more details.
*/

namespace TmpFileUpload\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
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

    public function getExpired() {
        $where = new Where();
        $now =  date('Y-m-d H:i:s', strtotime("now"));
        $where->lessThan('valid_until', $now);
        return $this->tableGateway->select($where);
    }

    public function getPubkey($pubkey, $notExpired=true)
    {
        $where = new Where();
        $where->equalTo('pubkey', $pubkey);
        if ($notExpired) {
            $now = date('Y-m-d H:i:s', strtotime("now"));
            $where->greaterThanOrEqualTo('valid_until', $now);
        }
        $rowset = $this->tableGateway->select($where);
        $row = $rowset->current();
        if (! $row) {
            throw new Exception\PubkeyDoesntExistsException($pubkey);
        }
        return $row;
    }

    public function saveFile(File $file)
    {
        $id = (int) $file->id;
        if ($id == 0) {
            $this->tableGateway->insert($file->getArrayCopy());
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
        return $this->tableGateway->delete(array('id' => $id));
    }
}