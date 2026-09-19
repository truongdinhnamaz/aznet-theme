import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const P4_BASE_URL = ( process.env.P4_BASE_URL || 'https://lstamduchn.vn' ).replace( /\/$/, '' );
const P4_STATE_DIR = process.env.P4_STATE_DIR || '/tmp/p4-public-pilot';
const viewports = [
    { name: 'desktop-1440', width: 1440, height: 1000 },
    { name: 'tablet-1024', width: 1024, height: 900 },
    { name: 'mobile-390', width: 390, height: 844 },
    { name: 'mobile-320', width: 320, height: 800 },
];

fs.mkdirSync( P4_STATE_DIR, { recursive: true } );
const screenshotsDir = path.join( P4_STATE_DIR, 'screenshots' );
fs.mkdirSync( screenshotsDir, { recursive: true } );

const fail = ( message ) => {
    throw new Error( message );
};

const sameOrigin = ( url ) => {
    try {
        return new URL( url ).origin === new URL( P4_BASE_URL ).origin;
    } catch {
        return false;
    }
};

const themeOwned = ( value ) => /(?:\/wp-content\/themes\/aznet-theme\/|aznet-theme)/i.test( String( value || '' ) );

async function fetchJson( relative ) {
    const url = new URL( relative, `${ P4_BASE_URL }/` );
    const response = await fetch( url, { headers: { accept: 'application/json' } } );
    if ( ! response.ok ) {
        fail( `Discovery request failed ${ response.status } ${ url.href }` );
    }
    return response.json();
}

function cleanSearchTerm( renderedTitle ) {
    const plain = String( renderedTitle || '' )
        .replace( /<[^>]*>/g, ' ' )
        .replace( /&[^;]+;/g, ' ' )
        .replace( /\s+/g, ' ' )
        .trim();
    const words = plain.match( /[\p{L}\p{N}]+/gu ) || [];
    return words.filter( ( word ) => word.length >= 4 ).slice( 0, 3 ).join( ' ' ) || 'luật';
}

async function discoverRoutes() {
    const [ posts, pages, categories ] = await Promise.all( [
        fetchJson( '/wp-json/wp/v2/posts?per_page=5&status=publish&_fields=link,title' ),
        fetchJson( '/wp-json/wp/v2/pages?per_page=20&status=publish&_fields=link,title' ),
        fetchJson( '/wp-json/wp/v2/categories?per_page=20&hide_empty=true&_fields=link,name' ),
    ] );

    const post = posts.find( ( item ) => item?.link && sameOrigin( item.link ) );
    const page = pages.find( ( item ) => {
        if ( ! item?.link || ! sameOrigin( item.link ) ) {
            return false;
        }
        return item.link.replace( /\/$/, '' ) !== P4_BASE_URL;
    } );
    const category = categories.find( ( item ) => item?.link && sameOrigin( item.link ) );

    if ( ! post?.link ) {
        fail( 'No public native Post route discovered through WordPress REST API' );
    }
    if ( ! page?.link ) {
        fail( 'No public native Page route discovered through WordPress REST API' );
    }
    if ( ! category?.link ) {
        fail( 'No public native Category route discovered through WordPress REST API' );
    }

    const searchTerm = cleanSearchTerm( post?.title?.rendered );
    const impossible = `aznet-p4-no-results-${ Date.now() }`;
    const missing = `aznet-p4-not-found-${ Date.now() }`;

    return [
        { name: 'home', url: `${ P4_BASE_URL }/`, expectedStatus: 200, bodyClass: 'home' },
        { name: 'post', url: post.link, expectedStatus: 200, bodyClass: 'single-post' },
        { name: 'page', url: page.link, expectedStatus: 200, bodyClass: 'page' },
        { name: 'category', url: category.link, expectedStatus: 200, bodyClass: 'category' },
        { name: 'search', url: `${ P4_BASE_URL }/?s=${ encodeURIComponent( searchTerm ) }`, expectedStatus: 200, bodyClass: 'search-results' },
        { name: 'no-results', url: `${ P4_BASE_URL }/?s=${ encodeURIComponent( impossible ) }`, expectedStatus: 200, bodyClass: 'search-no-results' },
        { name: '404', url: `${ P4_BASE_URL }/${ missing }/`, expectedStatus: 404, bodyClass: 'error404' },
    ];
}

