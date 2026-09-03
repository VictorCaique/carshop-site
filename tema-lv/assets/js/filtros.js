/**
 * Vitrine: drawer de filtros no mobile e submit automatico da ordenacao.
 * Os filtros continuam funcionando sem JS - isto e so conforto.
 */

const aside  = document.getElementById( 'lv-vitrine-aside' );
const abrir  = document.querySelector( '.lv-vitrine__abrir-filtros' );
const fechar = document.querySelector( '.lv-filtros__fechar' );

const setDrawer = ( aberto ) => {
	if ( ! aside || ! abrir ) {
		return;
	}
	aside.classList.toggle( 'is-aberto', aberto );
	abrir.setAttribute( 'aria-expanded', String( aberto ) );
	document.body.style.overflow = aberto ? 'hidden' : '';
};

abrir?.addEventListener( 'click', () => setDrawer( true ) );
fechar?.addEventListener( 'click', () => setDrawer( false ) );

// Clique no fundo escuro fecha.
aside?.addEventListener( 'click', ( e ) => {
	if ( e.target === aside ) {
		setDrawer( false );
	}
} );

document.addEventListener( 'keydown', ( e ) => {
	if ( 'Escape' === e.key ) {
		setDrawer( false );
	}
} );

window.matchMedia( '(min-width: 901px)' ).addEventListener( 'change', ( e ) => {
	if ( e.matches ) {
		setDrawer( false );
	}
} );

/**
 * Trocar a ordenacao ja envia o formulario.
 */
document.querySelectorAll( 'select[data-auto-submit]' ).forEach( ( select ) => {
	select.addEventListener( 'change', () => select.form?.submit() );
} );

/**
 * Campos vazios nao viram parametro na URL - deixa o link limpo e legivel.
 */
document.querySelectorAll( 'form.lv-filtros, form.lv-busca-rapida' ).forEach( ( form ) => {
	form.addEventListener( 'submit', () => {
		form.querySelectorAll( 'input, select' ).forEach( ( campo ) => {
			if ( '' === campo.value ) {
				campo.disabled = true;
			}
		} );
	} );
} );
