<?php
require_once __DIR__.'/vendor/autoload.php';

use DialogFlow\Client;
use DialogFlow\Model\Query;
use DialogFlow\Method\QueryApi;

/**
* Function that call Dialogflow or search in the GSI databases depending on the intent.
*/
function getResults($query){
    try {
        $client = new Client('588f9634991e45e4a77b31f4f2a2ad0f');
        $queryApi = new QueryApi($client);

        $meaning = $queryApi->extractMeaning($query, [
            'sessionId' => '1234567890',
            'lang' => 'es',
        ]);
        $response = new Query($meaning);
        $result = $response->getResult();
        $metadata = $result->getMetadata();
        $intentName = $metadata->getIntentName();
        //Si queremos buscar información la web nos salimos de la conversacion habitual, utilizamos el plugin.
        if($intentName == "getMembers"){
            $arrayAreas = array('staff');
            $found = searchCoincidences($query, $arrayAreas);
            if($found == ""){
                $speech = "No se ha encontrado información sobre este usuario. Escribe su nombre completo.";
            }else{
                //Ejemplo: Ángel Silván
                $speech = onSearch($found, $arrayAreas);
            }
        }elseif ($intentName == "getProjects"){
            $arrayAreas = array('projects');
            $found = searchCoincidences($query, $arrayAreas);
            if($found == ""){
                $speech = "No se ha encontrado información sobre este proyecto. Sea más preciso.";
            }else{
                //Ejemplo: EXPERBAO II
                $speech = onSearch($found, $arrayAreas);
            }
        }elseif ($intentName == "getPublications"){
            $arrayAreas = array('publications');
            $found = searchCoincidences($query, $arrayAreas);
            if($found == ""){
                $speech = "No se ha encontrado información sobre esta publicación. Sea más preciso.";
            }else{
                //Ejemplo: Bioingeniería y salud
                $speech = onSearch($found, $arrayAreas);
            }
        }
        else{
            $fulfillment = $result->getFulfillment();
            $speech = $fulfillment->getSpeech();
        }
        return $speech;
    } catch (\Exception $error) {
        echo $error->getMessage();
    }
}

function searchCoincidences($query, $arrayAreas){
    if($arrayAreas == array('staff')){
        $allInfo = getAllMembers();
    }else if($arrayAreas == array('projects')){
        $allInfo = getAllProjects();
    }else{
        $allInfo = getAllPublications();
    }
    for($i = 0; $i < count($allInfo); $i++){
        if(stripos($query, $allInfo[$i]) !== false){
            return $allInfo[$i];
        }
    }
    return "";
}

function url_completa($forwarded_host = false) {
    $ssl   = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
    $proto = strtolower($_SERVER['SERVER_PROTOCOL']);
    $proto = substr($proto, 0, strpos($proto, '/')) . ($ssl ? 's' : '' );
    if ($forwarded_host && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } else {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $port = $_SERVER['SERVER_PORT'];
            $port = ((!$ssl && $port=='80') || ($ssl && $port=='443' )) ? '' : ':' . $port;
            $host = $_SERVER['SERVER_NAME'] . $port;
        }
    }
    $request = $_SERVER['REQUEST_URI'];
    return $proto . '://' . $host . $request;
}

