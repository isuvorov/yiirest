<?php


class RestAuthController extends RestController
{

    public function actionReg()
    {
        $data = file_get_contents("php://input");
        $params = json_decode($data, 1);
        $user = new User();
        $user->attributes = $params;
        $user->password = $user->hashPassword($params['password']);
        $user->save();
        $this->login($params['username'], $params['password']);
    }

    public function actionLogin()
    {
        $data = file_get_contents("php://input");
        $params = json_decode($data, 1);
        $this->login($params['username'], $params['password']);
    }


    private function login($username, $password)
    {

        if ($username == null || $password == null) {
            throw new CHttpException('400', CJSON::encode('Переданны не все данные'));
        }

        $identity = new UserIdentity($username, $password);

        if (!$identity->authenticate()) {
            throw new CHttpException('403', CJSON::encode('Неправильно введены данные'));
        } else {

            Yii::app()->user->login($identity);
            $data = [
                "id" => $identity->getId(),
                "token" => $identity->getEncriptedIdentity()
            ];
//            var_dump($identity);
            $return = [
                'type' => 'raw',
                'data' => $data,
            ];

            echo json_encode($return);

        }
    }

    public function actionTest()
    {
        $headers = apache_request_headers();
        $token = $headers['token'];
        if ($token) {
            $identity = new RestUserIdentity(null, null);
            $identity->applyDecriptedIdentity($token);
            if ($identity->authenticate()) {
                Yii::app()->user->login($identity);
            } else {
                throw new CHttpException('500', 'Ошибка аутентификации через токен');
            }
        }
    }

    public function actionSocial()
    {
        $service = Yii::app()->request->getQuery('service');
        if (isset($service)) {
            $authIdentity = Yii::app()->eauth->getIdentity($service);
            $authIdentity->redirectUrl = Yii::app()->user->returnUrl;
            $authIdentity->cancelUrl = $this->createAbsoluteUrl('site/login');

            if ($authIdentity->authenticate()) {

                $identity = new ServiceUserIdentity($authIdentity);

                // Успешный вход
                if ($identity->authenticate()) {

                    Yii::app()->user->login($identity);

//                    var_dump($identity);
                    $data = [
                        "id" => $identity->getId(),
                        "token" => $identity->getEncriptedIdentity()
                    ];

//                    $return = [
//                        'type' => 'raw',
//                        'data' => $data,
//                    ];

//                    Yii::app()->request->redirect( 'http://dialog.k.mgbeta.ru');

//                    exit;

//                    var_dump($data);
//                    var_dump($_GET['redirect'] . json_encode($data));
//                    exit;
                    Yii::app()->request->redirect($_GET['redirect'] . json_encode($data));

//                    Yii::app()->request->redirect('http://'.$host['host'].'/auth/social?redirect='.$_GET['redirectUrl'].'&identity={'.$data.'} ' );

//                    Yii::app()->request->redirect( $_GET['redirectUrl'] );
                } else {

//                    $data = [
//                        "id" => $identity->getId(),
//                        "token" => $identity->getEncriptedIdentity()
//                    ];
////            var_dump($identity);

                    throw new CHttpException('500', 'Ошибка аутентификации через социальную сеть');
//                    throw new CHttpException('500', CJSON::encode('Ошибка аутентификации через социальную сеть'));

//                    Yii::app()->request->redirect('http://'.$host['host'].'/auth/social?redirect='.$_GET['redirectUrl'].'&identity={'.$data.'} ' );
//                    Yii::app()->request->redirect($_GET['redirectUrl'].json_encode($data));
                }
            }

            // Что-то пошло не так, перенаправляем на страницу входа
            Yii::app()->end();
        } else {
            echo "service empty";
        }

    }


    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }


    public function Userlinc()
    {
        $services = Userlink::model()->findAllByAttributes(array(
            'userId' => Yii::app()->user->id,
        ));
        $model = array();
        foreach ($services as $service) {
            array_push($model, $service->serviceName);
        }
        return $model;
    }

    public function SocialAll()
    {
        return array_keys(Yii::app()->eauth->getServices());
    }

    public function restEvents()
    {

        $this->onRest('req.post.reg.render', function () {
            $this->actionReg();
        });

        $this->onRest('req.post.login.render', function () {
            $this->actionLogin();
        });

        $this->onRest('req.get.allsocial.render', function () {
            return CJSON::encode([
                'type' => 'rest',
                'success' => (($count > 0) ? true : false),
                'message' => (($count > 0) ? "Record(s) Found" : "No Record(s) Found"),
                'totalCount' => $count,
                'data' => $this->SocialAll(),
            ]);
        });

    }

}
