<?php

$filePath = 'firewall-logs.csv';
$tempFilePath = 'firewall-logs-fixed.csv';

if (!file_exists($filePath)) {
    die("Input file '$filePath' does not exist.\n");
}

$memoryLimit = 300 * 1024 * 1024; // 300 MB
ini_set('memory_limit', $memoryLimit);

$inputFile = fopen($filePath, 'r');
$outputFile = fopen($tempFilePath, 'w');

$header = fgetcsv($inputFile);
$headerMap = array_flip($header);

if (!isset($headerMap['User Agent'])) {
    fclose($inputFile);
    fclose($outputFile);
    die("User Agent column not found in the CSV file.\n");
}

$userAgentIndex = $headerMap['User Agent'];

fputcsv($outputFile, $header);

while (($row = fgetcsv($inputFile)) !== FALSE) {
    if (isset($row[$userAgentIndex])) {
        $userAgent = trim($row[$userAgentIndex]);

        $row[$userAgentIndex] = str_replace('"', '""', $userAgent);
    }

    fputcsv($outputFile, $row);
}

fclose($inputFile);
fclose($outputFile);

echo "User-Agent column changed and new file generated successfully.\n";
?>
