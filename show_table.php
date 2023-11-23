<?php
require_once 'config.php';
require_once 'functions.php';
function getExtension($filename)
{
    $file = new SplFileInfo($filename); 
    return $file->getExtension();
}      

// получаем доступные поля таблицы mysql
$db = new mysqli($hostmysql, $user, $password, $database);
	if($db->connect_error){
		die("Ошибка: " . $db->connect_error);
}
$db->set_charset ('UTF8');
$option_list="";
$sql_columns="SHOW FULL COLUMNS FROM  `part_import`";
if($result = $db->query($sql_columns)){
	//print_r($result);
	
	foreach($result as $row){
		//print_r($row);
		if($row['Field']!='id'&&$row['Field']!='shop_id') $option_list.="<option value='".$row['Field']."'>".$row['Field']." (".$row['Comment'].")</option>";
	}
}else die('Error :' .  $result->error);                                               

$file_in=$_GET['file'];
//если файл соответствия уже есть, то считываем из него данные
$pattern=pattern_read($_GET['id']);
//print_r($pattern);
//найдем расширение файла для импорта
$extension=getExtension($file_in);
if ($extension=="csv")
{
    $descriptor = fopen($file_in, 'r');                     
    if ($descriptor)                                        
    {
        $array_for_prezent=array();
        for ($i=0; $i<=$count_for_prezent; $i++){
	        $current_string=fgets($descriptor);
	        $current_string = iconv('windows-1251//IGNORE', 'UTF-8//IGNORE', $current_string);
	        //echo $current_string;
	        $array=explode(";", $current_string);
	        for ($j=0; $j<count($array); $j++){
	        	$array_for_prezent[$i][$j]=trim($array[$j], '"');
		       }
	    }
		//print_r($array_for_prezent);
	    //сформируем табличку с полученными данными                                  
	    $table='<table collspawn="0" cellspawn="0" border="1" bordercolor="0000CD">';
	    for ($j=0; $j<count($array_for_prezent[0]); $j++){
		    $str_from_csv="";
		    for ($i=0; $i<=$count_for_prezent; $i++){
			    $str_from_csv.='<td>'.$array_for_prezent[$i][$j].'</td>';
			    //print_r($array_value);
		    }
			$table.='<tr>';
			if(isset($pattern[$array_for_prezent[0][$j]])){
				$search="value='".$pattern[$array_for_prezent[0][$j]]."'>";
				$replace="value='".$pattern[$array_for_prezent[0][$j]]."' selected>";
				$select_option=$option_list;
				$select_option=str_replace($search, $replace, $select_option);
				$table.='<td>'.$j.'</td>'.$str_from_csv.'<td><select name="'.$array_for_prezent[0][$j].'"><option value="">Выберите имя поля в БД</option>'.$select_option.'</select></td>';
			}
			else{
				$table.='<td>'.$j.'</td>'.$str_from_csv.'<td>'.'<select name="'.$array_for_prezent[0][$j].'"><option value="">Выберите имя поля в БД</option>'.$option_list.'</select></td>';
		        $table.='</tr>';
			}
	    }
	    $table.='</table>';
	    $post='<p><form action="write_config_to_mysql.php" method="post"></p>';
	    $post.='<p>'.$table.'</p>';
	    $post.='<p><input type="hidden" name="id" value="'.$_GET['id'].'"></p>';
	    $post.='<p><input type="submit" value="Записать полученные данные в конфигурационный файл"></p>';
	    $post.='</form>';
	    echo $post;
		//запуск импорта данных
		$import='<p><form action="write_csv_to_mysql.php" method="post"></p>';
		$import.='<p><input type="hidden" name="id" value="'.$_GET['id'].'"></p>';
		$import.='<p><input type="hidden" name="file" value="'.$_GET['file'].'"></p>';
		$import.='<p><input type="submit" value="Записать данные в MySQL"></p>';
	    $import.='</form>';
	    echo $import;
    }
    fclose($descriptor);  
} else if ($extension=="xml"){
	if(isset($pattern['part_node'])){//главный тег, нужен для выделения блока из xml файла
		$main_tag=$pattern['part_node'];
		unset($pattern['part_node']);
	}else{
		$main_tag="";
	}
	if($pattern){
		$pattern=array_flip($pattern);//если xml то данные нужно перевернуть key=>$value в $value=>$key
		//print_r($pattern);
	}
	$db = new mysqli($hostmysql, $user, $password, $database);
	if($db->connect_error){
		die("Ошибка: " . $db->connect_error);
	}

	$sql = "SELECT * FROM  shop_list WHERE id=".$_GET['id']." LIMIT 1";
	//echo $sql;

	if($result = $db->query($sql)){ $row = $result->fetch_assoc();	
	//print_r($row);
	$file_in=$row['price_url'];
	echo "работаем с прайсом $file_in";
	$html=file_get_contents($file_in);
	$html_portion=substr($html, 0, 5000);

	}else die('Error :' .  $result->error);

	?><br>
	<textarea rows="30" cols="145"><? echo $html_portion?></textarea><br><br>
	<?
	// выводим табличку для сопоставления полей
	?>
	Таблица для сопоставления полей xml и базы данных
	<?
	$option_list="<option value=''>Выберите нужное поле</option>";
	if (preg_match_all('#<(?<tag>.*?)><?[^<]*<\/\k<tag>#is', $html_portion, $out_tags)=='false'){                                                                                                         
		exit("Массив тегов получить не удалось, выхожу из скрипта");                                                                                           
	}else{                                                                                                         
		//print_r($out_tags[1]);
		//уберем дубли из массива
		$out_no_double=array_unique($out_tags[1]);//убираем дубли
		//print_r($out_no_double);
		$out_no_double=array_values($out_no_double);//перенумеровываем с 0
		//print_r($out_no_double);                                            
		foreach ($out_no_double as $value){
			$option_list.="<option value='".$value."'>".$value."</option>";
		}
	}                                                                                                         

	$db->set_charset ('UTF8');
	$sql_columns="SHOW FULL COLUMNS FROM  part_import";
	if($result = $db->query($sql_columns)){
		$table="<table border='1'>";
		//print_r($result);
		
		foreach($result as $row){
			if(isset($pattern[$row['Field']])){
				$search="value='".$pattern[$row['Field']]."'>";
				$replace="value='".$pattern[$row['Field']]."' selected>";
				$select_option=$option_list;
				$select_option=str_replace($search, $replace, $select_option);
				if($row['Field']!='id'&&$row['Field']!='shop_id') $table.="<tr><td>".$row['Field']."(".$row['Comment'].")</td><td><select name='".$row['Field']."'>".$select_option."</select></td></tr>";
			}else{
				if($row['Field']!='id'&&$row['Field']!='shop_id') $table.="<tr><td>".$row['Field']."(".$row['Comment'].")</td><td><select name='".$row['Field']."'>".$option_list."</select></td></tr>";
			}
			
		}
		$table.='</table>';
	    $post='<p><form action="write_config_to_mysql.php" method="post"></p>';
		$post.='<p>Тег запчасти: <input required type="text" name="part_node" value="'.$main_tag.'"/></p>';
	    $post.='<p>'.$table.'</p>';
	    $post.='<p><input type="hidden" name="id" value="'.$_GET['id'].'"></p>';
		$post.='<p><input type="hidden" name="xml" value="xml"></p>';
	    $post.='<p><input type="submit" value="Записать полученные данные в конфигурационный файл"></p>';
	    $post.='</form>';
	    echo $post;
		//запуск импорта данных
		$import='<form action="write_xml_to_mysql.php" method="post">';
		$import.='<p><input type="hidden" name="id" value="'.$_GET['id'].'"></p>';
		$import.='<p><input type="hidden" name="file" value="'.$_GET['file'].'"></p>';
		$import.='<p><input type="submit" value="Записать данные в MySQL"></p>';
	    $import.='</form>';
	    echo $import;
	}	
}
	
 ?>