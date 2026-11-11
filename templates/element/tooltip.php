<?php
/**
 * @param string $texto Texto que será exibido no tooltip
 * @var \App\View\AppView $this
 */
?>
<i class="bi bi-info-circle-fill" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-custom-class="custom-popover" data-bs-content="<?=__($texto)?>"></i>