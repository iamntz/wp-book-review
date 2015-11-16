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
- ajax în WordPress

-----------------

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

### Încărcarea fișierelor

Pentru a include fișierele necesare, avem două posibilități: ori folosim un autoloader ori le includem manual. Pentru că vor fi doar câteva, nu are rost să ne complicăm, deci vom recurge la clasicul `require`.


#### Git

Inițializăm și facem primul commit

```
git init
git add .
git commit -am "Initial commit"
```

