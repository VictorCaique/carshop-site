/**
 * Configuracoes do Site: abas, media picker, color picker e repeaters.
 * jQuery entra so pelo wpColorPicker, que e do proprio WordPress.
 */
( function () {
	'use strict';

	const raiz = document.querySelector( '.lv-opcoes' );
	if ( ! raiz ) {
		return;
	}

	/* ------------------------------------------------------------------
	 * Abas sem recarregar a pagina (o link continua funcionando sem JS)
	 * ------------------------------------------------------------------ */
	raiz.querySelectorAll( '[data-lv-aba]' ).forEach( ( link ) => {
		link.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const alvo = link.dataset.lvAba;

			raiz.querySelectorAll( '[data-lv-aba]' ).forEach( ( l ) => {
				l.classList.toggle( 'nav-tab-active', l === link );
			} );
			raiz.querySelectorAll( '[data-lv-painel]' ).forEach( ( p ) => {
				p.hidden = p.dataset.lvPainel !== alvo;
			} );

			// O TinyMCE inicializado dentro de um painel escondido as vezes
			// abre com altura zero. Repintar ao mostrar a aba resolve.
			if ( window.tinymce ) {
				window.tinymce.editors.forEach( ( ed ) => {
					if ( ed.getContainer()?.closest( '[data-lv-painel="' + alvo + '"]' ) ) {
						ed.execCommand( 'mceRepaint' );
					}
				} );
			}

			history.replaceState( null, '', link.href );
		} );
	} );

	/* ------------------------------------------------------------------
	 * Color picker
	 * ------------------------------------------------------------------ */
	const iniciarCores = ( escopo ) => {
		if ( ! window.jQuery || ! window.jQuery.fn.wpColorPicker ) {
			return;
		}
		window.jQuery( escopo ).find( '.lv-color' ).each( function () {
			if ( ! this.dataset.lvIniciado ) {
				this.dataset.lvIniciado = '1';
				window.jQuery( this ).wpColorPicker();
			}
		} );
	};
	iniciarCores( raiz );

	/* ------------------------------------------------------------------
	 * Media picker
	 * ------------------------------------------------------------------ */
	const textos = window.lvOpcoes || {};

	const ligarImagem = ( campo ) => {
		if ( campo.dataset.lvIniciado ) {
			return;
		}
		campo.dataset.lvIniciado = '1';

		const input   = campo.querySelector( '[data-lv-imagem-id]' );
		const preview = campo.querySelector( '.lv-campo-imagem__preview' );
		const remover = campo.querySelector( '[data-lv-imagem-remover]' );
		let frame     = null;

		campo.querySelector( '[data-lv-imagem-escolher]' ).addEventListener( 'click', () => {
			if ( frame ) {
				frame.open();
				return;
			}

			frame = window.wp.media( {
				title: textos.selecionar || 'Selecionar imagem',
				button: { text: textos.usar || 'Usar esta imagem' },
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', () => {
				const img = frame.state().get( 'selection' ).first().toJSON();
				const src = ( img.sizes && img.sizes.medium ) ? img.sizes.medium.url : img.url;

				input.value = img.id;
				preview.innerHTML = '';

				const el = document.createElement( 'img' );
				el.src = src;
				el.alt = '';
				preview.appendChild( el );

				remover.hidden = false;
			} );

			frame.open();
		} );

		remover.addEventListener( 'click', () => {
			input.value = '';
			preview.innerHTML = '';
			remover.hidden = true;
		} );
	};

	raiz.querySelectorAll( '[data-lv-imagem]' ).forEach( ligarImagem );

	/* ------------------------------------------------------------------
	 * Repeaters
	 * ------------------------------------------------------------------ */
	raiz.querySelectorAll( '[data-lv-repeater]' ).forEach( ( repeater ) => {
		const linhas   = repeater.querySelector( '[data-lv-linhas]' );
		const template = repeater.querySelector( '[data-lv-template]' );
		const adicionar= repeater.querySelector( '[data-lv-adicionar]' );
		const max      = parseInt( repeater.dataset.lvMax || '0', 10 );

		const atualizarBotao = () => {
			if ( max ) {
				adicionar.disabled = linhas.children.length >= max;
			}
		};

		// Reindexa os names depois de adicionar, remover ou reordenar.
		// Sem isso o PHP recebe indices repetidos e perde linhas.
		const reindexar = () => {
			[ ...linhas.children ].forEach( ( linha, i ) => {
				linha.querySelectorAll( '[name]' ).forEach( ( campo ) => {
					campo.name = campo.name.replace( /\[(\d+|__i__)\]/, '[' + i + ']' );
				} );
			} );
			atualizarBotao();
		};

		adicionar.addEventListener( 'click', () => {
			if ( max && linhas.children.length >= max ) {
				return;
			}

			const nova = template.content.cloneNode( true );
			linhas.appendChild( nova );

			const linha = linhas.lastElementChild;
			linha.querySelectorAll( '[data-lv-imagem]' ).forEach( ligarImagem );
			iniciarCores( linha );

			reindexar();
			linha.querySelector( 'input, select, textarea' )?.focus();
		} );

		linhas.addEventListener( 'click', ( e ) => {
			if ( ! e.target.closest( '[data-lv-remover-linha]' ) ) {
				return;
			}
			e.target.closest( '[data-lv-linha]' ).remove();
			reindexar();
		} );

		/* -------- Reordenar arrastando -------- */
		let arrastando = null;

		linhas.addEventListener( 'pointerdown', ( e ) => {
			const handle = e.target.closest( '.lv-repeater__handle' );
			if ( ! handle ) {
				return;
			}
			arrastando = handle.closest( '[data-lv-linha]' );
			arrastando.classList.add( 'is-arrastando' );
			handle.setPointerCapture( e.pointerId );
		} );

		linhas.addEventListener( 'pointermove', ( e ) => {
			if ( ! arrastando ) {
				return;
			}
			const alvo = document.elementFromPoint( e.clientX, e.clientY )?.closest( '[data-lv-linha]' );
			if ( ! alvo || alvo === arrastando || alvo.parentElement !== linhas ) {
				return;
			}
			const depois = alvo.compareDocumentPosition( arrastando ) & Node.DOCUMENT_POSITION_PRECEDING;
			linhas.insertBefore( arrastando, depois ? alvo.nextSibling : alvo );
		} );

		const soltar = () => {
			if ( ! arrastando ) {
				return;
			}
			arrastando.classList.remove( 'is-arrastando' );
			arrastando = null;
			reindexar();
		};

		linhas.addEventListener( 'pointerup', soltar );
		linhas.addEventListener( 'pointercancel', soltar );

		atualizarBotao();
	} );
}() );
