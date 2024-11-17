<?php
/* Ytk - Yii Toolkit
*
* Copyright (c) 2013-2024 Andreas Pott
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

// Code baseline to create a RESTful API.
// part of this controller code are inspired by this tutorial
// https://www.yiiframework.com/wiki/175/how-to-create-a-rest-api

// To use the generic RESTful API in a Yii project, simple create a file
//  controllers/ApiController.php
// that derives from this class as follows
//  class ApiController extends YtkRestApiController {}
// the class may be empty but its name is required to be recognized by Yiis
// convention-based routing system as a valid URL.

// Further work to make a better Ytk component:
// - configure the class members (application name), data format, ...
// - make proper rules to limit access control
// - ease integration into existing applications
// - employ UserIdentity to encapsulate user authentification

class YtkRestApiController extends Controller
{
    // Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */
    protected $APPLICATION_ID = 'YtkDefaultApplication';

    /**
     * Default response format
     * either 'json' or 'xml'
     * 
     * TODO: This is a concept right know, not yet userd
     */
    // private $format = 'json';

    /**
     * Blacklist of models that may not be accessed through RESTful API
     */
    private $blacklist = array('User');

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array();
    }

    /* a minimalistic check for an authentificated user.
     * the two tokens PHP_AUTH_USER and PHP_AUTH_PW are generated by using the Firefox addon RESTClient.
     * It must be validated if this is a kind of standard naming or propriatary; the request can be 
     * posted also using curl, therefore, it seems to be at least kind of standarized method
     */
    private function _checkAuth()
    {
        // debug output: subject to be removed 
//         Yii::log(
// //            '_SERVER '.print_r($_SERVER,true)."\n".
//             '_REQUEST '.print_r($_REQUEST,true)."\n".
//             '_GET '.print_r($_GET,true)."\n".
//             '_POST '.print_r($_POST,true)."\n".
// //            '_ENV '.print_r($_ENV,true)."\n".
//         "");

        // Check if we have the USERNAME and PASSWORD HTTP header is set?
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        } 
        // check if username and password are sent in POST variable
        else if (isset($_POST['PHP_AUTH_USER']) && isset($_POST['PHP_AUTH_PW'])) {
            $username = $_POST['PHP_AUTH_USER'];
            $password = $_POST['PHP_AUTH_PW'];
        }
        else
            // Error: Unauthorized
            $this->_sendResponse(401,'Error. No credentials received');
        
        // User the UserIdentity class to perform authentification such that this implementation
        // does not have to care for user identity.
        $identity = new UserIdentity($username,$password);
        if (!$identity->authenticate())
            $this->_sendResponse(401, 'Error: Authentification failed. User Name or password is invalid');
    }
    

    private function _getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }    

    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);
    
        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';
    
            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }
    
            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
    
            // this should be templated in a real-world solution
            $body = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
    </head>
    <body>
        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
        <p>' . $message . '</p>
        <hr />
        <address>' . $signature . '</address>
    </body>
    </html>';
    
            echo $body;
        }
        Yii::app()->end();
    }    

    /**
     * The function queries the _GET superglobal to contain a model name.
     * Check if $model is a proper model to be used with the RESTful API
     * Firstly, the model is check to be existing at all. Then we check
     * if the model is blacklisted to be inaccessable through RESTful API.
     * The function does not return a value but terminas the application
     * if its validation fails or if no model parameter was provided.
     * 
     * @return the name of a valid model
     */
    public function getModel()
    {
        // test if model was provided in the request
        if (!isset($_GET['model'])) {
            $this->_sendResponse(404, sprintf('Error: No model name given') );
            Yii::app()->end();      
        }

        $model = $_GET['model'];

        // get all valid model names
        $validmodels = YtkUtil::getAllModel();

        // exclude the models in the backlist from API
        // TOOD: We need to tackle the problem of case sensitive comparions
        $validmodels = array_diff($validmodels, $this->blacklist);

        if (!in_array($model, $validmodels)) {
            // Model not implemented error
            $this->_sendResponse(501, sprintf(
                'Error: Unknown <b>model</b>. Model <b>%s</b> is not in the model list %s',
                $model, implode(',',$validmodels)) );
            Yii::app()->end();            
        }

        return $model;
    }

    // Actions
    // we can trigger this action from windows commmand line using curl as follows:
    //   curl -X GET -i http://localhost/index.php/api/chars
    // with the default urlManager the request must be send through this command
    //   curl -X GET -i "http://localhost/index.php?r=api/list&model=chars"
    public function actionList() 
    {
        $this->_checkAuth();

        // try to get a valid model from the request or terminate the application with an error message
        $model = $this->getModel();

        // as $model is valid model name, we can use a generic algorithms to handle all kind of models
        // query all instances of type $model
        $models = $model::model()->findAll();

        // Did we get some results?
        if (empty($models)) {
            // No
            $this->_sendResponse(200, sprintf('No items where found for model <b>%s</b>', $model) );
        } else {
            // Prepare response
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            // Send the response
            $this->_sendResponse(200, CJSON::encode($rows));
        }
    }


    /** get a single item from for a model given that it is identifable by id 
     */
    public function actionView()
    {
        $this->_checkAuth();

        // try to get a valid model from the request or terminate the application with an error message
        $model = $this->getModel();

        // Check if id was submitted via GET
        if (!isset($_GET['id']))
            $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );

        // try to get the element with the respective ID
        $thismodel = $model::model()->findByPk($_GET['id']);

        if (is_null($thismodel))
            $this->_sendResponse(404, 'No Item found with id '.$_GET['id']);
        else
            $this->_sendResponse(200, CJSON::encode($thismodel));
    }

    public function actionCreate()
    {
    }
    public function actionUpdate()
    {
    }
    public function actionDelete()
    {
    }
    public function actionValidate()
    {
        $this->_checkAuth();

        $model = $this->getModel();

        // Check if id was submitted via GET
        if (!isset($_GET['id']))
        $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );
    
        // try to get the element with the respective ID
        $thismodel = $model::model()->findByPk($_GET['id']);
        
        // Did we get some results?
        if (empty($thismodel)) {
            // No
            $this->_sendResponse(200, 
                    sprintf('No items where found for model <b>%s</b>', $model) );
        } else {
            if ($thismodel->validate())
                $this->_sendResponse(200, CJSON::encode(array(true)));
            else
                $this->_sendResponse(200, CJSON::encode(array(false)));
        }    
    }

}