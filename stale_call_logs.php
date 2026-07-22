<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
require_once dirname(__DIR__, 2) . "/resources/require.php";
require_once "resources/check_auth.php";

if (!permission_exists('stale_call_log_view')) {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

$document['title'] = $text['title-stale_call_logs'];
require_once "resources/header.php";
require_once "resources/classes/stale_call_logger.php";

if (isset($_SESSION['domain']['time_zone']['name'])) {
        $time_zone = $_SESSION['domain']['time_zone']['name'];
}
else {
        $time_zone = date_default_timezone_get();
}

echo "<div class='action_bar' id='action_bar'>\n";
echo "	<div class='heading'><b>".$text['title-stale_call_logs']."</b></div>\n";
echo "	<div class='actions'>\n";
	echo button::create([
		'type'=>'button',
		'label'=>$text['button-back'],
		'icon'=>$settings->get('theme', 'button_icon_back'),
		'link'=>'stale_calls.php'
	]);
echo "	</div>\n";
echo "	<div style='clear: both;'></div>\n";
echo "</div>\n";

$logger = new stale_call_logger();
$logger->create_table();

global $database;

$sql = "
	select
		stale_call_log_uuid,
		channel_uuid,
		call_uuid,
		hostname,
		domain_name,
		xml_domain_name,
		cid_num,
		dest,
		direction,
		age_seconds,
		stale_reason,
		switch_db_type,
		switch_db_name,
		xml_cdr_uuid,
		xml_cdr_found,
		purged_by,
		purged_date,
		insert_date
	from v_stale_call_logs
	order by insert_date desc
	limit 200
";

$rows = $database->select($sql, null, 'all');

echo "<div class='card'>\n";
echo "<table class='list'>\n";
echo "<tr class='list-header'>\n";
echo "	<th>".$text['label-date']."</th>\n";
echo "	<th>".$text['label-domain']."</th>\n";
echo "	<th>".$text['label-caller_id']."</th>\n";
echo "	<th>".$text['label-destination']."</th>\n";
echo "	<th>".$text['label-age']."</th>\n";
echo "	<th>".$text['label-hostname']."</th>\n";
echo "	<th>".$text['label-reason']."</th>\n";
echo "	<th>".$text['label-cdr']."</th>\n";
echo "	<th>".$text['label-purged_by']."</th>\n";
echo "</tr>\n";

if (!empty($rows)) {
	foreach ($rows as $row) {
		$age_seconds = intval($row['age_seconds'] ?? 0);
		$hours = floor($age_seconds / 3600);
		$minutes = floor(($age_seconds % 3600) / 60);
		$age = $hours > 0 ? $hours."h ".$minutes."m" : $minutes."m";

		$domain_display = $row['xml_domain_name'] ?: ($row['domain_name'] ?? '');

		$cdr = "";
		if (!empty($row['xml_cdr_uuid'])) {
			$cdr = "<a href='/app/xml_cdr/xml_cdr_details.php?id=".escape($row['xml_cdr_uuid'])."'>".$text['label-view']."</a>";
		}
		else {
			$cdr = $text['label-not_found'];
		}

		echo "<tr class='list-row'>\n";
		try {
		        $dt = new DateTime($row['insert_date'], new DateTimeZone('UTC'));
		        $dt->setTimezone(new DateTimeZone($time_zone));
		        $insert_date = $dt->format('Y-m-d H:i:s');
		}
		catch (Exception $e) {
		        $insert_date = $row['insert_date'] ?? '';
		}
		echo "  <td>".escape($insert_date)."</td>\n";
		echo "	<td>".escape($domain_display)."</td>\n";
		echo "	<td>".escape($row['cid_num'] ?? '')."</td>\n";
		echo "	<td>".escape($row['dest'] ?? '')."</td>\n";
		echo "	<td>".escape($age)."</td>\n";
		echo "	<td>".escape($row['hostname'] ?? '')."</td>\n";
		echo "	<td>".escape($row['stale_reason'] ?? '')."</td>\n";
		echo "	<td>".$cdr."</td>\n";
		echo "	<td>".escape($row['purged_by'] ?? '')."</td>\n";
		echo "</tr>\n";
	}
}
else {
	echo "<tr class='list-row'><td colspan='9'>".$text['message-no_stale_calls_found']."</td></tr>\n";
}

echo "</table>\n";
echo "</div>\n";

require_once "resources/footer.php";

?>
