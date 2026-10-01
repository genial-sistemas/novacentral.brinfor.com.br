<?php
// ==============================================
// ANDROID-STYLE FILE MANAGER PHP
// ==============================================
session_start();

// Konfigurasi
$version = "1.4";
$app_name = "Redmi Device File Explorer";
$base_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : realpath('.');
if ($base_dir === false) $base_dir = realpath('.');

// Validasi path untuk keamanan
function validatePath($path) {
    $realpath = realpath($path);
    if ($realpath === false) return false;
    
    // Cegah akses ke luar direktori web (opsional)
    // $web_root = realpath($_SERVER['DOCUMENT_ROOT']);
    // if (strpos($realpath, $web_root) !== 0) {
    //     return false;
    // }
    
    return $realpath;
}

$base_dir = validatePath($base_dir) ?: realpath('.');

// Pesan notifikasi
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Fungsi utilitas
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 B';
    }
}

function getAndroidIcon($file, $is_dir) {
    if ($is_dir) {
        return '📁';
    }
    
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $icons = [
        'db' => '🗄️',
        'sqlite' => '🗄️',
        'sqlite3' => '🗄️',
        'db-shm' => '📊',
        'db-wal' => '📊',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'bmp' => '🖼️', 'webp' => '🖼️',
        'mp3' => '🎵', 'wav' => '🎵', 'ogg' => '🎵', 'flac' => '🎵',
        'mp4' => '🎬', 'avi' => '🎬', 'mov' => '🎬', 'mkv' => '🎬', 'webm' => '🎬',
        'pdf' => '📕',
        'doc' => '📘', 'docx' => '📘',
        'xls' => '📗', 'xlsx' => '📗',
        'ppt' => '📓', 'pptx' => '📓',
        'zip' => '📦', 'rar' => '📦', 'tar' => '📦', 'gz' => '📦', '7z' => '📦',
        'apk' => '📱',
        'xml' => '📋',
        'json' => '📋',
        'txt' => '📄',
        'log' => '📋',
        'ini' => '⚙️',
        'conf' => '⚙️',
        'php' => '🐘',
        'html' => '🌐', 'htm' => '🌐',
        'css' => '🎨',
        'js' => '📜',
        'py' => '🐍',
        'java' => '☕',
        'cpp' => '⚙️', 'c' => '⚙️',
        'sh' => '🐚',
        'md' => '📝',
    ];
    
    // Handle special Redmi files
    if (strpos($file, '.db') !== false) {
        if (strpos($file, '-shm') !== false) return '📊';
        if (strpos($file, '-wal') !== false) return '📊';
        return '🗄️';
    }
    
    return isset($icons[$extension]) ? $icons[$extension] : '📄';
}

function getAndroidPermissions($perms) {
    $symbolic = "";
    
    // File type
    $symbolic .= (($perms & 0x4000) ? 'd' : '-');
    
    // Owner
    $symbolic .= (($perms & 0x0100) ? 'r' : '-');
    $symbolic .= (($perms & 0x0080) ? 'w' : '-');
    $symbolic .= (($perms & 0x0040) ? 'x' : '-');
    
    // Group
    $symbolic .= (($perms & 0x0020) ? 'r' : '-');
    $symbolic .= (($perms & 0x0010) ? 'w' : '-');
    $symbolic .= (($perms & 0x0008) ? 'x' : '-');
    
    // Others
    $symbolic .= (($perms & 0x0004) ? 'r' : '-');
    $symbolic .= (($perms & 0x0002) ? 'w' : '-');
    $symbolic .= (($perms & 0x0001) ? 'x' : '-');
    
    return $symbolic;
}

function isAndroidAppFolder($name) {
    return strpos($name, '.') !== false && 
           (strpos($name, 'com.') === 0 || 
            strpos($name, 'org.') === 0 ||
            strpos($name, 'net.') === 0);
}

function isArchiveFile($filename) {
    $archive_extensions = ['zip', 'rar', 'tar', 'gz', '7z', 'bz2', 'xz'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $archive_extensions);
}

function getFolderSize($path) {
    $total_size = 0;
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $filepath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filepath)) {
                    $total_size += getFolderSize($filepath);
                } else {
                    $total_size += filesize($filepath);
                }
            }
        }
    }
    return $total_size;
}

// Fungsi Archive/Unarchive
function createZip($source, $destination) {
    if (!extension_loaded('zip')) {
        return false;
    }
    
    if (!file_exists($source)) {
        return false;
    }
    
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE)) {
        return false;
    }
    
    $source = realpath($source);
    
    if (is_dir($source)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $file = realpath($file);
            
            if (is_dir($file)) {
                $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
            } elseif (is_file($file)) {
                $zip->addFile($file, str_replace($source . DIRECTORY_SEPARATOR, '', $file));
            }
        }
    } elseif (is_file($source)) {
        $zip->addFile($source, basename($source));
    }
    
    return $zip->close();
}

function extractZip($source, $destination) {
    if (!extension_loaded('zip')) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($source) === TRUE) {
        // Create destination directory if it doesn't exist
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $zip->extractTo($destination);
        $zip->close();
        return true;
    }
    return false;
}

function extractArchive($source, $destination) {
    $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'zip':
            return extractZip($source, $destination);
        case 'tar':
            return extractTar($source, $destination);
        case 'gz':
        case 'tgz':
            return extractGz($source, $destination);
        case 'rar':
            return extractRar($source, $destination);
        case '7z':
            return extract7z($source, $destination);
        default:
            return false;
    }
}

