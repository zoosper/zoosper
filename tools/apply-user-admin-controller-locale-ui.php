<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginController = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper explicit UserAdminController locale UI integration\n";
print "==========================================================\n\n";

if (!is_file($userAdminController)) {
    fwrite(STDERR, "Missing UserAdminController: {$userAdminController}\n");
    exit(2);
}

$source = (string) file_get_contents($userAdminController);
if (str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) {
    print "- UserAdminController already contains a locale field\n";
    print "Result: OK\n";
    exit(0);
}

if (is_file($loginController)) {
    $loginSource = (string) file_get_contents($loginController);
    if (str_contains($loginSource, 'name="locale"') || str_contains($loginSource, "name='locale'")) {
        fwrite(STDERR, "LoginController still contains a locale field. Apply Phase 1.06.1 hotfix first.\n");
        exit(2);
    }
}

$insert = <<<'HTML'

        <div class="admin-form-field admin-form-field--locale">
            <label for="admin-user-locale">Admin interface locale</label>
            <select id="admin-user-locale" name="locale">
                <option value="">Use configured admin locale</option>
                <option value="en_AU" <?= (($submitted['locale'] ?? $user->locale ?? '') === 'en_AU') ? 'selected' : '' ?>>English (Australia)</option>
            </select>
            <small class="admin-form-help">Leave blank to use the configured admin locale.</small>
        </div>
HTML;

$updated = insert_after_email_field($source, $insert);
if ($updated === null) {
    fwrite(STDERR, "Unable to find a safe email-field insertion point in UserAdminController.\n");
    fwrite(STDERR, "Run tools/diagnose-user-admin-controller-locale-ui.php and share the output.\n");
    exit(2);
}

$backup = $userAdminController . '.phase-1.07.bak';
if (!is_file($backup)) {
    copy($userAdminController, $backup);
    print "- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.07.bak\n";
}

file_put_contents($userAdminController, $updated);
print "- updated app/zoosper-admin/src/Controller/UserAdminController.php\n";
print "Result: OK\n";

function insert_after_email_field(string $source, string $insert): ?string
{
    $patterns = [
        '/(<[^>]+name=["\']email["\'][^>]*>\s*<\/[^>]+>)/is',
        '/(<input[^>]+name=["\']email["\'][^>]*>)/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE) === 1) {
            $position = $match[0][1] + strlen($match[0][0]);

            return substr($source, 0, $position) . $insert . substr($source, $position);
        }
    }

    return null;
}
