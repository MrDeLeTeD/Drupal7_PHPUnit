<?php

abstract class PhpUnitWrapper extends PHPUnit_Framework_TestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '') 
    {
        try {
            drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        } catch (Exception $err) {
            echo $err->getMessage();
            exit();
        }
        
        parent::__construct($name, $data, $dataName);
    }
    
    public function __destruct() {
        //TODO clean l'environnement;
    }
}
