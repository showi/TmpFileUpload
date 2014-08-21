<?php
namespace TmpFileUpload\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use TmpFileUpload\Form\UploadForm;
use TmpFileUpload\Exception;

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
                        // Form is valid, save the form!
                        // return
                        // $this->redirect()->toRoute('upload-form/success');
                        echo print_r($data);
                        return $this->redirectToSuccessPage($form->getData());
                    }

            }
        } catch (Exception\HashExistsException $e) {
            return $this->redirectToLink($e->getMessage());
        }
        return new ViewModel(
            array(
                'mimes' => $this->getMimeTable()->fetchAll(),
                'form' => $form
            ));
        // return array('form' => $form);
    }

    protected function redirectToLink($hash)
    {
        $this->sessionContainer->hash = $hash;
        error_log("Redirect to hash: $hash");
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
        $hash = $this->sessionContainer->hash;
        if (is_null($hash)) {
            $hash = $this->params()->fromRoute('hash');
        }
        return array(
            'hash' => $hash
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