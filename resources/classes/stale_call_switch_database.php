<?php
 /*
 * Contributor(s):
 * denisent dev team
 */
class stale_call_switch_database {

	private $config = [];
	private $pdo = null;
	private $error = null;

	public function __construct() {
		$this->config = $this->read_config();
	}

	public function get_error() {
		return $this->error;
	}

	public function get_config() {
		return $this->config;
	}

	private function read_config() {
		$config_file = '/etc/fusionpbx/config.conf';
		$config = [];

		if (!is_readable($config_file)) {
			$this->error = "Unable to read ".$config_file;
			return $config;
		}

		$lines = file($config_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line == '' || substr($line, 0, 1) == '#') {
				continue;
			}
			if (strpos($line, '=') === false) {
				continue;
			}

			list($key, $value) = array_map('trim', explode('=', $line, 2));
			if (strpos($key, 'database.1.') === 0) {
				$config[str_replace('database.1.', '', $key)] = $value;
			}
		}

		return $config;
	}

	private function connect() {
		if ($this->pdo !== null) {
			return $this->pdo;
		}

		if (empty($this->config['type'])) {
			$this->error = "Switch database type is not configured.";
			return false;
		}

		try {
			if ($this->config['type'] == 'sqlite') {
				$path = rtrim($this->config['path'] ?? '/var/lib/freeswitch/db', '/');
				$name = $this->config['name'] ?? 'core.db';
				$file = $path.'/'.$name;

				if (!is_readable($file)) {
					$this->error = "SQLite switch database is not readable: ".$file;
					return false;
				}

				$this->pdo = new PDO('sqlite:'.$file);
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $this->pdo;
			}

			if ($this->config['type'] == 'pgsql') {
				$host = $this->config['host'] ?? '127.0.0.1';
				$port = $this->config['port'] ?? '5432';
				$name = $this->config['name'] ?? 'freeswitch';
				$user = $this->config['username'] ?? '';
				$pass = $this->config['password'] ?? '';

				$dsn = "pgsql:host=".$host.";port=".$port.";dbname=".$name;
				$this->pdo = new PDO($dsn, $user, $pass);
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $this->pdo;
			}

			$this->error = "Unsupported switch database type: ".$this->config['type'];
			return false;
		}
		catch (Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function table_exists($table_name) {
		$pdo = $this->connect();
		if (!$pdo) {
			return false;
		}

		try {
			if ($this->config['type'] == 'sqlite') {
				$stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table_name");
				$stmt->execute(['table_name' => $table_name]);
				return $stmt->fetchColumn() !== false;
			}

			if ($this->config['type'] == 'pgsql') {
				$stmt = $pdo->prepare("SELECT to_regclass(:table_name)");
				$stmt->execute(['table_name' => $table_name]);
				return $stmt->fetchColumn() !== null;
			}
		}
		catch (Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}

		return false;
	}

	public function select_channels($minimum_age_minutes = 10, $limit = 5000) {
		$pdo = $this->connect();
		if (!$pdo) {
			return [];
		}

		if (!$this->table_exists('channels')) {
			$this->error = "Switch database channels table was not found.";
			return [];
		}

		$minimum_age_seconds = intval($minimum_age_minutes) * 60;
		$cutoff_epoch = time() - $minimum_age_seconds;
		$limit = intval($limit);

		try {
			$sql = "select * from channels where created_epoch < :cutoff_epoch order by created_epoch asc limit ".$limit;
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['cutoff_epoch' => $cutoff_epoch]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (Exception $e) {
			$this->error = $e->getMessage();
			return [];
		}
	}

	public function delete_channels_by_uuid($uuids) {
		$pdo = $this->connect();
		if (!$pdo || empty($uuids) || !is_array($uuids)) {
			return 0;
		}

		$placeholders = [];
		$params = [];
		foreach ($uuids as $index => $uuid) {
			$key = ':uuid_'.$index;
			$placeholders[] = $key;
			$params[$key] = $uuid;
		}

		try {
			$sql = "delete from channels where uuid in (".implode(',', $placeholders).")";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($params);
			return $stmt->rowCount();
		}
		catch (Exception $e) {
			$this->error = $e->getMessage();
			return 0;
		}
	}
}

?>
