<?php 
session_start();
require_once("vendor/autoload.php");
require_once("functions.php");

use Slim\Slim;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;
use Hcode\Model\Products;

$app = new Slim();

$app->config('debug', true);

require_once("site.php");
require_once("admin.php");
require_once("users.php");
require_once("categories.php");
require_once("products.php");
require_once("functions.php");
require_once("admin-orders.php");

$app->run();
