<?php
declare(strict_types=1);

require_once __DIR__ . '/supabase_client.php';

final class PetService
{
    public function __construct(private ?\SupabaseClient $supabase = null)
    {
        $url = getenv('SUPABASE_URL') ?: '';
        $key = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_ANON_KEY') ?: '';
        if ($this->supabase === null && ($url === '' || str_contains($url, 'YOUR_PROJECT_REF') || $key === '' || str_contains($key, 'REPLACE_WITH'))) $this->supabase = null;
    }

    public function all(): array
    {
        $rows = $this->supabase ? $this->supabase->select('pets', ['select' => '*', 'order' => 'name.asc']) : col_pets()->find([], ['sort' => ['name' => 1]]);
        return array_map(fn(array $pet): array => $this->withDecay($pet), $rows);
    }

    public function act(string $petId, string $action): array
    {
        $pets = $this->supabase ? $this->supabase->select('pets', ['id' => 'eq.' . $petId, 'select' => '*']) : (($pet = col_pets()->findOne(['id' => $petId])) ? [$pet] : []);
        if (!$pets) throw new InvalidArgumentException('Pet not found.');
        $pet = $this->withDecay($pets[0]);
        $deltas = match ($action) {
            'feed' => ['hunger' => 25, 'happiness' => 10, 'exp' => 15, 'last_fed' => gmdate('c')],
            'bath' => ['hygiene' => 30, 'happiness' => 5, 'exp' => 10, 'last_bathed' => gmdate('c')],
            'pet' => ['happiness' => 20, 'energy' => 5, 'exp' => 10, 'last_petted' => gmdate('c')],
            'sleep' => ['energy' => 100, 'happiness' => 5, 'exp' => 5, 'last_slept' => gmdate('c')],
            default => throw new InvalidArgumentException('Unsupported pet action.'),
        };
        foreach (['hunger', 'hygiene', 'happiness', 'energy'] as $stat) {
            if (isset($deltas[$stat])) $deltas[$stat] = min(100, max(0, (int)$pet[$stat] + (int)$deltas[$stat]));
        }
        $deltas['exp'] = (int)$pet['exp'] + (int)$deltas['exp'];
        $deltas['level'] = (int)$pet['level'];
        while ($deltas['exp'] >= $deltas['level'] * 100) { $deltas['exp'] -= $deltas['level'] * 100; $deltas['level']++; }
        $updated = array_merge($pet, $deltas);
        $updated['mood'] = $this->mood($updated);
        $values = array_intersect_key($updated, array_flip(['hunger','hygiene','happiness','energy','exp','level','mood','last_fed','last_bathed','last_petted','last_slept']));
        $saved = $this->supabase ? $this->supabase->update('pets', ['id' => 'eq.' . $petId], $values) : $this->saveLocal($petId, $values, $deltas, $action);
        return $saved[0] ?? $updated;
    }

    private function saveLocal(string $petId, array $values, array $deltas, string $action): array
    {
        col_pets()->updateOne(['id' => $petId], ['$set' => $values]);
        try { (new \MysqlCollection(db(), 'pet_logs'))->insertOne(['pet_id' => $petId, 'action_type' => $action, 'stat_deltas' => $deltas, 'actor' => 'partner']); } catch (Throwable) { /* Activity logging must not block pet care. */ }
        return array_merge(['id' => $petId], $values);
    }

    private function withDecay(array $pet): array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $hungerElapsed = $this->hoursSince($pet['last_fed'] ?? null, $now);
        $hygieneElapsed = $this->hoursSince($pet['last_bathed'] ?? null, $now);
        $pet['hunger'] = max(0, (int)$pet['hunger'] - (int)floor($hungerElapsed / 2) * 5);
        $pet['hygiene'] = max(0, (int)$pet['hygiene'] - (int)floor($hygieneElapsed / 3) * 4);
        $pet['happiness'] = max(0, (int)$pet['happiness'] - (($pet['hunger'] < 50 || $pet['hygiene'] < 50) ? 10 : 0) - ((($this->hoursSince($pet['last_petted'] ?? null, $now)) > 6) ? 5 : 0));
        $pet['mood'] = $this->mood($pet);
        return $pet;
    }

    private function hoursSince(?string $value, DateTimeImmutable $now): float
    {
        if (!$value) return 0;
        try { return max(0, ($now->getTimestamp() - (new DateTimeImmutable($value))->getTimestamp()) / 3600); } catch (Throwable) { return 0; }
    }

    private function mood(array $pet): string
    {
        if ((int)$pet['hunger'] < 30) return 'hungry';
        if ((int)$pet['hygiene'] < 30) return 'dirty';
        if (min((int)$pet['hunger'], (int)$pet['hygiene'], (int)$pet['happiness'], (int)$pet['energy']) < 30) return 'sad';
        if (min((int)$pet['hunger'], (int)$pet['hygiene'], (int)$pet['happiness'], (int)$pet['energy']) > 80) return 'ecstatic';
        if ((int)$pet['energy'] < 35) return 'sleepy';
        return 'happy';
    }
}
