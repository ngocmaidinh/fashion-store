<?php
session_start();

function db() {
  $c = require '/etc/shop/db-config.php';
  $db = new mysqli($c['host'], $c['user'], $c['pass'], $c['name']);
  $db->set_charset('utf8mb4');
  return $db;
}

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function requireLogin() {
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
  }
}
