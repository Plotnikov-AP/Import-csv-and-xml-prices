<?php
ignore_user_abort(1);     
set_time_limit(0);     
require_once 'config.php';
require_once 'functions.php';
require_once 'mysql_class.php';

//print_r($_POST);
//прочитаем файл конфигурации по заливаемым в MySQL полям
$pattern=pattern_read($_POST['id']);
if(isset($pattern['part_node'])){//главный тег, нужен для выделения блока из xml файла
    $main_tag=$pattern['part_node'];
    unset($pattern['part_node']);
}else{
    exit($main_tag." не найден или пустой, работа скрипта далее невозможна!!!");
}
//echo $main_tag;
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
$columns = $db->showcolumnsnotid($table);
//print_r($columns);
$count_blocks=0;
$count_insert_ok=0;
//читаем полученный xml файл
$starttag="<".$main_tag;
$endtag="</".$main_tag.">";
$descriptor = fopen($_POST['file'], 'r');                                                                                                                   
if ($descriptor){                                                                                                                                                     
	$foundstart=0;                                                                                                                                
	$foundend=0;                                                                                                                                  
	$block='';                                                                                                                                    
	while (($string = fgets($descriptor)) !== false)                                                                                              
	{                                                                                                                                             
		//1 сначала ищем начало блока                                                                                                         
		$start=preg_match('#'.$starttag.'[\s\>]+#im', $string, $out);                                                                         
		if ($start)                                                                                                                           
		{                                                                                                                                     
			$block='';                                                                                                                    
			$foundstart=1;                                                                                                              
		}                                                                                                                                     
		else                                                                                                                                  
		{                                                                                                                                     
			//2 потом идём до конца блока блока                                                                                           
			$end=preg_match('#'.$endtag.'#is', $string, $out);                                                                            
			if ($end)                                                                                                                     
			{                                                                                                                             
				$foundend=1;                                                                                                          
			}                                                                                                                             
			else                                                                                                                          
			{                                                                                                                             
				$block.=$string;                                                                                                      
			}                                                                                                                             
		}                                                                                                                                     
		if ($foundstart&&$foundend)                                                                                                           
		{
            //echo $block;
            //print_r($pattern);
            $array_to_mysql=array();
            $array_to_mysql['shop_id']=$_POST['id'];
            foreach($pattern as $key=>$value){
                if (preg_match_all('#<'.$key.'>(.*?)<\/'.$key.'>#is', $block, $out_tags)=='false'){                                                                                                         
                    //exit("Массив тегов получить не удалось, выхожу из скрипта");
                    $array_to_mysql[$value]="NULL";                                                                                           
                }else{ 
                    //print_r($out_tags);
                    //собираем общее значение, если значений несколько
                    $temp="";
                    foreach($out_tags[1] as $value_temp){
                        if($value!="photo_arr"){
                            if($temp!=""){
                                $temp.=", ".strtoupper($value_temp);
                            }else{
                                $temp.=strtoupper($value_temp);
                            }
                        }else{
                            if($temp!=""){
                                $temp.=", ".$value_temp;
                            }else{
                                $temp.=$value_temp;
                            }
                        }
                        
                    }
                    //убираем из $temp повторяющиеся значения
                    $temp=str_replace("'", "", $temp);//убираем верхние кавычки, иначе в базу не зальется!
                    $temp_array=explode(', ', $temp);//переводим в массив
                    $out_no_double=array_unique($temp_array);//убираем дубли
		            //print_r($out_no_double);
		            $out_no_double=array_values($out_no_double);//перенумеровываем с 0
		            //print_r($out_no_double);
                    $temp=implode(', ', $out_no_double);//возвращаемся к строке, но уже без повторяющихся значений     
                }
                $array_to_mysql[$value]=$temp; 
            }
            //print_r($array_to_mysql);
            foreach($columns as $value_column){
                if(!isset($array_to_mysql[$value_column])){
                    $array_to_mysql[$value_column]='NULL';
                }
            }
            //print_r($array_to_mysql);
            //exit();
            //если фото пишем, то через #
		    if (isset($array_to_mysql['photo_arr'])){
			    $array_to_mysql['photo_arr']=str_replace(', ', '#', $array_to_mysql['photo_arr']);
		    }
            if ($db->insert($table, $array_to_mysql)){
                $count_insert_ok++;
            }
            //exit();                                                                                   
			$foundstart=0;                                                                                                                
			$foundend=0;                                                                                                                  
			$block='';
            $count_blocks++;                                                                                                                    
		}                                                                                                                                     
	}                                                                                                                                             
}                                                                                                                                                     
fclose($descriptor);
$db->close();
echo "Найдено блоков: $count_blocks<br>";
echo "Успешных insert to MySQL: $count_insert_ok<br>";
echo "Успешных insert в процентах=".number_format(100*$count_insert_ok/$count_blocks, 2)."%<br>";                                           
echo 'Скрипт завершил свою работу, чего и Вам желаем!!!';
require_once 'index.php';
?>