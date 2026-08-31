<?php
declare(strict_types=1);

final class SupabaseClient
{
    private string $url;
    private string $key;

    public function __construct(?string $url = null, ?string $key = null)
    {
        $this->url = rtrim($url ?: (function_exists('env_get') ? (env_get('SUPABASE_URL') ?: '') : (getenv('SUPABASE_URL') ?: '')), '/');
        $this->key = $key ?: (function_exists('env_get') ? (env_get('SUPABASE_SERVICE_ROLE_KEY') ?: env_get('SUPABASE_ANON_KEY') ?: '') : (getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_ANON_KEY') ?: ''));
        if ($this->url === '' || $this->key === '') {
            throw new RuntimeException('Supabase is not configured. Set SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY.');
        }
    }

    public function select(string $table, array $query = []): array
    {
        $response = $this->request('GET', $table, $query);
        return is_array($response) ? $response : [];
    }

    public function insert(string $table, array $row): array
    {
        $response = $this->request('POST', $table, [], $row, ['Prefer: return=representation']);
        return is_array($response) ? $response : [];
    }

    public function update(string $table, array $filters, array $values): array
    {
        $response = $this->request('PATCH', $table, $filters, $values, ['Prefer: return=representation']);
        return is_array($response) ? $response : [];
    }

    private function request(string $method, string $table, array $query = [], ?array $body = null, array $extraHeaders = []): mixed
    {
        $url = $this->url . '/rest/v1/' . rawurlencode($table);
        if ($query) {
            $parts = [];
            foreach ($query as $key => $value) {
                $parts[] = rawurlencode((string)$key) . '=' . rawurlencode((string)$value);
            }
            $url .= '?' . implode('&', $parts);
        }
        $handle = curl_init($url);
        if ($handle === false) throw new RuntimeException('Unable to initialize HTTP client.');
        $headers = array_merge([
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Accept: application/json',
        ], $extraHeaders);
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($body !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        $raw = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if ($raw === false || $error !== '') throw new RuntimeException('Supabase request failed.');
        $decoded = $raw === '' ? [] : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) ? (string)($decoded['message'] ?? $decoded['hint'] ?? 'Supabase request rejected.') : 'Supabase request rejected.';
            throw new RuntimeException($message);
        }
        return $decoded;
    }
}
