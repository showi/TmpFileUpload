<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $eventManager        = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
//         $sharedManager = $application->getEventManager()->getSharedManager();
//         $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
//             function($e) use ($sm) {
//                 $sm->get('Zend\Log\Logger')->crit($e->getParam('exception'));
//                 if ($e->getParam('exception')){
//                     $sm->get('Zend\Log\Logger')->crit($e->getParam('exception'));
//                 }
//             }
//         );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
