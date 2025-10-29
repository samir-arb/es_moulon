<?php
/**
 * ========================================
 * IMAGE OPTIMIZER - Conversion WebP
 * ========================================
 * Convertit automatiquement les images uploadées en WebP
 * pour améliorer les performances et le SEO
 */

/**
 * Convertit une image en WebP avec compression optimisée
 * 
 * @param string $source_path Chemin du fichier source
 * @param int $quality Qualité WebP (0-100, recommandé: 80-90)
 * @return string|false Chemin du fichier WebP créé ou false en cas d'erreur
 */
function convertToWebP($source_path, $quality = 85) {
    // Vérifier que le fichier existe
    if (!file_exists($source_path)) {
        error_log("Image Optimizer: Fichier source introuvable - $source_path");
        return false;
    }

    // Vérifier l'extension GD WebP
    if (!function_exists('imagewebp')) {
        error_log("Image Optimizer: Extension GD WebP non disponible");
        return false;
    }

    // Déterminer le type MIME
    $image_info = @getimagesize($source_path);
    if ($image_info === false) {
        error_log("Image Optimizer: Impossible de lire l'image - $source_path");
        return false;
    }

    $mime_type = $image_info['mime'];
    
    // Créer une ressource image selon le type
    $image = false;
    
    switch ($mime_type) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source_path);
            // Préserver la transparence
            if ($image) {
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            // Déjà en WebP, on optimise quand même
            $image = @imagecreatefromwebp($source_path);
            break;
        default:
            error_log("Image Optimizer: Type MIME non supporté - $mime_type");
            return false;
    }

    if (!$image) {
        error_log("Image Optimizer: Impossible de créer la ressource image");
        return false;
    }

    // Créer le chemin de sortie WebP
    $pathinfo = pathinfo($source_path);
    $webp_path = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.webp';

    // Convertir en WebP
    $success = imagewebp($image, $webp_path, $quality);
    imagedestroy($image);

    if (!$success) {
        error_log("Image Optimizer: Échec de la conversion WebP");
        return false;
    }

    // Calculer la réduction de taille
    $original_size = filesize($source_path);
    $webp_size = filesize($webp_path);
    $reduction = round((1 - $webp_size / $original_size) * 100, 1);

    error_log("Image Optimizer: Conversion réussie - Réduction: {$reduction}% ({$original_size} → {$webp_size} bytes)");

    return $webp_path;
}

/**
 * Optimise une image uploadée en la convertissant en WebP
 * Supprime l'original si la conversion réussit
 * 
 * @param string $uploaded_file_path Chemin du fichier uploadé
 * @param bool $keep_original Garder l'image originale (false par défaut)
 * @param int $quality Qualité WebP (85 par défaut)
 * @return array ['success' => bool, 'webp_path' => string, 'message' => string]
 */
function optimizeUploadedImage($uploaded_file_path, $keep_original = false, $quality = 85) {
    $result = [
        'success' => false,
        'webp_path' => null,
        'original_path' => $uploaded_file_path,
        'message' => ''
    ];

    // Vérifier si c'est une image
    $image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime_type = @mime_content_type($uploaded_file_path);
    
    if (!in_array($mime_type, $image_types)) {
        $result['message'] = "Le fichier n'est pas une image compatible";
        return $result;
    }

    // Convertir en WebP
    $webp_path = convertToWebP($uploaded_file_path, $quality);

    if ($webp_path === false) {
        $result['message'] = "Échec de la conversion WebP";
        return $result;
    }

    $result['success'] = true;
    $result['webp_path'] = $webp_path;
    $result['webp_filename'] = basename($webp_path);
    $result['message'] = "Image convertie en WebP avec succès";

    // Supprimer l'original si demandé
    if (!$keep_original && $mime_type !== 'image/webp') {
        if (@unlink($uploaded_file_path)) {
            $result['message'] .= " (original supprimé)";
        }
    }

    return $result;
}

/**
 * Redimensionne une image si elle dépasse les dimensions max
 * Utile pour éviter les uploads trop volumineux
 * 
 * @param string $image_path Chemin de l'image
 * @param int $max_width Largeur maximale (défaut: 1920px)
 * @param int $max_height Hauteur maximale (défaut: 1920px)
 * @return bool Succès du redimensionnement
 */
function resizeImageIfNeeded($image_path, $max_width = 1920, $max_height = 1920) {
    $image_info = @getimagesize($image_path);
    if (!$image_info) {
        return false;
    }

    list($width, $height) = $image_info;

    // Pas besoin de redimensionner
    if ($width <= $max_width && $height <= $max_height) {
        return true;
    }

    // Calculer les nouvelles dimensions en conservant le ratio
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);

    // Créer l'image source
    $mime_type = $image_info['mime'];
    $source = false;

    switch ($mime_type) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($image_path);
            break;
        case 'image/png':
            $source = @imagecreatefrompng($image_path);
            break;
        case 'image/gif':
            $source = @imagecreatefromgif($image_path);
            break;
        case 'image/webp':
            $source = @imagecreatefromwebp($image_path);
            break;
    }

    if (!$source) {
        return false;
    }

    // Créer l'image redimensionnée
    $resized = imagecreatetruecolor($new_width, $new_height);

    // Préserver la transparence pour PNG/WebP
    if ($mime_type === 'image/png' || $mime_type === 'image/webp') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
    }

    // Redimensionner
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Sauvegarder selon le type
    $success = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $success = imagejpeg($resized, $image_path, 90);
            break;
        case 'image/png':
            $success = imagepng($resized, $image_path, 8);
            break;
        case 'image/gif':
            $success = imagegif($resized, $image_path);
            break;
        case 'image/webp':
            $success = imagewebp($resized, $image_path, 85);
            break;
    }

    imagedestroy($source);
    imagedestroy($resized);

    return $success;
}
