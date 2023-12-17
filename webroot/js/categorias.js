
/*
 * Função que pinta no menu lateral esquerdo em qual categoria o usuário está.
 *
 **/
window.addEventListener('load', function(event)
{
	let menuLateral = document.getElementById('menu-lateral');

	if (menuLateral !== null)
	{
		document.getElementById('menu-lateral');
		let nodeListLi = menuLateral.querySelectorAll('li');
		let idCategoria = document.getElementById('id-categoria-selecionada').value;

		nodeListLi.forEach(function(li, index, array)
		{
			if (idCategoria == li.getAttribute('data-categoriaId')) 
			{
				//pegando os values existente e acrescentando a classe que estiliza o elemento
				let value = li.getAttribute('class') + " categoria-selecionada";
				li.setAttribute('class', value);
			}
		});
	}
});