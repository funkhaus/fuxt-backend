/**
 * WordPress dependencies
 */
const { getCategories, setCategories, registerBlockType } = wp.blocks;

// Icon
import icons from './utils/icons';

// Editor and Frontend Styles
import './styles/editor.scss';

//  Register Blocks
import * as gallery from './blocks/gallery';

// Category settings
const category = {
	slug: 'funkhaus',
	title: 'Funkhaus Block',
	icon: icons.logo,
};

// Custom foreground icon color based on the Block Funkhaus branding
const iconColor = '#1e35b9';

/**
 * Define block category
 */
setCategories( [
	// Add a Block Funkhaus block category
	category,
	...getCategories().filter( ( { slug } ) => slug !== 'funkhaus' ),
] );

/**
 * Define blocks
 */

export function registerBlocks() {
	[ gallery ].forEach( ( block ) => {
		if ( ! block ) {
			return;
		}

		const { name, icon, settings } = block;

		registerBlockType( `funkhaus/${ name }`, {
			category: category.slug,
			icon: { src: icon, foreground: iconColor },
			...settings,
		} );
	} );
}
registerBlocks();
