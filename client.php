<?php
define("WEBSERVICE_URL", "http://localhost/practice/database.php");
// define("WEBSERVICE_URL", "http://localhost/php-api-c/database.php");
function dd($str){
    echo '<pre>';
    var_dump($str);
}

function runWebService($formData = []){
    $resultSet = [];
    try{
        $handle = curl_init();        
        $queryData = http_build_query($formData,'', '&');    
        $url = WEBSERVICE_URL;
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_TRANSFERTEXT, true);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);  
        curl_setopt($handle, CURLOPT_POST, TRUE);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $queryData);
        curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );      
        
        $result = curl_exec($handle);       
        dd($result);

        if(curl_errno($handle)){
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode == 404){            
                echo "Your URL does not exist";
                $resultSet['error'] = "Your URL does not exist";
            } else {
                $resultSet['error'] = "Known error!";
            }            
        } else {
            $res = json_decode($result, true);
            
            if (isset($res['data']) && $res['data']){
                $resultSet['data'] = $res['data'];
            }            
            else if (isset($res['error'])  && $res['error']){
                $resultSet['error'] = $res['error'];
            }
            else if (isset($res['msg']) && $res['msg']){
                $resultSet['msg'] = $res['msg'];
            }
            else if (isset($res['data'])  && $res['data'] === 0){
                $resultSet['msg'] = 'record not found';
            }
            else {
                $resultSet['error'] = "Known webservice result!";
            }
        }
        curl_close($handle);        
    }
    catch(Exception $ex){        
        $resultSet['error'] = $ex->getMessage();
    }
    return $resultSet;
}

function getWebserviceData($formData){      
    $resultSet = runWebService($formData);
    if (isset($resultSet['data']) && $resultSet['data']){
        foreach($resultSet['data'] as $data){
            echo "<br>";
            print_r($data);
        }             
    }
    else if (isset($resultSet['error']) && $resultSet['error']){
        if (isset($resultSet['error']['dberror']) && $resultSet['error']['dberror'])
        {
            echo "Database Error!!!";
        }
        else{
            echo $resultSet['error'];
        }        
    } 
    else {
        echo $resultSet['msg'];
    }
}

function setWebserviceData($formData){
    $resultSet = runWebService($formData);
    if (isset($resultSet['data']) && $resultSet['data']){        
        print_r($resultSet['data']);                  
    }
    else if (isset($resultSet['error']) && $resultSet['error']){
        if (isset($resultSet['error']['dberror']) && $resultSet['error']['dberror'])
        {
            echo "Database Error!!!";
        }
        else{
            echo $resultSet['error'];
        }        
    } 
    else {
        echo $resultSet['msg'];
    }
}


function getSearch($op, $value){
    $formData = [
        'op' => $op,
        'search' => $value
    ];
    return $formData;
}

$formData = [
    'op' => 'show',
    'name' => 'mo'
];

$formData = [
    'op' => 'create',
    'name' => 'majid',
    'family' => 'moradi',
    'username' => 'monline',
    'password' => md5('123456')
];



// getWebserviceData($formData);
setWebserviceData($formData);
// ini_set('display_error', 0);
// error_reporting(0)
?>