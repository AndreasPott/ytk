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
 * This file contains a toolbox for writing Yii application. The design supports
 * Yii's MVC architecture and relates to models and controllers in terms of yii's
 * respective baseclasses.
 * Some function relate to user management and other support markup with bootstrap.
 * All operations are static functions of the Ytk class
 */
class Ytk
{
    // TODO: The getter functions for user are under development and not yet release for productive use
    //
    // /** A new set of session based getter functions to easily get the interesting objects, i.e.
    //  * - the current user-id and the respective user object (this is unique and must be defined if a user is login)
    //  */
    //
    // public static function getUserId()
    // {
    //     $record = User::model()->findByAttributes(array('username'=>Yii::app()->user->name));
	// 	if ($record === null)
    //         return 0;
    //     else
    //         return $record->id;
    // }
    //
    // /* get the name of the current use directly from the user table
    //  */
    // public static function getUser()
    // {
    //     $record = User::model()->findByAttributes(array('username'=>Yii::app()->user->name));
	// 	if ($record === null)
    //         return 0;
    //     else
    //         return $record;
    // }

    
    /**
     * Receive the value connected to $name of the global persistant storage. 
     * @param type $name name of the variable
     * @return the value of the global variable, null if the variable was not
     * found
     */
    public static function getGlobalState($name)
    {
        $sp = Yii::app()->getStatePersister();
        $state = $sp->load();
        if (!isset($state[$name]))
            return NULL;
        else
            return $state[$name];
    }

        
    /**
     * Store a $value under $name in the global persistant storage. 
     * @param type $name
     * @param type $value
     * @return boolean true, if successful, otherwise false.
     */
    public static function setGlobalState($name, $value)
    {
        if ($name == NULL)
            return false;
        $sp = Yii::app()->getStatePersister();
        $state = $sp->load();
        $state[$name] = $value;
        $sp->save($state);
        return true;    
    }


    /**
     * Helper function to simplify label generation with bootstrap.
     * @param string $value The label to be decorated with bootstrap as label
     * @return the encoded string
     */
    public static function EncodeSuccess($value)
    { return CHtml::tag('span', array('class'=>'label label-success'), $value); }
    
    public static function EncodeDanger($value)
    { return CHtml::tag('span', array('class'=>'label label-danger'), $value); }

    public static function EncodeWarning($value)
    { return CHtml::tag('span', array('class'=>'label label-warning'), $value); }

    public static function EncodeInfo($value)
    { return CHtml::tag('span', array('class'=>'label label-info'), $value); }

    public static function EncodeImportant($value)
    { return CHtml::tag('span', array('class'=>'label label-important'), $value); }

      
    /**
    * Extended the helper functions with functions that provide standarized modifications to Yii 
    * and Bootstraps widget behaviour.
    * we may include these functions in a separate class prefix Ytk:: or similar.
    */

    // change the standard markup of the TbListView and TbGridView to show pager on top (instead of foot)
    public static function paginationTemplate()
    {   
        return '<table><tr><td align="left">{pager}</td><td>{summary}</td></tr></table>{items}'; 
    }


    // change the standard markup of the TbListView  and TbGridView to show the pager control on top as affix, 
    // i.e. not scrolling with the view
    public static function paginationAffixTemplate($offset = 133)
    {   
        return '<div style=""><div id="tableheader" data-spy="affix" data-offset-top="'.$offset.'">{pager}</div></div> <div></div>{items}'; 
    }

    // the following functions are kept in namespace Ytk for convinience; however, if the number of helper
    // functions is increasing, the shall be moved to a distinct namespace as they are neither Ytk related
    // functions not markup helpers

    /* Convert a flat array of numbers (typically ids to a string that can be used in a SQL IN clause
     * @param $data the array to convert
     * @param $default, the value to insert into the string if array is empty 
     */
    public static function array_sql($data, $default=-1)
    {
        if (count($data) == 0) {
            if ($default != null)
                $data = array($default);	// set the array to impossible values 
            else
                return '( )';   // return an empty list
        }
		return '( '.implode(', ', $data).' )';        
    }

    
    /* convert a list of models (usuall received from Modell::findAll or similar functions
     * to a string for use in typeahead arr
     */
    public static function itemsToTypeahead($items)
    {
        $str = "'[";
        $first = true;
        foreach ($items as $i=>$item) {
            if (!$first)
                $str .= ", ";
            $str .= '"'.$item->name.'"';
            $first = false;
        }
        $str .= "]'";
        return $str;
    }
}

?>