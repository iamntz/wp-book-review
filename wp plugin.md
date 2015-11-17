Introducerea a fost [aici](https://devforum.ro/t/un-tutorial-de-plugin-pentru-wp/2230).

Repo se află [aici](https://github.com/iamntz/wp-book-review).

## Notă de început

Trebuie să menționez că o bună parte din cod este luat direct din [Codex-ul WordPress](http://codex.wordpress.org/Main_Page). Scopul acestui articol nu este acela de a-ți oferi cod complet original ci acela de a-ți arăta un mod de lucru: ce să folosești, unde să cauți, cum să organizezi codul, cum să folosești clase șamd.

Nu uita, suntem pe forum ca să învățăm cu toții!

- Dacă ai nelămuriri, întreabă, oricât de ridicolă ți s-ar părea întrebarea. Nu o să râdă nimeni de tine, nu o să te ia nimeni la mișto, nimeni nu s-a născut învățat.
- Dacă observi vreo greșeală în codul meu sau dacă ai vreo idee mai bună de a face un anumit lucru, nu ezita să lași un comentariu!
- Dacă ai de gând să faci miștouri, să glumești, să lași un comentariu offtopic, abtine-te sau comentează [aici](https://devforum.ro/t/tutorial-plugin-wordpress-book-review-comentarii-offtopic/2238). :smile:

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

    echo $this->addFields($post);

    do_action('book-review/metabox/after-fields', $post);
  }

  protected function addFields($post)
  {
    return implode("\n", $fields);
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

    $this->saveFields($post_id);

    do_action('book-review/metabox/save', $post_id);
  }

  protected function saveFields($postID)
  {

  }
}
```

Facem o clasă ce adaugă un metabox pentru CPT-ul nostru, adaugă nonce-ul și pregătește terenul pentru adăugarea field-urilor necesare. În mare parte, și aici este codul luat tot [din Codex](https://codex.wordpress.org/Function_Reference/add_meta_box).

După cum observi, am adăugat și câteva `do_action` pentru a permite adăugarea de conținut extra din alte plugin-uri sau din `functions.php`.

#### Git

```
git add .
git commit -am "Added metabox skeleton"
```


### Câmpurile din Metabox

Pentru că o să tot adăugăm câmpuri, vom adăuga întâi o metodă ce ne va ajuta să generăm `input`-uri și `textarea` foarte ușor:

```php
// inc/bookReview/Metabox.php

protected function getTextField($postID, $name, $label, $textarea = false)
{
  $value = get_post_meta($postID, $name, true);

  if ($textarea) {
    $field = sprintf('<textarea name="%2$s" id="%2$s" class="widefat">%1$s</textarea>', esc_textarea($value), $name );
  } else {
    $field = sprintf('<input type="text" name="%2$s" id="%2$s" value="%1$s" class="widefat">', esc_attr($value), $name );
  }

  return sprintf('<p><label for="%s">%s: %s</label></p>', $name, $label, $field);
}
```

După care vom adăuga field-urile în metoda `addFields`:


```php
// inc/bookReview/Metabox.php @ addFields
$fields[] = $this->getTextField($post->ID, '_isbn', __('ISBN'));
$fields[] = $this->getTextField($post->ID, '_publish_year', __('Publish Year'));
$fields[] = $this->getTextField($post->ID, '_buy_book', __('Buying Links'), true););

```

Evident, nu ar trebui să uităm să adăugăm numele în metoda `saveFields`!:

```php
// inc/bookReview/Metabox.php @ saveFields
update_post_meta($postID, '_isbn', sanitize_text_field($_POST['_isbn']));
update_post_meta($postID, '_publish_year', sanitize_text_field($_POST['_publish_year']));

update_post_meta($postID, '_buy_book', wp_kses($_POST['_buy_book']));
```

#### Ce nume folosești pentru metafields?

Probabil ai observat un underscore în fața fiecărui nume al field-urilor. Este așa deoarece nu vrem ca aceste meta data să fie vizibile sau editabile în afara plugin-ului (detalii [aici](https://codex.wordpress.org/Function_Reference/add_post_meta#Hidden_Custom_Fields)).

#### Git

```
git commit -am "Added basic meta fields"
```

### Rating & Progres

Pentru că rating-ul și progresul sunt niște chestii foarte fixe, vom adăuga o metodă asemănătoare cu `getTextField` dar care va genera un tag `select`:

```php
// inc/bookReview/Metabox.php
protected function getSelectField($postID, $name, $label, Array $values)
{
  $storedValue = get_post_meta($postID, $name, true);

  $options = array();
  foreach ($values as $value => $text) {
    $options[] = sprintf('<option value="%1$s"%2$s>%3$s</option>', $value, selected($storedValue, $value, false), $text);
  }

  $field = sprintf('<select name="%1$s" id="%1$s" class="widefat">%2$s</select>', $name, implode("\n", $options));

  return sprintf('<p><label for="%s">%s: %s</label></p>', $name, $label, $field);
}
```

După care, în metoda `addFields` adăugăm:

```php
// inc/bookReview/Metabox.php @ addFields
$fields[] = $this->getProgress($post->ID);
$fields[] = $this->getRating($post->ID);
```

Metoda `getProgress` va chema `getSelectField`:

```php
// inc/bookReview/Metabox.php
protected function getProgress($postID)
{
  $values = apply_filters('book-review/metabox/progress-options', array(
    "list" => __('On My List'),
    "reading" => __('Currently Reading'),
    "read" => __('Read'),
  ));

  return $this->getSelectField($postID, '_book_progress', __('Book Progress'), $values);
}
```

Trecerea valorilor implicite prin `apply_filters` va permite adăugarea de opțiuni ori dintr-un alt plugin ori din `functions.php`.

Similar, avem și `getRating`:

```php
// inc/bookReview/Metabox.php
protected function getRating($postID)
{
  $values = apply_filters('book-review/metabox/progress-options', array(
    -1 => __('- Pick One -'),
    1 => __('Bad'),
    2 => __('Meh'),
    3 => __('Mediocre'),
    4 => __('Pretty good'),
    5 => __('Awesome!'),
  ));

  return $this->getSelectField($postID, '_book_rating', __('Book Rating'), $values);
}
```

Nu ar trebui să uităm să salvăm toate aceste câmpuri (în metoda `saveFields`)::

```php
// inc/bookReview/Metabox.php @ saveFields

update_post_meta($postID, '_book_progress', sanitize_text_field($_POST['_book_progress']));
update_post_meta($postID, '_book_rating', sanitize_text_field($_POST['_book_rating']));
```

#### Git

```
git commit -am "Added rating & book status meta fields"
```


## Javascript

În mod normal, aș folosi pentru coperta cărții [featured image](https://codex.wordpress.org/Post_Thumbnails). Dar pentru că vrem să învățăm lucruri, voi aborda cealaltă metodă: integrarea cu galeria WordPress-ului.

În plus, putem să extindem puțin toată povestea și să adăugăm mai multe imagini pentru o carte (copertă, câteva poze/scan-uri etc).


#### Încărcarea fișierelor statice

Poți include fișierele statice în mai multe feluri, dar WordPress are un singur mod corect¹: folosind [wp_register_script](https://codex.wordpress.org/Function_Reference/wp_register_script)/[wp_register_stype](https://codex.wordpress.org/Function_Reference/wp_register_style) în interiorul hook-ul `wp_enqueue_scripts` pentru paginile de frontend, respectiv hook-ul  `admin_enqueue_scripts` pentru paginile de admin.

¹_Mai există și varianta `wp_print_scripts`/`admin_print_scripts` dar consider că în acest caz este un pic overkill_

Pentru că vrem ca pluginul nostru să fie ușor de tradus, vom folosi și [`wp_localize_script`](https://codex.wordpress.org/Function_Reference/wp_localize_script). Această funcție va genera un obiect Javascript accesibil global, astfel încât vom putea folosi în script-urile noastre ceva de genul `book_review_i18n.uploaderTitle`.

```php
// index.php
add_action('admin_enqueue_scripts', function ($hook) {
    wp_register_script('book-review-fileUpload', plugin_dir_url(__FILE__) . 'assets/javascripts/fileUpload.js', array('jquery'), '1');
    wp_enqueue_script('book-review-fileUpload');

    wp_localize_script('book-review-fileUpload', 'book_review_i18n', array(
        'uploaderTitle' => __('Upload a book Cover'),
        'uploaderButton' => __('Use selected Image')
    ));
});

```

### Galeria WordPress
De vreo doi-trei ani, WordPress a renunțat la modul vechi de administrare al imaginilor și s-a trecut la o aplicație Backbone care este destul de extensibilă. Noi vom avea nevoie doar de funcționalitate de bază: upload și selectarea fișierelor existente.

Pentru că jQuery din WordPress are modul de compatibilitate activat, vom folosi un `document.ready` cu `$` trimis ca parametru la callback, astfel încât `$` va fi disponibil.

Dacă nu facem asta, orice selector de jQuery va fi de de forma `jQuery('div')`.

```javascript
jQuery(document).ready(function($){
    // codul nostru
});
```


Înainte de a continua cu JS va trebui să adăugăm un trigger în clasa Metabox.php:


```php
// inc/bookReview/Metabox.php @ addFields
$fields[] = $this->getImageUploader($post->ID);
```

Respectiv metoda `getImageUploader`:

```php

// inc/bookReview/Metabox.php
protected function getImageUploader($postID)
{
    $value = get_post_meta($postID, '_book_cover', true);
    $field[] = sprintf('<input type="text" name="_book_cover" value="%s" class="js-bookCover">', esc_attr($value));
    $field[] = sprintf('<button class="js-uploadBookCover">%s</button>', __('Upload Book Cover'));

    return sprintf('<p>%s</p>', implode("\n", $field));
}
```

Să nu uităm să adăugăm și în metoda `saveFields` noul câmp:

```php
// inc/bookReview/Metabox.php @ saveFields
update_post_meta($postID, '_book_cover', sanitize_text_field($_POST['_book_cover']));
```

Momentan nu adăugăm preview, ci doar pregătim terenul. De asemenea, `input`-ul în care vor fi stocate ID-urile imaginilor va fi, în final, de tip `hidden`, dar momentan avem nevoie să vedem cum funcționază, deci rămâne de tip `text`.

De asemenea, toate elementele ce le vom folosi din JS vor avea o clasă de genul `js-*` pentru a putea separa ușor lucrurile.

```javascript
// assets/javascripts/fileUpload.js
var frame = wp.media({
    title : book_review_i18n.uploaderTitle,
    multiple : false,
    library : {
        type : 'image'
    },
    button : {
        text : book_review_i18n.uploaderButton
    }
});

$('.js-uploadBookCover').on('click', function(e){
    e.preventDefault();
    frame.open();
});

frame.on('close',function() {
    var attachments = frame.state().get('selection').toJSON();
    $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
});
```

În acest moment avem și un media manager cât de cât funcțional. Să punem totul în Git!

#### Git

```
git add .
git commit -am "Added media uploader"
```


### Galeria WordPress - Îmbunătățiri
Sunt mai multe probleme cu file uploader-ul nostru:

1. Dacă deschizi galeria și o închizi fără să selectezi nimic, se pierde valoarea stocată.
2. După ce salvezi, dacă deschizi galeria din nou nu ai nici o imagine selectată;


```diff
@ assets/javascripts/fileUpload.js

- $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
+
+ if(attachments.length){
+     $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
+ }
```

Asta a rezolvat prima problemă. Cum o rezolvăm pe a doua? Așa cum avem un event pentru `close`, avem un event și pentru `open`. Prin urmare:

```javascript
// assets/javascripts/fileUpload.js

frame.on('open', function(){
    var selection = frame.state().get('selection');
    var id = $('.js-bookCover').val();
    attachment = wp.media.attachment(id);
    attachment.fetch();
    selection.add( attachment ? [ attachment ] : [] );
});
```

#### Git
Acum că am rezolvat problemele, să mai facem un commit:
```
git commit -am "Fixed media uploader issues"
```

### Galeria WordPress - Și mai multe Îmbunătățiri!
Acum avem posibilitatea de a selecta o imagine, nu ar fi frumos să putem să:

1. O vedem în admin?
2. O ștergem :)

#### Preview pentru imaginea proaspăt adăugată

Ajustăm un pic metoda `getImageUploader`, astfel încât să afișăm imaginea selectată și salvată. În plus, schimbăm și tipul  `input`-ului din `text` în `hidden`:

```diff
//inc/bookReview/Metabox.php
protected function getImageUploader($postID)
{
  $value = get_post_meta($postID, '_book_cover', true);
- $field[] = sprintf('<input type="text" name="_book_cover" value="%s" class="js-bookCover">', esc_attr($value));
- $field[] = sprintf('<button class="js-uploadBookCover">%s</button>', __('Upload Book Cover'));
+
+ $attachmentPreview = '';
+ if (!empty($value)) {
+     $size = apply_filters('book-review/images/cover-size', 'thumbnail');
+     $attachmentPreview = wp_get_attachment_image($value, $size);
+ }
+
+ $field[] = sprintf('<input type="hidden" name="_book_cover" value="%s" class="js-bookCover">', esc_attr($value));
+ $field[] = sprintf('<span class="previewBookCover js-previewBookCover">%s</span>', $attachmentPreview);
+ $field[] = sprintf('<button class="button-secondary js-uploadBookCover">%s</button>', __('Upload Book Cover'));
```

Pentru a avea un preview funcțional și când se schimbă imaginea (deci nu doar la refresh) modificăm `fileUpload.js` astfel:

```diff
// assets/javascripts/fileUpload.js
   $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
+  var attachmentPreview = attachments[0].sizes.thumbnail;
+  var previewImage = $('<img />').attr({
+      src : attachmentPreview.url,
+      width : attachmentPreview.width,
+      height : attachmentPreview.height,
+  });
+
+  $('.js-previewBookCover').html(previewImage);
```

#### Git

```
git commit -am "Media upload will show preview"
```

#### Dimensiune dinamică a imaginii
În acest moment putem avea o dimensiune de preview când se încarcă paginii și o altă dimensiune când schimbăm imaginea. În configurația curentă nu e cazul, dar ce se întâmplă dacă un alt programator va folosi filtrul `book-review/images/cover-size` ?

Vom trimite această dimensiune și în `fileUpload.js` astfel încât schimbarea imaginii nu înseamnă și schimbarea dimensiunii de afișare.

Pentru asta, mutăm filtrul înaintea condiției, redenumim variabila astfel încât va avea un nume ceva mai sugestiv, după care trimitem dimensiunea ca parametru `data-*`:

```diff
inc/bookReview/Metabox.php
  $attachmentPreview = '';
+
+ $previewSize = apply_filters('book-review/images/cover-size', 'thumbnail');
  if (!empty($value)) {
-     $size = apply_filters('book-review/images/cover-size', 'thumbnail');
-     $attachmentPreview = wp_get_attachment_image($value, $size);
+     $attachmentPreview = wp_get_attachment_image($value, $previewSize);
  }

  $field[] = sprintf('<input type="hidden" name="_book_cover" value="%s" class="js-bookCover">', esc_attr($value));
- $field[] = sprintf('<span class="previewBookCover js-previewBookCover">%s</span>', $attachmentPreview);
+ $field[] = sprintf('<span class="previewBookCover js-previewBookCover" data-preview-size="%s">%s</span>', $previewSize, $attachmentPreview);
```

Pentru că se aglomerează situația din callback-ul `close` vom extrage tot ce ține de preview în funcția `previewAttachment`:

```diff
// assets/javascripts/fileUpload.js
if(attachments.length){
   $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
-  var attachmentPreview = attachments[0].sizes.thumbnail;
-  var previewImage = $('<img />').attr({
-      src : attachmentPreview.url,
-      width : attachmentPreview.width,
-      height : attachmentPreview.height,
-  });
-
-  $('.js-previewBookCover').html(previewImage);
+  previewAttachment(attachments[0]);
}
```


```diff
// assets/javascripts/fileUpload.js
+ function previewAttachment(attachment) {
+     var attachmentPreview = attachment.sizes.thumbnail;
+     var previewImage = $('<img />').attr({
+         src : attachmentPreview.url,
+         width : attachmentPreview.width,
+         height : attachmentPreview.height,
+     });
+
+     $('.js-previewBookCover').html(previewImage);
+ }
```


După care vom ține cont și de dimensiunea specificată în atributul `data-preview-size`:

```diff
// assets/javascripts/fileUpload.js
     function previewAttachment(attachment) {
-        var attachmentPreview = attachment.sizes.thumbnail;
+        var previewContainer = $('.js-previewBookCover');
+        var attachmentPreview = attachment.sizes[previewContainer.data('previewSize')];
+
         var previewImage = $('<img />').attr({
             src : attachmentPreview.url,
             width : attachmentPreview.width,
             height : attachmentPreview.height,
         });

-        $('.js-previewBookCover').html(previewImage);
+        previewContainer.html(previewImage);
     }
```


#### Git

```
git commit -am "Media upload will show preview at the right size"
```
