<?php

class Database{

  public $con;
  // public $error;

  public function __construct(){
    $this->connect();
  }

  private function connect(){
    $skipInstall = getenv('BLC_SKIP_INSTALL_CHECK') === 'true';

    $useSession = !$skipInstall
      && isset($_SESSION["db_host"], $_SESSION["db_username"], $_SESSION["db_pass"], $_SESSION["db_name"])
      && $_SESSION["db_host"] !== '';

    if($useSession){
      $host = (string) $_SESSION["db_host"];
      $user = (string) $_SESSION["db_username"];
      $pass = (string) $_SESSION["db_pass"];
      $db_name = (string) $_SESSION["db_name"];
    }else{
      $host = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
      $user = defined('DB_USER') ? DB_USER : getenv('DB_USER');
      $pass = defined('DB_PASS') ? DB_PASS : getenv('DB_PASS');
      $db_name = defined('DB_NAME') ? DB_NAME : getenv('DB_NAME');
    }

    // Normalize host and port (supports "host:port" or "[ipv6]:port")
    if(function_exists('blc_parse_mysql_host')){
      $parsed = blc_parse_mysql_host((string) $host);
      $normalizedHost = $parsed['host'] ?? $host;
      $port = $parsed['port'] ?? null;
    }else{
      $normalizedHost = $host;
      $port = null;
      if(strpos($host, ':') !== false){
        [$normalizedHost, $maybePort] = explode(':', $host, 2);
        if(ctype_digit($maybePort)){
          $port = (int) $maybePort;
        }
      }
    }
    if($port === null){
      $envPort = getenv('DB_PORT');
      if($envPort !== false && ctype_digit((string) $envPort)){
        $port = (int) $envPort;
      }
    }

    $dsn = "mysql:host={$normalizedHost}";
    if($port !== null){
      $dsn .= ";port={$port}";
    }
    if(!empty($db_name)){
      $dsn .= ";dbname={$db_name}";
    }
    $dsn .= ";charset=utf8mb4";

    try{
      $this->con = new PDO(
        $dsn,
        $user,
        $pass,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci")
      );
      $this->con->exec("SET CHARACTER SET utf8mb4");
      $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $ex){
      echo "Database Connection Error Is: " . $ex->getMessage();
      $this->con = null;
    }
  }

  public function query($query,$parameters = '',$limit = ''){
    try{
      $run_query = $this->con->prepare($query);
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if(!empty($limit)){
        foreach($limit as $key => $value){ 
          $run_query->bindValue("$key", $value, PDO::PARAM_INT); 
        }
      }
      if (!empty($parameters)) {
        foreach($parameters as $key => $value){ 
          $run_query->bindValue("$key", $value); 
        }
      }
      if ($run_query->execute()) {
        return $run_query;
      } else {
        return false;
      }
    }catch(PDOException $ex){
      echo "There Is Error In : ".$ex->getMessage();
    }
  }

  public function count($table,$parameters = ""){
    $where = "";
    if(!empty($parameters)){
      $i = 1;
      $where = "Where ";
      $values = [];
      foreach($parameters as $key => $value){
        if($i > 1){$where .= " AND ";}
        $where .= "$key=:$key";
        $values[":$key"] = $value;
        $i++;
      }
    }
    try{
      $run_query = $this->con->prepare("select * from $table $where");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if(!empty($parameters)){
      $run_query->execute($values);
      }else{ $run_query->execute(); }
      return $run_query->rowCount();
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function select($table,$parameters = "",$order = ""){
    $where = "";
    $order_by = "";
    if(!empty($order)){
    $order_by = "order by 1 $order";
    }
    if(!empty($parameters)){
      $i = 1;
      $where = "Where ";
      $values = [];
      foreach($parameters as $key => $value){
        if($i > 1){$where .= " AND ";}
        $where .= "$key=:$key";
        $values[":$key"] = $value;
        $i++;
      }
    }
    try{
      $run_query = $this->con->prepare("select * from $table $where $order_by");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if(!empty($parameters)){
        if($run_query->execute($values)){ return $run_query; }
      }else{
        if($run_query->execute()){ return $run_query; }
      }
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function insert($table,$parameters = ""){
    if(!empty($parameters)){
      $i = 1;
      $count = count($parameters);
      $fields = ""; $placeholders = ""; $values = [];
      foreach($parameters as $key => $value){
        $fields .= "$key";
        $placeholders .= ":$key";
        $values[":$key"] = $value;
        if($i < $count ) { $fields .=","; $placeholders .=","; } 
        $i++;
      }
    }
    try{
      $run_query = $this->con->prepare("insert into $table ($fields) values ($placeholders)");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if($run_query->execute($values)){ return $run_query; }
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function update($table,$parameters,$where_p = ""){
    $i = 1;
    $count = count($parameters);
    $fields = ""; $values = [];
    foreach($parameters as $key => $value){
      $fields .= "$key=:$key";
      $values[":$key"] = $value;
      if($i < $count ) { $fields .=","; } 
      $i++;
    }
    $where = "";
    if(!empty($where_p)){
    $i = 1;
    $where = "where ";
    foreach($where_p as $key => $value){
      if($i > 1){$where .= " AND ";}
      $where .= "$key=:w_$key";
      $values[":w_$key"] = $value;
      $i++;
    }
    }
    try{
      $run_query = $this->con->prepare("update $table set $fields $where");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if($run_query->execute($values)){ return $run_query; }
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function delete($table,$parameters=''){
    $i = 1;
    $where = "";
    $values = [];
    if(!empty($parameters)){
    $i = 1;
    $where = "where ";
    foreach($parameters as $key => $value){
      if($i > 1){$where .= " AND ";}
      $where .= "$key=:$key";
      $values[":$key"] = $value;
      $i++;
    }
    }
    try{
      $run_query = $this->con->prepare("delete from $table $where");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      if(!empty($parameters)){
      if($run_query->execute($values)){ return $run_query; }
      }else{
        if($run_query->execute()){ return $run_query; }
      }
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function insert_log($admin_id,$work,$work_id,$status){
    $date = date("F d, Y H:i:s");
    try{
      $run_query = $this->con->prepare("insert into admin_logs (admin_id,work,work_id,date,status) values (:admin_id,:work,:work_id,:date,:status)");
      $run_query->setFetchMode(PDO:: FETCH_OBJ);
      $run_query->bindParam(':admin_id', $admin_id);
      $run_query->bindParam(':work', $work);
      $run_query->bindParam(':work_id', $work_id);
      $run_query->bindParam(':date', $date);
      $run_query->bindParam(':status', $status);
      if($run_query->execute()){ return $run_query; }
    }catch(PDOException $ex){
      echo "There Is Error In : " . $ex->getMessage();
    }
  }

  public function lastInsertId(){
    return $this->con->lastInsertId();
  }


}

$db = new database();
