<?php
return array(
    'components'=>array(
        //Database of Magento1
        'mage1' => array(
            'connectionString' => 'mysql:host=127.0.0.1;dbname=farmazon',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'farmazon',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        ),
        //Database of Magento2 beta
        'mage2' => array(
            'connectionString' => 'mysql:host=127.0.0.1;dbname=farmazon_m2',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'farmazon',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        )
    ),

    'import'=>array(
        //This can change for your magento1 version if needed
        //'application.models.db.mage19x.*',
        'application.models.db.mage19x.*',
    )
);
