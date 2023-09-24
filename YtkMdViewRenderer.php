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
 * YtkMdViewRenderer is a very simple markdown renderer class to implement kind of 
 * content management system. This renderer allows to register a action within a 
 * controller the gives access to all markdown files in a given directory simple
 * by name (without implementing an event hanlder for each page). It does basically
 * the same as its counterpart CViewRenderer for php files.
 * 
 * Register this component in config/main.php and use it e.g.
 * in siteController as action handler.
 */
class YtkMdViewRenderer extends CViewRenderer
{
    // define the file extension for our mini CMS system
    public $fileExtension = '.md';

    protected function generateViewFile($sourceFile, $viewFile) 
    {
        $md = new CMarkdown;
        $input = file_get_contents($sourceFile);
        $output = $md->transform($input);       
        file_put_contents($viewFile, $output);
    }

}