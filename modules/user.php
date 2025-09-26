<?php
session_start();
require 'includes/pdo.php';       // 依實際路徑
header('Content-Type: application/json; charset=utf-8');
global $conn;
$p  = $_POST;
$do = $_GET['do'] ?? '';

function jok($msg='', $extra=[])  { echo json_encode(['ok'=>true,  'msg'=>$msg] + $extra);  exit; }
function jfail($msg='', $extra=[]) { echo json_encode(['ok'=>false, 'msg'=>$msg] + $extra); exit; }

switch ($do) {
    // 登入
     case 'login_sub':
    $acc = $p['acc'] ?? '';
    $pas = $p['pas'] ?? '';

    if ($acc==='' || $pas==='') jfail('請輸入帳號與密碼');

    $stmt = $conn->prepare("SELECT * FROM userdata WHERE u_ID = ? AND u_password = ?");
    $stmt->execute([$acc, $pas]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) jfail('帳號或密碼錯誤');

    $_SESSION['u_ID']   = $user['u_ID'];
    $_SESSION['u_name'] = $user['u_name'];
    $_SESSION['u_img']  = $user['u_img'] ?? null;

    // 角色判斷（依你原本）
    $roles = $conn->query("
      SELECT r.role_ID, r.role_name
      FROM userrolesdata ur
      JOIN roledata r ON ur.role_ID = r.role_ID
      WHERE ur.u_ID = ".$conn->quote($user['u_ID'])." AND ur.user_role_status = 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    $count = count($roles);

    if ($count === 1) {
      $_SESSION['role_ID']   = $roles[0]['role_ID'];
      $_SESSION['role_name'] = $roles[0]['role_name'];
      jok('登入成功');
    } elseif ($count > 1) {
      jok('登入成功，請選擇登入身分', ['needRole'=>true, 'roles'=>$roles]);
    } else {
      jfail('此帳號尚未設定任何角色');
    }
    break;

  case 'forgot_password':
    $acc = trim($p['acc'] ?? '');
    if ($acc==='') jfail('請先輸入帳號');

    // 檢查帳號存在
    $st = $conn->prepare("SELECT u_gmail FROM userdata WHERE u_ID = ?");
    $st->execute([$acc]);
    $email = $st->fetchColumn();

    if (!$email) jfail('查無此帳號');

    // TODO：在這裡真的寄信（或寫入重設 token）
    // mail($email, '重設密碼', '請點擊連結...');

    jok('已送出重設郵件至 ' . $email);
    break;

  default:
    http_response_code(404);
    echo json_encode(['ok'=>false,'msg'=>'Unknown API']);
    exit;

    // 角色清單（啟用）
    case 'role_choose':
        $u_ID = $_SESSION['u_ID'];
        echo json_encode(fetchAll(query("
            SELECT b.role_ID,b.role_name
            FROM userrolesdata a JOIN roledata b ON a.role_ID = b.role_ID
            WHERE a.u_ID='{$u_ID}' AND a.user_role_status=1;
        ")));
        break;

    // 設定角色到 session（相容舊前端：ID/name；也接受 role_ID/role_name）
    case 'role_session':
        $_SESSION["role_ID"]   = $p["role_ID"]   ?? $p["ID"]   ?? null;
        $_SESSION["role_name"] = $p["role_name"] ?? $p["name"] ?? null;
        echo json_encode($_SESSION["role_ID"]);
        break;

    // 更新個人資料（原樣 redirect）
    case 'update_profile':
        $u_ID    = $p['u_ID'] ?? '';
        $gmail   = trim($p['u_gmail'] ?? '');
        $profile = trim($p['profile'] ?? '');
        $clear   = isset($p['clear_avatar']) && $p['clear_avatar'] === '1';

        if ($u_ID === '') {
            echo "<script>alert('缺少使用者ID');history.back();</script>";
            exit;
        }

        $old_img = null;
        if ($clear) {
            $stmt = $conn->prepare("SELECT u_img FROM userdata WHERE u_ID = ?");
            $stmt->execute([$u_ID]);
            $old_img = $stmt->fetchColumn();
        }

        $u_img_filename = null;
        if (!$clear && !empty($_FILES['u_img']['name'])) {
            $ext = pathinfo($_FILES['u_img']['name'], PATHINFO_EXTENSION);
            $u_img_filename = 'u_img_' . $u_ID . '_' . time() . '.' . $ext;
            $target_path = 'headshot/' . $u_img_filename;
            if (!move_uploaded_file($_FILES['u_img']['tmp_name'], $target_path)) {
                echo "<script>alert('頭貼上傳失敗');history.back();</script>";
                exit;
            }
        }

        $set    = ['u_gmail = ?', 'u_profile = ?'];
        $params = [$gmail, $profile];

        if ($clear) {
            $set[] = 'u_img = NULL';
        } elseif ($u_img_filename) {
            $set[] = 'u_img = ?';
            $params[] = $u_img_filename;
        }

        $sql = "UPDATE userdata SET " . implode(',', $set) . " WHERE u_ID = ?";
        $params[] = $u_ID;
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if ($clear) {
            $_SESSION['u_img'] = null;
            if ($old_img) {
                $path = __DIR__ . '/../headshot/' . $old_img;
                if (is_file($path)) @unlink($path);
            }
        } elseif ($u_img_filename) {
            $_SESSION['u_img'] = $u_img_filename;
        }

        header("Location: main.php#pages/user_profile.php");
        exit;

    // 修改密碼（原樣 redirect）
    case 'update_password':
        $u_ID         = $p['u_ID'] ?? '';
        $old_pass     = $p['old_password'] ?? '';
        $new_pass     = $p['new_password'] ?? '';
        $confirm_pass = $p['confirm_password'] ?? '';

        if ($u_ID==='' || $old_pass==='' || $new_pass==='' || $confirm_pass==='') {
            header("Location: user_profile.php?error=empty&op=$old_pass&np=$new_pass&cp=$confirm_pass");
            exit;
        }

        $stmt = $conn->prepare("SELECT u_password FROM userdata WHERE u_ID = ?");
        $stmt->execute([$u_ID]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['u_password'] !== $old_pass) {
            header("Location: user_profile.php?error=wrongold&op=$old_pass&np=$new_pass&cp=$confirm_pass");
            exit;
        }

        if ($new_pass !== $confirm_pass) {
            header("Location: user_profile.php?error=mismatch&op=$old_pass&np=$new_pass&cp=$confirm_pass");
            exit;
        }

        $stmt = $conn->prepare("UPDATE userdata SET u_password = ? WHERE u_ID = ?");
        $stmt->execute([$new_pass, $u_ID]);

        header("Location: user_profile.php?success=password");
        exit;
}
