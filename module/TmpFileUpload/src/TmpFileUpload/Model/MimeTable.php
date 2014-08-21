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
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Where;
// use Zend\Db\Sql\Sql;
// use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Predicate\Like;

class MimeTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($startswith=Null)
    {
    	$resultSet = Null;
    	if (is_null($startswith)) {
        	$resultSet = $this->tableGateway->select();
    	} else {
    		$where = new Where();
    		$where->like('value', '%' . $startswith . '%');
    		$resultSet = $this->tableGateway->select($where);
    	}
        return $resultSet;
    }

    public function getMime($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getValue($value) {
    	$rowset = $this->tableGateway->select(array('value' => $value));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find mime $value");
    	}
    	return $row;
    }

    public function saveMime(File $file)
    {
        $data = array(
            'value' => $file->value,
        );
        $id = (int)$file->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getMime($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteMime($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}
