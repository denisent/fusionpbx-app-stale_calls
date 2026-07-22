<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
require_once __DIR__ . '/stale_call_switch_database.php';

class stale_call_detector {

	private $switch_database;
	private $error = null;
	private $live_channels = [];

	public function __construct() {
		$this->switch_database = new stale_call_switch_database();
	}

	public function get_error() {
		return $this->error ?: $this->switch_database->get_error();
	}

	public function get_switch_database_config() {
		return $this->switch_database->get_config();
	}

	public function get_live_channels() {
		$event_socket = event_socket::create();

		if (!$event_socket || !$event_socket->is_connected()) {
			$this->error = "Unable to connect to FreeSWITCH event socket.";
			return [];
		}

		$json = trim(event_socket::api('show channels as json'));
		$results = json_decode($json, true);

		if (!is_array($results) || empty($results['rows'])) {
			return [];
		}

		$this->live_channels = $results['rows'];
		return $this->live_channels;
	}

        public function uuid_exists($uuid) {
                $uuid = trim((string)$uuid);

                if ($uuid == '') {
                        return false;
                }

                $result = trim(event_socket::api('uuid_exists '.$uuid));

                return $result === 'true';
        }

	public function get_live_channel_uuids() {
		$live_channels = $this->get_live_channels();
		$uuids = [];

		foreach ($live_channels as $row) {
                        if (!empty($row['uuid']) && $this->uuid_exists($row['uuid'])) {
				$uuids[$row['uuid']] = true;
			}
		}

		return $uuids;
	}

	public function get_database_channels($minimum_age_minutes = 10, $limit = 5000) {
		return $this->switch_database->select_channels($minimum_age_minutes, $limit);
	}

	public function get_stale_channels($minimum_age_minutes = 10, $limit = 5000) {
		$live_uuids = $this->get_live_channel_uuids();
		$database_channels = $this->get_database_channels($minimum_age_minutes, $limit);
		$stale_channels = [];

		foreach ($database_channels as $row) {
			$uuid = $row['uuid'] ?? '';

			if ($uuid == '') {
				continue;
			}

			if (!isset($live_uuids[$uuid])) {
				$row['stale_reason'] = 'uuid_missing_from_freeswitch';
				$row['detected_epoch'] = time();
				$row['age_seconds'] = time() - intval($row['created_epoch'] ?? 0);
				$stale_channels[] = $row;
			}
		}

		return $stale_channels;
	}

	public function delete_stale_channels($uuids) {
		if (!is_array($uuids) || empty($uuids)) {
			return 0;
		}

		return $this->switch_database->delete_channels_by_uuid($uuids);
	}
}

?>
