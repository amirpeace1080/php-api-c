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


function getCommand($op, $value = null){
    $formData = [ 'op' => $op];
    
    switch($op){
        case 'srch' : 
        $data = [            
            'name' => 'mo'
        ];
        break;        
        case 'show' : 
        /*$data = [         
            'name' => '',
            'family' => '',
            'username' => ''            
        ];*/
        $data = [         
            'all' => '1',            
        ];
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
                'id' => 'majid'                
            ];
        break;

        case 'edit' : 
            $data = [               
                'id' => 3,
                'name' =>  'Morteza',
                'family' =>  'javadi'
            ];
        break;
        default:
            $data = [];
        }    
        $formData = array_merge($formData, $data);    
        return $formData;        
    }

    setWebserviceData(getCommand('show'));
    
  
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