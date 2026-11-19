<?php
// Kontroler strony głównej.
class HomeController
{
    public function index(array $config): void
    {
        $pdo = Database::connect($config['db']);

        $pageTitle = 'Strona główna';
        $appName = $config['app']['name'];

        // Strona główna pokazuje skrót realnej oferty: kategorie, liczniki
        // i kilka pierwszych aktywnych usług — bez duplikowania widoku oferty.
        $categoryList = (new ServiceCategory($pdo))->allWithActiveServices();
        $serviceList = (new Service($pdo))->allActive();
        $featuredServices = array_slice($serviceList, 0, 6);
        $employeeCount = (new Employee($pdo))->countActive();

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/home/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
