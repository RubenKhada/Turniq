<?php
/*
 * ui/layout/head.php — Encabezado HTML
 * Importa estilos base y el tema dinámico
 * Define $extra_css antes de incluir para cargar CSS adicional
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= BASE_URL ?>/ui/css/main.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/theme.php">
<?php if (!empty($extra_css)): ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/ui/css/<?= $extra_css ?>">
<?php endif; ?>