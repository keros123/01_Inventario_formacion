<?php

require_once __DIR__ . '/../../config/env.php';

/**
 * Cliente HTTP de Supabase (REST + Storage).
 * Usa la publishable/anon key; no requiere contraseña de PostgreSQL.
 */
class Database
{
    private static ?array $config = null;

    public static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/database.php';
        }
        return self::$config;
    }

    public static function prefix(): string
    {
        return (string) (self::getConfig()['prefix'] ?? '');
    }

    public static function table(string $name): string
    {
        return strtolower(self::prefix() . $name);
    }

    public static function from(string $logicalTable): SupabaseQuery
    {
        return new SupabaseQuery(self::table($logicalTable));
    }

    public static function url(): string
    {
        return rtrim((string) (self::getConfig()['url'] ?? ''), '/');
    }

    public static function anonKey(): string
    {
        return (string) (self::getConfig()['anon_key'] ?? '');
    }

    public static function storageBucket(): string
    {
        return (string) (self::getConfig()['storage_bucket'] ?? 'productos');
    }

    public static function fileUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($relative, 'uploads/') || str_starts_with($relative, 'public/')) {
            $app = require __DIR__ . '/../../config/app.php';
            return rtrim((string) ($app['base_url'] ?? ''), '/') . '/' . $relative;
        }

        return self::publicObjectUrl($relative);
    }

    public static function publicObjectUrl(string $objectPath): string
    {
        $objectPath = ltrim($objectPath, '/');
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));
        return self::url() . '/storage/v1/object/public/' . rawurlencode(self::storageBucket()) . '/' . $encodedPath;
    }

    /**
     * Sube un archivo al bucket y devuelve la ruta del objeto (para file_url()).
     */
    public static function upload(string $objectPath, string $tmpFile, string $mime = 'application/octet-stream'): string
    {
        $objectPath = ltrim(str_replace('\\', '/', $objectPath), '/');
        if ($objectPath === '' || !is_readable($tmpFile)) {
            throw new RuntimeException('No se pudo leer el archivo a subir.');
        }

        $bucket = rawurlencode(self::storageBucket());
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));
        $endpoint = self::url() . '/storage/v1/object/' . $bucket . '/' . $encodedPath;
        $contents = file_get_contents($tmpFile);
        if ($contents === false) {
            throw new RuntimeException('No se pudo leer el archivo a subir.');
        }

        $status = 0;
        $body = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => $contents,
                CURLOPT_HTTPHEADER     => [
                    'apikey: ' . self::anonKey(),
                    'Authorization: Bearer ' . self::anonKey(),
                    'Content-Type: ' . $mime,
                    'x-upsert: true',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
            ]);
            $result = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($result === false) {
                throw new RuntimeException('No se pudo conectar con Supabase Storage: ' . $error);
            }
            $body = (string) $result;
        } else {
            $response = self::http('POST', $endpoint, $contents, [
                'Content-Type: ' . $mime,
                'x-upsert: true',
            ], false);
            $status = $response['status'];
            $body = $response['body'];
        }

        if ($status >= 400) {
            $localPath = self::saveLocalObject($objectPath, $tmpFile);
            if ($localPath !== null) {
                error_log('Supabase Storage falló (' . $status . ' ' . $body . '); se guardó en ' . $localPath);
                return $localPath;
            }
            $decoded = json_decode($body, true);
            $message = is_array($decoded)
                ? (string) ($decoded['message'] ?? $decoded['error'] ?? $body)
                : $body;
            throw new RuntimeException('No se pudo subir la fotografía: ' . ($message !== '' ? $message : ('HTTP ' . $status)));
        }

        return $objectPath;
    }

    private static function saveLocalObject(string $objectPath, string $tmpFile): ?string
    {
        $app = require __DIR__ . '/../../config/app.php';
        $relative = $objectPath;
        $destDir = null;

        if (str_starts_with($objectPath, 'inventario/')) {
            $destDir = (string) ($app['upload_dir'] ?? '');
            $relative = 'uploads/inventario/' . basename($objectPath);
        } elseif (str_starts_with($objectPath, 'dar-baja/')) {
            $destDir = (string) ($app['upload_dar_baja'] ?? '');
            $relative = 'uploads/dar-baja/' . basename($objectPath);
        }

        if ($destDir === '' || $destDir === null) {
            return null;
        }
        if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            return null;
        }

        $dest = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . basename($objectPath);
        if (!@copy($tmpFile, $dest)) {
            return null;
        }

        return $relative;
    }

    /**
     * @return array{status:int, body:string, headers:array<string,string>}
     */
    public static function http(
        string $method,
        string $url,
        mixed $body = null,
        array $extraHeaders = [],
        bool $jsonBody = true
    ): array {
        $key = self::anonKey();
        if ($key === '' || self::url() === '') {
            throw new RuntimeException('Faltan SUPABASE_URL o SUPABASE_ANON_KEY en la configuración.');
        }

        $headers = array_merge([
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
            'Accept: application/json',
        ], $extraHeaders);

        $payload = $body;
        if ($jsonBody && $body !== null && !is_string($body)) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
        }

        $headerLine = implode("\r\n", $headers);
        $opts = [
            'http' => [
                'method'        => $method,
                'header'        => $headerLine,
                'content'       => $payload ?? '',
                'ignore_errors' => true,
                'timeout'       => 30,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ];

        $result = @file_get_contents($url, false, stream_context_create($opts));
        $rawHeaders = $http_response_header ?? [];
        $status = 0;
        $parsed = [];
        foreach ($rawHeaders as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                $status = (int) $m[1];
                continue;
            }
            $pos = strpos($line, ':');
            if ($pos !== false) {
                $parsed[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
            }
        }

        if ($result === false && $status === 0) {
            throw new RuntimeException('No se pudo conectar con Supabase: ' . $url);
        }

        return [
            'status'  => $status,
            'body'    => $result === false ? '' : $result,
            'headers' => $parsed,
        ];
    }

    public static function rest(
        string $method,
        string $table,
        array $query = [],
        mixed $body = null,
        array $prefer = [],
        array $extraHeaders = []
    ): array {
        $url = self::url() . '/rest/v1/' . rawurlencode($table);
        if ($query) {
            $url .= '?' . implode('&', $query);
        }

        $extra = $extraHeaders;
        if ($prefer) {
            $extra[] = 'Prefer: ' . implode(',', $prefer);
        }

        $response = self::http($method, $url, $body, $extra);
        if ($response['status'] >= 400) {
            $decoded = json_decode($response['body'], true);
            $message = $decoded['message'] ?? $response['body'] ?: ('HTTP ' . $response['status']);
            throw new RuntimeException('Supabase REST: ' . $message);
        }

        return $response;
    }
}

