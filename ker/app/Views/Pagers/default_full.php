<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(1);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
	<ul class="pagination justify-content-end">
		<?php if ($pager->hasPreviousPage()) : ?>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
					<span aria-hidden="true">&lt;&lt;</span>
				</a>
			</li>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>">
					<span aria-hidden="true">&lt;</span>
				</a>
			</li>
		<?php endif ?>

		<?php foreach ($pager->links() as $link) : ?>
			<li class="page-item <?= $link['active'] ? 'active' : '' ?>" >
				<a class="page-link" href="<?= $link['uri'] ?>">
					<?= $link['title'] ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ($pager->hasNextPage()) : ?>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>">
					<span aria-hidden="true">&gt;</span>
				</a>
			</li>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
					<span aria-hidden="true">&gt;&gt;</span>
				</a>
			</li>
		<?php endif ?>
	</ul>
</nav>
