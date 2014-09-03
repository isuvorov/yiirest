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

    public function actionSocial()
//    public function actionLoginsocial()
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

                    Yii::app()->request->redirect($_GET['redirect'].json_encode($data));

//                    Yii::app()->request->redirect('http://'.$host['host'].'/auth/social?redirect='.$_GET['redirectUrl'].'&identity={'.$data.'} ' );

//                    Yii::app()->request->redirect( $_GET['redirectUrl'] );
                }

                else {

//                    $data = [
//                        "id" => $identity->getId(),
//                        "token" => $identity->getEncriptedIdentity()
//                    ];
////            var_dump($identity);

                    throw new CHttpException('500', CJSON::encode('Ошибка аутентификации через социальную сеть'));

//                    Yii::app()->request->redirect('http://'.$host['host'].'/auth/social?redirect='.$_GET['redirectUrl'].'&identity={'.$data.'} ' );
//                    Yii::app()->request->redirect($_GET['redirectUrl'].json_encode($data));
                }
            }

            // Что-то пошло не так, перенаправляем на страницу входа
            Yii::app()->end();
        }

    }


    private function login($username, $password)
    {

        if ($username == null ||  $password == null) {
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

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }


    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionIndex()
    {

        echo __DIR__;
//        $this->redirect('/site/adduser');
        $this->render('index');
    }


    public function actionAdd()
    {

//        $this->render('add');
        $this->render('add');
    }


    public function actionAdduser()
    {
        if ($_POST) {
            $this->setUser($_POST['user']);
            $this->redirect("/site/adduser");
        } else {
            $this->render('adduser2');
        }
    }

    public function actionAdddomain()
    {

        $user = $this->getUser();
        $params = array();
        $params['domain'] = $user['domain'];
        $params['login'] = $user['login'];
        $params['pass'] = $user['pass'];
        $params['alias'] = 'www.' . $params['domain'];
        $params['ispmgr'] = 'https://skillme.net/manager/ispmgr';
        $this->render('domain', $params);
    }

    public function setUser($user)
    {
        if (!$user['pass'])
            $user['pass'] = $this->randomPassword(12);
        if (!$user['domain'])
            $user['domain'] = $user['login'] . '.skillme.net';
        if (!$user['wp_login'])
            $user['wp_login'] = $user['login'];
        $_SESSION['user'] = $user;
    }


    public function getUser()
    {
        $user = $_SESSION['user'];
        return $user;
    }


    function getUserFields($values)
    {

        $fields = array(
            array(
                'name' => 'email',
                'title' => 'Почта',
                'placeholder' => 'me@coder24.ru',
            ),
            array(
                'name' => 'name',
                'title' => 'Имя',
                'placeholder' => 'Вася',
            ),
            array(
                'name' => 'login',
                'title' => 'Логин',
                'placeholder' => 'coder',
            ),
            array(
                'name' => 'pass',
                'title' => 'Пароль',
                'placeholder' => 'dtuVJTS44qzE',
                'comment' => 'Оставьте пустым, для генерации рандомного пароля',
            ),
            array(
                'name' => 'vk',
                'title' => 'Адрес Вконтакте',
            ),
        );
        //var_dump($values);
        //exit;
        foreach ($fields as &$field) {
            $key = $field['name'];
            $field['value'] = $values[$key];
        }
        return $fields;
    }

    function randomPassword($length)
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function actionPress ($data) {
        echo '1';
//        $data = [
//            'text' => 'type	ques_id	title	link	mood
//ques	0	Здравствуйте, чем могу помочь?
//ans	0	Здравствуйте, я представитель компании Фармстандарт.	1	1
//ans	0	Я представитель компании Фармстандарт.	2	0
//ans	0	Я ищу заведующего аптекой, можете мне помочь?	3	-1'
//        ];
//
//
////        $data = file_get_contents("php://input");
//        $params = json_decode($data, 1);
//        var_dump($params);
        /*
        $rows = explode("\n", $data);

        $questions = array();

        foreach ($rows as $row) {

            $row = trim($row);

            $cells = explode("\t", $row);
            if ($cells[0] == 'ques' || $cells[0] == 'end') {
                $question = new Question();
                $question->id = $cells[1];
                $question->title = $cells[2];
                $questions[$question->id] = $question;
            } elseif ($cells[0] == 'ans') {
                $answer = new Answer();
                $answer->question_id = $cells[1];
                @$answer->id = count($questions[$answer->question_id]->answers);
                $answer->title = $cells[2];
                $questions[$answer->question_id]->answers[] = $answer;
            }
        }
        */
    }



    public function Userlinc () {
        $services =  Userlink::model()->findAllByAttributes(array(
            'userId' => Yii::app()->user->id,
        ));
        $model = array();
        foreach ($services as $service) {
            array_push($model, $service->serviceName);
        }
        return $model;
    }

    public function SocialAll () {
        return array_keys(Yii::app()->eauth->getServices());
    }

    public function restEvents()
    {
        $this->onRest('post.filter.req.auth.ajax.user', function() {
            return true;
        });

        $this->onRest('req.post.reg.render', function() {
            $this->actionReg();
        });

        $this->onRest('req.post.login.render', function() {
            $this->actionLogin();
        });

        $this->onRest('req.get.allsocial.render', function() {
            return CJSON::encode([
                'type'              => 'rest',
                'success'           => (($count > 0)? true: false),
                'message'           => (($count > 0)? "Record(s) Found": "No Record(s) Found"),
                'totalCount'        => $count,
                'data'              => $this->SocialAll(),
            ]);
        });

    }

}

