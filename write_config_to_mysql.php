<?php
ignore_user_abort(1);     
set_time_limit(0);        
//файл конфигурации       
require_once 'config.php';
require_once 'mysql_class.php';

//Получим переданные нам данные
if (count($_POST)>0)
{
	$post=$_POST;
	//print_r($post);
	//избавимся от пустых значений
	foreach ($post as $key=>$value){
		if ($value==""){
			unset($post[$key]);
		}
	}
	$id=$post['id'];
	unset($post['id']);
	if (isset($post['xml'])){//если xml то данные перед записью нужно перевернуть key=>$value в $value=>$key
		echo "Данные xml";
		$temp=$post['part_node'];
		unset($post['part_node']);
		unset($post['xml']);
		$post=array_flip($post);
	}
	$post['part_node']=$temp;
	//переводим полученный массив в json
	$json=json_encode($post);
	//echo $json;
	//exit();
	//пишем полученный файл в MySQL
	$db=new DB();	//создаём бд и подключаемся к ней
	$db->pattern_write($id, $json);
	require_once 'index.php';
 }
else
{
	echo "Произошла ошибка при получении данных конфигурации, скрипт завершает работу!!!";
	exit();
}
?>