<?php
require_once __DIR__ . '/config.php';
$title = $title ?? 'ES Moulon';
$pageClass = $pageClass ?? '';
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>

  <link href="https://fonts.googleapis.com/css2?family=Lily+Script+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Dirt&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="<?= asset('_front.css/styles.css') ?>">
  <link rel="stylesheet" href="<?= asset('_front.css/generics.css') ?>">
  <link rel="stylesheet" href="<?= asset('_front.css/formulaires.css') ?>">
  <link rel="stylesheet" href="<?= asset('_front.css/actualites.css') ?>">
  <link rel="stylesheet" href="<?= asset('_front.css/cookie-consent.css') ?>">
</head>

<body class="<?= htmlspecialchars($pageClass) ?>">