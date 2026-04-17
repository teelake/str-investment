<?php

declare(strict_types=1);

final class CustomerDocumentRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByCustomer(int $customerId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, customer_id, uploaded_by_user_id, original_name, storage_path, mime_type, size_bytes, created_at
             FROM customer_documents
             WHERE customer_id = :cid
             ORDER BY id DESC'
        );
        $stmt->execute([':cid' => $customerId]);
        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForCustomer(int $documentId, int $customerId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, customer_id, uploaded_by_user_id, original_name, storage_path, mime_type, size_bytes, created_at
             FROM customer_documents
             WHERE id = :id AND customer_id = :cid
             LIMIT 1'
        );
        $stmt->execute([':id' => $documentId, ':cid' => $customerId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(
        int $customerId,
        ?int $uploadedBy,
        string $originalName,
        string $storagePath,
        string $mime,
        int $sizeBytes
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO customer_documents (customer_id, uploaded_by_user_id, original_name, storage_path, mime_type, size_bytes, created_at)
             VALUES (:cid, :uid, :oname, :spath, :mime, :sz, NOW())'
        );
        $stmt->execute([
            ':cid' => $customerId,
            ':uid' => $uploadedBy,
            ':oname' => $originalName,
            ':spath' => $storagePath,
            ':mime' => $mime,
            ':sz' => $sizeBytes,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function delete(int $documentId, int $customerId): bool
    {
        $row = $this->findForCustomer($documentId, $customerId);
        if ($row === null) {
            return false;
        }
        $abs = CustomerDocumentStorage::absolutePathFromRelative((string) $row['storage_path']);
        if (is_file($abs)) {
            @unlink($abs);
        }
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM customer_documents WHERE id = :id AND customer_id = :cid');
        $stmt->execute([':id' => $documentId, ':cid' => $customerId]);
        return $stmt->rowCount() > 0;
    }
}
