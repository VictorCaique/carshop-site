/**
 * Menu mobile. Vanilla, sem jQuery.
 */

const toggle = document.querySelector( '.lv-menu-toggle' );
const nav    = document.getElementById( 'lv-menu-principal' );

if ( toggle && nav ) {
	const fechar = () => {
		nav.classList.remove( 'is-aberto' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-label', 'Abrir menu' );
	};

	toggle.addEventListener( 'click', () => {
		const aberto = nav.classList.toggle( 'is-aberto' );
		toggle.setAttribute( 'aria-expanded', String( aberto ) );
		toggle.setAttribute( 'aria-label', aberto ? 'Fechar menu' : 'Abrir menu' );
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( 'Escape' === e.key ) {
			fechar();
		}
	} );

	// Fecha ao clicar em um link do menu.
	nav.addEventListener( 'click', ( e ) => {
		if ( e.target.closest( 'a' ) ) {
			fechar();
		}
	} );

	// Ao voltar para o desktop, o menu nao pode ficar preso no estado mobile.
	window.matchMedia( '(min-width: 861px)' ).addEventListener( 'change', ( e ) => {
		if ( e.matches ) {
			fechar();
		}
	} );
}

/**
 * Modal generico: [data-abre="id"] abre, [data-fecha] fecha.
 */
document.addEventListener( 'click', ( e ) => {
	const abrir = e.target.closest( '[data-abre]' );

	if ( abrir ) {
		const modal = document.getElementById( abrir.dataset.abre );
		if ( modal ) {
			modal.hidden = false;
			modal.querySelector( 'input, select, button' )?.focus();
		}
		return;
	}

	const modalAberto = e.target.closest( '.lv-modal' );
	if ( ! modalAberto ) {
		return;
	}

	// Clique no fundo ou no botao de fechar.
	if ( e.target.closest( '[data-fecha]' ) || e.target === modalAberto ) {
		modalAberto.hidden = true;
	}
} );

document.addEventListener( 'keydown', ( e ) => {
	if ( 'Escape' === e.key ) {
		document.querySelectorAll( '.lv-modal:not([hidden])' ).forEach( ( m ) => {
			m.hidden = true;
		} );
	}
} );

/**
 * Simulador de financiamento. Estimativa simples (Price), sem promessa de taxa.
 */
const TAXA_MENSAL = 0.0179;

document.querySelectorAll( '[data-simulador]' ).forEach( ( form ) => {
	const saida = form.querySelector( '[data-estimativa]' );

	const calcular = () => {
		const preco    = parseFloat( form.dataset.preco || '0' );
		const entrada  = parseFloat( form.querySelector( '[name="entrada"]' ).value || '0' );
		const parcelas = parseInt( form.querySelector( '[name="parcelas"]' ).value, 10 );
		const saldo    = Math.max( preco - entrada, 0 );

		if ( ! saldo || ! parcelas ) {
			return null;
		}

		const fator   = Math.pow( 1 + TAXA_MENSAL, parcelas );
		const parcela = saldo * ( TAXA_MENSAL * fator ) / ( fator - 1 );

		return { saldo, parcelas, parcela };
	};

	const formatar = ( v ) => v.toLocaleString( 'pt-BR', { style: 'currency', currency: 'BRL' } );

	const atualizar = () => {
		const r = calcular();
		if ( ! r || ! saida ) {
			return;
		}
		saida.hidden = false;
		saida.textContent = `Estimativa: ${ r.parcelas }x de ${ formatar( r.parcela ) } (financiando ${ formatar( r.saldo ) }).`;
	};

	form.addEventListener( 'input', atualizar );
	atualizar();

	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();

		const r    = calcular();
		const nome = form.querySelector( '[name="nome"]' ).value.trim();
		const wpp  = form.dataset.whatsapp;

		if ( ! wpp ) {
			return;
		}

		const linhas = [
			`Ola! Sou ${ nome || 'um cliente' } e quero simular o financiamento do ${ form.dataset.veiculo }.`,
			r ? `Entrada: ${ formatar( parseFloat( form.querySelector( '[name="entrada"]' ).value || '0' ) ) }` : '',
			r ? `Parcelas: ${ r.parcelas }x (estimativa de ${ formatar( r.parcela ) })` : '',
			form.dataset.url,
		].filter( Boolean );

		window.open(
			`https://wa.me/${ wpp }?text=${ encodeURIComponent( linhas.join( '\n' ) ) }`,
			'_blank',
			'noopener'
		);
	} );
} );
