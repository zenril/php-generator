<?php

require '../PHPGenerator.php';

$PHPG = new PHPGenerator("test/class");

$PHPG->input(array('class' => array(
    'name' => 'Tree',
    'members' => array(
        array(
            'column' => array('type' => 'BigInt'),
            'name' => 'objectId',
            'id' => true            
        ),
        array(
            'column' => array('type' => 'varchar', 'length' => 255),
            'name' => 'objectName'           
        )
    )
)));

$PHPG->run();