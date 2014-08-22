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

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
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
        $where->lessThanOrEqualTo('valid_until', 'now()');
        return $this->tableGateway->select($where);
    }

    public function getPubkey($pubkey, $notExpired=true)
    {

        $where = new Where();
        $where->equalTo('pubkey', $pubkey);
        if ($notExpired) {
            $where->greaterThan('valid_until', 'now()');
        }
        $rowset = $this->tableGateway->select($where);
//         $rowset = $this->tableGateway->select(array(
//             'pubkey' => $pubkey
//         ));
        $row = $rowset->current();
        if (! $row) {
            throw new Exception\PubkeyDoesntExistsException($pubkey);
        }
        error_log('getPubkey: ' . print_r($row, true));
        return $row;
        //$resultSet = Null;
//         if (is_null($startswith)) {
//             $resultSet = $this->tableGateway->select();
//         } else {
//             $where = new Where();
//             $where->like('value', '%' . $startswith . '%');
//             $resultSet = $this->tableGateway->select($where);
//         }
        return $resultSet;
    }

    public function saveFile(File $file)
    {
        error_log($file->toString());
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