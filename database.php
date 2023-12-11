<?php
namespace person;
header("Content-type: application/json; charset=utf-8");

use Exception;
use mysqli;

class Db{    
    private $db_name;
    private $server_name;
    private $userName;
    private $password;
    private $connection;

    public function __construct()
    {
        if (file_exists('config.php')){
            require_once('config.php');        
            $this->db_name = $dbName;
            $this->server_name = $host;
            $this->userName = $dbuser;
            $this->password = $dbpass;
            $this->dbConnect();
        }
    }

    public function dbConnect(){
        try{
            $connect = mysqli_connect($this->server_name, $this->userName, $this->password,  $this->db_name);
            if (mysqli_connect_error()){
                //die(mysqli_error($connect));                    
                // add something in log file
                return 0;
            } else {
                $this->connection = $connect;                
                return $connect;
            }            
        }
        catch(Exception $ex){
            // add something in log file  $ex.getmessage();            
            return 0;
        }
    }

    public function createTable(){
        $resultset = [];
        $mySqlString = "
            CREATE TABLE IF NOT EXISTS person (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                family VARCHAR(255),
                username VARCHAR(25) NOT NULL,
                password VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        $result = mysqli_query($this->connection, $mySqlString);

        if ($result === TRUE){            
            $resultset['msg'] = "Table created successfully";
            
        } else {
            $resultset['error']['insert'] = mysqli_error($this->connection);
        }
      }

    public function inserrtDb($tableName, $data){        
        $resultset = [];
        $mySqlString = "INSERT INTO " . $tableName . " VALUES(null,'" . $data['name'] . "','" . $data['family'] . "', '" . $data['username'] . "', '" . $data['password'] . "', null, null)";
        // var_dump($mySqlString);
        $result = mysqli_query($this->connection, $mySqlString);
        if (!$result){            
            $resultset['error']['insert'] = mysqli_error($this->connection);
        } else {
            $resultset['msg'] = "One Record inserted";
        }
        return json_encode($resultset);
    }

    public function updateDb($tableName, $updateData, $whereClause){
        $resultset = [];
        $whereClause = " WHERE " . implode(' AND ' , $whereClause);
        $setClause = " SET " . implode(',' , $updateData);

        $sqlQuery = " UPDATE " . $tableName . 
        $setClause . 
        $whereClause;        
        // die($sqlQuery);

        $result = mysqli_query($this->connection, $sqlQuery);    
        if (!$result){
            $resultset['error'] = mysqli_error($this->connection);
        } else {
            $resultset['msg'] = mysqli_affected_rows($this->connection) . " Records updated <br/>";
        }
        return $resultset;
    }

    public function deleteDb($tableName, $whereClause = ["1!=1"]){
        $resultset = [];
        $whereClause = " WHERE " . implode(' AND ' , $whereClause);
        $sqlQuery = " DELETE FROM  " . $tableName
        . $whereClause;        
        
        $result = mysqli_query($this->connection, $sqlQuery);
        if (!$result){            
            $resultset['error']  = mysqli_error($this->connection);
        } else {
            $resultset['msg']  =  mysqli_affected_rows($this->connection) . " Records deleted <br/>";
        }
        return $resultset;
    }

    public function getDataTable($tableName, $whereClause = null){
        // print_r($whereClause);        
        // var_dump($whereClause);
        $resultset = [];
        if ($whereClause){
            $whereClause = " WHERE " . implode(' AND ' , $whereClause);
        } else {
            $whereClause = " WHERE 1";
        }
        
        $sqlQuery = " SELECT * FROM " . $tableName 
        . $whereClause
        . " ORDER BY id ASC
        ";
        // die($sqlQuery);
        try{
            $result = mysqli_query($this->connection, $sqlQuery);            
            if (!$result){            
                $resultset['error'] = mysqli_error($this->connection);            
            } else {                
                if ($result->num_rows){
                    $resultset['result'] = $result;           
                }
                else {
                    $resultset['result'] = 0;           
                }
            } 
        }catch(Exception $ex){        
            $resultset['error']['dberror'] = $ex->getMessage();
        }         
        return $resultset;
    }

    public function showTable($tableName) {
        $result = $this->getDataTable($tableName);
        echo mysqli_num_rows($result);
        while($row = mysqli_fetch_array($result)){ //while($row = mysqli_fetch_array($result)){
            echo $row['id'] . ' - ' . $row['name'] . ' - ' . $row['family'] . ' - ' . $row['username'] . '<br/>';
            //echo $row['id'] . ' - '  . '<br/>';
        }
        return $result;
    }
    
    public function getApiData($tableName, $whereClause = null){          
        $res = $this->getDataTable($tableName, $whereClause);        
        if (isset($res['result']) && $res['result']){            
            $arrayResult['data'] = mysqli_fetch_all($res['result']);    
        }
        else {
            if (isset($res['result']) &&  $res['result'] === 0){
                $arrayResult['data'] = 0;
            } 
            else {
                $arrayResult['error'] = $res['error'];
            }
        }        
        return json_encode($arrayResult);
    }
    
    public function __destruct()
    {
        $this->dbClose();
    }

    public function dbClose(){
        mysqli_close($this->connection);
        $this->connection = null;
    }
}

$tblName = "person";
$dbObj = new Db();

// echo json_encode($dbObj->createTable());

/*
for($i=0; $i<10; $i++){
    $data = [
        'name' => 'Mosab' . $i,
        'family' => 'sharifi' . $i,
        'username' => 'moh' . $i,
        'password' => md5('987654')    
    ];    
    echo json_encode($dbObj->inserrtDb($tblName, $data));
}
*/

// $dbObj->showTable($tblName);

// $a = hash("sha256", '123456');
// var_dump($a);

// if (md5('123456') == 'e10adc3949ba59abbe56e057f20f883e'){
//     echo "OK";
// }

function validateInputs($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

$whereClause = [];
$updateData = [];
$createData = [];
$delData = [];



if (isset($_POST['op']) && $_POST['op'] == 'edit' && isset($_POST['id'])){
    // echo "Edit Mode";
    if (isset($_POST['name'])){
        $updateData[] = "name = " . "'" . validateInputs($_POST['name']) . "'";
    }
    if (isset($_POST['family'])){
        $updateData[] = "family = " . "'" . validateInputs($_POST['family']) . "'";
    }
    if (intval($_POST['id'])){
        $whereClause[] = "id=" .  intval($_POST['id']); 
    }
    // filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
} 
else if (isset($_POST['op']) && $_POST['op'] == 'srch' && isset($_POST['search']) && !empty($_POST['search'])){        
    $whereClause[] = "username like " . "'%" . validateInputs($_POST['search']) . "%'";    
}
else if(isset($_POST['op']) && $_POST['op'] == 'del' && isset($_POST['id'])){
    if (intval($_POST['id'])){
        $delData[] = "id=" .  intval($_POST['id']);
    }
}
else if(isset($_POST['op']) && $_POST['op'] == 'show'){
    if (isset($_POST['name'])){
        $whereClause[] = "name  " . "like '%" . validateInputs($_POST['name']) . "%'";
    }
    if (isset($_POST['family'])){
        $whereClause[] = "family = " . "'" . validateInputs($_POST['family']) . "'";    
    }
    if (isset($_POST['username'])){
        $whereClause[] = "username = " . "'" . validateInputs($_POST['username']) . "'";    
    }
    if (isset($_POST['password'])){
        $whereClause[] = "password = " . "'" . md5(validateInputs($_POST['password'])) . "'";    
    }
} 
else if (isset($_POST['op']) && $_POST['op'] == 'create' && (    
    isset($_POST['name']) && 
    isset($_POST['family']) && 
    isset($_POST['username']) &&     
    isset($_POST['password'])
    )
    ){  
        $createData['name'] = validateInputs($_POST['name']);    
        $createData['family'] = validateInputs($_POST['family']);    
        $createData['username'] = validateInputs($_POST['username']);    
        $createData['password'] = validateInputs($_POST['password']);    
} 
else {
    echo json_encode(['msg' => "Command Not Found!!!"]);
}

// $dbObj->getDataTable($tblName, $whereClause); 

if (count($updateData)){
    echo $dbObj->updateDb($tblName, $updateData, $whereClause);
    // echo json_encode($dbObj->updateDb($tblName, $updateData, $whereClause));
}
else if (count($whereClause)){
    // return $dbObj->getApiData($tblName, $whereClause);
    // echo json_encode($dbObj->getApiData($tblName, $whereClause));
    echo $dbObj->getApiData($tblName, $whereClause);
} 
else if (count($delData)){    
    echo $dbObj->deleteDb($tblName, $delData);
    // echo json_encode($dbObj->deleteDb($tblName, $delData));
}
else if (count($createData)){
    // echo json_encode($dbObj->inserrtDb($tblName, $createData));
    echo $dbObj->inserrtDb($tblName, $createData);
}