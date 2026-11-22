<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Core\Validator;

final class ContactMessage extends Model
{
    protected string $table = 'contact_messages';
    protected array $fillable = ['name', 'contact', 'subject', 'message', 'ip_hash', 'user_agent', 'status'];

    /** @return array<int, array<string, mixed>> */
    public function recent(?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $limit  = max(1, min(200, $limit));
        $offset = max(0, $offset);

        if ($status !== null && in_array($status, ['new', 'read', 'archived'], true)) {
            return Database::all(
                'SELECT * FROM contact_messages WHERE status = ?
                 ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset,
                [$status]
            );
        }

        return Database::all(
            'SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset
        );
    }

    public function unreadCount(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM contact_messages WHERE status = \'new\'');
    }

    public function mark(int $id, string $status): bool
    {
        if (!in_array($status, ['new', 'read', 'archived'], true)) {
            return false;
        }

        Database::run('UPDATE contact_messages SET status = ? WHERE id = ?', [$status, $id]);

        return true;
    }

    /** Store a message from the public form, already validated by the caller. */
    public function store(array $input, string $ipHash, string $userAgent): int
    {
        return $this->create([
            'name'       => mb_substr(Validator::cleanText($input['name']), 0, 120),
            'contact'    => mb_substr(trim((string) $input['contact']), 0, 190),
            'subject'    => mb_substr(Validator::cleanText($input['subject'] ?? ''), 0, 120),
            'message'    => mb_substr(Validator::cleanText($input['message']), 0, 4000),
            'ip_hash'    => $ipHash,
            'user_agent' => mb_substr($userAgent, 0, 255),
            'status'     => 'new',
        ]);
    }
}
