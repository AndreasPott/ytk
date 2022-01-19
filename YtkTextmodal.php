<?php 
/* Ytk - Yii Toolkit
*
* Copyright (c) 2013-2022 Andreas Pott
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
 * This widget is a model text field editor that updates data by ajex (e.g. to edit descriptions or notes 
 * in a larger datarecord; usually this a model of the current model).
 * Note that the parameters $ajaxTarget and $model_id must be unique within one page
 *
 * Example; The usage of the widget has four simple steps
 * 1) add the widget
        $this->widget('application.components.ytktextmodal', array(...));
 * 2) add link or button to open the modal window. Example:
        <a href="#myModal" role="button" class="btn" data-toggle="modal">Note</a>
 *    Where the href target must be the html id used in $model->id
 * 3) Define a css-target to be altered by the ajax call with the id passed to $ajaxTarget
        <span id="ajaxTarget">Default content</span>
 * 4) Write an event handler in the controller class to perform the update. The controller is passed in $ajaxUrl
 *    as "controller/actionname"; for the code below it is 'controll/saveNote' An example code is as follows
    public function actionSaveNote()
 	 {
 	 	if (isset($_POST['data2']) && isset($_POST['src_id']))
 	 	{
 	 		$note = $_POST['data2'];
 	 		$id = $_POST['src_id'];
 	 	}
 	 	else
 	 		echo "Ajax-Error";
 	 	// save the note in the model
 	 	$item = TargetModelToBeUpdated::model()->findByPk($id);
 	 	$item->notes = $note;
 	 	if (!$item->save())
 	 		echo "Error while saving the model";
 	 	// send back a reply
 	 	echo $note;
 	 }
 */
class YtkTextModal extends CWidget { 

    public $title;          // the title to be displayed in the model window
    public $ajaxUrl;        // the Yii action to be trigger if the model is closed with the submit button
                            // the signature of the function must be an action of a Yii-Controller where the function
                            // takes no parameters actionCallback() and the data from the ajax call is passed in  
                            //   $_POST['data2'] and $_POST['src_id'] 
                            // which is the new text (frin data2) and the record to update (from src_id)
    public $model_id;       // the html id of the top level div; the id must be unique within one site and 
                            // is required to show the model
    public $ajaxTarget;     // the id of the html element which will be updated with the result of the ajax 
                            // controller call (usually a copy of the text which was written into the database)
                            // the name of the ajaxTarget usually starts with '#' followed by the id of the target
    public $item_id;        // the id (primary key) of the activeRecord to be updated. This id will be passed 
                            // to the event handler called through $ajaxUrl to identify the record to be updated
    public $value;          // the content of the textfield to be shown prior to editing.
    public $rows;           // the number of lines of the textfield in the modal dialog (defaults to 6)

    // assign default values for unset attributes
    public function init() { 
        parent::init(); 
        if ($this->title === null) { 
            $this->title = '';  // default value if not set 
        }
        if ($this->ajaxUrl === null) { 
            $this->ajaxUrl = '';  // default value if not set 
        }        
        if ($this->rows === null) { 
            $this->rows = 6; 
        } 
    } 

    // render the widget
    // TODO: we may want to parameterize the id of textArea which is also referred to in the ajaxButton
    // TODO: customize the rendered text tokens (current statically in German, e.g. "Speichern" und "Zurück")
    public function run() 
    {
        echo '<div id="'.$this->model_id.'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
        echo '<div class="modal-header">';
        echo '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
        echo '    <h3 id="myModalLabel">'.$this->title.'</h3>';
        echo '</div>';
        echo '<div class="modal-body">';
        echo CHtml::textArea('notes'.$this->item_id, $this->value, array('rows'=>$this->rows, 'cols'=>50, 'class'=>'span5', 'style'=>'height: 80px;'));
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '    <button class="btn" data-dismiss="modal" aria-hidden="true">Zurück</button>';
        // use of the ajex request handler with yii methods
        echo CHtml::ajaxButton('Speichern',
            array($this->ajaxUrl),	// Yii URL
            array(
                'type' => 'POST',
                'data' => 'js: {"data2": $("#notes'.$this->item_id.'").val(), "src_id": '.$this->item_id.'}',	// read the value of the input #MyAjaxTargetX. the value is stored in $_POST['data2'] 
                'update' => $this->ajaxTarget,	// the id of the html element to be modified by the result of the ajax request handler in controller/ajaxcontent
                ), // jQuery selector
            array('class'=>'btn btn-primary', "data-dismiss"=>"modal", "aria-hidden"=>"true")
        ); 
        echo '  </div>';
        echo '</div>';
    } 
} 
?>

