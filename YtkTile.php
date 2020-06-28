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
 * This widget is the templates for cards (tiles) that consist of a headline, a body, and a footer line.
 * The widget shall be used to group information in blocks with a clear structure. The header is followed
 * by key information and/or a list of links, separated with vertical bars | 
 * The widget is under design for usage in dasboards, cockpits, the start screens, and condensed cards in 
 * overviews but might be also used for other data that should be displayed in a two or three column floating layout. 
 */
class YtkTile extends CWidget { 

    public $header;         // headline in the top part of the tile
    public $buttons;        // array of buttons to be shown in the footer; each element must be an key-value pair with name and url
    public $body;           // content of the body to be render as HTML
    public $footer;         // text printed before the buttons in the footer
    public $span;           // class to be inserted into the main div; can be used to control with by bootstraps spanX CSS
    public $labels;         // array of labels to be rendered in the header behind the headline; labels 
                            // can be either a key-value pair for name and (bootstrap label-)class or
                            // provide an array with name, label, url, and value 
    public $icon;           // class name of an icon from awesome fonts to be rendered as background
    public $body_height;    // height in px of the body element; can be used to given tiles a fixed size
    public $fixed_header;   // defines the heights of the header line; by default it is fixed to 30px; other height can be assigned
                            // setting the value to empty '' makes the heights of the header dynamic

    // assign default values for unset attributes
    public function init() { 
        parent::init(); 
        if ($this->span === null) { 
            $this->span = 'span3'; 
        }
        if ($this->buttons === null) { 
            $this->buttons = array(); 
        } 
        if ($this->labels === null) { 
            $this->labels = array(); 
        } 
        if ($this->footer === null) { 
            $this->footer = ''; 
        } 
        if ($this->icon === null) {
            $this->icon = '';
        }
        if ($this->body_height === null) {
            $this->body_height = 'initial';
        }
        if ($this->fixed_header === null) {
            $this->fixed_header = '30px';
        }
    } 

    // render the widget
    public function run() {
        // we have to modify the style directly by setting position to static to remove the floating from the model-class.  
        echo '<div class="modal '.$this->span.'"  style="position: static; ">';
        echo '  <div class="modal-header"';
        if ($this->fixed_header!='')
            echo ' style="min-height: '.$this->fixed_header.'; max-height: '.$this->fixed_header.'; overflow: hidden;"';
        echo '>';
        // echo '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>';
        echo '    <div class="pull-left"><h3>'.$this->header.'</h3></div>';
        echo '    <div class="pull-right">';
        $first = true;
        foreach ($this->labels as $label => $value) {
            echo $first ? '': ' | ';
            if (is_array($value))
                echo $value['name']." <a class=\"label label-".$value['label']."\" href=\"".$value['url']."\">".$value['value']."</a>";
            else
                echo "<a class=\"label label-$value\" href=\"#\">$label</a>";
            $first = false;
        }
        echo '    </div>';
        echo '    <div class="clearfix"></div>';
        echo '  </div>';
        echo CHtml::openTag('div', array('class'=>'modal-body', 'style'=>"min-height: $this->body_height; max-height: $this->body_height; overflow: hidden;"));
        echo '  <div style="position: absolute; top: 2; left: 0; bottom: 0; right: 2; z-index: 0; align: right; overflow: hidden;">';
        echo '    <i class="'.$this->icon.' pull-right"></i></div>';
        echo '    '.$this->body;
        echo '  </div>';
        echo '  <div class="modal-footer">';
        echo $this->footer;
        foreach ($this->buttons as $button => $url) {
            echo '    <a href="'.$url.'" class="btn btn-primary btn-small">'.$button.'</a>';
        }
        echo '  </div>';
        echo '</div>';
    } 
} 
?>