function extractTar($source, $destination) {
    if (!class_exists('PharData')) {
        return false;
    }
    
    try {
        $phar = new PharData($source);
        $phar->extractTo($destination);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function extractGz($source, $destination) {
    if (!function_exists('gzopen')) {
        return false;
    }
    
    try {
        $phar = new PharData($source);
        $phar->extractTo($destination);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function extractRar($source, $destination) {
    if (!class_exists('RarArchive')) {
        return false;
    }
    
    try {
        $rar = RarArchive::open($source);
        if ($rar === FALSE) {
            return false;
        }
        
        $entries = $rar->getEntries();
        if ($entries === FALSE) {
            return false;
        }
        
        foreach ($entries as $entry) {
            $entry->extract($destination);
        }
        
        $rar->close();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function extract7z($source, $destination) {
    // 7z extraction requires 7zip binary or extension
    // This is a placeholder - you might need to implement this differently
    if (class_exists('SevenZipArchive')) {
        try {
            $sevenZip = new SevenZipArchive($source);
            return $sevenZip->extractTo($destination);
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Fallback to system command if available
    if (function_exists('shell_exec')) {
        $command = "7z x -o" . escapeshellarg($destination) . " " . escapeshellarg($source) . " 2>&1";
        $output = shell_exec($command);
        return strpos($output, 'Everything is Ok') !== false;
    }
    
    return false;
}

// Proses aksi POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $target = $_POST['target'] ?? '';
    $target_path = $base_dir . DIRECTORY_SEPARATOR . $target;
    
    switch ($action) {
        case 'create_folder':
            $folder_name = $_POST['folder_name'] ?? 'New Folder';
            $new_folder_path = $base_dir . DIRECTORY_SEPARATOR . $folder_name;
            
            if (!file_exists($new_folder_path)) {
                if (mkdir($new_folder_path, 0755, true)) {
                    $_SESSION['message'] = "Folder '$folder_name' created successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to create folder '$folder_name'.";
                    $_SESSION['message_type'] = 'error';
                }
            } else {
                $_SESSION['message'] = "Folder '$folder_name' already exists.";
                $_SESSION['message_type'] = 'error';
            }
            break;
            
        case 'create_file':
            $file_name = $_POST['file_name'] ?? 'newfile.txt';
            $new_file_path = $base_dir . DIRECTORY_SEPARATOR . $file_name;
            
            if (!file_exists($new_file_path)) {
                if (file_put_contents($new_file_path, '') !== false) {
                    $_SESSION['message'] = "File '$file_name' created successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to create file '$file_name'.";
                    $_SESSION['message_type'] = 'error';
                }
            } else {
                $_SESSION['message'] = "File '$file_name' already exists.";
                $_SESSION['message_type'] = 'error';
            }
            break;
            
        case 'rename':
            $new_name = $_POST['new_name'] ?? '';
            $new_path = $base_dir . DIRECTORY_SEPARATOR . $new_name;
            
            if ($new_name && file_exists($target_path) && !file_exists($new_path)) {
                if (rename($target_path, $new_path)) {
                    $_SESSION['message'] = "Renamed to '$new_name' successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to rename.";
                    $_SESSION['message_type'] = 'error';
                }
            } else {
                $_SESSION['message'] = "New name is invalid or already exists.";
                $_SESSION['message_type'] = 'error';
            }
            break;
            
        case 'delete':
            if (file_exists($target_path)) {
                if (is_dir($target_path)) {
                    // Hapus folder rekursif
                    $it = new RecursiveDirectoryIterator($target_path, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    
                    foreach($files as $file) {
                        if ($file->isDir()){
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    $success = rmdir($target_path);
                } else {
                    $success = unlink($target_path);
                }
                
                if ($success) {
                    $_SESSION['message'] = "Item deleted successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to delete item.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
            
        case 'chmod':
            $mode = $_POST['mode'] ?? '';
            if ($mode && file_exists($target_path)) {
                $octal_mode = octdec($mode);
                if (chmod($target_path, $octal_mode)) {
                    $_SESSION['message'] = "Permissions changed to $mode successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to change permissions.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
            
        case 'archive':
            if (file_exists($target_path)) {
                $archive_name = basename($target_path) . '.zip';
                $archive_path = $base_dir . DIRECTORY_SEPARATOR . $archive_name;
                
                if (createZip($target_path, $archive_path)) {
                    $_SESSION['message'] = "Archive '$archive_name' created successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to create archive.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
            
        case 'unarchive':
            if (file_exists($target_path) && isArchiveFile($target_path)) {
                $extract_name = pathinfo($target_path, PATHINFO_FILENAME);
                $extract_path = $base_dir . DIRECTORY_SEPARATOR . $extract_name;
                
                // If folder already exists, add number suffix
                $counter = 1;
                $original_extract_path = $extract_path;
                while (file_exists($extract_path)) {
                    $extract_path = $original_extract_path . '_' . $counter;
                    $counter++;
                }
                
                if (extractArchive($target_path, $extract_path)) {
                    $_SESSION['message'] = "Archive extracted successfully to '" . basename($extract_path) . "'.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to extract archive. Make sure the required PHP extension is installed.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
            
        case 'upload':
            if (isset($_FILES['upload_file']) && is_array($_FILES['upload_file']['name'])) {
                // Multiple files upload
                $uploaded_files = $_FILES['upload_file'];
                $success_count = 0;
                $error_count = 0;
                
                for ($i = 0; $i < count($uploaded_files['name']); $i++) {
                    if ($uploaded_files['error'][$i] === UPLOAD_ERR_OK) {
                        $filename = basename($uploaded_files['name'][$i]);
                        $destination = $base_dir . DIRECTORY_SEPARATOR . $filename;
                        
                        if (move_uploaded_file($uploaded_files['tmp_name'][$i], $destination)) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } elseif ($uploaded_files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                        $error_count++;
                    }
                }
                
                if ($success_count > 0) {
                    $_SESSION['message'] = "Successfully uploaded $success_count file(s)." . ($error_count > 0 ? " Failed to upload $error_count file(s)." : "");
                    $_SESSION['message_type'] = $error_count > 0 ? 'warning' : 'success';
                } elseif ($error_count > 0) {
                    $_SESSION['message'] = "Failed to upload $error_count file(s).";
                    $_SESSION['message_type'] = 'error';
                }
            } elseif (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
                // Single file upload
                $uploaded_file = $_FILES['upload_file'];
                $filename = basename($uploaded_file['name']);
                $destination = $base_dir . DIRECTORY_SEPARATOR . $filename;
                
                if (move_uploaded_file($uploaded_file['tmp_name'], $destination)) {
                    $_SESSION['message'] = "File '$filename' uploaded successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to upload file '$filename'.";
                    $_SESSION['message_type'] = 'error';
                }
            } elseif (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['message'] = "Upload error: " . getUploadError($_FILES['upload_file']['error']);
                $_SESSION['message_type'] = 'error';
            }
            break;
            
        case 'edit_file':
            $content = $_POST['content'] ?? '';
            if (file_exists($target_path) && is_file($target_path)) {
                if (file_put_contents($target_path, $content) !== false) {
                    $_SESSION['message'] = "File saved successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to save file.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
            
        case 'quick_upload':
            // Quick upload from drag & drop
            if (isset($_FILES['quick_upload_file']) && $_FILES['quick_upload_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded_file = $_FILES['quick_upload_file'];
                $filename = basename($uploaded_file['name']);
                $destination = $base_dir . DIRECTORY_SEPARATOR . $filename;
                
                if (move_uploaded_file($uploaded_file['tmp_name'], $destination)) {
                    $_SESSION['message'] = "File '$filename' uploaded successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Failed to upload file '$filename'.";
                    $_SESSION['message_type'] = 'error';
                }
            }
            break;
    }
    
    header("Location: ?dir=" . urlencode($base_dir));
    exit;
}

// Fungsi untuk mendapatkan pesan error upload
function getUploadError($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File size exceeds server limit.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File size exceeds form limit.';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension.';
        default:
            return 'Unknown upload error.';
    }
}

// Proses aksi GET
$get_action = $_GET['action'] ?? '';
$get_target = $_GET['target'] ?? '';
$edit_mode = false;
$view_mode = false;

if ($get_action && $get_target) {
    $target_path = $base_dir . DIRECTORY_SEPARATOR . $get_target;
    
    switch ($get_action) {
        case 'download':
            if (file_exists($target_path) && is_file($target_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($target_path) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($target_path));
                readfile($target_path);
                exit;
            }
            break;
            
        case 'view':
            if (file_exists($target_path) && is_file($target_path)) {
                $content = file_get_contents($target_path);
                $is_editable = in_array(pathinfo($target_path, PATHINFO_EXTENSION), 
                    ['txt', 'php', 'html', 'htm', 'css', 'js', 'json', 'xml', 'md', 'ini', 'conf', 'py', 'java', 'c', 'cpp', 'sh']);
                $view_mode = true;
            }
            break;
            
        case 'edit':
            if (file_exists($target_path) && is_file($target_path)) {
                $content = file_get_contents($target_path);
                $is_editable = true;
                $edit_mode = true;
            }
            break;
            
        case 'info':
            if (file_exists($target_path)) {
                $file_info = [
                    'name' => $get_target,
                    'path' => $target_path,
                    'type' => is_dir($target_path) ? 'Directory' : 'File',
                    'size' => is_dir($target_path) ? getFolderSize($target_path) : filesize($target_path),
                    'modified' => date('Y-m-d H:i:s', filemtime($target_path)),
                    'permissions' => getAndroidPermissions(fileperms($target_path)),
                    'owner' => fileowner($target_path),
                    'group' => filegroup($target_path),
                ];
            }
            break;
    }
}

// Dapatkan daftar file dan folder
$items = [];
$directories = [];
$files = [];

if ($handle = opendir($base_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $items[] = $entry;
        }
    }
    closedir($handle);
}

// Pisahkan direktori dan file
foreach ($items as $item) {
    $item_path = $base_dir . DIRECTORY_SEPARATOR . $item;
    if (is_dir($item_path)) {
        $directories[] = $item;
    } else {
        $files[] = $item;
    }
}

// Urutkan
usort($directories, function($a, $b) {
    $a_is_app = isAndroidAppFolder($a);
    $b_is_app = isAndroidAppFolder($b);
    $a_is_hidden = strpos($a, '.') === 0;
    $b_is_hidden = strpos($b, '.') === 0;
    
    // App folders first
    if ($a_is_app && !$b_is_app) return -1;
    if (!$a_is_app && $b_is_app) return 1;
    
    // Non-hidden folders before hidden
    if (!$a_is_hidden && $b_is_hidden) return -1;
    if ($a_is_hidden && !$b_is_hidden) return 1;
    
    // Alphabetical
    return strnatcasecmp($a, $b);
});

usort($files, function($a, $b) {
    return strnatcasecmp($a, $b);
});

// Gabungkan kembali
$items = array_merge($directories, $files);

// Breadcrumb navigation
$breadcrumbs = [];
$current_path = '';
$parts = explode(DIRECTORY_SEPARATOR, str_replace(realpath('.'), '', $base_dir));
foreach ($parts as $part) {
    if ($part) {
        $current_path .= DIRECTORY_SEPARATOR . $part;
        $breadcrumbs[] = [
            'name' => $part,
            'path' => realpath('.') . $current_path
        ];
    }
}

// Get parent directory
$parent_dir = dirname($base_dir);

// Get current directory name
$current_dir_name = basename($base_dir);
if ($current_dir_name === '' || $current_dir_name === '.') {
    $current_dir_name = 'Root';
}

// Get upload max size
$upload_max_size = ini_get('upload_max_filesize');
$post_max_size = ini_get('post_max_size');

// Check if zip extension is available
$zip_available = extension_loaded('zip');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_name; ?> - <?php echo $base_dir; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
        }
        
        body {
            background-color: #fafafa;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }
        
        .header-bar {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 16px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .header-title h1 {
            font-size: 18px;
            font-weight: 500;
        }
        
        .current-path {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .path-segment {
            color: white;
            text-decoration: none;
            padding: 2px 6px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }
        
        .path-segment:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .path-arrow {
            opacity: 0.7;
        }
        
        .message {
            padding: 12px 16px;
            margin: 16px;
            border-radius: 4px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }
        
        .error {
            background-color: #FFEBEE;
            color: #C62828;
            border-left: 4px solid #F44336;
        }
        
        .warning {
            background-color: #FFF3E0;
            color: #EF6C00;
            border-left: 4px solid #FF9800;
        }
        
        /* Navigation Bar */
        .nav-bar {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 73px;
            z-index: 90;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .current-dir-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
        }
        
        .dir-icon {
            font-size: 20px;
            color: #2196F3;
        }
        
        .dir-name {
            font-weight: 500;
            color: #333;
        }
        
        .dir-path {
            font-size: 12px;
            color: #666;
            background-color: #f5f5f5;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: monospace;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .nav-actions {
            display: flex;
            gap: 8px;
        }
        
        /* Upload Drop Zone */
        .upload-drop-zone {
            background-color: #f8f9fa;
            border: 2px dashed #ced4da;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            margin: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .upload-drop-zone:hover {
            border-color: #2196F3;
            background-color: #f0f7ff;
        }
        
        .upload-drop-zone.dragover {
            border-color: #2196F3;
            background-color: #e3f2fd;
            transform: scale(1.01);
        }
        
        .upload-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 16px;
        }
        
        .upload-text {
            color: #495057;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .upload-subtext {
            color: #6c757d;
            font-size: 14px;
        }
        
        .upload-btn {
            margin-top: 16px;
            padding: 10px 24px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }
        
        .upload-btn:hover {
            background-color: #1976D2;
        }
        
        .upload-progress {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 16px;
            display: none;
        }
        
        .upload-progress-bar {
            height: 6px;
            background-color: #2196F3;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .upload-progress-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
            text-align: center;
        }
        
        .toolbar {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #1976D2;
            transform: translateY(-1px);
        }
        
        .btn-icon {
            font-size: 16px;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #2196F3;
            color: #2196F3;
        }
        
        .btn-outline:hover {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-success {
            background-color: #4CAF50;
        }
        
        .btn-success:hover {
            background-color: #388E3C;
        }
        
        .btn-danger {
            background-color: #F44336;
        }
        
        .btn-danger:hover {
            background-color: #D32F2F;
        }
        
        .btn-warning {
            background-color: #FF9800;
        }
        
        .btn-warning:hover {
            background-color: #F57C00;
        }
        
        .btn-archive {
            background-color: #9C27B0;
        }
        
        .btn-archive:hover {
            background-color: #7B1FA2;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .file-table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
        }
        
        .file-table th {
            background-color: #f5f5f5;
            padding: 14px 16px;
            text-align: left;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
            font-size: 13px;
            position: sticky;
            top: 132px;
            z-index: 50;
        }
        
        .file-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .file-table tr:hover td {
            background-color: #f9f9f9;
        }
        
        .file-icon {
            font-size: 20px;
            margin-right: 12px;
            vertical-align: middle;
        }
        
        .file-name {
            vertical-align: middle;
            font-weight: 500;
        }
        
        .folder-link {
            color: #2196F3;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .folder-link:hover {
            text-decoration: underline;
        }
        
        .app-folder {
            color: #FF5722;
        }
        
        .hidden-folder {
            color: #9E9E9E;
        }
        
        .archive-file {
            color: #9C27B0;
        }
        
        .permission-badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #E3F2FD;
            color: #1976D2;
            border-radius: 12px;
            font-family: monospace;
            font-size: 12px;
            font-weight: 500;
        }
        
        .file-size {
            color: #666;
            font-size: 13px;
        }
        
        .progress-bar {
            display: inline-block;
            width: 80px;
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-left: 8px;
            vertical-align: middle;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 3px;
        }
        
        .file-actions {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 4px 8px;
            background-color: transparent;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            color: #666;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background-color: #f0f0f0;
            color: #2196F3;
        }
        
        .action-btn-archive {
            color: #9C27B0;
        }
        
        .action-btn-archive:hover {
            background-color: #F3E5F5;
            color: #7B1FA2;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            padding: 20px 24px;
            background-color: #f5f5f5;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 500;
            color: #333;
        }
        
        .modal-close {
            font-size: 24px;
            color: #666;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            background: none;
            border: none;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }
        
        .form-help {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        .modal-footer {
            padding: 16px 24px;
            background-color: #fafafa;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .file-info {
            margin-top: 20px;
        }
        
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            width: 120px;
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .editor-container {
            background-color: #fafafa;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .editor-header {
            background-color: #2196F3;
            color: white;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .editor-header h3 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .editor-actions {
            display: flex;
            gap: 8px;
        }
        
        .editor-body {
            padding: 0;
        }
        
        .code-editor {
            width: 100%;
            height: 500px;
            padding: 16px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            border: none;
            background-color: #fefefe;
            resize: vertical;
            line-height: 1.5;
            tab-size: 4;
        }
        
        .code-preview {
            padding: 16px;
            background-color: white;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 500px;
            overflow: auto;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
            margin-top: 20px;
        }
        
        .chmod-modal .modal-body {
            padding: 20px;
        }
        
        .chmod-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 15px 0;
        }
        
        .chmod-option {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chmod-option:hover {
            border-color: #2196F3;
            background-color: #f5f9ff;
        }
        
        .chmod-option.active {
            border-color: #2196F3;
            background-color: #E3F2FD;
            color: #1976D2;
        }
        
        .chmod-value {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .chmod-desc {
            font-size: 12px;
            color: #666;
        }
        
        /* Upload file list */
        .upload-file-list {
            margin-top: 16px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 8px;
        }
        
        .upload-file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .upload-file-item:last-child {
            border-bottom: none;
        }
        
        .upload-file-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .upload-file-icon {
            font-size: 16px;
        }
        
        .upload-file-name {
            font-size: 13px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .upload-file-size {
            font-size: 12px;
            color: #666;
        }
        
        .upload-file-remove {
            color: #F44336;
            cursor: pointer;
            padding: 4px;
            border-radius: 3px;
        }
        
        .upload-file-remove:hover {
            background-color: #FFEBEE;
        }
        
        .archive-info {
            background-color: #F3E5F5;
            border: 1px solid #E1BEE7;
            border-radius: 4px;
            padding: 12px;
            margin: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (max-width: 768px) {
            .header-title h1 {
                font-size: 16px;
            }
            
            .current-path {
                font-size: 11px;
            }
            
            .nav-bar {
                padding: 0 12px;
                top: 73px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .current-dir-info {
                padding: 8px 0;
                width: 100%;
                justify-content: space-between;
            }
            
            .dir-path {
                max-width: 150px;
            }
            
            .nav-actions {
                width: 100%;
                padding: 8px 0;
                border-top: 1px solid #f0f0f0;
                justify-content: center;
            }
            
            .upload-drop-zone {
                margin: 12px;
                padding: 30px 15px;
                min-height: 120px;
            }
            
            .upload-icon {
                font-size: 36px;
            }
            
            .upload-text {
                font-size: 14px;
            }
            
            .upload-subtext {
                font-size: 12px;
            }
            
            .toolbar {
                padding: 8px 12px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .file-table th,
            .file-table td {
                padding: 10px 12px;
            }
            
            .file-table th {
                top: 185px;
            }
            
            .modal-content {
                width: 95%;
            }
            
            .chmod-options {
                grid-template-columns: 1fr;
            }
            
            .file-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-btn {
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .file-table {
                display: block;
                overflow-x: auto;
            }
            
            .toolbar {
                justify-content: center;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-bar">
            <div class="header-title">
                <span>📱</span>
                <h1><?php echo $app_name; ?></h1>
            </div>
            <div class="current-path">
                <a href="?" class="path-segment">Device Storage</a>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <span class="path-arrow">›</span>
                    <a href="?dir=<?php echo urlencode($crumb['path']); ?>" class="path-segment">
                        <?php echo htmlspecialchars($crumb['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Navigation Bar -->
        <div class="nav-bar">
            <div class="current-dir-info">
                <span class="dir-icon">📁</span>
                <span class="dir-name"><?php echo htmlspecialchars($current_dir_name); ?></span>
                <span class="dir-path" title="<?php echo htmlspecialchars($base_dir); ?>">
                    <?php 
                    if (strlen($base_dir) > 40) {
                        echo '...' . substr($base_dir, -37);
                    } else {
                        echo htmlspecialchars($base_dir);
                    }
                    ?>
                </span>
            </div>
            <div class="nav-actions">
                <?php if ($parent_dir && $parent_dir != $base_dir): ?>
                    <a href="?dir=<?php echo urlencode($parent_dir); ?>" class="btn btn-sm btn-outline">
                        <span class="btn-icon">⬆️</span> Up
                    </a>
                <?php endif; ?>
                <button class="btn btn-sm" onclick="showModal('createFolderModal')">
                    <span class="btn-icon">📁</span> New Folder
                </button>
                <button class="btn btn-sm" onclick="showModal('createFileModal')">
                    <span class="btn-icon">📄</span> New File
                </button>
                <button class="btn btn-sm btn-success" onclick="location.reload()">
                    <span class="btn-icon">🔄</span> Refresh
                </button>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <span><?php echo $message_type == 'success' ? '✅' : ($message_type == 'warning' ? '⚠️' : '❌'); ?></span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Archive Support Info -->
        <?php if (!$zip_available): ?>
            <div class="archive-info">
                <span>⚠️</span>
                <span>Archive support (Zip extension) is not enabled. Some archive features may not work properly.</span>
            </div>
        <?php endif; ?>
        
        <!-- Upload Drop Zone -->
        <div class="upload-drop-zone" id="uploadDropZone">
            <div class="upload-icon">📤</div>
            <div class="upload-text">Drop files here to upload to <strong><?php echo htmlspecialchars($current_dir_name); ?></strong></div>
            <div class="upload-subtext">or click to select files</div>
            <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                <span class="btn-icon">📁</span> Select Files
            </button>
            <div class="upload-progress" id="uploadProgress">
                <div class="upload-progress-bar" id="uploadProgressBar"></div>
                <div class="upload-progress-text" id="uploadProgressText">0%</div>
            </div>
            <div class="upload-subtext" style="margin-top: 12px;">
                Max file size: <?php echo $upload_max_size; ?> | Max post size: <?php echo $post_max_size; ?>
            </div>
        </div>
        
        <!-- Hidden file input for upload -->
        <input type="file" id="fileInput" name="files[]" multiple style="display: none;" 
               onchange="handleFileSelect(this.files)">
        
        <!-- File Table -->
        <?php if (empty($items) && !isset($file_info) && !$view_mode && !$edit_mode): ?>
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3>This folder is empty</h3>
                <p>Upload files or create new files/folders to get started</p>
            </div>
        <?php elseif (!isset($file_info) && !$view_mode && !$edit_mode): ?>
            <table class="file-table">
                <thead>
                    <tr>
                        <th width="40%">Name</th>
                        <th width="15%">Permissions</th>
                        <th width="20%">Date</th>
                        <th width="15%">Size</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $item_path = $base_dir . DIRECTORY_SEPARATOR . $item;
                        $is_dir = is_dir($item_path);
                        $is_app = isAndroidAppFolder($item);
                        $is_hidden = strpos($item, '.') === 0;
                        $is_archive = !$is_dir && isArchiveFile($item);
                        
                        $size = '';
                        $size_display = '🖥️';
                        if ($is_dir) {
                            $folder_size = getFolderSize($item_path);
                            $size = formatSize($folder_size);
                            $size_display = '📁 ' . $size;
                        } else {
                            $file_size = filesize($item_path);
                            $size = formatSize($file_size);
                            $size_display = $size;
                        }
                        
                        $modified = date('Y-m-d H:i', filemtime($item_path));
                        $perms = getAndroidPermissions(fileperms($item_path));
                        $icon = getAndroidIcon($item, $is_dir);
                        
                        // Determine CSS class
                        $item_class = '';
                        if ($is_app) $item_class = 'app-folder';
                        elseif ($is_hidden) $item_class = 'hidden-folder';
                        elseif ($is_archive) $item_class = 'archive-file';
                        elseif ($is_dir) $item_class = 'folder';
                        
                        // Check if file is editable
                        $is_editable_file = !$is_dir && !$is_archive && in_array(pathinfo($item, PATHINFO_EXTENSION), 
                            ['txt', 'php', 'html', 'htm', 'css', 'js', 'json', 'xml', 'md', 'ini', 'conf', 'py', 'java', 'c', 'cpp', 'sh']);
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <span class="file-icon"><?php echo $icon; ?></span>
                                    <span class="file-name <?php echo $item_class; ?>">
                                        <?php if ($is_dir): ?>
                                            <a href="?dir=<?php echo urlencode($item_path); ?>" class="folder-link">
                                                <?php echo htmlspecialchars($item); ?>
                                                <?php if ($is_app): ?><span style="font-size: 11px; opacity: 0.7;"> (App)</span><?php endif; ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($item); ?>
                                            <?php if ($is_archive): ?><span style="font-size: 11px; opacity: 0.7;"> (Archive)</span><?php endif; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="permission-badge"><?php echo $perms; ?></span>
                                <button class="action-btn btn-sm" onclick="showChmodModal('<?php echo htmlspecialchars($item); ?>', '<?php echo substr(decoct(fileperms($item_path)), -4); ?>')" title="Change Permissions">
                                    🔧
                                </button>
                            </td>
                            <td>
                                <span style="color: #666; font-size: 13px;"><?php echo $modified; ?></span>
                            </td>
                            <td>
                                <span class="file-size"><?php echo $size_display; ?></span>
                            </td>
                            <td>
                                <div class="file-actions">
                                    <?php if ($is_dir): ?>
                                        <!-- Folder actions -->
                                        <button class="action-btn action-btn-archive" onclick="showArchiveModal('<?php echo htmlspecialchars($item); ?>')" title="Create Archive">
                                            📦
                                        </button>
                                    <?php elseif ($is_archive): ?>
                                        <!-- Archive file actions -->
                                        <button class="action-btn action-btn-archive" onclick="showUnarchiveModal('<?php echo htmlspecialchars($item); ?>')" title="Extract Archive">
                                            📤
                                        </button>
                                        <a href="?dir=<?php echo urlencode($base_dir); ?>&action=download&target=<?php echo urlencode($item); ?>" class="action-btn" title="Download">⬇️</a>
                                    <?php else: ?>
                                        <!-- Regular file actions -->
                                        <?php if ($is_editable_file): ?>
                                            <button class="action-btn" onclick="showEditModal('<?php echo htmlspecialchars($item); ?>')" title="Edit">✏️</button>
                                        <?php else: ?>
                                            <button class="action-btn" onclick="showViewModal('<?php echo htmlspecialchars($item); ?>')" title="View">👁️</button>
                                        <?php endif; ?>
                                        <a href="?dir=<?php echo urlencode($base_dir); ?>&action=download&target=<?php echo urlencode($item); ?>" class="action-btn" title="Download">⬇️</a>
                                    <?php endif; ?>
                                    
                                    <!-- Common actions -->
                                    <button class="action-btn" onclick="showRenameModal('<?php echo htmlspecialchars($item); ?>')" title="Rename">📝</button>
                                    <button class="action-btn" onclick="showInfoModal('<?php echo htmlspecialchars($item); ?>')" title="Info">ℹ️</button>
                                    <button class="action-btn" onclick="showDeleteModal('<?php echo htmlspecialchars($item); ?>')" title="Delete" style="color: #F44336;">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- File Info Display -->
        <?php if (isset($file_info)): ?>
            <div class="file-info">
                <div class="editor-header">
                    <h3><span>ℹ️</span> File Information</h3>
                    <div class="editor-actions">
                        <a href="?dir=<?php echo urlencode($base_dir); ?>" class="btn btn-sm btn-outline" style="color: white; border-color: white;">Close</a>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="info-row">
                        <div class="info-label">Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($file_info['name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Type:</div>
                        <div class="info-value"><?php echo $file_info['type']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Size:</div>
                        <div class="info-value"><?php echo formatSize($file_info['size']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Modified:</div>
                        <div class="info-value"><?php echo $file_info['modified']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Permissions:</div>
                        <div class="info-value"><?php echo $file_info['permissions']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Owner:</div>
                        <div class="info-value"><?php echo $file_info['owner']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Group:</div>
                        <div class="info-value"><?php echo $file_info['group']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Path:</div>
                        <div class="info-value" style="font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($file_info['path']); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- File Content Viewer/Editor -->
        <?php if ($view_mode || $edit_mode): ?>
            <div class="editor-container">
                <div class="editor-header">
                    <h3>
                        <span><?php echo $edit_mode ? '✏️' : '👁️'; ?></span>
                        <?php echo $edit_mode ? 'Editing:' : 'Viewing:'; ?>
                        <?php echo htmlspecialchars($get_target); ?>
                    </h3>
                    <div class="editor-actions">
                        <?php if ($edit_mode && $is_editable): ?>
                            <button type="submit" form="editForm" class="btn btn-sm btn-success">Save Changes</button>
                        <?php elseif (!$edit_mode && $is_editable): ?>
                            <a href="?dir=<?php echo urlencode($base_dir); ?>&action=edit&target=<?php echo urlencode($get_target); ?>" class="btn btn-sm btn-warning">Edit File</a>
                        <?php endif; ?>
                        <a href="?dir=<?php echo urlencode($base_dir); ?>&action=download&target=<?php echo urlencode($get_target); ?>" class="btn btn-sm btn-outline" style="color: white; border-color: white;">Download</a>
                        <a href="?dir=<?php echo urlencode($base_dir); ?>" class="btn btn-sm btn-outline" style="color: white; border-color: white;">Close</a>
                    </div>
                </div>
                <div class="editor-body">
                    <?php if ($edit_mode && $is_editable): ?>
                        <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>" id="editForm">
                            <input type="hidden" name="action" value="edit_file">
                            <input type="hidden" name="target" value="<?php echo htmlspecialchars($get_target); ?>">
                            <textarea class="code-editor" name="content" id="editorContent"><?php echo htmlspecialchars($content); ?></textarea>
                        </form>
                    <?php else: ?>
                        <div class="code-preview"><?php echo htmlspecialchars($content); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p><?php echo $app_name; ?> v<?php echo $version; ?> | PHP <?php echo PHP_VERSION; ?> | Items: <?php echo count($items); ?> | Upload Limit: <?php echo $upload_max_size; ?> | Archive Support: <?php echo $zip_available ? 'Enabled' : 'Limited'; ?></p>
        </div>
    </div>
    
    <!-- Modal Templates -->
    
    <!-- Upload Modal (Advanced) -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Upload Files to <?php echo htmlspecialchars($current_dir_name); ?></div>
                <button class="modal-close" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>" enctype="multipart/form-data" id="uploadForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload">
                    <div class="form-group">
                        <label class="form-label">Select Files</label>
                        <input type="file" class="form-input" name="upload_file[]" multiple required 
                               onchange="previewUploadFiles(this.files)" id="modalFileInput">
                        <div class="form-help">
                            Max file size: <?php echo $upload_max_size; ?> | Max post size: <?php echo $post_max_size; ?>
                        </div>
                    </div>
                    <div id="uploadFilePreview" class="upload-file-list" style="display: none;">
                        <!-- File preview will be inserted here -->
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload Options</label>
                        <div style="display: flex; gap: 16px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="overwrite" value="1" checked>
                                <span>Overwrite existing files</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Cancel</button>
                    <button type="submit" class="btn btn-success" id="uploadSubmitBtn">Upload Files</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Archive Modal -->
    <div id="archiveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Create Archive</div>
                <button class="modal-close" onclick="closeModal('archiveModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="archive">
                    <input type="hidden" id="archive_target" name="target" value="">
                    <div class="form-group">
                        <label class="form-label">Archive Name</label>
                        <input type="text" class="form-input" id="archive_name" name="archive_name" value="" placeholder="Enter archive name (optional)">
                        <div class="form-help">Leave empty to use folder name. Archive will be created as .zip file</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Archive Options</label>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="include_hidden" value="1">
                                <span>Include hidden files (starting with .)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="compress_level" value="9" checked>
                                <span>Maximum compression</span>
                            </label>
                        </div>
                    </div>
                    <p>Create a ZIP archive of "<strong id="archive_item_name"></strong>" folder?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('archiveModal')">Cancel</button>
                    <button type="submit" class="btn btn-archive">Create Archive</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Unarchive Modal -->
    <div id="unarchiveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Extract Archive</div>
                <button class="modal-close" onclick="closeModal('unarchiveModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="unarchive">
                    <input type="hidden" id="unarchive_target" name="target" value="">
                    <div class="form-group">
                        <label class="form-label">Extraction Folder</label>
                        <input type="text" class="form-input" id="extract_folder" name="extract_folder" value="" placeholder="Enter folder name (optional)">
                        <div class="form-help">Leave empty to use archive name. Will add number suffix if folder exists.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Extraction Options</label>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="overwrite_extract" value="1">
                                <span>Overwrite existing files</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="preserve_paths" value="1" checked>
                                <span>Preserve directory structure</span>
                            </label>
                        </div>
                    </div>
                    <p>Extract contents of "<strong id="unarchive_item_name"></strong>" archive?</p>
                    <?php if (!$zip_available): ?>
                        <div class="archive-info" style="margin: 12px 0;">
                            <span>⚠️</span>
                            <span>Zip extension not available. Only ZIP archives can be extracted.</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('unarchiveModal')">Cancel</button>
                    <button type="submit" class="btn btn-archive">Extract Archive</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Other Modals (Create Folder, Create File, Rename, Delete, CHMOD, etc.) -->
    <!-- Create Folder Modal -->
    <div id="createFolderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Create New Folder</div>
                <button class="modal-close" onclick="closeModal('createFolderModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_folder">
                    <div class="form-group">
                        <label class="form-label">Folder Name</label>
                        <input type="text" class="form-input" name="folder_name" value="New Folder" required>
                        <div class="form-help">Enter a name for the new folder</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createFolderModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Create File Modal -->
    <div id="createFileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Create New File</div>
                <button class="modal-close" onclick="closeModal('createFileModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_file">
                    <div class="form-group">
                        <label class="form-label">File Name</label>
                        <input type="text" class="form-input" name="file_name" value="newfile.txt" required>
                        <div class="form-help">Enter a name with extension (e.g., file.txt)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createFileModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Create File</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Rename Modal -->
    <div id="renameModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Rename Item</div>
                <button class="modal-close" onclick="closeModal('renameModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" id="rename_target" name="target" value="">
                    <div class="form-group">
                        <label class="form-label">New Name</label>
                        <input type="text" class="form-input" id="new_name" name="new_name" required>
                        <div class="form-help">Enter the new name for this item</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('renameModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Rename</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Confirm Delete</div>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_target" name="target" value="">
                    <p>Are you sure you want to delete "<strong id="delete_item_name"></strong>"?</p>
                    <p style="color: #F44336; font-size: 13px; margin-top: 8px;">
                        ⚠️ This action cannot be undone!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- CHMOD Modal -->
    <div id="chmodModal" class="modal chmod-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Change Permissions</div>
                <button class="modal-close" onclick="closeModal('chmodModal')">&times;</button>
            </div>
            <form method="POST" action="?dir=<?php echo urlencode($base_dir); ?>">
                <input type="hidden" name="action" value="chmod">
                <input type="hidden" id="chmod_target" name="target" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Permission Mode (Octal)</label>
                        <input type="text" class="form-input" id="chmod_mode" name="mode" value="0644" required>
                        <div class="form-help">Enter octal permission value (e.g., 0755, 0644)</div>
                    </div>
                    
                    <div class="chmod-options">
                        <div class="chmod-option" data-value="0755" onclick="setChmodValue('0755')">
                            <div class="chmod-value">0755</div>
                            <div class="chmod-desc">Folder (rwxr-xr-x)</div>
                        </div>
                        <div class="chmod-option" data-value="0644" onclick="setChmodValue('0644')">
                            <div class="chmod-value">0644</div>
                            <div class="chmod-desc">File (rw-r--r--)</div>
                        </div>
                        <div class="chmod-option" data-value="0777" onclick="setChmodValue('0777')">
                            <div class="chmod-value">0777</div>
                            <div class="chmod-desc">Full Access (rwxrwxrwx)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('chmodModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Change Permissions</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Upload functionality
        const uploadDropZone = document.getElementById('uploadDropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadProgressText = document.getElementById('uploadProgressText');
        
        // Drag and drop events
        uploadDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('dragover');
        });
        
        uploadDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
        });
        
        uploadDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files);
            }
        });
        
        // Click to select files
        uploadDropZone.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON') {
                fileInput.click();
            }
        });
        
        // Handle file selection
        function handleFileSelect(files) {
            if (files.length === 0) return;
            
            // Show progress bar
            uploadProgress.style.display = 'block';
            uploadProgressBar.style.width = '0%';
            uploadProgressText.textContent = '0%';
            
            // Upload files one by one with progress tracking
            uploadFilesSequentially(files, 0);
        }
        
        // Upload files sequentially with progress tracking
        function uploadFilesSequentially(files, index) {
            if (index >= files.length) {
                // All files uploaded
                uploadProgressBar.style.width = '100%';
                uploadProgressText.textContent = 'Upload complete!';
                
                // Refresh page after 1 second
                setTimeout(() => {
                    location.reload();
                }, 1000);
                return;
            }
            
            const file = files[index];
            const totalFiles = files.length;
            const formData = new FormData();
            formData.append('action', 'quick_upload');
            formData.append('quick_upload_file', file);
            
            // Calculate progress
            const baseProgress = (index / totalFiles) * 100;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?dir=<?php echo urlencode($base_dir); ?>', true);
            
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const fileProgress = (e.loaded / e.total) * (100 / totalFiles);
                    const totalProgress = baseProgress + fileProgress;
                    uploadProgressBar.style.width = totalProgress + '%';
                    uploadProgressText.textContent = 
                        `Uploading ${index + 1}/${totalFiles}: ${file.name} (${Math.round(totalProgress)}%)`;
                }
            };
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Upload next file
                    uploadFilesSequentially(files, index + 1);
                } else {
                    uploadProgressText.textContent = `Error uploading ${file.name}`;
                    uploadProgressBar.style.backgroundColor = '#F44336';
                    
                    // Still try to upload next file
                    setTimeout(() => {
                        uploadFilesSequentially(files, index + 1);
                    }, 1000);
                }
            };
            
            xhr.onerror = function() {
                uploadProgressText.textContent = `Error uploading ${file.name}`;
                uploadProgressBar.style.backgroundColor = '#F44336';
                
                // Still try to upload next file
                setTimeout(() => {
                    uploadFilesSequentially(files, index + 1);
                }, 1000);
            };
            
            xhr.send(formData);
        }
        
        // Preview files in modal upload
        function previewUploadFiles(files) {
            const previewContainer = document.getElementById('uploadFilePreview');
            previewContainer.innerHTML = '';
            
            if (files.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }
            
            previewContainer.style.display = 'block';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileItem = document.createElement('div');
                fileItem.className = 'upload-file-item';
                
                const fileInfo = document.createElement('div');
                fileInfo.className = 'upload-file-info';
                
                const fileIcon = document.createElement('span');
                fileIcon.className = 'upload-file-icon';
                fileIcon.textContent = getFileIcon(file.name);
                
                const fileName = document.createElement('span');
                fileName.className = 'upload-file-name';
                fileName.textContent = file.name;
                fileName.title = file.name;
                
                const fileSize = document.createElement('span');
                fileSize.className = 'upload-file-size';
                fileSize.textContent = formatFileSize(file.size);
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'upload-file-remove';
                removeBtn.innerHTML = '🗑️';
                removeBtn.title = 'Remove file';
                removeBtn.onclick = function() {
                    // Remove file from input
                    const dt = new DataTransfer();
                    const input = document.getElementById('modalFileInput');
                    
                    for (let j = 0; j < input.files.length; j++) {
                        if (j !== i) {
                            dt.items.add(input.files[j]);
                        }
                    }
                    
                    input.files = dt.files;
                    previewUploadFiles(input.files);
                };
                
                fileInfo.appendChild(fileIcon);
                fileInfo.appendChild(fileName);
                fileInfo.appendChild(fileSize);
                
                fileItem.appendChild(fileInfo);
                fileItem.appendChild(removeBtn);
                previewContainer.appendChild(fileItem);
            }
        }
        
        // Get appropriate icon for file
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️', 'bmp': '🖼️',
                'pdf': '📕',
                'doc': '📘', 'docx': '📘',
                'xls': '📗', 'xlsx': '📗',
                'ppt': '📓', 'pptx': '📓',
                'zip': '📦', 'rar': '📦', 'tar': '📦', 'gz': '📦', '7z': '📦',
                'mp3': '🎵', 'wav': '🎵',
                'mp4': '🎬', 'avi': '🎬',
                'php': '🐘',
                'html': '🌐', 'htm': '🌐',
                'css': '🎨',
                'js': '📜',
                'txt': '📄',
                'db': '🗄️'
            };
            
            return icons[ext] || '📄';
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' B';
            }
        }
        
        // Modal functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            
            // Reset file preview if it's upload modal
            if (modalId === 'uploadModal') {
                const previewContainer = document.getElementById('uploadFilePreview');
                previewContainer.innerHTML = '';
                previewContainer.style.display = 'none';
                
                // Reset file input
                const fileInput = document.getElementById('modalFileInput');
                fileInput.value = '';
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function showRenameModal(item) {
            document.getElementById('rename_target').value = item;
            document.getElementById('new_name').value = item;
            showModal('renameModal');
        }
        
        function showDeleteModal(item) {
            document.getElementById('delete_target').value = item;
            document.getElementById('delete_item_name').textContent = item;
            showModal('deleteModal');
        }
        
        function showChmodModal(item, currentMode) {
            document.getElementById('chmod_target').value = item;
            document.getElementById('chmod_mode').value = currentMode || '0644';
            
            // Update active option
            document.querySelectorAll('.chmod-option').forEach(option => {
                option.classList.remove('active');
                if (option.getAttribute('data-value') === currentMode) {
                    option.classList.add('active');
                }
            });
            
            showModal('chmodModal');
        }
        
        function showEditModal(item) {
            window.location.href = '?dir=<?php echo urlencode($base_dir); ?>&action=edit&target=' + encodeURIComponent(item);
        }
        
        function showViewModal(item) {
            window.location.href = '?dir=<?php echo urlencode($base_dir); ?>&action=view&target=' + encodeURIComponent(item);
        }
        
        function showInfoModal(item) {
            window.location.href = '?dir=<?php echo urlencode($base_dir); ?>&action=info&target=' + encodeURIComponent(item);
        }
        
        function showArchiveModal(item) {
            document.getElementById('archive_target').value = item;
            document.getElementById('archive_item_name').textContent = item;
            document.getElementById('archive_name').value = item + '.zip';
            showModal('archiveModal');
        }
        
        function showUnarchiveModal(item) {
            document.getElementById('unarchive_target').value = item;
            document.getElementById('unarchive_item_name').textContent = item;
            
            // Remove extension for folder name suggestion
            const fileName = item;
            const lastDotIndex = fileName.lastIndexOf('.');
            const folderName = lastDotIndex > 0 ? fileName.substring(0, lastDotIndex) : fileName;
            document.getElementById('extract_folder').value = folderName;
            
            showModal('unarchiveModal');
        }
        
        function setChmodValue(value) {
            document.getElementById('chmod_mode').value = value;
            
            // Update active option
            document.querySelectorAll('.chmod-option').forEach(option => {
                option.classList.remove('active');
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('active');
                }
            });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
            // Ctrl/Cmd + N for new file
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                showModal('createFileModal');
            }
            // Ctrl/Cmd + Shift + N for new folder
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'N') {
                e.preventDefault();
                showModal('createFolderModal');
            }
            // Ctrl/Cmd + U for upload
            if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
                e.preventDefault();
                showModal('uploadModal');
            }
            // Ctrl/Cmd + A for archive (for folders)
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                // Find first folder in table
                const firstFolder = document.querySelector('.folder-link');
                if (firstFolder) {
                    const folderName = firstFolder.textContent.trim();
                    showArchiveModal(folderName);
                }
            }
            // F5 to refresh
            if (e.key === 'F5') {
                location.reload();
            }
            // Ctrl/Cmd + S to save in editor
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && document.getElementById('editorContent')) {
                e.preventDefault();
                document.getElementById('editForm').submit();
            }
        });
        
        // Auto-focus on first input in modal
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown', function() {
                const input = this.querySelector('input, textarea, select');
                if (input) input.focus();
            });
        });
        
        // Auto-resize textarea in editor
        const editorTextarea = document.getElementById('editorContent');
        if (editorTextarea) {
            editorTextarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            // Trigger once on load
            setTimeout(() => {
                editorTextarea.style.height = (editorTextarea.scrollHeight) + 'px';
            }, 100);
        }
        
        // Show upload status when form is submitted
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('uploadSubmitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Uploading...';
                submitBtn.disabled = true;
            }
        });
        
        // Archive/unarchive form validation
        document.getElementById('archiveModal')?.querySelector('form')?.addEventListener('submit', function(e) {
            const archiveName = document.getElementById('archive_name').value;
            if (archiveName && !archiveName.toLowerCase().endsWith('.zip')) {
                document.getElementById('archive_name').value = archiveName + '.zip';
            }
        });
        
        // Check if file is an archive
        function isArchive(filename) {
            const archiveExtensions = ['.zip', '.rar', '.tar', '.gz', '.7z', '.bz2', '.xz'];
            return archiveExtensions.some(ext => filename.toLowerCase().endsWith(ext));
        }
    </script>
</body>
</html>