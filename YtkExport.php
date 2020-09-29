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
 * YtkExport transforms queries modelled by CActiveDataProvider to data structures
 * such as csv-files, json-strings, xml-files, or xlsx excel spreadsheets. Other ideas 
 * for data format could be markdown tables, html tables, or latex tables.
 * The underlying approach is quite simple: the dataProvider represents a query
 * including filtering rules and related data. The query mapps technically to a
 * table like structure which can be sored in different file formats.
 * 
 * @TODO Add code for data preparation, i.e. overriding a possibly configured pagination
 * @TODO We may also check for a maximum number of items to be loaded by sending a
 * count request to the database before we load the raw data.
 * 
 */
class YtkExport extends CWidget
{
    /* the data provider object which shall be transformed */
    public $dataProvider = null; 
    /* an array representing the names of the columns from the dataProvider to transformed. 
     * Names can be written in dot notation which allows to access data in related tables 
     * e.g. user.username to get record->user->username */
    public $columns = array();
    /* The file format used for the output. Defaults to csv but will be extended 
       as the class evolves */
    public $fileformat = 'csv';
    /* by default (without transposing) the data fields are generated as columns */
    public $transpose = false;
    /* the separatur value inserted in csv files */
    public $separator = ";";
    /* determine if column header is added to the output (if the file format allows for this) */
    public $include_header = true;
    
    public function init() 
    {
        parent::init(); 
        if ($this->dataProvider === null) { 
            $this->dataProvider = null; 
        }
        if ($this->columns === null) { 
            $this->columns = array(); 
        }
        if ($this->fileformat === null) { 
            $this->fileformat = 'csv'; 
        }     
        if ($this->transpose === null) { 
            $this->transpose = false; 
        }
        if ($this->separator === null) { 
            $this->separator = ";"; 
        }
        if ($this->include_header === null) { 
            $this->include_header = true; 
        }                                   
    }

    /*
     * Return a string which contains the transformed data as csv file
     * $include_header defines if the file shall have a header line
     * $columns configures which columns are rendered
     */
    public function writeCsv() 
    {
        $content = "";
        $items = $this->dataProvider->getData();
        // if no columns are defined, we render all columns
        if (count($this->columns) == 0)
        {
            // render the header for the given columns
            if (count($items) > 0 && $this->include_header == true)
            {
                $sep = "";
                foreach ($items[0]->attributes as $key=>$attributes) {
                    $content .= $sep.$key;
                    $sep = $this->separator;
                }
                $content .= "\n";
            }
            foreach ($items as $item) {
                $sep = "";
                foreach ($item->attributes as $key=>$attributes) {
                    $content .= $sep.$attributes;
                    $sep = $this->separator;
                }
                $content .= "\n";                
            }
        } else {
            // render only the columns defined by the array $columns
            if (count($items) > 0 && $this->include_header == true)
            {
                // render the header for the given columns
                // @TODO: Reconsider if we want to check already here if the columns exist?
                $sep = "";
                foreach ($this->columns as $columns) {
                    $content .= $sep.$columns;
                    $sep = $this->separator;
                }
                $content .= "\n";                
            }
            foreach ($items as $item) {
                $sep = "";
                foreach ($this->columns as $columns) {
                    $content .= $sep.CHtml::value($item,$columns,"");
                    $sep = $this->separator;
                }
                $content .= "\n";                        
            }

        }
        return $content;
    }

    /* return a string containing the transformed data in the format defined by $fileformat
     */
    public function transform() 
    {
        if ($this->fileformat === 'csv')
            return $this->writeCsv();
        
        return "";
    }

    // render the widget
    public function run() {
        $this->dataProvider

        if ($this->fileformat === 'csv')
            echo $this->writeCsv();
    }     
}
?>