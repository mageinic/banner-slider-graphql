# Banner Slider GraphQL

**Banner Slider GraphQL is a part of MageINIC Banner Slider extension that adds GraphQL features.** This extension extends Banner Slider definitions.

## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageinic/banner-slider-graphql

php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

**Note:**
Magento 2 Banner Slider GraphQL requires installing [MageINIC Banner Slider](https://github.com/mageinic/Banner-Slider) in your Magento installation.

**Or Install via composer [Recommend]**
```
composer require mageinic/banner-slider
```

## 2. How to use

- To view the queries that the **MageINIC Banner Slider GraphQL** extension supports, you can check `BannerSlider GraphQl User Guide.pdf` Or run `BannerSliderGraphQL.json` in Postman.

## 3. Get Support

- Feel free to [contact us](https://www.mageinic.com/contact.html) if you have any further questions.
- Like this project, Give us a **Star**
