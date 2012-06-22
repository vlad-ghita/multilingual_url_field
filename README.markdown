Field: Multilingual URL
======================



## 1 About

The multilingual version of [URL field](http://symphonyextensions.com/extensions/url_field/).



## 2 Installation

Dependencies:

1. Install [URL field](http://symphonyextensions.com/extensions/url_field/) if it's not already installed.
2. Install [Multilingual Entry URL](http://symphonyextensions.com/extensions/multilingual_entry_url/) if it's not already installed.

This extension:

1. Upload the `multilingual_url_field` folder in this archive to your Symphony `extensions` folder.
2. On `Extensions` page in Admin, install it by selecting `Field: Multilingual URL`, choose `Enable/Install` from the `With Selected` menu, then click `Apply`.
3. The field will be available in the list when creating a Section.



## 3 Usage

1\_ Add a `Multilingual Entry URL` field named `View on site` to `Section A` (I assume you have a `Multilingual Text box` with name = `Title`):

Set a slick value for `Anchor Label`: `{entry/title/item[ @lang='$language_code' ]}`<br />
Set a slick value for `Anchor URL`: `/entries/{entry/title/item[ @lang='$language_code' ]/@handle}`

2\_ Create some entries in `Section A`.

3\_ Go to `Section B` and add `Multilingual URL` field to it. In `Values` select choose `View on site` from `Section A`.

4\_ Create an entry in `Section B`. You can swtich between `Internal` and `External` links. Internal select will
be populated with all entries from `Section A` in current language of the Author.

For External links, the value must be a valid URI.

If you don't select any sections in field settings, the field will accept only External links.
