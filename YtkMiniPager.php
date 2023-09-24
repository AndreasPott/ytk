<?php 
/* Ytk - Yii Toolkit
*
* Copyright (c) 2013-2023 Andreas Pott
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
 * YtkMiniPager is a button group that allows to navigate forth and back implementing a 
 * next/previous logic. If a proper filter is configured, only a subset of the items
 * in the table model are reached. Filters must be valid SQL WHERE expressions as string.
 * By setting link to true, one can list all items as dropdown jump list in the middle button.
 */
class YtkMiniPager extends CWidget { 
    // the name as string of the model class derived from CActiveRecord on which we navigate
    public $model;

    // a model instance derived from CActiveRecord. The model must implement a primary key 
    // named id or return a respective items using tableSchema->primaryKey; 
    // pass the current model in a typical item view scenario
    public $item;

    // the url to call when the forward/backward button is clicked; the url must accept 
    // a GET parameter named 'id' to pass the primary key of the element. According to yii
    // standard behavior, the parameter is named id even if the primary keys name differs
    // (e.g. "modelname_id")
    public $url;

    // the size of the minipages as passed to TbButtonGroup control. Defaults to size 'normal'
    // Other options are 'large', 'small' or 'mini'
    public $buttonSize;

    // optional parameter: additional condition to filter for when determining next, 
    // prev, total index, and total item count. Defaults to value TRUE as this 
    // condition works both as placeholder to select all models by default and
    // can be postfixed with any AND-condition
    public $filter;

    // optional parameter: mixed | boolean or array: show direkt link options as dropdown 
    // on the middle button
    public $links;

    // name of the primary key attribute in $model
    public $pk;

    // optional parameter to be forwarded to the underlying button group widget
    // to customize style and setting classes
    public $htmlOptions;

    // assign default values for unset attributes
    public function init() {
        parent::init(); 
        // assign default values for those properties which are not defined in widget init
        if ($this->model === null)
            $this->model = '';
        // assign default values for those properties which are not defined in widget init
        if ($this->buttonSize === null)
            $this->buttonSize = 'normal';
        if ($this->filter === null)
            $this->filter = 'TRUE';     
        if ($this->links === null)
            $this->links = false;
        // if no name for primary key (used for navigation forth and back) is given, load the 
        // primary key from the model
        if ($this->pk === null)
            $this->pk = $this->item->tableSchema->primaryKey;
        if ($this->htmlOptions === null)
            $this->htmlOptions = array();
    } 

    // render the widget by using echo to print the desired content
    public function run() {
        echo '<div class="pull-right">';
        $model = $this->model;
        $pk = $this->pk;

        // find the next model after the current one
        $res = $model::model()->findAll(array(
            'condition'=>$this->filter." AND $pk > ".$this->item->$pk,
            'order'=>"$pk ASC",
            'limit'=>1));
        $next_id = count($res)>0 ? $res[0]->$pk : 0;

        // find previous model before the current one
        $res = $model::model()->findAll(array(
            'condition'=>$this->filter." AND $pk < ".$this->item->$pk,
            'order'=>"$pk DESC",
            'limit'=>1));
        $prev_id = count($res)>0 ? $res[0]->$pk : 0;

        // get index number and total count of items
        $cnt = $model::model()->count(array(
            'condition'=>$this->filter));
        $idx = $model::model()->count(array(
            'condition'=>$this->filter." AND $this->pk <= ".$this->item->$pk));

        // generate the optional links of the dropdown menu
        $dirLinks = array();
        if ($this->links === true) {
            $res = CHtml::listData($model::model()->findAll(array('condition'=>$this->filter)), "$pk", 'name');
            foreach ($res as $id=>$name)
                $dirLinks[] = array('label'=>$name, 'url'=>array($this->url, "$pk"=>$id));
        }

        // generate all three buttons as a button group
        $this->widget('bootstrap.widgets.TbButtonGroup', array(
            'size'=>$this->buttonSize,
            'type'=>'null',
            'htmlOptions'=>$this->htmlOptions,
            'buttons'=>array(
                array('icon'=>'chevron-left', 'label'=>' ', 'url'=>$prev_id>0 ? array($this->url, "id"=>$prev_id) : '#', 'active'=>$prev_id == 0,),
                array('label'=>$idx.' / '.$cnt, 'size'=>'small', 'url'=>'#', 'active'=>true, 'items'=>$dirLinks,),
                array('icon'=>'chevron-right', 'label'=>' ', 'url'=>$next_id>0 ? array($this->url, "id"=>$next_id) : '#', 'active'=>$next_id == 0,),
            ),
        ));
        echo '</div>';
    } 
} 
?>
