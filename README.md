![LT3 theme screenshot]( https://raw.github.com/beaucharman/lt3/master/screenshot.png "A slightly powerfull, intelligent and simple WordPress theme." )
## Change Log
1.0
- Changing the elegant theme over to a more modulated theme, now lt3

2.0
- Initial theme setup functionality, found in library/extensions/initial-theme-setup.php
- Conditional h1
- Split the theme-functions.php file up into modules
- Convert custom taxonomy and post type functions into classes
- Add get method to post type class
- Easy get terms, get term and get the terms methods for custom taxonomies

## Roadmap [Todo list]
- WordPress editor styles + instructions in Sass
- Work on default comment styles
- Replace most / relavent functions with hooks
- Better columns engine for posts types and taxonomies
- Add a custom user role extension
- Fix the taxonomy column in post types where they're are all empty terms

## Testing
Run http://codex.wordpress.org/Theme_Unit_Test thoroughly : )

## Notes for Production

- **Limit login attempts - plugin**

- **Strong Passwords**

- **Complex Database Prefix**

- **Place the .htaccess file currently in this directory, in the root directory**

- **.htaccess with Options -Indexes for wp-content folder**

```
  Order deny,allow
  Deny from all
  <Files ~ ".( xml|css|jpe?g|png|gif|js )$">
  Allow from all
  </Files>
```
- **Disallow Theme and Plug-in Editor Access [If used, remove reference from library/project/config]**

Within the wp-config.php file, place the following code:

```
  define( 'DISALLOW_FILE_EDIT', true );
```

- **Enforce SSL Usage**

Within the wp-config.php file, place the following code:

```
  /* Enable SSL Encryption */
  define( ‘FORCE_SSL_LOGIN’, true );
  define( ‘FORCE_SSL_ADMIN’, true );
```
