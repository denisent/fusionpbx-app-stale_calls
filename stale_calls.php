<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
require_once dirname(__DIR__, 2) . "/resources/require.php";
require_once "resources/check_auth.php";

require_once "resources/classes/stale_call_detector.php";

if (!permission_exists('stale_call_view')) {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

$document['title'] = $text['title-stale_calls'];
require_once "resources/header.php";

$settings = new settings(["domain_uuid" => $_SESSION['domain_uuid'], "user_uuid" => $_SESSION['user_uuid']]);
$minimum_age_minutes = intval($settings->get('stale_calls', 'minimum_age_minutes', 10));

$detector = new stale_call_detector();

$switch_db_config = $detector->get_switch_database_config();
$live_channels = $detector->get_live_channels();
$database_channels = $detector->get_database_channels($minimum_age_minutes);
$stale_calls = $detector->get_stale_channels($minimum_age_minutes);

$live_count = is_array($live_channels) ? count($live_channels) : 0;
$database_count = is_array($database_channels) ? count($database_channels) : 0;
$stale_count = is_array($stale_calls) ? count($stale_calls) : 0;

echo "<form id='form_list' method='post' action='stale_calls_exec.php'>\n";
echo "<input type='hidden' id='action' name='action' value=''>\n";
echo "<input type='hidden' id='select_all_purge' name='select_all_purge' value='false'>\n";

echo "<div class='action_bar' id='action_bar'>\n";
echo "	<div class='heading'><b>".$text['title-stale_calls']."</b><div class='count'>".number_format($stale_count)."</div></div>\n";
echo "	<div class='actions'>\n";
echo button::create([
	'type'=>'button',
	'label'=>$text['button-refresh'],
	'icon'=>$settings->get('theme', 'button_icon_refresh'),
	'link'=>'stale_calls.php'
]);
if (permission_exists('stale_call_log_view')) {
	echo button::create([
		'type'=>'button',
		'label'=>$text['button-view_logs'],
		'icon'=>$settings->get('theme', 'button_icon_list'),
		'link'=>'stale_call_logs.php'
	]);
}
if ($stale_count > 0) {
	if (permission_exists('stale_call_purge')) {
		echo button::create([
			'type'=>'button',
			'label'=>$text['button-purge_selected'],
			'icon'=>$settings->get('theme', 'button_icon_delete'),
			'onclick'=>"if (confirm('Purge selected stale calls?')) { document.getElementById('action').value='purge_selected'; document.getElementById('form_list').submit(); }"
		]);
		echo button::create([
			'type'=>'button',
			'label'=>$text['button-purge_all'],
			'icon'=>$settings->get('theme', 'button_icon_delete'),
			'onclick'=>"if (confirm('Purge all stale calls?')) { document.getElementById('select_all_purge').value='true'; document.getElementById('action').value='purge_all'; document.getElementById('form_list').submit(); }"
		]);
	}
}
echo "	</div>\n";
echo "	<div style='clear: both;'></div>\n";
echo "</div>\n";

echo "<p>".$text['description-stale_calls']."</p>\n";

if ($detector->get_error()) {
	echo "<div class='alert alert-warning'>".escape($detector->get_error())."</div>\n";
}

echo "<div class='card'>\n";
echo "<table class='list'>\n";
echo "<tr class='list-header'>\n";
echo "	<th>".$text['label-metric']."</th>\n";
echo "	<th>".$text['label-value']."</th>\n";
echo "</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-live_freeswitch_channels']."</td>
		<td>".number_format($live_count)."</td>
	</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-candidate_channels_evaluated']."</td>
		<td>".number_format($database_count)."</td>
	</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-current_stale_calls']."</td>
		<td>".number_format($stale_count)."</td>
	</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-switch_database_type']."</td>
		<td>".escape($switch_db_config['type'] ?? $text['label-unknown'])."</td>
	</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-switch_database_name']."</td>
		<td>".escape($switch_db_config['name'] ?? $text['label-unknown'])."</td>
	</tr>\n";
echo "<tr class='list-row'>
		<td>".$text['label-minimum_detection_age']."</td>
		<td>".number_format($minimum_age_minutes)." ".$text['label-minutes']."</td>
	</tr>\n";
echo "</table>\n";
echo "</div>\n";

echo "<br />\n";

echo "<div class='card'>\n";
echo "<table class='list'>\n";
echo "<tr class='list-header'>\n";
if (permission_exists('stale_call_purge')) {
	echo "	<th class='checkbox'>\n";
	if ($stale_count > 0) {
		echo "		<input type='checkbox' id='checkbox_all' onclick=\"var checked=this.checked; document.querySelectorAll('.channel-checkbox').forEach(function(el){ el.checked=checked; });\">\n";
	}
	echo "	</th>\n";
}
echo "	<th>".$text['label-age']."</th>\n";
echo "	<th>".$text['label-hostname']."</th>\n";
echo "	<th>".$text['label-domain']."</th>\n";
echo "	<th>".$text['label-caller_id']."</th>\n";
echo "	<th>".$text['label-destination']."</th>\n";
echo "	<th>".$text['label-direction']."</th>\n";
echo "	<th>".$text['label-call_state']."</th>\n";
echo "	<th>".$text['label-uuid']."</th>\n";
echo "	<th>".$text['label-reason']."</th>\n";
echo "</tr>\n";

if (!empty($stale_calls)) {
	foreach ($stale_calls as $row) {
		$age_seconds = intval($row['age_seconds'] ?? 0);
		$hours = floor($age_seconds / 3600);
		$minutes = floor(($age_seconds % 3600) / 60);
		$age = $hours > 0 ? $hours."h ".$minutes."m" : $minutes."m";

		$age_style = "";
		if ($age_seconds >= 86400) {
			$age_style = "color: #b00020; font-weight: bold;";
		}
		else if ($age_seconds >= 3600) {
			$age_style = "color: #b26a00; font-weight: bold;";
		}

		echo "<tr class='list-row'>\n";
		if (permission_exists('stale_call_purge')) {
			echo "	<td class='checkbox'><input class='channel-checkbox' type='checkbox' name='channel_uuid[]' value='".escape($row['uuid'] ?? '')."'></td>\n";
		}
		echo "	<td style='".$age_style."'>".escape($age)."</td>\n";
		echo "	<td>".escape($row['hostname'] ?? '')."</td>\n";
		echo "	<td>".escape($row['context'] ?? '')."</td>\n";
		echo "	<td>".escape($row['cid_num'] ?? '')."</td>\n";
		echo "	<td>".escape($row['dest'] ?? '')."</td>\n";
		echo "	<td>".escape($row['direction'] ?? '')."</td>\n";
		echo "	<td>".escape($row['callstate'] ?? '')."</td>\n";
		echo "	<td>".escape($row['uuid'] ?? '')."</td>\n";
		echo "	<td>".escape($row['stale_reason'] ?? '')."</td>\n";
		echo "</tr>\n";
	}
}
else {
	echo "<tr class='list-row'>\n";
	echo "	<td colspan='8'>".$text['message-no_stale_calls_found']."</td>\n";
	echo "</tr>\n";
}

echo "</table>\n";
echo "</div>\n";

echo "</form>\n";

require_once "resources/footer.php";

?>
