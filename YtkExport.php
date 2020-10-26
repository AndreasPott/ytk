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
    /* the maximum number of rows that are fetched from the dataProvider */
    public $maxSize = 1000;
    
    // helper function to clean strings
    public static function clean($string) {
        return preg_replace('/[^A-Za-z0-9\- ]/', '', $string); // Removes special chars.
    }

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
        if ($this->maxSize === null) {
            $this->maxSize = 1000;
        }
    }

    /* a revised version of the writeCsv function that generates a nested 
     * array with the data items instead of a string
     * This function takes the settings of $include_header and $columns 
     * into account
     */ 
    public function transformArray()
    {
        $result = array();

        // configure the number of rows that are loaded from the database
		$this->dataProvider->getPagination()->pageSize = $this->maxSize;
        $items = $this->dataProvider->getData();
        // if no columns are defined, we render all columns
        if (count($this->columns) == 0)
        {
            // render the header for the given columns
            if (count($items) > 0 && $this->include_header == true)
            {
                $header = array();
                foreach ($items[0]->attributes as $key=>$attributes)
                    array_push($header, $key);
                array_push($result, $header);
            }
            foreach ($items as $item) {
                $line = array();
                foreach ($item->attributes as $key=>$attributes) 
                    array_push($line, $attributes);
                array_push($result, $line);                
            }
        } else {
            // render only the columns defined by the array $columns
            if (count($items) > 0 && $this->include_header == true)
            {
                // @TODO: Reconsider if we want to check already here if the columns exist?
                $header = array();
                foreach ($this->columns as $columns) 
                    array_push($header, $columns);
                array_push($result, $header);               
            }
            foreach ($items as $item) {
                $line = array();
                foreach ($this->columns as $columns) 
                    array_push($line, CHtml::value($item,$columns,""));
                array_push($result, $line);                
            }
        }
        return $result;
    }

    /* use the nested array format generated by transformArray to serialize 
     * data to an csv formatted table */
    public function writeCsvFromArray($data)
    {
        $content = "";
        foreach ($data as $line) {
            $sep = "";
            foreach ($line as $item) {
                $content .= $sep.$item;
                $sep = ";";
            }
            $content .= "\n";
        }
        return $content;
    }

    /* use the nested array format generated by transformArray to serialize 
     * data to an html table formatted structure */
    public function writeHtmlFromArray($data)
    {
        $content = "<table>";
        foreach ($data as $line) {
            $content .= '<tr>';
            foreach ($line as $item) 
                $content .= CHtml::tag('td', array(), $item);
            $content .= "</tr>\n";
        }
        $content .= "</table>";
        return $content;
    }

    /*
     * Return a string which contains the transformed data as csv file
     * $include_header defines if the file shall have a header line
     * $columns configures which columns are rendered
     */
    public function writeCsv() 
    {
        return $this->writeCsvFromArray($this->transformArray());
    }

    /* return a string containing the transformed data in the format defined by $fileformat
     */
    public function transform() 
    {
        if ($this->fileformat === 'csv')
            return $this->writeCsv();
        
        return "";
    }

    // render the widget with the configured data export mode
    public function run()
    {
        $data = $this->transformArray();
        if ($this->fileformat === 'csv')
            echo $this->writeCsvFromArray($data);
        if ($this->fileformat === 'html')
            echo $this->writeCsvFromArray($data);
    }     
}
?>