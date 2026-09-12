<?php
/**
 * Contact form handler for Ziyad Sultan portfolio.
 * Sends messages to ziyadsultanduri22@gmail.com and returns "OK" for the template AJAX form.
 */

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method not allowed';
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $subject === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo 'Please fill in all fields with a valid email.';
  exit;
}

$receiving_email = 'ziyadsultanduri22@gmail.com';

$payload = http_build_query([
  'name' => $name,
  'email' => $email,
  'subject' => $subject,
  'message' => $message,
  '_subject' => 'Portfolio contact: ' . $subject,
]);

$context = stream_context_create([
  'http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
    'content' => $payload,
    'timeout' => 20,
    'ignore_errors' => true,
  ],
]);

$response = @file_get_contents('https://formsubmit.co/ajax/' . rawurlencode($receiving_email), false, $context);

if ($response !== false) {
  $data = json_decode($response, true);
  if (!empty($data['success'])) {
    echo 'OK';
    exit;
  }
}

$headers = "From: {$name} <{$email}>\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$body = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\nMessage:\n{$message}";

if (@mail($receiving_email, $subject, $body, $headers)) {
  echo 'OK';
  exit;
}

http_response_code(500);
echo 'Unable to send your message. Please email ziyadsultanduri22@gmail.com directly.';
