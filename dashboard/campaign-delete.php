<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

require_org();

if (!is_post()) {
    redirect(BASE_URL . "/dashboard/index.php");
}

csrf_verify();

$pdo = db();
$orgId = (int)current_org()['id'];
$id = (int)($_POST['id'] ?? 0);

$del = $pdo->prepare("DELETE FROM campaigns WHERE id = :id AND org_id = :oid");
$del->execute([':id' => $id, ':oid' => $orgId]);

redirect(BASE_URL . "/dashboard/index.php");

