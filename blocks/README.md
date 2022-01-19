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
1. See Missing Pieces for infor


#### OLD BELOW

Uses this plugin:
https://developer.wpengine.com/genesis-custom-blocks/

## Build a custom block

Keep in mind, that you need to build a block twice. The PHP template that is used in the WordPress editor, and then the Vue template used on the frontend of the site.

Be sure to add the block to the whitelist in `/functions/gutenberg-functions.php`

### Backend (PHP) template 

How to make a block template:
https://developer.wpengine.com/genesis-custom-blocks/get-started/add-a-custom-block-to-your-website-content/

How to style the block:
https://developer.wpengine.com/genesis-custom-blocks/get-started/style-your-custom-blocks/

Note the directory structure. 

```
/fuxt
    blocks.css <- Generic styles
    /blocks
        /{block slug}
            block.php
            block.css <- Specific styles
```

### Frontend (Vue) template

Create a template in your frontend 

1.	Add the GQL required to `gql/fragments/GutenbergBlocks.gql`
	1.	If you plan to allow this block to be used in columns, be sure to include in the Column fragment
	
1.	Lazy load the component in `components/WpGutenberg.vue`
1. 	Credit a template


## Tips:
This is helpful for basic setup. Don't use the "Builder", write a PHP template as shown above.
https://developer.wpengine.com/genesis-custom-blocks/get-started/create-your-first-custom-block/

In your template, these two functions are important:
	
	- block_field() <- Outputs (print) the value of a custom field.
	- block_value() <- Does not output anything, useful for use in variables. 
	
This is a good read for comma gotcha's:
https://developer.wpengine.com/genesis-custom-blocks/faqs/	