function axeRecord( violation, ownership = 'THEME_OR_UNATTRIBUTED' ) {
    return {
        id: violation.id,
        impact: violation.impact,
        help: violation.help,
        ownership,
        targets: violation.nodes.flatMap( ( node ) => node.target ),
    };
}

async function isAuthoredContentLabelViolation( page, violation ) {
    const authoredLabelRule = violation.id === 'label';
    if ( ! authoredLabelRule || ! violation.nodes.length ) {
        return false;
    }

    for ( const node of violation.nodes ) {
        if ( ! Array.isArray( node.target ) || node.target.length !== 1 ) {
            return false;
        }

        const selector = node.target[0];
        let insideAuthoredContent = false;
        try {
            insideAuthoredContent = await page.locator( selector ).first().evaluate( ( element ) => Boolean( element.closest( '.aznet-theme-article__content' ) ) );
        } catch {
            return false;
        }

        if ( ! insideAuthoredContent ) {
            return false;
        }
    }

    return true;
}

const browser = await chromium.launch( { headless: true } );
const summary = {
    base_url: P4_BASE_URL,
    generated_at: new Date().toISOString(),
    route_source: 'WordPress public REST API plus native search/404 requests',
    authenticated_admin_qa: 'BLOCKED_NOT_IN_SCOPE',
    rootprofile_public_surface: 'NOT_OBSERVED_ON_TESTED_ROUTES',
    routes: [],
    blocking_failures: [],
    external_observations: [],
    content_accessibility_observations: [],
};

