<?php
/**
 *	Element que gera o breadcrumb. Esse elemento não trata a estrutura onde será exibido (.row ou .col-).
 *
 * 	Chamada simples:
 *	    <?= $this->element(
 *			'breadcrumb',
 *			[
 *				'caminho' => [
 *					'Configurações',
 *					'Usuários',
 *					'Listagem'
 *				]
 *			]
 *    	);?>
 *		Resultado: Configurações / Usuários / Listagem
 *
 *	Chamada com link.
 *	Quando um elemento do array é outro array, subentende-se que se trata de uma parte que será exibida como um link.
 *	Esse elemento do array deve seguir o padrão: [ 0 => 'controller', 1 => 'action', 2 => 'Texto do link']
 * 	Exemplo:
 *    <?= $this->element(
 *        'breadcrumb',
 *        [
 *            'caminho' => [
 *                ['Pages', 'index', 'Home'],
 *               'Configurações',
 *                ['Users', 'index', 'Usuários'],
 *                'Novo'
 *            ]
 *        ]
 *    );?>
 *
 *	Resultado: Home / Configurações / Usuários / Listagem   ('Home' e 'Usuários' são links)
 *
 *	@param array $caminho contendo em cada posição do array uma parte do caminho exibido no breadcrumb
 * @var \App\View\AppView $this
 * @var mixed $caminho
 */
?>

<nav aria-label="breadcrumb" class="bg-light">
	<ol class="breadcrumb">
		<?php foreach ($caminho as $parte) : ?>
			<?php if (is_array($parte)) : ?>
				<li class="breadcrumb-item">
					<a href="<?= $this->Url->build(["controller" => $parte[0], "action" => $parte[1]]) ?>"><?= $parte[2] ?></a>
				</li>
			<?php else : ?>
				<li class="breadcrumb-item"><?= $parte ?></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ol>
</nav>
