<?php
// Oferta publiczna: kategorie i usługi dostępne bez logowania.
class OfferController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // Lista kategorii i usług z opcjonalnym filtrem jednej kategorii.
    // Strona publiczna — celowo bez requireLogin(), gość też ma tu dostęp.
    public function index(): void
    {
        $pdo = Database::connect($this->config['db']);
        $categories = new ServiceCategory($pdo);
        $services = new Service($pdo);

        // Filtr kategorii musi być liczbą; cokolwiek innego ("abc",
        // tablica) traktujemy jak brak filtra i pokazujemy wszystko.
        $categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId < 1) {
            $categoryId = null;
        }

        $pageTitle = 'Oferta usług';
        $categoryList = $categories->allWithActiveServices();
        $serviceList = $services->allActive($categoryId);

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/offer/index.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    // Szczegóły jednej aktywnej usługi; nieaktywna lub nieistniejąca to 404.
    public function show(int $id): void
    {
        $pdo = Database::connect($this->config['db']);
        $service = (new Service($pdo))->findActive($id);

        if ($service === null) {
            http_response_code(404);
            $pageTitle = 'Nie znaleziono usługi';
            require BASE_PATH . '/app/views/layout/header.php';
            require BASE_PATH . '/app/views/errors/404.php';
            require BASE_PATH . '/app/views/layout/footer.php';
            return;
        }

        // Wykonawcy usługi pokazywani pod przyciskiem rezerwacji —
        // klient widzi od razu, kto może wykonać pracę.
        $employeeList = (new Employee($pdo))->forService((int) $service['id']);

        $pageTitle = $service['name'];
        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/offer/show.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }
}
