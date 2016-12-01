<?php

require '../TemplateClassWriter.php';

use Zenril\PHPGenerator\TemplateClassWriter;

$PHPG = new TemplateClassWriter("app/Entities");

$PHPG->classData("Tree", array(
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
));

$PHPG->write();