try {
    const routes = await discoverRoutes();

    for ( const viewport of viewports ) {
        for ( const route of routes ) {
            const context = await browser.newContext( {
                viewport: { width: viewport.width, height: viewport.height },
                locale: 'vi-VN',
            } );
            const page = await context.newPage();
            const themeConsoleErrors = [];
            const externalConsoleErrors = [];
            const themePageErrors = [];
            const externalPageErrors = [];
            const themeRequestFailures = [];

            page.on('console', ( message ) => {
                if ( message.type() !== 'error' ) {
                    return;
                }
                const location = message.location();
                const detail = `${ message.text() } ${ location?.url || '' }`.trim();
                if ( themeOwned( detail ) ) {
                    themeConsoleErrors.push( detail );
                } else {
                    externalConsoleErrors.push( detail );
                }
            } );

            page.on('pageerror', ( error ) => {
                const detail = error?.stack || error?.message || String( error );
                if ( themeOwned( detail ) ) {
                    themePageErrors.push( detail );
                } else {
                    externalPageErrors.push( detail );
                }
            } );

            page.on('requestfailed', ( request ) => {
                const detail = `${ request.url() } ${ request.failure()?.errorText || '' }`.trim();
                if ( themeOwned( detail ) ) {
                    themeRequestFailures.push( detail );
                }
            } );

            let response;
            let navigationError = null;
            try {
                response = await page.goto( route.url, { waitUntil: 'domcontentloaded', timeout: 45000 } );
                await page.waitForLoadState( 'networkidle', { timeout: 10000 } ).catch( () => {} );
            } catch ( error ) {
                navigationError = error?.message || String( error );
            }

            const result = {
                route: route.name,
                url: route.url,
                viewport: viewport.name,
                status: response?.status() ?? null,
                final_url: page.url(),
                navigation_error: navigationError,
                body_class_expected: route.bodyClass,
                body_class_present: false,
                body_text_length: 0,
                horizontal_overflow_px: null,
                h1_count: null,
                main_count: null,
                profile_surface_count: 0,
                axe_blocking: [],
                content_accessibility_observations: [],
                theme_console_errors: themeConsoleErrors,
                theme_page_errors: themePageErrors,
                theme_request_failures: themeRequestFailures,
                external_console_errors: externalConsoleErrors,
                external_page_errors: externalPageErrors,
            };

            if ( ! navigationError ) {
                const metrics = await page.evaluate( ( expectedClass ) => {
                    const root = document.documentElement;
                    const body = document.body;
                    return {
                        bodyClassPresent: body?.classList.contains( expectedClass ) || false,
                        bodyTextLength: ( body?.innerText || '' ).trim().length,
                        scrollWidth: root?.scrollWidth || 0,
                        clientWidth: root?.clientWidth || 0,
                        h1Count: document.querySelectorAll( 'h1' ).length,
                        mainCount: document.querySelectorAll( 'main' ).length,
                        profileSurfaceCount: document.querySelectorAll( '.aznet-theme-profile-surface' ).length,
                    };
                }, route.bodyClass );

                result.body_class_present = metrics.bodyClassPresent;
                result.body_text_length = metrics.bodyTextLength;
                result.horizontal_overflow_px = Math.max( 0, metrics.scrollWidth - metrics.clientWidth );
                result.h1_count = metrics.h1Count;
                result.main_count = metrics.mainCount;
                result.profile_surface_count = metrics.profileSurfaceCount;

                if ( metrics.profileSurfaceCount > 0 ) {
                    summary.rootprofile_public_surface = 'OBSERVED_ON_TESTED_ROUTE';
                }

                const axe = await new AxeBuilder( { page } ).analyze();
                const blockingAxe = axe.violations.filter( ( violation ) => [ 'critical', 'serious' ].includes( violation.impact ) );

                for ( const violation of blockingAxe ) {
                    if ( await isAuthoredContentLabelViolation( page, violation ) ) {
                        const observation = axeRecord( violation, 'CONTENT_AUTHORED_SEMANTICS' );
                        result.content_accessibility_observations.push( observation );
                        summary.content_accessibility_observations.push( {
                            route: route.name,
                            viewport: viewport.name,
                            ...observation,
                        } );
                        continue;
                    }
                    result.axe_blocking.push( axeRecord( violation ) );
                }
            }

            const blocking = [];
            if ( navigationError ) {
                blocking.push( `navigation: ${ navigationError }` );
            }
            if ( result.status !== route.expectedStatus ) {
                blocking.push( `status ${ result.status } expected ${ route.expectedStatus }` );
            }
            if ( ! result.body_class_present ) {
                blocking.push( `missing native body class ${ route.bodyClass }` );
            }
            if ( result.body_text_length < 40 ) {
                blocking.push( `blank/thin body text length ${ result.body_text_length }` );
            }
            if ( result.horizontal_overflow_px > 1 ) {
                blocking.push( `horizontal overflow ${ result.horizontal_overflow_px }px` );
            }
            if ( result.main_count !== 1 ) {
                blocking.push( `main landmark count ${ result.main_count } expected 1` );
            }
            if ( result.h1_count < 1 ) {
                blocking.push( `h1 count ${ result.h1_count } expected >= 1` );
            }
            if ( result.axe_blocking.length > 0 ) {
                blocking.push( `axe critical/serious ${ result.axe_blocking.length }` );
            }
            if ( themeConsoleErrors.length > 0 ) {
                blocking.push( `Theme console errors ${ themeConsoleErrors.length }` );
            }
            if ( themePageErrors.length > 0 ) {
                blocking.push( `Theme uncaught errors ${ themePageErrors.length }` );
            }
            if ( themeRequestFailures.length > 0 ) {
                blocking.push( `Theme request failures ${ themeRequestFailures.length }` );
            }

            if ( externalConsoleErrors.length > 0 || externalPageErrors.length > 0 ) {
                summary.external_observations.push( {
                    route: route.name,
                    viewport: viewport.name,
                    console_errors: externalConsoleErrors,
                    page_errors: externalPageErrors,
                } );
            }

            result.blocking = blocking;
            summary.routes.push( result );
            if ( blocking.length > 0 ) {
                summary.blocking_failures.push( {
                    route: route.name,
                    viewport: viewport.name,
                    failures: blocking,
                } );
            }

            const screenshotName = `${ viewport.name }-${ route.name }.png`;
            await page.screenshot( { path: path.join( screenshotsDir, screenshotName ), fullPage: true } ).catch( () => {} );
            await context.close();
        }
    }
} catch ( error ) {
    summary.blocking_failures.push( {
        route: 'discovery-or-harness',
        viewport: 'n/a',
        failures: [ error?.stack || error?.message || String( error ) ],
    } );
} finally {
    await browser.close();
}

fs.writeFileSync( path.join( P4_STATE_DIR, 'summary.json' ), `${ JSON.stringify( summary, null, 2 ) }\n` );

if ( summary.blocking_failures.length > 0 ) {
    console.error( JSON.stringify( summary.blocking_failures, null, 2 ) );
    process.exit( 1 );
}

console.log( `PASS: P4 public pilot matrix ${ summary.routes.length } route/viewport checks; RootProfile surface ${ summary.rootprofile_public_surface }; authored-content a11y observations ${ summary.content_accessibility_observations.length }` );
