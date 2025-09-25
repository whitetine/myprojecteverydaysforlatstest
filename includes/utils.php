<?php
// 統一 JSON 成功
function json_ok(array $data = [], int $code = 200): void {
    http_response_code($code); // 仍預設 200
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => 'success'], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 統一 JSON 失敗（仍回 200，避免前端視為「連線錯誤」）
function json_err(string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => 'error', 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 安全取得原始 JSON
function read_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// 關閉錯誤輸出（避免把 JSON 打壞；若要看錯誤，請看伺服器 log）
if (!headers_sent()) {
    ini_set('display_errors', '0');
}
