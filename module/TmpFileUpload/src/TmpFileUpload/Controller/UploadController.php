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
        error_log('----- --- ----- --- ----- --- ----- --- ----- --- -----');

        #$mime = $this->getMimeTable()->fetchAll('image');
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
                        #$data['valid_until'] = MyHelper::validUntil("+5M");
                        #error_log("DATA: " . print_r($data, true));
                        $file = new Model\File();
                        $file->exchangeArray($data);
                        $tbl = $this->getFileTable();
                        $tbl->saveFile($file);
                        // Form is valid, save the form!
                        // return
                        // $this->redirect()->toRoute('upload-form/success');
                        echo print_r($data);
                        return $this->redirectToSuccessPage($form->getData());
                    }

            }
        } catch (Exception\HashExistsException $e) {
            $row = $this->getFileTable()->getHash($e->getMessage());
            return $this->redirectToLink($row->pubkey);
        } catch (Exception\FileSizeMaxException $e) {
            return $this->redirectToIndex('File is to big: ' .
                    $e->getMessage());
        }
//         $message = Null;
//         if (key_exists('message', $this->sessionContainer)) {
            $message = $this->sessionContainer->message;
//         }
        return new ViewModel(
            array(
                'mimes' => $this->getMimeTable()->fetchAll(),
                'form' => $form,
                'message' => $message,
            ));
        // return array('form' => $form);
    }

    protected function redirectToIndex($message = null) {
        if (!is_null($message)) {
            $this->sessionContainer->message = $message;
        }
        return $this->redirect()->toRoute('upload');
    }
    protected function redirectToLink($pubkey)
    {
        $this->sessionContainer->pubkey = $pubkey;
        error_log("Redirect to pubkey: $pubkey");
        return $this->redirect()->toRoute('upload/serve');
    }

    protected function redirectToSuccessPage($formData = null)
    {
        $this->sessionContainer->formData = $formData;
        $response = $this->redirect()->toRoute('upload/success');
        $response->setStatusCode(303);
        return $response;
    }

    public function serveAction()
    {
        $pubkey = $this->sessionContainer->pubkey;
        if (is_null($pubkey)) {
            $pubkey = $this->params()->fromRoute('pubkey');
        }
        return array(
            'pubkey' => $pubkey
        );
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