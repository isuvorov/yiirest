<?php

class RestController extends Controller
{


    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations

            array(
                'RestfullYii.filters.ERestFilter +
                REST.GET, REST.PUT, REST.POST, REST.DELETE, REST.OPTIONS'
            ),
        );
    }

    public function actions()
    {
        return array(
            'REST.' => 'RestfullYii.actions.ERestActionProvider',
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', 'actions' => array('REST.GET', 'REST.PUT', 'REST.POST', 'REST.DELETE', 'REST.OPTIONS'),
                'users' => array('*'),
            ),
            array('allow', // deny all users
                'users' => array('*'),
            ),

        );
    }

    public function restEvents()
    {
//        $this->onRest('req.get.resources.render', function($data) {
//            var_dump($data);
//            exit;
//            return [$data]; //Array[Array]
//        });

//        $this->onRest('pre.filter.req.get.render', function($data) {
//            var_dump(data);
//            exit;
//            return [$data]; //Array[Array]
//        });

        $this->onRest('config.dev.flag', function () {
            return true;
        });


        $this->onRest('req.auth.ajax.user', function () {
            return true;
        });


        $this->onRest('model.hidden.properties', function () {
            return ['password', 'author.password'];
        });

//        $this->onRest('req.cors.access.control.allow.origin', function() {
//            return ['*']; //List of sites allowed to make CORS requests
//        });


//        $this->onRest('model.user.override.attributes', function () {
//            return ['password'];
//        });

//        $this->onRest('model.user.hidden.properties', function () {
//            return ['password'];
//        });

        $this->onRest('req.is.subresource', function ($model, $subresource_name, $http_verb) {

//            var_dump(CActiveRecord::HAS_MANY);
//            exit;
//            model()->relations(array ());
            if (!array_key_exists($subresource_name, $model->relations())) {
                return false;
            }
            if ($model->relations()[$subresource_name][0] != CActiveRecord::HAS_MANY) {
                return false;
            }
            return true;
        });


        $this->onRest('req.get.resources.render', function ($data, $model_name, $relations, $count) {
            //Handler for GET (list resources) request
            $this->setHttpStatus((($count > 0) ? 200 : 204));
            return CJSON::encode([
                'type' => 'rest',
                'success' => (($count > 0) ? true : false),
                'message' => (($count > 0) ? "Record(s) Found" : "No Record(s) Found"),
                'totalCount' => $count,
                'data' => $data,
            ]);
        });


        $this->onRest('req.put.resource.render', function($model, $relations, $visibleProperties=[], $hiddenProperties=[]) {
            return CJSON::encode([
                'type'              => 'rest',
                'success'           => 'true',
                'message'           => "Record Updated",
                'totalCount'    => "1",
                'modelName'     => get_class($model),
                'relations'     => $relations,
                'visibleProperties' => $visibleProperties,
                'hiddenProperties'  => $hiddenProperties,
                'data'              => $model,
            ]);
        });



    }

}
