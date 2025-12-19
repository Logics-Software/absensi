<?php
class SettingJamBelajarController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $settingModel = new SettingJamBelajar();
        $setting = $settingModel->get();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate time format and required fields for active days
            $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
            $errors = [];
            
            foreach ($days as $day) {
                $status = $_POST[$day] ?? 'nonaktif';
                
                if ($status === 'aktif') {
                    $jamMasuk = trim($_POST['jammasuk' . $day] ?? '');
                    $jamPulang = trim($_POST['jampulang' . $day] ?? '');
                    
                    // Validate time format HH:MM
                    $timePattern = '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/';
                    
                    if (empty($jamMasuk)) {
                        $errors[] = "Jam masuk {$day} wajib diisi jika hari tersebut aktif";
                    } elseif (!preg_match($timePattern, $jamMasuk)) {
                        $errors[] = "Format jam masuk {$day} tidak valid. Gunakan format HH:MM (00:00 - 23:59)";
                    }
                    
                    if (empty($jamPulang)) {
                        $errors[] = "Jam pulang {$day} wajib diisi jika hari tersebut aktif";
                    } elseif (!preg_match($timePattern, $jamPulang)) {
                        $errors[] = "Format jam pulang {$day} tidak valid. Gunakan format HH:MM (00:00 - 23:59)";
                    }
                }
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Message::error($error);
                }
                $this->redirect('/settingjambelajar');
            }
            
            // Prepare data
            $data = [];
            foreach ($days as $day) {
                // Check if checkbox is checked (value will be 'aktif' if checked, otherwise use hidden input)
                $status = isset($_POST[$day]) && $_POST[$day] === 'aktif' ? 'aktif' : 'nonaktif';
                $data[$day] = $status;
                $data['jammasuk' . $day] = !empty($_POST['jammasuk' . $day]) ? trim($_POST['jammasuk' . $day]) : null;
                $data['jampulang' . $day] = !empty($_POST['jampulang' . $day]) ? trim($_POST['jampulang' . $day]) : null;
            }
            
            if ($settingModel->save($data)) {
                Message::success('Setting Jam Belajar berhasil disimpan');
            } else {
                Message::error('Gagal menyimpan setting jam belajar');
            }
            
            $this->redirect('/settingjambelajar');
        }
        
        $data = [
            'setting' => $setting
        ];
        
        $this->view('settingjambelajar/index', $data);
    }
}

