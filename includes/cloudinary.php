<?php
/**
 * Cloudinary Upload Helper for UrbanFlow
 * Uses Cloudinary's REST Upload API (no SDK needed)
 * Credentials are loaded from Vercel environment variables
 */

function uploadToCloudinary($filePath, $folder = 'urbanflow') {
    $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
    $api_key    = getenv('CLOUDINARY_API_KEY');
    $api_secret = getenv('CLOUDINARY_API_SECRET');

    // If Cloudinary is not configured, return null gracefully
    if (empty($cloud_name) || empty($api_key) || empty($api_secret)) {
        return null;
    }

    $timestamp  = time();
    $public_id  = $folder . '/' . uniqid('uf_', true);
    $signature_string = "folder={$folder}&public_id={$public_id}&timestamp={$timestamp}{$api_secret}";
    $signature  = sha1($signature_string);

    $upload_url = "https://api.cloudinary.com/v1_1/{$cloud_name}/auto/upload";

    $postFields = [
        'file'       => new CURLFile($filePath),
        'api_key'    => $api_key,
        'timestamp'  => $timestamp,
        'folder'     => $folder,
        'public_id'  => $public_id,
        'signature'  => $signature,
    ];

    $ch = curl_init($upload_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['secure_url'])) {
        return $result['secure_url'];
    }

    return null;
}

/**
 * Upload raw binary data (e.g. base64-decoded audio) to Cloudinary
 * Saves to a temp file first, then uploads
 */
function uploadBinaryToCloudinary($binaryData, $filename, $folder = 'urbanflow/audio') {
    // Write to /tmp which is the only writable dir on Vercel
    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($tmpPath, $binaryData);

    $url = uploadToCloudinary($tmpPath, $folder);

    // Clean up temp file
    if (file_exists($tmpPath)) {
        @unlink($tmpPath);
    }

    return $url;
}

/**
 * Upload an image file to Cloudinary using its tmp path from $_FILES
 */
function uploadImageToCloudinary($tmpFilePath, $folder = 'urbanflow/images') {
    return uploadToCloudinary($tmpFilePath, $folder);
}
?>
