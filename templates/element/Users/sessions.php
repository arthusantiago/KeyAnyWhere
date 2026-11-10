<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Session[]|\Cake\Collection\CollectionInterface $sessions
 */
?>
<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover">
        <thead>
            <tr class="text-center titulo-coluna-tabela">
                <th scope="col"><?=__('Dispositivo')?></th>
                <th scope="col"><?=__('Navegador')?></th>
                <th scope="col"><?=__('Criado em')?></th>
                <th scope="col"><?=__('Última atividade')?></th>
                <th scope="col"></th>
                <th scope="col"><?=__('Ações')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $session) : ?>
                <tr class="text-center">
                    <td><?= h($session->sistema_operacional); ?></td>
                    <td><?= h($session->navegador) ?></td>
                    <td><?= h($session->created) ?></td>
                    <td><?= h($session->modified) ?></td>
                    <td>
                        <?php if($session->esteDispositivo): ?>
                            <span class="badge bg-secondary"><?=__('Este dispositivo')?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                    <?php if (!$session->esteDispositivo) {
                        echo $this->element('Diversos/btnExcluir', ['idRegistro' => $session->id_secundario, 'texto' => 'Finalizar', 'tipo' => 'button']);
                    } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>