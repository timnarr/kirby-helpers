<?php

Kirby::plugin('timnarr/kirby-helpers', [
	'options' => [
		'vite' => [
			'manifestPath' => kirby()->root() . '/build/manifest.json',
		]
	],
	'translations' => [
		'en' => [
			'link_label_internal_home' => 'Link to homepage: { title }',
			'link_label_internal' => 'Link to page: { title }',
			'link_label_document' => 'Download file: { filename }',
			'link_label_external' => 'External link: { url } (Opens new tab)',
			'link_label_mail' => 'Send email to: { mail } (Opens new window of your email program)',
			'link_label_tel' => 'Call phone number: { tel } (Opens new window/program)',
		],
		'de' => [
			'link_label_internal_home' => 'Link zur Startseite: { title }',
			'link_label_internal' => 'Link zur Seite: { title }',
			'link_label_document' => 'Datei herunterladen: { filename }',
			'link_label_external' => 'Externer Link: { url } (Öffnet neuen Tab)',
			'link_label_mail' => 'E-Mail schreiben an: { mail } (Öffnet neues Fenster Ihres E-Mail Programms)',
			'link_label_tel' => 'Telefonnummer anrufen: { tel } (Öffnet neues Fenster/Programm)',
		]
	]
]);
