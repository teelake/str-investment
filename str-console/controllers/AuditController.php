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
                'from' => '',
                'to' => '',
                'dateFromInvalid' => false,
                'dateToInvalid' => false,
                'dbError' => 'Database not configured.',
            ]);
            return;
        }

        $page = Pagination::sanitizeRequestedPage(Request::query('page', 1));
        $filterTypeRaw = trim((string) Request::query('type', ''));
        $filterForRepo = $filterTypeRaw !== '' ? $filterTypeRaw : null;
        $fromRaw = trim((string) Request::query('from', ''));
        $toRaw = trim((string) Request::query('to', ''));
        $fromNorm = ReportRepository::normalizeDate($fromRaw);
        $toNorm = ReportRepository::normalizeDate($toRaw);

        try {
            $repo = new AuditLogRepository();
            $actorRole = (string) (ConsoleAuth::user()['role'] ?? '');
            $hideSysAdminActors = !str_console_may_view_system_admin_user_records($actorRole);
            $data = $repo->paginate($page, $filterForRepo, $fromNorm, $toNorm, $hideSysAdminActors);
            $types = $repo->distinctEntityTypes();
            $this->render('audit/index', [
                'pagination' => $data,
                'entityTypes' => $types,
                'filterType' => $filterTypeRaw,
                'from' => $fromRaw,
                'to' => $toRaw,
                'dateFromInvalid' => $fromRaw !== '' && $fromNorm === null,
                'dateToInvalid' => $toRaw !== '' && $toNorm === null,
                'dbError' => null,
            ]);
        } catch (Throwable) {
            $this->render('audit/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 40],
                'entityTypes' => [],
                'filterType' => '',
                'from' => '',
                'to' => '',
                'dateFromInvalid' => false,
                'dateToInvalid' => false,
                'dbError' => 'Could not load audit log.',
            ]);
        }
    }
}
