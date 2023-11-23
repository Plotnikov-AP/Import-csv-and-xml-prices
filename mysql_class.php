<?php

class DB                                                                                                                
{                                                                                                                       
	private $mysqli;                                                                                                
                                                                                                                        
	public function __construct()                                                                                   
	{                                                                                                               
		global $hostmysql, $user, $password, $database;                                                         
		//Подключение к серверу MySQL                                                                           
		$this->mysqli = new mysqli($hostmysql, $user, $password);                                               
		if($this->mysqli)                                                                                       
		{                                                                                                       
			//подключились успешно!                                                                         
			$this->mysqli->set_charset('utf8'); //установим кодовую страницу при записи в MySQL             
			$this->mysqli->select_db($database);                                                            
		}                                                                                                       
		else                                                                                                    
		{                                                                                                       
			printf("Невозможно подключиться к серверу баз данных. Код ошибки: %s\n",$this-> mysqli_error());
		}                                                                                                       
	}

	public function create_table($tablename)
	{
		global $database;
		//Проверим есть ли БД на сервере, если нет, то создадим                                   
		$this->mysqli->query("CREATE DATABASE IF NOT EXISTS $database"); //создаём базу, если её ещё нет
		$this->mysqli->select_db($database);
		$this->mysqli->query("CREATE TABLE IF NOT EXISTS $tablename (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY)"); //создаём таблицу, если её ещё нет                                                            
	}

	public function showcolumns($table)                                
	{                                                            
		$result = $this->mysqli->query("SHOW COLUMNS FROM $table");
		$columns = array();                                  
		while($row = $result->fetch_object())                
		{                                                    
			$columns[] = $row->Field;                    
		}
		if (count($columns)>0)
		{
			return $columns;
		}
		else
		{
			return false;
		}
	}

	public function showcolumnsnotid($table)                          
	{                                                            
		$result = $this->mysqli->query("SHOW COLUMNS FROM $table");
		$columns = array();                                  
		while($row = $result->fetch_object())                
		{
			if ($row->Field!='id')
			{                                                    
				$columns[] = $row->Field;
			}                    
		}                                                    
		if (count($columns)>0)                               
		{                                                    
			return $columns;                             
		}                                                    
		else                                                 
		{                                                    
			return false;                                
		}                                                    
	}

	public function addcolumn($table, $namecolumn)                                                                                                                            
	{                                                                                                     
		$this->mysqli->query("ALTER TABLE $table ADD COLUMN `$namecolumn` text"); //добавляем нужную колонку
	}

	public function addcolumnwithcheck($table, $columns, $namecolumn)                                                                                                     
        {                                                                                                  
        	if (!in_array($namecolumn, $columns))   
        	{                                      
        		$this->addcolumn($table, $namecolumn); 
        		return true;                   
        	}                                      
        	else                                   
        	{                                      
        		return false;                  
        	}                                      
        }

	public function insert($table, $array)
	{
		global $create_log_files, $file_err;                                           
		$fields = "`".implode('`, `', array_keys($array))."`";                                    
		$values = "'".implode("', '", $array)."'";                                                
		$sql = "INSERT INTO $table ($fields) VALUES ($values)";
		//echo "$sql<br>";
		$res=$this->mysqli->query($sql);                                                              
		if ($res) return true;                                                                  
		else                                                                                    
		{                                                                                       
			echo "$sql<br>";
			return false;                                                                   
		}                                                                                       
	}

	public function check_id($table, $id)                                        
	{
		$sql="SELECT `Stock Id` FROM $table WHERE `Stock Id`=$id";             
		$result=$this->mysqli->query($sql); //проверяем, нет ли такого id??? 
		$row = $result->fetch_array();                            
		if (isset($row[0]))                                            
		{                                                              
			return false;                                          
		}                                                              
		else                                                                                                  
		{                                                              
			return true;                                           
		}
	}
	
	public function select($sql)
	{
		$result=$this->mysqli->query($sql); //выполняем переданный нам запрос
		$row = $result->fetch_array();
		print_r($row);
	
	}

	public function pattern_read($id){
		$sql="SELECT `config_fields` FROM `shop_list` WHERE `id`=$id";
		$result=$this->mysqli->query($sql); //читаем из БД
		return $result->fetch_array();		
    }

	public function pattern_write($id, $json){
		$sql="UPDATE `shop_list` SET `config_fields` = '".$json."' WHERE `id` = ".$id;
		$result=$this->mysqli->query($sql); //пишем в БД
		if ($result)                                            
		{                                                              
			return false;                                          
		}                                                              
		else                                                                                                  
		{                                                              
			return true;                                           
		}
    }

	public function close()             
	{                                   
		mysqli_close($this->mysqli);
	}                                   
}

?>                                                                                                                        