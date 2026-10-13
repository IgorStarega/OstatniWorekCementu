<?php
// Panel klienta. Na tym etapie widok minimalny; edycja profilu w kolejnym kroku.
class ProfileController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index(): void
    {
        requireLogin();

        $pageTitle = 'Panel klienta';
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/profile/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
