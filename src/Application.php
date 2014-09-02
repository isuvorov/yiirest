<?php

/**
 * # Web Application
 *
 * @property \Restyii\Meta\Schema $schema the application schema
 * @property \Restyii\Web\UrlManager $urlManager the url manager
 * @property \Restyii\Web\Request $request the http request
 * @property \Restyii\Event\AbstractEventStream $eventStream the application event stream
 *
 * @method \Restyii\Web\Request getRequest()
 *
 * @author Charles Pick <charles@codemix.com>
 * @package Restyii\Web
 */
class Application extends \CWebApplication
{

    protected function registerCoreComponents()
    {

        parent::registerCoreComponents();

        require_once(__DIR__.'/components/ActiveRecord.php');
        require_once(__DIR__.'/components/Cipher.php');
        require_once(__DIR__.'/components/Controller.php');
        require_once(__DIR__.'/components/RecentComments.php');
        require_once(__DIR__.'/components/UserIdentity.php');
        require_once(__DIR__.'/components/ServiceUserIdentity.php');
        require_once(__DIR__.'/components/RestController.php');

//        $components=array(
//            'ActiveRecord' => array(
//                'class' => 'ActiveRecord',
//            ),
//            'Cipher'=>array(
//                'class' =>'Cipher',
//            ),
//            'Controller' => array(
//                'class' => 'Controller',
//            ),
//            'RecentComments' => array(
//                'class' => 'RecentComments',
//            ),
//            'RestController' => array(
//                'class' => 'RestController',
//            ),
//            'ServiceUserIdentity' => array(
//                'class' => 'ServiceUserIdentity',
//            ),
//            'UserIdentity' => array(
//                'class' => 'UserIdentity',
//            ),
//        );
//        $this->setComponents($components);
    }
//
//    /**
//     * @return \Restyii\Meta\Schema the schema for the application
//     */
    public function getActiveRecord()
    {
        return $this->getComponent('ActiveRecord');
    }

    public function getCipher()
    {
        return $this->getComponent('Cipher');
    }

    public function getRecentComments()
    {
        return $this->getComponent('RecentComments');
    }

    public function getRestController()
    {
        return $this->getComponent('RestController');
    }

    public function getServiceUserIdentity()
    {
        return $this->getComponent('ServiceUserIdentity');
    }

    public function getUserIdentity()
    {
        return $this->getComponent('UserIdentity');
    }

}