//
//class AuthController extends Controller
//{
//
//
//    public function actionReg()
//    {
//        $data = file_get_contents("php://input");
//        $params = json_decode($data, 1);
//        $user = new User();
//        $user->attributes = $params;
//        $user->password = $user->hashPassword($params['password']);
////        $user->password = $params['password'];
//        $user->save();
////        var_dump($user->save());
//        if ($user->errors) {
//            var_dump($user->errors);
//            exit;
//        }
////        var_dump($user->errors);
////        var_dump($user->id);
////        $this->redirect();
//
//        $this->login($params['username'], $params['password']);
//
//    }
//
//    public function actionLogin()
//    {
//
//        $data = file_get_contents("php://input");
//        $params = json_decode($data, 1);
////        $username = 'demo';
////        $password = 'demo';
////        $this->login($username, $password);
//        $this->login($params['username'], $params['password']);
//
//    }
//
//    private function login($username, $password)
//    {
//        $identity = new UserIdentity($username, $password);
//
//
//        if (!$identity->authenticate()) {
//            echo 'Incorrect username or password.';
//        } else {
//            $data = [
//                "id" => $identity->getId(),
//                "token" => $identity->getEncriptedIdentity()
//            ];
////            var_dump($identity);
//            $return = [
//                'type' => 'raw',
//                'data' => $data,
//            ];
//            echo json_encode($return);
//        }
//    }
//
//    /**
//     * Logs out the current user and redirect to homepage.
//     */
//    public function actionLogout()
//    {
//        Yii::app()->user->logout();
//        $this->redirect(Yii::app()->homeUrl);
//    }
//
//
//    /**
//     * Logs out the current user and redirect to homepage.
//     */
//    public function actionIndex()
//    {
//
//        echo __DIR__;
////        $this->redirect('/site/adduser');
//        $this->render('index');
//    }
//
//
//    public function actionAdd()
//    {
//
////        $this->render('add');
//        $this->render('add');
//    }
//
//
//    public function actionAdduser()
//    {
//        if ($_POST) {
//            $this->setUser($_POST['user']);
//            $this->redirect("/site/adduser");
//        } else {
//            $this->render('adduser2');
//        }
//    }
//
//    public function actionAdddomain()
//    {
//
//        $user = $this->getUser();
//        $params = array();
//        $params['domain'] = $user['domain'];
//        $params['login'] = $user['login'];
//        $params['pass'] = $user['pass'];
//        $params['alias'] = 'www.' . $params['domain'];
//        $params['ispmgr'] = 'https://skillme.net/manager/ispmgr';
//
//
//        $this->render('domain', $params);
//    }
//
//    public function setUser($user)
//    {
//        if (!$user['pass'])
//            $user['pass'] = $this->randomPassword(12);
//        if (!$user['domain'])
//            $user['domain'] = $user['login'] . '.skillme.net';
//        if (!$user['wp_login'])
//            $user['wp_login'] = $user['login'];
//        $_SESSION['user'] = $user;
//    }
//
//
//    public function getUser()
//    {
//        $user = $_SESSION['user'];
//
//        return $user;
//
//    }
//
//
//    function getUserFields($values)
//    {
//
//        $fields = array(
//            array(
//                'name' => 'email',
//                'title' => 'Почта',
//                'placeholder' => 'me@coder24.ru',
//            ),
//            array(
//                'name' => 'name',
//                'title' => 'Имя',
//                'placeholder' => 'Вася',
//            ),
//            array(
//                'name' => 'login',
//                'title' => 'Логин',
//                'placeholder' => 'coder',
//            ),
//            array(
//                'name' => 'pass',
//                'title' => 'Пароль',
//                'placeholder' => 'dtuVJTS44qzE',
//                'comment' => 'Оставьте пустым, для генерации рандомного пароля',
//            ),
//            array(
//                'name' => 'vk',
//                'title' => 'Адрес Вконтакте',
//            ),
//        );
//        //var_dump($values);
//        //exit;
//        foreach ($fields as &$field) {
//            $key = $field['name'];
//            $field['value'] = $values[$key];
//        }
//        return $fields;
//    }
//
//    function randomPassword($length)
//    {
//        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
//        $pass = array(); //remember to declare $pass as an array
//        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
//        for ($i = 0; $i < $length; $i++) {
//            $n = rand(0, $alphaLength);
//            $pass[] = $alphabet[$n];
//        }
//        return implode($pass); //turn the array into a string
//    }
//}
//
