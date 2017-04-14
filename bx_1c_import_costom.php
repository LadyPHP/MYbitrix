 <?	
/* подключаем ядро */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header("Content-type:text/html; charset=utf-8");
$_SESSION["BX_CML2_IMPORT"]["NS"]["STEP"]=0;
?>

<? /* интерфейс - begin */ ?>
<div class="row">
	<div class="row-import">
		<a  href="javascript:start('import.xml')">ИМПОРТ IMPORT.XML</a>
		<a href="javascript:start('offers.xml')">ИМПОРТ OFFERS.XML</a>
		<a href="javascript:start('company.xml')">ИМПОРТ COMPANY.XML</a>
	</div>
	<div class="row-action">
		<a class="row-button-red" href="javascript:reset()">ОБНУЛИТЬ ШАГ</a>
		<a class="row-button-red" href="javascript:status='stop'">ОСТАНОВИТЬ ИМПОРТ</a>
	</div>
	<div id="row-main">
		<div id="log"></div>
		<div id="load"></div>
	</div>
	<div id="timer"></div> 
</div>

<? /* интерфейс - end */ ?>

<script>
	/*  создания объекта XMLHttpRequest() */
	var 
	log = document.getElementById("log");
	timer = document.getElementById("timer");
	load = document.getElementById("load");
	var zup_import = false;
	/* переменные таймера */
	m_second = 0;
	seconds = 0;
	minute = 0;
	/* переменные импорта */
	i = 1;
	a = '';
	proccess = true;
	status = "continue";

	function createHttpRequest() {
		var httpRequest;
		if (window.XMLHttpRequest) 
			httpRequest = new XMLHttpRequest();  
		else if (window.ActiveXObject) {    
			try {
				httpRequest = new ActiveXObject('Msxml2.XMLHTTP');  
			} catch (e){}                                   
			try {                                           
				httpRequest = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (e){}
		}
		return httpRequest;
	}
	
	/* функция start(): обнуление переменных, запуск таймера и вызов 1с_query() */
	function start(file) {
		document.getElementById("main").style.display='block';
		load.innerHTML="<b>Загрузка...</b>";
		i = 1;
		a = "";
		m_second = 0;
		seconds = 0;
		proccess = true;
		start_timer();
		timer.innerHTML = "";
		if (file == "company.xml") {
			zup_import = true;
		}
      log.innerHTML = "<b>Импорт "+file+"</b><hr>";
      query_1c(file)
    }
	
	/* Эмулируем действия из 1С */
	/* функция 1с_query(): запросы к странице /bitrix/admin/1c_exchange.php и обработка ответов */
	function query_1c(file) {
		var import_1c = createHttpRequest();
		if (zup_import == true) {
			r = "/bitrix/admin/1c_intranet.php?type=catalog&mode=import&filename="+file;
		} else {
			r = "/bitrix/admin/1c_exchange.php?type=catalog&mode=import&filename="+file;
		}
		load.style.display = "block";
		import_1c.open("GET", r, true);
		import_1c.onreadystatechange = function() {
			a = log.innerHTML;
			if (import_1c.readyState == 4 && import_1c.status == 0) {
				error_text = "<em>Ошибка в процессе выгрузки. </em><div class='err-alarm'>Проблема с подключением.</div>"
                     log.innerHTML = a+"Шаг "+i+": "+error_text;
                     load.style.display = "none";
                     status = "continue"
                     alert("Import is crashed!");
            }
            
			if (import_1c.readyState == 4 && import_1c.status == 200) {
				if ( (import_1c.responseText.substr(0,8 ) != "progress")&&(import_1c.responseText.substr(0,7) != "success") ) {
                    error_text = "<em>Ошибка в процессе выгрузки</em><div class='err-alarm'>"+import_1c.responseText+"</div>"
                    log.innerHTML = a+"Шаг "+i+": "+error_text;
                    status = "error";
				} else {
					n = import_1c.responseText.lastIndexOf('s')+1;
					l = import_1c.responseText.length;
					mess = import_1c.responseText.substr(n,l);
					log.innerHTML = a+"Шаг "+i+": "+mess+" ("+seconds+" сек.)"+"<br>";
					seconds = 0;
					load.style.display = "none";
					i++;
				}
				if ((import_1c.responseText.substr(0,7)=="success")||(status=="error")||(status=="stop")) {
					load.style.display="none";
					status="continue"
					proccess=false;
					timer.innerHTML="<hr>Время выгрузки: <b>"+minute+" мин. "+m_second+" сек.</b>";
				} else { 
					query_1c(file);
				}
			} 
                  
        }; 
		import_1c.send(null);
    }
	
	/* функция start_timer(): таймер */
	function start_timer() {
		if (m_second == 60) {
			m_second = 0;
			minute += 1;
		}
		if (proccess==true) {
			seconds += 1;
			m_second += 1;
			setTimeout("start_timer()", 1000);
		}
	}
	
	/* функция reset(): сброс шага */     
	function reset() {
		var rest = createHttpRequest();
		q = "bx_1c_import_costom.php";
		rest.open("GET", q, true);
		rest.onreadystatechange = function() {
			if (rest.readyState == 4 && rest.status == 200)  
				alert("Шаг импорта обнулен.");
			}
			rest.send(null);
	}  
</script>