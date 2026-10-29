<?php
// Panel administratora: wspólny layout, moduły CRUD i przypisania usług.
class AdminController
{
    private $config;

    // Konstruktor nie jest trasą panelu — requireRole pilnuje każdej metody publicznej.
    function __construct(array $config)
    {
        $this->config = $config;
    }

    public function dashboard(): void
    {
        requireRole('admin');

        $stats = new Stats($this->database());
        $reservations = new Reservation($this->database());

        $this->render('dashboard', 'Dashboard', [
            'counters' => $stats->counters(),
            'upcoming' => $reservations->upcoming(5),
            'flash'    => $this->takeFlash(),
        ]);
    }

    // --- Kategorie usług ---------------------------------------------------

    public function categories(): void
    {
        requireRole('admin');

        $categories = new ServiceCategory($this->database());
        $errors = [];
        $name = '';
        $description = '';
        $editingId = 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $action = postString('action');

            if ($action === 'delete') {
                $id = $this->postId('id');
                $category = $id > 0 ? $categories->find($id) : null;

                if ($category === null) {
                    $errors[] = 'Nie znaleziono kategorii do usunięcia.';
                } else {
                    $servicesCount = $categories->countServices($id);
                    if ($servicesCount > 0) {
                        $this->flash('alert', 'Nie można usunąć: kategoria ma ' . $servicesCount . ' usług.');
                    } else {
                        $categories->delete($id);
                        $this->flash('status', 'Kategoria została usunięta.');
                    }
                }

                if ($errors === []) {
                    $this->redirect('admin-categories');
                }
            } elseif ($action === 'create' || $action === 'update') {
                $editingId = $action === 'update' ? $this->postId('id') : 0;
                $name = trim(postString('name'));
                $description = trim(postString('description'));

                $errors = array_merge($errors, collectErrors([
                    validationCategoryName($name),
                ]));

                if ($action === 'update' && ($editingId < 1 || $categories->find($editingId) === null)) {
                    $errors[] = 'Nie znaleziono kategorii do edycji.';
                }

                if ($errors === []) {
                    if ($action === 'create') {
                        $categories->create($name, $description);
                        $this->flash('status', 'Kategoria została dodana.');
                    } else {
                        $categories->update($editingId, $name, $description);
                        $this->flash('status', 'Kategoria została zaktualizowana.');
                    }

                    $this->redirect('admin-categories');
                }
            } else {
                $errors[] = 'Nieznana akcja formularza.';
            }
        } else {
            $editingId = $this->getInt('edit');
            $editRecord = $editingId > 0 ? $categories->find($editingId) : null;
            if ($editRecord !== null) {
                $name = (string) $editRecord['name'];
                $description = (string) ($editRecord['description'] ?? '');
            } else {
                $editingId = 0;
            }
        }

