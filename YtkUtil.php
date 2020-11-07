<?php 
/* Ytk - Yii Toolkit
*
* Copyright (c) 2013-2020 Andreas Pott
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

/**
 * YtkUtil is a collection of function to support development and consistency of
 * custom class and projects
 */
class YtkUtil {

    /* this function uses the typical yii data layout to receive a list with names of data models
     */
    public static function getAllModel($path='./protected/models/')
    {
        $files = glob($path.'*.php');
        $models = array();
        foreach ($files as $filename)
        {
            // remote path and fileextension
            array_push($models, basename($filename, '.php'));
        }
        return $models;
    }

    /* this function uses the typical yii data layout to receive a list with names of data models
     */
    public static function getAllController($path='./protected/controllers/')
    {
        $files = glob($path.'*.php');
        $controller = array();
        foreach ($files as $filename)
        {
            // remote path and fileextension
            // array_push($controller, str_replace('Controller', '', basename($filename, '.php')));
            array_push($controller, basename($filename, '.php'));
        }
        return $controller;
    }

    /* this function uses the standard yii directory structure to receive a list of all directories which
     * contain views. No information is extract on the respective views that are contained.
     */
    public static function getAllViews($path='./protected/views')
    {
        $files = glob($path."/*", GLOB_ONLYDIR);
        foreach ($files as &$file) {
            $file = substr($file, strlen($path)+1);
        }
        return $files;
    }

    /* this function extends the getAllViews by generating a key-array list of all view directory which
     * all its specific views
     */
    public static function getAllViewFiles($path='./protected/views')
    {
        // get all directories with view viles
        $files = YtkUtil::getAllViews($path);
        $res = array();
        foreach ($files as $file) {
            // get all files in these directories
            $viewfiles = glob($path."/$file/*.php");
            $views = array();
            // clean the filename from path and file extensions
            foreach ($viewfiles as $vf) {
                array_push($views, basename($vf, '.php'));
            }
            // store search results in nested array 
            $res[$file] = $views;
        }
        return $res;
    }

    /**
     * Extracta all actions implemented by the controller class, i.e. the name of all functions which
     * name starts with the prefix 'action'.
     */
    public static function getAllActions($controller)
    {
        $actions = array();
        // check if the class exists before quering its methods
        if (!class_exists($controller))
            return $actions;

        // get all methods names for this class
        $methods = get_class_methods($controller);
        foreach ($methods as $method) {
            // skip the function "actions" which has a different meaning
            if (strpos($method,'actions') === 0)
                continue;
            if (strpos($method,'action') === 0)
                array_push($actions,str_replace("action", "", $method));
        }
        return $actions;
    }

    public static function getAllActionsFromRbac($controller)
    {
        $actions = array();
        if (!class_exists($controller))
            return $actions;
        // if (!$prototype instanceof Controller)
        //     return $actions;
        // get the access rules as they list the actionso the controller
        $thisCtrl = new $controller(999);
        $rules = $thisCtrl->accessRules();
        foreach ($rules as $rule) {
            if ($rule[0]==='allow')
                $actions = array_merge($actions, $rule['actions']);
        }
        // sort the results
        sort($actions);
        // remove duplicates
        $actions = array_unique($actions);
        // remote the empty element
        if (count($actions)>0 && $actions[0]==='')
            array_shift($actions);

        return $actions;
    }

    /* Load all rows for the models in the list given in models.
     */
    public static function analyzeModels($models, &$faulty_rows)
    {
        $result = "";   
        // walk through the model and execute the sanity test
        foreach ($models as $model) {
            if (!class_exists($model)) {
                $result.= "Skipping $model; class does not exist\n";
                continue;                
            }

            // test if the class is an CActiveRecord
            $prototype = new $model;
            if (!$prototype instanceof CActiveRecord)
            {
                $result.= "Skipping $model; not an CActiveRecord\n";
                continue;
            }

            // execute actual validation
            $result.=CHtml::tag('h3', array(), $model);
            $items = $model::model()->findAll();
            foreach ($items as $item) {
                if (!$item->validate())
                {
                    $result.= "Validate failed for item ID $item->id\n";
                    $faulty_rows++;
                }
            }
        }
        return $result;
    }  
    
