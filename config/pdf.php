<?php

return [
	'mode' => 'utf-8',
	'format' => 'A4',
	'author' => '',
	'subject' => '',
	'keywords' => '',
	'creator' => 'Laravel Pdf',
	'display_mode' => 'fullpage',
	'tempDir' => base_path('storage/temp'),
	'font_path' => base_path('storage/fonts/'),
	'font_data' => [
		'vazir' => [
			'R' => 'Vazir.ttf', // regular font
			'B' => 'Vazir-Bold.ttf', // optional: bold font
			'I' => 'Vazir.ttf', // optional: italic font
			'BI' => 'Vazir-Bold.ttf', // optional: bold-italic font
			'useOTL' => 0xFF, // required for complicated langs like Persian, Arabic and Chinese
			'useKashida' => 75, // required for complicated langs like Persian, Arabic and Chinese
		] // ...add as many as you want.
	],
	'pdf_a' => false,
	'pdf_a_auto' => false,
	'icc_profile_path' => ''
];
