# ACF Custom Blocks

https://www.advancedcustomfields.com/resources/blocks/

1. Register Block in /blocks/register-blocks.php
	1. Be sure to set a good icon.

1. Create ACF field group, set location as Form > Block. Be sure to select "Show in GraphQL". You don't need to se the GraphQL types manually. The default works.
1. Verify that new component is available in the editor (note you may need to clear site cache)
1. Create backend PHP template and styles 
	1. in /blocks/ dir add new dir for block (eg "/block/slideshow") 
	1. add template to `/block/slideshow/block.php`
	1. add styles to `/block/slideshow/block.css`
1. Import Component to the WpContent.vue file for lazy loading
1. Build frontend Vue template in `/components/wp-block`
