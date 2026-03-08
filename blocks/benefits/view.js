( function() {
	const blocks = document.querySelectorAll( '.wp-block-procoders-benefits-section' );

	if ( ! blocks.length ) {
		return;
	}

	function toInt( value, fallback ) {
		const parsed = parseInt( value, 10 );
		return Number.isNaN( parsed ) ? fallback : parsed;
	}

	function setTabsState( tabs, activeTermId ) {
		tabs.forEach( function( tab ) {
			const isActive = toInt( tab.getAttribute( 'data-term-id' ), 0 ) === activeTermId;
			tab.classList.toggle( 'is-active', isActive );
			tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		} );
	}

	function initBlock( block ) {
		const grid = block.querySelector( '[data-grid]' );
		const tabs = Array.prototype.slice.call( block.querySelectorAll( '.procoders-benefits__tab' ) );
		const loadMoreButton = block.querySelector( '.procoders-benefits__load-more' );
		let isLoading = false;
		let requestToken = 0;
		let activeTermId = toInt( block.getAttribute( 'data-term-id' ), 0 );
		let page = toInt( block.getAttribute( 'data-page' ), 1 );
		let maxPages = toInt( block.getAttribute( 'data-max-pages' ), 1 );

		if ( ! grid ) {
			return;
		}

		function updateLoadMoreVisibility() {
			if ( ! loadMoreButton ) {
				return;
			}

			loadMoreButton.hidden = page >= maxPages;
		}

		function setLoadingState( loading ) {
			isLoading = loading;
			block.classList.toggle( 'is-loading', loading );

			tabs.forEach( function( tab ) {
				tab.disabled = loading;
			} );

			if ( loadMoreButton ) {
				loadMoreButton.disabled = loading;
			}
		}

		function fetchCards( nextTermId, nextPage, append ) {
			const ajaxUrl = block.getAttribute( 'data-ajax-url' );
			const nonce = block.getAttribute( 'data-nonce' );
			const taxonomy = block.getAttribute( 'data-taxonomy' );
			const perPage = block.getAttribute( 'data-per-page' );
			const params = new URLSearchParams();
			let currentToken;

			if ( isLoading ) {
				return;
			}

			setLoadingState( true );
			requestToken += 1;
			currentToken = requestToken;

			params.append( 'action', 'procoders_load_benefits' );
			params.append( 'nonce', nonce || '' );
			params.append( 'taxonomy', taxonomy || 'benefits-category' );
			params.append( 'term_id', String( nextTermId ) );
			params.append( 'page', String( nextPage ) );
			params.append( 'per_page', String( perPage || 3 ) );

			fetch( ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: params.toString(),
			} )
				.then( function( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Bad response status' );
					}

					return response.json();
				} )
				.then( function( payload ) {
					if ( currentToken !== requestToken ) {
						return;
					}

					if ( ! payload || ! payload.success || ! payload.data ) {
						throw new Error( 'Unexpected payload' );
					}

					if ( append ) {
						grid.insertAdjacentHTML( 'beforeend', payload.data.html || '' );
					} else {
						grid.innerHTML = payload.data.html || '';
					}

					activeTermId = nextTermId;
					page = toInt( payload.data.currentPage, 1 );
					maxPages = toInt( payload.data.maxPages, 1 );

					block.setAttribute( 'data-term-id', String( activeTermId ) );
					block.setAttribute( 'data-page', String( page ) );
					block.setAttribute( 'data-max-pages', String( maxPages ) );

					setTabsState( tabs, activeTermId );
					updateLoadMoreVisibility();
				} )
				.catch( function() {
					// Silent fail for test task scope.
				} )
				.finally( function() {
					if ( currentToken === requestToken ) {
						setLoadingState( false );
					}
				} );
		}

		tabs.forEach( function( tab ) {
			tab.addEventListener( 'click', function() {
				const nextTermId = toInt( tab.getAttribute( 'data-term-id' ), 0 );

				if ( nextTermId === activeTermId || isLoading ) {
					return;
				}

				fetchCards( nextTermId, 1, false );
			} );
		} );

		if ( loadMoreButton ) {
			loadMoreButton.addEventListener( 'click', function() {
				if ( isLoading || page >= maxPages ) {
					return;
				}

				fetchCards( activeTermId, page + 1, true );
			} );
		}

		updateLoadMoreVisibility();
	}

	blocks.forEach( initBlock );
} )();
