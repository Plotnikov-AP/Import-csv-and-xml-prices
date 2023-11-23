<?

require_once 'config.php';

$db = new mysqli($hostmysql, $user, $password, $database);
	if($db->connect_error){
		die("Ошибка: " . $db->connect_error);
}

if(isset($_POST['addshop'])){
	
	$query_addshop="INSERT INTO `shop_list` (`name`, `address`,  `descr`, `phone`, `email`, `site`, `price_url`, `date_update`) VALUES ('$_POST[namefirm]', '$_POST[address]', '$_POST[descr]', '$_POST[phone]', '$_POST[email]', '$_POST[sitename]', '$_POST[url_file]', NOW())";
	//echo $query_addshop;
	
	if($db->query($query_addshop)){
		echo "Данные успешно добавлены";
	} else{
		echo "Ошибка: " . $db->error;
	}
}
?>
<h2>Автомагазины<h2>
<table border="1" cellpadding="5">
<tr><th>id</th><th>Name</th><th>Price URL</th><th>Конфиг</th><th>Записей в бд</th><th></th></tr>
<?
$itogo=0;
$sql = "SELECT * FROM shop_list WHERE 1 ORDER BY id DESC";
if($result = $db->query($sql)){
    foreach($result as $row){

		if($row['config_fields']!='') $config='+'; 		else $config='-';
        $count_temp=$db->query("SELECT count(id) FROM part_import WHERE shop_id=".$row['id']." LIMIT 1");
        foreach($count_temp as $row_temp){
          $count=$row_temp['count(id)'];
        }
        echo "<tr><td>".$row['id']."</td><td>".$row['name']."</td><td>".$row['price_url']."</td><td>".$config."</td><td>".$count."</td><td><a href='show_table.php?file=".$row['price_url']."&pattern=".$row['config_fields']."&id=".$row['id']."'>импортировать</a></td></tr>";
		$itogo+=$count;
    }
}
?>
<tr><td></td><td></td><td></td><td>Итого</td><td><?=$itogo?></td><td></td></tr>
</table>
<h3>Добавить магазин</h3>
<form method="post" action="">
  <div class="form-group">
    <label for="namefirm">Название компании *</label>
    <input type="text" class="form-control" name="namefirm" id="namefirm" required="required" placeholder="Название компании" value="">
  </div>
<?/* <div class="form-group">
    <label for="phone">Телефоны для связи *</label>
    <textarea rows="3" class="form-control" name="phone" id="phone" required="required" placeholder="Указывайте каждый телефон с новой строки. Каждый телефон должен быть с кодом города. Телефоны будут показываться на странице товара. Ссылки на сайты запрещены."></textarea>
  </div> 
  <div class="form-group">
    <label for="sendemail">Email</label>
    <input type="email" class="form-control" name="sendemail" id="sendemail" placeholder="Укажите, если желаете получать заявки на email" value="">
  </div>  
  <div class="form-group">
    <label for="descr">Информация о компании</label>
    <textarea rows="3" class="form-control" name="descr" id="descr" placeholder="Будет показываться на странице товара. Ссылки на сайты и контактные данные запрещены."></textarea>
  </div>  
  <div class="form-group">
    <label for="adress">Адрес компании</label>
    <textarea rows="3" class="form-control" name="address" id="address" placeholder="Будет показываться в на странице товара."></textarea>
  </div>
   <div class="form-group">
    <label for="adress">Сайт</label>
    <input type="text" class="form-control" name="sitename" id="sitename" required="required" placeholder="Сайт компании" value="">
  </div>
*/?>  
  <div class="form-group">
    <label for="url_file">URL адрес прайс-листа</label>
    <input type="text" class="form-control" name="url_file" id="url_file" placeholder="Укажите url адрес где находится Ваш прайс-лист" value="">
  </div>
  <button type="submit" name="addshop" value="addshop" class="btn btn-primary">Отправить</button>
</form>

<?
	$db->close();
?>