        $this->render('categories', 'Kategorie', [
            'categories' => $categories->all(),
            'errors'     => $errors,
            'flash'      => $this->takeFlash(),
            'name'       => $name,
            'description' => $description,
            'editingId'  => $editingId,
        ]);
    }

    // --- Usługi ------------------------------------------------------------

    public function services(): void
    {
        requireRole('admin');

        $services = new Service($this->database());
        $categories = new ServiceCategory($this->database());
        $errors = [];
        $name = '';
        $description = '';
        $categoryId = 0;
        $durationMinutes = '';
        $price = '';
        $isActive = true;
        $editingId = 0;
        $categoryList = $categories->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $action = postString('action');

            if ($action === 'delete') {
                $id = $this->postId('id');
                $service = $id > 0 ? $services->find($id) : null;

                if ($service === null) {
                    $errors[] = 'Nie znaleziono usługi do usunięcia.';
                } else {
                    $reservationsCount = $services->countReservations($id);
                    if ($reservationsCount > 0) {
                        $this->flash('alert', 'Nie można usunąć: usługa ma ' . $reservationsCount . ' rezerwacji.');
                    } else {
                        $services->delete($id);
                        $this->flash('status', 'Usługa została usunięta.');
                    }
                }

                if ($errors === []) {
                    $this->redirect('admin-services');
                }
            } elseif ($action === 'toggle') {
                $id = $this->postId('id');
                if ($id < 1 || $services->find($id) === null) {
                    $errors[] = 'Nie znaleziono usługi.';
                } else {
                    $services->toggleActive($id);
                    $this->flash('status', 'Aktywność usługi została zmieniona.');
                    $this->redirect('admin-services');
                }
            } elseif ($action === 'create' || $action === 'update') {
                $editingId = $action === 'update' ? $this->postId('id') : 0;
                $name = trim(postString('name'));
                $description = trim(postString('description'));
                $categoryId = $this->postId('category_id');
                $durationMinutes = trim(postString('duration_minutes'));
                $price = trim(postString('price'));
                $isActive = isset($_POST['is_active']);

                $errors = array_merge($errors, collectErrors([
                    validationServiceName($name),
                    validationDurationMinutes($durationMinutes),
                    validationPrice($price),
                ]));

                if ($categoryId < 1 || $categories->find($categoryId) === null) {
                    $errors[] = 'Wybierz istniejącą kategorię usługi.';
                }

                if ($action === 'update' && ($editingId < 1 || $services->find($editingId) === null)) {
                    $errors[] = 'Nie znaleziono usługi do edycji.';
                }

                if ($errors === []) {
                    $data = [
                        'category_id'      => $categoryId,
                        'name'             => $name,
                        'description'      => $description,
                        'duration_minutes' => (int) $durationMinutes,
                        'price'            => priceToDecimal($price),
                        'is_active'        => $isActive ? 1 : 0,
                    ];

                    if ($action === 'create') {
                        $services->create($data);
                        $this->flash('status', 'Usługa została dodana.');
                    } else {
                        $services->update($editingId, $data);
                        $this->flash('status', 'Usługa została zaktualizowana.');
                    }

                    $this->redirect('admin-services');
                }
            } else {
                $errors[] = 'Nieznana akcja formularza.';
            }
        } else {
            $editingId = $this->getInt('edit');
            $editRecord = $editingId > 0 ? $services->find($editingId) : null;
            if ($editRecord !== null) {
                $name = (string) $editRecord['name'];
                $description = (string) ($editRecord['description'] ?? '');
                $categoryId = (int) $editRecord['category_id'];
                $durationMinutes = (string) $editRecord['duration_minutes'];
                $price = (string) $editRecord['price'];
                $isActive = (int) $editRecord['is_active'] === 1;
            } else {
                $editingId = 0;
            }
        }

        $this->render('services', 'Usługi', [
            'services'       => $services->all(),
            'categories'     => $categoryList,
            'errors'         => $errors,
            'flash'          => $this->takeFlash(),
            'name'           => $name,
            'description'    => $description,
            'categoryId'     => $categoryId,
            'durationMinutes' => $durationMinutes,
            'price'          => $price,
            'isActive'       => $isActive,
            'editingId'      => $editingId,
        ]);
    }

    // --- Pracownicy --------------------------------------------------------

    public function employees(): void
    {
        requireRole('admin');

        $employees = new Employee($this->database());
        $users = userRepository($this->config);
        $errors = [];
        $firstName = '';
        $lastName = '';
        $email = '';
        $phone = '';
        $description = '';
        $password = '';
        $editingId = 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $action = postString('action');

            if ($action === 'delete') {
                $id = $this->postId('id');
                $employee = $id > 0 ? $employees->find($id) : null;

                if ($employee === null) {
                    $errors[] = 'Nie znaleziono pracownika do usunięcia.';
                } else {
                    $reservationsCount = $employees->countReservations($id);
                    if ($reservationsCount > 0) {
                        $this->flash('alert', 'Nie można usunąć: pracownik ma ' . $reservationsCount . ' rezerwacji.');
                    } else {
                        $employees->delete($id);
                        $this->flash('status', 'Pracownik został usunięty.');
                    }
                }

                if ($errors === []) {
                    $this->redirect('admin-employees');
                }
            } elseif ($action === 'toggle') {
                $id = $this->postId('id');
                if ($id < 1 || $employees->find($id) === null) {
                    $errors[] = 'Nie znaleziono pracownika.';
                } else {
                    $employees->toggleActive($id);
                    $this->flash('status', 'Aktywność pracownika została zmieniona.');
                    $this->redirect('admin-employees');
                }
            } elseif ($action === 'create' || $action === 'update') {
                $editingId = $action === 'update' ? $this->postId('id') : 0;
                $firstName = trim(postString('first_name'));
                $lastName = trim(postString('last_name'));
                $email = mb_strtolower(trim(postString('email')));
                $phone = trim(postString('phone'));
                $description = trim(postString('description'));
                $password = postString('password');
                $employee = $editingId > 0 ? $employees->find($editingId) : null;

                $errors = array_merge($errors, collectErrors([
                    validationFirstName($firstName),
                    validationLastName($lastName),
                    validationEmail($email),
                    validationPhone($phone),
                ]));

                if ($action === 'create') {
                    $errors = array_merge($errors, collectErrors([validationPassword($password)]));
                }

                if ($action === 'update' && $employee === null) {
                    $errors[] = 'Nie znaleziono pracownika do edycji.';
                }

                // Duplikat e-maila sprawdzamy przed INSERT-em; przy edycji pomijamy własne konto.
                $existingUser = $errors === [] ? $users->findByEmail($email) : null;
                if ($existingUser !== null
                    && ($employee === null || (int) $existingUser['id'] !== (int) $employee['user_id'])) {
                    $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                }

                if ($errors === []) {
                    try {
                        if ($action === 'create') {
                            $employees->createWithUser([
                                'first_name' => $firstName,
                                'last_name'  => $lastName,
                                'email'      => $email,
                                'phone'      => $phone,
                                'password'   => password_hash($password, PASSWORD_DEFAULT),
                            ], $description);
                            $this->flash('status', 'Pracownik został dodany.');
                        } else {
                            $employees->update($editingId, [
                                'first_name' => $firstName,
                                'last_name'  => $lastName,
                                'email'      => $email,
                                'phone'      => $phone,
                            ], $description);
                            $this->flash('status', 'Dane pracownika zostały zaktualizowane.');
                        }

                        $this->redirect('admin-employees');
                    } catch (PDOException $exception) {
                        if ((string) $exception->getCode() === '23000') {
                            $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                        } else {
                            error_log('Błąd zapisu pracownika: ' . $exception->getMessage());
                            $errors[] = 'Nie udało się zapisać pracownika. Spróbuj ponownie.';
                        }
                    } catch (Throwable $exception) {
                        error_log('Błąd zapisu pracownika: ' . $exception->getMessage());
                        $errors[] = 'Nie udało się zapisać pracownika. Spróbuj ponownie.';
                    }
                }
            } else {
                $errors[] = 'Nieznana akcja formularza.';
            }
        } else {
            $editingId = $this->getInt('edit');
            $editRecord = $editingId > 0 ? $employees->find($editingId) : null;
            if ($editRecord !== null) {
                $firstName = (string) $editRecord['first_name'];
                $lastName = (string) $editRecord['last_name'];
                $email = (string) $editRecord['email'];
                $phone = (string) ($editRecord['phone'] ?? '');
                $description = (string) ($editRecord['description'] ?? '');
            } else {
                $editingId = 0;
            }
        }

        $this->render('employees', 'Pracownicy', [
            'employees'   => $employees->all(),
            'errors'      => $errors,
            'flash'       => $this->takeFlash(),
            'firstName'   => $firstName,
            'lastName'    => $lastName,
            'email'       => $email,
            'phone'       => $phone,
            'description' => $description,
            'editingId'   => $editingId,
        ]);
    }

    // --- Przypisywanie usług do pracowników (N:M) --------------------------

    public function employeeServices(): void
    {
        requireRole('admin');

        $employeesModel = new Employee($this->database());
        $servicesModel = new Service($this->database());
        $assignments = new EmployeeService($this->database());
        $employees = $employeesModel->all();
        $services = $servicesModel->all();
        $errors = [];

        $selectedEmployeeId = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $this->postId('employee_id')
            : $this->getInt('employee_id');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            if (postString('action') !== 'sync') {
                $errors[] = 'Nieznana akcja formularza.';
            }

            $employee = $selectedEmployeeId > 0 ? $employeesModel->find($selectedEmployeeId) : null;
            if ($employee === null) {
                $errors[] = 'Wybierz istniejącego pracownika.';
            }

            // Lista dozwolonych ID pochodzi z bazy, nie z formularza.
            $allowedServiceIds = array_map('intval', array_column($services, 'id'));
            $submitted = $_POST['service_ids'] ?? [];
            $serviceIds = [];

            if ($submitted !== [] && !is_array($submitted)) {
                $errors[] = 'Nieprawidłowy format listy usług.';
            } else {
                foreach ($submitted as $serviceId) {
                    if (!is_string($serviceId) || !ctype_digit($serviceId)) {
                        $errors[] = 'Nieprawidłowe przypisanie usługi.';
                        break;
                    }

                    $serviceId = (int) $serviceId;
                    if (!in_array($serviceId, $allowedServiceIds, true)) {
                        $errors[] = 'Wybrana usługa nie istnieje.';
                        break;
                    }

                    $serviceIds[] = $serviceId;
                }
            }

            if ($errors === []) {
                try {
                    $assignments->syncForEmployee($selectedEmployeeId, $serviceIds);
                    $this->flash('status', 'Przypisania usług zostały zapisane.');
                } catch (Throwable $exception) {
                    error_log('Błąd zapisu przypisań usług: ' . $exception->getMessage());
                    $this->flash('alert', 'Nie udało się zapisać przypisań. Spróbuj ponownie.');
                }

                $this->redirect('admin-employee-services&employee_id=' . $selectedEmployeeId);
            }
        } else {
            if ($selectedEmployeeId < 1 && $employees !== []) {
                $selectedEmployeeId = (int) $employees[0]['id'];
            }
        }

        $selectedEmployee = $selectedEmployeeId > 0 ? $employeesModel->find($selectedEmployeeId) : null;
        $assignedServiceIds = $selectedEmployee !== null
            ? $assignments->serviceIdsForEmployee($selectedEmployeeId)
            : [];

        $this->render('employee_services', 'Przypisania usług', [
            'employees'          => $employees,
            'services'           => $services,
            'errors'             => $errors,
            'flash'              => $this->takeFlash(),
            'selectedEmployeeId' => $selectedEmployeeId,
            'selectedEmployee'   => $selectedEmployee,
            'assignedServiceIds' => $assignedServiceIds,
        ]);
    }

    // --- Dostępność pracowników --------------------------------------------

    public function availability(): void
    {
        requireRole('admin');

        $employeesModel = new Employee($this->database());
        $availabilityModel = new EmployeeAvailability($this->database());
        $employees = $employeesModel->all();
        $errors = [];
        $formDay = 0;
        $formStart = '';
        $formEnd = '';

        $selectedEmployeeId = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $this->postId('employee_id')
            : $this->getInt('employee_id');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $action = postString('action');

            $employee = $selectedEmployeeId > 0 ? $employeesModel->find($selectedEmployeeId) : null;
            if ($employee === null) {
                $errors[] = 'Wybierz istniejącego pracownika.';
            }

            if ($action === 'upsert') {
                $formDay = $this->postId('day_of_week');
                $formStart = trim(postString('start_time'));
                $formEnd = trim(postString('end_time'));

                if ($formDay < 1 || $formDay > 7) {
                    $errors[] = 'Wybierz dzień tygodnia od 1 (poniedziałek) do 7 (niedziela).';
                }

                $startTime = $this->normalizeTime($formStart);
                $endTime = $this->normalizeTime($formEnd);

                if ($startTime === null || $endTime === null) {
                    $errors[] = 'Podaj poprawne godziny w formacie HH:MM lub HH:MM:SS.';
                } elseif ($startTime >= $endTime) {
                    $errors[] = 'Godzina rozpoczęcia musi być wcześniejsza niż godzina zakończenia.';
                }

                if ($errors === []) {
                    try {
                        $availabilityModel->upsert($selectedEmployeeId, $formDay, $startTime, $endTime);
                        $this->flash('status', 'Dostępność pracownika została zapisana.');
                        $this->redirect('admin-availability', ['employee_id' => $selectedEmployeeId]);
                    } catch (Throwable $exception) {
                        error_log('Błąd zapisu dostępności: ' . $exception->getMessage());
                        $errors[] = 'Nie udało się zapisać dostępności. Spróbuj ponownie.';
                    }
                }
            } elseif ($action === 'delete') {
                $formDay = $this->postId('day_of_week');

                if ($formDay < 1 || $formDay > 7) {
                    $errors[] = 'Wybierz dzień tygodnia od 1 (poniedziałek) do 7 (niedziela).';
                }

                if ($errors === []) {
                    try {
                        $availabilityModel->delete($selectedEmployeeId, $formDay);
                        $this->flash('status', 'Okno dostępności zostało usunięte.');
                        $this->redirect('admin-availability', ['employee_id' => $selectedEmployeeId]);
                    } catch (Throwable $exception) {
                        error_log('Błąd usuwania dostępności: ' . $exception->getMessage());
                        $errors[] = 'Nie udało się usunąć dostępności. Spróbuj ponownie.';
                    }
                }
            } else {
                $errors[] = 'Nieznana akcja formularza.';
            }
        } else {
            if ($selectedEmployeeId < 1 && $employees !== []) {
                $selectedEmployeeId = (int) $employees[0]['id'];
            }
        }

        $selectedEmployee = $selectedEmployeeId > 0 ? $employeesModel->find($selectedEmployeeId) : null;
        $availability = $selectedEmployee !== null
            ? $availabilityModel->forEmployee($selectedEmployeeId)
            : [];

        $this->render('availability', 'Dostępność', [
            'employees'          => $employees,
            'errors'             => $errors,
            'flash'              => $this->takeFlash(),
            'selectedEmployeeId' => $selectedEmployeeId,
            'selectedEmployee'   => $selectedEmployee,
            'availability'       => $availability,
            'formDay'            => $formDay,
            'formStart'          => $formStart,
            'formEnd'            => $formEnd,
        ]);
    }

    // --- Użytkownicy -------------------------------------------------------

    public function users(): void
    {
        requireRole('admin');

        $usersModel = new User($this->database());

        $role = $this->getString('role');
        if (!in_array($role, User::ROLES, true)) {
            $role = null;
        }

        $activeRaw = $this->getString('active');
        $active = $activeRaw === '0' ? 0 : ($activeRaw === '1' ? 1 : null);

        $preservedFilters = [];
        if ($role !== null) {
            $preservedFilters['role'] = $role;
        }
        if ($active !== null) {
            $preservedFilters['active'] = $active;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));
            $action = postString('action');
            $id = $this->postId('id');

            // Ochrona własnego konta: administrator nie może odebrać sobie uprawnień
            // ani zablokować się przed dalszą pracą w panelu.
            if ($id > 0 && $id === currentUserId()) {
                $this->flash('alert', 'Nie możesz zmienić roli ani zablokować własnego konta.');
                $this->redirect('admin-users', $preservedFilters);
            }

            $target = $id > 0 ? $usersModel->findById($id) : null;
            if ($target === null) {
                $this->flash('alert', 'Nie znaleziono użytkownika.');
                $this->redirect('admin-users', $preservedFilters);
            }

            if ($action === 'role') {
                $newRole = postString('role');

                if (!in_array($newRole, User::ROLES, true)) {
                    $this->flash('alert', 'Nieprawidłowa rola użytkownika.');
                } elseif ($usersModel->updateRole($id, $newRole)) {
                    $this->flash('status', 'Rola użytkownika została zmieniona.');
                } else {
                    $this->flash('alert', 'Nie udało się zmienić roli użytkownika.');
                }

                $this->redirect('admin-users', $preservedFilters);
            }

            if ($action === 'toggle') {
                $isActive = (int) $target['is_active'] === 1;
                $usersModel->setActive($id, !$isActive);
                $this->flash(
                    'status',
                    $isActive ? 'Konto użytkownika zostało zablokowane.' : 'Konto użytkownika zostało odblokowane.'
                );

                $this->redirect('admin-users', $preservedFilters);
            }

            $this->flash('alert', 'Nieznana akcja formularza.');
            $this->redirect('admin-users', $preservedFilters);
        }

        $this->render('users', 'Użytkownicy', [
            'users'        => $usersModel->allWithFilters($role, $active),
            'roleFilter'   => $role,
            'activeFilter' => $active,
            'errors'       => [],
            'flash'        => $this->takeFlash(),
        ]);
    }

    public function reservations(): void
    {
        requireRole('admin');

        $model = new Reservation($this->database());
        $employeesModel = new Employee($this->database());
        $errors = [];
        $filters = $this->reservationFilters();

        // Filtry odtwarzamy po zmianie statusu, żeby redirect wracał na tę samą listę.
        $preservedFilters = [];
        foreach (['date_from', 'date_to', 'status', 'employee_id'] as $key) {
            if (isset($filters[$key])) {
                $preservedFilters[$key] = $filters[$key];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify(postString('csrf_token'));

            if (postString('action') !== 'status') {
                $errors[] = 'Nieznana akcja formularza.';
            } else {
                $id = $this->postId('id');
                $newStatus = postString('status');

                if ($id < 1 || $model->find($id) === null) {
                    $this->flash('alert', 'Nie znaleziono rezerwacji.');
                } elseif (!$model->updateStatus($id, $newStatus)) {
                    $this->flash('alert', 'Nieprawidłowy status.');
                } else {
                    $this->flash('status', 'Status rezerwacji został zmieniony.');
                }

                $this->redirect('admin-reservations', $preservedFilters);
            }
        }

        $reservation = null;
        $id = $this->getInt('id');

        if ($id > 0) {
            $reservation = $model->find($id);

            if ($reservation === null) {
                $this->flash('alert', 'Nie znaleziono rezerwacji.');
                $this->redirect('admin-reservations', $preservedFilters);
            }
        }

        $this->render('reservations', 'Rezerwacje', [
            'reservations' => $reservation === null ? $model->list($filters) : [],
            'reservation'  => $reservation,
            'filters'      => $filters,
            'employees'    => $employeesModel->all(),
            'statuses'     => Reservation::STATUSES,
            'errors'       => $errors,
            'flash'        => $this->takeFlash(),
        ]);
    }

    // --- Pomocnicze --------------------------------------------------------

    private function database(): PDO
    {
        return Database::connect($this->config['db']);
    }

    // Renderuje widok modułu w ramce layoutu administratora.
    private function render(string $view, string $title, array $data = []): void
    {
        if (!preg_match('/^[a-z_]+$/', $view)) {
            throw new RuntimeException('Nieprawidłowa nazwa widoku administracyjnego.');
        }

        $pageTitle = $title;
        $pageClass = 'admin-main';
        $adminView = BASE_PATH . '/app/views/admin/' . $view . '.php';
        $navKey = $view === 'employee_services' ? 'admin-employees' : 'admin-' . str_replace('_', '-', $view);

        extract($data, EXTR_SKIP);

        require BASE_PATH . '/app/views/layout/header.php';
        require BASE_PATH . '/app/views/layout/admin.php';
        require BASE_PATH . '/app/views/layout/footer.php';
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['admin_flash'] = [
            'type'    => $type === 'status' ? 'status' : 'alert',
            'message' => $message,
        ];
    }

    private function takeFlash(): ?array
    {
        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        if (!is_array($flash)
            || !in_array($flash['type'] ?? '', ['status', 'alert'], true)
            || !is_string($flash['message'] ?? null)) {
            return null;
        }

        return $flash;
    }

    private function redirect(string $target, array $query = []): void
    {
        $url = '/?page=' . $target;

        if ($query !== []) {
            $url .= '&' . http_build_query($query);
        }

        header('Location: ' . $url);
        exit;
    }

    // ID z POST — wyłącznie dodatnia liczba całkowita.
    private function postId(string $key): int
    {
        $value = postString($key);

        return ctype_digit($value) ? (int) $value : 0;
    }

    // ID z GET — tablice i inne typy są ignorowane.
    private function getInt(string $key): int
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    // Tekst z GET — tablice i inne typy traktujemy jak brak wartości.
    private function getString(string $key): string
    {
        return is_string($_GET[$key] ?? null) ? $_GET[$key] : '';
    }

    // Normalizuje HH:MM lub HH:MM:SS do HH:MM:SS; null dla niepoprawnej godziny.
    private function normalizeTime(string $value): ?string
    {
        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            $format = 'H:i';
        } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            $format = 'H:i:s';
        } else {
            return null;
        }

        $time = DateTime::createFromFormat('!' . $format, $value);
        $parseErrors = DateTime::getLastErrors();

        if ($time === false
            || ($parseErrors !== false
                && ($parseErrors['warning_count'] > 0 || $parseErrors['error_count'] > 0))) {
            return null;
        }

        return $time->format('H:i:s');
    }

    // Filtry listy rezerwacji: każda wartość jest sprawdzana przed użyciem w modelu.
    private function reservationFilters(): array
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        $filters = [];

        foreach (['date_from', 'date_to'] as $key) {
            $value = $isPost ? postString($key) : $this->getString($key);

            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts) === 1
                && checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                $filters[$key] = $value;
            }
        }

        // W POST filtrem jest status_filter; pole status należy do zmiany statusu.
        $status = $isPost ? postString('status_filter') : $this->getString('status');
        if (in_array($status, Reservation::STATUSES, true)) {
            $filters['status'] = $status;
        }

        $rawEmployeeId = $_GET['employee_id'] ?? $_POST['employee_id'] ?? 0;
        $employeeId = is_string($rawEmployeeId) || is_int($rawEmployeeId)
            ? filter_var($rawEmployeeId, FILTER_VALIDATE_INT)
            : false;
        if ($employeeId !== false && $employeeId > 0) {
            $filters['employee_id'] = $employeeId;
        }

        return $filters;
    }
}
