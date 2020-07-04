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
 * custom class ane dprojects
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

    /* Load all rows for the models in the list given in models.
     */
    public static function analyzeModels($models, &$faulty_rows)
    {
        $result = "";   
        // walk through the model and execute the sanity test
        foreach ($models as $model) {
            if (!class_exists($model)) {
                $result.="Skipping $model; class does not exist\n";
                continue;                
            }

            // test if the class is an CActiveRecord
            $prototype = new $model;
            if (!$prototype instanceof CActiveRecord)
            {
                $result.="Skipping $model; not an CActiveRecord\n";
                continue;
            }
            $result.=CHtml::tag('h3',array(), $model);
            $items = $model::model()->findAll();
            foreach ($items as $item) {
                if (!$item->validate())
                {
                    $result.="Validate failed for item ID $item->id\n";
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
            'journaling' => array('created','changed'),
            'naming' => array('name','desc'),
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
        if (count($res)==0)
            return -1;
        if (count($res)==count($scheme))
            return 1;
        return 0;
    }

}