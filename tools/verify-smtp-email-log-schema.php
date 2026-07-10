<?php

declare(strict_types=1);

/**
 * Verify SMTP email log table/columns without reading message content.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$columns = ['id','message_uuid','transport','status','from_email','from_name','to_emails','subject','text_body','html_body','error_class','error_message','created_at','sent_at','failed_at'];

print "Zoosper SMTP email log schema verification\n";
print "==========================================\n\n";

$tableExists = false;
$statement = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
$statement->execute(['table' => 'smtp_email_log']);
$tableExists = (bool) $statement->fetchColumn();
print '- smtp_email_log: ' . ($tableExists ? 'exists' : 'missing') . PHP_EOL;
if (!$tableExists) {
    print "\nResult: FAIL\nRun php bin/zoosper migrate\n";
    exit(2);
}

$statement = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
$statement->execute(['table' => 'smtp_email_log']);
$actual = array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
$failed = false;
foreach ($columns as $column) {
    $ok = in_array($column, $actual, true);
    print '- ' . $column . ': ' . ($ok ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
