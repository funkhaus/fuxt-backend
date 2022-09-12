# ACF Custom Blocks

https://www.advancedcustomfields.com/resources/blocks/

1. Register Block (/functions/acf-custom-blocks.php)
	1. Be sure to set a good icon.
1. Create ACF field group, set location as Form > Block. Be sure to select "Show in GraphQL". You don't need to se the GraphQL types manually. The default works.
1. Refresh Block cache
1. Create backend PHP template and styles
1. Add GQL to frontend. Be sure to alias your fields with `fields` like so:
1. Import Component to the WpGutenberg.vue file
1. Build frontend Vue template 
1. See Missing Pieces for demos
