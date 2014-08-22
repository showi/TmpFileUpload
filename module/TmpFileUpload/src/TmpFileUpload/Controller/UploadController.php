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

namespace TmpFileUpload\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Http\Header;
use TmpFileUpload\Form\UploadForm;
use TmpFileUpload\Exception;
use TmpFileUpload\Model;
use TmpFileUpload\Helper\CommonHelper as MyHelper;

class UploadController extends AbstractActionController {

    protected $sessionContainer = Null;

    protected $fileTable = Null;

    protected $mimeTable = Null;

    protected $hash = Null;

    public function __construct()
    {
        $this->sessionContainer = new Container('file_upload');
    }

    public function indexAction()
    {
        try {
            $form = new UploadForm($this->getServiceLocator(), 'file-form');
            $request = $this->getRequest();
            if ($request->isPost()) {
                // Make certain to merge the files info!
                $post = array_merge_recursive($request->getPost()->toArray(),
                    $request->getFiles()->toArray());
                $form->setData($post);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $file = new Model\File();
                    $file->exchangeArray($data);
                    $tbl = $this->getFileTable();
                    $tbl->saveFile($file);
                    $data = $file->getArrayCopy();
                    $data['mime'] = $this->getMimeTable()->getMime($data['mime_id'])->value;
                    return $this->redirectToSuccessPage($data);
                }
            }
        } catch (Exception\HashExistsException $e) {
            $row = $this->getFileTable()->getHash($e->getMessage());
            $data = $row->getArrayCopy();
            error_log('HASH EXISTS: ' . print_r($data, true));
            $data['mime'] = $this->getMimeTable()->getMime($row->mime_id)->value;
            return $this->redirectToSuccessPage($data);
        } catch (Exception\FileSizeMaxException $e) {
            return $this->redirectToIndex(
                'File is to big: ' . $e->getMessage());
        }
        $message = $this->sessionContainer->message;
        return new ViewModel(
            array(
                'mimes' => $this->getMimeTable()->fetchAll(),
                'form' => $form,
                'message' => $message
            ));
    }

    protected function redirectToIndex($message = null)
    {
        if (! is_null($message)) {
            $this->sessionContainer->message = $message;
        }
        return $this->redirect()->toRoute('upload');
    }

//     protected function redirectToLink($pubkey)
//     {
//         $this->sessionContainer->pubkey = $pubkey;
//         error_log("Redirect to pubkey: $pubkey");
//         return $this->redirect()->toRoute('upload/success');
//     }

    protected function redirectToSuccessPage($formData = null)
    {
        $this->sessionContainer->formData = $formData;
        $response = $this->redirect()->toRoute('upload/success');
        $response->setStatusCode(303);
        return $response;
    }

    protected function deleteExpired() {
        $tbl = $this->getFileTable();
        $rs = $tbl->getExpired();
        $return = true;
        foreach($rs as $file) {
            error_log($file->toString());
            if (file_exists($file->path)){
                if(!unlink($file->path)) {
                    error_log('Cannot unlink file: ' . $file->path);
                    $return = false;
                    continue;
                } else {
                    error_log('File unlinked: ' . $file->path);
                }
            }
            if (!$tbl->deleteFile($file->id)) {
                error_log('Cannot remove file from database: ' . $file->path);
                $return = false;
            } else {
            	error_log('Database row deleted: ' . $file->path);
            }
        }
        return $return;
    }

    public function serveAction()
    {
        $this->deleteExpired();
        $pubkey = $this->params()->fromRoute('pubkey');
        $file = $this->getFileTable()->getPubkey($pubkey, true);
        if (! $file) {
            throw new Exception\PubkeyDoesntExistsException($pubkey);
        }
        $file->mime = $this->getMimeTable()->getMime($file->mime_id)->value;
        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Content-Type', $file->mime)
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', filesize($file->path));
        ob_clean();
        $response->setContent(file_get_contents($file->path));
        return $response;
    }

    public function successAction()
    {
        return array(
            'formData' => $this->sessionContainer->formData
        );
    }

    public function infoAction()
    {
        return array(
            'info' => phpinfo()
        );
    }

    public function getFileTable()
    {
        if (! $this->fileTable) {
            $sm = $this->getServiceLocator();
            $this->fileTable = $sm->get('TmpFileUpload\Model\FileTable');
        }
        return $this->fileTable;
    }

    public function getMimeTable()
    {
        if (! $this->mimeTable) {
            $sm = $this->getServiceLocator();
            $this->mimeTable = $sm->get('TmpFileUpload\Model\MimeTable');
        }
        return $this->mimeTable;
    }
}