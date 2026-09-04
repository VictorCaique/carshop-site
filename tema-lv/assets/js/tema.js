/**
 * Alternador de tema claro/escuro. Vanilla, sem jQuery.
 *
 * O tema inicial ja foi aplicado no <head> por um script inline (ver
 * customizacao.php): aqui so tratamos o clique. O botao nasce com [hidden]
 * porque sem JS ele nao teria o que fazer.
 */

const botao = document.querySelector( '[data-lv-tema]' );

if ( botao ) {
	const raiz  = document.documentElement;
	const texto = botao.querySelector( '.lv-tema-toggle__texto' );

	/**
	 * Quem decide qual tema esta valendo e o CSS, nao o JS: o token
	 * --lv-esquema ja resolveu o esquema fixo da loja, o prefers-color-scheme
	 * do aparelho e o [data-tema] do visitante, nessa ordem.
	 */
	const atual = () => (
		'escuro' === getComputedStyle( raiz ).getPropertyValue( '--lv-esquema' ).trim()
			? 'escuro'
			: 'claro'
	);

	const aplicar = ( tema, salvar ) => {
		raiz.dataset.tema = tema;
		botao.setAttribute( 'aria-pressed', String( 'escuro' === tema ) );

		if ( texto ) {
			texto.textContent = 'escuro' === tema ? 'Tema claro' : 'Tema escuro';
		}

		if ( salvar ) {
			try {
				localStorage.setItem( 'lv-tema', tema );
			} catch ( e ) {
				// Navegador com storage bloqueado: a troca vale so nesta pagina.
			}
		}
	};

	// Estado inicial sem salvar nada: quem nunca clicou continua seguindo a
	// loja ou o aparelho na proxima visita.
	aplicar( atual(), false );
	botao.hidden = false;

	botao.addEventListener( 'click', () => {
		aplicar( 'escuro' === atual() ? 'claro' : 'escuro', true );
	} );
}
