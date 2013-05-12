<?php
return array(
    'config' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 2592000, # month
            ),
        ),
    ),
    'generic' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 7200,
            ),
        ),
    ),
    'session' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 2592000, # 30 * 24 * 3600
            ),
        ),
    ),
    'model' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 604800, # 7 * 24 * 3600
            ),
        ),
    ),
    'form' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 604800, # 7 * 24 * 3600
            ),
        ),
    ),
    'entityViewDelay' => array(
        'frontend' => array(
            'options' => array(
                'lifetime' => 86400, # 24h to reset view
            ),
        ),
    ),

);
