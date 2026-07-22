<?php

//application details
	$apps[$x]['name'] = "Stale Calls";
	$apps[$x]['uuid'] = "b8a6d3c4-0e7f-4e21-9f8d-6d5a2b8c1f90";
	$apps[$x]['category'] = "Advanced";
	$apps[$x]['subcategory'] = "";
	$apps[$x]['version'] = "1.0";
	$apps[$x]['license'] = "Mozilla Public License 1.1";
	$apps[$x]['url'] = "https://www.fusionpbx.com";
	$apps[$x]['description']['en-us'] = "Detect, display, purge, and log stale channel records.";
	$apps[$x]['description']['en-gb'] = "Detect, display, purge, and log stale channel records.";
	$apps[$x]['description']['ar-eg'] = "اكتشاف سجلات القنوات الراكدة وعرضها وإزالتها وتسجيلها.";
	$apps[$x]['description']['de-at'] = "Veraltete Kanaldatensätze erkennen, anzeigen, löschen und protokollieren.";
	$apps[$x]['description']['de-ch'] = "Veraltete Kanaldatensätze erkennen, anzeigen, löschen und protokollieren.";
	$apps[$x]['description']['de-de'] = "Veraltete Kanaldatensätze erkennen, anzeigen, löschen und protokollieren.";
	$apps[$x]['description']['el-gr'] = "Εντοπισμός, προβολή, εκκαθάριση και καταγραφή ανενεργών εγγραφών καναλιών.";
	$apps[$x]['description']['es-cl'] = "Detectar, mostrar, purgar y registrar registros de canales obsoletos.";
	$apps[$x]['description']['es-mx'] = "Detectar, mostrar, purgar y registrar registros de canales obsoletos.";
	$apps[$x]['description']['fr-ca'] = "Détecter, afficher, purger et consigner les enregistrements de canaux périmés.";
	$apps[$x]['description']['fr-fr'] = "Détecter, afficher, purger et consigner les enregistrements de canaux périmés.";
	$apps[$x]['description']['he-il'] = "זיהוי, הצגה, ניקוי ותיעוד של רשומות ערוצים לא פעילות.";
	$apps[$x]['description']['it-it'] = "Rilevare, visualizzare, eliminare e registrare i record dei canali obsoleti.";
	$apps[$x]['description']['ka-ge'] = "მოძველებული არხების ჩანაწერების აღმოჩენა, ჩვენება, გასუფთავება და ჟურნალში ჩაწერა.";
	$apps[$x]['description']['nl-nl'] = "Verouderde kanaalrecords detecteren, weergeven, opschonen en registreren.";
	$apps[$x]['description']['pl-pl'] = "Wykrywaj, wyświetlaj, usuwaj i rejestruj nieaktualne rekordy kanałów.";
	$apps[$x]['description']['pt-br'] = "Detectar, exibir, limpar e registrar registros de canais obsoletos.";
	$apps[$x]['description']['pt-pt'] = "Detetar, apresentar, limpar e registar registos de canais obsoletos.";
	$apps[$x]['description']['ro-ro'] = "Detectează, afișează, șterge și înregistrează jurnalele canalelor învechite.";
	$apps[$x]['description']['ru-ru'] = "Обнаружение, просмотр, очистка и журналирование устаревших записей каналов.";
	$apps[$x]['description']['sv-se'] = "Identifiera, visa, rensa och logga inaktuella kanalposter.";
	$apps[$x]['description']['uk-ua'] = "Виявлення, перегляд, очищення та журналювання застарілих записів каналів.";
	$apps[$x]['description']['tr-tr'] = "Eski kanal kayıtlarını algılayın, görüntüleyin, temizleyin ve günlüğe kaydedin.";
	$apps[$x]['description']['zh-cn'] = "检测、显示、清除并记录陈旧的通道记录。";
	$apps[$x]['description']['ja-jp'] = "古いチャネルレコードを検出、表示、削除し、ログに記録します。";
	$apps[$x]['description']['ko-kr'] = "오래된 채널 레코드를 검색하고, 표시하고, 제거하고, 로그에 기록합니다.";

//permission details
	$y = 0;
	$apps[$x]['permissions'][$y]['name'] = "stale_call_view";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
	$apps[$x]['permissions'][$y]['groups'][] = "admin";
	$y++;
	$apps[$x]['permissions'][$y]['name'] = "stale_call_purge";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
	$apps[$x]['permissions'][$y]['groups'][] = "admin";
	$y++;
	$apps[$x]['permissions'][$y]['name'] = "stale_call_log_view";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
	$apps[$x]['permissions'][$y]['groups'][] = "admin";
	$y++;

//default settings
	$y = 0;
	//minimum age before a channel can be considered stale
	$apps[$x]['default_settings'][$y]['default_setting_uuid'] = '2c4c3dfc-1b25-438d-b750-e38b21b2d0e1';
	$apps[$x]['default_settings'][$y]['default_setting_category'] = 'stale_calls';
	$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = 'minimum_age_minutes';
	$apps[$x]['default_settings'][$y]['default_setting_name'] = 'numeric';
	$apps[$x]['default_settings'][$y]['default_setting_value'] = '10';
	$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
	$apps[$x]['default_settings'][$y]['default_setting_description'] = 'Minimum minutes before a channel can be considered stale.';
	$y++;

?>
