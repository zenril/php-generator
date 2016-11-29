<?php
require __DIR__ . '/../vendor/autoload.php';

$tmpl = file_get_contents(__DIR__ . '/templates/doctrine.tmpl',TRUE);
$m = new Mustache_Engine;
$m->addHelper('member', [
    'lower' => function($value) { return strtolower((string) $value); },
    'upper' => function($value) { return strtoupper((string) $value); },
    'uc' => function($value) { return ucfirst((string) $value); },
    'underscore' => function($value) { return strtolower(preg_replace('/\B([A-Z])/', '_$1', (string) $value)); }
]);
echo $m->render($tmpl, 
array('class' => array(

    'name' => 'Tree',
    'members' => array( 
        array(
            'column' => array('type' => 'BigInt', 'id' => true),
            'name' => 'objectId'            
        ),
        array(
            'column' => array('type' => 'varchar', 'length' => 255),
            'name' => 'objectName'           
        )
    )
))); 