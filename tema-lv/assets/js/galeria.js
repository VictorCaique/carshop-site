/**
 * Galeria da ficha do veiculo: setas, thumbnails, teclado e swipe.
 * Sem biblioteca externa.
 */

const galeria = document.getElementById( 'lv-galeria' );

if ( galeria ) {
	const slides   = [ ...galeria.querySelectorAll( '.lv-galeria__slide' ) ];
	const thumbs   = [ ...galeria.querySelectorAll( '.lv-galeria__thumb' ) ];
	const contador = galeria.querySelector( '[data-atual]' );
	const palco    = galeria.querySelector( '.lv-galeria__palco' );

	let atual = 0;

	const ir = ( indice ) => {
		if ( ! slides.length ) {
			return;
		}

		atual = ( indice + slides.length ) % slides.length;

		slides.forEach( ( s, i ) => s.classList.toggle( 'is-ativo', i === atual ) );
		thumbs.forEach( ( t, i ) => t.classList.toggle( 'is-ativo', i === atual ) );

		if ( contador ) {
			contador.textContent = String( atual + 1 );
		}

		// Mantem a thumb ativa visivel na faixa rolavel.
		thumbs[ atual ]?.scrollIntoView( { block: 'nearest', inline: 'nearest', behavior: 'smooth' } );
	};

	galeria.querySelector( '.lv-galeria__seta--esq' )?.addEventListener( 'click', () => ir( atual - 1 ) );
	galeria.querySelector( '.lv-galeria__seta--dir' )?.addEventListener( 'click', () => ir( atual + 1 ) );

	thumbs.forEach( ( thumb ) => {
		thumb.addEventListener( 'click', () => ir( parseInt( thumb.dataset.irPara, 10 ) ) );
	} );

	// Teclado: setas navegam quando o foco esta na galeria.
	galeria.setAttribute( 'tabindex', '0' );
	galeria.addEventListener( 'keydown', ( e ) => {
		if ( 'ArrowLeft' === e.key ) {
			e.preventDefault();
			ir( atual - 1 );
		}
		if ( 'ArrowRight' === e.key ) {
			e.preventDefault();
			ir( atual + 1 );
		}
	} );

	// Swipe no mobile.
	let x0 = null;

	palco?.addEventListener( 'touchstart', ( e ) => {
		x0 = e.changedTouches[ 0 ].clientX;
	}, { passive: true } );

	palco?.addEventListener( 'touchend', ( e ) => {
		if ( null === x0 ) {
			return;
		}
		const dx = e.changedTouches[ 0 ].clientX - x0;
		if ( Math.abs( dx ) > 45 ) {
			ir( dx < 0 ? atual + 1 : atual - 1 );
		}
		x0 = null;
	}, { passive: true } );

	// Pre-carrega a proxima foto para o swipe nao ficar em branco.
	const precarregar = () => {
		const proxima = slides[ ( atual + 1 ) % slides.length ]?.querySelector( 'img' );
		if ( proxima && 'lazy' === proxima.loading ) {
			proxima.loading = 'eager';
		}
	};

	galeria.addEventListener( 'click', precarregar, { once: true } );
	setTimeout( precarregar, 1200 );
}
