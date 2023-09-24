# YTK - Yii Toolkit
> Version 1.1.4, September, 24th, 2023

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
                                        

Copyright (c) 2013-2023 Andreas Pott

# About
YTK is a collection of functions and widgets for the php web framework Yii (version 1.11).
The toolkit is designed to to be used along with the bootstrap extension for Yii.

# Install
Copy this project to the extensions folder of the yii projects under `protected\extensions\ytk`

# Install using composer
ytk can be installed using composer from packagist as `"require": "aspott/ytk"`

# Configure 
To use the widgets from ytk, register ytk for autoloading in the file `protected\config\main.php` with the
following code snippet (after manuall install)

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.ytk.*',     // <-- add this entry
	),

If composer was used, the alias must be changes. Using a file alias of Ytk with (to be defined in 
yii's `config.php` on top of the statement returning the configuration array)
    
    Yii::setPathOfAlias('ytk', dirname(__FILE__).'/../extensions/vendor/aspott/ytk');
    
    require 'protected/extensions/vendor/autoload.php';

The last line calls the autoload script from composer which is likely to be required by other 
composer loaded extensions as well. Using the `setPathOfAlias`, one can shorten  references to 
ytk components from `application.extensions.ytk.ytk` to `ytk.Ytk`. Make 
sure that the latter `Ytk` starts with a capital letter if you are running Linux (as filename 
are case sensitive). So aliasing with composer shortens the import array as follows

	'import'=>array(
		'application.models.*',
		// ...
		'ytk.*',                            // <-- see the shortend syntax thanks to aliasing
	),

To use Ytk's markdown renderer as a view, add the following entry to the config file `config/main.php`
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

To use the javascript components/assets in the package, add the ytk component to the main 
configuration file under `components`

    'ytk'=>array(
        'class'=>'application.extensions.ytk.Ytk',
    ),

Also, here this is reducted to 'ytk.Ytk' when using the aliasing.

Additionally, the ytk component (with asset management must be initialized in protected/views/layout/main.php
directly after `<body>` (and if it is used right after the respective init call for the bootstrap components) with

    <?php Yii::app()->ytk->init();?>

# Usage of components
The provided widgets (here `ytktile`) of the package can be used as follows:

    $this->widget('application.extensions.ytk.YtkTile', array(
        'header'=>'Min Example',
        'labels'=>array('X'=>'primary'),
        'body'=>'Some content',
    ));

With aliasing, also the name of the widgets is reduced to `'ytk.YtkTile'`. 

Functions from the namespace `Ytk` can be simply called by prefixing it with `Ytk::`

    echo Ytk::EncodeSuccess('myLabel');

Prior to using java script extensions (such as chart.js), add the following line in each view file

    Yii::app()->ytk->register('chartjs');

# Acknowledgement
Ytk is shipped with a collection of php and javascript libraries under either MIT or LGPL license. As these 
libraries are exposed in the asset directory of the web projects, all thrid party code is fully available in source
code. 

# License

The YTK is licensed under MIT license, a weak copyleft open source license.