/**
* JResearch Related Information Search method. It includes information of research projects, 
* publications and staff members.
*
* The sql returns the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string matching option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
*/
function onSearch($query, $arrayAreas){
    JPluginHelper::importPlugin('search');
    $dispatcher = JEventDispatcher::getInstance();
    $results = $dispatcher->trigger('onContentSearch', array($query,'all','newest',$arrayAreas));
    if($arrayAreas === array('staff')){
       if($results[0]){
            if($results[0][0]){
                $resp = $results[0][0];
                $names = json_encode($resp->title);
                $name = Utf8_ansi(str_replace(array('"'), '', $names));
                $arrayName = explode(" ",$names);
                $firstName = Utf8_normal(str_replace(array('"'), '', $arrayName[0]));
                $lastName = Utf8_normal(str_replace(array('"'), '', $arrayName[count($arrayName)-1]));
                $position = getPosition($firstName, $lastName);
                $section = str_replace(array('"'), ' ', json_encode($resp->section));
                $sectionNoMember = Utf8_ansi(str_replace(array('\/Member'), '', $section));
                $urlCompleta = url_completa();
                $urlPrincipio = explode("index", $urlCompleta);
                $url = $urlPrincipio[0] . str_replace(array('"'), '', json_encode($resp->href)) . "1";
                return $name . " es: " . $position . ". Se encuentra en la sección de: " . $sectionNoMember . ". Tienes mas información aquí: " . $url;
            }
        }
    }else if($arrayAreas === array('projects')){
        if($results[0]){
            if($results[0][0]){
                $resp = $results[0][0];
                $title = Utf8_ansi(str_replace(array('"'), '', json_encode($resp->title)));
                $urlCompleta = url_completa();
                $urlPrincipio = explode("index", $urlCompleta);
                $url = $urlPrincipio[0] . str_replace(array('"'), '', json_encode($resp->href)) . "1";
                $section = str_replace(array('"'), ' ', json_encode($resp->section));
                $sectionNoProject = Utf8_ansi(str_replace(array('\/Project'), '', $section));
                $date = str_replace(array('"'), ' ', json_encode($resp->created));
                return $title . " se creó en la sección de: " . $sectionNoProject . "el día: " . $date . ". Puedes ver mas información aquí: " . $url;
            }
        }
    }else if($arrayAreas === array('publications')){
        if($results[0]){
            if($results[0][0]){
                $resp = $results[0][0];
                $title = Utf8_normal(str_replace(array('"'), '', json_encode($resp->title)));
                $urlCompleta = url_completa();
                if(strpos($urlCompleta, 'web') !== false){
                    $urlPrincipio = explode("/web", $urlCompleta);
                }else{
                    $urlPrincipio = explode("/index", $urlCompleta);
                }
                $url = $urlPrincipio[0] . str_replace(array('"'), '', json_encode($resp->href));
                $urlFormat = str_replace(array("\\"), '', $url) . "1";
                $date = str_replace(array('"'), ' ', json_encode($resp->created));
                return $title . " se publicó el día: " . $date . ". Puedes ver mas información aquí: " . $urlFormat;
            }
        }
    }
    return "No se ha encontrado lo que buscabas";
}

/**
* Function which replace the code of some characters by the special character.
*/
function Utf8_ansi($valor='') {
    $utf8_ansi2 = array(
    "\u00c0" =>"À",
    "\u00c1" =>"Á",
    "\u00c2" =>"Â",
    "\u00c3" =>"Ã",
    "\u00c4" =>"Ä",
    "\u00c5" =>"Å",
    "\u00c6" =>"Æ",
    "\u00c7" =>"Ç",
    "\u00c8" =>"È",
    "\u00c9" =>"É",
    "\u00ca" =>"Ê",
    "\u00cb" =>"Ë",
    "\u00cc" =>"Ì",
    "\u00cd" =>"Í",
    "\u00ce" =>"Î",
    "\u00cf" =>"Ï",
    "\u00d1" =>"Ñ",
    "\u00d2" =>"Ò",
    "\u00d3" =>"Ó",
    "\u00d4" =>"Ô",
    "\u00d5" =>"Õ",
    "\u00d6" =>"Ö",
    "\u00d8" =>"Ø",
    "\u00d9" =>"Ù",
    "\u00da" =>"Ú",
    "\u00db" =>"Û",
    "\u00dc" =>"Ü",
    "\u00dd" =>"Ý",
    "\u00df" =>"ß",
    "\u00e0" =>"à",
    "\u00e1" =>"á",
    "\u00e2" =>"â",
    "\u00e3" =>"ã",
    "\u00e4" =>"ä",
    "\u00e5" =>"å",
    "\u00e6" =>"æ",
    "\u00e7" =>"ç",
    "\u00e8" =>"è",
    "\u00e9" =>"é",
    "\u00ea" =>"ê",
    "\u00eb" =>"ë",
    "\u00ec" =>"ì",
    "\u00ed" =>"í",
    "\u00ee" =>"î",
    "\u00ef" =>"ï",
    "\u00f0" =>"ð",
    "\u00f1" =>"ñ",
    "\u00f2" =>"ò",
    "\u00f3" =>"ó",
    "\u00f4" =>"ô",
    "\u00f5" =>"õ",
    "\u00f6" =>"ö",
    "\u00f8" =>"ø",
    "\u00f9" =>"ù",
    "\u00fa" =>"ú",
    "\u00fb" =>"û",
    "\u00fc" =>"ü",
    "\u00fd" =>"ý",
    "\u00ff" =>"ÿ");
    return strtr($valor, $utf8_ansi2);      
}

