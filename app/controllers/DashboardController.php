<?php
// Panel pracownika i administratora. Na tym etapie widok minimalny.
class DashboardController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        requireRole('pracownik', 'admin');

        $pageTitle = 'Panel zarządzania';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/dashboard/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
