<?php

class ServiceUserIdentity extends RestUserIdentity
{

    const ERROR_NOT_AUTHENTICATED = 3;

    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function authenticate()
    {

        if (Yii::app()->user->isGuest) {

            $serviceModel = Userlink::model()->findByAttributes(array(
                'serviceUsername' => $this->service->id,
            ));
            /* Если в таблице tbl_service нет записи с таким id,
            значит сервис не привязан к аккаунту. */
            if ($serviceModel == null) {

                if ($this->service->isAuthenticated) {

                    $this->Registration();
//                $this->setState('service', $this->service->serviceName);

                    $this->errorCode = self::ERROR_NONE;
                } else {
                    $this->errorCode = self::ERROR_NOT_AUTHENTICATED;
                }
            } /* Если запись есть, то используем данные из
        таблицы tbl_users, используя связь в модели Service */
            else {
                $this->_id = $serviceModel->userId;
                $this->username = $this->service->getAttribute('name');
                $this->setState('photo', $this->service->getAttribute('photo'));
                $this->errorCode = self::ERROR_NONE;
            }
        }
        else {

            $serviceModel = Userlink::model()->findByAttributes(array(
                'serviceUsername' => $this->service->id,
            ));

            if ($serviceModel == null) {

                $service = new Userlink();
                $service->userId = Yii::app()->user->id;
                $service->serviceUsername = $this->service->id;
                $service->serviceName = $this->service->getServiceName();
                $params['photo'] = $this->service->getAttribute('photo');
                $params['name'] = $this->service->getAttribute('name');
                $params['url'] = $this->service->getAttribute('url');
                $service->params = json_encode($params);
                $service->save();

            } else {
                $this->_id = $serviceModel->userId;
                $this->username = $this->service->getAttribute('name');
                $this->setState('photo', $this->service->getAttribute('photo'));
                $this->errorCode = self::ERROR_NONE;
            }
        }
        return !$this->errorCode;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function Registration()
    {

        $users = new User();
        $users->username = $this->service->getAttribute('name');
        $users->password = md5($this->service->getId());
        $users->email = $this->service->getServiceName() . '@services.ru';
        $users->save();

        $service = new Userlink();
        $service->userId = $users->id;
        $service->serviceUsername = $this->service->id;
        $service->serviceName = $this->service->getServiceName();
        $params['photo'] = $this->service->getAttribute('photo');
        $params['name'] = $this->service->getAttribute('name');
        $params['url'] = $this->service->getAttribute('url');
        $service->params = json_encode($params);
        $service->save();

        $this->_id = $users->id;
        $this->username = $this->service->getAttribute('name');
        $this->setState('photo', $this->service->getAttribute('photo'));

        return 0;

    }

}