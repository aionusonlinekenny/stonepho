<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/../data/reviews.json';

if (!file_exists($file)) {
    $sample = [
        ["id" => "rev1", "author" => "John D.", "rating" => 5, "comment" => "Great food!"],
        ["id" => "rev2", "author" => "Sarah L.", "rating" => 4, "comment" => "Nice atmosphere."]
    ];
    file_put_contents($file, json_encode($sample, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
}

echo file_get_contents($file);
