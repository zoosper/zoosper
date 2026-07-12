# Phase 1.09.2 - Locale Placeholder Position Hotfix

The previous safe integration inserted `{$localeFieldHtml}` into the admin-user form, but the placeholder landed inside the Name input attribute area. The browser output showed invalid HTML where the locale field appeared between `name="name" value` and the rest of the Name input value/required attributes.

This hotfix removes the existing placeholder and reinserts it immediately before the Email label line. That keeps the locale field outside the Name input attribute and inside the form.
