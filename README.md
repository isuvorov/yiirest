yiirest
=======

Yii Restful API WebApplication

 'components' => array(

        // Аутентификация через соцсети
        'loid' => array(
            'class' => '\\loid',
        ),
        'eauth' => array(
            'class' => '\\EAuth',
            'popup' => true, // Use the popup window instead of redirecting.
            'cache' => false, // Cache component name or false to disable cache. Defaults to 'cache'.
            'cacheExpire' => 0, // Cache lifetime. Defaults to 0 - means unlimited.
            'services' => array(
                'twitter' => array(
                    // register your app here: https://dev.twitter.com/apps/new
                    'class' => 'TwitterOAuthService',
                    'key' => '....',
                    'secret' => '..',
                ),
                'facebook' => array(
                    // register your app here: https://developers.facebook.com/apps/
                    'class' => 'FacebookOAuthService',
                    'client_id' => '...',
                    'client_secret' => '...',
                ),
                'vkontakte' => array(
                    // register your app here: https://vk.com/editapp?act=create&site=1
                    'class' => 'VKontakteOAuthService',
                    'client_id' => '...',
                    'client_secret' => '...',
                ),
            ),
        ),

        'urlManager' => array(
                    'urlFormat' => 'path',
                    'rules' => array_merge(
                        require(
                            dirname(dirname(__FILE__)) . '/vendor/starship/restfullyii/starship/RestfullYii/config/routes.php'
                        ),

                        [


                        ]
                    ),
                ),

 ),

 'aliases' => array(
         'RestfullYii' => realpath(dirname(dirname(__FILE__)) . '/vendor/starship/restfullyii/starship/RestfullYii'),
     ),