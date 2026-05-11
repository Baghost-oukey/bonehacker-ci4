<?php
$uri = service('uri');
$segments = $uri->getSegments();
$title = $title ?? 'Dashboard';

if (!function_exists('breadcrumb_label')) {
	function breadcrumb_label(string $segment): string
	{
		$segment = trim(str_replace(['-', '_'], ' ', $segment));

		$map = [
			'beranda views' => 'Beranda',
			'beranda' => 'Beranda',
			'dashboard' => 'Dashboard',
			'patients' => 'Patients',
			'patient' => 'Patient',
			'users' => 'Users',
			'profile' => 'Profile',
			'setting' => 'Setting',
			'settings' => 'Settings',
			'region' => 'Cabang',
			'history' => 'Riwayat',
			'journal' => 'Jurnal',
			'transaksi' => 'Transaksi',
			'statistik' => 'Statistik',
			'antrean' => 'Antrean',
			'medis' => 'Rekam Medis',
			'result' => 'Pemeriksaan',
		];

		$key = strtolower($segment);

		if (isset($map[$key])) {
			return $map[$key];
		}

		return ucwords($segment);
	}
}

$crumbs = [];
$path = '';

foreach ($segments as $index => $segment) {
	if ($segment === '') {
		continue;
	}

	$path .= '/' . $segment;

	$crumbs[] = [
		'label' => breadcrumb_label($segment),
		'url' => site_url(ltrim($path, '/')),
		'isLast' => $index === array_key_last($segments),
	];
}

if (empty($crumbs)) {
	$crumbs[] = [
		'label' => $title,
		'url' => null,
		'isLast' => true,
	];
}
?>

<nav aria-label="Breadcrumb" class="text-[11px] md:text-sm">
	<ol class="flex items-center text-slate-500 overflow-hidden">

		<?php foreach ($crumbs as $index => $crumb): ?>
			<?php $isLast = ($index === array_key_last($crumbs)); ?>

			<li class="flex items-center <?= !$isLast ? 'hidden md:flex' : '' ?>">

				<?php if ($crumb['url'] && !$isLast): ?>
					<a href="<?= esc($crumb['url']) ?>" class="hover:text-slate-900 transition-colors whitespace-nowrap">
						<?= esc($crumb['label']) ?>
					</a>
				<?php else: ?>
					<span class="text-slate-900 font-bold md:font-medium truncate max-w-37.5 md:max-w-none">
						<?= esc($crumb['label']) ?>
					</span>
				<?php endif; ?>

				<?php if (!$isLast): ?>
					<span class="mx-1.5 md:mx-2 text-slate-400">/</span>
				<?php endif; ?>

			</li>
		<?php endforeach; ?>

	</ol>
</nav>