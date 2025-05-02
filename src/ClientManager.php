<?php

namespace ZCRM;

use PDO;
use Exception;
use Illuminate\Support\Facades\File;

class ClientManager
{
    protected $db;
    protected $path;

    public function __construct()
    {
        $this->path = config('zcrm.storage_path') . '/crm_connections.sqlite';

        if (!File::exists(dirname($this->path))) {
            File::makeDirectory(dirname($this->path), 0755, true);
        }

        $init = !File::exists($this->path);

        $this->db = new PDO('sqlite:' . $this->path);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($init) {
            $this->createTable();
        }
    }

    protected function createTable()
    {
        $this->db->exec("
            CREATE TABLE crm_connections (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                client_id TEXT NOT NULL,
                client_secret TEXT NOT NULL,
                refresh_token TEXT NOT NULL,
                access_token TEXT,
                expires_at DATETIME,
                api_domain TEXT DEFAULT 'https://www.zohoapis.eu',
                region TEXT DEFAULT 'eu',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    public function addConnection(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO crm_connections (name, client_id, client_secret, refresh_token, api_domain, region)
            VALUES (:name, :client_id, :client_secret, :refresh_token, :api_domain, :region)
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':client_id' => $data['client_id'],
            ':client_secret' => $data['client_secret'],
            ':refresh_token' => $data['refresh_token'],
            ':api_domain' => $data['api_domain'] ?? 'https://www.zohoapis.eu',
            ':region' => $data['region'] ?? 'eu',
        ]);
    }

    public function listConnections(): array
    {
        return $this->db->query("SELECT * FROM crm_connections ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeConnection(string $name): bool
    {
        $stmt = $this->db->prepare("DELETE FROM crm_connections WHERE name = :name");
        return $stmt->execute([':name' => $name]);
    }

    public function getConnection(?string $name = null): ?array
    {
        if ($name) {
            $stmt = $this->db->prepare("SELECT * FROM crm_connections WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
        } else {
            $stmt = $this->db->query("SELECT * FROM crm_connections ORDER BY created_at ASC LIMIT 1");
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateTokens(string $name, string $accessToken, string $expiresAt)
    {
        $stmt = $this->db->prepare("
            UPDATE crm_connections
            SET access_token = :access_token, expires_at = :expires_at
            WHERE name = :name
        ");

        $stmt->execute([
            ':access_token' => $accessToken,
            ':expires_at' => $expiresAt,
            ':name' => $name,
        ]);
    }
}
