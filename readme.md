# YTK - Yii Toolkit

> Version 1.0, 28. June, 2020

Copyright (c) 2013-2020 Andreas Pott

# About
YTK is a collection of functions and widgets for the php web framework Yii (version 1.11).
The toolkit is designed to tbe used along with the bootstrap extension of Yii.

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

    // renderer component for markdown pages simiar to the views/site/page rendering
    'viewRenderer' => array(
        'class'=>'YtkMdViewRenderer',
    ),

# Usage of components
The provided widgets (here `ytktile`) of the package can be used as follows:

    $this->widget('application.extensions.ytk.ytktile', array(
        'header'=>'Min Example',
        'labels'=>array('X'=>'primary'),
        'body'=>'Some content',
    ));

Functions from the namespace `Ytk` can be simply called by prefixing it with `Ytk::`

    echo Ytk::EncodeSuccess('myLabel');

# License

The YTK is licensed under MIT license, a weak copyleft open source license.