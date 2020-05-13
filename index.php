<?php 
// dcms/modules/page/page.php


Class page {
	function constructor() //Самописный конструктор
	{
		function ierarh_option($p,$par,$curent,$step){
			$t='';
			foreach($p as $i)	{
				if ($i->parent != $par) continue;
				if ($i->id == $curent) { 	$t .= '<option value='.$i->id.' selected >+'.$step.$i->name.'</option>';		}
				else { $t .= '<option value='.$i->id.'>&nbsp;'.$step.$i->name.'</option>';	} 
				$t .= ierarh_option($p,$i->id, $curent,$step.'&nbsp;&nbsp;&nbsp;&nbsp;');
			}
			return $t;
		}
		if ($tree = $this->db->select("pages", "sost = '0' ORDER BY sort ASC"))
		{
			echo '<div class="settings ucol">';
			if (isset($_GET['page'])) 
			{
				if ($page = $this->db->select("pages", "id = '".$_GET['page']."'"))
				{
					
					$all_page = '';
					if ($all_pager = $this->db->select("pages","id>0 ORDER BY sort ASC","id, name, path, parent"))
					{
						$all_page = ierarh_option($all_pager,0,$page[0]->parent,"");
						/*$coun_pg = count($item); 
						foreach($item as $i)
						{
							if ($i->id == $page[0]->parent) { 
								$all_page .= '<option value='.$i->id.' selected >'.$i->name.' [/'.$i->path.']</option>';
							}
							else { 
								$all_page .= '<option value='.$i->id.'>'.$i->name.' [/'.$i->path.']</option>';
							} 
						}*/
					}
					
					if (!isset($page[0]->sub_design)){
						//установка подмакетов
						$sql_q_ins = "ALTER TABLE  `pages` ADD  `sub_design` INT NOT NULL;";
						mysql_query($sql_q_ins);	
						$page = $this->db->select("pages", "id = '".$_GET['page']."'");	
					}
					if ($ms = $this->db->select("designes"))
					{
						$makets='';
						$sub_makets='';
						if ($page[0]->sub_design == 0) $sub_makets= '<option value="0">Наследовать</option>';
						foreach($ms as $m)
						{
							if ($m->id == $page[0]->design) { 
								$makets .= '<option value='.$m->id.' selected >'.$m->name.'</option>';
							}
							else { 
								$makets .= '<option value='.$m->id.'>'.$m->name.' </option>';
							} 
							
							if ($m->id == $page[0]->sub_design) { 
								$sub_makets .= '<option value='.$m->id.' selected >'.$m->name.'</option>';
							}
							else { 
								$sub_makets .= '<option value='.$m->id.'>'.$m->name.' </option>';
							} 
						}
					}
				
					echo $this->func->page_settings($this, $page[0], $this->url,$all_page,$makets,$sub_makets);
				}
				else
				{
					echo $this->func->page_not_found();
				}
			} else {
				echo '<div class="block">
						<h2>Информация о сайте</h2>
					</div>
					<div class="block">
						
						<b>Домен: </b> '.$this->url.'<br />
						<b>Размер сайта:</b> '.$this->func->format_size(4545728+$this->func->foldersize('../uploads')).'<br />
						<b>Разработка: </b> <a target="_blank" href="http://dathim.ru">Студия Dathim</a><br />
					</div>
					<div class="block">
						<h2>Информация о разработчике</h2>
						<b>Архангельск: </b> <br />
						Таран Андрей<br />  +7 902 507-37-48,<br />  dathim@gmail.com <br /><br />
						<b>Астрахань: </b> <br />
						Костров Пётр <br />+7 988 177-87-08,<br />  creatoff@gmail.com<br />
					</div>
					<div class="block">
						
					</div>
					<div class="block">
						<h2>Информация о гарантии</h2>
					</div>
					<div class="block">
						1 Срок гарантийных обязательств 1 год, с момента начала разработки сайта.<br />
						2 Для клиента могут быть сделаны по запросу: статистика посещаемасти, Бекап сайта не чаще чем раз в 2 месяца, восстановление паролей, возврат к резервной копии, предоставление расширенной функциональности (на свой страх и риск).<br />
						3 Заказчик самостоятельно отвечает за информацию публикуемую на сайте.<br />
						4 Исполнитель не несет ответственности за качество каналов связи общего пользования, посредством которых осуществляется доступ к Услугам.<br />
						
					<br />
					</div>
					';	
			}			
			echo '		</div>
					<div class="tree ucol"><ul>'. $this->func->recurs($tree,0,$this->url).'</ul></div>
					
					<div class="content ucol">
						<div class="block">
			';
		}
	}

	function destructor() 
	{
		echo '</div></div></div>';
	}
		
	function index(){
		if (isset($_GET['page'])) {
			if ($this_page = $this->db->select("pages","id='".$_GET['page']."'")){
				$endpath = $this_page[0]->path;
				$parent = $this_page[0]->parent;
				if ($this_page[0]->id!=1) {
					for(;;){
						$q = $this->db->select("pages","id='".$parent."'");
						if ($parent==1) break;
						$endpath = $q[0]->path.'/'.$endpath;
						$parent = $q[0]->parent;
					}
					$path_tp = $this->url.$endpath;
				} 
				else $path_tp = $this->url;
				echo '<h2 rel="tooltip" data-original-title="Перейти на страницу" data-placement="bottom"><a target="_blank" href="'.$path_tp.'">'.$this_page[0]->name.'</a></h2>';
			if (!isset($_GET['edit'])){ // обычный режим
					if ($page_item = $this->db->select("pages_items","parent='".$_GET['page']."' AND design='".$this_page[0]->design."' ORDER BY sort ASC"))
					{
						foreach($page_item as $pi)
						{
							if($pi->komp == 0) //одинчка
							{
								echo $this->func->page_edit_text($pi,$this->url);
							}
							else
							{
								echo $this->func->page_edit_com($pi,$this);
							}
						}
					}
					echo '<a href="'. $this->url .'dcms/page?page='.$_GET["page"].'&edit=1">Изменить тип материала</a>';
				}
			else { //режим "править элементы"
						echo '<div class="tools">
								<a href="'. $this->url .'dcms/page?page='.$_GET["page"].'">Страница</a>
								<a href="'. $this->url .'dcms/page/c_add_new_pi?id='.$_GET['page'].'">Добавить</a>
								<a href="javascript:go(\''. $this->url .'dcms/page/c_del_p_i?id='.$_GET['page'].'\')">Удалить всё</a>
							  </div>';
						$page = $this->db->select("pages","id='".$_GET['page']."'");
							  
						echo '<div class="table" id="sortable_pages" class="table ui-sortable">';
						$sql = "SELECT id, name, sort, text, komp , ful_copy_id, `design`, `parent`  FROM `designes_items` WHERE  `for_all`=1 AND `parent`=".$page[0]->design." 
								UNION SELECT  `id`, `name`, `sort`, `text`, `komp` , `ful_copy_id`, `design`, `parent`  FROM `pages_items` WHERE parent='".$_GET['page']."'   ORDER BY sort ASC";
						$res = mysql_query($sql);
							if(!$res) exit('bad_query '  . $sql);
							
							while($row = mysql_fetch_object($res)){
								$data[] = $row;
							}
							if (isset($data)){ 
								foreach($data as $c)
								{
									if ($komps  = $this->db->select("coms","name <>''")){								
										$kom_select='<select name="komp" onchange="javascript:save_ajax(this)">';
										$kom_select.= '<option selected value="0">Редакторы текста</option>';
										foreach($komps as $km)
										{
											if ($c->komp == $km->id) $kom_select.= '<option selected value="'.$km->id.'">Комп. '.$km->name.'</option>';
											else $kom_select.= '<option  value="'.$km->id.'">Комп. '.$km->name.'</option>';
										}
										$kom_select.= '</select>';
									}
									
									if ($mak  = $this->db->select("designes","name <>''")) {	
										$mak_select='<select name="design" onchange="javascript:save_ajax(this)">';
										
										foreach($mak as $ms)
										{
											if ($c->design == $ms->id) $mak_select.= '<option selected value="'.$ms->id.'">'.$ms->name.'</option>';
											else $mak_select.= '<option  value="'.$ms->id.'">'.$ms->name.'</option>';

										}
										$mak_select.= '</select>';
									}
								
									
									if ($c->design == 0 ) 
									{
										echo '<div class="pageitem ui-state-disabled" style=" background: #FFE4CB; ">'; 
										echo '<div class="_head"><h3><a href="#1">'.$c->name .' ( id:'.$c->id .')</a></h3></div>';	
										echo '<div class="_body"><span>Общий элемент. Порядок: '.$c->sort.'</span></div>';
										echo '</div>';
										continue;
									}				
									if ($page[0]->design == $c->design) echo '<div class="pageitem" style=" background: #C9FCBF; " id="'.$c->id.'">'; 
									if (($c->design != 0) && ($c->design != $page[0]->design)) echo '<div class="pageitem" style=" background: #EEE; " id="'.$c->id.'">'; 
									
									echo '<div class="_head"><h3><a href="javascript:window.open(\''. $this->url .'dcms/page/c_edit?editor=cm&id='.$c->id.'\',\'\',\'width=900,height=500\'); void(0)">'.$c->name .' ( id:'.$c->id .')	</a></h3>
											<div class="editors"><a class="_dell" href="'. $this->url .'dcms/page/c_del_p_i1?id='.$_GET['page'].'&piid='.$c->id.'"></a></div>
										  </div>';	
										  
										echo '<div class="_body">';
											echo '<form class="rcform" action="'. $this->url .'dcms/page/c_items_edit?id='.$_GET['page'].'" method="POST">';
												echo '
												<div class="_pif"><label>Имя: </label><input type="text" name="name" value="'.$c->name .'" onchange="javascript:save_ajax(this)"/></div>
													 <input type="hidden" name="pid" value="'.$c->id.'"/>
												<div class="_pif"><label>Порядок: </label><input type="text" name="resort" value="'.$c->sort.'" style=" width: 51px; " onchange="javascript:save_ajax(this)"/></div>
												<div class="_pif"><label>Тип материала: </label>'.$kom_select.'</div>
												<div class="_pif"><label>Макет: </label>'.$mak_select.'</div>';
												
											echo '</form>';
										echo '</div>';
									echo '</div>';
								}
							}
						echo '</div>
						
						
						<script type="text/javascript">
						$(function() 
						{
							$( "#sortable_pages" ).sortable({
							
								
								stop: function(event, ui) {
										var result = $("#sortable_pages").sortable("toArray");
										//console.log(result);
										$.post("'.$this->url.'dcms/page/c_page_iten_resort_script?id='. $_GET['page'].'",{arr:result},function(data){ // с двумя параметрами и вызовом функции
										if (data != 1) 
										{	
											alert("Порядок сортировки не сохранен"+data); 
										}
										else
										{
											//Сохоанено 
											window.location.replace(window.location);
										}
									});
								}

								});
							
						});
						</script>
						';	
					}
			}
			
			
			//Расширения параметров страницы
			$x=mysql_query('SELECT * FROM `com_page_extension`');
			if($x) {
				echo "<div class='block'>";
				if ($ext = $this->db->select("com_page_extension","page_id = {$_GET['page']}")){
					echo "<h3>Подключеные расширения:</h3>";
							//определить список полей
					$ext_com = $this->db->select("coms","table_name = 'com_page_extension'");
					if ($cf = $this->db->select('coms_fields' ,"parent='{$ext_com[0]->id}' AND show_edit = 1 ORDER BY sort ASC")){
						$_GET["iid"] = $ext_com[0]->id;
						$parent_var ='';
						if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
						echo '
							<script src="'.$this->url.'/dcms/plugins/fck/ckeditor.js"></script>
						<form action="'.$this->url.'dcms/page/c_edit_propertis?page='.$_GET['page'].'&com_id='.$ext_com[0]->id.'&iid='.$ext[0]->id.'" method="POST" enctype="multipart/form-data">';
						
						$count_edit = 1;
						foreach($cf as $f){
							$enname = $f->enname;
							$count_edit++;
							echo "
								<div class='com_items'>
								<script type='text/javascript'>
								var editor, html = '';
								function start".$count_edit."(){
								var config = {};
								//editor = CKEDITOR.appendTo('". $f->enname .$count_edit."', config, html );
								CKEDITOR.replace('1". $f->enname .$count_edit."', {
								
									filebrowserBrowseUrl: '".$this->url."dcms/files',
									filebrowserUploadUrl: '".$this->url."dcms/files/c_upload_fck'

								});
								}</script>";

							
							if ($f->type == 1) {echo '<p>'. $f->name. ':</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>Строка 120</b></div>'; continue;} 
							if ($f->type == 2) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>Строка 500</b></div>'; continue;} 
							if ($f->type == 3) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea  id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '">'.$ext[0]->$enname.'</textarea><b>Текст</b></div>'; continue;}
							if ($f->type == 4) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea  id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '">'.$ext[0]->$enname.'</textarea><b>Текст</b></div>'; continue;} 
							if ($f->type == 5) {echo '<p>'. $f->name. '</p><input id="date_only'.$f->id.'" type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>DATE - 1000-01-01</b></div>'; 
							echo "<script type='text/javascript'> new Calendar().assignTo('date_only{$f->id}'); </script>";
							continue;} 
							if ($f->type == 6) {echo '<p>'. $f->name. '</p><input id="date-with-time'.$f->id.'"  type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>DATETIME - 1000-01-01 00:00:00</b></div>'; 
							echo '<script type="text/javascript"> new Calendar({format: "%Y-%m-%d %H:%M"}).assignTo("date-with-time'.$f->id.'");</script>';
							continue;} 
							if ($f->type == 7) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>TINYINT - -128 до 127</b></div>'; continue;} 
							if ($f->type == 8) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>Целое число</b></div>'; continue;} 
							if ($f->type == 9) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/><b>Любое число</b></div>'; continue;} 
							if ($f->type == 10) {
								echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/>'.$ext[0]->$enname.' <b>Картинка max 4mb</b>';
								if ($ext[0]->$enname != '') echo '<img class="comimgshow" src="'.$this->url.'uploads/com_page_extension/1_'.$ext[0]->$enname.'" />
								<a href="'.$this->url.'dcms/page/c_del_ext_file?page='.$_GET['page'].'&com_ext_id='.$ext[0]->id.'&field='.$enname.'">Удалить</a>
								';
								
								echo '</div>';
								continue;} 
							if ($f->type == 11) {
								echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/>'.$ext[0]->$enname.' <b>Файл</b>';
								if ($ext[0]->$enname != '') echo '<a href="'.$this->url.'uploads/com_page_extension/'.$ext[0]->$enname.'" >Скачать ('.$ext[0]->$enname.')</a>
								<a href="'.$this->url.'dcms/page/c_del_ext_file?page='.$_GET['page'].'&com_ext_id='.$ext[0]->id.'&field='.$enname.'">Удалить</a>';
								echo '</div>'; continue;
								}
							if ($f->type == 12) {
								if ($ext[0]->$enname == 1) {
										echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1" checked/><b>Off/On (Галочка)</b></div>';
									} else {
										echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1"/><b>Off/On (Галочка)</b></div>';
									}
								continue;
							}
							
							if ($f->type == 13) { 
								$select_sajest = '<option value="">НЕТ</option>';
								if ($sujest = $this->db->select($f->param ,"name <> ''"))
								{
									foreach($sujest as $sj){
										if ($ext[0]->$enname ==  $sj->id) $select_sajest .= '<option selected value="'.$sj->id.'">'.$sj->name.'</option>';
										else $select_sajest .= '<option value="'.$sj->id.'">'.$sj->name.'</option>';
									}
								}
								echo '<p>'. $f->name. '</p><select  name="'. $f->enname. '" value="1"/>'.$select_sajest.'</select><b>Из списка</b></div>'; 
								continue;
							} 
							/*
							if ($f->type == 14) {	
								if ($com_use = $this->db->select('coms',"id='".$ext[0]->$enname."'")){
									echo 	'<div class="tools">
										<a href="'.$this->url.'dcms/page/show_com?page='.$_GET['page'].'&table='.$com_use[0]->table_name.'&pi='.$_GET['pi'].'&com_id='.$com_use[0]->id.'&parent='.$_GET['iid'].'">К подкомпоненту ('.$com_use[0]->name.')</a>
										</div>';
								} else echo '<p>Подкомпонент нельзя использовать для этого элемента <b>Создайте новый</b></p>';
								continue;
							}
							*/			
							echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$ext[0]->$enname.'"/></p></div>';
						
						} 
						echo '<input type="submit"   name="act"  value="Сохранить"/>
						<a href="javascript:go(\''. $this->url .'dcms/page/c_del_ext?com_ext_id='.$ext[0]->id.'&page='.$_GET['page'].'\')" style=" display: block; margin: 5px 0 0 0px; ">Удалить расширения</a>';
						echo '</form>';
					}
					
				} else {
					echo "<h3>Расширения доступны:</h3>";		
					if ($ext_com = $this->db->select("coms","table_name = 'com_page_extension'")){
						if ($ext_com_fields = $this->db->select("coms_fields","parent = {$ext_com[0]->id} AND show_edit = 1 ORDER BY sort ASC")){
							$count_edit = 0;
							echo '<script src="'.$this->url.'/dcms/plugins/fck/ckeditor.js"></script>
							<form action="'.$this->url.'dcms/page/c_edit_propertis?page='.$_GET['page'].'&com_id='.$ext_com[0]->id.'" method="POST" enctype="multipart/form-data">';
			
							foreach($ext_com_fields as $f){
									$count_edit++;
								echo "
										<div class='com_items'>
										<script type='text/javascript'>
										var editor, html = '';
										function start".$count_edit."(){
										var config = {};
										//editor = CKEDITOR.appendTo('". $f->enname .$count_edit."', config, html );
										CKEDITOR.replace('1". $f->enname .$count_edit."', {
								
									filebrowserBrowseUrl: '".$this->url."dcms/files',
									filebrowserUploadUrl: '".$this->url."dcms/files/c_upload_fck'

								});
										}</script>";
								if (($f->enname == "parent") && (isset($_GET['parent']))) 	continue;	
								if ($f->type == 1) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Строка 120</b></div>'; continue;} 
								if ($f->type == 2) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Строка 500</b></div>'; continue;} 
								if ($f->type == 3) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '"></textarea><b>Текст</b></p></div>'; continue;}
								if ($f->type == 4) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '"></textarea><b>Текст</b></p></div>'; continue;} 
								if ($f->type == 5) {echo '<p>'. $f->name. '</p><input id="date_only'.$f->id.'" type="text" name="'. $f->enname. '" value=""/><b>DATE - 1000-01-01</b></div>'; 
								echo "<script type='text/javascript'> new Calendar().assignTo('date_only{$f->id}'); </script>";
								continue;} 
								if ($f->type == 6) {echo '<p>'. $f->name. '</p><input id="date-with-time'.$f->id.'" type="text" name="'. $f->enname. '" value=""/><b>DATETIME - 1000-01-01 00:00:00</b></div>'; 
								echo '<script type="text/javascript"> new Calendar({format: "%Y-%m-%d %H:%M"}).assignTo("date-with-time'.$f->id.'");</script>';
								continue;} 
								if ($f->type == 7) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>TINYINT - -128 до 127</b></div>'; continue;} 
								if ($f->type == 8) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Целое число</b></div>'; continue;} 
								if ($f->type == 9) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Любое число</b></div>'; continue;} 
								if ($f->type == 10) {echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/><b>Картинка</b></div>'; continue;} 
								if ($f->type == 11) {echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/><b>Файл</b></div>'; continue;} 
								//new
								if ($f->type == 12) { echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1"/><b>Да </b></div>'; continue;} 
								if ($f->type == 13) { 
									$select_sajest = '<option value="">НЕТ</option>';
									if ($sujest = $this->db->select($f->param ,"name <> ''"))
									{
										foreach($sujest as $sj){
											$select_sajest .= '<option value="'.$sj->id.'">'.$sj->name.'</option>';
										}
									}
									echo '<p>'. $f->name. '</p><select  name="'. $f->enname. '" value="1"/>'.$select_sajest.'</select><b>Из списка</b></div>'; 
									continue;
								} 

								echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/></p></div>';
							
							}
							echo '<input type="submit"  value="Сохранить"/></form>';
							
						} else echo "<p>Данные не обозначены!</p>";
					
					} else echo "<p>Информация повреждена!</p>";
				}
				echo "</div>";
			} 
			
		}
		else 
		{
			// Главная
			echo "<h2>Система управления сайтом DCMS 4</h2>";
			echo $this->func->check_files($this);
			
			
			
			if (isset($_GET['log']))	$qmin = ($_GET['log'] * $show)-$show;
			
			
			if (isset($_GET['day'])){
				$date_log_s = $_GET['day'] . " 00:00:00";
				$date_log_e = $_GET['day'] . " 24:59:59";
				
				$nodate ="date > '$date_log_s' AND date < '$date_log_e' ORDER BY date DESC ";
				echo "<p>События за ".date('d-m-Y',strtotime($_GET['day'] )) .";</p>";
			} else {
				$nodate = "id>0 ORDER BY date DESC  LIMIT  20 ";
			}
			if ($adnlogs = $this->db->select('admin_logs' ,$nodate)){
				echo "<table class='com_table'>
					<tr>
						<td>Дата</td>
						<td>Пользователь</td>
						<td>Действие</td>
					</tr>";
				foreach($adnlogs as $log){
					$log_adte = date('d-m-Y h:i:s',strtotime($log->date));
					echo "<tr>";
					$action = '';
					$at = $log->action_type; 
					if ($log->action_object != '') $action = "<a href='{$this->url}dcms{$at}?{$log->action_object}'  data-original-title='{$this->url}dcms{$at}?{$log->action_object}' data-placement='right'  rel='tooltip'>...</a>";
					echo "<td>{$log_adte} </td>";
					echo "<td>{$log->user} [{$log->ip}]</td>";
					echo "<td>{$at}{$action}</td>";
					
					echo "</tr>";					
				}
				echo "</table>";
				
			} else { echo "<p>Ничего не найдено!</p>";}
			$count_log = $this->db->select('admin_logs','','COUNT(id)');
			$cval = 'COUNT(id)';
			echo "
			<p>Всего записей: {$count_log[0]->$cval} </p>
			<form>
				<p><input id='day_log' type='text' value='' name='day'/><input type='submit' value='Показать за день' /></p>
				</form>";
			echo "<script type='text/javascript'> new Calendar().assignTo('day_log'); </script>";
		}
	}
	
	function show_com(){ //  К Список позиций компонента
		echo "<style type='text/css'> div.settings{display:none;}</style>";
		$sort = $this->db->select('coms' ,"id='{$_GET['com_id'] }'");
		echo $this->func->com_header2($this,$sort);//header
		
		$cache1 = '';
		$cache2 = '';
		
		$add_query ='';
		/*пагинация*/
				$pagination='';
				$addp_sql='';
				$page_size=30;
				$sqlcount = mysql_query("SELECT COUNT(*) FROM ".$_GET['table']." WHERE page_item=".$_GET['pi']);
				$rowcount = mysql_fetch_row($sqlcount);
				$total = $rowcount[0]; // всего записей
				//echo $total;	
				if ($total > $page_size ){ //Пагинация доступна под $pagination;
					$pagination = '<ul class="pagination">';
					$pahe_count = ceil($total/$page_size); 
					$page_num = 0;
					$p_min = 0;
					$p_count = $page_size;
					if ((isset($_GET['pag'])) && ($_GET['pag'] <> 1)) {
						$p_min = $_GET['pag']*$page_size-$page_size;
						$p_count = $page_size;
					}
					echo 'min'.$p_min;
					echo 'max'.$p_count;
					$addp_sql = 'LIMIT '.$p_min.', '.$p_count;
					//echo $addp_sql;
					for($pgi=0; $pgi<$pahe_count; $pgi++){
						$page_num++;
						if ((isset($_GET['pag'])) && ($_GET['pag'] == $page_num)) {
							$pagination  .= ' <li class="active"><a href="?page='.$_GET['page'].'&table='.$_GET['table'].'&pi='.$_GET['pi'].'&com_id='.$_GET['com_id'].'&pag='.$page_num.'">'.$page_num.'</a></li>';
						}
						else
						{
							if ((!isset($_GET['pag'])) && (1 == $page_num)) {
								$pagination  .= ' <li class="active" ><a   href="?page='.$_GET['page'].'&table='.$_GET['table'].'&pi='.$_GET['pi'].'&com_id='.$_GET['com_id'].'&pag='.$page_num.'">'.$page_num.'</a></li>';
								} else {
									$pagination  .= ' <li><a   href="?page='.$_GET['page'].'&table='.$_GET['table'].'&pi='.$_GET['pi'].'&com_id='.$_GET['com_id'].'&pag='.$page_num.'">'.$page_num.'</a></li>';
								}
						}
					}
					$pagination .= '</ul>';
				}
								
		/*end пагинация*/
		
		
		if (isset($_GET['parent'])) $add_query = ' AND parent='.$_GET['parent'] .' ';
		if ($datat = $this->db->select($_GET['table'] ,"page_item='{$_GET['pi']}' {$add_query} ".$sort[0]->query.' '.$addp_sql)){
			if ($cf = $this->db->select('coms_fields' ,"parent='{$_GET['com_id'] }' AND show_table = 1 ORDER BY sort")){
			$parent_var ='';
			if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
			echo '
			<form action="'.$this->url.'dcms/page/c_del_items?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].$parent_var .'" method="POST" enctype="multipart/form-data">';
			
				echo '<table class="com_table">';
				
					echo '<th style="width: 17px"></th>
						  <th style="width: 29px">id</th>';
					foreach($cf as $f){	echo '<th>'.$f->name.'</th>'; }
					echo '<th></th>';
					
					foreach($datat as $d){
						echo '<tr>
							  <td><input type="checkbox"  name="del[]" value="'.$d->id.'"/></td>
							  <td>'.$d->id.'</td>';
						foreach($cf as $f){
							echo '<td>';
								$fname = $f->enname;
								if ($f->type < 3) echo $d->$fname;
								if (($f->type == 3) && ($d->$fname != '')) { $d->$fname = strip_tags($d->$fname);  $tmp = explode(' ',$d->$fname);  echo implode(' ', array_splice($tmp,0,2)).'..';  }
								if (($f->type == 4) && ($d->$fname != '')) echo $d->$fname;
								if ($f->type == 5) echo date('d m Y',strtotime($d->$fname));
								if ($f->type == 6) echo $d->$fname;
								if ($f->type == 7) echo $d->$fname;
								if ($f->type == 8) echo $d->$fname;
								if ($f->type == 9) echo $d->$fname;
								if (($f->type == 10) && ($d->$fname != '')) echo '<img style=" " class="comimgshow" src="'.$this->url.'uploads/'.$_GET['table'].'/1_'.$d->$fname.'" />';
								if (($f->type == 11) && ($d->$fname != '')) echo '<a  href="'.$this->url.'uploads/'.$_GET['table'].'/'.$d->$fname.'" >'.$d->$fname.'</a>';
								//new
								if ($f->type == 12) { if ($d->$fname == 1) echo '<b>Да</b>'; else echo 'Нет'; }
								if ($f->type == 13) { 
									if( $d->$fname != 0) {
										if ($sujest = $this->db->select($f->param ,"id = '{$d->$fname}'"))
										{
											echo $sujest[0]->name;
										}
									}
								}
								if ($f->type == 14) {  
									if ($cache1 != $d->$fname){
										if ($com_use = $this->db->select('coms',"id='".$d->$fname."'")){
											$cache1 = $d->$fname;
											$cache2 = $com_use;
										}
									}
									echo 	'<a href="'.$this->url.'dcms/page/show_com?page='.$_GET["page"].'&table='.$cache2[0]->table_name.'&pi='.$_GET['pi'].'&com_id='.$cache2[0]->id.'&parent='.$d->id.'">'.$cache2[0]->name.'</a>';
								} 
								
								
							echo '</td>';
						}
						echo '<td style=" width: 24px; "><a class="com_edit" href="'.$this->url.'dcms/page/edit_item?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$d->id. $parent_var.'"><img src="'.$this->url.'dcms/style/icons2/manage.png" / ></a></td>
							  </tr>';
					}
				echo '</table>'. $pagination .'  
					  <input type="submit"  value="Удалить выбраное"/>
					  </form>';
			}
		}
		else echo "Данных нет";
	}
	
	function add_form(){ // К Форма добавления элемента
		echo "<style type='text/css'> div.settings{display:none;}</style>";
		$sort = $this->db->select('coms' ,"id='{$_GET['com_id'] }'");
		echo $this->func->com_header2($this,$sort);//header
		if ($cf = $this->db->select('coms_fields' ,"parent='{$_GET['com_id'] }' AND show_edit = 1 ORDER BY sort ASC")){
			$parent_var ='';
			if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
			echo '
				<script src="'.$this->url.'/dcms/plugins/fck/ckeditor.js"></script>
			<form action="'.$this->url.'dcms/page/c_save_item?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].$parent_var.'" method="POST" enctype="multipart/form-data">';
			$count_edit = 1;
				foreach($cf as $f){
					$count_edit++;
				echo "
						<div class='com_items'>
						<script type='text/javascript'>
						var editor, html = '';
						function start".$count_edit."(){
						var config = {};
						//editor = CKEDITOR.appendTo('". $f->enname .$count_edit."', config, html );
						CKEDITOR.replace('1". $f->enname .$count_edit."', {
								
									filebrowserBrowseUrl: '".$this->url."dcms/files',
									filebrowserUploadUrl: '".$this->url."dcms/files/c_upload_fck'

								});
						}</script>";
				if (($f->enname == "parent") && (isset($_GET['parent']))) 	continue;	
				if ($f->type == 1) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Строка 120</b></div>'; continue;} 
				if ($f->type == 2) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Строка 500</b></div>'; continue;} 
				if ($f->type == 3) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '"></textarea><b>Текст</b></p></div>'; continue;}
				if ($f->type == 4) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '"></textarea><b>Текст</b></p></div>'; continue;} 
				if ($f->type == 5) {echo '<p>'. $f->name. '</p><input id="date_only'.$f->id.'" type="text" name="'. $f->enname. '" value=""/><b>DATE - 1000-01-01</b></div>'; 
				echo "<script type='text/javascript'> new Calendar().assignTo('date_only{$f->id}'); </script>";
				continue;} 
				if ($f->type == 6) {echo '<p>'. $f->name. '</p><input id="date-with-time'.$f->id.'" type="text" name="'. $f->enname. '" value=""/><b>DATETIME - 1000-01-01 00:00:00</b></div>'; 
				echo '<script type="text/javascript"> new Calendar({format: "%Y-%m-%d %H:%M"}).assignTo("date-with-time'.$f->id.'");</script>';
				continue;} 
				if ($f->type == 7) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>TINYINT - -128 до 127</b></div>'; continue;} 
				if ($f->type == 8) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Целое число</b></div>'; continue;} 
				if ($f->type == 9) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/><b>Любое число</b></div>'; continue;} 
				if ($f->type == 10) {echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/><b>Картинка</b></div>'; continue;} 
				if ($f->type == 11) {echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/><b>Файл</b></div>'; continue;} 
				//new
				if ($f->type == 12) { echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1"/><b>Да </b></div>'; continue;} 
				if ($f->type == 13) { 
					$select_sajest = '<option value="">НЕТ</option>';
					if ($sujest = $this->db->select($f->param ,"name <> ''"))
					{
						foreach($sujest as $sj){
							$select_sajest .= '<option value="'.$sj->id.'">'.$sj->name.'</option>';
						}
					}
					echo '<p>'. $f->name. '</p><select  name="'. $f->enname. '" value="1"/>'.$select_sajest.'</select><b>Из списка</b></div>'; 
					continue;
				} 
				if ($f->type == 14) {
					$com_use = $this->db->select('coms',"id='{$f->param}'");
					echo '<p>'. $f->name. ': '.$com_use[0]->name.'</p><input type="hidden" name="'. $f->enname. '" value="'.$f->param.'"/><b>Доступно при добавленом элементе</b></div>'; 
				continue;	}				 
				
				echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value=""/></p></div>';
			
			} 
			echo '<input type="submit"  name="act" value="Сохранить и добавить новый"/>';
			echo '<input type="submit"  name="act" value="Сохранить и к списку"/>';
			echo '</form>';
		}
	}
	
	function edit_item(){ // К Редактирование элемента
		echo "<style type='text/css'> div.settings{display:none;}</style>";
		$sort = $this->db->select('coms' ,"id='{$_GET['com_id'] }'");
		echo $this->func->com_header2($this,$sort);//header
		if ($row = $this->db->select($_GET['table'] ,"id='{$_GET['iid'] }'")){
			if ($cf = $this->db->select('coms_fields' ,"parent='{$_GET['com_id'] }' AND show_edit = 1 ORDER BY sort ASC")){
				$parent_var ='';
				if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
				echo '
					<script src="'.$this->url.'/dcms/plugins/fck/ckeditor.js"></script>
				<form action="'.$this->url.'dcms/page/c_save_item?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$_GET["iid"].$parent_var.'" method="POST" enctype="multipart/form-data">';
				echo '<input type="submit"   name="act"  value="Сохранить и добавить новый"/>';
				echo '<input type="submit"   name="act"  value="Сохранить и к списку"/>';
				echo '<input type="submit"   name="act"  value="Сохранить"/>';
				$count_edit = 1;
				foreach($cf as $f){
					$enname = $f->enname;
					$count_edit++;
					echo "
						<div class='com_items'>
						<script type='text/javascript'>
						var editor, html = '';
						function start".$count_edit."(){
						var config = {};
						//editor = CKEDITOR.appendTo('". $f->enname .$count_edit."', config, html );
						CKEDITOR.replace('1". $f->enname .$count_edit."', {
								
									filebrowserBrowseUrl: '".$this->url."dcms/files',
									filebrowserUploadUrl: '".$this->url."dcms/files/c_upload_fck'

								});
						}</script>";

					
					if ($f->type == 1) {echo '<p>'. $f->name. ':</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>Строка 120</b></div>'; continue;} 
					if ($f->type == 2) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>Строка 500</b></div>'; continue;} 
					if ($f->type == 3) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea  id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '">'.$row[0]->$enname.'</textarea><b>Текст</b></div>'; continue;}
					if ($f->type == 4) { echo '<p>'. $f->name. '</p>	<a href="javascript: start'.$count_edit.'()">Переключить редактор</a>	<textarea  id="1'. $f->enname. $count_edit.'" name="'. $f->enname. '">'.$row[0]->$enname.'</textarea><b>Текст</b></div>'; continue;} 
					if ($f->type == 5) {echo '<p>'. $f->name. '</p><input id="date_only'.$f->id.'" type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>DATE - 1000-01-01</b></div>'; 
					echo "<script type='text/javascript'> new Calendar().assignTo('date_only{$f->id}'); </script>";
					continue;} 
					if ($f->type == 6) {echo '<p>'. $f->name. '</p><input id="date-with-time'.$f->id.'"  type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>DATETIME - 1000-01-01 00:00:00</b></div>'; 
					echo '<script type="text/javascript"> new Calendar({format: "%Y-%m-%d %H:%M"}).assignTo("date-with-time'.$f->id.'");</script>';
					continue;} 
					if ($f->type == 7) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>TINYINT - -128 до 127</b></div>'; continue;} 
					if ($f->type == 8) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>Целое число</b></div>'; continue;} 
					if ($f->type == 9) {echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/><b>Любое число</b></div>'; continue;} 
					if ($f->type == 10) {
						echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/>'.$row[0]->$enname.' <b>Картинка max 4mb</b>';
						if ($row[0]->$enname != '') echo '<img class="comimgshow" src="'.$this->url.'uploads/'.$_GET['table'].'/1_'.$row[0]->$enname.'" />
						<a href="'.$this->url.'dcms/page/c_com_del_file?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$_GET["iid"].$parent_var.'&field='.$f->enname.'&file=1_'.$row[0]->$enname.'">Удалить</a>
						';
						
						echo '</div>';
						continue;} 
					if ($f->type == 11) {
						echo '<p>'. $f->name. '</p><input type="file" name="'. $f->enname. '" value=""/>'.$row[0]->$enname.' <b>Файл</b>';
						if ($row[0]->$enname != '') echo '<a href="'.$this->url.'uploads/'.$_GET['table'].'/'.$row[0]->$enname.'" >Скачать ('.$row[0]->$enname.')</a>
						<a href="'.$this->url.'dcms/page/c_com_del_file?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$_GET["iid"].$parent_var.'&field='.$f->enname.'&file='.$row[0]->$enname.'">Удалить</a>';
						echo '</div>'; continue;
						}
					if ($f->type == 12) {
						if ($row[0]->$enname == 1) {
								echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1" checked/><b>Off/On (Галочка)</b></div>';
							} else {
								echo '<p>'. $f->name. '</p><input type="checkbox" style=" width:20px;"  name="'. $f->enname. '" value="1"/><b>Off/On (Галочка)</b></div>';
							}
						continue;
					}
					
					if ($f->type == 13) { 
						$select_sajest = '<option value="">НЕТ</option>';
						if ($sujest = $this->db->select($f->param ,"name <> ''"))
						{
							foreach($sujest as $sj){
								if ($row[0]->$enname ==  $sj->id) $select_sajest .= '<option selected value="'.$sj->id.'">'.$sj->name.'</option>';
								else $select_sajest .= '<option value="'.$sj->id.'">'.$sj->name.'</option>';
							}
						}
						echo '<p>'. $f->name. '</p><select  name="'. $f->enname. '" value="1"/>'.$select_sajest.'</select><b>Из списка</b></div>'; 
						continue;
					} 
					if ($f->type == 14) {	
						if ($com_use = $this->db->select('coms',"id='".$row[0]->$enname."'")){
							echo 	'<div class="tools">
								<a href="'.$this->url.'dcms/page/show_com?page='.$_GET['page'].'&table='.$com_use[0]->table_name.'&pi='.$_GET['pi'].'&com_id='.$com_use[0]->id.'&parent='.$_GET['iid'].'">К подкомпоненту ('.$com_use[0]->name.')</a>
								</div>';
						} else echo '<p>Подкомпонент нельзя использовать для этого элемента <b>Создайте новый</b></p>';
						continue;
					}
								
					echo '<p>'. $f->name. '</p><input type="text" name="'. $f->enname. '" value="'.$row[0]->$enname.'"/></p></div>';
				
				} 
				echo '<input type="submit"   name="act"  value="Сохранить и добавить новый"/>';
				echo '<input type="submit"   name="act"  value="Сохранить и к списку"/>';
				echo '<input type="submit"   name="act"  value="Сохранить"/>';
				echo '</form>';
			}
		}

	}	
}


