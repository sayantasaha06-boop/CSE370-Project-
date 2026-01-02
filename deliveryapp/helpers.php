<?php
// backend/helpers.php

function read_json_body(): array {
  $raw = file_get_contents("php://input");
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function respond_json($payload, int $status = 200): void {
  header("Content-Type: application/json");
  http_response_code($status);
  echo json_encode($payload);
  exit;
}

function otp_code(int $digits = 6): string {
  $min = (int) pow(10, $digits-1);
  $max = (int) pow(10, $digits) - 1;
  return (string) random_int($min, $max);
}

// simple sanitizer for output
function s($v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}
