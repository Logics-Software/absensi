<?php
class HolidayController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $view = isset($_GET['view']) ? trim(strtolower($_GET['view'])) : 'calendar'; // 'calendar' or 'list'
        $sortBy = $_GET['sort_by'] ?? 'holiday_date';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        // Ensure view is either 'calendar' or 'list'
        if ($view !== 'calendar' && $view !== 'list') {
            $view = 'calendar';
        }
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $holidayModel = new Holiday();
        
        // CRITICAL: Normalize view one more time before using it
        $view = trim(strtolower($view));
        if ($view !== 'list') {
            $view = 'calendar';
        }
        
        if ($view === 'calendar') {
            // Calendar view
            $holidays = $holidayModel->getHolidaysForCalendar($year, $month);
            
            $data = [
                'view' => 'calendar',
                'year' => $year,
                'month' => $month,
                'holidays' => $holidays,
                'search' => '', // Initialize for calendar view
                'perPage' => $perPage,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'holidayList' => [], // Initialize for calendar view
                'page' => 1,
                'total' => 0,
                'totalPages' => 1
            ];
        } else {
            // List view
            $holidayList = $holidayModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $year);
            $total = $holidayModel->count($search, $year);
            $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
            
            $data = [
                'view' => 'list',
                'holidayList' => $holidayList,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'holidays' => [], // Initialize for list view
                'month' => $month
            ];
        }
        
        $this->view('holiday/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'holiday_date' => $_POST['holiday_date'] ?? null,
                'holiday_name' => trim($_POST['holiday_name'] ?? ''),
                'is_national' => isset($_POST['is_national']) ? 1 : 0,
                'is_recurring_yearly' => isset($_POST['is_recurring_yearly']) ? 1 : 0
            ];
            
            // Validate required fields
            if (empty($data['holiday_date']) || empty($data['holiday_name'])) {
                Message::error('Tanggal dan Nama Hari Libur wajib diisi');
                $this->redirect('/holiday/create');
            }
            
            $holidayModel = new Holiday();
            
            try {
                $holidayModel->create($data);
                Message::success('Hari Libur berhasil ditambahkan');
                $this->redirect('/holiday');
            } catch (Exception $e) {
                error_log("Error creating holiday: " . $e->getMessage());
                Message::error('Gagal menambahkan hari libur. Silakan coba lagi atau hubungi administrator.');
                $this->redirect('/holiday/create');
            }
        }
        
        $this->view('holiday/create');
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $holidayModel = new Holiday();
        $holiday = $holidayModel->findById($id);
        
        if (!$holiday) {
            Message::error('Data Hari Libur tidak ditemukan');
            $this->redirect('/holiday');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'holiday_date' => $_POST['holiday_date'] ?? null,
                'holiday_name' => trim($_POST['holiday_name'] ?? ''),
                'is_national' => isset($_POST['is_national']) ? 1 : 0,
                'is_recurring_yearly' => isset($_POST['is_recurring_yearly']) ? 1 : 0
            ];
            
            // Validate required fields
            if (empty($data['holiday_date']) || empty($data['holiday_name'])) {
                Message::error('Tanggal dan Nama Hari Libur wajib diisi');
                $this->redirect("/holiday/edit/{$id}");
            }
            
            try {
                $result = $holidayModel->update($id, $data);
                if ($result) {
                    Message::success('Hari Libur berhasil diupdate');
                    $this->redirect('/holiday');
                } else {
                    Message::error('Gagal mengupdate hari libur. Tidak ada perubahan data.');
                    $this->redirect("/holiday/edit/{$id}");
                }
            } catch (Exception $e) {
                error_log("Error updating holiday: " . $e->getMessage());
                Message::error('Gagal mengupdate hari libur. Silakan coba lagi atau hubungi administrator.');
                $this->redirect("/holiday/edit/{$id}");
            }
        }
        
        $data = [
            'holiday' => $holiday
        ];
        
        $this->view('holiday/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $holidayModel = new Holiday();
        $holiday = $holidayModel->findById($id);
        
        if (!$holiday) {
            Message::error('Data Hari Libur tidak ditemukan');
            $this->redirect('/holiday');
        }
        
        $holidayModel->delete($id);
        Message::success('Hari Libur berhasil dihapus');
        $this->redirect('/holiday');
    }
    
    /**
     * API endpoint to get holidays for calendar (JSON)
     */
    public function apiGetHolidays() {
        Auth::requireRole(['admin']);
        
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
        
        $holidayModel = new Holiday();
        $holidays = $holidayModel->getHolidaysForCalendar($year, $month);
        
        header('Content-Type: application/json');
        echo json_encode($holidays);
        exit;
    }
}

