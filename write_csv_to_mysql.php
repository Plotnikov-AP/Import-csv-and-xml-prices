<?php
ignore_user_abort(1);     
set_time_limit(0);     
require_once 'config.php';
require_once 'functions.php';
require_once 'mysql_class.php';

print_r($_POST);
//прочитаем файл конфигурации по заливаемым в MySQL полям
$pattern=pattern_read($_POST['id']);
//print_r($pattern);
$db=new DB();	//создаём бд и подключаемся к ней                    
$db->create_table($table);                                           
$columns = $db->showcolumns($table);
//print_r($columns);
//добавим в БД недостающие поля
foreach ($pattern as $value_column)
{
	$db->addcolumnwithcheck($table, $columns, $value_column);
}
//построчно читаем полученный csv файл
$descriptor = fopen($_POST['file'], 'r');                     
if ($descriptor){
	//читаем строку заголовков полученного csv файла
   	if (($current_string = fgets($descriptor))!== false){
		$current_string = iconv('windows-1251//IGNORE', 'UTF-8//IGNORE', $current_string);
		$array_columns=explode('";"', $current_string);
		//print_r($array_columns);
	}
	//читаем все оставшиеся строки
	while (($current_string = fgets($descriptor))!== false){
		$current_string = iconv('windows-1251//IGNORE', 'UTF-8//IGNORE', $current_string);
		$array=explode('";"', $current_string);
		//print_r($array);
		for ($i=0; $i<count($array); $i++){
			if (isset($pattern[trim($array_columns[$i], '"')])){
				if($array[$i]==""){
					$array[$i]="NULL";
				}
				$array_to_mysql[$pattern[trim($array_columns[$i], '"')]]=trim($array[$i], '"');
			}
		}
		$array_to_mysql['shop_id']=$_POST['id'];
		//print_r($array_to_mysql);
		//если фото пишем, то через #
		if (isset($array_to_mysql['photo_arr'])){
			$array_to_mysql['photo_arr']=str_replace(', ', '#', $array_to_mysql['photo_arr']);
		}
		//print_r($array_to_mysql);
		//exit();
		$db->insert($table, $array_to_mysql);
	}
}
$db->close();
echo 'Скрипт завершил свою работу, чего и Вам желаем!!!';
require_once 'index.php';
?>