    /* the proposal for schemes definitions
     * the *identity* scheme simply required that a model implements the attribute id as primary key, thus allowing to identify items in the tables in a unique way
     * the *journaling* scheme implies that the record keeps track of its creation and last update timestamp (in unit time format).
     * the *naming* requires records to implement both a name and a description
     * the *ownership* scheme requires the item to be assigned to user. This is a foreign key constraint and implicitly requires the active record to support users
     * 
     * More schemes may be use in the future: e.g. validity, user_comment, historization
     */
    public static function getSchemes() 
    {
        return array(
            'identity' => array('id'),
            'journaling' => array('created', 'changed'),
            'naming' => array('name', 'desc'),
            'ownership' => array('user_id'),
        );
    }

    /* Analyze if the ActiveRecord $item conforms the data fields defined by $scheme.
     * return  1 if the class implements the full scheme
     * return  0 if the class partially implements the scheme
     * return -1 if the class does not implement the scheme at all
     * return -1 if $item is not derived from CActiveRecord
     */
    public static function isScheme($scheme, $item) 
    {
        // if $item is a string, we check if $item is a class name and create an instance of this class
        if (is_string($item)) {
            if (class_exists($item))
                $item = new $item;
            else 
                return -1;
        }

        if (!$item instanceof CActiveRecord)
            return -1;
        if (is_string($scheme)) {
            // lookup the scheme in our database
            $schemes = self::getSchemes();
            if (!array_key_exists($scheme, $schemes))
                return -1;
            $scheme = $schemes[$scheme];
        } 
        else if (!is_array($scheme))
            return -1;
        // compare the attributs of the scheme with those of the active record-derived class
        $res = array_intersect($scheme, array_keys($item->attributes));
        if (count($res) == 0)
            return -1;
        if (count($res) == count($scheme))
            return 1;
        return 0;
    }


    /**
     * walk through all controller classes, get all action names, and query each
     * actions signature. Then draft the code for use in python unit testing
     * listing a controllers actions against its parameters
     * $unittest [out]: string with prepared code for be used in the python unittesting
     *                  framework
     * $totalActionCnt [out]: int counting the total number of actions found in all controllers
     * @return html-formatted report on the controllers
     */
    static public function analyzeController($controllers, &$unittest, &$totalActionCnt) 
    {
        $detail = "";
        // analyze support of schemes in all model classes
        foreach ($controllers as $controller) {
            // we need to skip the sitecontroller (or perhaps more elegantly the current
            // controller) to prevent two instances of the same class
            if ($controller === "SiteController")
                continue;
            $unittest.= "def test_$controller"."_views(self):\n";
            $unittest.= "\tsite = [\n";
            include('protected/controllers/'.$controller.".php");
            $actions = YtkUtil::getAllActions($controller);
            // generat output string
            $detail.= "<b>$controller</b> with ";
            $detail.= "#".count($actions)." action(s): ";
            foreach ($actions as $action) {
                $totalActionCnt++;
                // for url generation, we need the controller name without postfix "Controller"
                $shortname = str_replace("Controller", "", $controller);
                $unittest.= "\t\t{'r', '$shortname/$action'";
                    $detail.= $action."(";
                $r = new ReflectionMethod($controller, "action".$action);
                $params = $r->getParameters();
                // walk through the parameters of the function and show them in the report
                foreach ($params as $param) {
                    // if we have a single id parameter, we generate appropriate code 
                    // for unit testing
                    if ($param->getName() === 'id' && count($param) === 1)
                        $unittest.= ", 'id':self.valid_$shortname";
                    //$param is an instance of ReflectionParameter
                    $detail.= $param->getName();
                    $detail.= $param->isOptional();
                    $detail.= ", ";
                }            
                $unittest.= "},\n";
                $detail.= "), ";
            }
            // complete the report and unittest string for this controller
            $unittest.= "\t]\n";
            $unittest.= "\tself.coreSiteTest(self.admin_credential, site, 200)\n\n";
            $detail.= "<br>\n";
        }
        return $detail;
    }

}