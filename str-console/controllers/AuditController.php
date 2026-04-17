<?php

declare(strict_types=1);

final class AuditController extends BaseController
{
    public function index(): void
    {
        if (!str_console_database_ready()) {
            $this->render('audit/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 40],
                'entityTypes' => [],
                'filterType' => '',
                'dbError' => 'Database not configured.',
            ]);
            return;
        }

        $page = (int) Request::query('page', 1);
        $filterTypeRaw = trim((string) Request::query('type', ''));
        $filterForRepo = $filterTypeRaw !== '' ? $filterTypeRaw : null;

        try {
            $repo = new AuditLogRepository();
            $data = $repo->paginate($page, $filterForRepo);
            $types = $repo->distinctEntityTypes();
            $this->render('audit/index', [
                'pagination' => $data,
                'entityTypes' => $types,
                'filterType' => $filterTypeRaw,
                'dbError' => null,
            ]);
        } catch (Throwable) {
            $this->render('audit/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 40],
                'entityTypes' => [],
                'filterType' => '',
                'dbError' => 'Could not load audit log.',
            ]);
        }
    }
}
