<?php
// define("WEBSERVICE_URL", "http://localhost/practice/database.php");
define("WEBSERVICE_URL", "http://localhost/php-api-c/database.php");

function dd($str){
    echo '<pre>';
    var_dump($str);
}

function runWebService($formData = [], $debugMode = false){
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

        if($debugMode){
            dd($result);
        }        

        if(curl_errno($handle)){
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode == 404){                
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
                $resultSet['error'] = "unKnown webservice result!";
            }
        }
        curl_close($handle);        
    }
    catch(Exception $ex){        
        $resultSet['error'] = $ex->getMessage();
    }
    return $resultSet;
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


function getCommand($user = null, $pass = null, $op = null, $value = null){
    $formData = [ 
        'op' => $op,
        'user' => $user,
        'pass' => $pass
    ];
    
    // $all = 1;
    // $name = 'd3';
    $family = 'javadi';
    // $username = 'monline';
    $data = [];
    switch($op){
        case 'srch' : 
        $data = [            
            'search' => 'mo'
        ];
        break;        
        case 'show' :        
            if(isset($name)){
                $data['name'] = $name;
            }
            if(isset($family)){
                $data['family'] = $family;
            }
            if(isset($username)){
                $data['username'] = $username;
            }
            if(isset($all)){
                $data = ['all' => '1'];
            }        
        break;        
        case 'create' : 
            $data = [              
                'name' => 'majid',
                'family' => 'moradi',
                'username' => 'monline',
                'password' => md5('123456')
            ];
        break;

        case 'del' : 
            $data = [                
                'id' => '2'                
            ];
        break;

        case 'edit' : 
            $data = [               
                'id' => 3,
                'name' =>  'jafar',
                'family' =>  'razavi'
            ];
        break;
        default:
            $data = [];
        }    
        $formData = array_merge($formData, $data);    
        return $formData;        
 }

    // $username = "ali";
    // $username = "bahar";
    // $username = "reza";
    $username = "admin";
    $password = md5('123456');

    // setWebserviceData(getCommand($username, $password, 'show'));
    // setWebserviceData(getCommand($username, $password, 'srch'));
    // setWebserviceData(getCommand($username, $password, 'edit'));
    //  setWebserviceData(getCommand($username, $password, 'del'));
     setWebserviceData(getCommand($username, $password, 'create'));
    // setWebserviceData(getCommand('create'));
    // setWebserviceData(getCommand('edit'));
    // setWebserviceData(getCommand('del'));
    // setWebserviceData(getCommand());
    
  
    /*
    for($i=0; $i<10; $i++){    
        $formData = [
            'op' => 'create',
            'name' => 'majid' . $i,
            'family' => 'moradi' . $i,
            'username' => 'monline' . $i ,
            'password' => md5('123456')
        ];
        setWebserviceData($formData);
    }
    */
    




// getWebserviceData($formData);
//  setWebserviceData($formData);
// ini_set('display_error', 0);
// error_reporting(0)
?>