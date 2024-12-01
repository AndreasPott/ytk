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

/**
 * This file contains a toolbox for writing Yii application. The design supports
 * Yii's MVC architecture and relates to models and controllers in terms of yii's
 * respective baseclasses.
 * Some function relate to user management and other support markup with bootstrap.
 * All operations are static functions of the Ytk class
 */
class Ytk extends CApplicationComponent
{
    /**
	 * @var boolean indicates whether assets should be republished on every request.
	 */
	public $forceCopyAssets = false;

    protected $_assetsUrl;
    
    /**
	 * Returns the URL to the published assets folder.
	 * @return string the URL
	 */
	protected function getAssetsUrl()
	{
		if (isset($this->_assetsUrl))
			return $this->_assetsUrl;
		else
		{
            $assetsPath = Yii::getPathOfAlias('ytk.assets');    // we do not receive a proper url here!
			$assetsUrl = Yii::app()->assetManager->publish($assetsPath, false, -1, $this->forceCopyAssets);
			return $this->_assetsUrl = $assetsUrl;
		}
    }

    /* return an array with key-value-pairs of all supported script
     */
    protected function getScripts()
    {
        return array(
            'mermaid' => 'registerMermaid',
            'chartjs' => 'registerChartjs',
            'jspdf' => 'registerJspdf',
            'simplemde' => 'registerSimplemde',
        );
    }

    /* register the script for the mermaid package */
    protected function registerMermaid($position = CClientScript::POS_HEAD)
    {
        /** @var CClientScript $cs */
        $cs = Yii::app()->getClientScript();

        $filename = YII_DEBUG ? 'mermaid.min.js' : 'mermaid.min.js';    
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);    // exchange '/' by a path if the files are not stored plainly in the asset dir but in subdirs
    }

    /* register the script for the chart.js package */
    protected function registerChartjs($position = CClientScript::POS_HEAD)
    {
        /** @var CClientScript $cs */
        $cs = Yii::app()->getClientScript();

        $filename = YII_DEBUG ? 'chart.min.js' : 'chart.min.js';        // exchange the first filename to chart.js to have a readable version in debug mode
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);    
        $filename = YII_DEBUG ? 'chartjs-plugin-colorschemes.js' : 'chartjs-plugin-colorschemes.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);    
        $filename = YII_DEBUG ? 'chartjs-plugin-annotation.min.js' : 'chartjs-plugin-annotation.min.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);    
        $filename = YII_DEBUG ? 'chartjs-chart-matrix.js' : 'chartjs-chart-matrix.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);     
        $filename = YII_DEBUG ? 'chartjs-chart-sankey.js' : 'chartjs-chart-sankey.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);     
        $filename = YII_DEBUG ? 'chartjs-chart-wordcloud.js' : 'chartjs-chart-wordcloud.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);     
    }

    /* register the script for the jspdf package */
    protected function registerJspdf($position = CClientScript::POS_HEAD)
    {
        /** @var CClientScript $cs */
        $cs = Yii::app()->getClientScript();

        $filename = YII_DEBUG ? 'jspdf.min.js' : 'jspdf.min.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);
    }

    /** register the script for the simpleMDE package;
     *  as if Nov/2024 simplemde was replaced by its work easymde which is essentially a 
     *  newer version of simplemde where the original software is discontinued 
     */
    protected function registerSimplemde($position = CClientScript::POS_HEAD)
    {
        /** @var CClientScript $cs */
        $cs = Yii::app()->getClientScript();

        $filename = YII_DEBUG ? 'easymde.min.js' : 'easymde.min.js';
        $cs->registerScriptFile($this->getAssetsUrl().'/'.$filename, $position);
		$filename = YII_DEBUG ? 'easymde.min.css' : 'easymde.min.css';
		$cs->registerCssFile($this->getAssetsUrl().'/'.$filename);        
    }

    /* Call the register method for the pacakge identified by the name $script
     */ 
    public function register($script)
    {
        $sc = $this->getScripts();
        if (array_key_exists($script, $sc)) 
            call_user_func(array($this, $sc[$script]));
    }

    /**
	 * Registers all JavaScript.
	 * @param int $position the position of the JavaScript code.
     * TODO: Now that we have started to manage more that on script code
     *       we want to load only the required scripts; therefore, we 
     *       need a mechanisms to request specific JS plugins to load them only when required.
	 */
	protected function registerAllJS($position = CClientScript::POS_HEAD)
	{
        $sc = $this->getScripts();
        foreach ($sc as $script) {
            $this->register($script);
        }
    }

    /* Setting autoloadAllJs to all will load all assets shiped with ytk. Otherwise, one has to 
     * call Yii::app()->ytk->registerX() with e.g. X=Mermaid to register the scripts one-by-one
     */
	public function init($autoloadAllJs=false) 
	{ 
        if ($autoloadAllJs===true)
            $this->registerAllJS(); 
		parent::init(); 
    }
    
    // --------------------------------------------------------------------- //
    // The following part of this file contains the static function being
    // being part of the same namespace ytk but there is no connection to the
    // component and asset management performed by the code above.
    // --------------------------------------------------------------------- //


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

    /* putputs a almost arbirary variable enclosed into a pre tag
     * @param item is the object (typically a parameter or array)
     * @param tag is the html tag to embed the output. Defaults to a code region in the page
     */
    public static function vardump($item, $tag='pre') 
    {
        echo CHtml::tag($tag, array(), print_r($item, true));
    }

    /**
     * Register a script to toggle on click the size of HTML elements with a given class
     * @param $cssClass the html class name to which the toggle function is registered
     * @param $context The jQuery selector of the top html which childs shall receive the toggle 
     *        property; can be "body". The element must have the maxHeight css attribute (e.g. div tags)
     * @param $minSize the (limited) size of the element
     * @param $maxSize the alternative size as second state, usually the inital value
     */
    public static function sizeToggler($cssClass, $context='body',  $minSize='250px', $maxSize='none') {
        Yii::app()->clientScript->registerScript('ajax-link-handler', "
        jQuery('$context').on('click', '.$cssClass', function() {
            this.style.maxHeight = this.style.maxHeight != '$maxSize' ? '$maxSize' : '$minSize';
        });");
    }
}

?>