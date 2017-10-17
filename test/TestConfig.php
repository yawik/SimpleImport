<?php
return array(
    'modules' => array(
	    'Zend\ServiceManager\Di',
	    'Zend\Session',
	    'Zend\Router',
	    'Zend\Navigation',
	    'Zend\I18n',
	    'Zend\Filter',
	    'Zend\InputFilter',
	    'Zend\Form',
	    'Zend\Validator',
	    'Zend\Log',
	    'Zend\Mvc\Plugin\Prg',
	    'Zend\Mvc\Plugin\Identity',
	    'Zend\Mvc\Plugin\FlashMessenger',
	    'Zend\Mvc\I18n',
	    'Zend\Mvc\Console',
	    'Zend\Hydrator',
	    'Zend\Serializer',
	    'DoctrineModule',
	    'DoctrineMongoODMModule',
        'Core',
        'Auth',
        'Jobs',
        'SimpleImport'
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor',
        ),

        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
    )
);
