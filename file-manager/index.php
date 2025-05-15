<?php
session_start();
$base = '/mnt/smb_' . session_id();

function safePath($base, $sub) {
    $full = realpath($base . $sub);
    return ($full && strpos($full, $base) === 0) ? $full : $base;
}

function listDir($base, $current) {
    $path = safePath($base, $current);
    $items = @scandir($path);
    $output = [];
    if ($current !== '/') $output[] = ['name' => '..', 'type' => 'dir'];
    if ($items) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full = $path . '/' . $item;
            $output[] = ['name' => $item, 'type' => is_dir($full) ? 'dir' : 'file'];
        }
    }
    return $output;
}

function getSmbShares($server, $user, $pass) {
    $cmd = sprintf("smbclient -L %s -U %s%%%s", escapeshellarg($server), escapeshellarg($user), escapeshellarg($pass));
    $output = shell_exec($cmd . " 2>&1");
    $shares = [];
    if (preg_match_all('/^\\s*(\\S+)\\s+Disk/m', $output, $matches)) {
        $shares = $matches[1];
    }
    return $shares;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $_SESSION['smb_user'] = $_POST['user'];
    $_SESSION['smb_pass'] = $_POST['pass'];
    $_SESSION['smb_server'] = '127.0.0.1';
    $_SESSION['smb_mounts'] = [];
    $_SESSION['current_share'] = null;
    $_SESSION['current_dir'] = '/';
    echo json_encode(['status' => 'success']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discover_shares'])) {
    if (!isset($_SESSION['smb_user']) || !isset($_SESSION['smb_pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    $shares = getSmbShares('127.0.0.1', $_SESSION['smb_user'], $_SESSION['smb_pass']);
    echo json_encode(['status' => 'success', 'shares' => $shares]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mount'])) {
    $share = $_POST['share'];
    $mountPath = $base . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $share);
    if (!is_dir($mountPath)) mkdir($mountPath, 0700, true);
    $cmd = sprintf("sudo mount -t cifs //127.0.0.1/%s %s -o username=%s,password=%s,rw,iocharset=utf8",
        escapeshellarg($share),
        escapeshellarg($mountPath),
        escapeshellarg($_SESSION['smb_user']),
        escapeshellarg($_SESSION['smb_pass'])
    );
    $output = shell_exec("$cmd 2>&1");
    if (strpos($output, 'error') !== false) {
        echo json_encode(['status' => 'error', 'message' => $output]);
    } else {
        $_SESSION['smb_mounts'][$share] = $mountPath;
        $_SESSION['current_share'] = $share;
        $_SESSION['current_dir'] = '/';
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if (isset($_GET['logout'])) {
    foreach ($_SESSION['smb_mounts'] as $mount) {
        shell_exec("sudo umount " . escapeshellarg($mount));
        @rmdir($mount);
    }
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'list') {
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    echo json_encode(listDir($_SESSION['smb_mounts'][$share], $dir));
    exit;
}

// For directory browser in move/copy modal (flat tree, max depth 3)
if (isset($_GET['action']) && $_GET['action'] === 'dirlist') {
    $share = $_SESSION['current_share'];
    $dir = isset($_GET['dir']) ? $_GET['dir'] : '/';
    $basePath = $_SESSION['smb_mounts'][$share];
    $result = [];
    function listSubDirs($basePath, $dir, &$result, $level = 0) {
        if ($level > 3) return;
        $full = safePath($basePath, $dir);
        $items = @scandir($full);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $sub = $full . '/' . $item;
            $rel = ($dir === '/' ? '' : $dir) . '/' . $item;
            if (is_dir($sub)) {
                $result[] = $rel;
                listSubDirs($basePath, $rel, $result, $level+1);
            }
        }
    }
    listSubDirs($basePath, $dir, $result, 0);
    sort($result, SORT_STRING);
    echo json_encode($result);
    exit;
}

if (isset($_POST['navigate'])) {
    $dir = $_POST['path'];
    $cur = $_SESSION['current_dir'] ?? '/';
    if ($dir === '..') {
        $cur = dirname($cur);
        if ($cur === '' || $cur === '.') $cur = '/';
    } elseif (strpos($dir, '/') === 0) {
        $cur = $dir;
    } else {
        $cur = rtrim($cur, '/') . '/' . $dir;
    }
    $_SESSION['current_dir'] = $cur;
    echo json_encode(['status' => 'success']);
    exit;
}

if (isset($_POST['fileAction'])) {
    $action = $_POST['fileAction'];
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    $basePath = $_SESSION['smb_mounts'][$share];

    // Bulk actions support
    $files = isset($_POST['files']) ? $_POST['files'] : [];
    if (!is_array($files)) $files = [];

    if ($action === 'delete' && count($files) > 0) {
        foreach ($files as $filename) {
            $targetPath = safePath($basePath, $dir) . "/" . basename($filename);
            is_dir($targetPath) ? shell_exec("rm -rf " . escapeshellarg($targetPath)) : @unlink($targetPath);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    if (($action === 'copy' || $action === 'move') && count($files) > 0) {
        $destName = isset($_POST['dest']) ? $_POST['dest'] : null;
        $destDir = isset($_POST['destDir']) ? $_POST['destDir'] : $dir;
        foreach ($files as $filename) {
            $targetPath = safePath($basePath, $dir) . "/" . basename($filename);
            $destPath = safePath($basePath, $destDir) . "/" . ($destName ? $destName : basename($filename));
            if ($action === 'copy') {
                is_dir($targetPath) ?
                    shell_exec("cp -r " . escapeshellarg($targetPath) . ' ' . escapeshellarg($destPath)) :
                    copy($targetPath, $destPath);
            } else {
                rename($targetPath, $destPath);
            }
        }
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Single file actions
    $filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';
    $targetPath = safePath($basePath, $dir) . "/$filename";
    $destName = isset($_POST['dest']) ? basename($_POST['dest']) : null;
    $destDir = isset($_POST['destDir']) ? $_POST['destDir'] : $dir;
    $destPath = $destName ? safePath($basePath, $destDir) . "/$destName" : null;

    if ($action === 'delete') {
        is_dir($targetPath) ? shell_exec("rm -rf " . escapeshellarg($targetPath)) : @unlink($targetPath);
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'mkdir') {
        mkdir($targetPath);
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'copy' && $destPath) {
        is_dir($targetPath) ?
            shell_exec("cp -r " . escapeshellarg($targetPath) . ' ' . escapeshellarg($destPath)) :
            copy($targetPath, $destPath);
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'move' && $destPath) {
        rename($targetPath, $destPath);
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'rename' && $destPath) {
        rename($targetPath, $destPath);
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'newtext') {
        file_put_contents($targetPath, $_POST['content'] ?? '');
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if (isset($_GET['viewfile'])) {
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    $file = basename($_GET['viewfile']);
    $filePath = safePath($_SESSION['smb_mounts'][$share], $dir) . "/$file";
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'])) {
        $type = 'image';
    } elseif (in_array($ext, ['mp3','ogg','wav'])) {
        $type = 'audio';
    } elseif (in_array($ext, ['mp4','webm','mov','avi'])) {
        $type = 'video';
    } elseif (in_array($ext, ['txt','log','md','php','js','json','xml','html','css','conf','ini','sh','py','c','cpp','rb','go','rs','yml','yaml'])) {
        $type = 'text';
    } else {
        $type = 'other';
    }
    if ($type === 'image') {
        header('Content-Type: image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
        readfile($filePath);
    } elseif ($type === 'audio') {
        header('Content-Type: audio/' . $ext);
        readfile($filePath);
    } elseif ($type === 'video') {
        header('Content-Type: video/' . ($ext === 'mp4' ? 'mp4' : $ext));
        readfile($filePath);
    } elseif ($type === 'text') {
        header('Content-Type: text/plain');
        readfile($filePath);
    } else {
        http_response_code(415);
    }
    exit;
}

if (isset($_POST['savefile'])) {
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    $file = basename($_POST['savefile']);
    $filePath = safePath($_SESSION['smb_mounts'][$share], $dir) . "/$file";
    file_put_contents($filePath, $_POST['content']);
    echo json_encode(['status' => 'success']);
    exit;
}

if (!empty($_FILES['upload'])) {
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    $files = $_FILES['upload'];
    $ok = true;
    for ($i=0; $i < count($files['name']); $i++) {
        $targetPath = safePath($_SESSION['smb_mounts'][$share], $dir) . '/' . basename($files['name'][$i]);
        if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) $ok = false;
    }
    echo json_encode(['status' => $ok ? 'success' : 'error']);
    exit;
}

if (isset($_GET['download'])) {
    $share = $_SESSION['current_share'];
    $dir = $_SESSION['current_dir'] ?? '/';
    $file = basename($_GET['file']);
    $filePath = safePath($_SESSION['smb_mounts'][$share], $dir) . "/$file";
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filePath));
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { background: #23272b; color: #eee; font-size: 1rem;}
        .bg-dark, .modal-content, .form-control, .input-group-text { background: #222 !important; color: #eee !important; }
        .navbar-dark { background: #181a1b !important; }
        .dropdown-menu-right.bg-dark { background: #23272b !important; }
        .dropdown-item { color: #fff !important; }
        .dropdown-item:hover, .dropdown-item:focus { background: #1b1e21 !important; color: #87cefa !important;}
        .list-group-item { background: #23272b; color: #eee; border-color: #343a40; }
        .list-group-item.active, .list-group-item:hover { background: #343a40 !important; color: #fff; }
        .pointer { cursor: pointer; }
        #sidebar { min-width: 190px; max-width: 240px; background: #212529; border-right: 1px solid #343a40; }
        #filelist { min-height: 420px;}
        .file-link { color: #87cefa; cursor:pointer; }
        .file-link:hover { text-decoration: underline; }
        .file-actions button, .file-actions a { margin-right: 0.2rem; }
        .modal-content { border-radius: 0.5rem;}
        .file-row .fa { min-width: 20px;}
        .modal-header, .modal-footer { border-color: #333; }
        .ace_editor { height:350px !important; min-height:320px; background: #191a1b !important; color: #fff !important;}
        .ace_gutter, .ace_gutter-active-line { background: #25282a !important;}
        .breadcrumb { background:transparent !important; }
        .page-link, .breadcrumb-item a { color: #87cefa !important; background: #23272b !important;}
        .page-item.active .page-link { background:#444 !important; border-color:#333;}
        .bulk-select { margin-right:8px; }
        .pagination { margin-bottom:0; }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['smb_user'])): ?>
    <div class="d-flex align-items-center justify-content-center" style="height:95vh;">
    <div class="card p-4 shadow-lg bg-dark" style="min-width:340px;">
        <form id="loginForm" method="post" onsubmit="event.preventDefault(); doLogin();">
            <h4 class="mb-4 text-center"><i class="fa fa-folder-open"></i> File Manager</h4>
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label>Username</label>
                <input name="user" class="form-control" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input name="pass" type="password" class="form-control" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary btn-block mt-3"><i class="fa fa-sign-in-alt"></i> Login</button>
        </form>
    </div>
    </div>
    <script>
        async function doLogin() {
            const form = new FormData(document.getElementById('loginForm'));
            const res = await fetch('', { method: 'POST', body: form });
            const json = await res.json();
            if (json.status === 'success') location.reload();
            else alert('Login failed');
        }
    </script>
<?php else: ?>
    <nav class="navbar navbar-expand navbar-dark px-3 mb-4 shadow-sm">
        <span class="navbar-brand font-weight-bold"><i class="fa fa-folder-open"></i> File Manager</span>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                    <i class="fa fa-user-circle"></i> <?=htmlspecialchars($_SESSION['smb_user'])?>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-dark" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="?logout=1"><i class="fa fa-sign-out-alt"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>
    <div class="d-flex" style="min-height:85vh">
        <div id="sidebar" class="py-3 px-2">
            <h6 class="text-light">Shares</h6>
            <ul id="shares" class="list-group mb-2"></ul>
        </div>
        <div class="flex-grow-1 pl-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                    <h5 id="currentShare" class="mb-0"></h5>
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm mr-1" onclick="showNewTextModal()"><i class="fa fa-file-alt"></i> New Text</button>
                    <button class="btn btn-outline-secondary btn-sm mr-1" onclick="showNewFolderModal()"><i class="fa fa-folder-plus"></i> New Folder</button>
                    <form style="display:inline" onsubmit="event.preventDefault(); uploadFile();">
                        <input type="file" id="file_upload" style="display:none" multiple onchange="uploadFile()">
                        <button class="btn btn-outline-info btn-sm" type="button" onclick="document.getElementById('file_upload').click()"><i class="fa fa-upload"></i> Upload</button>
                    </form>
                </div>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb py-2" id="breadcrumbs"></ol>
            </nav>
            <input id="fileSearch" class="form-control form-control-sm mb-2" placeholder="Search files/folders...">
            <div class="mb-2">
                <input type="checkbox" id="selectAll" onchange="selectAllFiles(this)">
                <label for="selectAll" style="user-select:none;">Select All</label>
                <button class="btn btn-sm btn-outline-primary" onclick="bulkAction('copy')"><i class="fa fa-copy"></i> Copy</button>
                <button class="btn btn-sm btn-outline-warning" onclick="bulkAction('move')"><i class="fa fa-arrows-alt"></i> Move</button>
                <button class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')"><i class="fa fa-trash"></i> Delete</button>
            </div>
            <ul class="list-group mb-2" id="filelist"></ul>
            <nav><ul class="pagination pagination-sm justify-content-center" id="pagination"></ul></nav>
        </div>
    </div>
    <!-- File View/Edit Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="fileModal">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle"></h5>
            <button type="button" class="close" onclick="closeModal()">&times;</button>
          </div>
          <div class="modal-body" id="modalBody"></div>
          <div class="modal-footer" id="modalFooter"></div>
        </div>
      </div>
    </div>
    <!-- Move/Copy Modal with dir browser -->
    <div class="modal fade" tabindex="-1" role="dialog" id="moveCopyModal">
      <div class="modal-dialog" role="document">
        <form class="modal-content" id="moveCopyForm" onsubmit="event.preventDefault(); submitMoveCopy();">
          <div class="modal-header">
            <h5 class="modal-title" id="moveCopyTitle"></h5>
            <button type="button" class="close" onclick="closeMoveCopy()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group mb-2">
                <label>New Name</label>
                <input type="text" class="form-control" id="destName" required>
            </div>
            <div class="form-group mb-0">
                <label>Select Destination Folder</label>
                <div id="dirBrowser" style="max-height:170px;overflow:auto;border:1px solid #333;padding:6px;border-radius:4px;background:#25282a;"></div>
                <input type="hidden" id="destDir" value="/">
            </div>
            <small class="text-muted">Pick a folder and optionally rename. Your current directory is highlighted.</small>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="moveCopyBtn">OK</button>
            <button type="button" class="btn btn-secondary" onclick="closeMoveCopy()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Rename Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="renameModal">
      <div class="modal-dialog" role="document">
        <form class="modal-content" id="renameForm" onsubmit="event.preventDefault(); submitRename();">
          <div class="modal-header">
            <h5 class="modal-title">Rename</h5>
            <button type="button" class="close" onclick="closeRename()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label>New Name</label>
                <input type="text" class="form-control" id="renameName" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Rename</button>
            <button type="button" class="btn btn-secondary" onclick="closeRename()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/ext-language_tools.min.js"></script>
    <script>
        let currentShare = '';
        let currentDir = '/';
        let moveCopyAction = null, moveCopyFile = null, bulkModeFiles = null;
        let renameFile = null;
        let filesPerPage = 30, currentPage = 1, allFiles = [];

        async function loadShares() {
            const res = await fetch('', { method: 'POST', body: new URLSearchParams({discover_shares: '1'}) });
            const json = await res.json();
            const ul = document.getElementById('shares');
            ul.innerHTML = '';
            if (!json.shares) return;
            json.shares.forEach(s => {
                const li = document.createElement('li');
                li.className = 'list-group-item pointer' + (s === (window.currentShare||'') ? ' active' : '');
                li.innerHTML = '<i class="fa fa-server mr-2"></i>' + s;
                li.onclick = () => mountShare(s);
                ul.appendChild(li);
            });
        }
        async function mountShare(share) {
            const form = new FormData();
            form.append('mount', '1');
            form.append('share', share);
            const res = await fetch('', { method: 'POST', body: form });
            const result = await res.json();
            if (result.status === 'success') location.reload();
            else alert(result.message);
        }
        async function loadFiles() {
            const res = await fetch(`?action=list`);
            const files = await res.json();
            allFiles = files;
            currentPage = 1;
            renderBreadcrumbs();
            renderPagination(allFiles.length);
            renderFileList();
            $('#selectAll').prop('checked',false);
        }
        function renderFileList(filesOverride) {
            let start = (currentPage-1)*filesPerPage;
            let files = filesOverride || allFiles.slice(start, start+filesPerPage);
            const ul = document.getElementById('filelist');
            ul.innerHTML = '';
            files.forEach(f => {
                let ext = f.name.split('.').pop().toLowerCase();
                let icon = f.type === 'dir'
                  ? '<i class="fa fa-folder-open text-warning mr-2"></i>'
                  : ['jpg','jpeg','png','gif','webp','bmp'].includes(ext) ? '<i class="fa fa-file-image text-info mr-2"></i>'
                  : ['mp3','ogg','wav'].includes(ext) ? '<i class="fa fa-music text-info mr-2"></i>'
                  : ['mp4','webm','mov','avi'].includes(ext) ? '<i class="fa fa-film text-info mr-2"></i>'
                  : ['txt','log','md','php','js','json','xml','html','css','conf','ini','sh','py','c','cpp','rb','go','rs','yml','yaml'].includes(ext) ? '<i class="fa fa-file-code text-success mr-2"></i>'
                  : '<i class="fa fa-file text-secondary mr-2"></i>';

                const li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center file-row justify-content-between py-2 px-2';
                let mainLink;
                if (f.type === 'dir') {
                    mainLink = `<span class="file-link" onclick="navigate('${f.name}')">${icon}${f.name}</span>`;
                } else if(['mp3','ogg','wav'].includes(ext)) {
                    mainLink = `<span class="file-link" onclick="playMedia('${f.name}','audio')">${icon}${f.name}</span>`;
                } else if(['mp4','webm','mov','avi'].includes(ext)) {
                    mainLink = `<span class="file-link" onclick="playMedia('${f.name}','video')">${icon}${f.name}</span>`;
                } else if(['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
                    mainLink = `<span class="file-link" onclick="viewImage('${f.name}')">${icon}${f.name}</span>`;
                } else if(['txt','log','md','php','js','json','xml','html','css','conf','ini','sh','py','c','cpp','rb','go','rs','yml','yaml'].includes(ext)) {
                    mainLink = `<span class="file-link" onclick="viewText('${f.name}')">${icon}${f.name}</span>`;
                } else {
                    mainLink = `<span>${icon}${f.name}</span>`;
                }
                // Actions
                let actions = `<div class="file-actions d-flex align-items-center">`;
                if (f.name !== '..') {
                    actions += `<input type="checkbox" class="bulk-select" value="${f.name}" style="margin-right:6px;">`;
                }
                if (f.type === 'file') {
                    actions += `
                        <button class="btn btn-sm btn-outline-primary" title="Copy" onclick="showMoveCopyModal('copy','${f.name}')"><i class="fa fa-copy"></i></button>
                        <button class="btn btn-sm btn-outline-warning" title="Move" onclick="showMoveCopyModal('move','${f.name}')"><i class="fa fa-arrows-alt"></i></button>
                        <button class="btn btn-sm btn-outline-success" title="Rename" onclick="showRenameModal('${f.name}')"><i class="fa fa-edit"></i></button>
                        <a class="btn btn-sm btn-outline-info" title="Download" href="?download=1&file=${encodeURIComponent(f.name)}"><i class="fa fa-download"></i></a>
                    `;
                }
                if (f.name !== '..') {
                    actions += `<button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteFile('${f.name}')"><i class="fa fa-trash"></i></button>`;
                }
                actions += `</div>`;
                li.innerHTML = `<div>${mainLink}</div>${actions}`;
                ul.appendChild(li);
            });
        }
        function renderBreadcrumbs() {
            const crumbs = (window.currentDir||'/').replace(/^\/|\/$/g,'').split('/');
            let path = '';
            let html = `<li class="breadcrumb-item"><a href="#" onclick="jumpToBreadcrumb('/')"><i class="fa fa-home"></i></a></li>`;
            for (let i = 0; i < crumbs.length; i++) {
                if (crumbs[i]==='') continue;
                path += '/' + crumbs[i];
                if (i < crumbs.length - 1)
                    html += `<li class="breadcrumb-item"><a href="#" onclick="jumpToBreadcrumb('${path}')">${crumbs[i]}</a></li>`;
                else
                    html += `<li class="breadcrumb-item active">${crumbs[i]}</li>`;
            }
            $('#breadcrumbs').html(html);
        }
        function jumpToBreadcrumb(dir) {
            window.currentDir = dir;
            const form = new FormData();
            form.append('navigate', '1');
            form.append('path', dir);
            fetch('', { method: 'POST', body: form }).then(()=>loadFiles());
        }
        function renderPagination(totalFiles) {
            let totalPages = Math.ceil(totalFiles / filesPerPage);
            let html = '';
            for (let i = 1; i <= totalPages; i++) {
                html += `<li class="page-item${i===currentPage?' active':''}">
                  <a class="page-link" href="#" onclick="gotoPage(${i});return false;">${i}</a>
                </li>`;
            }
            $('#pagination').html(html);
        }
        function gotoPage(p) {
            currentPage = p;
            renderFileList();
        }
        $('#fileSearch').on('input', function(){
            let q = $(this).val().toLowerCase();
            if (!q) {
                currentPage = 1;
                renderFileList();
                renderPagination(allFiles.length);
                return;
            }
            let filtered = allFiles.filter(f=>f.name.toLowerCase().includes(q));
            currentPage = 1;
            renderFileList(filtered);
            $('#pagination').html(''); // hide pagination for search results
        });
        function selectAllFiles(cb) {
            $('.bulk-select').prop('checked', cb.checked);
        }
        function getSelectedFiles() {
            return Array.from(document.querySelectorAll('.bulk-select:checked')).map(e=>e.value);
        }
        function bulkAction(action) {
            let files = getSelectedFiles();
            if (!files.length) return alert("Select files first!");
            if (action==='delete' && !confirm("Delete selected files?")) return;
            if (action==='delete') {
                const form = new FormData();
                form.append('fileAction','delete');
                for(let f of files) form.append('files[]',f);
                fetch('',{method:'POST',body:form}).then(()=>setTimeout(loadFiles,400));
            } else {
                // For move/copy, open modal as before, apply to all selected
                bulkModeFiles = files;
                showMoveCopyModal(action, files[0], true);
            }
        }
        async function navigate(dir) {
            const form = new FormData();
            form.append('navigate', '1');
            form.append('path', dir);
            await fetch('', { method: 'POST', body: form });
            if (dir === '..') {
                window.currentDir = window.currentDir.replace(/\/?[^\/]+$/, '') || '/';
            } else if (dir.startsWith('/')) {
                window.currentDir = dir;
            } else {
                window.currentDir = (window.currentDir === '/' ? '' : window.currentDir) + '/' + dir;
            }
            loadFiles();
        }
        async function deleteFile(name) {
            if (!confirm(`Delete ${name}?`)) return;
            const form = new FormData();
            form.append('fileAction', 'delete');
            form.append('filename', name);
            const res = await fetch('', { method: 'POST', body: form });
            if ((await res.json()).status === 'success') loadFiles();
        }
        function showMoveCopyModal(action, name, isBulk) {
            moveCopyAction = action;
            moveCopyFile = name;
            $('#moveCopyModal').modal('show');
            $('#moveCopyTitle').text((action === 'move' ? 'Move ' : 'Copy ') + (isBulk ? 'Selected Files' : name));
            $('#destName').val(isBulk ? '' : name);
            buildDirBrowser();
        }
        async function buildDirBrowser() {
            const res = await fetch(`?action=dirlist`);
            const dirs = await res.json();
            let html = `<div class="dir-opt pointer py-1 px-2 rounded" onclick="setDestDir('/')" style="background:#333;">/</div>`;
            dirs.forEach(d => {
                html += `<div class="dir-opt pointer py-1 px-3 ml-2 rounded" onclick="setDestDir('${d}')">${d}</div>`;
            });
            document.getElementById('dirBrowser').innerHTML = html;
            setDestDir(window.currentDir || '/');
        }
        function setDestDir(path) {
            $('#dirBrowser .dir-opt').css('background','#25282a');
            let match = Array.from(document.querySelectorAll('.dir-opt')).find(x => x.textContent.trim()===path.trim());
            if(match) match.style.background='#225788';
            document.getElementById('destDir').value = path;
        }
        async function submitMoveCopy() {
            let dest = document.getElementById('destName').value.trim();
            let destDir = document.getElementById('destDir').value.trim() || '/';
            if (bulkModeFiles && bulkModeFiles.length) {
                const form = new FormData();
                form.append('fileAction', moveCopyAction);
                for(let f of bulkModeFiles) form.append('files[]',f);
                form.append('destDir', destDir);
                if (dest) form.append('dest', dest); // Optional: for mass rename
                $('#moveCopyModal').modal('hide');
                await fetch('', { method: 'POST', body: form });
                setTimeout(loadFiles, 600);
                bulkModeFiles = null;
                return;
            }
            if (!dest) { alert("New name required"); return; }
            const form = new FormData();
            form.append('fileAction', moveCopyAction);
            form.append('filename', moveCopyFile);
            form.append('dest', dest);
            form.append('destDir', destDir);
            const res = await fetch('', { method: 'POST', body: form });
            $('#moveCopyModal').modal('hide');
            if ((await res.json()).status === 'success') loadFiles();
        }
        function closeMoveCopy() { $('#moveCopyModal').modal('hide'); }
        function showRenameModal(name) {
            renameFile = name;
            $('#renameModal').modal('show');
            $('#renameName').val(name);
        }
        async function submitRename() {
            let newName = document.getElementById('renameName').value.trim();
            if (!newName) { alert("New name required"); return; }
            const form = new FormData();
            form.append('fileAction', 'rename');
            form.append('filename', renameFile);
            form.append('dest', newName);
            const res = await fetch('', { method: 'POST', body: form });
            $('#renameModal').modal('hide');
            if ((await res.json()).status === 'success') loadFiles();
        }
        function closeRename() { $('#renameModal').modal('hide'); }
        function showNewTextModal() {
            document.getElementById('modalTitle').textContent = "New Text File";
            document.getElementById('modalBody').innerHTML = `<input id="newTextName" class="form-control mb-2" placeholder="filename.txt"><div id="aceEdit" style="height:200px;width:100%;"></div>`;
            setTimeout(function(){
                let editor = ace.edit("aceEdit");
                editor.setTheme("ace/theme/monokai");
                editor.getSession().setMode("ace/mode/text");
                editor.setValue('');
                window._aceEditor = editor;
            },200);
            document.getElementById('modalFooter').innerHTML = `<button class="btn btn-primary" onclick="createTextFile()">Create</button>`;
            $('#fileModal').modal('show');
        }
        function showNewFolderModal() {
            document.getElementById('modalTitle').textContent = "New Folder";
            document.getElementById('modalBody').innerHTML = `<input id="newFolderName" class="form-control mb-2" placeholder="Folder Name">`;
            document.getElementById('modalFooter').innerHTML = `<button class="btn btn-primary" onclick="createFolder()">Create</button>`;
            $('#fileModal').modal('show');
        }
        async function createTextFile() {
            let name = document.getElementById('newTextName').value.trim();
            let content = window._aceEditor ? window._aceEditor.getValue() : '';
            if (!name) { alert("Filename required"); return; }
            const form = new FormData();
            form.append('fileAction', 'newtext');
            form.append('filename', name);
            form.append('content', content);
            const res = await fetch('', { method: 'POST', body: form });
            $('#fileModal').modal('hide');
            if ((await res.json()).status === 'success') loadFiles();
        }
        async function createFolder() {
            let name = document.getElementById('newFolderName').value.trim();
            if (!name) { alert("Folder name required"); return; }
            const form = new FormData();
            form.append('fileAction', 'mkdir');
            form.append('filename', name);
            const res = await fetch('', { method: 'POST', body: form });
            $('#fileModal').modal('hide');
            if ((await res.json()).status === 'success') loadFiles();
        }
        async function uploadFile() {
            const input = document.getElementById('file_upload');
            if (!input.files.length) return;
            const form = new FormData();
            for(let i=0;i<input.files.length;i++)
                form.append('upload[]', input.files[i]);
            const res = await fetch('', { method: 'POST', body: form });
            if ((await res.json()).status === 'success') loadFiles();
        }
        async function viewImage(name) {
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('modalBody').innerHTML = `<img src="?viewfile=${encodeURIComponent(name)}" class="img-fluid">`;
            document.getElementById('modalFooter').innerHTML = '';
            $('#fileModal').modal('show');
        }
        function playMedia(name, type) {
            document.getElementById('modalTitle').textContent = name;
            let media = type==='audio' 
                ? `<audio controls style="width:100%"><source src="?viewfile=${encodeURIComponent(name)}"></audio>`
                : `<video controls style="width:100%;max-height:420px"><source src="?viewfile=${encodeURIComponent(name)}"></video>`;
            document.getElementById('modalBody').innerHTML = media;
            document.getElementById('modalFooter').innerHTML = '';
            $('#fileModal').modal('show');
        }
        async function viewText(name) {
            const res = await fetch(`?viewfile=${encodeURIComponent(name)}`);
            const text = await res.text();
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('modalBody').innerHTML = `<div id="aceEdit" style="height:350px;width:100%;"></div>`;
            setTimeout(function(){
                let ext = name.split('.').pop().toLowerCase();
                let mode = "text";
                let modes = {
                    "php":"php","js":"javascript","json":"json","xml":"xml","md":"markdown","css":"css",
                    "conf":"text","ini":"ini","sh":"sh","py":"python","c":"c_cpp","cpp":"c_cpp",
                    "rb":"ruby","go":"golang","rs":"rust","yml":"yaml","yaml":"yaml","html":"html"
                };
                if (modes[ext]) mode = modes[ext];
                let editor = ace.edit("aceEdit");
                editor.setTheme("ace/theme/monokai");
                editor.getSession().setMode("ace/mode/"+mode);
                editor.setValue(text, -1);
                editor.setOptions({enableBasicAutocompletion: true, enableLiveAutocompletion: true});
                window._aceEditor = editor;
            },200);
            document.getElementById('modalFooter').innerHTML = `<button class="btn btn-primary" onclick="saveText('${name}')">Save</button>`;
            $('#fileModal').modal('show');
        }
        async function saveText(name) {
            const content = window._aceEditor ? window._aceEditor.getValue() : '';
            const form = new FormData();
            form.append('savefile', name);
            form.append('content', content);
            const res = await fetch('', { method: 'POST', body: form });
            $('#fileModal').modal('hide');
            if ((await res.json()).status === 'success') loadFiles();
        }
        function closeModal() { $('#fileModal').modal('hide'); }
        window.currentShare = <?= json_encode($_SESSION['current_share'] ?? '') ?>;
        window.currentDir = <?= json_encode($_SESSION['current_dir'] ?? '/') ?>;
        $(function() {
            loadShares();
            if (window.currentShare) loadFiles();
        });
    </script>
<?php endif; ?>
</body>
</html>
