<?php
// Kontroler strony głównej.
class HomeController
{
    public function index(array $config): void
    {
        $pageTitle = 'Strona główna';
        $appName = $config['app']['name'];
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/home/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
