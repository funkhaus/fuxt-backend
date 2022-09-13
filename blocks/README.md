# ACF Custom Blocks

https://www.advancedcustomfields.com/resources/blocks/

1. Register Block in /blocks/register-blocks.php
	1. Be sure to set a good icon.

1. Create ACF field group, set location as Form > Block. Be sure to select "Show in GraphQL". You don't need to se the GraphQL types manually. The default works.
1. Refresh Gutenberg Block cache in menu graphql > gutenberg 
1. Verify that new component is available in gutenberg editor or graphiql (note you may need to clear site cache)
1. Create backend PHP template and styles 
	1. in /blocks/ dir add new dir for block (eg "/block/slideshow") 
	1. add template to `/block/slideshow/block.php`
	1. add styles to `/block/slideshow/block.css`
1. Add GQL to /gql/fragments/GutenbergBlocks.gql query. Be sure to alias your fields with `fields` like so:
```
# Custom Blocks
fragment SlideshowBlock on Block {
    ... on AcfSlideshowBlock {
        # AcfCreditBlock is always like `Acf${BlockName}Block`
        attributes {
            wpClasses: className
        }
        fields: blockSlideshow {
            # blockSlideshow is the ACF field group name
            slides {
                ...MediaImage
            }
        }
    }
}
```
1. Import Component to the WpGutenberg.vue file for lazy loading
1. Build frontend Vue template in `/components/gutenberg`
1. See Missing Pieces for demos
