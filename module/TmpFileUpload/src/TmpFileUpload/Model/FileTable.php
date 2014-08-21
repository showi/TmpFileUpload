<?php

namespace TmpFileUpload\Model;

use Zend\Db\TableGateway\TableGateway;
use TmpFileUpload\Exception;

class FileTable
{
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
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function hashExists($hash) {
        $rowset = $this->tableGateway->select(array('hash' => $hash));
        $row = $rowset->current();
        if (!$row) {
            throw new Exception\HashDoesntExistsException("Could not find hash $hash");
        }
        return $row;
    }

    public function saveFile(File $file)
    {
        $data = array(
            'pubkey' => $file->pubkey,
            'valid_until'  => $file->valid_until,
        	'hash'  => $file->hash,
        	'mime_id' => $file->mime_id
        );

        $id = (int)$file->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getFile($id)) {
                $this->tableGateway->update($data, array('id' => $id));
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