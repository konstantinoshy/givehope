<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$adminPageTitle = $adminPageTitle ?? 'Admin';
?>
<!doctype html>
<html lang="el">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($adminPageTitle); ?> | <?php echo e(APP_NAME); ?></title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo filemtime(__DIR__ . '/../../public/css/style.css'); ?>">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css?v=<?php echo filemtime(__DIR__ . '/../../public/css/admin.css'); ?>">
</head>

<body>

  <?php include __DIR__ . '/../sidebar.php'; ?>

  <main class="main-content<?php echo isset($adminMainClass) ? ' ' . e($adminMainClass) : ''; ?>"<?php echo isset($adminMainStyle) ? ' style="' . $adminMainStyle . '"' : ''; ?>>
