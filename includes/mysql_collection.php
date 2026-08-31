<?php
declare(strict_types=1);

final class MysqlCollection
{
    public function __construct(private PDO $pdo, private string $table) {}

    public function findOne(array $filter = []): ?array
    {
        if ($this->table === 'settings') {
            $rows = $this->pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) return null;
            $result = ['_id' => 'app'];
            foreach ($rows as $row) $result[$row['setting_key']] = json_decode($row['setting_value'], true);
            return $result;
        }
        [$where, $params] = $this->where($filter);
        $statement = $this->pdo->prepare("SELECT * FROM `{$this->table}` {$where} LIMIT 1");
        $statement->execute($params);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->fromRow($row) : null;
    }

    public function find(array $filter = [], array $options = []): array
    {
        [$where, $params] = $this->where($filter);
        $order = '';
        if (!empty($options['sort'])) {
            $parts = [];
            foreach ($options['sort'] as $field => $direction) $parts[] = '`' . $this->column($field) . '` ' . ((int)$direction < 0 ? 'DESC' : 'ASC');
            $order = ' ORDER BY ' . implode(', ', $parts);
        }
        $limit = !empty($options['limit']) ? ' LIMIT ' . (int)$options['limit'] : '';
        $statement = $this->pdo->prepare("SELECT * FROM `{$this->table}` {$where}{$order}{$limit}");
        $statement->execute($params);
        return array_map(fn(array $row): array => $this->fromRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function countDocuments(array $filter = []): int
    {
        [$where, $params] = $this->where($filter);
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM `{$this->table}` {$where}");
        $statement->execute($params);
        return (int)$statement->fetchColumn();
    }

    public function insertOne(array $document): void
    {
        $row = $this->toRow($document);
        if (in_array($this->table, ['pets', 'gifts'], true) && !isset($row['id'])) $row['id'] = $this->uuid();
        if ($this->table === 'gifts') {
            $row['timeline_milestones'] ??= '[]';
            $row['scratch_coupons'] ??= '[]';
            $row['is_unlocked'] = $row['is_unlocked'] ?? 0;
        }
        $columns = array_keys($row);
        $placeholders = array_map(fn(string $column): string => ':' . $column, $columns);
        $statement = $this->pdo->prepare('INSERT INTO `' . $this->table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $placeholders) . ')');
        $statement->execute(array_combine($placeholders, array_values($row)));
    }

    public function updateOne(array $filter, array $update, array $options = []): MysqlUpdateResult
    {
        $set = $update['$set'] ?? [];
        if ($this->table === 'gifts' && isset($set['recipient_reply'])) {
            $reply = (array)$set['recipient_reply'];
            $set['recipient_reaction'] = $reply['reaction'] ?? null;
            $set['recipient_note'] = $reply['message'] ?? null;
            unset($set['recipient_reply']);
        }
        $row = $this->toRow($set);
        if ($this->table === 'settings' && isset($filter['_id'])) {
            $key = (string)$filter['_id'];
            foreach ($set as $field => $value) $this->pdo->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$field, json_encode($value)]);
            return new MysqlUpdateResult(1);
        }
        [$where, $params] = $this->where($filter);
        if (!$row) return new MysqlUpdateResult(0);
        $assignments = [];
        foreach ($row as $column => $value) { $parameter = 'set_' . $column; $assignments[] = "`{$column}` = :{$parameter}"; $params[$parameter] = $value; }
        $statement = $this->pdo->prepare("UPDATE `{$this->table}` SET " . implode(', ', $assignments) . " {$where}");
        $statement->execute($params);
        return new MysqlUpdateResult($statement->rowCount());
    }

    public function deleteOne(array $filter): void
    {
        [$where, $params] = $this->where($filter);
        $statement = $this->pdo->prepare("DELETE FROM `{$this->table}` {$where} LIMIT 1");
        $statement->execute($params);
    }

    private function where(array $filter): array
    {
        $clauses = []; $params = [];
        foreach ($filter as $field => $value) {
            if ($field === '$or') continue;
            $column = $this->column($field);
            if (is_array($value) && isset($value['$elemMatch'])) continue;
            $parameter = 'where_' . count($params);
            $clauses[] = "`{$column}` = :{$parameter}";
            $params[$parameter] = $this->encodeValue($column, $value);
        }
        return [$clauses ? 'WHERE ' . implode(' AND ', $clauses) : '', $params];
    }

    private function column(string $field): string
    {
        return match ($field) { '_id' => 'id', 'message' => $this->table === 'gifts' ? 'letter_content' : $field, 'image' => $this->table === 'gifts' ? 'polaroid_image_url' : $field, 'music' => $this->table === 'gifts' ? 'audio_bg_url' : $field, 'lock_hint' => $this->table === 'gifts' ? 'recipient_note' : $field, 'coupons' => $this->table === 'gifts' ? 'scratch_coupons' : $field, default => $field };
    }

    private function toRow(array $document): array
    {
        $row = [];
        foreach ($document as $field => $value) {
            if ($field === '_id') $field = 'id';
            if ($field === 'recipient_reply' && $this->table === 'gifts') continue;
            $field = $this->column($field);
            if ($field === 'created_at' && is_object($value)) $value = date('Y-m-d H:i:s');
            if (in_array($field, ['timeline_milestones', 'scratch_coupons', 'coupons', 'recipient_reply', 'accepted_formats', 'stat_deltas'], true)) $value = json_encode($value);
            if ($field === 'is_locked') { $field = 'is_unlocked'; $value = !$value; }
            if ($field === 'owner_id') continue;
            $row[$field] = $value;
        }
        return $row;
    }

    private function fromRow(array $row): array
    {
        if ($this->table === 'gifts') {
            $row['_id'] = $row['id']; $row['message'] = $row['letter_content']; $row['image'] = $row['polaroid_image_url']; $row['music'] = $row['audio_bg_url']; $row['music_url'] = $row['audio_bg_url']; $row['is_locked'] = !$row['is_unlocked']; $row['lock_hint'] = $row['recipient_note'];
            foreach (['timeline_milestones','scratch_coupons','coupons','recipient_reply','accepted_formats'] as $field) if (isset($row[$field])) $row[$field] = json_decode((string)$row[$field], true) ?: [];
            $row['coupons'] = $row['scratch_coupons'];
        } else $row['_id'] = $row['id'] ?? null;
        return $row;
    }

    private function encodeValue(string $column, mixed $value): mixed
    {
        if ($column === 'id' && is_object($value)) return (string)$value;
        if (in_array($column, ['timeline_milestones','scratch_coupons','recipient_reply','stat_deltas'], true)) return json_encode($value);
        return $value;
    }

    private function uuid(): string { return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535)); }
}

final class MysqlUpdateResult
{
    public function __construct(private int $modifiedCount) {}
    public function getModifiedCount(): int { return $this->modifiedCount; }
}
