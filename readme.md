# YTK - Yii Toolkit
> Version 1.0, 28. June, 2020

                      ___           ,-.  
            ,---,   ,--.'|_     ,--/-/|  
           /_ ./|   |--| :,'  ,--. :/ |  
     ,---, |--' :   :--: ' :  :--: ' /   
    /___/ \.--: | .;__,'  /   |--'  /    
     .--\  \ ,' ' |--|   |    '--|  :    
      \--;  `  ,' :__,'| :    |--|   \   
       \--\    '    '--: |__  '--: |. \  
        '--\   |    |--| '.'| |--| ' \ \ 
         \--;  ;    ;--:    ; '--: |--'  
          :--\  \   |--,   /  ;--|,'     
           \--' ;    ---`-'   '--'       
            `--`  Y i i  T o o l k i t 
                                        

Copyright (c) 2013-2020 Andreas Pott

# About
YTK is a collection of functions and widgets for the php web framework Yii (version 1.11).
The toolkit is designed to to be used along with the bootstrap extension for Yii.

# Install
Copy this project to the extensions folder of the yii projects under `protected\extensions\ytk`

# Configure 
To use the widgets, register ytk for autoloading in the file `protected\config\main.php` with the
following code snippet

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.ytk.*',     // <-- add this entry
	),

To the use the markdown renderer, add also the following entry to this config file
under the section `components`

    // application components
	'components'=>array(
        // [...] some components

        // renderer component for markdown pages simiar to the views/site/page rendering
        'viewRenderer' => array(
            'class'=>'YtkMdViewRenderer',
        ),
        // [...] some more components
    ),

To use the javascript components in the package, add the ytk component as viewRenderer to the main 
configuration file under `components`

    'ytk'=>array(
        'class'=>'application.extensions.ytk.ytk',
    ),

Additionally, the ytk component (with asset management must be initialized in protected/views/layout/main.php
directly after `<body>` (and if it is used right after the respective init call for the bootstrap components) with

    <?php Yii::app()->ytk->init();?>

# Usage of components
The provided widgets (here `ytktile`) of the package can be used as follows:

    $this->widget('application.extensions.ytk.ytktile', array(
        'header'=>'Min Example',
        'labels'=>array('X'=>'primary'),
        'body'=>'Some content',
    ));

Functions from the namespace `Ytk` can be simply called by prefixing it with `Ytk::`

    echo Ytk::EncodeSuccess('myLabel');

# Acknowledgement
Ytk is shipped with a small collection of php and javascript libraries under either MIT or LGPL license. As these 
libraries are exposed in the asset directory of the web projects, all thrid party code is fully available in source
code. 

# License

The YTK is licensed under MIT license, a weak copyleft open source license.