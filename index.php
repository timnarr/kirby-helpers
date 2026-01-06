<?php

use Kirby\Content\Field;

Kirby::plugin('timnarr/kirby-helpers', [
	'options' => [
		'vite' => [
			'manifestPath' => kirby()->root() . '/build/manifest.json',
		],
	],
	'fieldMethods' => [
		'ensureLeft' => function (Field $field, string $prefix): Field {
			$field->value = ensureLeft($field->value, $prefix);
			return $field;
		},
		'ensureRight' => function (Field $field, string $suffix): Field {
			$field->value = ensureRight($field->value, $suffix);
			return $field;
		},
		'ensureHashed' => function (Field $field): Field {
			$field->value = ensureHashed($field->value);
			return $field;
		},
		'autoLinkTitles' => function (Field $field): Field {
			$field->value = autoLinkTitles($field->value);
			return $field;
		},
	],
	'fileMethods' => [
		'readAccessible' => function (string $title = '', string $description = '', bool $isDecorative = false) {
			return readAccessible($this, $title, $description, $isDecorative);
		},
	],
	'pageMethods' => [
		'hasTranslations' => function (): bool {
			return !empty(getAvailableTranslations($this));
		},
		'getTranslations' => function (): array {
			return getAvailableTranslations($this);
		},
		'getMissingTranslations' => function (): array {
			return getMissingTranslations($this);
		},
		'missingTranslationsBadge' => function (): string {
			$codes = $this->getMissingTranslations();

			if (empty($codes)) {
				return '<span class="k-info-badge" data-theme="green">' . t('translation_status_all') . '</span>';
			}

			return '<span class="k-info-badge" data-theme="red">' . tt('translation_status_missing', ['codes' => strtoupper(implode(', ', $codes))]) . '</span>';
		},
	],
	'translations' => [
		'en' => [
			'link_label_internal_home' => 'Link to homepage: { title }',
			'link_label_internal' => 'Link to page: { title }',
			'link_label_document' => 'Download file: { filename }',
			'link_label_external' => 'External link: { url } (Opens new tab)',
			'link_label_mail' => 'Send email to: { mail } (Opens new window of your email program)',
			'link_label_tel' => 'Call phone number: { tel } (Opens new window/program)',
			'translation_status_missing' => 'Missing: { codes }',
			'translation_status_all' => 'Translated',
		],
		'de' => [
			'link_label_internal_home' => 'Link zur Startseite: { title }',
			'link_label_internal' => 'Link zur Seite: { title }',
			'link_label_document' => 'Datei herunterladen: { filename }',
			'link_label_external' => 'Externer Link: { url } (Öffnet neuen Tab)',
			'link_label_mail' => 'E-Mail schreiben an: { mail } (Öffnet neues Fenster Ihres E-Mail Programms)',
			'link_label_tel' => 'Telefonnummer anrufen: { tel } (Öffnet neues Fenster/Programm)',
			'translation_status_missing' => 'Fehlt: { codes }',
			'translation_status_all' => 'Übersetzt',
		],
	],
]);
