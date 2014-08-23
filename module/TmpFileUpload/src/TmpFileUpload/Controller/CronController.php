<?php
namespace TmpFileUpload\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Math\Rand;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use TmpFileUpload\Helper\CommonHelper as Helper;

$VERSION = "0.0.1";

function l($msg, $eol = "\n") {
    echo "$msg$eol";
}

class CronController extends AbstractActionController
                     implements ServiceLocatorAwareInterface
{
    private $VERSION = "0.0.1";
    protected $config;

    public function __construct() {
    }

    public function getConfig() {
        if(!$this->config) {
            $this->config = $this->getServiceLocator()->get('Config');
        }
        return $this->config;
    }
    public function getFileTable() {
        if (!$this->fileTable) {
        	$this->fileTable = Helper::getFileTable($this->getServiceLocator());
        }
        return $this->fileTable;
    }

    public function getParam($name) {
        return $this->getRequest()->getParam($name);
    }

    public function genCronLine() {
        $path = '*/5 * * * * ' . realpath('public/') . '/index.php cron';
        l("Path: $path");
        return $path;
    }

    public function installCron() {
        $bin = $this->getConfig()['bin_crontab'];
        $tmp = tmpfile();
        $meta = stream_get_meta_data($tmp);
        $fn = $meta['uri'];
        fwrite($tmp, $this->genCronLine() . "\n");
        fflush($tmp);
        $result = null;
        system("$bin $fn", $result);
        if ($result != 0) {
            l('[-] >> Cannot install cron!!!');
            return false;
        }
        l('[+] >> Cron installed');
        return true;
    }
    public function isCronInstalled() {
        $bin = $this->getConfig()['bin_crontab'];
        if (!is_executable($bin)) {
            l('Cron is not executable, ' . $bin);
        }
        $gencronline = trim($this->genCronLine());
        l('Gine: ' . $gencronline);
        $return = null;
        exec("$bin -l", $result, $return);
        l("Return: " . print_r($return, true));
        if ($return != 0) {
            l('Cannot execute crontab');
            return false;
        }
        if (count($result) == 0) {
        	l('Crontab is empty');
        	return false;
        }
        foreach($result as $line) {
            $line = trim($line);
            l("Line: $line");
            if ($line == $gencronline) {
                return true;
            }
        }
        return false;
    }

    public function installAction() {
        l('----- Installing script in system----');
        if (!$this->isCronInstalled()) {
            l('[+] installing cron');
            $this->installCron();
        } else {
            l('cron already installed');
        }
        return new ViewModel();
    }

    public function entryAction()
    {
        if ($this->getParam('install')) {
        	return $this->installAction();
        }
        l("---- TmpFileUpload - Cron (".$this->VERSION.") -----");
        $tbl = Helper::getFileTable($this->getServiceLocator());
        $rowset = $tbl->getExpired();
        if (sizeof($rowset) <= 0) {
            l('Nothing to do');
        } else {
            l('[*] Deleting files:');
            foreach ($rowset as $idx => $file) {
            	$msg = ' -' . $file->path . ' ';
            	if (!unlink($file->path)) {
                    $msg .= 'Fail';
            	    l($msg);
                    continue;
            	}
            	$msg .= "Ok";
            	l($msg);
                $tbl->deleteFile($file->id);
                l("\t" . 'Associated row removed from database (' . $file->id . ')');
            }
        }
        return new ViewModel(); // display standard index page
    }

//     public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
//         $this->serviceLocator = $serviceLocator;
//     }

    public function resetpasswordAction(){
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Get user email from console and check if the user used --verbose or -v flag
        $userEmail   = $request->getParam('userEmail');
        $verbose     = $request->getParam('verbose');

        // reset new password
        $newPassword = Rand::getString(16);

        //  Fetch the user and change his password, then email him ...
        // [...]

        if (!$verbose){
            return "Done! $userEmail has received an email with his new password.\n";
        }else{
            return "Done! New password for user $userEmail is '$newPassword'. It has also been emailed to him. \n";
        }
    }
}