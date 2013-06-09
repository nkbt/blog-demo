<?php
namespace PHPSTORM_META {


    /** @noinspection PhpUnusedLocalVariableInspection */
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    $STATIC_METHOD_TYPES = [

        \Core_Model_Factory::get('') => [
            'Comment' instanceof \Model_Comment,
            'Topic' instanceof \Model_Topic,
            'User' instanceof \Model_User,
        ],

        \Zend_Registry::get('')      => [
            'dbAdapter' instanceof \Custom_Db_Adapter_Pdo_Mysql,

            # Configs
            'applicationConfig' instanceof \Zend_Config,
            'config' instanceof \Zend_Config,
            'fileConfig' instanceof \Zend_Config,
            'navigationConfig' instanceof \Zend_Config,

            # Application specific
            'Redis' instanceof \Redis,
        ],

    ];
}