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


        if (!$this->service->isAuthenticated) {
            $this->errorCode = self::ERROR_NOT_AUTHENTICATED;
            return false;
        }
        $this->errorCode = self::ERROR_NONE;


        /**
         * проверяем профиль соцсети
         */
        $userlink = Userlink::model()->findByAttributes(array(
            'serviceName' => $this->service->getServiceName(),
            'serviceUsername' => $this->service->id,
        ));
        if (!$userlink) {
            /**
             * данный профиль соцсети не привязан, создаем связку
             */
            $userlink = new Userlink();
            $userlink->serviceUsername = $this->service->id;
            $userlink->serviceName = $this->service->getServiceName();
        }


        /**
         * Вычисляем наш userId
         */
        //Проверяем залогинены ли мы
        $userId = Yii::app()->user->id;

//        var_dump($userId);
        //Если нет, то пробуем посмотреть в связку с соцсетью
        if (!$userId) {
            $userId = $userlink->userId;
        }

//        var_dump($userId);
        //Если нет, то регистрируемся
        if (!$userId) {
            $user = $this->registration();
            $userId = $user->id;
        }
//        var_dump($userId);

        if (!$userId) {
            throw new Exception('Чтото пошло не так');
        }
        /**
         * приязываем\перепривязываем соц сеть
         * Обновляем атрибуты соцсети
         */
        $userlink->userId = $userId;
        $userlink->params = ['attributes' => $this->service->attributes];
        $userlink->save();

        $this->_id = $userId;

        return !$this->errorCode;
    }

    public function registration()
    {

        $user = new User();
        $user->username = md5($this->service->getId());
        $user->password = md5($this->service->getId());
        $user->email = null;
        if (!$user->save()) {
            throw new Exception('Cant save user');
        }


        $this->_id = $user->id;
        $this->username = $this->service->getAttribute('name');

        return $user;

    }

}