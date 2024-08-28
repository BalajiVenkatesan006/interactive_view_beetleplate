<?php
$baseDirectory = 'data/';
$datasets = array_filter(glob($baseDirectory . '*'), 'is_dir');
$datasetNames = array_map('basename', $datasets);

function selectEveryNth($array, $n) {
    $result = array();

    // Start the loop from the 0 index and increment by $n each time
    for ($i = $n - 1; $i < count($array); $i += $n) {
        $result[] = $array[$i];
    }

    return $result;
}

function getImagePathsForDataset($directory) {
    $images = glob($directory . '/*.jpg');

    // Sort images numerically based on the number in the filename
    usort($images, function($a, $b) {
        $numA = intval(pathinfo($a, PATHINFO_FILENAME));
        $numB = intval(pathinfo($b, PATHINFO_FILENAME));
        return $numA - $numB;
    });

    return array_map('basename', $images);
}

$n = isset($_GET["n"]) ? $_GET["n"] : 1;

foreach ($datasets as $dataset) {
    $images = getImagePathsForDataset($dataset);

    echo (count($images). "\n");
    $images = selectEveryNth($images, $n);
    echo (count($images). "\n");
    file_put_contents($dataset . '/images.json', json_encode($images));
}

echo "Created JSON files.";

?>