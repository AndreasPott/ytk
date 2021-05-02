<?php 
/* Ytk - Yii Toolkit
*
* Copyright (c) 2013-2021 Andreas Pott
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
 * in the table are reached. Filtes must be valid SQL WHERE expressions.
 * By setting link
 */
class YtkMiniPager extends CWidget { 
    // the name as string of the model class derived from CActiveRecord on which we navigate
    public $model;

    // a model instance derived from CActiveRecord. The model must implment an attribute 
    // named id; pass the current model in a typical item view scenario
    public $item;

    // the url to call when the button is clicked; the url must accept a GET parameter 
    // named id to pass the id of the element
    public $url;

    // the size of the minipages as passed to TbButtonGroup control. Defaults to size 'normal'
    public $buttonSize;

    // optional parameter: additional condition to filter for when determining next, 
    // prev, total index, and total item count
    public $filter;

    // optional parameter: mixed | boolean or array: show direkt link options as dropdown 
    // on the middle button
    public $links;


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
            $this->filter = 'TRUE';     // if we have no specific filter condition we use TRUE as 
                                        // this condition works both as placeholder in empty 
                                        /// condition and as optional condition with AND 
        if ($this->links === null)
            $this->links = false;
        } 

    // render the widget by using echo to print the desired content
    public function run() {
        echo '<div class="pull-right">';
        $model = $this->model;

        // find the next model after the current one
        $res = $model::model()->findAll(array(
            'condition'=>$this->filter.' AND id > '.$this->item->id));
        $next_id = count($res)>0 ? $res[0]->id : 0;

        // find previous model before the current one
        $res = $model::model()->findAll(array(
            'condition'=>$this->filter.' AND id < '.$this->item->id,
            'order'=>'id DESC'));
        $prev_id = count($res)>0 ? $res[0]->id : 0;

        // get index number and total count of fow handouts
        $cnt = $model::model()->count(array(
            'condition'=>$this->filter));
        $idx = $model::model()->count(array(
            'condition'=>$this->filter.' AND id <= '.$this->item->id));

        // generate the optional links of the dropdown menu
        $dirLinks = array();
        if ($this->links === true) {
            $res = CHtml::listData($model::model()->findAll(array('condition'=>$this->filter)), 'id', 'name');
            foreach ($res as $id=>$name)
                $dirLinks[] = array('label'=>$name, 'url'=>array($this->url, 'id'=>$id));
        }

        // generate all three buttons as a button group
        $this->widget('bootstrap.widgets.TbButtonGroup', array(
            'size'=>$this->buttonSize, // null, 'large', 'small' or 'mini'
            'type'=>'null', // null, 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'	
            'buttons'=>array(
                array('icon'=>'chevron-left', 'label'=>' ', 'url'=>$prev_id>0 ? array($this->url, 'id'=>$prev_id) : '#', 'active'=>$prev_id == 0,),
                array('label'=>$idx.' / '.$cnt, 'size'=>'small', 'url'=>'#', 'active'=>true, 'items'=>$dirLinks,),
                array('icon'=>'chevron-right', 'label'=>' ', 'url'=>$next_id>0 ? array($this->url, 'id'=>$next_id) : '#', 'active'=>$next_id == 0,),
            ),
        ));
        echo '</div>';
    } 
} 
?>
