<?php

declare(strict_types=1);

namespace Zoosper\Auth\Dashboard;

use JsonException;
use PDO;
use RuntimeException;
use Zoosper\AdminDashboard\Contract\DashboardRolePreferenceRepositoryInterface;
use Zoosper\AdminDashboard\DashboardRole;
use Zoosper\AdminDashboard\DashboardRolePreference;

final readonly class DashboardRolePreferenceRepository implements DashboardRolePreferenceRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function roles(): array
    {
        $rows = $this->pdo->query('SELECT id, code, label FROM admin_roles ORDER BY code ASC')->fetchAll();

        return array_map(
            static fn (array $row): DashboardRole => new DashboardRole((int) $row['id'], (string) $row['code'], (string) $row['label']),
            $rows,
        );
    }

    public function findForRole(int $roleId): ?DashboardRolePreference
    {
        $statement = $this->pdo->prepare(
            'SELECT r.id AS role_id, r.code AS role_code, p.hidden_widget_codes_json, p.widget_order_json '
            . 'FROM admin_roles r LEFT JOIN admin_role_dashboard_preferences p ON p.role_id = r.id '
            . 'WHERE r.id = :role_id LIMIT 1',
        );
        $statement->execute(['role_id' => $roleId]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }
        if ($row['hidden_widget_codes_json'] === null || $row['widget_order_json'] === null) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findForUser(int $adminUserId): array
    {
        if ($adminUserId <= 0) {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT r.id AS role_id, r.code AS role_code, p.hidden_widget_codes_json, p.widget_order_json '
            . 'FROM admin_user_roles ur INNER JOIN admin_roles r ON r.id = ur.role_id '
            . 'INNER JOIN admin_role_dashboard_preferences p ON p.role_id = r.id '
            . 'WHERE ur.user_id = :user_id ORDER BY r.code ASC',
        );
        $statement->execute(['user_id' => $adminUserId]);

        return array_map(fn (array $row): DashboardRolePreference => $this->hydrate($row), $statement->fetchAll());
    }

    public function saveForRole(int $roleId, array $hiddenWidgetCodes, array $widgetOrder): void
    {
        $role = $this->role($roleId);
        $preference = new DashboardRolePreference($role->id, $role->code, $hiddenWidgetCodes, $widgetOrder);
        $now = gmdate('Y-m-d H:i:s');
        $payload = [
            'role_id' => $preference->roleId,
            'hidden' => json_encode($preference->hiddenWidgetCodes, JSON_THROW_ON_ERROR),
            'ordering' => json_encode($preference->widgetOrder, JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ];
        $existing = $this->pdo->prepare('SELECT role_id FROM admin_role_dashboard_preferences WHERE role_id = :role_id');
        $existing->execute(['role_id' => $roleId]);
        if ($existing->fetchColumn() !== false) {
            $statement = $this->pdo->prepare(
                'UPDATE admin_role_dashboard_preferences SET hidden_widget_codes_json = :hidden, widget_order_json = :ordering, updated_at = :updated_at WHERE role_id = :role_id',
            );
        } else {
            $statement = $this->pdo->prepare(
                'INSERT INTO admin_role_dashboard_preferences (role_id, hidden_widget_codes_json, widget_order_json, updated_at) '
                . 'VALUES (:role_id, :hidden, :ordering, :updated_at)',
            );
        }
        $statement->execute($payload);
    }

    public function clearForRole(int $roleId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_role_dashboard_preferences WHERE role_id = :role_id');
        $statement->execute(['role_id' => $roleId]);
    }

    private function role(int $roleId): DashboardRole
    {
        foreach ($this->roles() as $role) {
            if ($role->id === $roleId) {
                return $role;
            }
        }

        throw new RuntimeException('Dashboard role does not exist.');
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): DashboardRolePreference
    {
        try {
            $hidden = json_decode((string) $row['hidden_widget_codes_json'], true, 512, JSON_THROW_ON_ERROR);
            $order = json_decode((string) $row['widget_order_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Stored Dashboard role preference is invalid.', previous: $exception);
        }
        if (!is_array($hidden) || !array_is_list($hidden) || !is_array($order) || !array_is_list($order)) {
            throw new RuntimeException('Stored Dashboard role preference is invalid.');
        }

        return new DashboardRolePreference((int) $row['role_id'], (string) $row['role_code'], $hidden, $order);
    }
}










