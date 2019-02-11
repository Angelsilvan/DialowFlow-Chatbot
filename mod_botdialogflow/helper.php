<?php

//require "/var/www/web/modules/mod_botdialogflow/tmpl/getResultApiAi.php"; 

require(dirname(__FILE__).'/tmpl/getResultApiAi.php');

/**
 * Helper class for Bot Dialogflow module
 * 
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @link http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * @license        GNU/GPL, see LICENSE.php
 */
class ModBotDialogflowHelper{

    /**
    * Retrieves the hello message
    */    
    public static function getTitle()
    {
        return 'Empieza con el bot:';
    }

    /**
    * Sets the answer by calling api ai with the query of the user
    */
    public static function setQueryAjax() {
        // Get module parameters
        if (isset($_POST['query'])){
            $query = $_POST['query'];
            $answer = getResults($query);
        }
        return $answer;
    }
}