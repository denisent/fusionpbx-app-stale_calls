<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
require_once dirname(__DIR__, 2) . "/resources/require.php";
require_once "resources/check_auth.php";

require_once "resources/classes/stale_call_detector.php";
require_once "resources/classes/stale_call_logger.php";

$action = $_POST['action'] ?? '';
$selected = $_POST['channel_uuid'] ?? [];
$select_all_purge = $_POST['select_all_purge'] ?? 'false';

if (!is_array($selected)) {
	$selected = [];
}

$settings = new settings([
	'domain_uuid' => $_SESSION['domain_uuid'],
	'user_uuid' => $_SESSION['user_uuid']
]);

$minimum_age_minutes = intval($settings->get('stale_calls', 'minimum_age_minutes', 10));

$detector = new stale_call_detector();
$logger = new stale_call_logger();
$logger->create_table();

$config = $detector->get_switch_database_config();

$stale_calls = $detector->get_stale_channels($minimum_age_minutes);

$calls_to_purge = [];

if ($action == 'purge_all' || $select_all_purge == 'true') {
	$calls_to_purge = $stale_calls;
}
else if ($action == 'purge_selected') {
	foreach ($stale_calls as $call) {
		if (!empty($call['uuid']) && in_array($call['uuid'], $selected)) {
			$calls_to_purge[] = $call;
		}
	}
}

if (!empty($calls_to_purge)) {
	$logger->log_calls(
		$calls_to_purge,
		[
			'switch_db_type' => $config['type'] ?? '',
			'switch_db_name' => $config['name'] ?? ''
		]
	);

	$uuids = array_column($calls_to_purge, 'uuid');
	$detector->delete_stale_channels($uuids);
}

header('Location: stale_calls.php');
exit;

?>
