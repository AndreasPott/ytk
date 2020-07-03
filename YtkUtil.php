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
            $str = str_replace(".php", "", $filename);
            $str = str_replace($path, "", $str);
            array_push($models, $str);
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
    
}