<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Basic renderer for Admin forms.
 */
final readonly class AdminFormRenderer
{
    /** @param array<string, mixed> $values */
    public function render(AdminFormDefinition $form, array $values = [], string $action = '', string $method = 'POST'): string
    {
        $html = '<form action="' . htmlspecialchars($action, ENT_QUOTES) . '" method="' . htmlspecialchars($method, ENT_QUOTES) . '" class="admin-form">';
        
        foreach ($form->fields as $field) {
            $html .= $this->renderField($field, $values[$field->name] ?? null);
        }
        
        $html .= '<div class="form-actions"><button type="submit" class="btn btn-primary">Save</button></div>';
        $html .= '</form>';
        
        return $html;
    }

    private function renderField(AdminFormField $field, mixed $value): string
    {
        $label = htmlspecialchars($field->label, ENT_QUOTES);
        $name = htmlspecialchars($field->name, ENT_QUOTES);
        $type = htmlspecialchars($field->type, ENT_QUOTES);
        $val = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES);
        
        $html = '<div class="form-group">';
        $html .= '<label for="' . $name . '">' . $label . '</label>';
        
        if ($field->type === 'select') {
            $html .= '<select id="' . $name . '" name="' . $name . '" class="form-control">';
            foreach (($field->config['options'] ?? []) as $k => $v) {
                $selected = (string) $k === (string) $value ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars((string) $k, ENT_QUOTES) . '"' . $selected . '>' . htmlspecialchars((string) $v, ENT_QUOTES) . '</option>';
            }
            $html .= '</select>';
        } elseif ($field->type === 'textarea') {
            $html .= '<textarea id="' . $name . '" name="' . $name . '" class="form-control">' . $val . '</textarea>';
        } else {
            $html .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" value="' . $val . '" class="form-control">';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
