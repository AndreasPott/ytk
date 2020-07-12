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
 * YtkCharsjs is a wrapper to use the chart.js library with yii. 
 * The widget must generate the div element to which the chart is rendered. 
 * Moreover, the widget must manage the assets requres, namely the chart.js 
 * script and add the script file to the header of the page.
 * The current version is drafted based on the implementation used in the bootstrap
 * extension for yii.
 */
class YtkCharsjs extends CApplicationComponent {

    /**
	 * @var boolean indicates whether assets should be republished on every request.
	 */
	public $forceCopyAssets = false;

    protected $_assetsUrl;
    
	/**
	 * Registers the chars.js JavaScript.
	 * @param int $position the position of the JavaScript code.
	 */
	protected function registerJS($position = CClientScript::POS_HEAD)
	{
		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		$filename = YII_DEBUG ? 'chart.js' : 'chart.min.js';
		$cs->registerScriptFile($this->getAssetsUrl().'/js/'.$filename, $position);
    }
    
    public function register()
    {
        $this->registerJS();
    }

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
			$assetsPath = Yii::getPathOfAlias('ytk.assets');
			$assetsUrl = Yii::app()->assetManager->publish($assetsPath, true, -1, $this->forceCopyAssets);
			return $this->_assetsUrl = $assetsUrl;
		}
    }
    
	public function init() 
	{ 
		$this->registerJs(); 
		parent::init(); 
	}
}
?>