<?php

//include_once("../../../lib/config.php");
require_once(dirname(__FILE__)."/../../lib/config.php");
//yii::setPathOfAlias('bootstrap', dirname(__FILE__).'/../extensions/bootstrap');
Yii::setPathOfAlias('editable', dirname(__FILE__).'/../extensions/x-editable');

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'iConnex Web Interface',
    'defaultController' => 'golap/golap',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model andt component classes
    'import' => array(

        'application.models.*',

        'application.components.*',

        'application.modules.srbac.controllers.SBaseController',

        'application.extensions.api.*',

        'application.extensions.redis.*',

        'application.extensions.bootstrap.*',
		'editable.*',
    ),

   //  'theme' => 'bootstrap',

    'behaviors' => array(

        'onBeginRequest' => array(

            'class' => 'application.components.RequireLogin'

        )

    ),

    'modules' => array(

        // uncomment the following to enable the Gii tool

        'gii' => array(

            'class' => 'system.gii.GiiModule',

            'password' => '12yii',

            // If removed, Gii defaults to localhost only. Edit carefully to taste.

            'ipFilters' => array('127.0.0.1', '10.*', '::1','192.168.1.61'),

           // 'generatorPaths' => array('bootstrap.gii'),

        ),

        'pwi' => array(

        ),

        'golap' => array(

        ),

		'preferences' => array(

        ),

		'controlpanel' => array(

        ),

        'srbac' => array(

            'userclass' => 'CentUser', //default: User

            'userid' => 'userid', //default: userid

            'username' => 'usernm', //default:username

            'delimeter' => '@', //default:-

            'debug' => true, //default :false

            'pageSize' => 10, // default : 15

            'superUser' => 'Authority', //default: Authorizer

            'css' => 'srbac.css', //default: srbac.css

            'layout' =>

            'application.views.layouts.main', //default: application.views.layouts.main,

            //must be an existing alias

            'notAuthorizedView' => 'srbac.views.authitem.unauthorized', // default:

            //srbac.views.authitem.unauthorized, must be an existing alias

            'alwaysAllowed' => array(

                //default: array()

                'SiteLogin', 'SiteLogout', 'SiteIndex', 'SiteAdmin',

                'SiteError', 'SiteContact'),

            'userActions' => array('Show', 'View', 'List'), //default: array()

            'listBoxNumberOfLines' => 15, //default : 10

            'imagesPath' => 'srbac.images', // default: srbac.images

            'imagesPack' => 'noia', //default: noia

            'iconText' => true, // default : false

            'header' => 'srbac.views.authitem.header', //default : srbac.views.authitem.header,

            //must be an existing alias

            'footer' => 'srbac.views.authitem.footer', //default: srbac.views.authitem.footer,

            //must be an existing alias

            'showHeader' => true, // default: false

            'showFooter' => true, // default: false

            'alwaysAllowedPath' => 'srbac.components', // default: srbac.components

        // must be an existing alias

        ),

    ),

    // application components

    'components' => array(

		//Script Map

		/*'clientScript'=>array(

			'scriptMap'=>array(

			  'jquery.js'=>'js/jquery.js',

			),

		 ),*/

		

        //redis server compenent

      /*  'cache' => array(

            'class' => 'ext.redis.CRedisCache',

            'servers' => array(

                array('host' => 'localhost',

                    'port' => 6378,

                )

            ),

        ), */

		'editable' => array(

            'class'     => 'editable.EditableConfig',

            'form'      => 'bootstrap',        //form style: 'bootstrap', 'jqueryui', 'plain' 

            'mode'      => 'inline',            //mode: 'popup' or 'inline'  

            'defaults'  => array(              //default settings for all editable elements

               'emptytext' => 'Click to edit'

            )

        ), 

        //bootstrap component

     	 'bootstrap' => array(

			'class' => 'ext.bootstrap.components.Bootstrap',

			'responsiveCss' => true,

		  ),

      

        'user' => array(

            // enable cookie-based authentication

            'class' => 'WebUser',

            'allowAutoLogin' => true,

        ),

        // uncomment the following to enable URLs in path-format

        /*

          'urlManager'=>array(

          'urlFormat'=>'path',

          'rules'=>array(

          '<controller:\w+>/<id:\d+>'=>'<controller>/view',

          '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',

          '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',

          ),

          ),

         */

	      'db'=>array(
		      'connectionString' => 'mysql:host=localhost;dbname=nttests_ods;',
		      'emulatePrepare' => true,
		      'username' => 'root',
		      'password' => '',
		      'charset' => 'utf8',
	      ),

        /*'db' => array(

            'class' => 'CDbConnection',

            'connectionString' => ICX_ODSAUTH_DB_CONN_STRING,

            'emulatePrepare' => true,

            'username' => ICX_ODSAUTH_DB_USER,

            'password' => ICX_ODSAUTH_DB_PASSWORD,

            'charset' => 'utf8',

        ),*/

        'errorHandler' => array(

            // use 'site/error' action to display errors

            'errorAction' => 'site/error',

        ),

        'log' => array(

            'class' => 'CLogRouter',

            'routes' => array(

                array(

                    'class' => 'CFileLogRoute',

                    'levels' => 'error, warning',

                ),

            // uncomment the following to show log messages on web pages

//				array(

//					'class'=>'CWebLogRoute',

//				),

            ),

        ),

        'authManager' => array(

            // Path to SDbAuthManager in srbac module if you want to use case insensitive

            //access checking (or CDbAuthManager for case sensitive access checking)

            'class' => 'application.modules.srbac.components.SDbAuthManager',

            // The database component used

            'connectionID' => 'db',

            // The itemTable name (default:authitem)

            'itemTable' => 'items',

            // The assignmentTable name (default:authassignment)

            'assignmentTable' => 'assignments',

            // The itemChildTable name (default:authitemchild)

            'itemChildTable' => 'itemchildren',

        ),

    ),

    // application-level parameters that can be accessed

    // using Yii::app()->params['paramName']

    'params' => array(

        // this is used in contact page

        'adminEmail' => 'webmaster@example.com',

    ),

);

