<?php

$filePath = 'firewall-logs-fixed.csv';

if (!file_exists($filePath)) {
    die("Input file '$filePath' does not exist. Please run preprocess.php first.\n");
}

$memoryLimit = 300 * 1024 * 1024; // 300 MB
ini_set('memory_limit', $memoryLimit);

$requestCounts = [];
$endpoints = [];
$ipRequests = [];

if (($handle = fopen($filePath, 'r')) !== FALSE) {
    $header = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== FALSE) {
        $timestamp = strtotime($row[8]);
        if ($timestamp === false) {
            continue;
        }
        $ip = $row[2];
        $endpoint = $row[6];

        $timeBucket = floor($timestamp / 10) * 10;
        if (!isset($requestCounts[$timeBucket])) {
            $requestCounts[$timeBucket] = [];
        }
        if (!isset($requestCounts[$timeBucket][$ip])) {
            $requestCounts[$timeBucket][$ip] = 0;
        }
        $requestCounts[$timeBucket][$ip]++;

        if (!isset($endpoints[$endpoint])) {
            $endpoints[$endpoint] = ['count' => 0, 'ips' => []];
        }
        $endpoints[$endpoint]['count']++;
        if (!isset($endpoints[$endpoint]['ips'][$ip])) {
            $endpoints[$endpoint]['ips'][$ip] = 0;
        }
        $endpoints[$endpoint]['ips'][$ip]++;

        if (!isset($ipRequests[$ip])) {
            $ipRequests[$ip] = 0;
        }
        $ipRequests[$ip]++;
    }
    fclose($handle);
}


$timeSeriesFile = fopen('request_timeseries.csv', 'w');
fputcsv($timeSeriesFile, ['Timestamp', 'Request Count']);

$totalRequestCounts = [];

foreach ($requestCounts as $time => $ipCounts) {
    foreach ($ipCounts as $ip => $count) {
        if (isset($totalRequestCounts[$time])) {
            $totalRequestCounts[$time] += $count;
        } else {
            $totalRequestCounts[$time] = $count;
        }
    }
}

foreach ($totalRequestCounts as $time => $count) {
    fputcsv($timeSeriesFile, [date('Y-m-d H:i:s', $time), $count]);
}

fclose($timeSeriesFile);

arsort($endpoints);
$topEndpoints = array_slice($endpoints, 0, 5, true);

$endpointReport = "Top 5 Most Requested Endpoints:\n";
foreach ($topEndpoints as $endpoint => $data) {
    $mostFrequentIp = array_keys($data['ips'], max($data['ips']))[0];
    $endpointReport .= sprintf("%s - %d requests, Most frequent IPs: %s\n", $endpoint, $data['count'], $mostFrequentIp);
}
file_put_contents('endpoint_report.txt', $endpointReport);

arsort($ipRequests);
$topIps = array_slice($ipRequests, 0, 5, true);

$ipReport = "Top 5 Requesting IPs:\n";
foreach ($topIps as $ip => $count) {
    $ipReport .= sprintf("%s - %d requests\n", $ip, $count);
}
file_put_contents('ip_report.txt', $ipReport);

$ipTimeSeriesFile = fopen('ip_timeseries.csv', 'w');
$header = array_merge(['Timestamp'], array_keys($topIps));
fputcsv($ipTimeSeriesFile, $header);

foreach ($requestCounts as $time => $ipCounts) {
    $row = [date('Y-m-d H:i:s', $time)];
    foreach (array_keys($topIps) as $ip) {
        $ipCount = isset($ipCounts[$ip]) ? $ipCounts[$ip] : 0;
        $row[] = $ipCount;
    }
    fputcsv($ipTimeSeriesFile, $row);
}

fclose($ipTimeSeriesFile);

echo "Analysis complete. Reports and charts generated.\n";
?>
