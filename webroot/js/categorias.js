
/*
 * Função que pinta no menu lateral esquerdo em qual categoria o usuário está.
 *
 **/
function categoriaSelecionada()
{
	let menuLateral = document.getElementById('menuLateral');

	if (menuLateral !== null)
	{
		let nodeListLi = menuLateral.querySelectorAll('li');
		
		//exemplo /categorias/listagem-entradas/2
		let pathName =  window.location.pathname;
		pathName = pathName.split('/');


		nodeListLi.forEach(function(li, index, array)
		{			
			if (pathName[pathName.length - 1] == li.getAttribute('data-categoriaId')) 
			{
				//pegando os values existente e acrescentando a classe que estiliza o elemento
				let value = li.getAttribute('class') + " categoriaSelecionada";
				li.setAttribute('class', value);
			}
		});
	}
}