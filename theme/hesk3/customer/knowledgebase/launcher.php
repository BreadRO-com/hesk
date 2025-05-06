<?php
global $hesk_settings, $hesklang;
/**
 * @var array $currentCategory
 * @var array $subcategories
 * @var string $subcategoriesWidth
 * @var string $parentLink
 * @var array $articlesInCategory
 * @var array $serviceMessages
 * @var boolean $noSearchResults
 * @var array $topArticles
 * @var array $latestArticles
 * @var array $latestArticles
 * @var bool $customerLoggedIn - `true` if a customer is logged in, `false` otherwise
 * @var array $customerUserContext - User info for a customer if logged in.  `null` if a customer is not logged in.
 */

// This guard is used to ensure that users can't hit this outside of actual HESK code
if (!defined('IN_SCRIPT')) {
    die();
}

require_once(TEMPLATE_PATH . 'customer/util/alerts.php');
require_once(TEMPLATE_PATH . 'customer/util/kb-search.php');
require_once(TEMPLATE_PATH . 'customer/util/rating.php');
require_once(TEMPLATE_PATH . 'customer/partial/login-navbar-elements.php');

$service_message_type_to_class = array(
    '0' => 'none',
    '1' => 'success',
    '2' => '', // Info has no CSS class
    '3' => 'warning',
    '4' => 'danger'
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $hesk_settings['tmp_title']; ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo HESK_PATH; ?>img/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo HESK_PATH; ?>img/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo HESK_PATH; ?>img/favicon/favicon-16x16.png" />
    <link rel="manifest" href="<?php echo HESK_PATH; ?>img/favicon/site.webmanifest" />
    <link rel="mask-icon" href="<?php echo HESK_PATH; ?>img/favicon/safari-pinned-tab.svg" color="#5bbad5" />
    <link rel="shortcut icon" href="<?php echo HESK_PATH; ?>img/favicon/favicon.ico" />
    <meta name="msapplication-TileColor" content="#2d89ef" />
    <meta name="msapplication-config" content="<?php echo HESK_PATH; ?>img/favicon/browserconfig.xml" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="format-detection" content="telephone=no" />
    <link rel="stylesheet" media="all" href="<?php echo TEMPLATE_PATH; ?>customer/css/app<?php echo $hesk_settings['debug_mode'] ? '' : '.min'; ?>.css?<?php echo $hesk_settings['hesk_version']; ?>" />
    <!--[if IE]>
    <link rel="stylesheet" media="all" href="<?php echo TEMPLATE_PATH; ?>customer/css/ie9.css" />
    <![endif]-->
    <!--suppress CssOverwrittenProperties -->
    <style>
        .topics__block {
            width: <?php echo $subcategoriesWidth; ?>;
        }

        .content .block__head {
            margin-bottom: <?php echo $currentCategory['id'] != 1 ? '0' : '16px' ?>;
        }

        .back-link {
            display; -ms-flexbox;
            display: flex;
            margin-bottom: 16px;
            text-align: center;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-pack: center;
            justify-content: center;
        }
    </style>
    <?php include(TEMPLATE_PATH . '../../head.txt'); ?>
</head>

<body class="cust-help">
<?php include(TEMPLATE_PATH . '../../header.txt'); ?>
<div class="wrapper">
    <main class="main">
        <div class="main__content">
            <div class="contr">
                <?php if (count($articlesInCategory) > 0): ?>
                <article class="article">
                    <div class="block__head">
                        <h3 class="h-3 text-center"><?php echo $currentCategory['name']; ?></h3>
                    </div>
                    <?php foreach ($articlesInCategory as $article): ?>
                    <a href="knowledgebase.php?article=<?php echo $article['id']; ?>" class="preview">
                        <div class="icon-in-circle">
                            <svg class="icon icon-knowledge">
                                <use xlink:href="<?php echo TEMPLATE_PATH; ?>customer/img/sprite.svg#icon-knowledge"></use>
                            </svg>
                        </div>
                        <div class="preview__text">
                            <h5 class="preview__title"><?php echo $article['subject']; ?></h5>
                            <p class="navlink__descr"><?php echo $article['content_preview']; ?></p>
                        </div>
                    </a>
                    <!--[if IE]>
                        <p>&nbsp;</p>
                    <![endif]-->
                    <?php endforeach; ?>
                </article>
                <?php
                endif;

                // No sub-categories and no articles in this category
                if ( ! count($articlesInCategory) && ! count($subcategories)):
                ?>
                <div class="main__content notice-flash">
                    <div class="notification blue text-center">
                        <?php echo $hesklang['noac']; ?><br><br>
                        <a class="link" href="javascript:history.go(-1)"><?php echo $hesklang['back']; ?></a>
                    </div>
                </div>
                <?php
                endif;
                ?>
            </div>
        </div>
<?php
/*******************************************************************************
The code below handles HESK licensing and must be included in the template.

Removing this code is a direct violation of the HESK End User License Agreement,
will void all support and may result in unexpected behavior.

To purchase a HESK license and support future HESK development please visit:
https://www.hesk.com/buy.php
*******************************************************************************/
$hesk_settings['hesk_license']('Qo8Zm9vdGVyIGNsYXNzPSJmb290ZXIiPg0KICAgIDxwIGNsY
XNzPSJ0ZXh0LWNlbnRlciI+UG93ZXJlZCBieSA8YSBocmVmPSJodHRwczovL3d3dy5oZXNrLmNvbSIgY
2xhc3M9ImxpbmsiPkhlbHAgRGVzayBTb2Z0d2FyZTwvYT4gPHNwYW4gY2xhc3M9ImZvbnQtd2VpZ2h0L
WJvbGQiPkhFU0s8L3NwYW4+PGJyPk1vcmUgSVQgZmlyZXBvd2VyPyBUcnkgPGEgaHJlZj0iaHR0cHM6L
y93d3cuc3lzYWlkLmNvbS8/dXRtX3NvdXJjZT1IZXNrJmFtcDt1dG1fbWVkaXVtPWNwYyZhbXA7dXRtX
2NhbXBhaWduPUhlc2tQcm9kdWN0X1RvX0hQIiBjbGFzcz0ibGluayI+U3lzQWlkPC9hPjwvcD4NCjwvZ
m9vdGVyPg0K',"\104", "a809404e0adf9823405ee0b536e5701fb7d3c969");
/*******************************************************************************
END LICENSE CODE
*******************************************************************************/
?>
    </main>
</div>
<?php include(TEMPLATE_PATH . '../../footer.txt'); ?>
<script src="<?php echo TEMPLATE_PATH; ?>customer/js/jquery-3.5.1.min.js"></script>
<script src="<?php echo TEMPLATE_PATH; ?>customer/js/hesk_functions.js?<?php echo $hesk_settings['hesk_version']; ?>"></script>
<script src="<?php echo TEMPLATE_PATH; ?>customer/js/svg4everybody.min.js"></script>
<script src="<?php echo TEMPLATE_PATH; ?>customer/js/selectize.min.js?<?php echo $hesk_settings['hesk_version']; ?>"></script>
<script src="<?php echo TEMPLATE_PATH; ?>customer/js/app<?php echo $hesk_settings['debug_mode'] ? '' : '.min'; ?>.js?<?php echo $hesk_settings['hesk_version']; ?>"></script>
</body>
</html>
