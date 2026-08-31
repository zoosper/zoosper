<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use JsonException;
use PDO;

final readonly class DashboardPreferenceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findForUser(int $adminUserId): ?DashboardPreference
    {
        $statement = $this->pdo->prepare(
            'SELECT hidden_widget_codes_json, widget_order_json '
            . 'FROM admin_dashboard_preferences WHERE admin_user_id = :admin_user_id LIMIT 1',
        );
        $statement->execute(['admin_user_id' => $adminUserId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        try {
            $hidden = $this->decodeStringList($row['hidden_widget_codes_json'] ?? null);
            $order = $this->decodeStringList($row['widget_order_json'] ?? null);
        } catch (JsonException) {
            return null;
        }

        return $hidden === null || $order === null ? null : new DashboardPreference($hidden, $order);
    }

    public function saveForUser(int $adminUserId, DashboardPreference $preference): void
    {
        $values = [
            'admin_user_id' => $adminUserId,
            'hidden' => json_encode($preference->hiddenWidgetCodes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'widget_order' => json_encode($preference->widgetOrder, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        $existing = $this->pdo->prepare(
            'SELECT id FROM admin_dashboard_preferences WHERE admin_user_id = :admin_user_id LIMIT 1',
        );
        $existing->execute(['admin_user_id' => $adminUserId]);
        $id = $existing->fetchColumn();

        if ($id !== false) {
            $statement = $this->pdo->prepare(
                'UPDATE admin_dashboard_preferences SET hidden_widget_codes_json = :hidden, '
                . 'widget_order_json = :widget_order, updated_at = :updated_at WHERE id = :id',
            );
            unset($values['admin_user_id']);
            $values['id'] = (int) $id;
            $statement->execute($values);
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO admin_dashboard_preferences '
            . '(admin_user_id, hidden_widget_codes_json, widget_order_json, updated_at) '
            . 'VALUES (:admin_user_id, :hidden, :widget_order, :updated_at)',
        );
        $statement->execute($values);
    }

    public function clearForUser(int $adminUserId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM admin_dashboard_preferences WHERE admin_user_id = :admin_user_id',
        );
        $statement->execute(['admin_user_id' => $adminUserId]);
    }

    /** @return list<string>|null */
    private function decodeStringList(mixed $json): ?array
    {
        if (!is_string($json) || $json === '') {
            return null;
        }
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || !array_is_list($decoded)) {
            return null;
        }
        foreach ($decoded as $value) {
            if (!is_string($value)) {
                return null;
            }
        }

        return array_values($decoded);
    }
}










