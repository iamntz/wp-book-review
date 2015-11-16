Zilele astea am zis că ar fi cazul să-mi organizez într-un fel cărțile citite de care tot scriu pe blog. Cum GoodReads nu-mi se pare potrivit, am zis că ori pot folosi un plugin gata făcut ori pot face un tutorial despre cum fac pluginul. Prin urmare...

### Ce aș vrea să facă plugin-ul?

1. Afișarea unui Metabox ce va permite introducerea diverselor informații pentru o carte: tilu, isbn, imagine, editură, autor, stadiul curent (de citit, în curs de citire, citită) etc;
2. Widget ce permite afișarea cărților citite/de citit;
3. (probabil) să facă fetch automat de pe un API extern după ISBN;
4. Permite adăugarea de link-uri de unde poți cumpăra cartea.
5. Listarea unei arhive de cărți

### Ce ai putea învăța?

Ai putea învăța să lucrezi cu:

- custom post types;
- custom taxonomies;
- custom widgets;
- post metaboxes;
- actions & filters;
- media uploader;
- ajax în WordPress;
- localizarea plugin-urilor;
- shortcodes
- Git

-----------------

## Notă de început

Trebuie să menționez că o bună parte din cod este luat direct din [Codex-ul WordPress](http://codex.wordpress.org/Main_Page). Scopul acestui articol nu este acela de a-ți oferi cod complet original ci acela de a-ți arăta un mod de lucru: ce să folosești, unde să cauți, cum să organizezi codul, cum să folosești clase șamd.

## Primii Pași

Primul lucru pe care îl avem de făcut este să facem un fișier numit `index.php` în `wp-content/plugins/book-review` și să adăugăm minimum de informație pentru ca WordPress să vadă fișierul ca pe un plugin:

```php
<?php //index.php

/*
Plugin Name: Book Review
Author: Ionuț Staicu
Version: 1.0.0
*/
```

În continuare va trebui să facem o verificare pentru a preveni accesarea directă a fișierului plugin-ului:

```
//index.php
if (!defined('ABSPATH')) {
  exit;
}
```


### Localizare

Mai încărcăm și traducerile (un pas opțional dar util):

```
add_action('plugins_loaded', function () {
  load_plugin_textdomain('book-review', false, dirname(plugin_basename(__FILE__)) . '/lang');
});
```

Și am terminat ce era mai greu :)

Facem o paranteză la traduceri pentru a menționa un lucru important ce poate scăpa ușor din vedere: numele fișierelor `*.mo` & `*.po` trebuie să urmeze următoarea structură: `text-domain-lang_LANG`. În cazul nostru vor fi nevoie de fișierele `book-review-ro_RO.mo` respectiv `book-review-ro_RO.po`. Aceste două fișiere vor fi plasate în directorul `lang`.

