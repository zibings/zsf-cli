<?php for ($i = 0; $i < count($models); $i++): ?><?php $model = $models[$i]; ?>
export interface I<?= $this->e($model['className']) ?> {
<?php foreach ($model['properties'] as $property): ?>	<?= $this->e($property['name']) ?>: <?= $this->e($property['type']) ?>;
<?php endforeach; ?>
}

export class <?= $this->e($model['className']) ?> {
<?php foreach ($model['properties'] as $property): ?>	public <?= $this->e($property['name']) ?>: <?= $this->e($property['type']) ?>;
<?php endforeach; ?>

	constructor(model?: I<?= $this->e($model['className']) ?>) {
<?php foreach ($model['properties'] as $property): ?>		this.<?= $this->e($property['name']) ?> = model.<?= $this->e($property['name']) ?> ?? <?= $property['defaultValue'] ?>;
<?php endforeach; ?>

		return;
	}
}
<?php if ($i !== (count($models) - 1)): ?>
<?php echo PHP_EOL; ?>
<?php endif; ?>
<?php endfor; ?>
