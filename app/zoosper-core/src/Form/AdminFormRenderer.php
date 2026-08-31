<?php

declare(strict_types=1);

namespace Zoosper\Core\Form;


/**
 * Basic renderer for Admin forms.
 */
final readonly class AdminFormRenderer
{
    public function __construct()
    {
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $errors
     */
    public function render(
        AdminFormDefinition $form,
        array $values = [],
        string $action = '',
        string $method = 'POST',
        array $errors = [],
        ?string $cancelUrl = null,
        ?string $csrfToken = null
    ): string {
        $html = '<form action="' . htmlspecialchars($action, ENT_QUOTES) . '" method="' . htmlspecialchars($method, ENT_QUOTES) . '" class="admin-form">';

        if ($csrfToken !== null) {
            $html .= '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES) . '">';
        }

        $fieldsBySection = [];
        foreach ($form->fields as $field) {
            $fieldsBySection[$field->section][] = $field;
        }

        foreach ($fieldsBySection as $sectionHandle => $fields) {
            $metadata = $form->sections[$sectionHandle] ?? null;
            $title = $metadata['title'] ?? ($sectionHandle === 'default' ? '' : ucfirst($sectionHandle));
            $description = $metadata['description'] ?? null;

            if ($title !== '') {
                $html .= '<section class="card admin-form-section">';
                $html .= '<div class="card__header"><div><h2 class="card__title">' . htmlspecialchars($title, ENT_QUOTES) . '</h2>';
                if ($description) {
                    $html .= '<p class="muted">' . htmlspecialchars($description, ENT_QUOTES) . '</p>';
                }
                $html .= '</div></div>';
                $html .= '<div class="card__body">';
            }

            foreach ($fields as $field) {
                $html .= $this->renderField($field, $values[$field->name] ?? null, $errors[$field->name] ?? null);
            }

            if ($title !== '') {
                $html .= '</div></section>';
            }
        }

        $html .= '<div class="form-actions">';
        $html .= '<button type="submit" class="button button--primary">Save</button>';
        if ($cancelUrl !== null) {
            $html .= ' <a href="' . htmlspecialchars($cancelUrl, ENT_QUOTES) . '" class="button button--secondary">Cancel</a>';
        }
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }

    private function renderField(AdminFormField $field, mixed $value, ?string $error): string
    {
        $label = htmlspecialchars($field->label, ENT_QUOTES);
        $name = htmlspecialchars($field->name, ENT_QUOTES);
        $type = htmlspecialchars($field->type, ENT_QUOTES);
        $errorHtml = $error ? '<div class="field-error">' . htmlspecialchars($error, ENT_QUOTES) . '</div>' : '';
        $groupClass = $error ? 'form-group has-error' : 'form-group';

        $html = '<div class="' . $groupClass . '">';
        if ($field->type !== 'checkbox') {
            $html .= '<label for="' . $name . '">' . $label . '</label>';
        }

        if ($field->type === 'select') {
            $html .= '<select id="' . $name . '" name="' . $name . '" class="form-control">';
            foreach (($field->config['options'] ?? []) as $k => $v) {
                $selected = (string) $k === (string) ($value ?? '') ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '"' . $selected . '>' . htmlspecialchars((string) $v, ENT_QUOTES) . '</option>';
            }
            $html .= '</select>';
        } elseif ($field->type === 'textarea') {
            $html .= '<textarea id="' . $name . '" name="' . $name . '" class="form-control">' . htmlspecialchars((string) ($value ?? ''), ENT_QUOTES) . '</textarea>';
        } elseif ($field->type === 'password') {
            $html .= '<input type="password" id="' . $name . '" name="' . $name . '" value="" class="form-control" autocomplete="new-password">';
        } elseif ($field->type === 'checkbox') {
            $checked = $value ? ' checked' : '';
            $html .= '<div class="checkbox-wrapper"><input type="checkbox" id="' . $name . '" name="' . $name . '" value="1"' . $checked . '> <label for="' . $name . '" class="checkbox-label">' . $label . '</label></div>';
        } elseif ($field->type === 'checkbox-list') {
            $html .= '<div class="checkbox-list">';
            foreach (($field->config['options'] ?? []) as $k => $v) {
                $checked = in_array((string) $k, array_map('strval', (array) ($value ?? [])), true) ? ' checked' : '';
                $html .= '<label class="checkbox-list-item"><input type="checkbox" name="' . $name . '[]" value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '"' . $checked . '><span>' . htmlspecialchars((string) $v, ENT_QUOTES) . '</span></label>';
            }
            $html .= '</div>';
        } elseif ($field->type === 'html') {
            $html .= $field->config['html'] ?? (string) ($value ?? '');
        } else {
            $html .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars((string) ($value ?? ''), ENT_QUOTES) . '" class="form-control">';
        }

        $html .= $errorHtml;
        $html .= '</div>';

        return $html;
    }
}