Class clear{

	function c_mak_refresh() // обновление макета с удалением
	{
		if ($page = $this->db->select("pages","id='".$_GET['id']."'")) {
			$this->db->delete('pages_items', 'parent="'.$_GET['id'].'"');
			$this->c_select_design($_GET['id'],$page[0]->design);
			header('Location: '.$this->url.'dcms/page?page='.$_GET['id'] );
		}
	}
	
	
	function c_select_design($id_page,$id_new_design,$sub_design = 0) //Смена макета
	{
		if ($all_new_item_des = $this->db->select("designes_items","parent='".$id_new_design."' AND for_all = 0"))
		{
			//$this->db->delete('pages_items', 'parent="'.$id_page.'"'); //удаление всех старых элементов страницы
			
			if ($old_items = $this->db->select("pages_items","design='".$id_new_design."' AND parent = '".$id_page."'"))
				{
					return;
				}
				
	
		}
	}

	function c_sort_ci(){
		if (isset($_POST['resort'])) {
			$i->sort = $_POST['resort'];
			$this->db->update("pages","id='". $_POST['pid'] ."'",$i);
		}
	
		if ($sortitems = $this->db->select($_GET['table'],"page_item='".$_GET['pi']."' ORDER BY d_sort ASC"))
		{
			echo '
					<html>
						<head>
							<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.js"></script>
							<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.form.js"></script>
							<script type="text/javascript" src="'.$this->url.'dcms/js/jquery_ui.js"></script>
							<script type="text/javascript" src="'.$this->url.'dcms/js/minibox.js"></script>
							
							<title>Сортировка '.$_GET['table'].'</title>

							 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/lib/codemirror.css">
							 <script src="'. $this->url .'dcms/plugins/cm/lib/codemirror.js"></script>
							 <script src="'. $this->url .'dcms/plugins/cm/lib/util/overlay.js"></script>
							 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/theme/default.css">
							 <script src="'. $this->url .'dcms/plugins/cm/mode/xml/xml.js"></script>
							 <script src="'. $this->url .'dcms/plugins/cm/mode/php/php.js"></script>
							
							
							<link rel="stylesheet" type="text/css" href="'.$this->url.'dcms/style/editors.css" />
							</head><body>
							<div class="editors_head">
								<h2 class="title">324234</h2>
							</div>
							<div class="editors">
						';
			
			echo '<div class="table" id="sortable_pages" class="table ui-sortable">';
			foreach($sortitems as $c)
			{
				echo '
					<div class="row" id="'.$c->id.'">
						<span>id:'.$c->id .' &nbsp; '.$c->name .'</span>
					</div>';
			}
			echo '</div>
				
				
				<script type="text/javascript">
				$(function() {

					$( "#sortable_pages" ).sortable({

						placeholder: "ui-state-highlight",
							stop: function(event, ui) {
								var result = $("#sortable_pages").sortable("toArray");
								console.log(result);
								$.post("'.$this->url.'dcms/page/c_ci_resort_script?table='.$_GET['table'].'",{arr:result},function(data){ // с двумя параметрами и вызовом функции
								if (data != 1) 
								{	
									alert(data);  
									
								}
								else
								{
									alert("Порядок сортировки сохранен"); 
									//window.location.replace(window.location);
								}
							});
						}

							});
						});
				</script>
				';
			
			
			
			echo '</form>
					</div>
					</body>
					</html>';
		}
		else echo "Данных нет";
	}
	
	function c_ci_resort_script()
	{
		$count=0;
		foreach($_POST['arr'] as $a)
		{
			$count+=1000;
			$i->d_sort = ++$count;
			$this->db->update($_GET['table'],"id=". $a, $i);
		}
		echo 1;
	}
	
	function c_page_resort()
	{
		if (isset($_POST['resort'])) {
		$i->sort = $_POST['resort'];
		$this->db->update("pages","id='". $_POST['pid'] ."'",$i);
		}
	
		if ($page = $this->db->select("pages","id='".$_GET['id']."'"))
		{
			echo '
				<html>
					<head>
						<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.js"></script>
						<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.form.js"></script>
						<script type="text/javascript" src="'.$this->url.'dcms/js/jquery_ui.js"></script>
						<script type="text/javascript" src="'.$this->url.'dcms/js/minibox.js"></script>
						
						<title>'.$page[0]->name.'</title>

						 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/lib/codemirror.css">
						 <script src="'. $this->url .'dcms/plugins/cm/lib/codemirror.js"></script>
						 <script src="'. $this->url .'dcms/plugins/cm/lib/util/overlay.js"></script>
						 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/theme/default.css">
						 <script src="'. $this->url .'dcms/plugins/cm/mode/xml/xml.js"></script>
						 <script src="'. $this->url .'dcms/plugins/cm/mode/php/php.js"></script>
						
						
						<link rel="stylesheet" type="text/css" href="'.$this->url.'dcms/style/editors.css" />
						</head><body>
						<div class="editors_head">
							<h2 class="title">'.$page[0]->name .'</h2>
						</div>
						<div class="editors">
					';
		if ($child = $this->db->select("pages","parent='".$_GET['id']."' ORDER BY sort ASC"))
		{
			echo '<div class="table" id="sortable_pages" class="table ui-sortable">';
			foreach($child as $c)
			{
				echo '<div class="row" id="'.$c->id.'">'; 
				
				echo '<form action="'. $this->url .'dcms/page/c_page_resort?id='.$_GET['id'].'" method="POST">';
					echo '<span>id:'.$c->id .' &nbsp; '.$c->name .'</span>
					<input type="hidden" name="pid" value="'.$c->id.'"/>
					<input type="text" name="resort" value="'.$c->sort.'"/>';
					echo '<input type="submit" name="" value="Сохранить">';
				echo '</form>';
				echo '</div>';
			}
			echo '</div>
			
			
			<script type="text/javascript">
			$(function() {

				$( "#sortable_pages" ).sortable({

					placeholder: "ui-state-highlight",
						stop: function(event, ui) {
							var result = $("#sortable_pages").sortable("toArray");
							console.log(result);
							$.post("'.$this->url.'dcms/page/c_page_resort_script?id='. $_GET['id'].'",{arr:result},function(data){ // с двумя параметрами и вызовом функции
							if (data != 1) 
							{	
								alert("Порядок сортировки не сохранен");  
								
							}
							else
							{
								alert("Порядок сортировки сохранен"); 
								//window.location.replace(window.location);
							}
						});
					}

						});
					});
			</script>
			';
		
		}
		
		echo '</form>
				</div>
				</body>
				</html>';
		}
		else echo "Страница не найдена";
	}
	
	function c_page_resort_script()
	{
		$count=0;
		foreach($_POST['arr'] as $a)
		{
			$count+=1000;
			$i->sort = ++$count;
			$this->db->update("pages","id='". $a ."'",$i);
		}
		echo 1;
	}
	
	function c_items_edit() 
	{
		if (isset($_POST['resort'])) {
		$i->sort = $_POST['resort'];
		$i->name = $_POST['name'];
		$i->komp = $_POST['komp'];
		$i->design = $_POST['design'];
		if ($this->db->update("pages_items","id='". $_POST['pid'] ."'",$i)) echo 1;
		}	
	}
	
	function c_add_new_pi(){
		if ($page = $this->db->select("pages","id='".$_GET['id']."'")){
			$add->name   = 'Новый блок';
			$add->parent = $page[0]->id;
			$add->design = $page[0]->design;
			if ($this->db->insert("pages_items",$add))
			header('Location: '.$this->url.'dcms/page?page='.$_GET['id'].'&edit=1' );
		}
	}
	
	function c_page_iten_resort_script()
	{
		$count=0;
		foreach($_POST['arr'] as $a)
		{
			$count+=1000;
			$i->sort = $count;
			$this->db->update("pages_items","id='". $a ."'",$i);
		}
		echo 1;
	}
	
	function c_del_p_i() 
	{
		$this->db->delete('pages_items', 'parent="'.$_GET['id'].'"'); //удаление всех старых элементов страницы
		header('Location: '.$this->url.'dcms/page?page='.$_GET['id'].'&edit=1' );
	}
	
	function c_del_p_i1() 
	{
		$this->db->delete('pages_items', 'id="'.$_GET['piid'].'"'); //1
		header('Location: '.$this->url.'dcms/page?page='.$_GET['id'].'&edit=1' );
	}
	
	function c_add_page() 
	{
		$use_parent_design = $this->db->select("pages","id=".$_GET['id']);
		$max_sort_id = $this->db->select("pages","parent=".$_GET['id']." ORDER BY sort DESC LIMIT 1");
		$add->parent = $_GET['id'];
		$add->name = 'Новая_страница';
		$add->sort = $max_sort_id[0]->sort+1;
		//Если подмакет не задан 
		if ($use_parent_design[0]->sub_design == 0){
			$add->design = $use_parent_design[0]->design;
		} else {
			$add->design = $use_parent_design[0]->sub_design;
			$add->sub_design = $use_parent_design[0]->sub_design;
		}
		if ($this->db->insert("pages",$add)) //создание страницы
		{
			//создание компонентов страницы
			if ($new_id = $this->db->select("pages","id>0  ORDER BY id DESC LIMIT 1 ","id"))
			{
				//print_r($add);
				$this->c_select_design($new_id[0]->id, $add->design, $use_parent_design[0]->sub_design);	
				header('Location: '.$this->url.'dcms/page?page='.$new_id[0]->id );
				//echo '<script type="text/javascript">location.replace("'. $this->url .'dcms/page");</script>';
			}
		}
		else echo 'Ошибка создание новой страницы';
	}

	function c_recurs_page_dell($id)
	{
		$this->db->delete('pages', 'id="'.$id.'"');
		$this->db->delete('z', 'parent="'.$id.'"');
		if ($pd = $this->db->select("pages","parent='".$id."'"))
		{
			foreach($pd as $p)
			{
				$this->c_recurs_page_dell($p->id);
			}
		}
	}
	
	function c_del_page()
	{	
		$this->db->delete('pages', 'id="'.$_GET['id'].'"');
		$this->db->delete('pages_items', 'parent="'.$_GET['id'].'"');
		
		if($pd = $this->db->select("pages","parent='".$_GET['id']."'"))
		{
			foreach($pd as $p)
			{
				$this->c_recurs_page_dell($p->id);
			}
		}
		echo '<script type="text/javascript">location.replace("'. $this->url .'dcms/page");</script>';
	
	}
	
	function c_save_set()
	{
		$page = $this->db->select("pages","id='".$_GET['id']."'");
		$page[0]->name = $_POST['name'];
		if ($_POST['path']=='') $page[0]->path = trim($this->func->ru_en_translite($_POST['name'])); else $page[0]->path = $_POST['path'];
		if ($_POST['title']=='') $page[0]->title = $_POST['name']; else $page[0]->title = $_POST['title'];
		if ($_POST['keyw']=='') $page[0]->keyw = $_POST['name']; else $page[0]->keyw = $_POST['keyw'];
		//$page[0]->title = $_POST['title'];
		//$page[0]->keyw = $_POST['keyw'];
		$page[0]->descr = $_POST['descr'];
		$page[0]->parent = $_POST['parent'];
		$page[0]->sub_design = $_POST['sub_maket'];
		if ($page[0]->design != $_POST['maket']) { $this->c_select_design($page[0]->id,$_POST['maket'],$_POST['sub_maket'] ); echo 'sd'; }
		$page[0]->design = $_POST['maket']; // Смена макета
		if ((isset($_POST['onoff'])) && ($_POST['onoff'] == 1)) $page[0]->off = 1; else  $page[0]->off = 0;
		if ((isset($_POST['hide_child'])) && ($_POST['hide_child'] == 1))  $page[0]->hide_child = 1; else  $page[0]->hide_child = 0;
		$this->db->update("pages","id='".$_GET['id']."'",$page[0]); 		
	}

	function c_menu_show()
	{
		if (isset($_GET['new'])) {
		if (isset($_SESSION['map']))	$arr = $_SESSION['map']; 
		$arr[$_GET['new']] = 0; 
		$_SESSION['map'] = $arr;
		}	else 	print_r($_SESSION['map']);
	}
	
	function c_menu_hide()
	{
		if (isset($_GET['new'])) {
		if (isset($_SESSION['map'])) 	$arr = $_SESSION['map']; 
		$arr[$_GET['new']] = 1; 
		$_SESSION['map'] = $arr;
		} else print_r($_SESSION['map']);
	}
	
	function c_edit()
	{
		if ($page = $this->db->select("pages_items","id='".$_GET['id']."'"))
		{
			$page_parent_name = "***";
			if ($pn = $this->db->select("pages","id='".$page[0]->parent ."'")){
				$page_parent_name = $pn[0]->name;
			}
			echo '
			<html>
				<head>
					<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.js"></script>
					<script type="text/javascript" src="'.$this->url.'dcms/js/jquery.form.js"></script>
					<script type="text/javascript" src="'.$this->url.'dcms/js/jquery_ui.js"></script>
					<script type="text/javascript" src="'.$this->url.'dcms/js/minibox.js"></script>
					
					<title>'.$page[0]->name .' — '.$page_parent_name.'</title>

					 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/lib/codemirror.css">
					 <script src="'. $this->url .'dcms/plugins/cm/lib/codemirror.js"></script>
					 <script src="'. $this->url .'dcms/plugins/cm/lib/util/overlay.js"></script>
					 <link rel="stylesheet" href="'. $this->url .'dcms/plugins/cm/theme/default.css">
					 <script src="'. $this->url .'dcms/plugins/cm/mode/xml/xml.js"></script>
					 <script src="'. $this->url .'dcms/plugins/cm/mode/php/php.js"></script>
					
					
					<link rel="stylesheet" type="text/css" href="'.$this->url.'dcms/style/editors.css" />
					</head><body>
					<div class="editors_head">
						<form  class="myForm" action="'. $this->url .'dcms/page/c_save?editor='.$_GET['editor'].'&id='.$_GET['id'].'" method="POST">
						<h2 class="title">'.$page[0]->name .' — '.$page_parent_name.'</h2>
						<input class="right_submit" type="submit" name="" value="Сохранить">
					</div>
					<div class="editors">
				';
		
		
			if ($_GET['editor'] ==  'text' ){ //текст
				echo '<textarea style="width:100%;  " name="text'.$page[0]->id.'"  onkeydown="insertTab(this, event);">'.htmlspecialchars($page[0]->text).'</textarea>';
			}
			if ($_GET['editor'] ==  'fck' ){ // виз
				echo '
				
				<script src="'.$this->url.'/dcms/plugins/fck/ckeditor.js"></script>';
									
				echo '<textarea id="editor1" class="ckeditor" name="text'.$page[0]->id.'">'. htmlspecialchars($page[0]->text).'</textarea>
				<script>
					
					CKEDITOR.replace( "editor1", {
								
								filebrowserBrowseUrl: "'.$this->url.'dcms/files",
								filebrowserUploadUrl: "'.$this->url.'dcms/files/c_upload_fck"

							});

				
				
				</script>
				';
			}
			
			if ($_GET['editor'] ==  'cm' ){  //код
					echo '
						
						

						<style type="text/css">
						div.CodeMirror-scroll{height:100% !important; }
						</style>
						<textarea id="code" name="text'.$page[0]->id.'"  onkeydown="insertTab(this, event);">'.htmlspecialchars($page[0]->text).'</textarea><br/>
						<script>
						CodeMirror.defineMode("mustache", function(config, parserConfig) {
						  var mustacheOverlay = {
							 token: function(stream, state) {
								if (stream.match("{{")) {
								  while ((ch = stream.next()) != null)
									 if (ch == "}" && stream.next() == "}") break;
								  return "mustache";
								}
								while (stream.next() != null && !stream.match("{{", false)) {}
								return null;
							 }
						  };
						  return CodeMirror.overlayParser(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
						});
						var editor = CodeMirror.fromTextArea(document.getElementById("code"), {mode: "mustache"});
						</script>
						';
			}
				echo '</form>
				</div>
				</body>
				</html>';
		}	
	} 

	function c_com_del_file(){
		//удаление файла
		$uploaddir = '../uploads/'.$_GET['table'] .'/' . $_GET['file'];
		if (unlink($uploaddir)) echo "OK (i)";
		//удаление из бд
		if ($cf = $this->db->select($_GET['table'],"id={$_GET['iid']}")){
			$field = $_GET['field'];
			$cf[0]->$field  = '';
			$this->db->update($_GET['table'],"id='". $_GET['iid'] ."'",$cf[0]);
		}
		header('Location: '.$this->url.'dcms/page/edit_item?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$_GET['iid'] );
	}
	
	
	
	function c_save_item(){ // К Добавление элемента
		$uploaddir = '../uploads/'.$_GET['table'] .'/';
		include 'img_res.php';
		if ($cf = $this->db->select('coms_fields' ,"parent='{$_GET['com_id'] }' AND show_edit = 1 ORDER BY sort ASC")){
			$add->name='Без названия';
			$add->page_item	= $_GET["pi"];
			$add->sys_date	= date('Y-m-d');
			foreach($cf as $f){
				$enname = $f->enname;
				
				if (isset($_GET['parent'])) $add->parent = $_GET['parent'];	
				if ($f->type < 3) {  if(isset($_POST[$enname])) $add->$enname = str_replace('"','&#034;',$_POST[$enname]); } 
				if (($f->type < 10) && ($f->type > 2)) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				if ($f->type == 10) {  //IMG
					$new_file_name = 'img_'.substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789',5)),0,7) . strtolower(strrchr($_FILES[$enname]['name'],'.'));
					if (copy($_FILES[$enname]['tmp_name'], $uploaddir . $new_file_name)) 
					{
						$add->$enname = $new_file_name;
						//Ресайз по пораметру  *200x300x640x480
						$resolutions = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/","", $f->param);
						$resxy = explode('x', $resolutions);
						$minx = 640;
						$miny = 480;
						if ((isset($resxy[0])) && $resxy[0] > 0) {
							$minx = $resxy[0];
							if ((isset($resxy[1])) && $resxy[0] > 0) {
								$miny = $resxy[1];
								img_resize($uploaddir.$new_file_name, $uploaddir.'1_'.$new_file_name, $minx, $miny,  90, 0xFFFFF0, 0);
							}
						}
						if ((isset($resxy[2])) && $resxy[0] > 0){
							$min2x = $resxy[2];
							if ((isset($resxy[3])) && $resxy[0] > 0){
								$min2y = $resxy[3];
								img_resize($uploaddir.$new_file_name, $uploaddir.'2_'.$new_file_name, $min2x, $min2y,  80, 0xFFFFF0, 0);
							}
						}
						if (unlink($uploaddir.$new_file_name)) echo "OK (i)";
						else { rename($uploaddir.$new_file_name, $uploaddir.'1_'.$new_file_name);	}
	
					}
				} 
				if ($f->type == 11) {  //IMG
					$new_file_name = date("m_Y").'_'.$this->func->ru_en_translite($_FILES[$enname]['name']);
					if (copy($_FILES[$enname]['tmp_name'], $uploaddir  . $new_file_name)) 
					{
						$add->$enname = $new_file_name;
					}
				} 
				//new
				if ($f->type == 12){
					if (isset($_POST[$enname]))  $add->$enname  = 1;  else $add->$enname  = 0;
				}
				
				if ($f->type == 13) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				if ($f->type == 14) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				
			}
			
			if (isset($_GET['iid'])) { 	$this->db->update($_GET['table'],"id='". $_GET['iid'] ."'",$add); }
			else{
				$this->db->insert($_GET['table'],$add);
			}
		}
		$parent_var ='';
		if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
		if ($_POST['act'] == "Сохранить и добавить новый") header('Location: '.$this->url.'dcms/page/add_form?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].$parent_var );
		if ($_POST['act'] == "Сохранить и к списку") header('Location: '.$this->url.'dcms/page/show_com?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].$parent_var );
		
		if (isset($_GET['iid'])){
		if ($_POST['act'] == "Сохранить") header('Location: '.$this->url.'dcms/page/edit_item?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].'&iid='.$_GET['iid'].$parent_var );
		}
	}
	
	function c_edit_propertis(){ // К Добавление элемента
		$uploaddir = '../uploads/com_page_extension/';
		include 'img_res.php';
		if ($cf = $this->db->select('coms_fields' ,"parent='{$_GET['com_id'] }' AND show_edit = 1 ORDER BY sort ASC")){
			$add->name='';
			$add->page_item	= $_GET['page'];
			$add->page_id	= $_GET['page'];
			$add->sys_date	= date('Y-m-d');
			foreach($cf as $f){
				$enname = $f->enname;
				if ($f->type < 3) {  if(isset($_POST[$enname])) $add->$enname = str_replace('"','&#034;',$_POST[$enname]); } 
				if (($f->type < 10) && ($f->type > 2)) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				if ($f->type == 10) {  //IMG
					$new_file_name = 'img_'.substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789',5)),0,7) . strtolower(strrchr($_FILES[$enname]['name'],'.'));
					if (copy($_FILES[$enname]['tmp_name'], $uploaddir . $new_file_name)) 
					{
						$add->$enname = $new_file_name;
						//Ресайз по пораметру  *200x300x640x480
						$resolutions = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/","", $f->param);
						$resxy = explode('x', $resolutions);
						$minx = 640;
						$miny = 480;
						if ((isset($resxy[0])) && $resxy[0] > 0) {
							$minx = $resxy[0];
							if ((isset($resxy[1])) && $resxy[0] > 0) {
								$miny = $resxy[1];
								img_resize($uploaddir.$new_file_name, $uploaddir.'1_'.$new_file_name, $minx, $miny,  90, 0xFFFFF0, 0);
							}
						}
						if ((isset($resxy[2])) && $resxy[0] > 0){
							$min2x = $resxy[2];
							if ((isset($resxy[3])) && $resxy[0] > 0){
								$min2y = $resxy[3];
								img_resize($uploaddir.$new_file_name, $uploaddir.'2_'.$new_file_name, $min2x, $min2y,  80, 0xFFFFF0, 0);
							}
						}
						if (unlink($uploaddir.$new_file_name)) echo "OK (i)";
						else { rename($uploaddir.$new_file_name, $uploaddir.'1_'.$new_file_name);	}
	
					}
				} 
				if ($f->type == 11) {  //IMG
					$new_file_name = date("m_Y").'_'.$this->func->ru_en_translite($_FILES[$enname]['name']);
					if (copy($_FILES[$enname]['tmp_name'], $uploaddir  . $new_file_name)) 
					{
						$add->$enname = $new_file_name;
					}
				} 
				//new
				if ($f->type == 12){
					if (isset($_POST[$enname]))  $add->$enname  = 1;  else $add->$enname  = 0;
				}
				
				if ($f->type == 13) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				if ($f->type == 14) {  if(isset($_POST[$enname])) $add->$enname = $_POST[$enname]; } 
				
			}
			
			if (isset($_GET['iid'])) { 	$this->db->update("com_page_extension","id='". $_GET['iid'] ."'",$add); }
			else{
				$this->db->insert("com_page_extension",$add);
			}
		}
		header('Location: '.$this->url.'dcms/page?page='.$_GET['page']);
	}
	
	function c_del_items(){	// К Удаление элементов
		if (isset($_POST['del'])){
			foreach($_POST['del'] as $i)
			{
				echo $i;
				$this->db->delete($_GET['table'], 'id="'.$i.'"');
			}
		}
		$parent_var ='';
		if (isset($_GET['parent'])) $parent_var = '&parent='.$_GET['parent'];
		header('Location: '.$this->url.'dcms/page/show_com?page='.$_GET["page"].'&table='.$_GET["table"].'&pi='.$_GET["pi"].'&com_id='.$_GET["com_id"].$parent_var  );
	}
	
	function c_save() // 
	{
		$page = $this->db->select("pages_items","id='".$_GET['id']."'");
		$page[0]->text = $_POST['text'.$_GET['id']];
		if ($this->db->update("pages_items","id='".$_GET['id']."'",$page[0])) 
		echo '<b>Сохранено</b>'; 
		//echo '<script type="text/javascript">location.replace("'. $this->url .'dcms/page/c_edit?editor='.$_GET['editor'].'&id='.$_GET['id'].'");</script>';	
		header('Location: '.$this->url.'dcms/page/c_edit?editor='.$_GET['editor'].'&id='.$_GET['id'] );
	}
	
	
	
	function c_del_ext(){
		//c_del_ext
		$this->db->delete("com_page_extension", 'id='.$_GET['com_ext_id']);
		header('Location: '.$this->url.'dcms/page?page='.$_GET['page']);
	}
	
	
	function c_del_ext_file(){
		//'.$this->url.'dcms/page/c_del_ext_file?page='.$_GET['page'].'&com_ext_id='.$ext[0]->id.'
		$ext = $this->db->select("com_page_extension","id=".$_GET['com_ext_id']);
		$f = $_GET['field'];
		$ext[0]->$f = '';
		if ($this->db->update("com_page_extension","id=".$_GET['com_ext_id'],$ext[0])) {
		header('Location: '.$this->url.'dcms/page?page='.$_GET['page']);
		}
	}
	
	function c_exit()
	{	
		session_unset();
		header('Location: '.$this->url );
	}
}