class SupabaseQuery
{
    private string $table;
    private string $select = '*';
    /** @var list<string> */
    private array $filters = [];
    /** @var list<string> */
    private array $order = [];
    private ?int $limit = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(string $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function eq(string $column, mixed $value): self
    {
        return $this->filter($column, 'eq', $value);
    }

    public function neq(string $column, mixed $value): self
    {
        return $this->filter($column, 'neq', $value);
    }

    public function gt(string $column, mixed $value): self
    {
        return $this->filter($column, 'gt', $value);
    }

    public function gte(string $column, mixed $value): self
    {
        return $this->filter($column, 'gte', $value);
    }

    public function lte(string $column, mixed $value): self
    {
        return $this->filter($column, 'lte', $value);
    }

    public function ilike(string $column, string $value): self
    {
        return $this->filter($column, 'ilike', $value);
    }

    public function in(string $column, array $values): self
    {
        if ($values === []) {
            $this->filters[] = $column . '=in.()';
            return $this;
        }
        $encoded = implode(',', array_map(
            static fn ($v) => str_replace([',', '(', ')'], ['\\,', '\\(', '\\)'], (string) $v),
            $values
        ));
        $this->filters[] = $column . '=in.(' . $encoded . ')';
        return $this;
    }

    public function orIlike(array $columns, string $pattern): self
    {
        $parts = [];
        foreach ($columns as $column) {
            $parts[] = $column . '.ilike.' . $pattern;
        }
        $this->filters[] = 'or=(' . implode(',', $parts) . ')';
        return $this;
    }

    public function orIn(string $column, array $values): self
    {
        $parts = [];
        foreach ($values as $value) {
            $parts[] = $column . '.eq.' . $value;
        }
        $this->filters[] = 'or=(' . implode(',', $parts) . ')';
        return $this;
    }

    public function order(string $column, bool $ascending = true): self
    {
        $this->order[] = $column . '.' . ($ascending ? 'asc' : 'desc');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function get(): array
    {
        $response = Database::rest('GET', $this->table, $this->buildQuery());
        $data = json_decode($response['body'], true);
        return is_array($data) ? $data : [];
    }

    public function first(): ?array
    {
        $this->limit(1);
        $rows = $this->get();
        return $rows[0] ?? null;
    }

    public function count(): int
    {
        $query = $this->buildQuery();
        $response = Database::rest('GET', $this->table, $query, null, ['count=exact'], ['Range: 0-0']);
        $range = $response['headers']['content-range'] ?? '';
        if (preg_match('#/(\d+)$#', $range, $m)) {
            return (int) $m[1];
        }
        $data = json_decode($response['body'], true);
        return is_array($data) ? count($data) : 0;
    }

    public function insert(array $row): array
    {
        $response = Database::rest('POST', $this->table, [], $row, ['return=representation']);
        $data = json_decode($response['body'], true);
        if (isset($data[0]) && is_array($data[0])) {
            return $data[0];
        }
        return is_array($data) ? $data : [];
    }

    public function insertMany(array $rows): array
    {
        if ($rows === []) {
            return [];
        }
        $response = Database::rest('POST', $this->table, [], $rows, ['return=representation']);
        $data = json_decode($response['body'], true);
        return is_array($data) ? $data : [];
    }

    public function update(array $row): array
    {
        $response = Database::rest('PATCH', $this->table, $this->buildQuery(false), $row, ['return=representation']);
        $data = json_decode($response['body'], true);
        return is_array($data) ? $data : [];
    }

    public function delete(): array
    {
        $response = Database::rest('DELETE', $this->table, $this->buildQuery(false), null, ['return=representation']);
        $data = json_decode($response['body'], true);
        return is_array($data) ? $data : [];
    }

    private function filter(string $column, string $op, mixed $value): self
    {
        $this->filters[] = $column . '=' . $op . '.' . $this->escape((string) $value);
        return $this;
    }

    private function escape(string $value): string
    {
        return rawurlencode($value);
    }

    /** @return list<string> */
    private function buildQuery(bool $includeSelect = true): array
    {
        $query = [];
        if ($includeSelect) {
            $query[] = 'select=' . rawurlencode($this->select);
        }
        foreach ($this->filters as $filter) {
            $query[] = $filter;
        }
        if ($this->order) {
            $query[] = 'order=' . rawurlencode(implode(',', $this->order));
        }
        if ($this->limit !== null) {
            $query[] = 'limit=' . $this->limit;
        }
        return $query;
    }
}

function file_url(?string $path): string
{
    return Database::fileUrl($path);
}
