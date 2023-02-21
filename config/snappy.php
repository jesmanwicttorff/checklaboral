<?php
/* version linunx */
return array(
 'pdf' => array(
 'enabled' => true,
 'binary'  => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),
 'timeout' => false,
 'options' => array(),
 ),
 'image' => array(
 'enabled' => true,
 'binary'  => base_path('vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64'),
 'timeout' => false,
 'options' => array(),
 ),
);


/* versión windows
return array(
 'pdf' => array(
 'enabled' => true,
 'binary'  => base_path('vendor\wemersonjanuario\wkhtmltopdf-windows\bin\64bit\wkhtmltopdf'),
 'timeout' => false,
 'options' => array(),
 ),
 'image' => array(
 'enabled' => true,
 'binary'  => base_path('vendor\wemersonjanuario\wkhtmltopdf-windows\bin\64bit\wkhtmltoimage'),
 'timeout' => false,
 'options' => array(),
 ),
);

*/
