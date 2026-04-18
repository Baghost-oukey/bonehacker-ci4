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

<nav aria-label="Breadcrumb" class="flex items-center text-sm">
	<ol class="flex items-center gap-1.5 text-slate-500">

		<?php foreach ($crumbs as $index => $crumb): ?>
			<?php $isLast = ($index === array_key_last($crumbs)); ?>

			<li class="inline-flex items-center gap-1.5">

				<?php if ($crumb['url'] && !$isLast): ?>
					<a 
						href="<?= esc($crumb['url']) ?>" 
						class="transition-colors hover:text-slate-900"
					>
						<?= esc($crumb['label']) ?>
					</a>
				<?php else: ?>
					<span class="font-medium text-slate-900">
						<?= esc($crumb['label']) ?>
					</span>
				<?php endif; ?>

				<?php if (!$isLast): ?>
					<i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
				<?php endif; ?>

			</li>
		<?php endforeach; ?>

	</ol>
</nav>
