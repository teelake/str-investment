<?php

declare(strict_types=1);

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->render('dashboard/index', [
            'user' => ConsoleAuth::user(),
        ]);
    }
}
