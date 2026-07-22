<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
class stale_call_logger {

	private $database;
	private $error = null;

	public function __construct() {
		global $database;
		$this->database = $database;
	}

	public function get_error() {
		return $this->error;
	}

	public function create_table() {
		$sql = "
		CREATE TABLE IF NOT EXISTS v_stale_call_logs (
			stale_call_log_uuid UUID PRIMARY KEY,
			channel_uuid VARCHAR(255),
			call_uuid VARCHAR(255),
			hostname VARCHAR(255),
			domain_name VARCHAR(255),
			domain_uuid UUID,
			xml_domain_name VARCHAR(255),
			cid_num VARCHAR(255),
			dest VARCHAR(255),
			direction VARCHAR(32),
			created_epoch BIGINT,
			age_seconds BIGINT,
			stale_reason VARCHAR(255),
			switch_db_type VARCHAR(32),
			switch_db_name VARCHAR(255),
			xml_cdr_uuid UUID,
			xml_cdr_found BOOLEAN DEFAULT FALSE,
			purged_by VARCHAR(255),
			purged_date TIMESTAMP,
			insert_date TIMESTAMP,
			channel_json TEXT
		)";
		return $this->database->execute($sql);
	}

        private function digits_only($value) {
                return preg_replace('/[^0-9]/', '', (string)$value);
        }

	public function find_xml_cdr_data($call) {
		$channel_uuid = $call['uuid'] ?? '';
		$call_uuid = $call['call_uuid'] ?? '';
                $domain_name = $call['domain_name'] ?? ($call['context'] ?? '');
                $cid_num = $this->digits_only($call['cid_num'] ?? '');
                $dest = $this->digits_only($call['dest'] ?? '');
                $created_epoch = intval($call['created_epoch'] ?? 0);

                if ($channel_uuid != '' || $call_uuid != '') {
		$sql = "
			select
				xml_cdr_uuid,
				domain_uuid,
				domain_name
			from v_xml_cdr
            where xml_cdr_uuid::text = :channel_uuid
               or xml_cdr_uuid::text = :call_uuid
               or originating_leg_uuid::text = :channel_uuid
			   or originating_leg_uuid::text = :call_uuid
               or bridge_uuid::text = :channel_uuid
               or bridge_uuid::text = :call_uuid
			order by start_epoch desc
			limit 1
		";

		$parameters = [
			'channel_uuid' => $channel_uuid,
			'call_uuid' => $call_uuid
		];

		$result = $this->database->select($sql, $parameters, 'row');

                        if (is_array($result) && !empty($result['xml_cdr_uuid'])) {
                                return $result;
                        }
                }

                if ($domain_name != '' && $cid_num != '' && $created_epoch > 0) {
                        $sql = "
                                select
	                                xml_cdr_uuid,
	                                domain_uuid,
	                                domain_name
                                from v_xml_cdr
                                where domain_name = :domain_name
	                                and regexp_replace(coalesce(caller_id_number, ''), '[^0-9]', '', 'g') = :cid_num
	                                and start_epoch between :start_epoch_min and :start_epoch_max
                                order by
	                                case
	                                    when regexp_replace(coalesce(destination_number, ''), '[^0-9]', '', 'g') = :dest then 0
	                                    else 1
	                                end,
	                                abs(start_epoch - :created_epoch) asc
                                limit 1
                        ";

                        $parameters = [
                                'domain_name' => $domain_name,
                                'cid_num' => $cid_num,
                                'dest' => $dest,
                                'start_epoch_min' => $created_epoch - 300,
                                'start_epoch_max' => $created_epoch + 300,
                                'created_epoch' => $created_epoch
                        ];

                        $result = $this->database->select($sql, $parameters, 'row');

                        if (is_array($result) && !empty($result['xml_cdr_uuid'])) {
                                return $result;
                        }
                }

                return [];
	}

	public function log_call($call, $extra = []) {
		$xml_cdr = $this->find_xml_cdr_data($call);

		$xml_cdr_uuid = $xml_cdr['xml_cdr_uuid'] ?? null;
		$domain_uuid = $xml_cdr['domain_uuid'] ?? null;
		$xml_domain_name = $xml_cdr['domain_name'] ?? null;

		$sql = "
			insert into v_stale_call_logs (
				stale_call_log_uuid,
				channel_uuid,
				call_uuid,
				hostname,
				domain_name,
				domain_uuid,
				xml_domain_name,
				cid_num,
				dest,
				direction,
				created_epoch,
				age_seconds,
				stale_reason,
				switch_db_type,
				switch_db_name,
				xml_cdr_uuid,
				xml_cdr_found,
				purged_by,
				purged_date,
				insert_date,
				channel_json
			)
			values (
				:stale_call_log_uuid,
				:channel_uuid,
				:call_uuid,
				:hostname,
				:domain_name,
				:domain_uuid,
				:xml_domain_name,
				:cid_num,
				:dest,
				:direction,
				:created_epoch,
				:age_seconds,
				:stale_reason,
				:switch_db_type,
				:switch_db_name,
				:xml_cdr_uuid,
				:xml_cdr_found,
				:purged_by,
				:purged_date,
				:insert_date,
				:channel_json
			)
		";

		$parameters = [
			'stale_call_log_uuid' => uuid(),
			'channel_uuid' => $call['uuid'] ?? null,
			'call_uuid' => $call['call_uuid'] ?? null,
			'hostname' => $call['hostname'] ?? null,
			'domain_name' => $call['domain_name'] ?? ($call['context'] ?? null),
			'domain_uuid' => $domain_uuid,
			'xml_domain_name' => $xml_domain_name,
			'cid_num' => $call['cid_num'] ?? null,
			'dest' => $call['dest'] ?? null,
			'direction' => $call['direction'] ?? null,
			'created_epoch' => $call['created_epoch'] ?? null,
			'age_seconds' => $call['age_seconds'] ?? null,
			'stale_reason' => $call['stale_reason'] ?? null,
			'switch_db_type' => $extra['switch_db_type'] ?? null,
			'switch_db_name' => $extra['switch_db_name'] ?? null,
			'xml_cdr_uuid' => $xml_cdr_uuid,
			'xml_cdr_found' => $xml_cdr_uuid ? 'true' : 'false',
			'purged_by' => $_SESSION['username'] ?? 'system',
			'purged_date' => date('Y-m-d H:i:s'),
			'insert_date' => date('Y-m-d H:i:s'),
			'channel_json' => json_encode($call)
		];

		$this->database->execute($sql, $parameters);

		if (isset($this->database->message['code']) && $this->database->message['code'] != '200') {
			$this->error = $this->database->message['message'] ?? 'Unknown database error';
			return false;
		}

		return true;
	}

	public function log_calls($calls, $extra = []) {
		if (!is_array($calls)) {
			return 0;
		}

		$count = 0;
		foreach ($calls as $call) {
			if ($this->log_call($call, $extra)) {
				$count++;
			}
		}

		return $count;
	}

	public function cleanup_logs($retention_days = 180) {
		$retention_days = intval($retention_days);
		$sql = "
			delete from v_stale_call_logs
			where insert_date < now() - interval '".$retention_days." days'
		";
		return $this->database->execute($sql);
	}
}

?>