Pentru că vrem ca plugin-ul să fie ușor de tradus de oricine, vom folosi doar texte în limba engleză. Pentru traducere poți folosi [Poeditor](https://poeditor.com) (web) sau [Poedit](https://poedit.net/) (stand alone; versiunea gratuită funcționează foarte bine cu pattern-urile menționate [aici](http://www.cssigniter.com/ignite/wordpress-poedit-translation-secrets/))

Poți citi mai multe despre localizări [aici](https://codex.wordpress.org/Function_Reference/load_plugin_textdomain)

### Încărcarea fișierelor

Pentru a include fișierele necesare, avem două posibilități: ori folosim un autoloader ori le includem manual. Pentru că vor fi doar câteva, nu are rost să ne complicăm, deci vom recurge la clasicul `require`.


#### Git

Inițializăm și facem primul commit

```
git init
git add .
git commit -am "Initial commit"
```


## Taxonomii și Post Types

Prima idee avută de mine a fost să țin totul în meta data, dar mi-am dat seama că unele informații se pot repeta: editură, autor sau genul cărții. În plus, aș fi limitat la altele: de exemplu n-aș putea afișa foarte ușor o arhivă cu toate cărțile citite sau nu aș putea afișa un widget.

Prin urmare, folosim un post type pentru cărți numit `books` și taxonomii pentru editură, autor și gen. Restul de informații (anul apariției, ISBN, imagine etc) sunt destul de specifice fiecărei publicații. Anul ar putea fi pus într-o taxonomie, dar consider că ar încărca prea mult UI-ul și ar face mai dificilă o filtrare a publicațiilor apărute între două date.

Pentru că vom folosi numele taxonomiilor în mai multe locuri, vom defini constante:

```php
//index.php

define('BOOK_POST_TYPE', 'book');

define('BOOK_TAX_GENRE', 'book_genre');
define('BOOK_TAX_AUTHOR', 'book_author');
define('BOOK_TAX_PUBLISHER', 'book_publisher');
```

Apoi le înregistrăm. Practic tot codul de mai jos este exemplul din [documentație](https://codex.wordpress.org/Function_Reference/register_post_type):

```php
//inc/bookReview/PostTypes.php

<?php

namespace bookReview;

class PostTypes
{
  public function __construct()
  {
    $this->registerPostType();
    $this->registerGenre();
    $this->registerAuthor();
    $this->registerPublisher();
  }

  protected function registerPostType()
  {
    $labels = array(
      'name' => _x('Books', 'post type general name'),
      'singular_name' => _x('Book', 'post type singular name'),
      'menu_name' => _x('Books', 'admin menu'),
      'name_admin_bar' => _x('Book', 'add new on admin bar'),
      'add_new' => _x('Add New', 'book'),
      'add_new_item' => __('Add New Book'),
      'new_item' => __('New Book'),
      'edit_item' => __('Edit Book'),
      'view_item' => __('View Book'),
      'all_items' => __('All Books'),
      'search_items' => __('Search Books'),
      'parent_item_colon' => __('Parent Books:'),
      'not_found' => __('No books found.'),
      'not_found_in_trash' => __('No books found in Trash.'),
    );

    $args = array(
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => array('slug' => 'book'),
      'capability_type' => 'post',
      'has_archive' => true,
      'hierarchical' => false,
      'supports' => array('title', 'editor', 'author', 'thumbnail'),
    );

    register_post_type(BOOK_POST_TYPE, $args);
  }

  protected function registerGenre()
  {
    $this->registerTaxonomy(array(
      'singular' => _x('Genre', 'taxonomy general name'),
      'plural' => _x('Genre', 'taxonomy general name'),
      'taxonomy' => BOOK_TAX_GENRE,
      'isHierarchical' => true,
    ));
  }

  protected function registerAuthor()
  {

    $this->registerTaxonomy(array(
      'singular' => _x('Writer', 'taxonomy general name'),
      'plural' => _x('Writers', 'taxonomy general name'),
      'taxonomy' => BOOK_TAX_AUTHOR,
      'isHierarchical' => false,
    ));
  }

  protected function registerPublisher()
  {
    $this->registerTaxonomy(array(
      'singular' => _x('Publisher', 'taxonomy general name'),
      'plural' => _x('Publishers', 'taxonomy general name'),
      'taxonomy' => BOOK_TAX_PUBLISHER,
      'isHierarchical' => false,
    ));
  }

  protected function registerTaxonomy($options)
  {
    $labels = array(
      'name' => $options['plural'],
      'singular_name' => $options['singular'],
      'search_items' => sprintf(__('Search %s'), $options['plural']),
      'all_items' => sprintf(__('All %s'), $options['plural']),
      'parent_item' => sprintf(__('Parent %s'), $options['singular']),
      'parent_item_colon' => sprintf(__('Parent %s:'), $options['singular']),
      'edit_item' => sprintf(__('Edit %s'), $options['singular']),
      'update_item' => sprintf(__('Update %s'), $options['singular']),
      'add_new_item' => sprintf(__('Add New %s'), $options['singular']),
      'new_item_name' => sprintf(__('New %s Name'), $options['singular']),
      'menu_name' => sprintf(__('%s'), $options['singular']),

      'popular_items' => sprintf(__('Popular %s'), $options['plural']),
      'separate_items_with_commas' => sprintf(__('Separate %s with commas'), $options['plural']),
      'add_or_remove_items' => sprintf(__('Add or remove %s'), $options['plural']),
      'choose_from_most_used' => sprintf(__('Choose from the most used %s'), $options['plural']),
      'not_found' => sprintf(__('No %s found.'), $options['plural']),
    );

    $args = array(
      'hierarchical' => $options['isHierarchical'],
      'labels' => $labels,
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => array('slug' => $options['slug']),
    );

    register_taxonomy($options['taxonomy'], BOOK_POST_TYPE, $args);
  }
}
```

Pentru că avem trei taxonomii ce au aceleași proprietăți, am făcut o metodă specială pentru a evita codul duplicat.

Genul cărții ne va permite să avem cărțile organizate într-o structură ierarhică (IT/Programare/Web/PHP). La autor și la editor nu este nevoie de așa ceva.

În `index.php` va trebui să includem clasa și să o instanțiem:

```php
//index.php

require_once 'inc/bookReview/PostTypes.php';

add_action('init', function () {
  new bookReview\PostTypes;
});

register_activation_hook(__FILE__, function () {
  new bookReview\PostTypes;
  flush_rewrite_rules();
});
```

A doua instanțiere a clasei `PostTypes` se face pentru a permite rescrierea permalinks. Detalii [aici](https://codex.wordpress.org/Function_Reference/register_activation_hook)

#### Git

```
git add .
git commit -am "Added post types & taxonomies"
```


## Metabox

```php
// inc/bookReview/Metabox.php

<?php

namespace bookReview;

class Metabox
{
  public function __construct()
  {
    add_action('add_meta_boxes', array($this, 'addMetaBox'));
    add_action('save_post', array($this, 'saveMeta'));
  }

  public function addMetaBox()
  {
    add_meta_box('book_properties', __('Book Properties'), array($this, 'displayMetaBox'), BOOK_POST_TYPE, 'advanced', 'high');
  }

  public function displayMetaBox($post)
  {
    wp_nonce_field('book-review-nonce', 'book-review-nonce');
    do_action('book-review/metabox/before-fields', $post);

    do_action('book-review/metabox/after-fields', $post);
  }

  public function saveMeta($post_id)
  {
    if (!isset($_POST['book-review-nonce']) || !wp_verify_nonce($_POST['book-review-nonce'], 'book-review-nonce')) {
      return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
      if (!current_user_can('edit_page', $post_id)) {
        return;
      }
    } else {
      if (!current_user_can('edit_post', $post_id)) {
        return;
      }
    }

    //  TODO: add fields

    do_action('book-review/metabox/save', $post_id);
  }
}
```

Facem o clasă ce adaugă un metabox pentru CPT-ul nostru, adaugă nonce-ul și pregătește terenul pentru adăugarea field-urilor necesare. În mare parte, și aici este codul luat tot [din Codex](https://codex.wordpress.org/Function_Reference/add_meta_box).

#### Git

```
git add .
git commit -am "Added metabox skeleton"
```