/**
* Function which replace the code of some characters by the normal character.
*/
function Utf8_normal($valor='') {
    $utf8_ansi2 = array(
    "\u00c0" =>"A",
    "\u00c1" =>"A",
    "\u00c8" =>"E",
    "\u00c9" =>"E",
    "\u00cc" =>"I",
    "\u00cd" =>"I",
    "\u00d1" =>"Ñ",
    "\u00d2" =>"O",
    "\u00d3" =>"O",
    "\u00d9" =>"U",
    "\u00da" =>"U",
    "\u00e0" =>"a",
    "\u00e1" =>"a",
    "\u00e7" =>"ç",
    "\u00e8" =>"e",
    "\u00e9" =>"e",
    "\u00ec" =>"i",
    "\u00ed" =>"i",
    "\u00f1" =>"ñ",
    "\u00f2" =>"o",
    "\u00f3" =>"o",
    "\u00f9" =>"u",
    "\u00fa" =>"u",);
    return strtr($valor, $utf8_ansi2);      
}


/**
* Function which get all the members from the GSI database.
*/
function getAllMembers(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('firstname', 'lastname')));
    $query->from($db->quoteName('j3_jresearch_member'));

    // Reset the query using our newly populated query object.
    $db->setQuery($query);

    // Load the results as a list of stdClass objects (see later for more options on retrieving data).
    $results = $db->loadObjectList();

    $values = "";
    foreach($results as $row){
        $values =  $values . $row->firstname. " " . $row->lastname. "/";
    }

    $valuesArray = explode("/", $values);
    for($i=0; $i<count($valuesArray); $i++){
        $valuesArray[$i] = Utf8_ansi($valuesArray[$i]);
    }
    return $valuesArray;
}

/**
* Function which get all the projects from the GSI database.
*/
function getAllProjects(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('title')));
    $query->from($db->quoteName('j3_jresearch_project'));

    // Reset the query using our newly populated query object.
    $db->setQuery($query);

    // Load the results as a list of stdClass objects (see later for more options on retrieving data).
    $results = $db->loadObjectList();

    $b = "";
    foreach($results as $row){
        $b =  $b . $row->title. "/";
    }

    $valuesArray = explode("/", $b);
    return $valuesArray;
}

/**
* Function which get all the publications from the GSI database.
*/
function getAllPublications(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('title')));
    $query->from($db->quoteName('j3_jresearch_publication'));

    // Reset the query using our newly populated query object.
    $db->setQuery($query);

    // Load the results as a list of stdClass objects (see later for more options on retrieving data).
    $results = $db->loadObjectList();

    $p = "";
    foreach($results as $row){
        $p =  $p . $row->title. "/";
    }

    $valuesArray = explode("/", $p);
    return $valuesArray;
}

/**
* Function which get the position of a member passed as a parameter.
*/
function getPosition($firstname, $lastname){
    $db = JFactory::getDbo();
    $a = $db->getQuery(true);
    $a->select($db->quoteName(array('a.position')));
    $a->from($db->quoteName('j3_jresearch_member_position', 'a'));
    $a->join('INNER', $db->quoteName('j3_jresearch_member', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.position') . ')');
    $a->where($db->quoteName('firstname') . " LIKE '%" . $firstname . "%'" . 'AND ' . $db->quoteName('lastname') . " LIKE '%" . $lastname . "%'");

    // Reset the query using our newly populated query object.
    $db->setQuery($a);

    // Load the results as a list of stdClass objects (see later for more options on retrieving data).
    $results = $db->loadObjectList();

    $pos = "";
    foreach($results as $row){
        $pos =  $pos . $row->position;
    }

    return $pos;
}
?>