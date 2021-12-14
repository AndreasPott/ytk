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
 * The Storyboard components implements a slide show like navigation through
 * a set of preselected files. Beside storing order and URL of the pages to
 * be shown in story mode, each page has a custom title and summary text block
 * that can be rendered on top (or bottom) of the page.
 * 
 * Implementation in split into two parts. A modelfile with CActiveRecord-derived
 * class manages the interface with the database for persistent storage of 
 * Storyboards in the database. The component YtkStoryboard provides all interfaces
 * to render and edit the story board.
 * 
 * Extending an existing project with Storybord function requires the following steps
 * - extend the layout/main.php file
 *      - with a custom fork for the main menu as follows:
 * 
 *         $ysb = new YtkStoryboard;
 *         if ($ysb->isStoryboard()) 
 *         	    $ysb->renderMenu();
 *         else {
 *         		$this->widget('bootstrap.widgets.TbNavbar', array(
 * 
 * - add the placeholder for the textblock in the page "container" e.g.
 * 
 *          <div class="container" id="page">
 *      	    <?php $ysb->renderHeader();	?>
 *              <?php if (isset($this->breadcrumbs)):?>
 * 
 * - if derired/required add the capture button in the footer, e.g.
 * 
 *   		<?php echo Yii::powered(); ?>
 *    	    <?php $ysb->renderCapture(); ?>
 *    </div><!-- footer -->
 * 
 * Moreover, one needs to implement an config and control page. The default settings uses the action
 * site/storyboard. On can implement this action without a view file as follows:
 * 	    public function actionStoryboard()
 *	    {
 *		    ob_start();
 *            echo "<h1>Storyboard</h1>\n";
 *	        $ysb = new YtkStoryboard;
 *		    $ysb->eventHandler();
 *		    $ysb->renderLinks();
 *  
 *		    $ysb->renderEditWidget($this);
 *		    // send the captured content to a text renderer
 *		    $this->renderText(ob_get_clean());	
 *	    }
 * 
 *  Clearly, one can also implement a full view file to customize the output either with a regular 
 *  action or view a page view. Set proper values in $configController and $configView to customize
 *  the view file. The following minimum content can be used in this view file:
 *
 *      $ysb = new YtkStoryboard;       // create the component
 *      $ysb->renderLinks();            // render the options menu
 *      $ysb->eventHandler();           // add the event handler to process request send to this page 
 *      $ysb->renderEditWidget($this);  // list all story board elements in a mini editor
 * 
 *  To completely customize editing of story board items, one can implement a full CRUD function on
 *  top of the Storyboard model class.
 */


/** 
 * We add as baselayer for data storage a model file that stores its information
 * in the database as ActiveRecord
 *
 * This is the model class for table "storyboard".
 *
 * The followings are the available columns in table 'storyboard':
 * @property integer $storyboard_id
 * @property integer $story_id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property integer $sequence
 * @property string $settings
 *
 * 
 * To create the respecitve data structure use the following SQL Statement
 * CREATE TABLE IF NOT EXISTS  `storyboard` (
 *   `storyboard_id` int(10) NOT NULL AUTO_INCREMENT,
 *   `story_id` int(11) NOT NULL,
 *   `title` text not null ,
 *   `text` text not null,
 *   `url` text not null,
 *   `sequence` int not null,
 *   `settings` text not null,
 *   PRIMARY KEY (`storyboard_id`)
 * ) ENGINE=InnoDB;
 * 
 */
class StoryBoard extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StoryBoard the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'storyboard';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('story_id, title, url, sequence', 'required'),
            array('settings, text', 'length', 'min'=>0),
            array('story_id, sequence', 'numerical', 'integerOnly'=>true),
			// Please remove those attributes that should not be searched.
			// The following rule is used by search().
            array('storyboard_id, story_id, title, text, url, sequence, settings', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

    /**
     * configure default properties for searching
     */
    public function defaultScope()
    {
        return array(
            'order'=>'sequence ASC',
        );
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'storyboard_id' => 'ID',
            'story_id' => 'Story ID',
			'title' => 'Title',
			'text' => 'Text',
			'url' => 'url',
			'sequence' => 'Sequence',
			'settings' => 'Settings',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('storyboard_id', $this->storyboard_id);
		$criteria->compare('story_id', $this->story_id);
		$criteria->compare('title', $this->title, true);
		$criteria->compare('text', $this->text);
		$criteria->compare('url',$this->url);
		$criteria->compare('sequence',$this->sequence);
		$criteria->compare('settings',$this->settings);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}


/**
 * A component class with all functions required for the story board feature
 * The revised version is based on a database table rather than a json storage.
 * 
 */
class YtkStoryboard
{
    // the yii controller, action, and view to where the eventHandler is placed
    public $configController = "site/storyboard";
    public $configView = '';

    public $configSessionTokenMode  = 'sbMode';
    public $configSessionTokenStory = 'sbStory';

    public $currentStory = 1;

    function __construct() {      
        // try to load the current story name from session token; the logical connection between 
        // the class member currentStory and the session entry is not clearly settled
        $this->currentStory = isset($_SESSION[$this->configSessionTokenStory]) ? $_SESSION[$this->configSessionTokenStory] : 1;
        
        // load all entries of this story
        $this->sb = Storyboard::model()->findAllByAttributes(array('story_id'=>$this->currentStory));
    }

    /** 
     * return true, if story board mode is active
     */
    public function isStoryboard() {
        return isset($_SESSION[$this->configSessionTokenMode]);
    }

    /** render a simple grid view with all items
     */
    public function renderListWidget($controller) {
        $dataProvider = new CActiveDataProvider('Storyboard');
        $controller->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider'=>$dataProvider,
        ));        
    }

    /**
     * Render a grid view with edit controls to change the values of the model class
     * Storyboard
     */
    public function renderEditWidget($controller) {
        $dataProvider = new CActiveDataProvider('Storyboard');
        $link = array($this->configController, 'view'=>$this->configView);
        echo CHtml::beginForm($link); 
        $controller->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider'=>$dataProvider,
            'columns'=>array(
                'storyboard_id',
                array(
                    'name'=>'title',
                    'value'=>function($data) {
                        echo CHtml::textField("Storyboard[$data->storyboard_id][title]", $data->title, array());
                    }
                ),
                array(
                    'name'=>'text',
                    'value'=>function($data) {
                        echo CHtml::textField("Storyboard[$data->storyboard_id][text]", $data->text, array());
                    }
                ),
                array(
                    'name'=>'story_id',
                    'value'=>function($data) {
                        echo CHtml::textField("Storyboard[$data->storyboard_id][story_id]", $data->story_id, array('class'=>'span1'));
                    }
                ),                
                array(
                    'name'=>'sequence',
                    'value'=>function($data) {
                        echo CHtml::textField("Storyboard[$data->storyboard_id][sequence]", $data->sequence, array('class'=>'span1'));
                    }
                ),
                array(
                    'name'=>'Operations',
                    'value'=>function($data) use($link) {
                        echo CHtml::ajaxLink(
                            CHtml::tag('i', array('class'=>'icon-trash'), ''),
                            $link,
                            array(
                                'type' => 'POST',
                                'data' => array('id'=>$data->storyboard_id, 'cmd'=>'delete'),
                                // reload the page after processing the request; this is not very efficient but works fine
                                'success' => 'function(response) { window.location = "'.Yii::app()->createUrl($link[0]).'"}',
                            )
                        );
                    }
                )
            )
        )); 
        // add a submit button 
        $controller->widget('bootstrap.widgets.TbButton', array(
            'buttonType'=>'submit',
            'type'=>'primary',
            'label'=>'Update',
        )); 
        echo CHtml::endForm();               
    }

    /** Create a variant of the main menu to replace the original version while in 
     *  Story board mode.
     */
    public function renderMenu() {
		$buttons = array();
        // Menu generation: Create only three entries: Current, previous, and next item
        // therefore, we need to search the neighbouring instances
        $storyboard = Storyboard::model()->findAllByAttributes(array('story_id'=>$this->currentStory), array('order'=>'sequence ASC'));
        if (count($storyboard) > 0)
            $storyboardid = isset($_GET['storyboardid']) ? $_GET['storyboardid'] : $storyboard[0]->storyboard_id;
        else
            $storyboardid = 0;
        // determine the length of the story and our current position
        $current = Storyboard::model()->findByPk($storyboardid);
        if (isset($current)) {

            $length = count($this->sb);
            $pos = Storyboard::model()->count(array('order'=>'sequence DESC', 'condition'=>"story_ID = $this->currentStory && sequence <= $current->sequence"));
            // determine the previous entry
            $prev = Storyboard::model()->find(array('order'=>'sequence DESC', 'condition'=>"story_ID = $this->currentStory && sequence < $current->sequence"));
            if ($prev) {
                $buttons[] = array('label'=>"Back", 'icon'=>'chevron-left', 'url'=>array_merge(json_decode($prev->url, true), array('storyboardid'=>$prev->storyboard_id)));
            } else
                $buttons[] = array('label'=>"Back", 'icon'=>'chevron-left', 'url'=>'#', 'disabled'=>'disabled');
            // determine the next entry
            $next = Storyboard::model()->find(array('order'=>'sequence ASC', 'condition'=>"story_ID = $this->currentStory && sequence > $current->sequence"));
            if ($next) {
                $buttons[] = array('label'=>"Next", 'icon'=>'chevron-right', 'url'=>array_merge(json_decode($next->url, true), array('storyboardid'=>$next->storyboard_id)));
            } else
                $buttons[] = array('label'=>"Next", 'icon'=>'chevron-right', 'url'=>'#', 'disabled'=>'disabled');

            // render button for current slide
            $buttons[] = array('label'=>$current->title." ($pos/$length)", 'url'=>array_merge(json_decode($current->url, true), array('storyboardid'=>$current->storyboard_id)));
            $i = 1;
            foreach ($this->sb as $page) {
                // limit the number of pages to seven and show and ellipse if there are more pages
                $buttons2[] = array('label'=>"$i -- $page->title", 'url'=>array_merge(json_decode($page->url, true), array('storyboardid'=>$page->storyboard_id)));
                if ($i++ > 25) {
                    $buttons2[] = array('label'=>'...', 'url'=>'#', 'disabled'=>'disabled');
                    break;
                }
            }
            // add the buttons with the slide names as submenu at the end of the menu bar
            $buttons[] = array('label'=>'Pages', 'items'=>$buttons2);
        }

        Yii::app()->getController()->widget('bootstrap.widgets.TbNavbar', array(
			'brand'=>'Exit',       // <- exh
			'type'=>'inverse',
			'brandUrl'=>array($this->configController, 'view'=>$this->configView, 'cmd'=>'end'),
			'collapse'=>false,
			'items'=>array(array(
				'class'=>'bootstrap.widgets.TbMenu',
				'items'=>$buttons,
			)),
		));        
    }

    /**
     * Render the small widget which is place on top of the actual page to show title and remarks
     */
    public function renderHeader() {
        // loop through all entries and find the one that matches our current storyboardid
		foreach ($this->sb as $page) {
			if (isset($_GET['storyboardid']) && $_GET['storyboardid'] == $page->storyboard_id) {
				$md = new CMarkdown;
				echo CHtml::tag('div', array('class'=>'box'), CHtml::tag('h1', array(), $page->title).$md->transform($page->text));
				break;
			}
		}        
    }

    /** render a "capture" link that saves the current page into the database
     *  This function must be refactored as the target link must be page
     *  that exists in the project
     */
    public function renderCapture() {
        $opt = self::getUri(); 
        $ctrl = Yii::app()->getController();
        echo CHtml::link('capture', array(
            $this->configController,
            'view'=>$this->configView,
            'cmd'=>'capture', 
            'target'=>json_encode($opt), 
            'title'=>$ctrl->id."/".$ctrl->action->id)
        );        
    }

    /**
     * Render some control buttons to trigger start, stop, and reset of story mode
     * The eventHander must be in the same file
     */
    public function renderLinks() {
        $ctrl = Yii::app()->getController();
        $form = $ctrl->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'id'=>'npc-form',
            'enableAjaxValidation'=>false,
            'type'=>'horizontal',
            'action'=>array($this->configController, 'view'=>$this->configView),
        ));
        // add teh control links for start/stop/reset
        $htmlOpts = array('class'=>'btn');
        echo CHtml::link('Start', array($this->configController, 'view'=>$this->configView, 'cmd'=>'start'), $htmlOpts)." ";
        echo CHtml::link('End',   array($this->configController, 'view'=>$this->configView, 'cmd'=>'end'), $htmlOpts)." ";
        echo CHtml::link('Reset', array($this->configController, 'view'=>$this->configView, 'cmd'=>'reset'), $htmlOpts);
        echo "&nbsp;";
        // create the cotrol to select and change story ID
        $stories = CHtml::listData(Storyboard::model()->findAll(array(
            'distinct'=>false,	// remove double entries from the list
        )), 'story_id','story_id');
        echo "Stories: ".CHtml::dropDownList('story_id',$this->currentStory, $stories, array('class'=>'span1'));        
        $ctrl->widget('bootstrap.widgets.TbButton', array(
            'buttonType'=>'submit',
            'label'=>'Set Story',
        ));
        $ctrl->widget('bootstrap.widgets.TbButton', array(
            'buttonType'=>'submit',
            // add custom POST parameters
            'label'=>'Renumber Story',
            // this sets the parameter $_POST['renumber'] such that isset(_POST['renumber]) is true afterwards
            'htmlOptions'=>array('name'=>'renumber'),
        ));
        $ctrl->endWidget();
    }

    /**
     * Handle the commands passed to the class via get and post variables in GET[cmd]
     */
    public function eventHandler() {
        // reset the story board data structure to default values
        if (isset($_GET['cmd']) && $_GET['cmd'] == 'reset') {
            // as data items can now be edited through the database
            // there is no commands for the reset command
        }
        
        // set the start flag to set story board to on
        if (isset($_GET['cmd']) && $_GET['cmd'] == 'start') {
            $_SESSION[$this->configSessionTokenMode] = 1;
            $_SESSION[$this->configSessionTokenStory] = $this->currentStory;
        }

        // stop the story board mode by unsetting the session variable
        if (isset($_GET['cmd']) && $_GET['cmd'] == 'end') {
            unset($_SESSION[$this->configSessionTokenMode]);
        }

        // event handler for capture
        if (isset($_GET['cmd']) && $_GET['cmd'] == 'capture') {
            // append a line
            //  handling a GET[target] gives an empty array
            $url = json_decode($_GET['target'], true);
            if (count($url) == 0)
                $url = array('/');
            else if (strpos($url[0],'/') == false)
                $url[0] = '/'.$url[0];
            
            $lastItem = Storyboard::model()->find(array('order'=>'storyboard_id DESC'));

            $storyboard = new Storyboard;
            $storyboard->story_id = $this->currentStory;
            $storyboard->title = $_GET['title'];
            $storyboard->text = "";
            $storyboard->url = json_encode($url);
            $storyboard->sequence = isset($lastItem) ? $lastItem->storyboard_id+1 : 1;
            $storyboard->settings = "";
            $storyboard->save();
        }
        
        // handle the request for deleting an item; delete request must be submitte by POST requests
        if (isset($_POST['cmd']) && $_POST['cmd'] == 'delete') {
            if (isset($_POST['id']))
                $item = Storyboard::model()->findByPk($_POST['id']);
            if (isset($item)) {
                $item->delete();
            }            
        }
            // the renumber command loads all elements of a given story and assigs increasing sequence numbers
        if (isset($_POST['renumber']) && isset($_POST['story_id']))
        {
            $items = Storyboard::model()->findAllByAttributes(array('story_id'=>$_POST['story_id']), array('order'=>'sequence ASC'));
            foreach ($items as $i=>$item)
            {
                $item->sequence = $i+1;
                $item->save();
            }
        }
        
        // in addition to handling GET variables we also handle form submission with POST
        // variabels e.g. submitted by the form generated by renderEditWidget())
        if (isset($_POST['Storyboard']) && is_array($_POST['Storyboard']))
        {
            // process data coming from batch update; data is given as key(id)-valeu(attribute array) pairs
            foreach ($_POST['Storyboard'] as $id=>$line) {
                // we can only update elements that exist; batch update does not create items
                $item = Storyboard::model()->findByPk($id);
                if (!isset($item))
                    continue;
                $item->attributes = $line;
                $item->save();
            }
        }
        if (isset($_POST['story_id']))
        {
            $_SESSION[$this->configSessionTokenStory] = $_POST['story_id'];
            $this->currentStory = $_POST['story_id']; 
        }        
    }

    /** 
     * convert the current yii Uri (essentially expressed as the current GET variables) 
     * into yii's array syntax 
     */
    static public function getUri() {
        if (isset($_GET['r'])) 
            $res = array($_GET['r']);
        else
            $res = array();
    
        foreach ($_GET as $k=>$v)
            if ($k != 'r')
                $res[$k] = $v;
    
        return $res;
    }
}

?>